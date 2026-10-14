// Lance toute la suite navigateur sur une copie jetable de l'application :
//   1. recrée la base travel_company_e2e avec les données de démo (votre base de travail n'est pas touchée)
//   2. démarre un serveur de test sur le port 8001
//   3. vérifie toutes les pages, explore les liens avec les 4 comptes, envoie tous les formulaires
//   4. arrête le serveur, supprime les fichiers envoyés pendant les tests, affiche le bilan
//
// Options : --captures            captures d'écran de chaque page dans output/captures/
//           --sans-formulaires    pages et liens seulement (plus rapide)
//           --seulement=scenarios une ou plusieurs étapes : pages, liens, formulaires, scenarios
//           --phpunit             lance aussi les tests PHPUnit à la fin
//           --garder-serveur      laisse le serveur de test allumé (pour regarder une erreur)
import { spawn, spawnSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { BASE, DIR, OUTPUT, log } from './lib.mjs';

const ROOT = path.resolve(DIR, '../..');
const args = process.argv.slice(2);
const option = (name) => args.includes(name);
const PHP = process.env.PHP || 'php';
const PORT = new URL(BASE).port || '80';
const DATABASE = process.env.E2E_DATABASE || 'travel_company_e2e';

// Configuration du serveur de test : base dédiée, aucun envoi réel d'e-mail ou de SMS
const env = {
  ...process.env,
  APP_ENV: 'local', APP_URL: BASE, DB_DATABASE: DATABASE, APP_LOCALE: 'fr', // les scénarios cherchent des libellés français
  CACHE_STORE: 'array', CACHE_DRIVER: 'array', MAIL_MAILER: 'array', QUEUE_CONNECTION: 'sync',
  ISENDPROID: '', ISENDPROEMETTEUR: '',
};

// Sous Windows, « php » est souvent un .bat (Herd) : il faut passer par le shell, avec une commande complète
// (les arguments sont tous écrits dans ce fichier, rien ne vient de l'extérieur)
const WINDOWS = process.platform === 'win32';
const phpCommand = (phpArgs) => (WINDOWS ? [`${PHP} ${phpArgs.join(' ')}`, [], { shell: true }] : [PHP, phpArgs, {}]);
const runPhp = (phpArgs, options) => { const [cmd, a, o] = phpCommand(phpArgs); return spawnSync(cmd, a, { ...o, ...options }); };

const php = (phpArgs, label) => {
  const r = runPhp(phpArgs, { cwd: ROOT, env, encoding: 'utf8' });
  if (r.status !== 0) {
    console.error(r.stdout, r.stderr);
    throw new Error(`échec : ${label}`);
  }
  return r.stdout;
};


async function main() {
  const started = Date.now();
  const failures = {};
  let server;
  // Les formulaires envoient et remplacent des fichiers (logos, photos, passeports, guide PDF) dans ces deux
  // dossiers : copie de sécurité au départ, remise à l'identique à la fin, même si les tests échouent
  const fileDirs = [path.join(ROOT, 'storage', 'app', 'public'), path.join(ROOT, 'public', 'uploads')];
  const backupOf = (dir) => path.join(OUTPUT, '.sauvegarde', path.relative(ROOT, dir));
  for (const dir of fileDirs) {
    fs.rmSync(backupOf(dir), { recursive: true, force: true });
    if (fs.existsSync(dir)) fs.cpSync(dir, backupOf(dir), { recursive: true });
  }
  const restoreUploads = () => {
    for (const dir of fileDirs) {
      if (!fs.existsSync(backupOf(dir))) continue;
      fs.rmSync(dir, { recursive: true, force: true });
      fs.cpSync(backupOf(dir), dir, { recursive: true });
      fs.rmSync(backupOf(dir), { recursive: true, force: true });
    }
  };
  process.on('SIGINT', () => { restoreUploads(); process.exit(130); });

  try {
    log.title(`Préparation (base ${DATABASE})`);
    php(['tests/Browser/prepare.php'], 'création de la base');
    php(['artisan', 'migrate:fresh', '--seed', '--force', '--no-interaction'], 'migrations et données de démo');
    log.ok('base recréée avec les données de démo');
    const counts = JSON.parse(php(['tests/Browser/urls.php'], 'liste des pages'));
    log.ok(`pages à tester : ${counts.agent} agent, ${counts.admin} admin, ${counts.public} publiques`);
    const urls = JSON.parse(fs.readFileSync(path.join(OUTPUT, 'urls.json'), 'utf8'));
    if (urls.missing.length) {
      failures['routes sans valeur de test'] = urls.missing.length;
      log.ko(`routes non testées faute de valeur pour leurs paramètres (à ajouter dans tests/Browser/urls.php) :`);
      urls.missing.forEach((m) => log.info(m));
    }

    const [cmd, serverArgs, o] = phpCommand(['-S', `127.0.0.1:${PORT}`, '-t', 'public', 'server.php']);
    server = spawn(cmd, serverArgs, { ...o, cwd: ROOT, env, stdio: 'ignore' });
    for (let i = 0; i < 60; i++) {
      if (await fetch(`${BASE}/connexion`).then((r) => r.ok).catch(() => false)) break;
      await new Promise((r) => setTimeout(r, 500));
      if (i === 59) throw new Error(`le serveur de test ne répond pas sur ${BASE}`);
    }
    log.ok(`serveur de test démarré sur ${BASE}`);

    // Étapes à lancer : toutes par défaut, ou --seulement=pages,liens,formulaires,scenarios
    const only = args.find((a) => a.startsWith('--seulement='))?.split('=')[1].split(',');
    const wanted = (step) => (only ? only.includes(step) : !(option('--sans-formulaires') && ['formulaires', 'scenarios'].includes(step)));
    if (wanted('pages')) {
      const { checkPages } = await import('./pages.mjs');
      log.title(option('--captures') ? 'Pages (avec captures)' : 'Pages');
      failures.pages = await checkPages({ captures: option('--captures') });
    }
    if (wanted('liens')) {
      const { crawl } = await import('./crawl.mjs');
      log.title('Exploration des liens (4 comptes)');
      failures.liens = await crawl();
    }
    if (wanted('scenarios')) {
      const { runScenarios } = await import('./scenarios.mjs');
      log.title('Scénarios guidés');
      failures['scénarios'] = await runScenarios();
    }
    // Les formulaires passent en dernier : leurs suppressions retirent des données de démo
    if (wanted('formulaires')) {
      const { checkForms } = await import('./forms.mjs');
      log.title('Formulaires');
      failures.formulaires = await checkForms({ destructive: true });
    }
  } catch (e) {
    failures.lancement = 1;
    log.ko(e.message);
  } finally {
    if (server && !option('--garder-serveur')) {
      if (WINDOWS) spawnSync('taskkill', ['/pid', String(server.pid), '/T', '/F'], { stdio: 'ignore' });
      else server.kill();
    }
    restoreUploads();
  }

  if (option('--phpunit')) {
    log.title('PHPUnit');
    const r = runPhp(['vendor/bin/phpunit'], { cwd: ROOT, stdio: 'inherit' });
    failures.phpunit = r.status ? 1 : 0;
  }

  const total = Object.values(failures).reduce((a, b) => a + b, 0);
  const minutes = ((Date.now() - started) / 60000).toFixed(1);
  log.title('Bilan');
  for (const [name, count] of Object.entries(failures)) (count ? log.ko : log.ok)(`${name} : ${count ? count + ' erreur(s)' : 'OK'}`);
  console.log(total ? `\n\x1b[31m${total} erreur(s)\x1b[0m — détail dans tests/Browser/output/ (${minutes} min)` : `\n\x1b[32mTout est vert\x1b[0m (${minutes} min)`);
  if (option('--captures')) console.log(`Captures : ${path.join(OUTPUT, 'captures')}`);
  if (option('--garder-serveur')) console.log(`Serveur de test toujours allumé sur ${BASE} (pid ${server?.pid})`);
  process.exit(total ? 1 : 0);
}

main();
