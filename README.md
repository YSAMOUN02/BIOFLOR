# Bioflor storefront (Laravel + MySQL)

A Laravel 12 rebuild of the global template storefront's layout for the
**Bioflor** range: same information architecture, header and mega menu,
footer, page composition, components, URL scheme and visual design. Brand,
products and content are data — editable in the admin and reusable for another
range or brand.

## Requirements

- PHP 8.2+ with GD and Zip (XAMPP's PHP is used here)
- Composer 2
- MySQL / MariaDB

## Getting started

```bash
composer install
cp .env.example .env          # already done in this checkout
php artisan key:generate
```

Create the database and load the content:

```sql
CREATE DATABASE sb_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed
php artisan serve --port=8123   # port 8000 is taken by IIS on this machine
```

- Storefront: <http://127.0.0.1:8123/en/home>
- Admin: <http://127.0.0.1:8123/admin> — `admin@sbglobal.test` / `password`

**Change the admin password before this is reachable by anyone else.**
Re-seeding never resets it.

## Products: the `Product` folder

`Product/` holds the range: a product list (`.xlsx` or `.csv`) and the pack
shots. Seeding imports it; so does

```bash
php artisan catalog:import Product --dry-run   # preview
php artisan catalog:import Product             # import
php artisan catalog:import "D:\other range" --deactivate-missing
```

or **Admin → Import products** (folder or upload). The importer:

- uses the newest `.xlsx`/`.csv` in the folder (currently *Wibsite Bioflor 2026 (2).xlsx*,
  the approved product list) and skips Excel `~$` lock files;
- finds the header row (the first with a *Description* / *Product* / *Name* column);
- maps *Composition* → active ingredient (+ ingredients text), *Packaging* (any spelling,
  e.g. "Parkaging") → pack size and units, *Indication* → indications, *Dosage*,
  *Storage*, *Precautions*, *How to use* → those fields;
- adds rows without a product name to the product above (multi-row indications),
  joins a line starting in lower case to the line before, fixes known typos
  ("Saccharomycess", "vancomycine");
- reads the **Q&A below the table** (format heading, then question "…?" and answer)
  into the product questions;
- reads strength (`250 MG`) and dosage form (`SACHETS`, `CAPSULES`, …) from the name;
- turns **any other column into a new attribute** automatically;
- removes the translations of English text that changed (re-run
  `php artisan db:seed --class=TranslationSeeder` for the Khmer it knows);
- matches each row to its image by form, count and strength in the file name
  (`Bioflor 250mg B.10Sachet_1.png`), resizes it and saves it as WebP;
- files each product under its format category (one tab per format on the site).

## Reusable catalogue tables

Product-specific data lives in attributes, not in `products` columns, so a new
range (other formats, a food supplement, a cosmetic) needs no migration.

| Table | Purpose |
| --- | --- |
| `products` | Core record: id (URL ref), name, slug, intro, main image, SEO, order, visibility |
| `attribute_groups` | Where attributes show: `labels`, `badges`, `specs`, `accordion`, `steps`, `hidden` |
| `attributes` | Dictionary: code, name, type (text, richtext, number+unit, yes/no, choice, list, colour…), options, badge icon |
| `attribute_sets` + `attribute_attribute_set` | Product types and the attributes they are edited with |
| `product_attribute_values` | One value per product × attribute × locale (`''` = every language) |
| `categories` + `category_product` | Range tree; children of `our-products` become the product tabs |
| `product_images` | Gallery after the main pack shot |
| `product_faqs`, `reviews` | Per-product Q&A and ratings |

The product page renders attributes by group: **labels** (`10 sachets`),
**badges** (yes/no with an icon), **key facts**, **accordion** panels
(Usage, Dosage, Precautions, Storage, Ingredients) and numbered **how-to-use**
steps (`[pour] Empty the sachet…`). Put a new attribute in a group and it
appears — no template change. Templates read values with
`$product->attr('code')`, `labels()`, `badges()`, `specs()`, `accordion()`, `steps()`.

## Brand and settings

**Admin → Settings** (`settings` table): brand name, logo strapline, logo
uploads, manufacturer, distributor block (PPM Pharma), social links (Facebook,
TikTok, Instagram, YouTube — an empty URL hides the icon), newsletter wording. Any text may contain `{brand}`; product-page sections may
contain `{product}`.

Until a logo is uploaded, the header and footer show a text wordmark built
from the brand name.

To rename a brand in content that was written out in full:

```bash
php artisan storefront:rebrand Enterol Bioflor --dry-run
php artisan storefront:rebrand Enterol Bioflor
```

## Languages (English, Khmer)

Locales are rows in `locales` (`en` default, `km`) and the first segment
of every URL. Each kind of text is stored and edited here:

| What | Stored in | Edited in |
| --- | --- | --- |
| Interface words (buttons, labels, dates) | `lang/km.json` | the file |
| Products, categories, attributes, articles, pages, Q&A, menu | `translations` table (`HasTranslations` trait) | the **Translations** card on each edit form |
| Product attribute values (pack size, dosage, how to use…) | `product_attribute_values` with `locale = km_KH` | product form → language switch |
| Settings (title, address, newsletter text…) | `settings` rows `key:km` | **Settings** → language switch |
| Page sections | `page_sections.translations.km` | section editor → language switch |

**Translating with an AI chat (Gemini, ChatGPT).** Interface words: send
`lang/km.json` and save the answer over it. Content (products, Q&A, pages,
articles, sections, settings):

```bash
php artisan translations:ai export            # lang/ai-translate/km-01-site.json …
# translate each file in the chat, save the answer over the same file
php artisan translations:ai import --dry-run  # lists problems, saves nothing
php artisan translations:ai import
```

The import only reads the `km` values and skips any text whose `{brand}` /
`:placeholder`, HTML tags or script look wrong (or that contains Thai letters).
Re-running `TranslationSeeder` afterwards would put its own Khmer back.

Anything not translated falls back to English. `TranslationSeeder` holds the
Khmer working text (matched by slug, label or position) — have it reviewed by a
native speaker. Khmer pages use **Noto Serif Khmer**. Article and legal-page
bodies are still in English. Menu and section links are stored without a
language (`gut-health.html`), so they open in the visitor's language.

## Pages and URL scheme

| Pattern | Example |
| --- | --- |
| `/{locale}/home` | `/en/home` |
| `/{locale}/our-products` | range page |
| `/{locale}/{slug}/{id}.html` | `/en/bioflor-250mg-sachets/11111.html` |
| `/{locale}/{slug}.html` | composed pages (`gut-health`, `our-uniqueness`, `our-news`, `about-us`), articles, legal pages |
| `/{locale}/all-news.html` | news listing with topic filter, sort and "show more" |
| `/{locale}/faq.html`, `/contact-us.html`, `/find-in-store` | Q&A, contact form, where to buy |

Pages are ordered, typed sections (`page_sections`, edited in **Admin → Page
sections**). Each type has a form (`SectionController::schema()`): text, rich
text, links, choices, **image and video fields (upload, or type to pick from
the media library)** and **lists of slides / items / cards with add, remove and
reorder**. A *JSON (advanced)* tab edits the raw content. Each type is a Blade
component in `resources/views/sections`:
`heroCarousel`, `keyFigures`, `introBlock`, `productTabs`, `featureBlock`,
`iconCarousel`, `learnGrid`, `tvSpot`, `heroBanner`, `articleCarousel`,
`articleGrid`, `collapsibleContent`, `richText`, `mosaic`, `categoryBanner`,
`faqList`, `ctaBox`, `timeline`, `iconGrid`, `breadcrumbs`, `topicLinks`.
Adding a section pre-fills an example payload for its type. Product pages use
page key `pdp` with regions `after_main`, `middle`, `bottom`.

## Front end

No build step: `public/css/site.css`, `public/js/site.js`, `public/js/admin.js`,
and Splide 4.1.4 (MIT) in `public/vendor/splide` for carousels. Fonts are
Public Sans and Staatliches from Google Fonts. Icons come from
[Tabler Icons](https://tabler.io/icons) (interface, product badges, how-to-use
steps) and [Health Icons](https://healthicons.org) (diarrhoea, intestine,
medicine, travel…), both MIT — licences in `public/vendor/icons`.

## Security

- **Headers** (`App\Http\Middleware\SecurityHeaders`, every response): a Content
  Security Policy that only runs scripts from this site — a `<script>` or
  `onerror=""` pasted into content does not execute — plus `X-Frame-Options`,
  `nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS over HTTPS, and
  `X-Robots-Tag: noindex` on the admin and search. Templates therefore use no
  inline scripts or `on…=""` handlers (the admin uses `data-confirm`,
  `data-autosubmit`, `data-copy`, handled in `admin.js`).
- **Uploads** (`App\Support\SafeUpload`): the saved extension must be on an
  allow-list (a PNG named `x.html` is refused) and SVG files are cleaned of
  scripts, event handlers and outside links. `public/uploads/.htaccess` stops
  Apache from running or serving scripts from the uploads folder.
- **Rate limits**: admin login 10/min, contact form and newsletter 5/min,
  search suggestions 60/min (per IP).
- CSRF on every form, sessions renewed at login, subscriber CSV export
  neutralises spreadsheet formulas, search input capped at 100 characters.

## SEO

- `/robots.txt` and `/sitemap.xml` are generated (`SeoController`): English and
  Khmer pages with `hreflang` alternates, products and articles with dates.
- Every page has a canonical link, `hreflang` alternates (+ `x-default`), Open
  Graph / Twitter tags for link previews on Facebook, TikTok and chat apps
  (**Settings → Share image**; products and articles use their own photo),
  JSON-LD (Organization, WebSite, Product).
- Search results are `noindex`.
- Photos carry `width`/`height` (`img_size()`), so pages do not jump while
  loading; the first hero slide and the product photo load first
  (`fetchpriority`). The home hero has a phone-sized image (`image_mobile`).

## Tests

The tests run on `sb_website_test`, a copy of the site database, and roll
every change back. Create or refresh the copy, then run them:

```bash
mysql -u root -e "DROP DATABASE IF EXISTS sb_website_test; CREATE DATABASE sb_website_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysqldump -u root --single-transaction sb_website | mysql -u root sb_website_test
php artisan test
```

They cover every page in every language, every product and article, forms,
redirects and 404s, admin access, security headers, rate limits, upload
checks, SVG cleaning, robots/sitemap and the SEO tags. A test refuses to run on
any other database.

## Media library

Images and videos live in `public/uploads/library` and the `media` table.
Add them in **Admin → Media library**, directly from an image/video field in
the section editor, or in bulk:

```bash
php artisan media:import Product
```

Images are resized (max 1600 px) and converted to WebP; videos are copied; a
file already in the library is not added twice. Seeding imports the `Product`
folder and `storage/app/seed-media` (the video still), and the page and article
seeders use those files where they exist, falling back to the placeholder art
in `public/img/placeholders`.

## Before production

**Admin → Launch checklist** (`App\Support\LaunchChecklist`) lists these as
numbered tasks. Most steps tick themselves when the placeholder wording is gone
(English "Placeholder", Khmer "អត្ថបទគំរូ", sample stores and reviews, default
admin password, `APP_DEBUG`); the others have a *Mark done* button (stored as
`settings` rows `checklist:{step}`). The admin password is changed under
**Admin → (your email, top right)**.

1. Replace the remaining placeholders: articles, key figures and their
   references, testimonial, the data protection page. Product information,
   product Q&A and the About page now hold the approved content (product list
   and About deck, October 2026). A medicine's information must be the
   **regulator-approved wording for your market**.
2. Footer: PPM Pharma's address, phone, email and website are set in
   **Settings**; the legal links (terms, cookies, accessibility) were removed
   from the footer at the client's request — the pages remain in **Content
   pages**. Data protection is still linked from the newsletter sign-up.
3. Use only images, video and brand material you are licensed to publish; the
   brand owner's approval is needed for a public site. The home hero photo has
   a "#1 probiotic drug worldwide" claim printed in the image — it needs its
   reference and local approval, or a version without the claim.
4. The app sets only Laravel's session and CSRF cookies (and localStorage for
   the newsletter pop-in counter); add a consent tool before adding analytics.
5. Add the online shops and local pharmacies in **Admin → Where to buy** ("Online
   shop" ticked = *Buy online*, otherwise *Local pharmacies* with a map link),
   replace the sample reviews, change the admin password, set `APP_DEBUG=false`.
6. Have the Khmer text reviewed and translate the article bodies.
7. Server: point the web root at `public/` only; HTTPS with `APP_URL=https://…`;
   in `.env` set `APP_ENV=production`, `APP_DEBUG=false` (with debug on, an
   error page shows code and paths), `SESSION_SECURE_COOKIE=true`; enable PHP
   OPcache; run `php artisan optimize` after each deploy. `public/.htaccess`
   turns on compression and browser caching for static files on Apache.
   Never commit `.env` or `storage/app/backups`.
