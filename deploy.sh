#!/bin/bash

# ==== Konfiguration ====
set -o allexport
source .env
set +o allexport

APP_NAME="markt-app"
ARCHIVE_PATH="/tmp"
TARFILE="update.tar.gz"
REMOTE_PATH="/usr/home/$SSH_USER/public_html/markt.fuerstenfeld.de"

# ==== Pfade ====
PROJECT_DIR=$(pwd)
ARCHIVE_FULL="$ARCHIVE_PATH/$TARFILE"

# ==== Build ====
echo "🛠 Baue Assets lokal..."
npm ci
npm run build

# ==== Archiv erstellen ====
echo "📦 Erstelle $TARFILE..."
COPYFILE_DISABLE=1 gtar \
  --format=ustar \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=storage/logs \
  --exclude=".DS_Store" \
  --exclude=.git \
  --exclude="._*" \
  -czf "$ARCHIVE_FULL" -C "$PROJECT_DIR" .

# ==== Hochladen ====
echo "📤 Übertrage nach $SSH_SERVER..."
scp -P "$SSH_PORT" "$ARCHIVE_PATH/$TARFILE" "$SSH_USER@$SSH_SERVER:/tmp/"

# ==== Remote entpacken ====
echo "🚀 Aktualisiere remote..."
ssh -p "$SSH_PORT" "$SSH_USER@$SSH_SERVER" "
    cd /tmp &&
    rm -rf $REMOTE_PATH.old &&
    mv $REMOTE_PATH $REMOTE_PATH.old || true &&
    mkdir -p $REMOTE_PATH &&
    tar -xzf $TARFILE -C $REMOTE_PATH --strip-components=1 &&
    rm $TARFILE
    rm -rf $REMOTE_PATH.old
"

echo "✅ Fertig! Projekt wurde erfolgreich aktualisiert."