#!/bin/bash

# ==== Deployment-Auswahl ====
echo "🚀 Wohin soll deployed werden?"
echo "1) Production (markt.fuerstenfeld.de)"
echo "2) Development (marktdev.fuerstenfeld.de)"
echo "3) Beide (Production + Development)"
echo -n "Auswahl (1/2/3): "
read DEPLOY_TARGET

case $DEPLOY_TARGET in
    1)
        DEPLOY_TARGETS=("prod")
        echo "➡️ Deploying to Production..."
        ;;
    2)
        DEPLOY_TARGETS=("dev")
        echo "➡️ Deploying to Development..."
        ;;
    3)
        DEPLOY_TARGETS=("prod" "dev")
        echo "➡️ Deploying to Production and Development..."
        ;;
    *)
        echo "❌ Ungültige Auswahl. Abbruch."
        exit 1
        ;;
esac

# ==== Konfiguration ====
set -o allexport
source .env
set +o allexport

APP_NAME="markt-app"
ARCHIVE_PATH="/tmp"
TARFILE="update.tar.gz"

# ==== Pfade ====
PROJECT_DIR=$(pwd)
ARCHIVE_FULL="$ARCHIVE_PATH/$TARFILE"

# ==== Build ====
echo "🛠 Baue Assets lokal..."
npm ci
npm run build

# ==== Version generieren ====
echo "📌 Generiere Version..."
git describe --tags --always > VERSION
echo "Version: $(cat VERSION)"

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

# ==== Deployment für jedes Target ====
for TARGET in "${DEPLOY_TARGETS[@]}"; do
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [ "$TARGET" == "prod" ]; then
        REMOTE_PATH="/usr/home/$SSH_USER/public_html/markt.fuerstenfeld.de"
        echo "📦 Deploying to PRODUCTION..."
    else
        REMOTE_PATH="/usr/home/$SSH_USER/public_html/marktdev.fuerstenfeld.de"
        echo "📦 Deploying to DEVELOPMENT..."
    fi
    
    # ==== Hochladen ====
    echo "📤 Übertrage nach $SSH_SERVER ($TARGET)..."
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
    
    echo "✅ $TARGET deployment abgeschlossen!"
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Alle Deployments erfolgreich abgeschlossen!"