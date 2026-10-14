// Ouvre chaque page de output/urls.json dans Chromium (connecté avec le compte qui a tous les droits)
// et relève : code HTTP, erreurs JavaScript, requêtes en erreur, images/scripts introuvables.
// Option --captures : enregistre une capture pleine page de chacune dans output/captures/<espace>/.
// Usage seul : node pages.mjs [--captures]
import fs from 'node:fs';
import path from 'node:path';
import { OUTPUT, isMain, openSession, urlsFor, visit, isLoginPage, writeReport, log } from './lib.mjs';

export async function checkPages({ captures = false } = {}) {
  const runs = [['public', null], ['admin', 'admin'], ['agent', 'super.agent']];
  const report = [];
  for (const [space, account] of runs) {
    const urls = urlsFor(space);
    const { browser, page, login } = await openSession(account);
    const shotDir = path.join(OUTPUT, 'captures', space);
    if (captures) fs.mkdirSync(shotDir, { recursive: true });
    let ok = 0;
    for (const url of urls) {
      page.resetProblems();
      const result = await visit(page, url);
      if (account && isLoginPage(page)) { await login(); }
      await page.waitForTimeout(400);
      // Le 404 de la page elle-même est compté dans « status », pas en double dans les problèmes
      const problems = page.problems().filter((p) => !p.endsWith(` GET /${url}`));
      if (result.file && (result.status >= 400 || !/pdf|sheet|excel|octet|image|zip|csv/.test(result.file))) problems.push(`fichier ${result.status} ${result.file}`);
      if (result.error) problems.push(result.error);
      const failed = result.status >= 400 || result.status === 0 || problems.length;
      if (captures && !result.file && !result.error) {
        const name = url.replace(/[^a-z0-9]+/gi, '_').replace(/^_|_$/g, '') || 'accueil';
        await page.screenshot({ path: path.join(shotDir, `${name}.png`), fullPage: true }).catch(() => {});
      }
      report.push({ space, url, status: result.status, problems });
      if (failed) { log.ko(`${result.status} /${url}`); problems.forEach(log.info); } else ok++;
    }
    (ok === urls.length ? log.ok : log.ko)(`${space} : ${ok}/${urls.length} pages sans erreur`);
    await browser.close();
  }
  writeReport('pages.json', report);
  return report.filter((r) => r.status >= 400 || r.status === 0 || r.problems.length).length;
}

if (isMain(import.meta.url)) {
  log.title('Pages');
  process.exitCode = (await checkPages({ captures: process.argv.includes('--captures') })) ? 1 : 0;
}
