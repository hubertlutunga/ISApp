#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_DIR="${ISAPP_BACKUP_DIR:-$PROJECT_ROOT/storage/backups/sql}"
REPORT_DIR="${ISAPP_BACKUP_REPORT_DIR:-$PROJECT_ROOT/storage/backups/reports}"
DB_HOST="${ISAPP_DB_HOST:-127.0.0.1}"
DB_NAME="${ISAPP_DB_NAME:-invizfxg_is}"
DB_USER="${ISAPP_DB_USER:-root}"
DB_PASSWORD="${ISAPP_DB_PASSWORD:-Root_2023}"
KEEP_TEMP_DB="${ISAPP_KEEP_RESTORE_TEST_DB:-0}"
TMP_SQL_FILE=""

mkdir -p "$REPORT_DIR"
START_TS="$(date +%Y%m%d_%H%M%S)"
REPORT_FILE="$REPORT_DIR/restore_test_${START_TS}.md"
LATEST_FILE="$(ls -1t "$BACKUP_DIR"/isapp_full_*.sql* 2>/dev/null | head -n 1 || true)"

if [[ -z "$LATEST_FILE" ]]; then
  echo "Aucun backup SQL trouve dans $BACKUP_DIR" >&2
  exit 1
fi

TEMP_DB="${DB_NAME}_restore_test_${START_TS}"
START_EPOCH="$(date +%s)"
STATUS="SUCCESS"
ERROR_MSG=""
TABLE_COUNT="0"
USERS_COUNT="N/A"
EVENTS_COUNT="N/A"
INVITES_COUNT="N/A"
WHATSAPP_LOGS_COUNT="N/A"

cleanup_temp_db() {
  if [[ -n "$TMP_SQL_FILE" && -f "$TMP_SQL_FILE" ]]; then
    rm -f "$TMP_SQL_FILE" || true
  fi

  if [[ "$KEEP_TEMP_DB" != "1" ]]; then
    mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -e "DROP DATABASE IF EXISTS \`$TEMP_DB\`;" >/dev/null 2>&1 || true
  fi
}

trap cleanup_temp_db EXIT

if ! mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -e "CREATE DATABASE \`$TEMP_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"; then
  STATUS="FAILED"
  ERROR_MSG="Impossible de creer la base temporaire $TEMP_DB"
fi

if [[ "$STATUS" == "SUCCESS" ]]; then
  TMP_SQL_FILE="$(mktemp -t isapp_restore_test_XXXXXX.sql)"

  if [[ "$LATEST_FILE" == *.gz ]]; then
    if ! gunzip -c "$LATEST_FILE" > "$TMP_SQL_FILE"; then
      STATUS="FAILED"
      ERROR_MSG="Extraction gzip echouee: $LATEST_FILE"
    fi
  else
    if ! cp "$LATEST_FILE" "$TMP_SQL_FILE"; then
      STATUS="FAILED"
      ERROR_MSG="Copie SQL echouee: $LATEST_FILE"
    fi
  fi

  if [[ "$STATUS" == "SUCCESS" ]]; then
    if ! sed -i '' "s/$DB_NAME/$TEMP_DB/g" "$TMP_SQL_FILE"; then
      STATUS="FAILED"
      ERROR_MSG="Remappage nom de base echoue (source=$DB_NAME, cible=$TEMP_DB)."
    fi
  fi

  if [[ "$STATUS" == "SUCCESS" ]]; then
    if ! mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" < "$TMP_SQL_FILE"; then
      STATUS="FAILED"
      ERROR_MSG="Import SQL remappe echoue: $LATEST_FILE"
    fi
  fi
fi

if [[ "$STATUS" == "SUCCESS" ]]; then
  TABLE_COUNT="$(mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TEMP_DB';" 2>/dev/null || echo 0)"

  USERS_COUNT="$(mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -N -e "SELECT IFNULL((SELECT COUNT(*) FROM \`$TEMP_DB\`.is_users), 'N/A');" 2>/dev/null || echo N/A)"
  EVENTS_COUNT="$(mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -N -e "SELECT IFNULL((SELECT COUNT(*) FROM \`$TEMP_DB\`.events), 'N/A');" 2>/dev/null || echo N/A)"
  INVITES_COUNT="$(mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -N -e "SELECT IFNULL((SELECT COUNT(*) FROM \`$TEMP_DB\`.invite), 'N/A');" 2>/dev/null || echo N/A)"
  WHATSAPP_LOGS_COUNT="$(mysql --host="$DB_HOST" --user="$DB_USER" --password="$DB_PASSWORD" -N -e "SELECT IFNULL((SELECT COUNT(*) FROM \`$TEMP_DB\`.whatsapp_message_logs), 'N/A');" 2>/dev/null || echo N/A)"

  if [[ "$TABLE_COUNT" -lt 5 ]]; then
    STATUS="FAILED"
    ERROR_MSG="Verification KO: nombre de tables trop faible ($TABLE_COUNT)."
  fi
fi

END_EPOCH="$(date +%s)"
DURATION="$((END_EPOCH - START_EPOCH))"

{
  echo "# Rapport test de restauration mensuel"
  echo
  echo "- Date: $(date '+%Y-%m-%d %H:%M:%S')"
  echo "- Source backup: $LATEST_FILE"
  echo "- Base temporaire: $TEMP_DB"
  echo "- Duree: ${DURATION}s"
  echo "- Statut: $STATUS"
  if [[ -n "$ERROR_MSG" ]]; then
    echo "- Erreur: $ERROR_MSG"
  fi
  echo
  echo "## Verifications"
  echo "- Nombre de tables: $TABLE_COUNT"
  echo "- is_users: $USERS_COUNT"
  echo "- events: $EVENTS_COUNT"
  echo "- invite: $INVITES_COUNT"
  echo "- whatsapp_message_logs: $WHATSAPP_LOGS_COUNT"
  echo
  if [[ "$KEEP_TEMP_DB" == "1" ]]; then
    echo "- Note: base temporaire conservee (ISAPP_KEEP_RESTORE_TEST_DB=1)."
  else
    echo "- Note: base temporaire supprimee automatiquement."
  fi
} > "$REPORT_FILE"

if [[ "$STATUS" == "FAILED" ]]; then
  echo "Test restauration mensuel: ECHEC. Voir $REPORT_FILE" >&2
  exit 1
fi

echo "Test restauration mensuel: OK. Rapport: $REPORT_FILE"
