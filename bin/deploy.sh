#!/bin/bash
# Deploy PushpaSeva theme code to this VPS.
# Run this ON THE VPS (not locally). Safe to re-run.
set -e

CODE_DIR="/opt/pushpaseva-code"
WP_DIR="/var/www/pushpaseva"
THEME_LINK="${WP_DIR}/wp-content/themes/pushpaseva"

if [ ! -d "$CODE_DIR/.git" ]; then
  echo "Cloning repo into ${CODE_DIR}..."
  git clone https://github.com/Dhananjaiah/Flower-Pro.git "$CODE_DIR"
else
  echo "Pulling latest into ${CODE_DIR}..."
  git -C "$CODE_DIR" pull origin main
fi

if [ -L "$THEME_LINK" ] || [ -e "$THEME_LINK" ]; then
  if [ ! -L "$THEME_LINK" ]; then
    echo "Removing non-symlink placeholder at ${THEME_LINK}"
    rm -rf "$THEME_LINK"
    ln -s "${CODE_DIR}/wp-content/themes/pushpaseva" "$THEME_LINK"
  fi
else
  echo "Linking theme into WordPress install..."
  ln -s "${CODE_DIR}/wp-content/themes/pushpaseva" "$THEME_LINK"
fi

chown -R www-data:www-data "$CODE_DIR/wp-content/themes/pushpaseva"

cd "$WP_DIR"
wp theme activate pushpaseva --allow-root
wp cache flush --allow-root || true
wp rewrite flush --allow-root

echo "Deploy complete."
