# Tests navigateur

Vérifient l'application telle qu'un utilisateur la voit, dans un vrai Chromium, après chaque modification.
Ils complètent les tests PHPUnit (`tests/Feature`), qui vérifient le comportement côté serveur.

## Lancer

Une seule fois :

```bash
cd tests/Browser
npm install
npx playwright install chromium
```

Ensuite, MySQL allumé (`docker compose up -d`) :

```bash
cd tests/Browser
npm test                      # tout : pages, liens, scénarios, formulaires (~25 min)
npm run test:rapide           # pages et liens seulement (~12 min)
npm run test:captures         # tout + une capture d'écran de chaque page
node run.mjs --phpunit        # tout + les tests PHPUnit à la fin
node run.mjs --seulement=scenarios        # une ou plusieurs étapes : pages, liens, formulaires, scenarios
node run.mjs --garder-serveur # laisse le serveur de test allumé pour regarder une erreur
```

Le bilan s'affiche à la fin : **« Tout est vert »**, ou la liste des erreurs. Le code de sortie vaut 1 en cas
d'erreur, donc la commande peut servir dans une intégration continue.

## Ce qui est testé

| Étape | Fichier | Ce qu'elle vérifie |
|---|---|---|
| Pages | `pages.mjs` | Chaque route GET des espaces admin, agent et public, paramètres remplis avec de vraies données de démo : code HTTP, erreurs JavaScript, requêtes AJAX en erreur, images ou scripts introuvables |
| Liens | `crawl.mjs` | Avec chacun des 4 comptes (admin, super agent, agent, partenaire), suit tous les liens affichés : un lien visible qui mène à une erreur (403, 404, 500) est signalé. Vérifie donc aussi les droits |
| Formulaires | `forms.mjs` | Chaque formulaire de chaque page est rempli et envoyé avec le vrai bouton : pas d'erreur 500, pas de route absente (404/405), pas de jeton expiré (419), pas d'erreur JavaScript. Les suppressions et annulations passent en dernier |
| Scénarios | `scenarios.mjs` | Parcours guidés pour les formulaires à étapes JavaScript (modifier un passager d'une réservation ou à l'émission, créer un devis) : fait les mêmes gestes qu'un agent et vérifie la réponse |

Les nouvelles pages sont testées automatiquement : la liste vient des routes (`urls.php`). Si une nouvelle route
a un paramètre inconnu (`{facture}` par exemple), le bilan le signale : ajoutez sa valeur dans `$byParam`
de `urls.php`.

Quelques formulaires ne peuvent pas être envoyés automatiquement parce qu'ils demandent des étapes
JavaScript (liste dépendante, champs à déverrouiller). Ils sont listés à la fin de l'étape « Formulaires »
sans compter comme des erreurs, et les plus importants ont un parcours guidé dans `scenarios.mjs` (un scénario
de plus = une entrée dans la liste `SCENARIOS`). Toutes leurs routes sont aussi couvertes par PHPUnit.

## Sans risque pour vos données

- Les tests tournent sur une base à part, **`travel_company_e2e`**, recréée avec les données de démo à
  chaque lancement. La base de travail `travel_company` n'est jamais touchée (le script refuse de
  démarrer si la configuration pointe dessus, par exemple avec un `config:cache` actif).
- Serveur de test sur le port **8001** : votre `php artisan serve` sur le port 8000 peut rester allumé.
- Aucun e-mail ni SMS n'est envoyé. Les dossiers des fichiers envoyés (`storage/app/public`, `public/uploads`) sont copiés au
  départ et remis à l'identique à la fin, même si un test échoue ou si vous interrompez avec Ctrl+C.
- Le cache du serveur de test est en mémoire : rien de ce que font les tests ne reste dans le cache de
  votre application.

## Résultats

Tout est écrit dans `tests/Browser/output/` (ignoré par git) :
`urls.json` (pages testées), `pages.json`, `crawl.json`, `forms.json` (détail de chaque envoi), `captures/`.

Variables utiles : `PHP` (chemin de PHP s'il n'est pas dans le PATH), `E2E_URL` (adresse du serveur de test,
`http://127.0.0.1:8001` par défaut), `E2E_DATABASE`.
