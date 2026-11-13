# VG Digital Falcon — AI-Powered Digital Marketing & Growth Engine

Official enterprise website repository for **VG Digital Falcon** (Group of Vilvaa Guru), an autonomous AI marketing engine and digital performance agency.

---

## 1. Project Overview

- **Production URL:** [https://vgdigitalfalcon.com](https://vgdigitalfalcon.com)
- **Staging / GitHub Pages:** [https://vgdigitalfalcon.github.io/vgdigitalfalcon/](https://vgdigitalfalcon.github.io/vgdigitalfalcon/)
- **Repository:** `https://github.com/vgdigitalfalcon/vgdigitalfalcon`
- **Default Branch:** `main`
- **Working / Feature Branch:** `dev`

---

## 2. Technology Stack

- **Markup & Layout:** Semantic HTML5, Bootstrap 5.3 (Grid & Responsive Utilities).
- **Styling:** Modular CSS3 with custom CSS Variables, Cybernetic Dark Theme palette (`#070212`, `#060111`, `#38bdf8`, `#c084fc`, `#10b981`), Glassmorphism, and Neumorphic glow effects.
- **Client-Side Scripting:** Vanilla JavaScript (ES6+), jQuery 3.7.1, HTML5 Canvas 2D Rendering Engine (`seo-hero-canvas.js`, `dtapex-particles.js`).
- **UI Animation & Sliders:** Swiper.js, WOW.js, Animate.css, Magnific Popup, Waypoints, CounterUp.
- **Backend / Form Processing:** PHP 8.x for lead routing, contact forms, CAPTCHA generation, and SMTP dispatch.
- **Accessibility:** WCAG 2.2 AA compliant (keyboard navigable, visible `:focus-visible` styling, screen reader `aria-label` bindings, and `prefers-reduced-motion` compliance).

---

## 3. Repository Architecture

```text
vgdigitalfalcon/
├── .editorconfig              # Code formatting & indentation rules
├── .gitignore                 # Excludes OS, cache, build, & sensitive credentials
├── .htaccess                  # Apache server configuration & security headers
├── .nojekyll                  # GitHub Pages Jekyll bypass flag
├── index.html                 # Homepage & Global Growth Engine
├── about-us.html              # About VG Digital Falcon, Mission & Leadership
├── contact.html               # Contact & Consultation Booking Form
├── service.html               # Full Digital Service Catalog
├── seo-service.html           # Dedicated SEO & Algorithmic Telemetry Service Page
├── digital-marketing-service.html # Digital Marketing & Brand Growth
├── performance-marketing.html # Paid Ads, Meta & Google Performance Marketing
├── personal-branding-service.html # Executive & Founder Personal Branding
├── ecommerce-seo-service.html # E-commerce Rank & Conversion Optimization
├── social-media-optimization-agency.html # SMO & Organic Social Growth
├── web-development.html       # Web & Custom Application Development
├── youtube-seo-agency.html    # YouTube Video SEO & Channel Monetization
├── faq.html                   # Frequently Asked Questions
├── thankyou.html              # Lead Submission Success Page
├── 404.html                   # Custom Error 404 Page
├── robots.txt                 # Search Engine Crawler Directives
├── sitemap.xml                # Canonical XML Sitemap
├── llms.txt                   # LLM Crawling & Context Specification
│
├── assets/
│   ├── css/
│   │   ├── color.css          # Brand design system, cybernetic theme, & accessibility
│   │   ├── main.css           # Core layout styling & components
│   │   ├── bootstrap.min.css  # Bootstrap 5 framework
│   │   ├── all.min.css        # Font Awesome icons
│   │   └── ...                # Plugin stylesheets (swiper, animate, popup)
│   ├── js/
│   │   ├── main.js            # Main interactive controller & UI animations
│   │   ├── dtapex-particles.js# Global 3D mesh & particle canvas engine
│   │   ├── seo-hero-canvas.js # Dedicated SEO Constellation backlink 3D mesh
│   │   └── ...                # Vendor libraries (jquery, swiper, wow)
│   ├── img/                   # Compressed, high-resolution media & transparent SVGs
│   └── webfonts/              # Font Awesome icon fonts
│
├── backend/
│   ├── config/
│   │   └── config.sample.php  # Sample database & SMTP configuration (Copy to config.php)
│   └── handlers/
│       ├── send_email.php     # Contact form email delivery handler
│       ├── process_form.php   # General lead intake processor
│       ├── seo_process_form.php # SEO audit form processor
│       ├── captcha.php        # Mathematical security CAPTCHA generator
│       └── ...                # Specialized service form processors
│
├── components/
│   └── react/
│       ├── FalconAgentsSection.jsx # Standalone React AI Falcon Agents component
│       └── FalconAgentsSection.tsx # TypeScript version
│
├── projects/
│   └── wavestek/              # Isolated Wavestek Power Solutions subsidiary project
│
└── tests/
    ├── accessibility/
    │   └── wcag-check.js      # Automated WCAG 2.2 AA accessibility test runner
    ├── functional/
    │   └── link-checker.js    # Internal link and static asset validator
    └── performance/
        └── audit-assets.js    # Asset payload and image weight auditor
```

---

## 4. Local Development Setup

To run and preview the website locally:

### Option A: Node.js / npx (Static Site Preview)
```bash
# Run local live server in the repository root
npx serve .
# Or with python:
python -m http.server 8000
```
Open `http://localhost:8000` or `http://localhost:3000` in your web browser.

### Option B: PHP Built-in Server (Full Form & Backend Preview)
```bash
# Run local PHP development server
php -S localhost:8000
```
Forms submitted will be routed to the respective PHP handlers.

---

## 5. Backend & PHP Setup

1. Navigate to `backend/config/`:
   ```bash
   cp backend/config/config.sample.php backend/config/config.php
   ```
2. Open `backend/config/config.php` and configure:
   - **Database:** Hostname, database name, username, and password.
   - **Mail / SMTP:** SMTP host, TLS/SSL port, sending address, and credentials.
3. Ensure PHP 8.0+ is installed on your web server with `mysqli` and `curl` extensions enabled.
4. **Security Notice:** `config.php` is explicitly ignored by `.gitignore` to prevent leaking production database and email credentials.

---

## 6. Hosting Capabilities & Limitations

- **GitHub Pages (Static Hosting):**
  - Fully supports all HTML, CSS, JS, 3D Canvas animations, and SVG assets.
  - Server-side PHP handlers (`.php`) are NOT executed on GitHub Pages. Contact forms will route to your live cPanel/Apache backend URL or external webhook.
- **cPanel / Apache / LiteSpeed (Full-Stack Hosting):**
  - Fully supports both static assets and PHP backend handlers.
  - Uses `.htaccess` for caching headers, Gzip compression, and URL rewrites.

---

## 7. Automated Testing & Verification

Run the test suite using Node.js:

```bash
# 1. Run WCAG 2.2 AA Accessibility Audit
node tests/accessibility/wcag-check.js

# 2. Run Functional Link & Asset Integrity Checker
node tests/functional/link-checker.js

# 3. Run Performance & Asset Size Audit
node tests/performance/audit-assets.js
```

---

## 8. Branching & Deployment Strategy

- **`dev`:** Active development, feature additions, and optimization testing.
- **`main`:** Production-ready releases deployed to the live environment.
- Always run automated verification tests before merging `dev` into `main`.

