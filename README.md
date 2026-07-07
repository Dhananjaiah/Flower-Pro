# PushpaSeva

Weekly pooja-flower subscription site, built on WordPress + WooCommerce.
Custom theme (this repo) + WooCommerce + Razorpay for WooCommerce, hosted
on a self-managed Ubuntu VPS behind Caddy.

Live site: https://flowers.ekamops.com

See [prompt.md](prompt.md) for the full project spec and Phase 1/2 scope.

## What's in this repo

- `wp-content/themes/pushpaseva/` — the custom theme (landing page, bilingual
  flower-item fields, checkout customizations). This is the only thing that's
  actually version-controlled; WordPress core, WooCommerce, and other plugins
  are installed directly on the server and are **not** in this repo.
- `bin/seed-products.php` — idempotent WP-CLI script that creates/updates the
  3 starting subscription plans (Basic/Standard/Premium) with bilingual
  (English/Telugu) flower items.
- `bin/deploy.sh` — run **on the VPS** to pull the latest theme code from
  GitHub and activate it. Safe to re-run.

## Architecture

- WordPress + WooCommerce (products = subscription plans; WooCommerce
  handles cart/checkout/orders — no custom backend)
- Razorpay for WooCommerce (official plugin) for payment
- Custom classic PHP theme (`pushpaseva`) — no page builder, no build step
- Server: Ubuntu VPS, Caddy (auto-HTTPS via Let's Encrypt), PHP 8.3-FPM,
  MariaDB, WP-CLI
- Pricing/plan data lives in the WooCommerce database, edited via wp-admin —
  that's the entire "admin portal." No custom admin UI was built.

## Local development

There's no local WordPress environment for this project — the theme is
edited locally as plain PHP/CSS/JS files and deployed straight to the VPS
(same pattern as EkamOps). To work on it:

1. Edit files under `wp-content/themes/pushpaseva/`.
2. Commit and push to `main` on GitHub (`Dhananjaiah/Flower-Pro`).
3. Deploy (see below).

If you want a local preview before deploying, the fastest option is a local
WordPress install (e.g. via WSL + a LAMP stack, or a tool like LocalWP) with
the same theme folder symlinked in — not set up as part of this MVP.

## Deploying to the VPS

The VPS keeps a bare checkout of this repo at `/opt/pushpaseva-code`, and
`wp-content/themes/pushpaseva` inside the live WordPress install
(`/var/www/pushpaseva`) is a symlink into it. To deploy the latest `main`:

```bash
ssh -p 2244 root@163.128.113.19
bash <(curl -fsSL https://raw.githubusercontent.com/Dhananjaiah/Flower-Pro/main/bin/deploy.sh)
```

This pulls the latest code, re-links the theme if needed, activates it,
and flushes caches/rewrite rules.

## Re-seeding / updating the starting plans

The 3 starting plans are created by `bin/seed-products.php`, matched by SKU
(`plan-basic`, `plan-standard`, `plan-premium`) so it's safe to re-run — it
updates existing products instead of duplicating them. Day-to-day price
edits should just happen in wp-admin (Products); re-run this script only if
you want to reset the starting catalog or seed a fresh environment:

```bash
cd /var/www/pushpaseva
wp eval-file /opt/pushpaseva-code/bin/seed-products.php --allow-root
```

## Server details

- Host: see `.env` (gitignored, not in this repo) for VPS SSH credentials
  and the generated WordPress DB/admin credentials.
- WordPress admin: https://flowers.ekamops.com/wp-admin — login with
  `WP_ADMIN_USER` / `WP_ADMIN_PASS` from `.env`.
- Caddy config: `/etc/caddy/Caddyfile` on the VPS (this site's block was
  appended after the existing `ekamops.com` and `scanner.devstackaiops.com`
  blocks — same pattern: PHP-FPM via `php_fastcgi`, automatic HTTPS,
  security headers).
- Database: MariaDB, database `pushpaseva_wp`, dedicated user (not root) —
  credentials in `.env`.

## Razorpay setup (not done yet)

The Razorpay for WooCommerce plugin is installed and active, but not yet
configured with API keys. To finish it:

1. Sign up / log in at razorpay.com, switch to **Test Mode**.
2. Go to Settings → API Keys → Generate Test Key, copy the Key ID and Key
   Secret.
3. In wp-admin: WooCommerce → Settings → Payments → Razorpay → Manage, paste
   in the test Key ID/Secret, and enable the gateway.
4. Test a full checkout using Razorpay's test card numbers before going live.
5. Once ready to accept real payments, switch Razorpay to Live Mode, get
   live keys, and repeat step 3 with those.

## What's actually built (Phase 1)

- Landing page: hero, why-choose-us, subscription packages (pulled live
  from WooCommerce — price/plan edits in wp-admin show up immediately, no
  redeploy), delivery-area check (capture-only), how-it-works, FAQ, footer
- Bilingual (English/Telugu) flower items per plan, editable per-product in
  wp-admin via a custom "Flower Items" field
- WooCommerce cart → checkout (with Apartment Name / Flat Number fields) →
  Razorpay payment → order confirmation
- Currency set to INR, timezone to Asia/Kolkata

Note: the checkout/cart pages use WooCommerce's **classic shortcode**
templates (`[woocommerce_cart]` / `[woocommerce_checkout]`), not the newer
block-based cart/checkout — the custom Apartment Name/Flat Number fields are
implemented via the classic `woocommerce_checkout_fields` filter, which the
block-based checkout doesn't support.

## What's not built yet (Phase 2 — see prompt.md)

True recurring auto-billing, customer accounts/self-service, a mobile app,
notifications, referrals/coupons, GST invoicing, a delivery-person
app/portal. None of this is started — see `prompt.md` for the full list and
reasoning.
