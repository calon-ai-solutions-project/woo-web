# Deploying to a UK WordPress host

This guide puts the store live at clearanceliquidationoutlet.co.uk. It takes the code from this repo, runs `scripts/setup.sh` on the server to build the store, and then lists the accounts only the owner can connect.

The database is never copied from a local machine. Everything the store needs is rebuilt from code by `setup.sh`, and products are added on the live site (or imported by CSV).

## 1. What you need

- A UK WordPress host with **SSH access and WP-CLI**. Most managed WordPress and cPanel hosts offer both; check before you buy.
- PHP 8.1 or newer (8.3 recommended) and MySQL 8 or MariaDB 10.6 or newer.
- Free HTTPS (Let's Encrypt) and daily server backups.
- The domain clearanceliquidationoutlet.co.uk, with access to its DNS settings.
- On your computer: Git, and this repo.

WooCommerce 11 needs WordPress 7.0 or newer. Use the latest WordPress.

## 2. Domain and HTTPS

1. In your domain's DNS, point the domain (and `www`) to the host, as the host's instructions describe.
2. Turn on HTTPS in the host's control panel once the DNS has updated.
3. Ask the host to set up SPF and DKIM for the domain, so order emails and enquiry replies are not marked as spam.

## 3. Install WordPress

Use the host's one-click installer, or WP-CLI over SSH:

```bash
cd ~/public_html            # the web root; your host may call it something else
wp core download --locale=en_GB
wp config create --dbname=DB_NAME --dbuser=DB_USER --dbpass='DB_PASSWORD' --dbhost=localhost
wp core install --url=https://clearanceliquidationoutlet.co.uk \
  --title="Clearance Liquidation Outlet" \
  --admin_user=YOUR_ADMIN_USER --admin_email=YOUR_EMAIL --prompt=admin_password
```

Use a strong, unique admin password and a username other than `admin`.

## 4. Fill in the owner details

Before the first run, edit `wp-content/themes/clo-child/inc/site-config.php` in the repo and replace every value in [SQUARE BRACKETS]:

| Setting | Example |
| --- | --- |
| Company name, number, registered address, contact email | Shown in the footer and on the Contact page |
| `clo_enquiry_inbox()` | The shared inbox that receives enquiries |
| `clo_response_time()` | `one working day` (shown as "within one working day") |
| `clo_delivery_rates()` | `4.99` and `9.99`. Delivery is only switched on once both are real prices |
| `clo_signup_form_id()` | Leave as 0 until MailPoet is set up (step 8) |

Commit the change so the repo stays the single source of truth.

## Shortcut: deploy with one command

Once WordPress is installed (step 3), the owner details are filled in (step 4) and SSH works with a key, `scripts/deploy.sh` does steps 5 and 6 from your computer: it uploads the theme, the store plugin and the setup scripts, then builds the store on the server. Run it again after every change.

**Set up an SSH key once** (so the script is not asked for a password at every step). On a Mac:

```bash
ssh-keygen -t ed25519 -C "clo-deploy"     # press Enter at each question
cat ~/.ssh/id_ed25519.pub                 # copy the line it prints
```

On Hostinger, paste that line into hPanel > Advanced > SSH Access > SSH keys. The same page shows the IP address, port and username. Test it once with `ssh -p PORT USERNAME@IP` and answer `yes`.

**Deploy:**

```bash
./scripts/deploy.sh -p PORT USERNAME@IP domains/clearanceliquidationoutlet.co.uk/public_html
```

The last part is the WordPress folder (the one holding `wp-config.php`), relative to your home folder on the server; on Hostinger it is usually `domains/YOUR-DOMAIN/public_html`. Options for `setup.sh`, such as `--reset-menus`, can go at the end.

Skip steps 5 and 6 when you use this.

## 5. Copy the code to the server

Clone the repo on the server, outside the web root, and copy the theme and the store plugin into WordPress:

```bash
cd ~
git clone https://github.com/calon-ai-solutions-project/woo-web.git clo
rsync -a --delete clo/wp-content/themes/clo-child/ ~/public_html/wp-content/themes/clo-child/
mkdir -p ~/public_html/wp-content/mu-plugins
rsync -a clo/wp-content/mu-plugins/clo-store.php ~/public_html/wp-content/mu-plugins/
rsync -a --delete clo/wp-content/mu-plugins/clo-store/ ~/public_html/wp-content/mu-plugins/clo-store/
```

Do not use `--delete` on the whole `mu-plugins` folder: some hosts keep their own files there.

## 6. Build the store

```bash
cd ~/clo
WP_PATH=~/public_html bash scripts/setup.sh --production
```

This installs Kadence, the child theme and the plugins (including UpdraftPlus and Wordfence, because of `--production`), and creates the settings, categories, attributes, tags, shipping classes, delivery zone, pages and menus. It is safe to run again at any time. Pages and menus edited in the dashboard are kept.

If WP-CLI refuses to run as root, add `--allow-root` to the `wp` calls or, better, run it as the site's own user.

## 7. Keep it private until launch

New WooCommerce stores start in **Coming soon** mode: visitors see a holding page, while you see everything when logged in. Leave it on until the launch checklist (step 11) is done. The setting is in WooCommerce > Settings > Site visibility; make sure it covers the entire site, not only the store pages, so the draft legal pages are not public either.

## 8. Connect the accounts

These need the owner's logins, so they cannot be scripted. Do them in this order.

1. **Bank transfer details.** WooCommerce > Settings > Payments > Direct bank transfer. Add the account name, sort code and account number.
2. **Stripe** (cards, Apple Pay, Google Pay). WooCommerce > Settings > Payments > Stripe. Connect your Stripe account and keep **test mode** on. Turn on Apple Pay and Google Pay (express checkout). Add Klarna and Clearpay only after Stripe confirms you are eligible.
3. **PayPal.** WooCommerce > Settings > Payments > PayPal. Connect in sandbox (test) mode first. Leave Pay Later messaging off until PayPal confirms eligibility.
4. **Pay-later wording.** When Klarna, Clearpay or PayPal Pay in 3 are confirmed, add `define( 'CLO_SHOW_PAY_LATER', true );` to `wp-config.php`. The announcement bar and footer then mention them.
5. **Rank Math** (SEO). Run its setup wizard: choose Easy mode, connect Google Search Console, and make sure the Sitemap, Schema and WooCommerce modules are on. Product schema and the XML sitemap then work on their own.
6. **Complianz** (cookie banner). Run its wizard: region United Kingdom, let it scan the site, and publish the banner. Copy the cookies it finds into the "Optional cookies" table on the Cookie Policy page, or use the policy page Complianz creates and update the footer menu to point to it.
7. **MailPoet** (stock alert emails). Choose a sending method, then create a form under MailPoet > Forms with just an email field. Put the form's ID (the number in its shortcode) into `clo_signup_form_id()` in `site-config.php`, copy the theme again (step 5), and the "Get new stock alerts first." section appears on the homepage.
8. **Google Listings & Ads.** Connect your Google account and Merchant Center so products appear in free Google listings.
9. **Wordfence.** Run its setup, add your email for security alerts, and let it optimise the firewall.
10. **UpdraftPlus.** Settings > UpdraftPlus Backups: back up the database daily and files weekly to remote storage (Google Drive, Dropbox or similar), keeping at least 14 database backups.

## 9. Run WP-Cron from the server

The New Stock tag expiry and WooCommerce's background tasks run on WordPress's scheduler, which only runs when someone visits. A server cron job makes it reliable:

1. Add `define( 'DISABLE_WP_CRON', true );` to `wp-config.php`.
2. In the host's cron settings, run this every 15 minutes:

   ```bash
   cd ~/public_html && wp cron event run --due-now --quiet
   ```

## 10. Decide on VAT

Tax was left off on purpose. Once you know your VAT position, set WooCommerce > Settings > General > Enable taxes, then WooCommerce > Settings > Tax, and update the VAT statement in the Terms & Conditions. Ask your accountant if you are unsure.

## 11. Launch checklist

- [ ] Every [PLACEHOLDER] replaced: `site-config.php`, the homepage wholesale banner ([RESPONSE TIME] comes from `site-config.php`), and the Delivery Information, Returns & Refunds, Terms & Conditions, Privacy Policy and Cookie Policy pages.
- [ ] Legal pages reviewed (ideally by a solicitor) and the "DRAFT: needs review before launch" line removed from each.
- [ ] Category page text reviewed in Products > Categories.
- [ ] Registered with the Information Commissioner's Office (data protection fee) and the number added to the Privacy Policy.
- [ ] Delivery prices set and tested at checkout, including a basket with a pallet item.
- [ ] Test orders placed in test mode: card, Apple Pay or Google Pay on a phone, PayPal, and bank transfer. Order emails received by you and the customer.
- [ ] Wholesale enquiry and contact forms tested: the inbox gets the message and the sender gets a copy.
- [ ] Stripe and PayPal switched from test to live.
- [ ] Cookie banner showing, and optional cookies blocked until accepted.
- [ ] Backups running, and one restore tested.
- [ ] WooCommerce > Settings > Site visibility set to **Live**.
- [ ] Sitemap submitted in Google Search Console (Rank Math shows its address).

## Updating the live site later

1. Make and test changes locally with `./scripts/local-up.sh`, then commit and push.
2. If you added or changed a block pattern, raise the theme version in `style.css` and `CLO_VERSION` in `functions.php`. WordPress caches the list of patterns by theme version, so the live site will not see new patterns otherwise. (Local Docker skips this cache.)
3. On the server: `cd ~/clo && git pull`, then repeat the `rsync` commands in step 5.
4. Run `WP_PATH=~/public_html bash scripts/setup.sh --production` if the setup steps or page text changed. Pages edited in the dashboard are not overwritten.

Never copy the local database or `wp-content/uploads` to the live site, and never commit `.env`, database dumps, payment keys or customer data.
