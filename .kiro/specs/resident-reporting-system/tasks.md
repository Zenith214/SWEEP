# Implementation Plan: Resident Reporting System

- [x] 1. Install and configure dependencies





  - Install Intervention Image package via Composer
  - Configure storage disk for report photos in config/filesystems.php
  - Create directory structure for photos and thumbnails in storage
  - Set up symbolic link for public photo access if needed
  - _Requirements: 2.4, 2.5_

- [x] 2. Create database migrations for resident reporting






  - [x] 2.1 Create reports table migration

    - Add columns: reference_number (unique), resident_id (FK), report_type (enum), location, description, status (enum), route_id (FK, nullable), assigned_to (FK, nullable), resolved_at
    - Add foreign key constraints to users and routes tables
    - Add indexes on reference_number, resident_id, status, report_type, created_at, route_id, assigned_to
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 3.2, 3.4, 9.1, 9.3, 11.1, 11.3_
  

  - [x] 2.2 Create report_photos table migration

    - Add columns: report_id (FK), file_path, file_name, file_size, uploaded_at
    - Add foreign key constraint to reports table with cascade delete
    - Add index on report_id
    - _Requirements: 2.1, 2.4, 2.5_
  

  - [x] 2.3 Create report_responses table migration

    - Add columns: report_id (FK), admin_id (FK to users), response
    - Add foreign key constraints with cascade delete on report_id
    - Add indexes on report_id and admin_id
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  

  - [x] 2.4 Create report_status_history table migration

    - Add columns: report_id (FK), old_status (enum, nullable), new_status (enum), changed_by (FK to users), note, created_at
    - Add foreign key constraints with cascade delete on report_id
    - Add indexes on report_id and changed_by
    - Disable updated_at timestamp (only created_at needed)
    - _Requirements: 6.1, 6.2, 6.4, 6.5, 9.3, 9.4_
  
  - [x] 2.5 Run migrations


    - Execute php artisan migrate to create all tables
    - Verify table structure in database
    - _Requirements: All database-related requirements_

- [x] 3. Create Eloquent models with relationships






  - [x] 3.1 Create Report model

    - Define fillable fields and casts
    - Add constants for report types and statuses
    - Implement relationships: resident(), route(), assignedTo(), photos(), responses(), statusHistory()
    - Add query scopes: pending(), inProgress(), resolved(), forDateRange()
    - Implement helper methods: isPending(), isResolved(), getResolutionTime(), generateReferenceNumber()
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 3.4, 4.2, 4.3, 9.1, 9.2, 11.1, 11.2_
  

  - [x] 3.2 Create ReportPhoto model

    - Define fillable fields and casts
    - Implement relationship: report()
    - Add helper methods: getUrl(), getThumbnailUrl(), getFileSizeFormatted()
    - _Requirements: 2.1, 2.4, 2.5_
  
  - [x] 3.3 Create ReportResponse model


    - Define fillable fields
    - Implement relationships: report(), admin()
    - _Requirements: 10.1, 10.2, 10.3_
  

  - [x] 3.4 Create ReportStatusHistory model

    - Define fillable fields and casts
    - Disable updated_at timestamp
    - Implement relationships: report(), changedBy()
    - _Requirements: 6.1, 6.2, 6.4, 6.5, 9.3_

- [x] 4. Create service classes for business logic






  - [x] 4.1 Create ReportService

    - Implement createReport() with unique reference number generation
    - Implement updateStatus() with history recording
    - Implement addResponse() for admin responses
    - Implement assignReport() for route/crew assignment
    - Implement getResidentReports() with filtering
    - Implement getReportsWithFilters() for admin view
    - Implement searchByReference() for resident search
    - _Requirements: 1.3, 1.4, 4.1, 4.2, 4.4, 9.1, 9.3, 9.4, 10.3, 11.1, 11.3, 15.2, 15.3_
  
  - [x] 4.2 Create ReportPhotoService


    - Implement uploadPhoto() with file storage
    - Implement createThumbnail() using Intervention Image (200x200px)
    - Implement deletePhoto() for cleanup
    - Implement validatePhotoCount() to enforce 3-photo limit
    - Implement getPhotoUrl() and getThumbnailUrl()
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  
  - [x] 4.3 Create ReportAnalyticsService


    - Implement getReportsByLocation() for location grouping
    - Implement getReportsByType() with counts and percentages
    - Implement getAverageResolutionTime() calculation
    - Implement getResolutionTimeByType() breakdown
    - Implement getOverdueReports() with configurable threshold
    - Implement getTypeDistribution() for charts
    - Implement getStatusDistribution() for charts
    - Implement getLocationHotspots() for problem area identification
    - _Requirements: 12.1, 12.2, 12.3, 13.1, 13.2, 13.3, 14.1, 14.2, 14.3, 14.4_

- [x] 5. Create form request validation classes






  - [x] 5.1 Create StoreReportRequest

    - Validate report_type (required, enum values)
    - Validate location (required, string, max 255)
    - Validate description (required, string, max 2000)
    - Validate photos (nullable, array, max 3 items)
    - Validate each photo (image, mimes: jpeg,png,webp, max 5120KB)
    - _Requirements: 1.2, 2.1, 2.2, 2.3, 3.1, 3.3_
  

  - [x] 5.2 Create UpdateStatusRequest

    - Validate status (required, enum values)
    - Validate note (required, string, max 1000)
    - _Requirements: 9.1, 9.2, 9.4_
  
  - [x] 5.3 Create AddResponseRequest


    - Validate response (required, string, max 1000)
    - _Requirements: 10.1, 10.4_
  

  - [x] 5.4 Create AssignReportRequest

    - Validate route_id (nullable, exists in routes table)
    - Validate assigned_to (nullable, exists in users table)
    - Add custom validation: at least one field required
    - Add custom validation: assigned_to must be collection_crew role
    - _Requirements: 11.1, 11.2, 11.5_

- [x] 6. Create resident-facing controllers and routes




  - [x] 6.1 Create ResidentReportController


    - Implement index() to display resident's reports with status filter
    - Implement create() to show report submission form
    - Implement store() to create report with photos using ReportService and ReportPhotoService
    - Implement show() to display report details (authorize: own reports only)
    - Implement search() to find reports by reference number
    - Add middleware: auth, role:resident
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5, 15.1, 15.2, 15.3, 15.5_
  
  - [x] 6.2 Add resident report routes to web.php


    - GET /resident/reports (index)
    - GET /resident/reports/create (create form)
    - POST /resident/reports (store)
    - GET /resident/reports/search (search)
    - GET /resident/reports/{report} (show)
    - Apply middleware: auth, role:resident
    - _Requirements: 1.1, 4.1, 5.1, 15.1_

- [x] 7. Create admin-facing controllers and routes





  - [x] 7.1 Create AdminReportController

    - Implement index() to display all reports with filters (status, type, date range)
    - Implement show() to display detailed report view
    - Implement updateStatus() to change status with note
    - Implement addResponse() to add admin response
    - Implement assign() to link report to route/crew
    - Implement unassign() to remove assignment
    - Add middleware: auth, role:administrator
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5, 9.1, 9.2, 9.3, 9.4, 9.5, 10.1, 10.2, 10.3, 10.4, 10.5, 11.1, 11.2, 11.3, 11.4, 11.5_
  

  - [x] 7.2 Create ReportAnalyticsController

    - Implement index() to display analytics dashboard
    - Implement locationAnalysis() for location-based view
    - Implement typeAnalysis() for type-based view
    - Implement getTypeDistribution() API endpoint for AJAX
    - Implement getResolutionTimes() API endpoint for AJAX
    - Implement getStatusTrend() API endpoint for AJAX
    - Add middleware: auth, role:administrator
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 13.1, 13.2, 13.3, 13.4, 13.5, 14.1, 14.2, 14.3, 14.4, 14.5_
  

  - [x] 7.3 Add admin report routes to web.php

    - GET /admin/reports (index)
    - GET /admin/reports/{report} (show)
    - PATCH /admin/reports/{report}/status (update status)
    - POST /admin/reports/{report}/responses (add response)
    - PATCH /admin/reports/{report}/assign (assign)
    - PATCH /admin/reports/{report}/unassign (unassign)
    - GET /admin/analytics/reports (analytics dashboard)
    - GET /admin/analytics/reports/location (location analysis)
    - GET /admin/analytics/reports/type (type analysis)
    - GET /admin/analytics/reports/type-distribution (AJAX)
    - GET /admin/analytics/reports/resolution-times (AJAX)
    - GET /admin/analytics/reports/status-trend (AJAX)
    - Apply middleware: auth, role:administrator
    - _Requirements: 7.1, 8.1, 9.1, 10.1, 11.1, 12.1, 13.1, 14.1_

- [x] 8. Create resident views for report submission and tracking




  - [x] 8.1 Create report submission form view (resources/views/resident/reports/create.blade.php)


    - Report type selection (radio buttons or dropdown)
    - Location input field
    - Description textarea with character counter (max 2000)
    - Photo upload area with drag & drop support (max 3 photos)
    - Photo preview thumbnails with remove option
    - Submit and cancel buttons
    - Display validation errors
    - _Requirements: 1.1, 1.2, 2.1, 2.2, 3.1, 3.2_
  
  - [x] 8.2 Create report submission success view or flash message


    - Display reference number prominently
    - Success message
    - Link to view report
    - Link to submit another report
    - _Requirements: 1.4, 1.5_
  
  - [x] 8.3 Create reports list view (resources/views/resident/reports/index.blade.php)


    - Display all resident's reports in table or card format
    - Show: reference number, type, location, date, status
    - Status badges with color coding (pending: yellow, in_progress: blue, resolved: green, closed: gray)
    - Filter by status dropdown
    - Search by reference number input
    - Click to view details
    - Sort by newest first
    - Pagination
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 15.1, 15.2_
  
  - [x] 8.4 Create report details view (resources/views/resident/reports/show.blade.php)


    - Reference number header with status badge
    - Report type and location
    - Submission date
    - Description
    - Photo gallery with lightbox/modal for full-size viewing
    - Status history timeline with timestamps and admin names
    - Administrator responses section (chronological order)
    - Assignment information (route and crew if assigned)
    - Back to list button
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 6.2, 6.3, 6.4, 6.5, 10.4, 11.4_

- [x] 9. Create admin views for report management





  - [x] 9.1 Create reports index view (resources/views/admin/reports/index.blade.php)


    - Data table with columns: Reference, Resident, Type, Location, Date, Status, Actions
    - Filters: date range picker, status dropdown, report type dropdown
    - Search by reference number or resident name
    - Status badges with color coding
    - Pagination
    - Click row or view button to see details
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [x] 9.2 Create report details view (resources/views/admin/reports/show.blade.php)


    - Reference number and status header
    - Resident information (name, contact details)
    - Report details (type, location, description, date)
    - Photo gallery with full-size viewing
    - Status history timeline
    - Update status form with status dropdown and note textarea
    - Add response form with response textarea
    - Assignment section with route dropdown and crew dropdown
    - Action buttons: Update Status, Add Response, Assign/Update Assignment, Remove Assignment
    - Back to list button
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 9.1, 9.2, 9.4, 10.1, 10.4, 11.1, 11.2, 11.4, 11.5_

- [x] 10. Create admin analytics views






  - [x] 10.1 Create analytics dashboard view (resources/views/admin/analytics/reports/index.blade.php)


    - Date range selector
    - Key metrics cards: Total reports, Pending reports, Average resolution time, Resolution rate
    - Report trend chart (line chart using Chart.js)
    - Type distribution chart (pie chart)
    - Status distribution chart (bar chart)
    - Resolution time by type table
    - Overdue reports list with links to details
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 14.1, 14.2, 14.3, 14.4, 14.5_
  
  - [x] 10.2 Create location analysis view (resources/views/admin/analytics/reports/location.blade.php)


    - Date range selector
    - List or table of locations with report counts
    - Highlight hotspots (locations with 3+ reports)
    - Sort by report count (descending)
    - Click location to view all reports for that location
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_
  
  - [x] 10.3 Create type analysis view (resources/views/admin/analytics/reports/type.blade.php)




    - Date range selector
    - Type distribution chart (pie or bar chart)
    - Table showing count and percentage per type
    - Average resolution time per type
    - Click type to view all reports of that type
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

- [x] 11. Update navigation and dashboard links




  - [x] 11.1 Add "My Reports" link to resident navigation

    - Add link in resources/views/layouts/navigation.blade.php for resident role
    - Add link in resident dashboard (resources/views/dashboards/resident.blade.php)
    - _Requirements: 4.1_
  
  - [x] 11.2 Add "Reports" and "Report Analytics" links to admin navigation


    - Add links in resources/views/layouts/navigation.blade.php for administrator role
    - Add links in admin dashboard (resources/views/dashboards/admin.blade.php)
    - _Requirements: 7.1, 12.1, 13.1, 14.1_

- [x] 12. Create database seeders for sample data





  - Create ReportSeeder with sample reports for testing
  - Include various report types, statuses, and dates
  - Include sample photos, responses, and status history
  - Link some reports to existing routes and users
  - _Requirements: All requirements (for testing)_

- [x] 13. Write feature tests for report functionality




  - Test report submission by resident with photos
  - Test report listing and filtering for resident
  - Test report search by reference number
  - Test report viewing authorization (own reports only)
  - Test admin report listing with filters
  - Test status updates with history recording
  - Test response addition
  - Test report assignment to route/crew
  - Test analytics calculations
  - _Requirements: All requirements_

