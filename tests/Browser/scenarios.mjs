// Parcours guidés pour les formulaires qui demandent des étapes JavaScript (listes dépendantes, champs à
// déverrouiller) et que forms.mjs ne peut pas remplir seul. Chaque scénario fait exactement ce que ferait
// un agent, puis vérifie la réponse du serveur.
// Pour en ajouter un : une entrée dans SCENARIOS { nom, compte, async run(page, urls) } qui lève une erreur si ça échoue.
// Usage seul : node scenarios.mjs
import { fill } from './forms.mjs';
import { BASE, isMain, openSession, urlsFor, writeReport, log } from './lib.mjs';

/** Déverrouille les fiches passagers (bouton « modifier ») puis complète les champs que l'écran exige */
async function unlockPassengers(page) {
  for (const button of await page.$$('.modify_passager')) await button.click().catch(() => {});
  const form = await page.$('#passagers_form');
  await fill(form);
  return form;
}

/** Attend la réponse d'envoi (POST) dont l'adresse contient `path` après l'action donnée */
async function expectPost(page, path, action) {
  const response = page.waitForResponse((r) => r.request().method() !== 'GET' && r.url().includes(path), { timeout: 20000 })
    .catch(() => null);
  await action();
  const r = await response;
  if (!r) {
    // Rien n'est parti : on affiche les messages de validation de la page pour comprendre pourquoi
    const shown = await page.$$eval('.error, .invalid-feedback, .text-danger, .tc-toast', (els) => els
      .filter((x) => x.offsetParent && x.textContent.trim()).map((x) => x.textContent.replace(/\s+/g, ' ').trim())).catch(() => []);
    throw new Error(`formulaire non envoyé${shown.length ? ' : ' + [...new Set(shown)].slice(0, 4).join(' | ') : ''}`);
  }
  if (r.status() >= 400) {
    const detail = await r.text().then((t) => t.slice(0, 300)).catch(() => '');
    throw new Error(`${r.status()} sur ${r.url().replace(BASE, '')} ${r.status() === 422 ? detail : ''}`);
  }
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  return r;
}

/** Ouvre le menu du profil, vérifie qu'il tient dans l'écran, se déconnecte */
async function logoutFromMenu(page, space) {
  await page.goto(`${BASE}/${space}`, { waitUntil: 'networkidle' });
  await page.click('#userDropdown');
  const logout = page.locator('.dropdown-menu.show .dropdown-item.text-danger');
  await logout.waitFor({ state: 'visible', timeout: 5000 });
  const box = await logout.boundingBox();
  const width = page.viewportSize().width;
  if (!box || box.x < 0 || box.x + box.width > width) throw new Error(`menu du profil hors de l'écran (${JSON.stringify(box)})`);
  await logout.click();
  // Espace agent : message d'au revoir avant de confirmer
  const confirm = page.locator('#farewellModal.show a.btn-primary');
  if (await confirm.waitFor({ state: 'visible', timeout: 3000 }).then(() => true).catch(() => false)) await confirm.click();
  await page.waitForURL(/\/connexion/, { timeout: 10000 });
}

const SCENARIOS = [
  { nom: 'Admin : se déconnecter par le menu du profil', compte: 'admin', run: (page) => logoutFromMenu(page, 'admin') },
  { nom: 'Agent : se déconnecter par le menu du profil', compte: 'agent', run: (page) => logoutFromMenu(page, 'agent') },
  {
    nom: 'Guide des allotements : affiché dans la page, sans nouvel onglet',
    compte: 'super.agent',
    async run(page) {
      await page.goto(`${BASE}/agent/allotements`, { waitUntil: 'networkidle' });
      const pages = page.context().pages().length;
      await page.locator('.tc-page a, #content a', { hasText: 'Guide' }).first().click();
      await page.locator('.tc-viewer.is-open iframe').waitFor({ timeout: 5000 });
      if (page.context().pages().length !== pages) throw new Error('un nouvel onglet a été ouvert');
      const src = await page.locator('.tc-viewer iframe').getAttribute('src');
      const r = await page.request.get(src);
      if (r.status() !== 200) throw new Error(`guide : ${r.status()}`);
    },
  },
  {
    nom: 'Réservation de vol : modifier un passager',
    compte: 'super.agent',
    async run(page, urls) {
      const url = urls.find((u) => /^agent\/vols\/\d+\/reservations\/\d+$/.test(u));
      await page.goto(`${BASE}/${url}`, { waitUntil: 'networkidle' });
      const id = await page.locator('.modify_passager').first().getAttribute('passager_id');
      await unlockPassengers(page);
      const name = page.locator(`[id^=old_][id$=_nom_${id}]`);
      await name.fill('Scénario');
      await expectPost(page, '/reservations/enregistrer', () => page.locator('.enregister_passager:visible').first().click());
      await page.goto(`${BASE}/${url}`, { waitUntil: 'networkidle' });
      if ((await name.inputValue()) !== 'Scénario') throw new Error("le nom du passager n'a pas été enregistré");
    },
  },
  {
    nom: 'Émission : modifier un passager',
    compte: 'super.agent',
    async run(page, urls) {
      const url = urls.find((u) => /^agent\/vols\/\d+\/emission\/reservations\/\d+$/.test(u));
      await page.goto(`${BASE}/${url}`, { waitUntil: 'networkidle' });
      await unlockPassengers(page);
      const name = page.locator('#passagers_form [id^=old_][id*=_nom_]').first();
      await name.fill('Émission');
      await expectPost(page, '/emission/reservations/passagers', () => page.locator('.enregister_passager:visible').first().click());
    },
  },
  {
    nom: 'Demande client : devis',
    compte: 'super.agent',
    async run(page, urls) {
      const client = urlsFor('admin').map((u) => u.match(/^admin\/clients\/(\d+)$/)?.[1]).find(Boolean);
      await page.goto(`${BASE}/agent/demandes/nouvelle`, { waitUntil: 'networkidle' });
      // Équivaut à la recherche d'un client existant en haut de l'écran
      await page.evaluate((id) => { document.querySelector('#client_id').value = id; }, client);
      // Vol (« Aérien ») vers la première destination dont la liste des villes d'arrivée se remplit
      const found = await page.evaluate(() => {
        jQuery('#formule').val('Aérien').trigger('change');
        const destination = document.querySelector('#destination');
        for (const option of [...destination.options].filter((o) => o.value && !o.disabled)) {
          jQuery(destination).val(option.value).trigger('change');
          if ([...document.querySelector('#arrival_city').options].some((o) => o.value)) return option.value;
        }
        return null;
      });
      if (!found) throw new Error("aucune destination ne propose de ville d'arrivée");
      await page.evaluate(() => {
        const pick = (selector) => {
          const select = document.querySelector(selector);
          const option = select && [...select.options].find((o) => o.value && !o.disabled);
          if (option) jQuery(select).val(option.value).trigger('change');
        };
        ['#arrival_city', '#depart', '#Compagnie', '#Installation', '#Véhicule', '#bagage-devis'].forEach(pick);
        // Les calendriers enregistrent la date au format AAAA-MM-JJ (affichée en JJ/MM/AAAA)
        const date = (days) => new Date(Date.now() + days * 864e5).toISOString().slice(0, 10);
        document.querySelector('#devis_departure_date')._flatpickr?.setDate(date(40), true);
        document.querySelector('#devis_return_date')._flatpickr?.setDate(date(55), true);
        jQuery('#proposed_price').val('650');
      });
      await expectPost(page, '/demandes/devis', () => page.locator('#sendDevisSms').click());
    },
  },
];

export async function runScenarios() {
  const report = [];
  for (const scenario of SCENARIOS) {
    const { browser, page } = await openSession(scenario.compte);
    page.resetProblems();
    try {
      await scenario.run(page, urlsFor('agent'));
      const js = page.problems().filter((p) => p.startsWith('JS') || /^5\d\d /.test(p));
      if (js.length) throw new Error(js.join(' ; '));
      log.ok(scenario.nom);
      report.push({ scenario: scenario.nom, ok: true });
    } catch (e) {
      log.ko(`${scenario.nom} : ${e.message.split('\n')[0]}`);
      report.push({ scenario: scenario.nom, ok: false, error: e.message });
    }
    await browser.close();
  }
  writeReport('scenarios.json', report);
  return report.filter((r) => !r.ok).length;
}

if (isMain(import.meta.url)) {
  log.title('Scénarios');
  process.exitCode = (await runScenarios()) ? 1 : 0;
}
