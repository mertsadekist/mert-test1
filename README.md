# Mert Real Estate Admin

This project is a lightweight PHP administration panel used to manage real estate
listings. It includes user authentication, role based permissions and tools for
importing, viewing and exporting apartment data.

## Features

- Manage developers and their projects.
- Upload apartment data from Excel spreadsheets.
- Filter apartments across projects by developer, location, bedrooms, area and price.
- Export filtered results to Excel (via PhpSpreadsheet) or PDF (via Dompdf).
- Role based access control with admin, editor and viewer roles.

## Requirements

- **PHP**: 7.4 or newer (8.x is supported). The required version is defined in `composer.lock`.
- **Composer**: used for installing dependencies.
- **MySQL** (or compatible) database.

## Installation

1. Clone the repository and change into the project directory.
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Configure your database connection. Edit `db_connection.php` or define the following environment variables to override the defaults:
   ```bash
   DB_HOST     # Database hostname (default: localhost)
   DB_DATABASE # Database name
   DB_USER     # Database user
   DB_PASS     # Database password
   ```
   The provided `db_connection.php` reads these variables if they exist; otherwise it falls back to the hard coded values.
4. Create the database and tables required by the application. (No SQL dump is included.)

## Running the application

You can launch the application locally with PHP's built-in server:

```bash
php -S localhost:8000
```

Then open [http://localhost:8000/index.php](http://localhost:8000/index.php) in your browser.

## Bootstrapping an admin user

The `create_admin.php` script can be run once to insert an initial administrator account. The script contains empty placeholders for the admin email and password; edit these values in the file before executing:

```bash
php create_admin.php
```

After logging in with this account, you can manage users from the dashboard.


## Roles

The application defines three roles:

- **viewer** – can browse apartment listings.
- **editor** – can manage projects, upload apartment spreadsheets and export data.
- **admin** – full access including user management in addition to editor rights.
