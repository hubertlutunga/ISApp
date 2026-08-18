#!/usr/bin/env bash
set -euo pipefail

if ! command -v rclone >/dev/null 2>&1; then
  echo "rclone n'est pas installe."
  exit 1
fi

BASE_REMOTE_PATH="${ISAPP_RCLONE_BASE_REMOTE_PATH:-}"
CRYPT_REMOTE_NAME="${ISAPP_RCLONE_CRYPT_REMOTE_NAME:-isapp_crypt}"
CRYPT_PASSWORD="${ISAPP_RCLONE_CRYPT_PASSWORD:-}"
CRYPT_PASSWORD2="${ISAPP_RCLONE_CRYPT_PASSWORD2:-}"
CRYPT_SUBPATH="${ISAPP_RCLONE_CRYPT_SUBPATH:-mysql}"

if [[ -z "$BASE_REMOTE_PATH" ]]; then
  echo "ISAPP_RCLONE_BASE_REMOTE_PATH manquant (ex: b2:my-bucket/isapp-backups)."
  exit 1
fi

if [[ -z "$CRYPT_PASSWORD" || -z "$CRYPT_PASSWORD2" ]]; then
  echo "ISAPP_RCLONE_CRYPT_PASSWORD et ISAPP_RCLONE_CRYPT_PASSWORD2 sont obligatoires."
  exit 1
fi

OBSCURED_PASS="$(rclone obscure "$CRYPT_PASSWORD")"
OBSCURED_PASS2="$(rclone obscure "$CRYPT_PASSWORD2")"

rclone config create "$CRYPT_REMOTE_NAME" crypt \
  remote "$BASE_REMOTE_PATH" \
  filename_encryption standard \
  directory_name_encryption true \
  password "$OBSCURED_PASS" \
  password2 "$OBSCURED_PASS2" >/dev/null

echo "Remote chiffre configure: ${CRYPT_REMOTE_NAME}:"

echo "Verification acces..."
rclone lsd "${CRYPT_REMOTE_NAME}:" >/dev/null || true

echo "Definir ensuite la variable suivante:"
echo "ISAPP_RCLONE_REMOTE_PATH=${CRYPT_REMOTE_NAME}:${CRYPT_SUBPATH}"
