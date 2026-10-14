// Outils communs aux tests navigateur : configuration, comptes de démo, connexion, collecte des erreurs.
import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export const DIR = path.dirname(fileURLToPath(import.meta.url));
export const OUTPUT = path.join(DIR, 'output');
export const BASE = process.env.E2E_URL || 'http://127.0.0.1:8001';
export const PASSWORD = 'Demo1234!';

/** Comptes de démo (DemoSeeder) et l'espace dans lequel chacun se connecte */
export const ACCOUNTS = {
  admin: { space: 'admin', email: 'admin@travel-company.test' },
  'super.agent': { space: 'agent', email: 'super.agent@travel-company.test' },
  agent: { space: 'agent', email: 'agent@travel-company.test' },
  partenaire: { space: 'agent', email: 'partenaire@travel-company.test' },
};

export function urlsFor(space) {
  return JSON.parse(fs.readFileSync(path.join(OUTPUT, 'urls.json'), 'utf8'))[space] || [];
}

export function writeReport(name, data) {
  fs.mkdirSync(OUTPUT, { recursive: true });
  fs.writeFileSync(path.join(OUTPUT, name), JSON.stringify(data, null, 1));
}

/**
 * Ouvre un navigateur connecté avec un compte de démo.
 * page.problems() renvoie les erreurs relevées depuis le dernier page.resetProblems() :
 * erreurs JavaScript, réponses >= 400 de l'application, ressources introuvables (images, scripts…).
 */
export async function openSession(account) {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  let problems = [];
  let transient = false;
  page.on('pageerror', (e) => problems.push('JS : ' + e.message.split('\n')[0].slice(0, 160)));
  page.on('response', (r) => {
    if (r.status() >= 400 && r.url().startsWith(BASE) && !r.url().includes('favicon')) {
      problems.push(`${r.status()} ${r.request().method()} ${r.url().replace(BASE, '')}`);
    }
  });
  page.on('requestfailed', (r) => {
    const error = r.failure()?.errorText || '';
    // ERR_ABORTED : navigation interrompue par une autre (normal) ; les CDN externes ne sont pas testés ;
    // ERR_NO_BUFFER_SPACE / ERR_INSUFFICIENT_RESOURCES : saturation réseau passagère de Windows, la page est revérifiée
    if (/ERR_NO_BUFFER_SPACE|ERR_INSUFFICIENT_RESOURCES/.test(error)) transient = true;
    else if (!error.includes('ERR_ABORTED') && r.url().startsWith('http://')) problems.push(`ressource inaccessible ${r.url()} (${error})`);
  });
  page.on('dialog', (d) => d.accept().catch(() => {}));
  page.resetProblems = () => { problems = []; transient = false; };
  page.hadTransientError = () => transient;
  page.problems = () => [...new Set(problems)];

  const session = { browser, page, account: ACCOUNTS[account] };
  session.login = () => login(page, ACCOUNTS[account]);
  if (account) await session.login();
  return session;
}

async function login(page, { space, email }) {
  await page.goto(`${BASE}/connexion`);
  await page.fill('input[name=email]', email);
  await page.fill('input[name=password]', PASSWORD);
  await Promise.all([page.waitForLoadState('networkidle'), page.locator('form button[type=submit]').first().click()]);
  if (new URL(page.url()).pathname.endsWith('/connexion')) throw new Error(`connexion impossible avec ${email}`);
}

/**
 * Ouvre une URL. Renvoie le code HTTP, ou « fichier » si l'URL déclenche un téléchargement
 * (dans ce cas le fichier est redemandé pour vérifier son code et son type).
 */
export async function visit(page, url, retry = true) {
  const result = await load(page, url);
  if (retry && page.hadTransientError?.()) {
    // Saturation réseau de la machine : on laisse respirer puis on recharge une fois
    await page.waitForTimeout(3000);
    page.resetProblems();
    return load(page, url);
  }
  return result;
}

async function load(page, url) {
  try {
    const response = await page.goto(`${BASE}/${url.replace(/^\//, '')}`, { waitUntil: 'load', timeout: 30000 });
    await page.waitForLoadState('networkidle', { timeout: 3000 }).catch(() => {});
    return { status: response ? response.status() : 0 };
  } catch (e) {
    if (!String(e.message).includes('Download is starting')) return { status: 0, error: e.message.split('\n')[0] };
    const r = await page.request.get(`${BASE}/${url.replace(/^\//, '')}`, { timeout: 60000 });
    return { status: r.status(), file: r.headers()['content-type'] || '' };
  }
}

export const isLoginPage = (page) => new URL(page.url()).pathname.endsWith('/connexion');

/** Affichage console */
export const log = {
  title: (t) => console.log(`\n\x1b[1m${t}\x1b[0m`),
  ok: (t) => console.log(`  \x1b[32m✔\x1b[0m ${t}`),
  ko: (t) => console.log(`  \x1b[31m✘\x1b[0m ${t}`),
  info: (t) => console.log(`    ${t}`),
};

/** Vrai si le fichier est lancé directement (node pages.mjs) et non importé par run.mjs */
export const isMain = (url) => Boolean(process.argv[1]) && path.resolve(process.argv[1]) === fileURLToPath(url);
