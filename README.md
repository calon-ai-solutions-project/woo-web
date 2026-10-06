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

This starts the containers, installs WordPress (en_GB), Kadence and WooCommerce with WP-CLI, and activates the child theme. The site is then at http://localhost:8080. It is safe to run again.

## Build status

| Phase | What | Status |
| --- | --- | --- |
| 0 | Local environment | Script written. Not yet run: Docker was not installed on the build machine |
| 1 | Child theme: brand tokens, fonts, header, footer, buttons, product tiles | Done |
| 2 | `scripts/setup.sh`: plugins, settings, categories, pages, menu | To do |
| 3 | Homepage block patterns | To do |
| 4 | Wholesale: tiered pricing, minimum quantities, enquiry form | To do |
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
| `scripts/local-up.sh` | Starts Docker and installs WordPress, Kadence and WooCommerce |
| `scripts/build-logos.py` | Rebuilds the theme's logo files from `brand/tokens.json` with the letters as outlines |
| `wp-content/themes/clo-child/` | The Kadence child theme |

## Owner details to fill in

Placeholders in [SQUARE BRACKETS] live in one file: `wp-content/themes/clo-child/inc/site-config.php`. That file holds the company name, number, registered address and contact email shown in the footer, the announcement bar messages and the payment methods list. Pay-later wording (Klarna, Clearpay, PayPal Pay in 3) stays hidden until you add `define( 'CLO_SHOW_PAY_LATER', true );` to `wp-config.php`.

## Not in Git, ever

Database dumps, `wp-content/uploads`, `.env`, payment keys, customer data.
