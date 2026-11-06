# Wavestek Power Solutions — Website

**PHP + Bootstrap 5 | Production-Ready | WordPress-Convertible**

---

## 📁 Folder Structure

```
/wavestek
├── .htaccess                   ← Apache rules (clean URLs, caching, security)
├── index.php                   ← Home page
├── about.php                   ← About page
├── service.php                 ← Services page
├── contact.php                 ← Contact page with PHP mail form
├── 404.php                     ← Custom 404 error page
│
├── /includes
│   ├── config.php              ← Site settings, nav menu, contact details
│   ├── header.php              ← HTML head + sticky navigation
│   ├── footer.php              ← Footer + WhatsApp/Call buttons
│   └── product-template.php   ← Reusable product page layout
│
├── /products
│   ├── ups-1-3kva.php
│   ├── ups-6-10kva.php
│   ├── ups-15-400kva.php
│   ├── ups-modular-10-300kva.php
│   ├── isolation-transformer.php
│   ├── stabilizer-single-phase.php
│   ├── stabilizer-three-phase.php
│   ├── inverter-microtek.php
│   ├── inverter-luminous.php
│   ├── battery-smf.php
│   ├── battery-tubular.php
│   ├── solar-inverter.php
│   └── solar-water-heater.php
│
└── /assets
    ├── /css
    │   └── style.css           ← All styles (variables, components, responsive)
    ├── /js
    │   └── main.js             ← Swiper, counters, scroll reveal, etc.
    └── /images                 ← Upload product images here
```

---

## 🚀 Setup Instructions

### Requirements
- PHP 7.4 or higher
- Apache with `mod_rewrite` enabled
- OR: Nginx (update rewrite rules accordingly)

### Local Development (XAMPP / Laragon)
1. Extract the `wavestek` folder into your web root (`htdocs/` or `www/`)
2. Visit `http://localhost/wavestek/`
3. Done! No database required.

### Live Server (cPanel / Hosting)
1. Upload all files to `public_html/` (or a subdirectory)
2. Ensure `.htaccess` is uploaded (check "show hidden files")
3. Set file permissions: folders `755`, files `644`
4. Update `SITE_URL` in `includes/config.php` if deploying in a subdirectory

### Contact Form Setup
The contact form uses PHP `mail()`. To ensure it works:
- Your hosting must have `mail()` enabled (most cPanel hosts do)
- Update `EMAIL_ADMIN` in `includes/config.php` to the correct email
- For better deliverability, consider using **PHPMailer + SMTP** (Gmail / Zoho)

**PHPMailer upgrade (optional):**
```bash
composer require phpmailer/phpmailer
```
Then replace the `mail()` call in `contact.php` with PHPMailer SMTP.

---

## 🖼️ Adding Product Images

Product pages currently use icon placeholders. To add real images:

1. Upload images to `/assets/images/products/`
2. In each product file, change the `icon` key to `image`:

```php
// Current (icon):
'icon'       => 'fas fa-plug',

// With real image:
'image'      => '../assets/images/products/ups-1-3kva-main.jpg',
'thumbs'     => [
    '../assets/images/products/ups-1-3kva-1.jpg',
    '../assets/images/products/ups-1-3kva-2.jpg',
    '../assets/images/products/ups-1-3kva-3.jpg',
],
```

Then update `product-template.php` to render `<img>` tags when `image` key exists.

---

## 📱 Features Included

| Feature | Status |
|---|---|
| Responsive design (mobile-first) | ✅ |
| Sticky header | ✅ |
| Hero slider (Swiper.js) | ✅ |
| Animated counters | ✅ |
| Scroll reveal animations | ✅ |
| Multi-level dropdown menu | ✅ |
| WhatsApp floating button | ✅ |
| Mobile sticky CTA bar (Call / WA / Quote) | ✅ |
| Back to top button | ✅ |
| PHP contact form | ✅ |
| Google Maps embed | ✅ |
| Product specifications grid | ✅ |
| Related products section | ✅ |
| Testimonials slider | ✅ |
| Custom 404 page | ✅ |
| Clean URL rewriting (.htaccess) | ✅ |
| Browser caching headers | ✅ |
| GZip compression | ✅ |
| Security headers | ✅ |
| SEO-friendly structure | ✅ |

---

## 🔄 WordPress Conversion Guide

This codebase was intentionally structured for easy WordPress theme conversion.

### Mapping PHP → WordPress

| PHP File | WordPress Equivalent |
|---|---|
| `includes/header.php` | `header.php` (theme) |
| `includes/footer.php` | `footer.php` (theme) |
| `includes/config.php` | `functions.php` (theme) |
| `index.php` | `front-page.php` |
| `about.php` | Page template: `page-about.php` |
| `service.php` | Page template: `page-service.php` |
| `contact.php` | Page template: `page-contact.php` |
| `includes/product-template.php` | Custom Post Type template: `single-product.php` |
| Nav menu (`$nav_menu` array) | `wp_nav_menus()` in `functions.php` |
| `SITE_URL`, constants | `get_template_directory_uri()`, `get_site_url()` |

### Steps to Convert
1. Create a new WP theme folder: `/wp-content/themes/wavestek/`
2. Add `style.css` (theme header comment) and `functions.php`
3. Copy CSS + JS assets to theme folder
4. Convert each `.php` page to a WordPress template
5. Replace hardcoded nav with `wp_nav_menu()`
6. Register product Custom Post Type (CPT) in `functions.php`
7. Convert product data arrays to CPT posts with ACF fields

### Elementor Conversion
Each section in the HTML maps directly to an Elementor section/widget:
- Hero Slider → Elementor Pro Slides widget
- Counter Section → Number Counter widget
- Offering Cards → Icon Box widget
- CTA Section → Elementor section with background
- Why Cards → Icon List / Icon Box widgets

---

## 🎨 Brand Colors (CSS Variables)

```css
--red:    #D32F2F   /* Primary brand red */
--blue:   #3A5F8A   /* Secondary blue */
--yellow: #F5A623   /* Accent / lightning */
--dark:   #111827   /* Text / dark backgrounds */
--light:  #f5f6f8   /* Light section backgrounds */
```

---

## 📞 Contact Details (Edit in `includes/config.php`)

- **Sales Office:** 3/23, Old Bangaru Colony 1st Street, West K.K.Nagar, Chennai - 600078
- **Factory:** Door No 5B, Perumal Koil Street, Kolapakkam, Chennai - 600128
- **Phone:** +91 9884152004 / +91 93424 86992
- **Office:** +91 044 4863 4854
- **Email:** admin@wavestekpowersolutions.in

---

*Built with ❤️ for Wavestek Power Solutions, Chennai*
