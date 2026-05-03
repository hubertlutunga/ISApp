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

## Configuration Twilio

Les identifiants Twilio ne doivent jamais etre stockes en dur dans le code PHP. Configurez-les uniquement via des variables d'environnement sur le serveur.

En local, le projet charge automatiquement les fichiers prives `.env` et `.env.local` situes a la racine du depot s'ils existent. Les variables deja definies par le serveur gardent la priorite.

En production, si le projet est deploie dans un dossier web public de type `public_html`, l'application charge aussi un fichier prive dedie place un niveau au-dessus du webroot: `../.isapp.env` puis `../.isapp.env.local`. Cela permet de garder les secrets hors du dossier publiquement servi.

Exemple minimal de fichier `.env` local:

```dotenv
TWILIO_ACCOUNT_SID=AC5cbb94f85695ce16d97ce2ca2c3f7db0
TWILIO_AUTH_TOKEN=REMPLACER_MANUELLEMENT
TWILIO_WHATSAPP_FROM=whatsapp:+17167403177
TWILIO_WHATSAPP_TEMPLATE_SID=REMPLACER_PAR_LE_TEMPLATE_ACTUEL
```

Le fichier `.env` est ignore par Git et bloque cote Apache via `.htaccess`, pour eviter toute exposition publique accidentelle. En production, privilegiez toujours des variables d'environnement definies au niveau du serveur.

Exemple de structure serveur recommandee:

```text
home/
	.isapp.env
	public_html/
		index.php
		bootstrap/
		config/
		...
```

Dans cette configuration, placez les variables Twilio dans `.isapp.env`, pas dans `public_html/.env`.

Variables requises pour l'envoi WhatsApp:

- `TWILIO_ACCOUNT_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_WHATSAPP_FROM`
- `TWILIO_WHATSAPP_TEMPLATE_SID`

Variable optionnelle:

- `ISAPP_PUBLIC_BASE_URL`

Les anciennes pages d'essai Twilio du projet ont ete desactivees pour eviter toute exposition publique ou reutilisation accidentelle.