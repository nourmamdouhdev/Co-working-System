# Co-working Space System (Native PHP)

Native PHP + MySQL app for staff-operated co-working check-in/check-out billing.

## Features

- Staff/Admin login
- Client profile by unique phone (name can update)
- One active session per client
- Time billing per hour with round-up rule
- Add-on purchases from product catalog
- Checkout with `cash` or `visa`
- Printable receipt page
- Admin management for staff, products, and system settings
- Daily summary dashboard with revenue split by payment method

## Tech

- PHP 8.2+
- MySQL 8+
- PDO prepared statements
- Server-rendered HTML

## Project Structure

- `public/` web root and front controller
- `app/Controllers` route handlers
- `app/Models` data access and business logic
- `app/Views` templates
- `config/` app and DB config
- `database/migrations` SQL schema

## Setup

1. Create MySQL database (example):

```sql
CREATE DATABASE coworking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import SQL schema:

```bash
mysql -u root coworking_system < database/migrations/001_init.sql
```

3. Put project in `xampp/htdocs/Co-working-System`.
4. Start Apache + MySQL from XAMPP Control Panel.
5. Open in browser:

`http://localhost/Co-working-System/`

## Default Admin

- Username: `admin`
- Password: `admin123`

Change it immediately after first login by creating a new admin and deactivating the default one.

## Main Routes

- `GET /login`, `POST /login`, `POST /logout`
- `GET /checkin`, `POST /checkin`
- `GET /checkout`, `GET /checkout/search?q=...`
- `POST /checkout/{visit_id}/finalize`
- `GET /visits/active`
- `GET /admin/dashboard/daily`
- `GET/POST /admin/staff`
- `GET/POST /admin/products`
- `GET/POST /admin/settings`
- `GET /receipt/{payment_id}`

## Billing Logic

- `duration_minutes = floor((checkout_time - checkin_time) / 60)`
- `billable_hours = max(1, ceil(duration_minutes / 60))`
- `time_charge = billable_hours * hourly_rate_snapshot`
- `grand_total = time_charge + addons_total`

## Notes

- The app enforces CSRF tokens for all `POST` routes.
- The system stores snapshots of hourly rate and product prices for historical accuracy.
- Search supports exact phone match or partial name match, with active sessions prioritized.
