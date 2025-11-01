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
2. Create the PostgreSQL schema and seed data:
   ```bash
   psql -U csr_user -d csr_platform -f create_data_table.sql
   ```
3. Start the PHP built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Demo accounts

The seed data created by `create_data_table.sql` provisions these credentials:

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@example.com` | `Password1` |
| CSR Representative | `csr@example.com` | `Password1` |
| Person in Need | `pin@example.com` | `Password1` |
| Project Manager | `pm@example.com` | `Password1` |

## Testing

Execute PHPUnit tests with:

```bash
composer test
```

## Licensing

This project is provided for academic use in CSIT314 labs. No commercial license is granted.
