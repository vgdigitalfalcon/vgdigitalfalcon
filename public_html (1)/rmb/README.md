# RMB Chennai Central - Member CRM

A simple PHP + MySQL CRM to manage members for the two member pages:
- **RMB Chennai Central Members**
- **Rotarians Non Members**

## Requirements
- PHP 7.4+ (with `mysqli` extension)
- MySQL / MariaDB
- Any web server (Apache/Nginx/XAMPP/WAMP/LAMP)

## Setup Instructions

1. **Upload files**
   Upload the entire `rmb_crm` folder to your server (e.g. `htdocs`, `public_html`, or your project root).

2. **Create the database**
   Import `database.sql` into your MySQL server. This creates the `rmb_crm` database with `admins` and `members` tables.
   ```
   mysql -u root -p < database.sql
   ```
   Or import it via phpMyAdmin.

3. **Configure DB connection**
   Edit `includes/config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'rmb_crm');
   ```

4. **Create your admin login**
   Open `setup_admin.php` in your browser **once** (e.g. `http://yoursite.com/rmb_crm/setup_admin.php`).
   Fill in a username and password to create your first CRM login.

   **After creating the admin account, delete `setup_admin.php` from the server** for security.

5. **Login**
   Go to `login.php` and log in with the credentials you just created.

6. **Make sure `uploads/` folder is writable**
   ```
   chmod 755 uploads
   ```

## Features

- Secure login (passwords hashed with PHP's `password_hash`)
- Add / Edit / Delete members
- Profile picture upload
- Fields: Name (auto-prefixed with "Rtn."), Position, Company Name, Website, Club Name, LinkedIn, Phone Number
- Dropdown to assign each member to one of the two pages:
  - RMB Chennai Central Members
  - Rotarians Non Members
- Search and filter members by name/company/position and by page
- Dashboard with member counts per page

## File Structure
```
rmb_crm/
├── database.sql            -> Import this into MySQL
├── setup_admin.php         -> Run once to create admin login, then delete
├── login.php
├── logout.php
├── dashboard.php            -> Main member listing + filters
├── add_member.php
├── edit_member.php
├── delete_member.php
├── includes/
│   ├── config.php           -> DB credentials
│   ├── auth_check.php
│   ├── header.php
│   └── footer.php
├── assets/
│   └── style.css
└── uploads/                 -> Profile pictures stored here
```

## Notes
- No approval workflow is included, as requested — all added members are immediately visible in the CRM and can be pulled into your website's member pages directly.
- To display members on your actual public-facing website pages, query the `members` table filtering by `member_page = 'RMB Chennai Central Members'` or `member_page = 'Rotarians Non Members'`.
