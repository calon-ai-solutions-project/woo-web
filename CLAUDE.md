# Clearance Liquidation Outlet: build spec

UK online store selling clearance, liquidation, wholesale and retail stock, one item or by the pallet. Domain: clearanceliquidationoutlet.co.uk. Short form: CL Outlet. Parent brand: PupCuddle (not shown on the site).

## Working rules

- Everything must be reproducible from code. `scripts/setup.sh` (WP-CLI) builds the store from a fresh WordPress install. The only manual steps allowed are connecting accounts (Stripe, PayPal, Google, eBay).
- Theme: Kadence (free) with a child theme at `wp-content/themes/clo-child`. No page builder plugins. Use core blocks and block patterns.
- Free plugins only. Keep the count low.
- No secrets in the repo. Read config from `.env`.
- Copy rules: plain professional English, UK spelling, no em dashes, no hype words. Never invent reviews, stats, prices or company details. Use [PLACEHOLDERS] in square brackets.
- Performance: mobile pages under 3 seconds. No sliders, no heavy animation. Images served as WebP.
- Accessibility: text contrast at least 4.5:1. Navy text on yellow. Never white text on yellow.
- Work in the phases at the bottom. After each phase: commit, summarise what was done, list anything that needs the owner, and stop.
- Before using a plugin slug, WP-CLI command or WooCommerce setting name, verify it. Do not guess.

## Brand (concept B: Yellow Sticker)

Tokens are in `brand/tokens.json`; logo files are in `brand/`.

| Token | Hex | Use |
| --- | --- | --- |
| Navy | #13233F | Header, footer, headings, body text, OUTLET text |
| Sticker Yellow | #FFD21F | Primary buttons, sale and clearance badges, price highlights |
| Chalk | #F7F5EE | Page background |
| White | #FFFFFF | Cards, product tiles |

Fonts (Google Fonts, self-host in the child theme): Anton for display headings and prices, Barlow Condensed 500/600 for navigation, labels and body.
Logo: three stacked lines, CLEARANCE / LIQUIDATION / OUTLET, with OUTLET on a yellow sticker tilted -3 degrees.

## Plugins (wp.org slugs, verify before installing)

| Job | Plugin |
| --- | --- |
| Shop | woocommerce |
| Cards, Apple Pay, Google Pay, Klarna, Clearpay | woocommerce-gateway-stripe |
| PayPal and Pay in 3 | woocommerce-paypal-payments |
| Wholesale prices | woocommerce-wholesale-prices |
| Forms | fluentform |
| SEO | seo-by-rank-math |
| Google free listings | google-listings-and-ads |
| Cookie consent | complianz-gdpr |
| WebP images | webp-converter-for-media |
| Backups and security | updraftplus, wordfence (production only) |
| Email list | mailpoet |
| eBay (optional, later) | wp-lister-for-ebay |

## Store settings

Currency GBP, selling to UK only at launch, prices shown as entered, weight kg, dimensions cm. Tax settings left as a flagged owner decision (VAT registration status unknown).

## Information architecture

Main menu, left to right: Shop (drop-down of the 13 categories), New Stock, Clearance, Wholesale & Bulk Buy, Wholesale Enquiries (styled as a yellow button at the right end), Contact. The logo links to Home. No emojis in the menu. Search bar large and always visible, including on mobile.

Product categories: Health & Beauty, Household, Electronics, Clothing & Fashion, Home & Garden, Toys & Games, Pet Supplies, DIY & Tools, Kitchen, Office & Stationery, Sports & Leisure, Seasonal, Mixed Products. Plus: Wholesale, Liquidation & Bulk Buy (with child category Pallets & Lots).

Tags: New Stock (auto-removed after 30 days by a scheduled task), Clearance.

Attributes (used as shop filters): Condition, Brand, Colour, Size.

Condition grades, exact wording:
| Grade | Meaning |
| --- | --- |
| New | Unused, in original sealed packaging |
| New, Open Box | Unused, packaging opened or damaged |
| Customer Return, Tested | Returned by a customer, checked and working |
| Customer Return, Untested | Returned by a customer, not checked. Sold as seen, mainly for trade |
| Used | Previously used, working, signs of wear described |

Shipping classes: Standard parcel, Large parcel, Pallet (delivery quoted after order). Settings: hide out-of-stock items from catalogue, but never delete sold-out products.

## Pages

Home, Shop, New Stock, Clearance, Wholesale & Bulk Buy, Wholesale Enquiries, Contact, Delivery Information, Returns & Refunds, Terms & Conditions, Privacy Policy, Cookie Policy, Guides (blog). Footer: shop links, policy links, [COMPANY NAME], [COMPANY NUMBER], [REGISTERED ADDRESS], [CONTACT EMAIL], payment logos.

Legal pages are drafts. Put "DRAFT: needs review before launch" at the top of each. Returns must state the 14-day right to cancel for consumers, with stricter separate terms for business buyers.

## Homepage, top to bottom

1. Announcement bar (navy): "UK delivery on every order · Pay in 3 with Klarna, Clearpay or PayPal · New stock every week"
2. Header: logo left, large search centre, account and basket right, menu below.
3. Hero. Headline: "Clearance and Wholesale Stock, One Item or by the Pallet". Sub-line: "New · Clearance · Wholesale · Liquidation · Bulk Buy". Text: "Quality products at competitive prices, from single items to wholesale and bulk-buy deals." Buttons: Shop Now (yellow), Wholesale & Bulk Buy (navy outline).
4. Two shopping paths side by side. "Shop Individual Products: Browse single items and small quantities, delivered across the UK." and "Shop Wholesale, Liquidation & Bulk Buy: Wholesale, liquidation and clearance stock for businesses, resellers and larger orders."
5. Just Arrived: 8 newest products, automatic.
6. Shop by Category: tile grid of the 13 categories.
7. Clearance Deals: products tagged Clearance, discount shown.
8. Wholesale Enquiries banner (navy, yellow button): "Looking for specific products, larger quantities or regular supply? Tell us what you need and we will come back to you within [RESPONSE TIME]."
9. Why Shop With Us: Great Value, Wide Range, Regular New Stock, Retail and Wholesale, UK Delivery.
10. Reviews block: built but hidden until real reviews exist.
11. Email signup: "Get new stock alerts first."
12. Footer.

## Wholesale

Launch with two ways to buy wholesale: lots and pallets as single products, and tiered quantity pricing (for example 1 to 9, 10 to 49, 50 plus). Trade-only accounts come later. Bulk listings have a minimum order quantity. Bank transfer (BACS) enabled for trade orders.

Wholesale Enquiry form fields: Name, Business name, Email, Phone, Business type (reseller, market trader, online seller, shop, other), Categories or products wanted, Quantity needed, Budget range, How often (one-off, monthly, weekly), Delivery postcode, Message, optional file upload, consent tick box. Auto-reply to the buyer, copy to [SHARED INBOX].

## Payments

Enable in this order, test mode only until the owner connects live accounts: Stripe (cards, Apple Pay, Google Pay), PayPal with Pay in 3, bank transfer, then Klarna and Clearpay through Stripe. Do not show pay-later messaging until the owner confirms eligibility.

## Product listing formula

Titles under 70 characters.
- Retail: Brand + Product + Key detail + Size or colour + Condition
- Wholesale: Quantity + Product + Size + Condition ("Job Lot 50 x Scented Candles 200g, New")
- Lots and pallets: Type + Category + Approx. item count + Stock source

SKU: category code, YYMM, 3-digit number. Example HB-2609-001.

Description template: one-sentence summary, Key features (3 bullets), Condition, What's included, Specifications (Brand, Model, Size, Colour), Buying in bulk line, Delivery line.

## eBay converter (`tools/ebay-to-woo/`)

Python script that converts an eBay Seller Hub listings CSV into a WooCommerce product import CSV. Apply the title formula, SKU format and condition mapping; put old eBay item IDs in a meta field; output a report of rows missing photos or descriptions. Column names in eBay reports vary: ask the owner for a sample file before writing the mapping.

## SEO

Rank Math with sitemap and product schema. Clean permalinks (/product/%postname%/). Every category page gets 150 to 300 words of unique text (draft placeholders). Target searches: wholesale job lots UK, liquidation stock UK, liquidation pallets UK, customer return pallets, clearance [category] UK.

## Phases

0. Local environment: Docker up, WordPress installed via WP-CLI, `.gitignore` correct.
1. Child theme: brand tokens, fonts, header, footer, buttons, product tile styling.
2. `scripts/setup.sh`: plugins, WooCommerce settings, categories, attributes, tags, shipping classes, pages, menu. Idempotent (safe to run twice).
3. Homepage built as block patterns in the child theme.
4. Wholesale: tiered pricing, minimum quantities, enquiry form.
5. eBay converter and 10 sample products following the formula.
6. Legal page drafts, SEO configuration, cookie banner.
7. `docs/DEPLOY.md`: how to deploy to a UK WordPress host and run setup there.
