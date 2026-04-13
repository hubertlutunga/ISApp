# ISApp

ISApp est une application PHP de gestion d'evenements et d'invitations. Le depot regroupe plusieurs parcours metier autour des invitations, de la confirmation des presences, de la configuration d'evenements et de la personnalisation des espaces associes.

## Apercu

- Back office et parcours utilisateurs PHP classiques.
- Modules distincts pour les evenements, menus, couples et sites associes.
- Assets front, bibliotheques tierces et services partages conserves dans le depot.

## Structure du depot

```text
bootstrap/   Initialisation applicative et chargement des services
config/      Configuration du projet
docs/        Documentation et notes de travail
event/       Gestion des evenements, invitations, confirmations et exports
images/      Ressources graphiques partagees
js/          Scripts front globaux
menu/        Parcours et pages lies aux menus
pages/       Pages et composants PHP communs
site/        Variantes ou sous-sites lies aux evenements
src/         Classes et logique metier partagee
sweet/       Ressources UI et dependances associees
couple/      Parcours et pages lies aux couples
qrscan/      Fonctionnalites de scan QR
PHPMailer/   Bibliotheque d'envoi d'e-mails
twilio-php-main/ SDK Twilio embarque
```

## Conventions Git retenues

- Les archives source et exports lourds ne sont plus versionnes.
- Les videos generees localement ne sont plus suivies par Git.
- Les assets applicatifs reellement utilises restent dans le depot.

## Fichiers exclus du depot

Le fichier [.gitignore](.gitignore) exclut desormais notamment :

- les archives telles que `.zip`, `.7z`, `.tar`, `.gz`, `.rar`
- les videos et exports media tels que `.mp4`, `.mov`, `.avi`, `.mkv`, `.webm`
- quelques fichiers temporaires usuels

## Publication GitHub

Le depot est prepare pour un usage GitHub simple :

- README racine pour presenter le projet
- structure racine documentee
- suppression des gros binaires non essentiels du suivi Git

## Notes

Certaines ressources lourdes encore presentes dans le projet peuvent rester necessaires a l'application, par exemple certaines images de fond. Si vous souhaitez alleger davantage le depot, la prochaine etape coherente est de deplacer les medias indispensables vers un stockage externe ou Git LFS.