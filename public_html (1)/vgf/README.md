# VG Digital Falcon — FR Holding Client Requirement Form

PHP + MySQL form in the VG Digital Falcon template (blue / orange + official logo).

- Public form: `index.php`
- Saves to MySQL
- Admin dashboard
- View submission
- Download PDF (logo on the PDF header)
- Plain-text password in `config.php`

## 1. Create the database

In phpMyAdmin or MySQL:

```sql
SOURCE db.sql;
```

Or paste `db.sql` and run it.

## 2. Edit `config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vgdf_crf');
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_MYSQL_PASSWORD');

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // plain text, as requested
```

## 3. Upload the folder

Upload the whole `vgdf-crf` folder to your hosting (cPanel public_html or a subfolder).

- Client link: `https://yourdomain.com/vgdf-crf/`
- Team login: `https://yourdomain.com/vgdf-crf/login.php`

## Login

```
username: admin
password: admin123
```

Change this before sending the form to the client.

## Logo

Official files already included:

- `assets/img/dflogo.png`
- `assets/img/dflogo-white.png` (header)
- `assets/img/dflogo-black.png` (login, thanks, PDF)

Replace these three files if you update the brand mark.

## Notes

This workspace did not contain the previous-chat PHP form. This version uses the live VG Digital Falcon site colors and logos, with the FR Holding 33-question set, MySQL storage, dashboard and PDF.
