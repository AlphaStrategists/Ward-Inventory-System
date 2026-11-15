# 🏥 Ward Inventory Management System

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white) ![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white) ![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1?logo=mysql&logoColor=white) ![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?logo=vite&logoColor=white) ![Status](https://img.shields.io/badge/Project-In_Development-orange)
A Laravel-based web application for managing ward inventory operations,
stock movements, medicine-related records, requisitions, and role-based
access.

**Repository:**
[AlphaStrategists/Ward-Inventory-System](https://github.com/AlphaStrategists/Ward-Inventory-System.git)

------------------------------------------------------------------------

## Table of Contents

-   [Project Overview](#project-overview)
-   [Main Modules](#main-modules)
-   [User Roles and Access](#user-roles-and-access)
-   [Technology Stack](#technology-stack)
-   [System Requirements](#system-requirements)
-   [Installation and Setup](#installation-and-setup)
-   [Database Configuration](#database-configuration)
-   [Running the Application](#running-the-application)
-   [Database Migrations and Seeders](#database-migrations-and-seeders)
-   [Useful Artisan Commands](#useful-artisan-commands)
-   [Project Structure](#project-structure)
-   [Deployment](#deployment)
-   [Security and Data Protection](#security-and-data-protection)
-   [Troubleshooting](#troubleshooting)
-   [Contributing](#contributing)
-   [License](#license)

------------------------------------------------------------------------

## 📌 Project Overview

The Ward Inventory Management System is designed to support
inventory-related activities within a hospital ward. It provides
authenticated pages and server-side operations for managing different
inventory categories, requisitions, stock transactions, and related
records.

The application uses Laravel's routing, middleware, database,
authentication, and migration facilities. Access to protected pages and
operations is controlled through authentication and role middleware.

> **Note:** This repository is an application project, not a substitute
> for a hospital's approved clinical, information-security, or
> records-management procedures. Use test data during development and
> testing.

## 🧩 Main Modules

The current application routes and database migrations cover the
following areas:

### 🩺 1. Surgical Consumables

-   Surgical Consumables 1
-   Surgical Consumables 2
-   Consumable item and requisition operations
-   Requisition approval and rejection routes
-   Requisition receiving and update operations
-   Stock adjustment operations

### 🛒 2. Local Purchases

-   Local Purchases pages for the supported user roles
-   Local-purchase-related application operations

### 💊 3. Medicine and Pharmacy Operations

-   Medicine records
-   Medicine forms, strengths and units
-   Medicine batches and stock receipts
-   Stock ledger records
-   Dispensation records and reports
-   Medicine stock adjustments

### 📦 4. General Inventory

-   General inventory items
-   General inventory transactions
-   General stock adjustments
-   Daily-count-related transaction details

### 🧑‍⚕️ 5. Ward and Patient-Related Records

-   Ward records
-   Patient records
-   Admission records
-   Related inventory and dispensing records

### 🛡️ 6. User, Role and Permission Management

-   User authentication
-   Role records
-   Permission records
-   Role-permission relationships
-   Role-protected routes
-   Activity log records

The exact actions available to a user depend on the routes, middleware,
controller logic, and permissions configured in the current codebase.

## 👥 User Roles and Access

The web routes define authenticated, role-specific page paths for these
role labels:

  -----------------------------------------------------------------------
  Role / route prefix                 Main page routes
  ----------------------------------- -----------------------------------
  Nurse (`nurse`)                     Surgical Consumables 1, Surgical
                                      Consumables 2, Local Purchases

  MS (`ms`)                           Surgical Consumables 1, Surgical
                                      Consumables 2, Local Purchases

  Admin (`admin`)                     Surgical Consumables 1, Surgical
                                      Consumables 2, Local Purchases

  Pharmacist (`pharmacist`)           Surgical Consumables 1, Surgical
                                      Consumables 2, Local Purchases
  -----------------------------------------------------------------------

The project also defines role and permission middleware aliases in
`bootstrap/app.php`:

-   `role` --- `CheckRole`
-   `permission` --- `CheckRolePermission`
-   `admin` --- `IsAdmin`

Role names and role IDs are represented in the database. When changing
access rules, check both the role data and the middleware/controller
implementation. Do not assume that changing a displayed role name alone
updates authorization.

Example protected page URLs after local setup:

``` text
/nurse/surgical-consumable-1
/nurse/surgical-consumable-2
/nurse/local-purchases

/ms/surgical-consumable-1
/admin/surgical-consumable-1
/pharmacist/surgical-consumable-1
```

All pages other than the public login routes are placed inside the
authentication middleware group in `routes/web.php`.

## 🛠️ Technology Stack

  Component                                   Technology
  ------------------------------------------- ----------------------------------------------------
  Backend framework                           Laravel 12
  Programming language                        PHP 8.2 or later
  Database                                    MySQL / MariaDB supported by Laravel configuration
  Frontend build tool                         Vite 7
  CSS framework                               Bootstrap 5 and Tailwind CSS 4
  JavaScript                                  Alpine.js
  Reactive components                         Livewire 4
  Dependency management                       Composer and npm
  Local development environment (suggested)   XAMPP on Windows
  Source control                              Git and GitHub

## 💻 System Requirements

Install the following before setting up the project:

-   PHP **8.2 or later**, with the PHP extensions required by Laravel
    and the project.
-   Composer 2.
-   Node.js and npm (use a currently supported Node.js LTS release).
-   MySQL or MariaDB.
-   Git.
-   A local web/database environment such as XAMPP, or equivalent PHP
    and MySQL services.

For a local XAMPP setup, ensure the PHP version used by the terminal is
compatible with the PHP version configured in XAMPP.

Check installed versions:

``` bash
php -v
composer --version
node -v
npm -v
git --version
```

## ⚙️ Installation and Setup

### 1. Clone the repository

``` bash
git clone https://github.com/AlphaStrategists/Ward-Inventory-System.git
cd Ward-Inventory-System
```

To clone a particular branch:

``` bash
git clone --branch <branch-name> --single-branch https://github.com/AlphaStrategists/Ward-Inventory-System.git
cd Ward-Inventory-System
```

### 2. Install PHP dependencies

``` bash
composer install
```

### 3. Install frontend dependencies

``` bash
npm install
```

### 4. Create the environment file

On Windows PowerShell:

``` powershell
Copy-Item .env.example .env
```

On macOS/Linux:

``` bash
cp .env.example .env
```

### 5. Generate the application key

``` bash
php artisan key:generate
```

### 6. Configure the database

Edit `.env` and set the database connection values. An example MySQL
configuration is shown below. Replace the example database name,
username and password with your own local database details.

``` dotenv
APP_NAME="Ward Inventory Management System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ward_48_inventory
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Keep the generated `APP_KEY` in `.env`; do not leave it blank after
running `php artisan key:generate`.

### 7. Create the local database

Using phpMyAdmin or a MySQL client, create a database matching the
`DB_DATABASE` value. For example:

``` sql
CREATE DATABASE ward_48_inventory
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

### 8. Run database migrations

For a **new, empty database**, run:

``` bash
php artisan migrate
```

Only run migrations against a database whose current schema and
migration history you understand. If you are restoring an existing SQL
backup, read the [Database Migrations and
Seeders](#database-migrations-and-seeders) section first.

### 9. Build frontend assets

``` bash
npm run build
```

### 10. Start the development server

``` bash
php artisan serve
```

Open the local URL printed by Artisan, normally:

``` text
http://127.0.0.1:8000
```

For frontend development with Vite, run this in a separate terminal:

``` bash
npm run dev
```

------------------------------------------------------------------------

## 🗄️ Database Configuration

The project includes Laravel migrations under `database/migrations/` and
seeders under `database/seeders/`. Its database configuration supports
SQLite, MySQL, MariaDB and other Laravel-supported database drivers. For
the intended MySQL setup, use `DB_CONNECTION=mysql` in `.env`.

### Existing SQL files

The repository archive contains:

-   `database/ward_48_inventory.sql`
-   `users_backup.sql`

Treat these as database backup/import files, not as migrations. Before
importing either file:

1.  Back up the target database.
2.  Confirm which file is the correct and most recent source.
3.  Inspect the SQL file and verify the database/schema it targets.
4.  Check whether it contains users, patient records, or other sensitive
    information.
5.  Check whether it includes Laravel's `migrations` table.
6.  Avoid importing a backup over a database that already contains
    records unless you have planned and tested the merge/restore
    process.

**Do not import a full SQL dump and then blindly run every migration.**
The imported database may already contain tables that the migrations
attempt to create.

### Safe migration checks

Check migration status:

``` bash
php artisan migrate:status
```

For a new, empty database, apply migrations:

``` bash
php artisan migrate
```

For production, Laravel requires the force flag:

``` bash
php artisan migrate --force
```

Before running production migrations, take a database backup and review
pending migrations. Never use `php artisan migrate:fresh` or
`php artisan db:wipe` on a database containing records you need to
preserve.

### Seeders

The main `DatabaseSeeder` calls these seeders:

-   `RoleSeeder`
-   `PermissionSeeder`
-   `MasterDataSeeder`
-   `WardInventorySeeder`
-   `BulkCategorySeeder`
-   `DummyBulkPatientSeeder`

Review the seeder code before running it, especially because one seeder
is explicitly named `DummyBulkPatientSeeder`.

To run the configured seeders:

``` bash
php artisan db:seed
```

Run seeders only when appropriate for the target environment. Do not run
demo or dummy-data seeders against a live production database unless
that is intentional and approved.

------------------------------------------------------------------------

## 🚀 Running the Application

For normal local development, use two terminal windows.

**Terminal 1 --- Laravel:**

``` bash
php artisan serve
```

**Terminal 2 --- Vite:**

``` bash
npm run dev
```

Build production frontend assets with:

``` bash
npm run build
```

Clear cached Laravel configuration/views/routes after relevant
configuration changes:

``` bash
php artisan optimize:clear
```

------------------------------------------------------------------------

## ⌨️ Useful Artisan Commands

  ---------------------------------------------------------------------------------------
  Command                                             Purpose
  --------------------------------------------------- -----------------------------------
  `php artisan serve`                                 Start the local Laravel development
                                                      server

  `php artisan route:list`                            Display registered routes

  `php artisan migrate:status`                        Show migration status

  `php artisan migrate`                               Run pending migrations

  `php artisan db:seed`                               Run configured seeders

  `php artisan make:controller ExampleController`     Create a controller

  `php artisan make:model Example -m`                 Create a model and migration

  `php artisan make:migration create_example_table`   Create a migration

  `php artisan optimize:clear`                        Clear Laravel's cached optimization
                                                      files

  `php artisan test`                                  Run the automated test suite
  ---------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 📁 Project Structure

A simplified view of the main Laravel directories:

``` text
Ward-Inventory-System/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
├── bootstrap/
│   └── app.php
├── config/
│   └── database.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── ward_48_inventory.sql
├── public/
│   └── index.php
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── console.php
│   └── web.php
├── storage/
├── tests/
├── artisan
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

-   `app/` --- application controllers, models, middleware and other PHP
    code.
-   `bootstrap/` --- application bootstrapping and middleware aliases.
-   `config/` --- application and service configuration.
-   `database/migrations/` --- database schema changes.
-   `database/seeders/` --- initial and sample data setup.
-   `public/` --- web entry point and public assets.
-   `resources/` --- Blade views, frontend CSS and JavaScript source
    files.
-   `routes/` --- web and console route definitions.
-   `storage/` --- logs, cache, sessions and generated application
    files.
-   `tests/` --- automated tests.

## ☁️ Deployment

The planned hosting arrangement for this project is:

-   **Application:** Vercel
-   **Database:** Railway MySQL

The Laravel application must connect to Railway MySQL using the
connection details supplied by Railway. When Vercel is outside the
Railway private network, configure Railway's public TCP Proxy and use
the proxy hostname and port as `DB_HOST` and `DB_PORT`.

Deployment also requires production environment variables, a PHP runtime
with the MySQL PDO extension, built frontend assets, and a deployment
configuration compatible with Laravel.

See the separate **Vercel + Railway Deployment Guide** prepared for this
repository for the detailed deployment procedure.

Do not commit production `.env` files, database passwords, application
keys, SQL backups, or patient data to GitHub.

## 🔐 Security and Data Protection

-   Keep `.env` out of version control.
-   Never publish `APP_KEY`, database passwords, API tokens or other
    credentials.
-   Set `APP_DEBUG=false` in production.
-   Use HTTPS in production.
-   Restrict access using authentication, role middleware and
    server-side authorization checks.
-   Validate and authorize all requests on the server; hiding a button
    in the interface is not an access-control mechanism.
-   Back up the database before migrations, imports or schema changes.
-   Use least-privilege database credentials where possible.
-   Do not use real patient or confidential hospital information in
    development, demonstration, or public test environments.
-   Before using this system with real hospital data, obtain the
    relevant institutional approval and complete privacy, security,
    backup and operational reviews.

## 🧰 Troubleshooting

### Composer dependencies fail to install

Check the PHP version and required extensions:

``` bash
php -v
composer diagnose
composer install
```

### Database connection fails

-   Confirm MySQL/MariaDB is running.
-   Confirm `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and
    `DB_PASSWORD`.
-   Confirm the database exists.
-   After changing `.env`, clear cached configuration:

``` bash
php artisan config:clear
```

### A table already exists during migration

Do not delete the table immediately. Check:

``` bash
php artisan migrate:status
```

Compare the existing schema and migration history. Back up the database
before attempting a repair.

### Frontend styles or scripts are missing

Install dependencies and rebuild:

``` bash
npm install
npm run build
```

### Storage or cache permission errors

Ensure the web/PHP process can write to Laravel's `storage/` and
`bootstrap/cache/` directories. The exact permission command depends on
the operating system and hosting environment.

------------------------------------------------------------------------

## 🤝 Contributing

1.  Clone the repository.
2.  Create a separate branch for your changes.
3.  Make and test your changes locally.
4.  Review the Git diff and ensure no secrets or local database files
    are included.
5.  Commit with a descriptive message.
6.  Push the branch and open a pull request according to the project's
    team workflow.

Example:

``` bash
git checkout -b feature/your-feature
git add .
git commit -m "Add: describe your change"
git push -u origin feature/your-feature
```

## 📄 License

No project-specific license information was confirmed from the inspected
project files. Add a `LICENSE` file and update this section once the
project owner has selected and approved a license.

------------------------------------------------------------------------

**Repository:**
<https://github.com/AlphaStrategists/Ward-Inventory-System>
