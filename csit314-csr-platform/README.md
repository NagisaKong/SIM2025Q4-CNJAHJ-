# CSR Match Platform (Boundary-Control-Entity Skeleton)

This directory contains a pure PHP implementation of the CSR platform using the Boundary–Control–Entity paradigm.
All pages are implemented as boundary scripts that orchestrate controllers and entities without any framework dependencies.

## Getting Started

1. **Install PHP 8.2+** with the SQLite PDO extension enabled.
2. **Seed the database**
   ```bash
   php scripts/generate-test-data.php
   ```
3. **Run the development server**
   ```bash
   php -S localhost:8000 -t public
   ```
4. Navigate to `http://localhost:8000/public/index.php` and sign in using one of the seeded accounts:

| Role | Email | Password |
| ---- | ----- | -------- |
| User Admin | admin@example.com | password123 |
| CSR Rep | csr.rep@example.com | password123 |
| Person In Need | pin.user@example.com | password123 |
| Platform Manager | manager@example.com | password123 |

## Project Layout

The repository follows a module-first BCE structure:

```
csit314-csr-platform/
├── config/               # Application configuration
├── public/               # Front controller and static assets
├── src/                  # Boundary/Controller/Entity implementations
│   ├── admin/
│   ├── csr-representative/
│   ├── pin/
│   ├── PM/
│   ├── login/
│   └── shared/
├── scripts/              # CLI utilities
├── storage/              # SQLite database and logs
└── create_data_table.sql # Schema definition
```

## Notes

- Each boundary script owns its logout handler (`logout()`) and renders HTML matching the migrated views.
- Controllers encapsulate validation and persistence, returning scalar results for the boundary to interpret.
- Entities interact directly with the SQLite database via PDO.
