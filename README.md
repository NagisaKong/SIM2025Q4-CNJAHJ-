# CSR Match Platform (Pure PHP)

This project implements a CSR volunteer matching platform using a custom MVC stack that follows the Boundary–Control–Entity (BCE) methodology. It is written in pure PHP 8.2 with PDO and is designed for coursework that mandates object-oriented middleware, RBAC, and evidence of agile/TDD practices.

## Features

- Custom lightweight framework with Router, Controller, Auth, Session, Validator, and CSRF utilities.
- Role-based access control using user accounts and profiles (User Admin, CSR Rep, Person In Need, Platform Manager).
- User Admin console for managing accounts, profiles, suspension, and audit logs.
- CSR Rep workspace for browsing requests, viewing details, maintaining shortlists, and reviewing service history.
- PIN portal for creating and managing help requests plus tracking views and shortlist counts.
- Platform Manager tools for managing service categories and generating daily/weekly/monthly CSV reports.
- Reporting and seed scripts that populate 100+ records per data type for classroom demos.
- PHPUnit test suite covering authentication, request workflows, and reporting services.

## Getting Started

1. **Install dependencies**
   ```bash
   composer install
   ```
2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```
3. **Configure database**
   Update `.env` with your PDO DSN. The default configuration now targets PostgreSQL (`pgsql:host=127.0.0.1;port=5432;dbname=csr_platform`). You can still point the DSN to MySQL or SQLite for local experiments if needed.

4. **Run migrations and seeders**
   ```bash
   php scripts/migrate.php
   php scripts/generate_test_data.php
   ```

5. **Serve the application**
   ```bash
   php -S localhost:8000 -t public
   ```

6. **Run the automated tests**
   ```bash
   composer test
   ```

## Windows Environment Setup Guide

Follow the steps below to stand up the project on a Windows 10/11 machine. The flow mirrors the "Getting Started" section, but also covers installing the required tooling on Windows.

1. **Install PHP 8.2**
   - Download the *Thread Safe* x64 build of PHP 8.2 from [windows.php.net/download](https://windows.php.net/download/).
   - Extract the archive to a directory such as `C:\php` and add that directory to your system `PATH` environment variable.
   - Copy `php.ini-development` to `php.ini`, then enable the following extensions inside the file: `extension=openssl`, `extension=pdo_pgsql`, `extension=pdo_mysql`, and `extension=sqlite3` (uncomment the relevant lines). Enabling all three PDO drivers makes it easy to point the DSN to PostgreSQL, MySQL, or SQLite as needed.

2. **Install Composer**
   - Download and run the Composer installer from [getcomposer.org](https://getcomposer.org/download/).
   - When prompted, point the installer at the `php.exe` you configured in step 1. This will add the `composer` command to your `PATH`.

3. **Install Git**
   - Grab the latest Git for Windows installer from [git-scm.com](https://git-scm.com/download/win) and accept the defaults so that `git` is available in both Command Prompt and PowerShell.

4. **Clone the repository**
   ```powershell
   git clone https://example.com/your-org/csr-match-platform.git
   cd csr-match-platform
   ```

5. **Install project dependencies**
   ```powershell
   composer install
   ```

6. **Prepare environment configuration**
   ```powershell
   copy .env.example .env
   ```
   Update the `.env` file with the DSN that matches your local database. For PostgreSQL on Windows using a default installation, the DSN is typically `pgsql:host=127.0.0.1;port=5432;dbname=csr_platform`.

7. **Set up the database**
   - Install the database engine of your choice (e.g., [PostgreSQL for Windows](https://www.postgresql.org/download/windows/)).
   - Create an empty database named `csr_platform` (or any name that matches your DSN settings).
   - Run the migration and seeding scripts:
     ```powershell
     php scripts/migrate.php
     php scripts/generate_test_data.php
     ```

8. **Serve the application locally**
   ```powershell
   php -S localhost:8000 -t public
   ```
   Then visit `http://localhost:8000` in your browser.

9. **Run the automated tests (optional)**
   ```powershell
   composer test
   ```

Troubleshooting tips:
- If PowerShell does not recognize `php` or `composer`, reopen the terminal so that it reloads your PATH changes.
- Ensure the required PHP extensions (`openssl`, `pdo_pgsql`, `pdo_mysql`, `sqlite3`) are enabled; otherwise migrations may fail when connecting to your database.

## Project Layout

```
project-root/
├─ public/                     # Front controller and assets
├─ app/
│  ├─ Core/                    # Framework kernel
│  ├─ Controllers/             # Boundary layer
│  ├─ Http/Middleware/         # Request guards
│  ├─ Models/                  # Entity layer
│  ├─ Repositories/            # Persistence helpers
│  ├─ Services/                # Control layer
│  └─ Views/                   # Templates
├─ config/                     # Routes, database, roles
├─ database/                   # Migrations, seeders
├─ scripts/                    # CLI utilities (migrate, seed)
├─ tests/                      # PHPUnit suite
└─ storage/logs/               # Application logs
```

## Demo Credentials

Seed data creates the following sample accounts:

| Role | Username | Password |
| ---- | -------- | -------- |
| User Admin | admin@example.com | password123 |
| CSR Rep | csr.rep@example.com | password123 |
| Person In Need | pin.user@example.com | password123 |
| Platform Manager | manager@example.com | password123 |

## License

Educational use only.
