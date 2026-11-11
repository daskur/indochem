# CHANGELOG

## 2026-11-04

### Added
- Foundation folder structure
- Normalized IPLA MySQL schema
- Initial seed data for the eleven specified process areas
- Sample cement process, materials and steps
- PDO database configuration
- Shared constants and PHP helpers
- Reusable header, navbar, footer and sidebar includes
- Responsive CSS and mobile navigation JavaScript
- Project foundation documentation
- Student registration page
- Student and administrator login
- Secure session logout
- Role-aware dashboard
- CSRF-protected registration, login and logout
- Public Home Page with industrial-process hero
- Published-process and latest-news database sections
- Global process search entry point
- Responsive resource navigation cards
- Industrial process listing with search and category filters
- Published-process detail pages
- Process navigation for all content sections
- Individual production-step pages
- Cement process equipment associations
- Authenticated step progress tracking
- Equipment Library listing and category/search filters
- Equipment detail pages with operating and safety sections
- Equipment links to related published industrial processes
- Equipment links to published related videos
- Chemical Library listing with search and physical-state filters
- Chemical detail pages with formula, properties, hazards and storage information
- Chemical reaction and process links derived from reaction names, reactants, products and formulas
- Industrial Calculators index and dedicated calculator routes
- Client-side molarity, normality, molar-mass, dilution, pH, yield and density calculations
- Mass, volume and length unit converter
- Laboratory Safety index and four focused guidance pages
- PPE, hazard recognition, first-aid and fire-safety educational content
- Flow Diagrams listing and detail module
- Search and process filtering for industrial process diagrams
- Related diagram navigation and responsive diagram viewer
- Video Tutorials listing and detail module
- YouTube, Vimeo and external-video support
- Search and process filtering for published video tutorials
- Process and equipment links from video detail pages
- Quiz and Practice listing with search and process filters
- Authenticated quiz attempts with score, percentage and pass/fail results
- Correct-answer review and educational explanations
- Seeded Cement Production practice quiz
- Industrial Glossary with alphabet, category and text search
- Global search across processes, equipment, chemicals, videos, quizzes and glossary terms
- Home-page search routed to the global search module
- Added glossary seed terms for chemistry, production, separation and safety
- Authenticated SIWES Corner with daily logbook CRUD
- SIWES report draft, submission and read-only status views
- Per-user logbook and report ownership enforcement
- Responsive SIWES summary, tables and report cards
- User profile details and password management
- Validated profile-image uploads
- Process progress and quiz history
- Profile learning-summary counts
- Authenticated bookmarks for learning resources
- Bookmark listing and content-type filtering
- Add, remove and stale-content handling
- Bookmark actions on process, equipment, chemical, diagram, video and quiz pages
- Certificate generation for completed processes
- Authenticated certificate collection and printable certificate view
- Public certificate verification by certificate number
- Certificate navigation and responsive certificate styling
- Published news listing, category filtering and detail pages
- Authenticated notification inbox
- Mark-read and mark-all-read notification actions
- Unread notification count in authenticated navigation
- Seeded platform and safety news articles

### Changed
- Updated shared navigation to use the role-aware dashboard and session-secured logout
- Added reusable Home Page card and responsive section styling

### Fixed
- Corrected seed SQL reaction syntax
- Made PHP shared helpers compatible with the installed PHP 7.4 runtime
- Corrected the seed import after schema regeneration
- Improved base URL detection for nested application folders
- Added process and step module responsive styling
- Added Equipment Library cards, detail layouts and responsive navigation
- Added Chemical Library cards, formula headers and responsive detail layouts
- Added responsive calculator cards and calculator result styling
- Added responsive safety cards, document layout and emergency guidance styling

### Current Task
News and Notifications complete; next task is Admin Panel CRUD.
