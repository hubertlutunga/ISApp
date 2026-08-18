# Runbook restauration d'urgence (ISApp)

## 1. Quand utiliser ce runbook
- Site inaccessible, corruption DB, suppression de donnees, ransomware, panne serveur.

## 2. Prerequis
- Acces SSH/cPanel au serveur.
- Acces MySQL admin.
- Dernier backup SQL local: storage/backups/sql
- Copie hors serveur (local securise + cloud via rclone).

## 3. Contacts critiques
- Responsable technique ISApp: A REMPLACER (nom, tel, email)
- Responsable produit: A REMPLACER (nom, tel, email)
- Namecheap support: https://www.namecheap.com/support/
- Namecheap Live Chat: https://www.namecheap.com/help-center/live-chat/
- Namecheap statut incidents: https://www.namecheap.com/status-updates/

## 4. Procedure express (RTO court)
1. Mettre le site en maintenance (page statique ou regle .htaccess).
2. Identifier le backup le plus recent valide.
3. Creer un snapshot de l'etat actuel avant restauration (forensic).
4. Restaurer la base sur une base temporaire de test.
5. Verifier integrite (tables critiques, comptes, factures, logs WhatsApp).
6. Restaurer en production apres validation.
7. Purger cache, redemarrer services PHP/web si necessaire.
8. Reouvrir progressivement l'acces public.
9. Lancer une verification fonctionnelle complete.
10. Emettre un compte-rendu incident (timeline + actions preventives).

## 5. Commandes de restauration MySQL

### 5.1 Sauvegarde de securite avant restauration
```bash
mysqldump --host=127.0.0.1 --user=ROOT_USER --password='ROOT_PASS' --databases invizfxg_is > pre_restore_$(date +%Y%m%d_%H%M%S).sql
```

### 5.2 Restauration depuis un .sql.gz
```bash
gunzip -c /path/to/isapp_full_YYYYMMDD_HHMMSS.sql.gz | \
mysql --host=127.0.0.1 --user=ROOT_USER --password='ROOT_PASS'
```

### 5.3 Restauration depuis un .sql
```bash
mysql --host=127.0.0.1 --user=ROOT_USER --password='ROOT_PASS' < /path/to/isapp_full_YYYYMMDD_HHMMSS.sql
```

## 6. Validation post-restauration
- Connexion admin OK.
- Liste clients/events charge correctement.
- Envoi WhatsApp test sur environnement controle.
- Integrite de 5 echantillons factures/commandes.
- Aucun message d'erreur PHP/SQL dans les logs.

## 7. Prevention et hygiene
- Backup SQL quotidien local + retention 14 jours minimum.
- Copie hors serveur hebdomadaire minimum (local securise + cloud).
- Test de restauration mensuel automatique (base temporaire + rapport dans storage/backups/reports).
- Rotation des credentials DB et cloud.
