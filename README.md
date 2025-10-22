# CSIT314 CSR Platform

This repository contains a lightweight CSR matching platform implemented with native PHP. The application avoids any framework or shared core and organises logic by roles using the boundary-controller-entity pattern.

## Project structure

```
csit314-csr-platform/
├── config/
├── public/
├── src/
│   ├── admin/
│   ├── csr-representative/
│   ├── pin/
│   ├── PM/
│   ├── login/
│   └── shared/
└── scripts/
```

Refer to the `config/` directory for database and application settings. PostgreSQL is required for data persistence.

## Getting started

1. Install dependencies:
   ```bash
   composer install
   ```
2. Create the PostgreSQL schema:
   ```bash
   psql -U csr_user -d csr_platform -f create_data_table.sql
   ```
3. (Optional) Seed demo data:
   ```bash
   php scripts/generate-test-data.php
   ```
4. Start the PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Testing

Execute PHPUnit tests with:

```bash
composer test
```

## Licensing

This project is provided for academic use in CSIT314 labs. No commercial license is granted.
