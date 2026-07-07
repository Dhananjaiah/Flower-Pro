Project Spec — PushpaSeva

A flower subscription webpage for apartment residents, built on WordPress + WooCommerce. This is a draft/MVP (Phase 1). The goal is one small, fully working, polished site — not a large half-finished platform.

Project Name: PushpaSeva (keep configurable — theme option / constant, not hardcoded everywhere)

---------------------------------------------------

Purpose

Apartment residents currently buy pooja flowers daily from local vendors. This webpage lets them subscribe to a monthly plan instead, and receive fresh flowers delivered weekly (or per the plan's schedule).

Target Users
- Apartment residents
- Gated communities
- Villas / individual homes

---------------------------------------------------

Why WordPress (decision record)

Originally scoped as a custom Next.js + Prisma app. Switched to WordPress + WooCommerce because:
- WooCommerce gives us cart/checkout/order-management for free — no custom backend to write or maintain
- Razorpay has a mature official WooCommerce plugin
- Price/plan editing is just wp-admin editing a WooCommerce product — no custom admin portal needed at all (this *is* the "change prices, reflects to user" requirement, out of the box)
- Matches prior experience running EkamOps on WordPress
- A future mobile app (Phase 2) would talk to the WooCommerce REST API regardless of backend choice, so this doesn't cost us anything on the "convert to app" front

---------------------------------------------------

Workflow (important — code vs config split)

- **Custom code lives in git**, on GitHub (`Dhananjaiah/Flower-Pro`), and is what gets built/edited locally: the custom theme (`wp-content/themes/pushpaseva`) and any small custom plugin/mu-plugin for the bilingual flower-item fields.
- **WordPress core, WooCommerce, and all plugins are installed directly on the VPS** — not tracked in git (standard practice; they're third-party code, not ours).
- **Product/price data (the subscription plans) lives in the VPS database**, edited via wp-admin day-to-day. A WP-CLI seed script for the initial 3 plans *is* checked into git, so the starting catalog is reproducible.
- Deploy = `git pull` on the VPS inside the theme directory (or a small deploy script that does this + cache clear). No Docker.

---------------------------------------------------

Phase 1 — Build Now (MVP Draft)

Stack
- WordPress (latest stable)
- WooCommerce (products = subscription plans, handles cart/checkout/orders)
- Razorpay for WooCommerce (official plugin) — UPI / cards / netbanking via Razorpay's own checkout widget
- Custom theme `pushpaseva` (a lightweight block/classic theme — not a page-builder-dependent theme), built by hand as PHP/CSS files so it's fully version-controlled
- Server: self-hosted VPS (Ubuntu), Nginx + PHP-FPM + MySQL (LEMP), Certbot/Let's Encrypt for TLS
- No Docker

No custom customer login system — WooCommerce's built-in guest checkout / My Account handles this. No custom admin portal — wp-admin is the admin portal.

Bilingual Flower Items
Every flower item shown on a plan must display **both English and Telugu names** (e.g. "Marigold (Banthi)"), since that's how residents actually refer to pooja flowers locally. Implement as a repeatable custom field on the WooCommerce Product (a small custom meta box registered in the theme's `functions.php` or a tiny mu-plugin — no paid ACF Pro needed for this): each row has `name_en` and `name_te`. Render on the single-product page and in the plan cards on the landing page.

Reference item list (English — Telugu):
- Chrysanthemum — Chamanthi (చామంతి)
- Marigold — Banthi (బంతి)
- Jasmine — Malle puvvulu (మల్లె పువ్వులు)
- Rose — Gulabilu (గులాబీలు)
- Flower Garland — Puvvula Dhanda (పువ్వుల దండ)
- Betel Leaves — Thamalapakulu (తమలపాకులు)
- Cotton Wicks — Vathulu (వత్తులు)
- Coconut — Kobbari Kaya (కొబ్బరి కాయ)

Subscription Packages (seed as WooCommerce simple products via WP-CLI script, editable afterward in wp-admin)

| Plan | Price | Includes |
|------|-------|----------|
| Basic | ₹299/month | Marigold (Banthi), Jasmine (Malle puvvulu), Betel Leaves (Thamalapakulu), Cotton Wicks (Vathulu) |
| Standard | ₹499/month | Everything in Basic + Chrysanthemum (Chamanthi), Rose (Gulabilu), Coconut (Kobbari Kaya) |
| Premium | ₹799/month | Everything in Standard + Flower Garland (Puvvula Dhanda), Priority Delivery |

"/month" is just label text on the product for Phase 1 (a manual monthly purchase, not automated recurring billing — see note below). Each plan card has an "Add to Cart" / "Subscribe" button.

Note on recurring billing: true auto-recurring subscriptions need the paid WooCommerce Subscriptions plugin (~$199/yr) plus a Razorpay recurring integration. Out of scope for Phase 1 — customers place a monthly order manually; auto-renewal is a Phase 2 decision once we know if it's worth the cost.

Landing Page Sections (single page, custom theme templates — not Elementor, so it's real reviewable code)

Hero Section
- Headline: "Fresh Flowers Delivered Every Week for Your Daily Pooja"
- Sub-heading: "Never miss your daily pooja again. Fresh flowers delivered to your apartment every week."
- CTA buttons: "Subscribe Now" (scrolls to packages), "View Packages"

Why Choose Us
- Fresh flowers
- Weekly delivery, no daily market visits
- Affordable subscriptions
- Easy online payments

Subscription Packages — pulled live from WooCommerce products (never hardcoded), so admin price edits in wp-admin show up immediately.

Delivery Area Check
- Simple input: apartment name / pincode / city (client-side only for now — just captured, no real serviceability backend)

How It Works
1. Choose Package → 2. Pay Online → 3. Receive Weekly Flowers

FAQ (static content)
- How often is delivery?
- Can I cancel or pause?
- Can I change my address?
- Refund policy?

Footer
- Privacy Policy / Terms (WooCommerce/WordPress default pages, placeholder copy is fine for draft)
- Contact: WhatsApp link, phone number

Checkout
- Standard WooCommerce cart → checkout → Razorpay payment → order confirmation (thank-you page)
- Customize WooCommerce checkout fields to include Apartment Name and Flat Number (in addition to standard address fields)

Theme / Design
- Style: Premium, minimal, modern
- Colors: White, Saffron, Dark Green, Gold accent
- Font: Poppins
- Rounded cards, responsive, light mode only for now, clean empty/error/loading states

Performance & Quality
- SEO meta on landing/product pages (Yoast SEO or RankMath — free plugin, don't hand-roll)
- Image optimization (WordPress native + reasonably sized uploads)
- Mobile-first responsive layout
- Basic accessibility (semantic HTML, alt text, focus states)

Deliverables for Phase 1
1. Custom `pushpaseva` theme, committed to GitHub (`Dhananjaiah/Flower-Pro`)
2. WP-CLI seed script (git-tracked) that creates the 3 plans with bilingual items — idempotent, safe to re-run
3. WordPress + WooCommerce + Razorpay plugin installed and configured on the VPS
4. Working checkout end-to-end in Razorpay **test mode** first, switched to live keys only when the user confirms
5. README with local setup, deploy steps (`git pull` on VPS), and required WooCommerce/Razorpay/SMTP settings
6. TLS via Certbot (needs a domain pointed at the VPS — confirm domain before this step)

---------------------------------------------------

Phase 2 — Future (do not build yet)

- True recurring subscriptions (WooCommerce Subscriptions plugin + Razorpay recurring) — evaluate cost vs. manual monthly renewal first
- Customer accounts: manage/pause/resume/upgrade/cancel, payment history, invoices (WooCommerce My Account covers a lot of this already — revisit what's actually missing)
- React Native (Expo) or Flutter companion app, talking to the WooCommerce REST API
- SMS / WhatsApp / push notifications (delivery reminders, renewal reminders)
- Referral program, coupon engine (WooCommerce coupons cover basic cases already)
- GST invoicing
- Delivery-person app/portal (routes, mark-delivered, photos)
- Festival specials, prasadam, milk/fruit/veg add-on subscriptions
- Dark mode
- CI/CD for theme deploys (beyond a simple `git pull`)

---------------------------------------------------

Instructions
- Build Phase 1 completely and get it fully working on the VPS before touching Phase 2.
- Custom theme code is real, reviewable PHP/CSS/JS — no placeholder/TODO templates.
- Product/pricing data changes happen in wp-admin, not in code.
- Use Razorpay test mode until the user explicitly says to go live.
