#!/bin/bash

# ==== Konfiguration ====
set -o allexport
if [ -f .env_local ]; then
  source .env_local
else
  source .env
fi
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
GTAR_BIN=$(command -v gtar || command -v tar)

COPYFILE_DISABLE=1 "$GTAR_BIN" \
  --format=ustar \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=storage/logs \
  --exclude=".DS_Store" \
  --exclude=.git \
  --exclude="._*" \
  --exclude=".env" \
  --exclude=".htaccess" \
  --exclude="public/.htaccess" \
  -czf "$ARCHIVE_FULL" -C "$PROJECT_DIR" .

# ==== Hochladen ====
echo "📤 Übertrage nach $SSH_SERVER..."
scp -P "$SSH_PORT" "$ARCHIVE_PATH/$TARFILE" "$SSH_USER@$SSH_SERVER:/tmp/"

# ==== Remote entpacken ====
ssh -p "$SSH_PORT" "$SSH_USER@$SSH_SERVER" "
    cd /tmp &&
    mkdir -p $REMOTE_PATH/.backup &&
    cp $REMOTE_PATH/.env $REMOTE_PATH/.backup/.env 2>/dev/null || true
    cp $REMOTE_PATH/.htaccess $REMOTE_PATH/.backup/.htaccess 2>/dev/null || true
    cp $REMOTE_PATH/public/.htaccess $REMOTE_PATH/.backup/public_htaccess 2>/dev/null || true

    tar -xzf update.tar.gz -C $REMOTE_PATH --strip-components=1 &&
    rm update.tar.gz

    mv $REMOTE_PATH/.backup/.env $REMOTE_PATH/.env 2>/dev/null || true
    mv $REMOTE_PATH/.backup/.htaccess $REMOTE_PATH/.htaccess 2>/dev/null || true
    mv $REMOTE_PATH/.backup/public_htaccess $REMOTE_PATH/public/.htaccess 2>/dev/null || true
    rm -rf $REMOTE_PATH/.backup

    chmod +x $REMOTE_PATH/remote-setup.sh &&
    $REMOTE_PATH/remote-setup.sh
"

echo "✅ Fertig! Projekt wurde erfolgreich aktualisiert."