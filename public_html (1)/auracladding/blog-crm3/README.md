# Aura Cladding Blog CRM

**PHP + MySQL Content Management System**  
Fully themed to match the website (Deep Navy + Orange/Gold)  
**Optimized for Google Search + Generative Engine Optimization (GEO)**

---

## What is GEO?

**GEO = Generative Engine Optimization**

Making your content more likely to be retrieved, cited and surfaced by AI systems such as:

- Google AI Overviews / SGE
- ChatGPT / GPTBot
- Perplexity
- Claude / Anthropic
- Other generative search engines

This CRM includes dedicated fields and structured data specifically for that purpose.

---

## Features

### Classic SEO
- Meta Title, Meta Description, Keywords, Focus Keyword
- Editable clean Slugs
- Canonical URL
- Featured Image + Social / OG Image with ALT text
- Open Graph + Twitter Cards
- Automatic XML Sitemap (regenerates on publish/update/delete)
- Editable robots.txt

### Generative Engine Optimization (GEO)
- **AI Summary** — short direct-answer text AI engines love to quote
- **Key Takeaways** — bullet points frequently extracted by generative systems
- **FAQ Builder** — generates FAQPage JSON-LD schema (very strong for AI Overviews)
- **Schema Type** selector (BlogPosting / Article / HowTo / FAQPage)
- **Sources / Citations** field
- JSON-LD structured data auto-generated on every single post page
- robots.txt explicitly allows GPTBot, ClaudeBot, PerplexityBot, Google-Extended, Bytespider, etc.
- Author bio + expertise (E-E-A-T signals)

### Blogging
- Rich text editor (Bold, Italic, Links, Images, Tables…)
- Multiple images inside content with ALT
- Categories & Tags
- Featured Blog / Story toggle
- Draft / Published / Scheduled / Archived
- Auto reading time
- View counter

### Public Pages
- `public/blog.php` — Blog listing page matching your design (9 cards, category badges, dates, pagination)
- `public/post.php` — Single article with full SEO + GEO markup

---

## Preferred Featured / Cover Image Size

**Recommended size: 1200 × 630 pixels**

| Use case              | Size          | Format     | Notes                          |
|-----------------------|---------------|------------|--------------------------------|
| Featured / Cover      | **1200 × 630** | JPG / WebP | Best for cards + social + AI  |
| Social / OG Image     | **1200 × 630** | JPG / WebP | Exact Open Graph standard     |
| Ideal file size       | under 300 KB  | —          | Faster loading & better UX    |

This ratio (≈ 1.91:1) works perfectly for:
- Blog card thumbnails
- Facebook / LinkedIn / Twitter sharing
- AI system image previews

---

## Installation

### 1. Database
```bash
mysql -u root -p < sql/schema.sql
```

### 2. Config
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aura_blog_crm');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('SITE_URL', 'https://www.auracladding.com');
```

Also set the same Site URL inside **Admin → SEO & GEO Settings**.

### 3. Permissions
```bash
chmod -R 755 admin/uploads
```

### 4. Login
- URL: `/blog-crm/admin/login.php`
- Username: `admin`
- Password: `Admin@123`  
  **Change immediately after first login.**

### 5. Point public files
Make sure your server can serve:
- `/blog` → `blog-crm/public/blog.php` (or copy/symlink)
- `/blog/{slug}` → `blog-crm/public/post.php?slug={slug}` (use .htaccess rewrite)
- `/sitemap.xml` → `blog-crm/public/sitemap.xml`
- `/robots.txt` → `blog-crm/public/robots.txt`

Example Apache rewrite for clean URLs:
```apache
RewriteEngine On
RewriteRule ^blog/?$ /blog-crm/public/blog.php [L]
RewriteRule ^blog/([a-z0-9-]+)/?$ /blog-crm/public/post.php?slug=$1 [L,QSA]
```

---

## How Published Posts Appear on the Blog Page

1. Create / edit a post in the CRM
2. Set **Status = Published**
3. Save → sitemap is auto-updated
4. Visit `/blog` (or your `blog.php`) — the new article appears in the grid with:
   - Category badge
   - Date + reading time
   - Title + excerpt
   - Featured image
   - “Read More” link

The listing page is styled to match your original Blog page screenshot (navy header, orange accents, 3-column card grid, pagination, CTA banner, footer).

---

## Folder Structure

```
blog-crm/
├── config.php
├── sql/schema.sql
├── admin/                  ← CRM (login required)
│   ├── login.php
│   ├── index.php           (Dashboard)
│   ├── posts.php
│   ├── post-edit.php       (full SEO + GEO form)
│   ├── categories.php
│   ├── tags.php
│   ├── media.php
│   ├── sitemap.php
│   ├── seo-settings.php
│   ├── uploads/
│   └── assets/css/admin.css
└── public/                 ← Public-facing pages
    ├── blog.php            (listing — matches screenshot)
    ├── post.php            (single article + JSON-LD)
    ├── sitemap.xml
    ├── robots.txt
    └── assets/css/blog.css
```

---

## Default Categories
Cladding • Glazing • Exterior Cladding • Toughened Glass • Architecture • Installation • Sustainability • Design • Energy Efficiency

---

Built for **Aura Cladding** — Insights. Ideas. Inspiration.  
SEO + Generative Engine Optimization ready.


---

## Gallery / Our Works (NEW)

Completely **separate** from the Blog module.

### What you get
- **CRM**: Admin → Gallery / Works → All Projects + Add Project
- Fields per project:
  - Project Title
  - Location
  - Area (e.g. `45,000 sq.ft.`)
  - **Materials** (multiple — add as many as you need)
  - **Photos** (multiple images → carousel on the public card)
  - Status (Draft / Published), Sort order, Featured flag
- **Public page**: `public/works.php` — matches your Works UI (hero, stats bar, 3-column cards with carousel, CTA, footer)

### Install / migrate

**Fresh install** — `sql/schema.sql` already includes the gallery tables.

**Existing DB** — run the migration once:

```bash
mysql -u YOUR_USER -p YOUR_DB < sql/gallery_migration.sql
```

### Public URL
- Listing: `{site}/blog-crm/public/works.php`
- Clean URL example (optional rewrite):

```apache
RewriteRule ^works/?$ /blog-crm/public/works.php [L]
```

### How it works
1. Log in to CRM → **All Projects** or **Add Project**
2. Fill Title, Location, Area
3. Add one or more Materials
4. Upload multiple photos (they become the card carousel)
5. Set Status = **Published**
6. Visit the Works page — the project appears with the same design as your screenshot

Blog and Gallery never mix; they are independent modules sharing the same theme and media uploads folder.
