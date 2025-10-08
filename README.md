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
