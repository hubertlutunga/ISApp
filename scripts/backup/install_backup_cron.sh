#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_SCRIPT="$PROJECT_ROOT/scripts/backup/mysql_full_backup.sh"
OFFSITE_SCRIPT="$PROJECT_ROOT/scripts/backup/offsite_push.sh"
MONTHLY_RESTORE_SCRIPT="$PROJECT_ROOT/scripts/backup/monthly_restore_test.sh"
LOG_DIR="$PROJECT_ROOT/storage/backups/logs"

mkdir -p "$LOG_DIR"

CURRENT_CRON="$(crontab -l 2>/dev/null || true)"
FILTERED_CRON="$(printf '%s\n' "$CURRENT_CRON" | sed '/# ISAPP_BACKUP_DAILY/d;/# ISAPP_BACKUP_WEEKLY/d;/# ISAPP_BACKUP_MONTHLY/d;/mysql_full_backup\.sh/d;/offsite_push\.sh/d;/monthly_restore_test\.sh/d')"

DAILY_JOB="30 1 * * * $BACKUP_SCRIPT >> $LOG_DIR/daily_backup.log 2>&1 # ISAPP_BACKUP_DAILY"
DAILY_OFFSITE_JOB="45 1 * * * $OFFSITE_SCRIPT >> $LOG_DIR/daily_offsite.log 2>&1 # ISAPP_BACKUP_DAILY"
WEEKLY_JOB="0 9 * * 1 echo '[ISAPP] Rappel hebdo: verifier restauration + integrite backup offsite' >> $LOG_DIR/weekly_reminder.log 2>&1 # ISAPP_BACKUP_WEEKLY"
MONTHLY_JOB="15 2 1 * * $MONTHLY_RESTORE_SCRIPT >> $LOG_DIR/monthly_restore_test.log 2>&1 # ISAPP_BACKUP_MONTHLY"

NEW_CRON="$(printf '%s\n%s\n%s\n%s\n%s\n' "$FILTERED_CRON" "$DAILY_JOB" "$DAILY_OFFSITE_JOB" "$WEEKLY_JOB" "$MONTHLY_JOB" | awk 'NF')"
printf '%s\n' "$NEW_CRON" | crontab -

echo "Cron backup installe:"
echo "- Quotidien 01:30 (backup SQL local)"
echo "- Quotidien 01:45 (copie hors serveur locale + cloud via rclone)"
echo "- Hebdo lundi 09:00 (rappel de verification restauration/backup)"
echo "- Mensuel le 1er a 02:15 (test de restauration sur base temporaire + rapport)"
