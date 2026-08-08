# PHP MySQL ERP System

This is a clean ERP starter built with HTML, CSS, JavaScript, PHP, and MySQL.

## Modules Included

- Dashboard: total clients, active projects, total revenue, total expenses, net profit, pending payments, this month salary, this month rent/bills.
- Clients / CRM: add, edit, activate/deactivate client records, company details, contacts, status, client summary.
- Projects: add, edit, delete project records with client, status, budget, start date, deadline.
- Revenue & Expenses: add/edit/delete invoices, receive payments, track pending payments, add/edit/delete expense records.
- Salaries: add, edit, delete monthly payroll records and paid/pending status.
- Rent & Bills: add, edit, delete rent, electricity, internet, software, due dates, paid/pending status.
- Reports: summary totals and recommended ERP module blueprint.

## Setup

1. Copy this folder into your XAMPP `htdocs` folder, or run it from this folder with PHP's built-in server.
2. Open phpMyAdmin and import `database/schema.sql`.
3. Make sure MySQL has a database named `erp_system`.
4. Default database settings are in `config/database.php`:
   - Host: `localhost`
   - Database: `erp_system`
   - User: `root`
   - Password: empty

## Run With PHP Built-In Server

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000
```

## Default Login

- Username: `admin`
- Password: `admin123`

The public landing page opens first. Use the Login button to access the protected ERP dashboard.

## Recommended Production Additions

- Login and role-based access control.
- Edit/delete actions with confirmation.
- Invoice PDF export.
- Monthly profit/loss charts.
- Data backup and restore.
- Audit logs for finance changes.
