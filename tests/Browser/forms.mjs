// Envoie chaque formulaire de chaque page, comme un utilisateur : remplit les champs vides selon leur
// type, clique le vrai bouton d'envoi, puis vérifie qu'il n'y a ni erreur serveur (500), ni route
// absente (404/405), ni jeton expiré (419), ni erreur JavaScript.
// Une erreur de validation (champ refusé par le serveur) n'est pas un échec : le formulaire a répondu.
//
// Deux passes : d'abord les formulaires « normaux », puis les suppressions/annulations (qui retirent
// des données de démo). La base de test est recréée à chaque lancement de run.mjs.
// Usage seul : node forms.mjs [--destructifs]
import fs from 'node:fs';
import path from 'node:path';
import { BASE, DIR, PASSWORD, isMain, openSession, urlsFor, isLoginPage, writeReport, log } from './lib.mjs';

const UPLOAD = path.join(DIR, 'fixtures', 'image.png');
const DANGER = /supprim|destroy|annul|relach|desactiv/i;
// Formulaires à étapes JavaScript testés par un parcours guidé de scenarios.mjs
const GUIDED = ['/reservations/enregistrer', '/emission/reservations/passagers', '/demandes/devis'];
const NEVER = /deconnexion|logout|^#$/i; // « # » : formulaire en lecture seule, sans envoi

/** Formulaires d'envoi d'une page (hors recherche GET et déconnexion) */
const listForms = (page) => page.$$eval('form', (forms) => forms.map((f, index) => ({
  index,
  action: f.getAttribute('action') || '',
  method: (f.querySelector('input[name=_method]')?.value || f.getAttribute('method') || 'GET').toUpperCase(),
  id: f.id || '',
})));

/** Remplit les champs vides d'un formulaire avec des valeurs plausibles (utilisé aussi par scenarios.mjs) */
export async function fill(form) {
  for (const input of await form.$$('input[type=file]')) {
    if (fs.existsSync(UPLOAD)) await input.setInputFiles(UPLOAD).catch(() => {});
  }
  await form.evaluate((el, password) => {
    const iso = (d) => d.toISOString().slice(0, 10);
    const future = new Date(Date.now() + 30 * 864e5);
    const farFuture = new Date(Date.now() + 3 * 365 * 864e5); // pièces d'identité valides après le voyage
    for (const field of el.querySelectorAll('input,select,textarea')) {
      if (field.disabled || field.readOnly) continue;
      const type = (field.getAttribute('type') || field.tagName).toLowerCase();
      if (['hidden', 'file', 'submit', 'button', 'reset'].includes(type)) continue;
      if (type === 'checkbox' || type === 'radio') {
        if (field.required && !field.checked) field.checked = true;
        continue;
      }
      const name = (field.name || field.id || '').toLowerCase();
      if (field.tagName === 'SELECT') {
        if (!field.value) {
          const option = [...field.options].find((o) => o.value);
          if (option) field.value = option.value;
        }
      } else if (type === 'password') {
        field.value = password;
      } else if ((type === 'tel' || /^(old_.*_)?tele?\d?(_|$)|phone/.test(name)) && !/^0[67]\d{8}$/.test(field.value)) {
        field.value = '0601020304'; // format attendu par les écrans : 06 ou 07 + 8 chiffres
      } else if (/expiration/.test(name) && (type === 'date' || /^\d{4}-/.test(field.value) || !field.value)) {
        field.value = iso(farFuture);
      } else if (!field.value) {
        field.value =
          type === 'email' || name.includes('mail') ? 'test.formulaire@example.com'
          : type === 'number' ? String(Math.max(Number(field.min || 1), 1))
          : type === 'date' ? iso(/naissance|birth/.test(name) ? new Date('1985-06-15') : future)
          : type === 'datetime-local' ? iso(future) + 'T10:00'
          : type === 'time' ? '10:00'
          : type === 'month' ? iso(future).slice(0, 7)
          : type === 'color' ? '#0f766e'
          : type === 'tel' || /tel|phone/.test(name) ? '0601020304'
          : type === 'url' ? 'https://example.com'
          : /date/.test(name) ? iso(future)
          : /prix|price|montant|amount|^nb/.test(name) ? '1'
          : 'Test formulaire';
      }
      if (type === 'number' && field.min && Number(field.value) < Number(field.min)) field.value = field.min;
      field.dispatchEvent(new Event('input', { bubbles: true }));
      field.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }, PASSWORD).catch(() => {});
}

/** Clique le vrai bouton d'envoi s'il est visible, sinon déclenche l'envoi comme le ferait la touche Entrée */
async function submit(page, form) {
  const buttons = (await form.$$('button[type=submit], input[type=submit], button:not([type])')).reverse();
  for (const button of buttons) {
    const own = await button.evaluate((b, f) => b.form === f && !b.closest('.ck'), form).catch(() => false);
    if (own && await button.isVisible().catch(() => false)) {
      if (await button.click({ timeout: 5000 }).then(() => true).catch(() => false)) return 'clic';
    }
  }
  await form.evaluate((f) => {
    const button = [...f.querySelectorAll('button[type=submit],input[type=submit],button:not([type])')].filter((b) => b.form === f && !b.closest('.ck')).pop();
    f.requestSubmit(button || undefined);
  });
  return 'programmé';
}

async function run(account, destructive, done, report) {
  const { browser, page, login, account: { space } } = await openSession(account);
  // Requêtes d'envoi (formulaires classiques et AJAX) déclenchées par le formulaire
  let sent = [];
  page.on('response', (r) => {
    const method = r.request().method();
    if (method !== 'GET' && r.url().startsWith(BASE)) sent.push({ method, url: r.url().replace(BASE, ''), status: r.status() });
  });
  page.on('download', () => sent.push({ method: 'POST', url: '(fichier téléchargé)', status: 200 }));

  for (const url of urlsFor(space)) {
    const open = async () => {
      await page.goto(`${BASE}/${url}`, { waitUntil: 'networkidle', timeout: 30000 }).catch(() => {});
      if (isLoginPage(page)) { await login(); await page.goto(`${BASE}/${url}`, { waitUntil: 'networkidle', timeout: 30000 }).catch(() => {}); }
    };
    await open();
    const forms = await listForms(page).catch(() => []);
    for (const f of forms) {
      if (f.method === 'GET' || NEVER.test(f.action) || (DANGER.test(f.action) || f.method === 'DELETE') !== destructive) continue;
      const target = `${f.method} ${(f.action.replace(BASE, '').split('?')[0] || '/' + url).replace(/\/\d+(?=\/|$)/g, '/{id}')}`;
      if (done.has(target)) continue; // chaque point d'envoi n'est testé qu'une fois
      done.add(target);

      await open();
      const form = (await page.$$('form'))[f.index];
      if (!form) continue;
      page.resetProblems();
      sent = [];
      await fill(form);
      await page.waitForTimeout(600); // listes chargées en AJAX après un premier choix
      await fill(form);
      const how = await submit(page, form).catch((e) => 'impossible : ' + e.message.split('\n')[0]);
      await page.waitForLoadState('networkidle', { timeout: 20000 }).catch(() => {});
      await page.waitForTimeout(1000);

      const body = await page.textContent('body').catch(() => '');
      const problems = [
        ...page.problems().filter((p) => p.startsWith('JS') || p.startsWith('ressource') || /^(5\d\d|404|405|419) /.test(p)),
        ...(/Server Error|Whoops|SQLSTATE|ErrorException/.test(body || '') ? ["page d'erreur serveur affichée"] : []),
      ];
      const entry = { account, page: url, form: target, id: f.id, how, sent: sent.map((s) => `${s.status} ${s.method} ${s.url}`), problems };
      if (!sent.length) {
        // Rien n'est parti : on note les champs refusés par le navigateur pour comprendre pourquoi
        // Champs refusés par le navigateur, sinon messages affichés par la validation JavaScript de la page
        entry.blocked = await form.evaluate((el) => {
          const invalid = [...el.querySelectorAll(':invalid')].filter((x) => !['FORM', 'FIELDSET'].includes(x.tagName))
            .map((x) => `${x.name || x.id} : ${x.validationMessage}`);
          const shown = [...document.querySelectorAll('.error, .invalid-feedback, .text-danger')]
            .filter((x) => x.offsetParent && x.textContent.trim()).map((x) => x.textContent.replace(/\s+/g, ' ').trim());
          return [...invalid, ...[...new Set(shown)].slice(0, 3)];
        }).catch(() => ['formulaire remplacé par la page suivante']);
      }
      report.push(entry);
      if (problems.length) { log.ko(`${target}  (page /${url})`); problems.forEach(log.info); }
    }
  }
  await browser.close();
}

export async function checkForms({ destructive = true } = {}) {
  const report = [];
  const done = new Set();
  for (const pass of destructive ? [false, true] : [false]) {
    for (const account of ['admin', 'super.agent']) await run(account, pass, done, report);
  }
  writeReport('forms.json', report);
  const failed = report.filter((r) => r.problems.length);
  const notSent = report.filter((r) => !r.sent.length && !r.problems.length);
  (failed.length ? log.ko : log.ok)(`${report.length} formulaires testés, ${report.length - notSent.length - failed.length} envoyés sans erreur, ${failed.length} en erreur`);
  const guided = notSent.filter((r) => GUIDED.some((g) => r.form.endsWith(g)));
  const manual = notSent.filter((r) => !guided.includes(r));
  if (guided.length) log.info(`${guided.length} à étapes JavaScript, testés par les scénarios guidés : ${guided.map((r) => r.form.split(' ')[1]).join(', ')}`);
  if (manual.length) {
    log.info(`${manual.length} non envoyés automatiquement (à couvrir par un scénario, voir output/forms.json) :`);
    manual.forEach((r) => log.info(`  · ${r.form}  (page /${r.page})${r.blocked?.length ? ', ' + r.blocked.join(', ') : ''}`));
  }
  return failed.length;
}

if (isMain(import.meta.url)) {
  log.title('Formulaires');
  process.exitCode = (await checkForms({ destructive: process.argv.includes('--destructifs') })) ? 1 : 0;
}
