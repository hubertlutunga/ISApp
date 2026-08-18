#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
BACKUP_DIR="${ISAPP_BACKUP_DIR:-$PROJECT_ROOT/storage/backups/sql}"
LOCAL_OFFSITE_DIR="${ISAPP_LOCAL_OFFSITE_DIR:-$HOME/ISApp-backups}"
RCLONE_REMOTE_PATH="${ISAPP_RCLONE_REMOTE_PATH:-}"

LATEST_FILE="$(ls -1t "$BACKUP_DIR"/isapp_full_*.sql.gz 2>/dev/null | head -n 1 || true)"
if [[ -z "$LATEST_FILE" ]]; then
  echo "Aucun backup .sql.gz trouve dans $BACKUP_DIR"
  exit 1
fi

mkdir -p "$LOCAL_OFFSITE_DIR"
cp -f "$LATEST_FILE" "$LOCAL_OFFSITE_DIR/"
echo "Copie locale hors serveur OK: $LOCAL_OFFSITE_DIR/$(basename "$LATEST_FILE")"

if [[ -n "$RCLONE_REMOTE_PATH" ]]; then
  if ! command -v rclone >/dev/null 2>&1; then
    echo "rclone est requis pour l'envoi cloud mais n'est pas installe."
    exit 1
  fi

  REMOTE_NAME="${RCLONE_REMOTE_PATH%%:*}"
  if [[ -z "$REMOTE_NAME" || "$REMOTE_NAME" == "$RCLONE_REMOTE_PATH" ]]; then
    echo "ISAPP_RCLONE_REMOTE_PATH invalide. Format attendu: remote:path"
    exit 1
  fi

  if ! rclone config show "$REMOTE_NAME" 2>/dev/null | grep -q '^type = crypt$'; then
    echo "Le remote rclone '$REMOTE_NAME' n'est pas de type crypt. Upload refuse pour securite."
    exit 1
  fi

  rclone copy "$LATEST_FILE" "$RCLONE_REMOTE_PATH" --progress
  echo "Upload cloud OK: $RCLONE_REMOTE_PATH"
else
  echo "Upload cloud ignore (ISAPP_RCLONE_REMOTE_PATH non configure)."
fi
