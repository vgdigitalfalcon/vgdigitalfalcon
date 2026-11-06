# A.R. Waterproofing - Services, Projects & Mini CRM

A complete PHP/MySQL system matching the existing homepage design. Includes:

- Public pages: Services listing, Service detail (with related projects), Projects listing, Project detail with gallery slideshow
- Admin CRM: Login, Add/Edit/Delete Services, Add/Edit/Delete Projects with multi-image/video gallery uploads
- Same visual theme (teal #55b7b9, cards, buttons, layout)

## Setup Instructions

### 1. Database
1. Create a MySQL database (or use the SQL file).
2. Import `sql/schema.sql`:
   ```sql
   mysql -u root -p < sql/schema.sql
   ```
   Or run the contents in phpMyAdmin / MySQL client.

3. **Default login:**
   - Username: `admin`
   - Password: `password`
   - **Change this immediately** after first login (update the hash in the `users` table).

### 2. Configuration
Edit `includes/config.php`:
- Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Update `SITE_URL` to your domain (e.g. `https://www.arwaterproofing.com`)

### 3. File Permissions
Make the `uploads/` folder writable by the web server:
```bash
chmod 755 uploads/
# or chown to www-data / apache user
```

### 4. Assets
Place your existing assets in the `assets/` folder:
- `ar-logo.png`
- `favicon-64.png`
- `apple-touch-icon.png`
- Any SVG service images

### 5. Homepage Integration
Your existing `index.html` can stay as-is. Update the service cards to link to the dynamic pages:
- Change service links from `#services` to `services.php`
- Or replace the static service grid with a link to `services.php`

The dynamic pages (`services.php`, `service.php`, `projects.php`, `project.php`) use the same header/footer design.

### 6. Access
- **Public site:** `services.php`, `projects.php`
- **Admin:** `admin/login.php`

## Features

### Services Module
- Add service: Name, About, Key Benefits (one per line), Our Process
- Automatically listed on `services.php`
- Clicking a service opens `service.php?id=X` showing full details + related projects

### Projects Module
- Add project: Name, Service (dropdown), Client Name, Location, Duration, Overview
- Gallery: Multiple images and videos (upload)
- Listed on `projects.php`
- Project detail page with slideshow gallery (images + videos, keyboard arrows, prev/next)
- Related projects appear on the service detail page

### Gallery Slideshow
- Click any gallery item → full-screen modal
- Navigate with arrows or keyboard
- Supports both images and videos

## File Structure
```
ar-waterproofing/
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── services.php
│   └── projects.php
├── includes/
│   ├── config.php
│   ├── header.php
│   └── footer.php
├── assets/
│   └── styles.css
├── uploads/          ← uploaded media goes here
├── sql/
│   └── schema.sql
├── services.php
├── service.php
├── projects.php
├── project.php
└── README.md
```

## Security Notes
- Change the default admin password immediately
- For production, add more robust auth, CSRF protection, and file upload validation
- Restrict `uploads/` directory with `.htaccess` if needed
- Use HTTPS

## Customization
- Colors and spacing are in `assets/styles.css` (`:root` variables)
- WhatsApp number and contact details are hardcoded in the PHP templates (search for `919841044994`)
- Update them to match your needs

Enjoy the system!
