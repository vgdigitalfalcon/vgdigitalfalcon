# VG Digital Falcon — Programmatic SEO System
## Complete Setup & Usage Guide

---

## 📁 Project Structure

```
vg-digital-falcon/
├── index.php               ← Main router (all page requests go here)
├── .htaccess               ← URL rewriting, caching, security
├── generate-pages.php      ← Bulk page generator (run once to seed pages)
├── sitemap.xml.php         ← Dynamic XML sitemap
│
├── includes/
│   ├── config.php          ← DB credentials + site settings
│   └── functions.php       ← All helper functions
│
├── templates/
│   ├── seo-page.php        ← Master SEO page template (all pages use this)
│   └── 404.php             ← 404 error page
│
└── data/
    └── keyword-clusters.md ← Master keyword cluster reference
```

---

## 🚀 Setup Steps

### Step 1 — Configure Database
```
Edit includes/config.php:
  DB_HOST  = 'localhost'
  DB_USER  = 'your_db_user'
  DB_PASS  = 'your_db_password'
  DB_NAME  = 'vg_digital_falcon'
  SITE_URL = 'https://yourdomain.com'
```

### Step 2 — Create Database
```sql
-- Import the schema + seed data:
mysql -u root -p < database.sql
```

### Step 3 — Upload Files
Upload all project files to your web server root (public_html or www).

### Step 4 — Enable .htaccess
Ensure mod_rewrite is enabled in Apache:
```
sudo a2enmod rewrite
sudo service apache2 restart
```

### Step 5 — Generate All Pages
Run the bulk generator to create all city, industry, and business pages:

**Via browser:**
```
https://yourdomain.com/generate-pages.php?secret=vgdf_generate_2024
```

**Via CLI (recommended):**
```bash
cd /path/to/project
php generate-pages.php
```

### Step 6 — Verify Pages
Visit these URLs to confirm pages are live:
- https://yourdomain.com/seo-services-chennai
- https://yourdomain.com/seo-services-healthcare
- https://yourdomain.com/seo-services-small-business

---

## 🔑 Keyword Clusters Summary

### City Pages (26 pages)
`/seo-services-{city}` — e.g. /seo-services-mumbai

### Industry Pages (20 pages)
`/seo-services-{industry}` — e.g. /seo-services-healthcare

### Business Type Pages (14 pages)
`/seo-services-{business-type}` — e.g. /seo-services-ecommerce

**Total: 60+ unique pages immediately, expandable to 150+**

---

## ✅ Anti-Duplicate Content Strategy

Each page is unique because:

1. **GEO-specific intro paragraphs** — reference actual city neighborhoods, stats, local context
2. **Industry-specific content** — different FAQs, process steps, benefits per industry
3. **Unique headings** — target keyword injected naturally throughout
4. **Localized trust signals** — "businesses in Chennai trust us because..."
5. **Dynamic related links** — each page links to contextually different pages
6. **Schema markup** — page-specific LocalBusiness + FAQPage structured data
7. **Breadcrumbs** — reinforces page context and hierarchy

---

## 🔧 Adding New Pages

### Via Database (recommended for bulk):
```sql
INSERT INTO seo_pages (slug, page_type, target_keyword, location_name, meta_title, meta_description, hero_headline, hero_subheadline, intro_paragraph, why_trust_content, ...)
VALUES ('seo-services-nagpur', 'city', 'SEO Services in Nagpur', 'Nagpur', ...);
```

### Via generate-pages.php:
Add a new city/industry/business entry to the respective array and re-run the script.

---

## 📊 Sitemap
Dynamic sitemap available at:
```
https://yourdomain.com/sitemap.xml
```
(Make sure .htaccess routes sitemap.xml to sitemap.xml.php)

Add to Google Search Console immediately after launch.

---

## 📬 Lead Capture
All form submissions are stored in the `leads` table.
View leads:
```sql
SELECT * FROM leads ORDER BY created_at DESC;
```

---

## 🔒 Security Notes
- Change the generator secret key in generate-pages.php
- Delete or password-protect generate-pages.php after initial run
- The .htaccess blocks direct access to /includes/ directory
- All user inputs are sanitized via PDO prepared statements

---

## 🤝 Support
VG Digital Falcon | hello@vgdigitalfalcon.com
