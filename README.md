# Clearance Liquidation Outlet

WooCommerce store for clearanceliquidationoutlet.co.uk. This repo is built with Claude Code from the spec in `CLAUDE.md`.

## Start in 5 steps

You need Git, the GitHub CLI (`gh`), Docker Desktop and Claude Code installed.

1. Unzip this folder and open a terminal in it.
2. Create the repo and push (personal account):
   ```bash
   git init && git add . && git commit -m "Project brief and brand kit"
   gh repo create clearance-liquidation-outlet --private --source=. --push
   ```
   To put it under the Calon organisation instead, use `gh repo create [YOUR-ORG]/clearance-liquidation-outlet --private --source=. --push`.
3. Copy `.env.example` to `.env` and set your own passwords.
4. Start Claude Code in this folder:
   ```bash
   claude
   ```
5. Paste the prompt from `KICKOFF_PROMPT.md`.

Claude Code reads `CLAUDE.md` automatically and works phase by phase, committing after each one.

## Run the site locally

With Docker Desktop running and `.env` in place:

```bash
./scripts/local-up.sh
```

This starts the containers, installs WordPress (en_GB) with WP-CLI, then runs `scripts/setup.sh` to build the store: Kadence and the child theme, the plugins, WooCommerce settings, categories, attributes, tags, shipping classes, pages and menus. The site is then at http://localhost:8080, and every email it sends (order emails, enquiry forms) lands in a local inbox at http://localhost:8025. It is safe to run again: pages and menus you have edited in the dashboard are kept. To rebuild the menus from scratch, run `./scripts/local-up.sh --reset-menus`.

## Build status

| Phase | What | Status |
| --- | --- | --- |
| 0 | Local environment | Script written. Not yet run: Docker was not installed on the build machine |
| 1 | Child theme: brand tokens, fonts, header, footer, buttons, product tiles | Done |
| 2 | `scripts/setup.sh`: plugins, settings, categories, pages, menu | Done |
| 3 | Homepage block patterns | Done |
| 4 | Wholesale: tiered pricing, minimum quantities, enquiry form | Done |
| 5 | eBay converter and 10 sample products | To do. Needs a sample eBay Seller Hub CSV |
| 6 | Legal page drafts, SEO, cookie banner | To do |
| 7 | `docs/DEPLOY.md` | To do |

## What is in here

| Path | Purpose |
| --- | --- |
| `CLAUDE.md` | Full build spec: brand, plugins, structure, pages, copy, wholesale, payments, phases |
| `KICKOFF_PROMPT.md` | First prompt to paste into Claude Code |
| `docker-compose.yml` | Local WordPress, WooCommerce database and WP-CLI on http://localhost:8080 |
| `brand/` | Logo concept B (Yellow Sticker) as SVG, icon, and colour and font tokens |
| `scripts/local-up.sh` | Starts Docker, installs WordPress, then runs `setup.sh` |
| `scripts/setup.sh` | Builds the store on any WordPress install with WP-CLI. Steps and page text are in `scripts/setup/` |
| `scripts/build-logos.py` | Rebuilds the theme's logo files from `brand/tokens.json` with the letters as outlines |
| `wp-content/themes/clo-child/` | The Kadence child theme |
| `wp-content/mu-plugins/clo-store/` | Store features that must not depend on the theme: bulk prices, minimum order quantities, pallet delivery, the wholesale enquiry and contact forms (kept under Enquiries in the dashboard), and New Stock tag expiry |
| `wp-content/themes/clo-child/patterns/` | Homepage sections as block patterns. The Home page is built from them, and "Homepage: all sections" in the block inserter rebuilds it |

## Owner details to fill in

Placeholders in [SQUARE BRACKETS] live in one file: `wp-content/themes/clo-child/inc/site-config.php`. That file holds the company name, number, registered address and contact email shown in the footer, the inbox that receives enquiries, the enquiry response time, the UK delivery prices, the announcement bar messages and the payment methods list. Delivery is only switched on once real prices are in that file and `setup.sh` has run again. Pay-later wording (Klarna, Clearpay, PayPal Pay in 3) stays hidden until you add `define( 'CLO_SHOW_PAY_LATER', true );` to `wp-config.php`.

## Wholesale features

- **Bulk prices.** On a simple product, Product data > General > Bulk prices, for example from 10 units at 4.50 each and from 50 units at 3.95 each. The product page shows a price table and the basket applies the right price. A sale price lower than the bulk price still wins.
- **Minimum order quantity.** Product data > Inventory. Buyers cannot add or check out fewer.
- **Pallets.** Give a product the "Pallet" shipping class. The product page, basket and delivery line all say that pallet delivery is quoted after the order.
- **Enquiries.** The Wholesale Enquiries and Contact forms email the inbox set in `site-config.php`, send the buyer a copy, and keep every message under Enquiries in the dashboard. Files sent with an enquiry are stored outside public view. Enquiries are included in Tools > Export / Erase Personal Data.
- **New Stock.** Tag a product New Stock and the tag comes off by itself after 30 days.

For CSV imports, bulk prices and minimums can be set with the columns `Meta: _clo_price_tiers` (for example `10:4.50|50:3.95`) and `Meta: _clo_min_qty`.

## Not in Git, ever

Database dumps, `wp-content/uploads`, `.env`, payment keys, customer data.
