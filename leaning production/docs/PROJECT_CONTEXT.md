# IPLA PROJECT CONTEXT

## Project
Industrial Process Learning App (IPLA)

## Purpose
An educational platform for learning industrial production processes step by step.

## Technology
- HTML5 and CSS3
- JavaScript
- PHP 8+
- MySQL with PDO
- Apache/XAMPP

## Database
Database name: `ipla`

## Architecture
Modular PHP application following the approved folder structure. Shared configuration lives in `config/`, reusable page markup lives in `includes/`, public feature areas live in top-level folders, and CRUD administration lives in `admin/`.

## Current Phase
Phase 1

## Current Module
Laboratory Safety complete

## Completed
- Project folders
- Core database schema
- Initial seed data
- PDO connection configuration
- Shared constants and functions
- CSRF/session foundations
- Header, navbar, footer and sidebar includes
- Responsive CSS architecture
- Student registration and login
- Secure session login
- Role-aware dashboard
- CSRF-protected forms and logout
- Responsive public Home Page
- Published-process database query
- Home-page search form
- Quick navigation resource cards
- Latest-news section with empty-state handling
- Industrial process listing and search
- Process detail navigation
- Raw materials, equipment, steps, reactions, diagrams, quality, safety and applications
- Individual production-step pages
- Authenticated user progress and completion tracking
- Equipment Library listing and category/search filters
- Equipment detail pages
- Related-process and related-video links
- Chemical Library listing and physical-state/search filters
- Chemical detail pages with hazard, storage and safety sections
- Related process reaction discovery from chemical names and formulas
- Industrial calculator index
- Molarity, normality and molar-mass calculators
- Dilution, pH, percentage-yield and density calculators
- Common mass, volume and length unit converter
- Laboratory Safety index
- PPE, hazard, first-aid and fire-safety guidance pages
- Flow Diagrams listing and detail pages
- Video Tutorials listing and detail pages
- Quiz and Practice listing, attempts and answer review
- Industrial Glossary and global search
- Authenticated SIWES logbook and report management
- User profile, password changes and learning history
- Authenticated bookmarks across learning content
- Certificates generated after process completion with public verification

## In Progress
News and Notifications complete; next module is Admin Panel CRUD

## Pending
- Authentication pages (foundation complete)
- Home Page (complete)
- Industrial Processes (complete)
- Equipment Library (complete)
- Chemical Library (complete)
- Industrial Calculators (complete)
- Laboratory Safety (complete)
- All Phase 2 and Phase 3 modules

## Important Rules
1. Do not destroy existing working code.
2. Read this file and `docs/PROGRESS.md` before changes.
3. Do not duplicate database tables.
4. Reuse shared functions and includes.
5. Use prepared SQL statements.
6. Validate user input.
7. Escape all displayed values.
8. Require authentication for protected pages.
9. Require the admin role for admin pages.
10. Use CSRF protection on state-changing forms.
11. Validate uploads by size, MIME type and extension.
12. Keep admin functionality separate.
13. Update progress and changelog documentation after major work.

## Security Decisions
- Passwords use `password_hash()` and `password_verify()`.
- Database access uses PDO and prepared statements.
- Output uses the `e()` HTML escaping helper.
- Sessions use HTTP-only cookies and Lax SameSite mode.

## Default Local Admin
- Email: `admin@ipla.local`
- Password: `Admin123!`
- Change or remove this account before production deployment.
