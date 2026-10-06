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

## What is in here

| Path | Purpose |
| --- | --- |
| `CLAUDE.md` | Full build spec: brand, plugins, structure, pages, copy, wholesale, payments, phases |
| `KICKOFF_PROMPT.md` | First prompt to paste into Claude Code |
| `docker-compose.yml` | Local WordPress, WooCommerce database and WP-CLI on http://localhost:8080 |
| `brand/` | Logo concept B (Yellow Sticker) as SVG, icon, and colour and font tokens |

## Not in Git, ever

Database dumps, `wp-content/uploads`, `.env`, payment keys, customer data.
