# A.R. Waterproofing PWA

A mobile-first company profile website with a desktop-responsive layout, persistent mobile actions, and basic Progressive Web App support.

## Included

- Responsive one-page company profile
- All 14 services from the supplied business card
- About, project showcase, why choose us, review prompt, contact section and footer
- Sticky mobile bottom bar: Call, Add Review, WhatsApp
- Telephone, email, WhatsApp and Google Maps links
- Web app manifest and service worker
- Install prompt support where the browser provides it
- Basic offline app-shell caching

## Run locally

Service workers require HTTPS or localhost. Do not open `index.html` directly with a `file://` URL.

### Option A: Python
From this folder run:

```bash
python -m http.server 8080
```

Then open `http://localhost:8080`.

### Option B: PHP / XAMPP
Copy this folder into `htdocs/ar-waterproofing-pwa`, start Apache, and open:

`http://localhost/ar-waterproofing-pwa/`

## Before publishing

1. Confirm the three mobile numbers, email, address and official website.
2. Replace the Google search links in the Review buttons with the direct Google Business Profile review URL.
3. Replace sample Unsplash photos with genuine company project photos. The photos are remotely hosted, so those images need internet access and are not cached by the current service worker.
4. Verify the 15+ years and 500+ projects figures before using them publicly, or edit them in `index.html`.
5. Replace the placeholder service descriptions with company-approved wording where necessary.
6. For the strongest install compatibility, provide 192x192 and 512x512 PNG icons and add them to the manifest. The included SVG icon is supported by modern browsers, but some app stores and older installation flows require PNG icons.
7. Deploy on HTTPS to enable service worker/PWA functionality on production.

## Configuration notes

- Primary WhatsApp number is currently `+91 98410 44994`.
- The service worker caches the local app shell. External images and Google Fonts are not guaranteed to work offline.
- This is a static front-end. It does not submit forms or store customer data; no enquiry form is included by design.


## Latest UI improvements

- Tap any service card to open a detail popup with WhatsApp enquiry, phone call, and callback details form that prepares an email.
- Tap any project card to open an image lightbox with previous/next controls.
- Animated company statistics and subtle scroll-reveal animations, with reduced-motion support.
- Upgraded property-type section for residential, commercial and industrial customers.
- The fixed mobile action dock includes all six actions in a compact two-row layout.


## Troubleshooting after updating

- Hard-refresh the website once after uploading the new files.
- If the PWA is already installed and still shows an old icon, remove/uninstall the old installed app and install it again. Browsers and operating systems can cache installed-app icons separately from the website cache.
- The supplied teal-and-white logo file is `assets/ar-logo.png`; the PWA install icons are generated from that exact supplied image.
- Service workers work on HTTPS or localhost, not by opening the HTML file directly.


## Final requested changes
- Removed Exchange Contact from the page and mobile action bar.
- Removed callback request form from service pop-ups; service pop-ups now only offer WhatsApp and Call Now.
- Changed Share Profile to Download Profile. It downloads a standalone HTML profile with C. Rajendran's name, role, contact numbers, email, address, services and direct call/WhatsApp links.
- Save Contact downloads a VCF contact card for C. Rajendran with the two retained contact numbers.
- The Need Help contact card displays only +91 98410 44994 and +91 86800 70461.
- Added a floating Install App button and a launch splash screen using the supplied teal-and-white logo.
- Updated the PWA cache version to v5. After deployment, uninstall/reinstall the PWA if the operating system still shows a cached old splash/icon.


## Service image and animation update
- Added offline-safe, locally stored illustrations for Basement Waterproofing, Sunken Slab and Scaffolding Rental so these cards do not depend on remote photo availability.
- Added subtle live motion to the hero background and image, a pulsing location/status dot, service/project image hover zoom, animated shine, and hover feedback on buttons and property cards.
- Motion effects respect `prefers-reduced-motion`.
- Service illustrations are cached in the updated service worker (`ar-waterproofing-v6`). Reinstall or refresh the PWA after deployment if old assets remain cached.

## Responsive alignment update (v7)
- Added a final responsive layout layer for desktop, tablet, small phones, and narrow screens.
- Prevented horizontal overflow and grid/card width shifting by setting `min-width: 0` on layout containers.
- Normalized image sizing and positioning so service/project images keep their intended crop and local service illustrations stay fully visible.
- Updated the service-worker cache to `ar-waterproofing-v7` so browsers refresh the latest CSS and assets.
