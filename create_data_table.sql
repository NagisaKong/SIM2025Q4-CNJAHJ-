-- Schema bootstrap aligned with CSR platform domain
\c csr_platform

-- Drop compatibility views first to avoid dependency conflicts
DROP VIEW IF EXISTS profiles CASCADE;
DROP VIEW IF EXISTS users CASCADE;
DROP VIEW IF EXISTS service_categories CASCADE;
DROP VIEW IF EXISTS pin_requests CASCADE;
DROP VIEW IF EXISTS shortlists CASCADE;

-- Drop tables if they already exist
DROP TABLE IF EXISTS "Shortlists" CASCADE;
DROP TABLE IF EXISTS "Requests" CASCADE;
DROP TABLE IF EXISTS "serviceCategories" CASCADE;
DROP TABLE IF EXISTS "userAccounts" CASCADE;
DROP TABLE IF EXISTS "userProfiles" CASCADE;

-- Core profile definitions
CREATE TABLE "userProfiles" (
    "profileID"      BIGSERIAL PRIMARY KEY,
    "role"           VARCHAR(50) NOT NULL UNIQUE,
    "description"    TEXT NOT NULL,
    "status"         VARCHAR(20) NOT NULL DEFAULT 'active',
    "created_at"     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    "updated_at"     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- User account records referencing the profiles
CREATE TABLE "userAccounts" (
    "accountID"      BIGSERIAL PRIMARY KEY,
    "profileID"      BIGINT NOT NULL REFERENCES "userProfiles" ("profileID") ON DELETE RESTRICT,
    "username"       VARCHAR(120) NOT NULL,
    "email"          VARCHAR(150) NOT NULL UNIQUE,
    "password_hash"  VARCHAR(255) NOT NULL,
    "status"         VARCHAR(20) NOT NULL DEFAULT 'active',
    "registeredDate" TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    "updated_at"     TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- Service category catalogue
CREATE TABLE "serviceCategories" (
    "serviceID"   BIGSERIAL PRIMARY KEY,
    "name"        VARCHAR(120) NOT NULL,
    "description" TEXT NOT NULL,
    "status"      VARCHAR(20) NOT NULL DEFAULT 'active',
    "created_at"  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    "updated_at"  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- Request records created by PINs and visible to CSRs
CREATE TABLE "Requests" (
    "requestID"        BIGSERIAL PRIMARY KEY,
    "pinID"            BIGINT NOT NULL REFERENCES "userAccounts" ("accountID") ON DELETE CASCADE,
    "csrID"            BIGINT REFERENCES "userAccounts" ("accountID") ON DELETE SET NULL,
    "serviceID"        BIGINT NOT NULL REFERENCES "serviceCategories" ("serviceID") ON DELETE RESTRICT,
    "title"            VARCHAR(150) NOT NULL,
    "description"      TEXT NOT NULL,
    "location"         VARCHAR(150) NOT NULL,
    "status"           VARCHAR(20) NOT NULL DEFAULT 'open',
    "requestedDate"    DATE NOT NULL,
    "viewCount"        INTEGER NOT NULL DEFAULT 0,
    "shortlistCount"   INTEGER NOT NULL DEFAULT 0,
    "additionalDetails" TEXT,
    "completedDate"    DATE,
    "created_at"       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    "updated_at"       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- Shortlist links between CSRs and requests
CREATE TABLE "Shortlists" (
    "shortlistID" BIGSERIAL PRIMARY KEY,
    "csrID"       BIGINT NOT NULL REFERENCES "userAccounts" ("accountID") ON DELETE CASCADE,
    "requestID"   BIGINT NOT NULL REFERENCES "Requests" ("requestID") ON DELETE CASCADE,
    "status"      VARCHAR(20) NOT NULL DEFAULT 'active',
    "created_at"  TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    CONSTRAINT uniq_shortlist UNIQUE ("csrID", "requestID")
);

-- ---------------------------------------------------------------------------
-- Seed data (deterministic but realistic for 2024-2025 timeframe)
-- ---------------------------------------------------------------------------

-- Profiles
INSERT INTO "userProfiles" ("role", "description") VALUES
    ('admin', 'Administrator with full access to manage accounts and profiles'),
    ('csr', 'CSR representative handling shortlisted requests and outreach'),
    ('pin', 'Persons in need submitting community service requests'),
    ('pm', 'Project manager overseeing reports and service categories');

-- Accounts (bcrypt hash for Password1)
INSERT INTO "userAccounts" ("profileID", "username", "email", "password_hash", "status", "registeredDate") VALUES
    ((SELECT "profileID" FROM "userProfiles" WHERE "role" = 'admin'), 'Alice Admin', 'admin@example.com', '$2y$12$QMZ1pq9hx5XGGiYlQIq65O0E.lhGY18fcTBh2hnbD16gk2Z4dH/v2', 'active', '2024-01-15 09:30:00'),
    ((SELECT "profileID" FROM "userProfiles" WHERE "role" = 'csr'), 'Carol CSR', 'csr@example.com', '$2y$12$QMZ1pq9hx5XGGiYlQIq65O0E.lhGY18fcTBh2hnbD16gk2Z4dH/v2', 'active', '2024-02-10 11:20:00'),
    ((SELECT "profileID" FROM "userProfiles" WHERE "role" = 'pin'), 'Peter PIN', 'pin@example.com', '$2y$12$QMZ1pq9hx5XGGiYlQIq65O0E.lhGY18fcTBh2hnbD16gk2Z4dH/v2', 'active', '2024-03-05 14:05:00'),
    ((SELECT "profileID" FROM "userProfiles" WHERE "role" = 'pm'), 'Paula PM', 'pm@example.com', '$2y$12$QMZ1pq9hx5XGGiYlQIq65O0E.lhGY18fcTBh2hnbD16gk2Z4dH/v2', 'active', '2024-01-28 08:45:00'),
    ((SELECT "profileID" FROM "userProfiles" WHERE "role" = 'csr'), 'Chris CSR', 'chris.csr@example.com', '$2y$12$QMZ1pq9hx5XGGiYlQIq65O0E.lhGY18fcTBh2hnbD16gk2Z4dH/v2', 'active', '2024-05-17 10:12:00');

-- Service categories
INSERT INTO "serviceCategories" ("name", "description", "status", "created_at") VALUES
    ('Food Assistance', 'Support for food distribution and meal preparation', 'active', '2024-01-05 12:00:00'),
    ('Elder Care', 'In-home visits and assistance for elderly residents', 'active', '2024-02-12 09:00:00'),
    ('Tutoring', 'Educational support and tutoring for students', 'active', '2024-03-18 13:30:00'),
    ('Community Cleanup', 'Neighborhood beautification and cleanup efforts', 'active', '2024-04-02 15:45:00');

-- Requests (PIN initiated)
INSERT INTO "Requests" (
    "pinID", "csrID", "serviceID", "title", "description", "location", "status",
    "requestedDate", "viewCount", "shortlistCount", "additionalDetails", "created_at", "updated_at"
) VALUES
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'pin@example.com'),
     (SELECT "accountID" FROM "userAccounts" WHERE "email" = 'csr@example.com'),
     (SELECT "serviceID" FROM "serviceCategories" WHERE "name" = 'Food Assistance'),
     'Weekly Meal Delivery Support',
     'Looking for volunteers to assist with preparing and delivering meals to families in need.',
     'Downtown District',
     'open',
     '2024-06-03',
     18,
     2,
     'Deliveries occur every Saturday morning.',
     '2024-05-28 09:15:00',
     '2024-06-01 16:45:00'),
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'pin@example.com'),
     (SELECT "accountID" FROM "userAccounts" WHERE "email" = 'chris.csr@example.com'),
     (SELECT "serviceID" FROM "serviceCategories" WHERE "name" = 'Elder Care'),
     'Senior Companionship Visits',
     'Requesting CSR support to schedule weekly companionship visits for isolated seniors.',
     'Riverside Community Center',
     'in_progress',
     '2024-07-12',
     25,
     3,
     'Visits should last approximately two hours each.',
     '2024-06-20 10:40:00',
     '2024-07-05 18:10:00'),
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'pin@example.com'),
     NULL,
     (SELECT "serviceID" FROM "serviceCategories" WHERE "name" = 'Community Cleanup'),
     'Autumn Neighborhood Cleanup',
     'Organising a fall cleanup for the Oakridge neighborhood; need volunteers for trash pickup and recycling.',
     'Oakridge Park',
     'open',
     '2024-09-21',
     9,
     1,
     'Provide gloves and trash bags to all volunteers.',
     '2024-08-30 14:20:00',
     '2024-08-30 14:20:00'),
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'pin@example.com'),
     NULL,
     (SELECT "serviceID" FROM "serviceCategories" WHERE "name" = 'Tutoring'),
     'STEM Tutoring Sessions',
     'Need tutors to support high school students with mathematics and science homework.',
     'Eastside Learning Hub',
     'completed',
     '2025-01-15',
     42,
     4,
     'Sessions scheduled twice a week after school hours.',
     '2024-11-05 11:05:00',
     '2025-01-20 17:30:00');

-- Shortlists connecting CSRs to requests
INSERT INTO "Shortlists" ("csrID", "requestID", "status", "created_at") VALUES
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'csr@example.com'),
     (SELECT "requestID" FROM "Requests" WHERE "title" = 'Weekly Meal Delivery Support'),
     'active',
     '2024-06-02 08:10:00'),
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'chris.csr@example.com'),
     (SELECT "requestID" FROM "Requests" WHERE "title" = 'Senior Companionship Visits'),
     'active',
     '2024-06-25 09:50:00'),
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'csr@example.com'),
     (SELECT "requestID" FROM "Requests" WHERE "title" = 'Autumn Neighborhood Cleanup'),
     'pending',
     '2024-09-05 12:30:00'),
    ((SELECT "accountID" FROM "userAccounts" WHERE "email" = 'csr@example.com'),
     (SELECT "requestID" FROM "Requests" WHERE "title" = 'STEM Tutoring Sessions'),
     'completed',
     '2024-12-10 15:00:00');

-- ---------------------------------------------------------------------------
-- Compatibility views so existing PHP code can continue to query legacy names
-- ---------------------------------------------------------------------------

CREATE OR REPLACE VIEW profiles AS
SELECT
    "profileID" AS id,
    "role",
    "description",
    "status",
    "created_at",
    "updated_at"
FROM "userProfiles";

CREATE OR REPLACE VIEW users AS
SELECT
    "accountID" AS id,
    "profileID",
    "username" AS name,
    "email",
    "password_hash",
    "status",
    "registeredDate" AS created_at,
    "updated_at"
FROM "userAccounts";

CREATE OR REPLACE VIEW service_categories AS
SELECT
    "serviceID" AS id,
    "name",
    "description",
    "status",
    "created_at",
    "updated_at"
FROM "serviceCategories";

CREATE OR REPLACE VIEW pin_requests AS
SELECT
    "requestID" AS id,
    "pinID" AS pin_id,
    "serviceID" AS category_id,
    "title",
    "description",
    "location",
    "status",
    "requestedDate" AS requested_date,
    "viewCount" AS views_count,
    "shortlistCount" AS shortlist_count,
    "additionalDetails" AS additional_details,
    "created_at",
    "updated_at"
FROM "Requests";

CREATE OR REPLACE VIEW shortlists AS
SELECT
    "shortlistID" AS id,
    "csrID" AS csr_id,
    "requestID" AS request_id,
    "status",
    "created_at"
FROM "Shortlists";

