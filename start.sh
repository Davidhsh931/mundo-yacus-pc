#!/bin/sh
set -e

# If Railway mounts the volume at /app/storage/app/public but our app lives at
# /var/www, we need to bridge the two so uploaded files are actually persisted.
VOLUME_PATH="/app/storage/app/public"
APP_STORAGE="/var/www/storage/app/public"

if [ -d "$VOLUME_PATH" ]; then
    echo "[start.sh] Volume detected at $VOLUME_PATH"

    # Ensure the parent directory exists
    mkdir -p "$(dirname "$APP_STORAGE")"

    # If the app storage path is already a symlink pointing elsewhere, remove it
    if [ -L "$APP_STORAGE" ] && [ "$(readlink "$APP_STORAGE")" != "$VOLUME_PATH" ]; then
        echo "[start.sh] Removing stale symlink at $APP_STORAGE"
        rm "$APP_STORAGE"
    fi

    # If it's a real directory (from the image), move any existing files into the
    # volume so they are not lost, then replace it with a symlink.
    if [ -d "$APP_STORAGE" ] && [ ! -L "$APP_STORAGE" ]; then
        echo "[start.sh] Migrating existing files from $APP_STORAGE to $VOLUME_PATH"
        cp -rn "$APP_STORAGE/." "$VOLUME_PATH/" 2>/dev/null || true
        rm -rf "$APP_STORAGE"
    fi

    # Create the symlink: /var/www/storage/app/public -> /app/storage/app/public
    if [ ! -L "$APP_STORAGE" ]; then
        echo "[start.sh] Linking $APP_STORAGE -> $VOLUME_PATH"
        ln -s "$VOLUME_PATH" "$APP_STORAGE"
    fi
fi

# Ensure storage and cache directories exist and are writable
mkdir -p /var/www/storage/framework/{sessions,views,cache} \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# (Re)create the public/storage symlink so /storage/... URLs resolve correctly.
# Remove any stale symlink or directory first.
if [ -L /var/www/public/storage ]; then
    rm /var/www/public/storage
fi

ln -s /var/www/storage/app/public /var/www/public/storage
echo "[start.sh] Symlink created: public/storage -> storage/app/public"

# Start the built-in PHP server
echo "[start.sh] Starting PHP server on 0.0.0.0:8080"
exec php -S 0.0.0.0:8080 -t /var/www/public
