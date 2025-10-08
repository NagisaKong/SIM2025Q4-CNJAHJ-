# CSR Volunteer Matching Platform

A minimalist Django 5 application that connects corporate CSR representatives with persons in need while enabling administrator and platform manager workflows. The system follows a Boundary-Control-Entity separation: Django templates/forms act as boundaries, class-based views and dedicated services provide control logic, and Django ORM models represent entities.

## Quick start

```bash
python -m venv .venv
source .venv/bin/activate  # Windows: .venv\Scripts\activate
pip install -r requirements.txt
python manage.py migrate
python manage.py createsuperuser  # optional admin access
python manage.py seed_demo        # load demo accounts & data
python manage.py runserver
```

Demo accounts created by `seed_demo` share the password `Passw0rd!`:

| Role | Username | Access |
| --- | --- | --- |
| User Admin | admin1 | `/admin/accounts/` & `/admin/profiles/` |
| CSR Rep | csr1 | `/csr/requests/` |
| Person in Need | pin1 | `/pin/requests/` |
| Platform Manager | manager1 | `/manager/categories/`, `/manager/reports/` |

For PostgreSQL-based local development you can start the optional stack:

```bash
docker compose up --build
```

## Running tests

```bash
python manage.py test
```

The suite covers core entities, access control, and representative/person-in-need happy paths.

## Architecture notes

- **Entities:** `ServiceCategory`, `HelpRequest`, `ShortlistItem`, `MatchRecord`, `UserProfile`, `AccountAuditLog`.
- **Control services:** `AccountService`, `CsrService`, `PinService`, `ReportService` encapsulate transactional use-cases consumed by class-based views.
- **Boundaries:** Django templates with Tailwind CDN & minimal CSS, plus forms that validate user input.
- **Admin reuse:** Django admin registers every entity to keep back-office CRUD trivial.

## Acceptance criteria & routes

Each user story pairs Given/When/Then acceptance criteria with the primary route/template.

### User Administrator

| Story | Acceptance criteria | Route & template |
| --- | --- | --- |
| Login | Given the admin is registered, When they submit valid credentials, Then they are redirected to `/` with dashboard access. | `accounts/login/` → `registration/login.html` |
| Create account | Given an admin on `/admin/accounts/create/`, When they submit valid account + profile data, Then a new user/profile is persisted and listed. | `/admin/accounts/create/` → `core/accounts/account_form.html` |
| View accounts | Given an admin visits the accounts list, When the page loads, Then all users appear with status & role columns. | `/admin/accounts/` → `core/accounts/account_list.html` |
| Update account | Given an admin views an account detail, When they edit and submit changes, Then the user & profile fields update. | `/admin/accounts/<id>/edit/` → `core/accounts/account_form.html` |
| Suspend account | Given an admin on an account detail, When they press Suspend, Then the user becomes inactive and login is blocked. | `/admin/accounts/<id>/` → `core/accounts/account_detail.html` |
| Search accounts | Given an admin enters keywords, When they submit, Then the list filters to matching usernames/emails. | `/admin/accounts/?q=...` |
| Create profile | Covered by account create; profile data is captured simultaneously. | `/admin/accounts/create/` |
| View profiles | Given an admin visits profiles list, When the page loads, Then each profile displays role/status. | `/admin/profiles/` → `core/accounts/profile_list.html` |
| Update profile | Given an admin edits a profile, When they submit, Then role/description/groups update. | `/admin/profiles/<id>/edit/` → `core/accounts/profile_form.html` |
| Suspend profile | Given a profile form, When the admin unticks "is active", Then the profile deactivates and access is denied. | `/admin/profiles/<id>/edit/` |
| Search profiles | Given an admin searches on `/admin/profiles/`, When they submit, Then results filter by username/role/description. | `/admin/profiles/?q=...` |
| Logout | Given an authenticated admin, When they hit Logout, Then the session ends and they return to login. | `/accounts/logout/` |

### CSR Representative

| Story | Acceptance criteria | Route & template |
| --- | --- | --- |
| Login | Given a CSR account, When they authenticate, Then they reach the dashboard. | `accounts/login/` |
| Search opportunities | Given the CSR on the browse page, When they filter by category/location/date/keyword, Then matching requests are listed. | `/csr/requests/` → `core/csr/request_list.html` |
| View request detail | Given a CSR opens a request, When the page loads, Then full description plus analytics display and view count increments. | `/csr/requests/<id>/` → `core/csr/request_detail.html` |
| Shortlist request | Given a CSR views a request, When they click “Add to shortlist”, Then the request appears in their shortlist and count increases. | `/csr/requests/<id>/shortlist/` |
| View shortlist | Given the CSR opens their shortlist, When it loads, Then saved requests show with quick links. | `/csr/shortlist/` → `core/csr/shortlist_list.html` |
| Search shortlist | Given a CSR enters a keyword, When they submit, Then shortlist rows filter accordingly. | `/csr/shortlist/?keyword=...` |
| Completed history | Given a CSR visits history, When they filter by category/date, Then only matching completed matches display. | `/csr/history/` → `core/csr/history_list.html` |
| Logout | Same as other roles via `/accounts/logout/`. | `accounts/logout/` |

### Person in Need (PIN)

| Story | Acceptance criteria | Route & template |
| --- | --- | --- |
| Login | Given a PIN account, When they sign in, Then the dashboard renders. | `accounts/login/` |
| Create request | Given they visit the create form, When valid data is submitted, Then a new help request exists with status. | `/pin/requests/create/` → `core/pin/request_form.html` |
| View requests | Given the PIN visits the list, When the page loads, Then each request shows status, views, shortlist counts. | `/pin/requests/` → `core/pin/request_list.html` |
| Update request | Given they select Edit, When they change fields and submit, Then the request updates. | `/pin/requests/<id>/edit/` |
| Delete request | Given they choose Delete, When they confirm, Then the request disappears from the list. | `/pin/requests/<id>/delete/` → `core/pin/request_confirm_delete.html` |
| Search requests | Given a keyword search, When submitted, Then only matching titles display. | `/pin/requests/?keyword=...` |
| View analytics | Provided on the list page via Views/Shortlists columns updated automatically. | `/pin/requests/` |
| Completed history | Given the PIN visits history, When they filter by category/date, Then completed matches show CSR info. | `/pin/history/` → `core/pin/history_list.html` |
| Logout | `/accounts/logout/` |

### Platform Manager

| Story | Acceptance criteria | Route & template |
| --- | --- | --- |
| Login | Given a manager account, When they sign in, Then dashboard renders. | `accounts/login/` |
| Create category | Given the manager opens create form, When they submit, Then the category appears in list. | `/manager/categories/create/` → `core/catalog/category_form.html` |
| View categories | Given they open the list, When the page loads, Then categories show name/status. | `/manager/categories/` → `core/catalog/category_list.html` |
| Update category | Given they edit a category, When saved, Then the attributes update. | `/manager/categories/<id>/edit/` |
| Suspend category | Given they toggle is_active off, When saved, Then category hides from active filters. | `/manager/categories/<id>/edit/` |
| Search categories | Given filters are applied, When submitted, Then list matches keyword/status selections. | `/manager/categories/?keyword=...` |
| Daily report | Given manager selects Daily, When they submit, Then table summarizes accepted/completed counts per day. | `/manager/reports/` → `core/reports/report_view.html` |
| Weekly report | Same flow with Weekly option summarizing ISO weeks. | `/manager/reports/` |
| Monthly report | Same flow with Monthly option summarizing months. | `/manager/reports/` |
| Export CSV | Given results exist, When Export CSV is pressed, Then a CSV download starts. | `/manager/reports/export/` |
| Logout | `/accounts/logout/` |

## Seeded dataset

`python manage.py seed_demo` provisions roughly thirty help requests alongside five users per role and realistic shortlist/match relationships for demos and classroom walkthroughs.

## Suggested improvements

- Integrate real-time notifications (email/SMS) when CSR reps shortlist or accept requests.
- Replace Tailwind CDN with a compiled subset for production hardening.
- Extend the reporting service with cumulative metrics (hours volunteered, response times).
- Add REST APIs (Django REST Framework) to support mobile or SPA clients.
