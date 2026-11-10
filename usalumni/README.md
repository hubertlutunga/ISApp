# U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity

Plateforme complète de candidatures, back-office, notifications e-mail, QR codes et contrôle d’accès pour l’événement **U.S. Alumni Impact 2026 – Promoting Partnership and Prosperity**.

## URLs

- Site public: `/usalumni/`
- Confirmation: `/usalumni/confirmation/`
- Back-office: `/usalumni/admin/`
- Scan/contrôle d’accès: `/usalumni/admin/scan/`
- Cible QR publique sans login: `/usalumni/presence_cible.php?id={token}`

## Prérequis

- PHP 8+
- MySQL / MariaDB
- PDO MySQL
- Accès aux bibliothèques existantes du dépôt:
  - `config/database.php`
  - `PHPMailer/src/*`
  - `phpqrcode/qrlib.php` (ou `qrscan/phpqrcode/qrlib.php`)
- Optionnel PDF: `event/pages/tcpdf/tcpdf.php`

## Déploiement (non destructif)

1. Configurer les variables d’environnement (voir `.env.example`) dans l’un des fichiers lus au runtime (`/isapp.env`, `/.isapp.env`, `/.env` à la racine projet, ou `usalumni/.env`).
2. Sauvegarder la base de données.
3. Exécuter la migration SQL:
   - [`001_drcalumni.sql`](./database/migrations/001_drcalumni.sql)
4. Ouvrir `/usalumni/admin/register.php` et créer le premier super administrateur.
5. Se connecter sur `/usalumni/admin/`.
6. Vérifier:
   - logos officiels chargés,
   - création candidature,
   - traitement outbox e-mail,
   - sélection/rejet,
   - scan QR jour 1 et jour 2.

## Fonctionnalités principales

- Formulaire wizard 6 étapes + récapitulatif + validations client/serveur.
- 20 questions officielles, champs conditionnels, compteurs de mots.
- Anti CSRF, anti double soumission, unicité e-mail.
- Back-office avec:
  - authentification et rôles (`super_admin`, `reviewer`, `checkin_operator`),
  - dashboard, filtres, gestion des candidatures,
  - actions unitaires et par lot,
  - capacité de sélection configurable,
  - file d’envoi e-mail (outbox + reprises),
  - exports CSV et PDF.
- E-mails:
  - accusé de réception configurable,
  - texte officiel de sélection,
  - texte officiel de non-sélection.
- QR:
  - génération token opaque hashé,
  - scan caméra + saisie manuelle,
  - présence indépendante par jour (1er et 2 décembre).

## Notes de sécurité

- Aucune donnée de `creatorsbomoko` n’est modifiée.
- Tables dédiées préfixées `drcalumni_`.
- Données sensibles d’aménagement visibles côté back-office uniquement.
- Paramètres SMTP et secrets via variables d’environnement.
