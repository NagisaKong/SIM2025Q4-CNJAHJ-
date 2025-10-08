# CSR Volunteer Matching Platform Prototype

This repository contains a **Node.js + Express + EJS** prototype for a corporate social responsibility (CSR) volunteer matching platform. The experience now requires users to authenticate before viewing the dashboard, keeps the interface minimal, and ships with English copy and demo data for every module.

## Project Structure
- `server.js`: Class-based Express entry point that loads demo data, handles authentication, and renders the dashboard and login pages.
- `views/index.ejs`: Dashboard template featuring tools for administrators, CSR representatives, PIN users, and platform managers.
- `views/login.ejs`: Focused login form with role selection plus username and password fields.
- `public/styles.css`: Global stylesheet with streamlined typography, layout, and responsive rules.
- `data/sampleData.js`: Object-oriented in-memory sample data service (accounts, profiles, opportunities, metrics, etc.).
- `test/validateUAtest.js`: Jest smoke test that verifies the login template retains mandatory form controls.
- `test/app.view.test.js`: Ensures protected views render correctly for authenticated administrators.
- `test/app.error.test.js`: Covers middleware guards such as admin-only access and suspended account blocking.
- `package.json`: Node.js dependencies and scripts.

## Getting Started
1. **Install dependencies**
   ```bash
   npm install
   ```
2. **Start the development server**
   ```bash
   npm run dev
   ```
   `nodemon` watches for changes and serves the app at [http://localhost:3000](http://localhost:3000). For a one-off run, use `npm start`.
3. **Run tests (optional)**
   ```bash
   npm test
   ```
   Executes the Jest suite covering login rendering, dashboard access, and admin guards.
4. **Access the experience**
   - Visit `http://localhost:3000/login` to select a role and enter credentials.
   - After a successful sign-in you are redirected to `http://localhost:3000/dashboard`.
   - Demo APIs are available at `/api/pin-requests`, `/api/csr-history`, `/api/pin-matches`, `/api/service-categories`, and `/api/reports`.

### Sample Credentials
| Role | Username | Password |
| --- | --- | --- |
| User Administrator | `admin.reed` | `admin123` |
| CSR Representative | `csr.wilson` | `csr12345` |
| Person in Need (PIN) | `pin.jordan` | `pin12345` |

## Current Capabilities
- **Authentication first**: the root route redirects to the login form and the dashboard is protected by a session check.
- **Admin workspace**: signed-in user administrators can create, update, search, and suspend user accounts and profiles.
- **Role selection on login**: the form remembers the selected role and username after a failed attempt.
- **Streamlined UI**: cards, tables, and stacked forms provide a lightweight layout focused on data and actions.
- **English demo data**: all sample roles, requests, opportunities, and metrics are authored in English for consistency.

## Page Highlights
1. **Top bar** – displays the signed-in user and a sign-out button.
2. **Platform overview** – describes how the four roles collaborate on the platform.
3. **User administrator workspace** – exposes account and profile creation forms (only for the admin role).
4. **User accounts overview** – searchable table with inline editing and suspension controls.
5. **User profiles overview** – searchable table with editable descriptions, permissions, and statuses.
6. **CSR volunteer activity** – lists open opportunities, shortlist examples, and service history.
7. **PIN engagement insights** – shows current requests, visibility metrics, and completed matches.
8. **Platform operations** – summarises service categories and the latest daily/weekly/monthly reports.
9. **Login page** – role selector plus credential inputs, reminding users to contact the administrator for new accounts.

## Future Improvements
- **Persisted data**: replace `data/sampleData.js` with a database or external API and adopt an ORM/SDK for CRUD operations.
- **Richer interactions**: add front-end scripts or component libraries for filtering, shortlisting, and inline validation.
- **Performance tuning**: minify CSS, enable HTTP caching, and optimise images to improve initial load.
- **Accessibility**: extend WAI-ARIA attributes, keyboard hints, and high-contrast themes.
- **Automated tests**: extend the current Jest suite to cover data helpers and route edge cases for future refactors.
