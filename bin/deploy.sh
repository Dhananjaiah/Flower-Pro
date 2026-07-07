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

# WooCommerce store settings for India.
wp option update woocommerce_currency "INR" --allow-root
wp option update woocommerce_default_country "IN" --allow-root
wp option update timezone_string "Asia/Kolkata" --allow-root

# Use classic shortcode-based cart/checkout, not the WooCommerce block
# versions — the Apartment Name / Flat Number custom fields (added via
# the classic woocommerce_checkout_fields filter in functions.php) don't
# render on the block-based checkout.
CART_PAGE_ID=$(wp option get woocommerce_cart_page_id --allow-root)
CHECKOUT_PAGE_ID=$(wp option get woocommerce_checkout_page_id --allow-root)
wp post update "$CART_PAGE_ID" --post_content="[woocommerce_cart]" --allow-root
wp post update "$CHECKOUT_PAGE_ID" --post_content="[woocommerce_checkout]" --allow-root

wp cache flush --allow-root || true
wp rewrite flush --allow-root

echo "Deploy complete."
