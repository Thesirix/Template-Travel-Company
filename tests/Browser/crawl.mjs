// Explore l'application comme un utilisateur, avec chaque compte de démo : part de l'accueil de son
// espace, suit tous les liens internes (menus, boutons, tableaux) et vérifie chaque page atteinte.
// Les droits sont ainsi testés aussi : un lien affiché qui mène à une erreur 403/404/500 est signalé.
// Usage seul : node crawl.mjs [compte…]   (par défaut les 4 comptes)
import { ACCOUNTS, BASE, isMain, openSession, visit, isLoginPage, writeReport, log } from './lib.mjs';

// Liens jamais suivis : déconnexion, suppressions, téléchargements, envois
const SKIP = /logout|deconnexion|delete|supprim|destroy|remove|desactiv|export|download|telecharger|excel|pdf|imprim|print|send|sms|mail|cron|\.(pdf|xlsx?|png|jpe?g|svg|zip)$/i;
const MAX_PER_PATTERN = 2; // /agent/bateau/{id} : 2 fiches suffisent

const pattern = (p) => p.replace(/\/\d+(?=\/|$)/g, '/{id}').replace(/=\d+/g, '={n}');

export async function crawl(accounts = Object.keys(ACCOUNTS)) {
  let failures = 0;
  const report = {};
  for (const account of accounts) {
    const { browser, page, login, account: { space } } = await openSession(account);
    const seen = new Set();
    const perPattern = new Map();
    const queue = [`/${space}`];
    const results = [];
    while (queue.length) {
      const url = queue.shift();
      const pat = pattern(url);
      if (seen.has(url) || (perPattern.get(pat) || 0) >= MAX_PER_PATTERN) continue;
      seen.add(url);
      perPattern.set(pat, (perPattern.get(pat) || 0) + 1);

      page.resetProblems();
      const result = await visit(page, url);
      if (isLoginPage(page)) { results.push({ url, status: result.status, problems: ['renvoyé vers la connexion'] }); await login(); continue; }
      const problems = page.problems().filter((p) => !p.endsWith(` GET ${url}`));
      if (result.error) problems.push(result.error);
      results.push({ url, status: result.status, problems });
      if (result.status >= 400 || result.file) continue;

      const links = await page.$$eval('a[href]', (as) => as.map((a) => a.getAttribute('href'))).catch(() => []);
      for (const href of links) {
        if (!href || /^(#|javascript|mailto|tel)/.test(href)) continue;
        let u;
        try { u = new URL(href, BASE + url); } catch { continue; }
        if (u.origin !== BASE) continue;
        const next = u.pathname.replace(/\/+$/, '') + u.search;
        if (next.startsWith(`/${space}/`) && !SKIP.test(next) && !seen.has(next)) queue.push(next);
      }
    }
    await browser.close();
    const bad = results.filter((r) => r.status >= 400 || r.status === 0 || r.problems.length);
    bad.forEach((r) => { log.ko(`${r.status} ${r.url}`); r.problems.forEach(log.info); });
    (bad.length ? log.ko : log.ok)(`${account} : ${results.length - bad.length}/${results.length} pages atteintes sans erreur`);
    failures += bad.length;
    report[account] = results;
  }
  writeReport('crawl.json', report);
  return failures;
}

if (isMain(import.meta.url)) {
  log.title('Exploration des liens');
  const accounts = process.argv.slice(2).filter((a) => ACCOUNTS[a]);
  process.exitCode = (await crawl(accounts.length ? accounts : undefined)) ? 1 : 0;
}
