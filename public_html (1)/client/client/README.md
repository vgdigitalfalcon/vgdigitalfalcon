# Digital Falcon CRM

PHP, MySQL, HTML, CSS, JavaScript, Bootstrap 5, and a PWA shell. No React and no Node.

## Setup

1. Copy `config.sample.php` to `config.php`.
2. Set the MySQL host, database name, user, and password.
3. Put this folder on Apache or nginx with PHP 8.
4. Open `install.php` once. It creates the tables and the admin login.
5. Delete `install.php` after install.

Three separate login pages:

- Admin: `/admin/index.php` · `admin` / `Admin@123`
- Employee: `/employee/index.php` · `employee` / `Emp@123`
- Client: `/client/index.php` · `client` / `Client@123`

The home page only links to those three portals. A client password will not open the admin page.

## What is included

- Three portals: admin, employee, client.
- Admin overview, client add, employee add, INR package cost hidden from employees.
- Not Active clients drop out of employee lists. Client login still works.
- SEO: ranking with page position, site metrics, Search Console, Analytics, GBP, Off Page, On Page and Technical.
- SMO: Facebook, Instagram, LinkedIn, YouTube, date ranges, comparison, show to client.
- Meta Ads campaign report, age bands, show to client.
- Client month tabs and a client-safe PDF download.
- In-app notifications for retention day and a missing current-month log.
- PWA manifest and service worker.

Logo colours: red `#FF4647`, blue `#504FFF`, black.

Google Ads, Twitter, Reddit, and WhatsApp are not in this release.
