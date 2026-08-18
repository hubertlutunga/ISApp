#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_DIR="${ISAPP_BACKUP_DIR:-$PROJECT_ROOT/storage/backups/sql}"
DB_HOST="${ISAPP_DB_HOST:-127.0.0.1}"
DB_NAME="${ISAPP_DB_NAME:-invizfxg_is}"
DB_USER="${ISAPP_DB_USER:-root}"
DB_PASSWORD="${ISAPP_DB_PASSWORD:-Root_2023}"
RETENTION_DAYS="${ISAPP_BACKUP_RETENTION_DAYS:-14}"

mkdir -p "$BACKUP_DIR"
TS="$(date +%Y%m%d_%H%M%S)"
OUT_SQL="$BACKUP_DIR/isapp_full_${TS}.sql"
OUT_GZ="${OUT_SQL}.gz"

mysqldump \
  --host="$DB_HOST" \
  --user="$DB_USER" \
  --password="$DB_PASSWORD" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --events \
  --databases "$DB_NAME" > "$OUT_SQL"

gzip -f "$OUT_SQL"

find "$BACKUP_DIR" -type f -name "isapp_full_*.sql.gz" -mtime +"$RETENTION_DAYS" -delete

SIZE="$(du -h "$OUT_GZ" | awk '{print $1}')"
echo "Backup SQL termine: $OUT_GZ ($SIZE)"
