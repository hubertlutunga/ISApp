# Politique backup ISApp

## Sauvegarde manuelle immediate (realisee)
- Fichier cree: storage/backups/sql/isapp_full_20260818_174455.sql

## Automatisation mise en place
- Script backup SQL: scripts/backup/mysql_full_backup.sh
- Script copie hors serveur: scripts/backup/offsite_push.sh
- Script setup remote chiffre rclone: scripts/backup/setup_rclone_crypt_remote.sh
- Script test restauration mensuel: scripts/backup/monthly_restore_test.sh
- Installation cron: scripts/backup/install_backup_cron.sh

## Rappels quotidiens/hebdo (cron)
- Quotidien 01:30: backup SQL local (gzip + retention)
- Quotidien 01:45: copie hors serveur locale + cloud
- Hebdo lundi 09:00: rappel de verification restauration et integrite backup
- Mensuel le 1er a 02:15: test restauration sur base temporaire + rapport

## Installation
```bash
chmod +x scripts/backup/*.sh
./scripts/backup/install_backup_cron.sh
```

## Variables utiles
- ISAPP_DB_HOST, ISAPP_DB_NAME, ISAPP_DB_USER, ISAPP_DB_PASSWORD
- ISAPP_BACKUP_DIR
- ISAPP_BACKUP_RETENTION_DAYS
- ISAPP_LOCAL_OFFSITE_DIR
- ISAPP_RCLONE_REMOTE_PATH (ex: remote:bucket/isapp)
- ISAPP_RCLONE_BASE_REMOTE_PATH (ex: b2:my-bucket/isapp-backups)
- ISAPP_RCLONE_CRYPT_REMOTE_NAME (defaut: isapp_crypt)
- ISAPP_RCLONE_CRYPT_PASSWORD
- ISAPP_RCLONE_CRYPT_PASSWORD2
- ISAPP_BACKUP_REPORT_DIR
- ISAPP_KEEP_RESTORE_TEST_DB (1 pour conserver la base temporaire)

## Activation cloud chiffre (rclone)
1. Installer rclone.
2. Configurer un remote cloud de base (B2/S3/Drive...) via rclone.
3. Exporter les variables ci-dessus, puis executer:
```bash
chmod +x scripts/backup/*.sh
./scripts/backup/setup_rclone_crypt_remote.sh
```
4. Definir ISAPP_RCLONE_REMOTE_PATH selon la sortie (ex: isapp_crypt:mysql).
5. Tester:
```bash
./scripts/backup/offsite_push.sh
```

Note: le script offsite_push refuse tout remote non chiffre (type crypt).

## Rapport mensuel restauration
- Rapport genere dans storage/backups/reports/restore_test_YYYYMMDD_HHMMSS.md
- En cas d'echec, le cron journalise dans storage/backups/logs/monthly_restore_test.log

## Conseils cloud
- Utiliser rclone avec un remote chiffre.
- Activer la versioning policy cote cloud (S3/B2/Drive).
- Tester une restauration complete au moins 1 fois/mois.
