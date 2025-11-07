SWEEP (Solid Waste Evaluation and Efficiency Platform) System Architecture

Framework: Laravel (latest version)
Database: MariaDB
Environment: Localhost (development phase only)
Hosting: None (offline MVP)
Notifications: Not included in MVP

System Overview:
SWEEP is a centralized web-based management system that allows administrators, collection crews, and residents to collaborate in managing community waste collection. It provides scheduling, route tracking, reporting, and recycling data in one platform. The system aims to make waste management operations more efficient, transparent, and data-driven.

User Roles:

Administrator – manages users, routes, schedules, and reports, and monitors all activities.

Collection Crew/Driver – views assigned routes and logs completed collections.

Resident – views schedules, submits complaints, and tracks report status.

Core Modules and Features:

User Management – handles authentication, role-based access, and user data.

Waste Collection Schedule – stores and displays pickup schedules per area or route.

Truck Assignment – assigns trucks and drivers to specific zones or schedules.

Collection Logs – allows crews to mark routes as completed and add notes or photos.

Resident Reports – lets residents submit complaints or missed pickup reports with images and locations.

Recycling Tracker – records recyclable materials collected per zone or date.

Dashboard and Analytics – provides basic data visualization for admin (total collections, pending reports, recycling rates).

User Roles and Functions:
Administrator

Create and manage accounts for crew and residents

Add, edit, or delete routes and schedules

Assign trucks and monitor their progress

Review and update report statuses

Generate reports on collection and recycling performance

Access dashboard analytics

Collection Crew

View assigned routes and schedules

Mark collection points as completed

Upload photos as proof of collection

Report route issues (blocked road, truck problems)

View past collection logs

Resident

View garbage collection schedule for their area

Submit complaints with photo and description

Track the status of submitted reports

View announcements or waste management tips from admin

Database Tables:
users – stores all user accounts and role information
roles – defines access levels (admin, crew, resident)
routes – stores routes and zone details
schedules – defines collection days and times for each route
trucks – stores truck information and operational status
assignments – links drivers and trucks to specific routes
collections – records collection activity, completion status, and notes
reports – stores resident complaints with status and photo path
recycling_logs – records recyclable material type, weight, and collection date

System Flow:
Administrator creates schedules and assigns routes and trucks.
Collection crews log in, view their route, and mark each area as collected after completion.
Residents can check their collection schedules and report uncollected garbage or issues.
Admin monitors all activities on the dashboard and updates report statuses.
System generates simple analytics (number of reports, completed routes, recycling data).

User Interface and Experience:
The system uses a clean, minimal, and responsive design built with Laravel Blade and Bootstrap 5.
Primary color: Forest Green (#2E8B57) – symbolizes sustainability.
Secondary color: Amber (#F4A300) – used for alerts or calls to action.
Accent color: Teal (#4FB4A2) – for buttons and highlights.
Background color: Off-white (#F9FAFB) – clean and easy on the eyes.
Text color: Dark Gray (#333333) – ensures readability.

Admin dashboard includes sidebar navigation (Dashboard, Routes, Reports, Recycling, Users, Settings).
Crew dashboard displays assigned route, collection status, and photo upload form.
Resident view includes schedule calendar and complaint form with report tracking.
All pages are optimized for desktop and mobile viewing.

Laravel Packages to Use:
Laravel Breeze – for authentication scaffolding
Spatie Laravel Permission – for role and permission management
Laravel Charts – for simple dashboard visualization
Intervention Image – for image upload and resizing