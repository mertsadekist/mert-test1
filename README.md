# Mert Real Estate Admin

This project is a simple PHP web application for managing real estate listings and users.

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
