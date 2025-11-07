# Implementation Plan: Collection Logging

- [ ] 1. Install and configure Intervention Image package
  - Install Intervention Image via Composer
  - Publish configuration if needed
  - Configure image driver (GD or Imagick)
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [ ] 2. Configure file storage for collection photos
  - Create storage disk configuration for collection photos
  - Create directory structure for photos and thumbnails
  - Configure public access for photo viewing
  - Set up symbolic link if needed
  - _Requirements: 3.4, 3.5_

- [ ] 3. Create database migrations for collection logging
  - [ ] 3.1 Create collection_logs table migration
    - Add columns: assignment_id (FK, unique), completion_time, status (enum), issue_type, issue_description, completion_percentage, crew_notes, created_by (FK), edited_at
    - Add foreign key constraints to assignments and users tables
    - Add indexes on assignment_id, status, created_by, created_at
    - _Requirements: 1.3, 2.2, 2.3, 2.4, 4.4, 5.3, 12.5_
  
  - [ ] 3.2 Create collection_photos table migration
    - Add columns: collection_log_id (FK), file_path, file_name, file_size, uploaded_at
    - Add foreign key constraint to collection_logs table
    - Add index on collection_log_id
    - _Requirements: 3.1, 3.4, 3.5_
  
  - [ ] 3.3 Create admin_notes table migration
    - Add columns: collection_log_id (FK), admin_id (FK to users), note
    - Add foreign key constraints
    - Add indexes on collection_log_id and admin_id
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

- [ ] 4. Create Eloquent models with relationships
  - [ ] 4.1 Create CollectionLog model
    - Add fillable fields and casts
    - Define status and issue type constants
    - Define assignment(), creator(), photos(), and adminNotes() relationships
    - Implement completed() scope
    - Implement withIssues() scope
    - Implement forDateRange() scope
    - Implement isEditable() method (2-hour window check)
    - Implement canBeEditedBy() method
    - Implement isCompleted() method
    - Implement hasIssue() method
    - Implement getEditTimeRemaining() method
    - _Requirements: 1.3, 2.2, 2.4, 4.4, 5.3, 6.4, 7.3, 8.3, 12.1, 12.2, 12.5_
  
  - [ ] 4.2 Create CollectionPhoto model
    - Add fillable fields and casts
    - Define collectionLog() relationship
    - Implement getUrl() method
    - Implement getThumbnailUrl() method
    - Implement getFileSizeFormatted() method
    - _Requirements: 3.4, 3.5_
  
  - [ ] 4.3 Create AdminNote model
    - Add fillable fields
    - Define collectionLog() and admin() relationships
    - _Requirements: 11.1, 11.2, 11.3_

- [ ] 5. Create CollectionLogService for business logic
  - Implement createLog() method with validation
  - Implement updateLog() method with edit window check
  - Implement canEdit() method (2-hour window + ownership)
  - Implement getCrewHistory() method with date filtering
  - Implement getLogsWithFilters() method for admin
  - Implement getCompletionRate() method
  - Implement getStatusBreakdown() method
  - Implement getRoutesWithRecurringIssues() method
  - Implement getIssuesByType() method
  - _Requirements: 2.2, 6.1, 6.2, 6.3, 7.1, 7.2, 7.3, 9.2, 9.3, 9.5, 10.2, 10.3, 10.4, 12.1, 12.2_

- [ ] 6. Create PhotoService for image processing
  - Implement uploadPhoto() method with file storage
  - Implement createThumbnail() method using Intervention Image (200x200px)
  - Implement deletePhoto() method with file cleanup
  - Implement validatePhotoCount() method (max 5)
  - Implement getPhotoUrl() method
  - Implement getThumbnailUrl() method
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [ ] 7. Create AnalyticsService for statistics
  - Implement getCompletionTrend() method for daily rates
  - Implement getCrewPerformance() method
  - Implement getRoutePerformance() method
  - Implement getAverageCompletionTime() method
  - Implement getIssueHotspots() method
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 10.1, 10.2, 10.3_

- [ ] 8. Create form request validation classes
  - [ ] 8.1 Create StoreCollectionLogRequest
    - Validate completion_time (required if completed)
    - Validate status (required, enum)
    - Validate issue_type (required if issue_reported)
    - Validate issue_description (required if issue_reported)
    - Validate completion_percentage (nullable, 0-100)
    - Validate crew_notes (nullable, max 1000)
    - Validate photos array (max 5, image, max 5MB each)
    - _Requirements: 2.1, 2.2, 2.3, 2.5, 3.1, 3.2, 3.3, 4.2, 4.3, 5.2, 5.4_
  
  - [ ] 8.2 Create UpdateCollectionLogRequest
    - Same validations as StoreCollectionLogRequest (except photos)
    - Add authorize() method to check edit window and ownership
    - _Requirements: 12.1, 12.2, 12.3_
  
  - [ ] 8.3 Create UploadPhotoRequest
    - Validate photo (required, image, max 5MB)
    - Add authorize() method to check edit window and photo count
    - _Requirements: 3.1, 3.2, 3.3, 12.4_
  
  - [ ] 8.4 Create AddAdminNoteRequest
    - Validate note (required, max 1000)
    - _Requirements: 11.1, 11.3_

- [ ] 9. Create EnsureLogIsEditable middleware
  - Implement handle() method to check edit window
  - Check ownership (crew can only edit own logs)
  - Redirect with error message if not editable
  - Register middleware in Kernel
  - _Requirements: 12.1, 12.2_

- [ ] 10. Implement CollectionLogController for crew logging
  - [ ] 10.1 Implement today's assignment view with logging option
    - Create index() method to show current day assignment
    - Display assignment details and status
    - Show "Log Collection" button if not logged
    - Show log status and link if already logged
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_
  
  - [ ] 10.2 Implement collection log creation
    - Create create() method to show logging form
    - Create store() method with validation
    - Use CollectionLogService to create log
    - Use PhotoService to upload and process photos
    - Handle different status types (completed, incomplete, issue)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.4, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4_
  
  - [ ] 10.3 Implement collection log viewing
    - Create show() method to display log details
    - Display assignment information
    - Display status, completion time, notes
    - Display photo gallery
    - Display issue details if applicable
    - Show edit button if within time window
    - _Requirements: 6.5, 8.3, 8.4, 8.5_
  
  - [ ] 10.4 Implement collection log editing
    - Create edit() method to show edit form (with middleware)
    - Create update() method with validation (with middleware)
    - Use CollectionLogService to update log
    - Display remaining edit time
    - _Requirements: 12.1, 12.2, 12.3, 12.5_
  
  - [ ] 10.5 Implement photo management
    - Create uploadPhoto() AJAX method for additional photos
    - Create deletePhoto() method for photo removal
    - Use PhotoService for operations
    - Apply edit window middleware
    - _Requirements: 3.1, 3.4, 12.4_
  
  - [ ] 10.6 Implement collection history view
    - Create history() method to display crew's past logs
    - Add date range filter
    - Display route, date, status, completion time
    - Add pagination
    - Link to log details
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 11. Implement AdminCollectionLogController for admin management
  - [ ] 11.1 Implement collection logs listing
    - Create index() method with filters
    - Add filters for date range, status, route, crew
    - Display route, crew, truck, date, status, completion time
    - Add search functionality
    - Add pagination
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [ ] 11.2 Implement detailed log viewing
    - Create show() method to display complete log details
    - Display assignment information
    - Display crew notes and completion details
    - Display photo gallery with full-size viewing
    - Display issue details if applicable
    - Display admin notes section
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_
  
  - [ ] 11.3 Implement admin note functionality
    - Create addNote() method
    - Store admin note with admin_id and timestamp
    - Display success message
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [ ] 11.4 Implement issue analysis view
    - Create issueAnalysis() method
    - Use CollectionLogService to get routes with recurring issues
    - Display issue type breakdown
    - Display routes with issue counts
    - Add date range filter
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  
  - [ ] 11.5 Implement route-specific issues view
    - Create routeIssues() method
    - Display all issues for specific route
    - Show date, crew, issue type, description
    - Display photos if available
    - Show admin notes
    - _Requirements: 10.5_

- [ ] 12. Implement CollectionAnalyticsController for analytics
  - [ ] 12.1 Implement analytics dashboard
    - Create index() method to display dashboard
    - Show key metrics cards (total, completion rate, avg time, issues)
    - Add date range selector
    - _Requirements: 9.1, 9.2, 9.3, 9.4_
  
  - [ ] 12.2 Implement completion rates API
    - Create getCompletionRates() AJAX endpoint
    - Use AnalyticsService to calculate rates
    - Return JSON data for chart rendering
    - _Requirements: 9.2, 9.4_
  
  - [ ] 12.3 Implement status breakdown API
    - Create getStatusBreakdown() AJAX endpoint
    - Use CollectionLogService to get status counts
    - Return JSON data for pie chart
    - _Requirements: 9.3_
  
  - [ ] 12.4 Implement crew performance API
    - Create getCrewPerformance() AJAX endpoint
    - Use AnalyticsService to calculate crew metrics
    - Return JSON data for table display
    - _Requirements: 9.5_
  
  - [ ] 12.5 Implement route performance API
    - Create getRoutePerformance() AJAX endpoint
    - Use AnalyticsService to calculate route metrics
    - Return JSON data for table display
    - _Requirements: 9.5_

- [ ] 13. Define application routes
  - Define crew routes for collection logging with role middleware
  - Define crew routes for history viewing
  - Apply EnsureLogIsEditable middleware to edit routes
  - Define admin routes for log management with role middleware
  - Define admin routes for analytics
  - Apply authentication middleware to all routes
  - _Requirements: All_

- [ ] 14. Create crew views for collection logging
  - [ ] 14.1 Create today's assignment view with logging
    - Build assignment card with truck, route, zone details
    - Display collection time
    - Add "Log Collection" button (Teal) if not logged
    - Display status badge if logged
    - Add link to view log if exists
    - Style for mobile optimization
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_
  
  - [ ] 14.2 Create collection logging form
    - Build form with status selection (radio buttons)
    - Add conditional fields based on status:
      - Completed: completion time picker, notes
      - Incomplete: reason textarea, completion percentage slider, notes
      - Issue Reported: issue type dropdown, description textarea, notes
    - Add photo upload area with drag & drop
    - Display photo preview thumbnails with delete option
    - Add submit button (Teal) and cancel button
    - Style with SWEEP design system
    - _Requirements: 2.1, 2.2, 2.5, 3.1, 4.1, 4.2, 4.3, 5.1, 5.2, 5.4_
  
  - [ ] 14.3 Create collection log view
    - Display status badge (color-coded)
    - Show assignment details (truck, route, date)
    - Display completion time and crew notes
    - Build photo gallery with lightbox functionality
    - Display issue details if applicable
    - Add edit button if within 2-hour window
    - Display timestamp and edit time remaining
    - _Requirements: 6.5, 8.3, 8.4, 8.5, 12.1_
  
  - [ ] 14.4 Create collection log edit form
    - Similar to create form but pre-populated
    - Display remaining edit time prominently
    - Allow photo additions/deletions
    - Style with SWEEP design system
    - _Requirements: 12.1, 12.3, 12.4_
  
  - [ ] 14.5 Create collection history view
    - Build list view of past logs
    - Add date range filter
    - Display date, route, status, completion time for each
    - Add status color coding
    - Add click to view details
    - Add pagination
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 15. Create admin views for collection log management
  - [ ] 15.1 Create collection logs index view
    - Build data table with columns: Date, Route, Crew, Truck, Status, Completion Time, Actions
    - Add filters for date range, status, route, crew member
    - Add status badges with color coding
    - Add search functionality
    - Add pagination
    - Add export button
    - Style with Bootstrap 5 and SWEEP colors
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [ ] 15.2 Create collection log details view
    - Display complete log information
    - Show assignment details section
    - Display crew notes section
    - Build photo gallery with full-size viewing
    - Display issue details section if applicable
    - Create admin notes section with add note form
    - Display timestamp information
    - Show edit history if applicable
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 11.1, 11.2, 11.4_
  
  - [ ] 15.3 Create issue analysis view
    - Add date range selector
    - Display issue type breakdown (bar chart)
    - Build routes with recurring issues table
    - Show issue count per route
    - Add link to view all issues for route
    - Add filter by issue type
    - _Requirements: 10.1, 10.2, 10.3, 10.4_
  
  - [ ] 15.4 Create route issues view
    - Display route information header
    - Build list of all issues for route
    - Show date, crew, issue type, description for each
    - Display photos if available
    - Show admin notes
    - Add timeline view option
    - _Requirements: 10.5_

- [ ] 16. Create collection analytics dashboard views
  - [ ] 16.1 Create analytics dashboard layout
    - Add date range selector
    - Build key metrics cards:
      - Total collections
      - Completion rate percentage
      - Average completion time
      - Issue count
    - Style cards with SWEEP colors
    - _Requirements: 9.1, 9.2, 9.3, 9.4_
  
  - [ ] 16.2 Create completion trend chart
    - Integrate Chart.js or similar library
    - Build line chart for daily completion rates
    - Load data via AJAX
    - Add interactive tooltips
    - _Requirements: 9.2, 9.4_
  
  - [ ] 16.3 Create status breakdown chart
    - Build pie chart for status distribution
    - Load data via AJAX
    - Add color coding by status
    - Add interactive tooltips
    - _Requirements: 9.3_
  
  - [ ] 16.4 Create performance tables
    - Build crew performance table
    - Build route performance table
    - Load data via AJAX
    - Add sorting functionality
    - Display completion rates and issue counts
    - _Requirements: 9.5_

- [ ] 17. Implement error handling and user feedback
  - Add flash messages for success/error notifications
  - Implement validation error display in all forms
  - Handle "assignment already logged" error
  - Handle "edit window expired" error
  - Handle photo upload errors (size, format, limit)
  - Display user-friendly error messages
  - Add confirmation for photo deletions
  - _Requirements: 2.2, 3.3, 12.1, 12.2_

- [ ] 18. Add navigation links to role-specific dashboards
  - Add "Log Collection" link to crew navigation
  - Add "Collection History" link to crew navigation
  - Add "Collection Logs" link to administrator sidebar
  - Add "Collection Analytics" link to administrator sidebar
  - Update dashboard controllers to include navigation
  - _Requirements: All_

- [ ] 19. Implement photo upload UI enhancements
  - Add drag-and-drop functionality for photo uploads
  - Implement client-side image preview before upload
  - Add progress indicators for uploads
  - Implement AJAX photo upload for better UX
  - Add photo count indicator (X/5 photos)
  - Style upload area with SWEEP design system
  - _Requirements: 3.1, 3.4_

- [ ] 20. Create database seeders for testing
  - [ ] 20.1 Create CollectionLogSeeder
    - Create sample logs for various assignments
    - Include all status types (completed, incomplete, issue_reported)
    - Vary completion times and percentages
    - Include different issue types
    - _Requirements: 2.2, 4.4, 5.3_
  
  - [ ] 20.2 Create CollectionPhotoSeeder
    - Create sample photo records for logs
    - Use placeholder images or generate test images
    - Vary photo counts per log
    - _Requirements: 3.4_
  
  - [ ] 20.3 Create AdminNoteSeeder
    - Create sample admin notes for various logs
    - Include different administrators
    - Vary note content
    - _Requirements: 11.1_

- [ ] 21. Write feature tests for collection logging
  - [ ] 21.1 Test collection log creation
    - Test completed collection logging
    - Test incomplete collection logging
    - Test issue reporting
    - Test photo upload during creation
    - Test validation errors
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.4, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4_
  
  - [ ] 21.2 Test photo management
    - Test photo upload (single and multiple)
    - Test photo format validation
    - Test photo size validation
    - Test photo count limit (max 5)
    - Test photo deletion
    - Test thumbnail generation
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [ ] 21.3 Test collection log editing
    - Test edit within 2-hour window
    - Test edit prevention after 2-hour window
    - Test ownership validation (crew can only edit own logs)
    - Test status updates
    - Test photo additions during edit
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_
  
  - [ ] 21.4 Test crew authorization
    - Test that only assigned crew can log collections
    - Test that crew cannot edit other crew's logs
    - Test that crew can view their own history
    - _Requirements: 1.1, 6.1, 12.2_

- [ ] 22. Write feature tests for admin log management
  - [ ] 22.1 Test admin log viewing
    - Test collection logs listing with filters
    - Test date range filtering
    - Test status filtering
    - Test route and crew filtering
    - Test detailed log viewing
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5_
  
  - [ ] 22.2 Test admin note functionality
    - Test admin note creation
    - Test admin note display
    - Test multiple notes per log
    - Test admin identification in notes
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
  
  - [ ] 22.3 Test admin authorization
    - Test that only administrators can view all logs
    - Test that only administrators can add admin notes
    - _Requirements: 7.1, 11.1_

- [ ] 23. Write feature tests for analytics
  - [ ] 23.1 Test completion rate calculations
    - Test completion rate for date range
    - Test completion rate with filters
    - Test status breakdown calculations
    - _Requirements: 9.2, 9.3, 9.4_
  
  - [ ] 23.2 Test performance metrics
    - Test crew performance calculations
    - Test route performance calculations
    - Test average completion time calculation
    - _Requirements: 9.5_
  
  - [ ] 23.3 Test issue analysis
    - Test routes with recurring issues identification
    - Test issue type grouping
    - Test issue hotspot identification
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [ ] 24. Write feature tests for collection history
  - Test crew history viewing
  - Test date range filtering
  - Test log details viewing from history
  - Test pagination
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 25. Implement photo cleanup and optimization
  - Create scheduled job to clean up orphaned photos
  - Implement photo compression for storage optimization
  - Add image optimization during upload
  - Test cleanup job functionality
  - _Requirements: 3.4_
