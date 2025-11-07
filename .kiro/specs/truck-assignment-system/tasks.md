# Implementation Plan: Truck Assignment System

- [x] 1. Create database migrations for trucks and assignments






  - [x] 1.1 Create trucks table migration

    - Add columns: truck_number (unique), license_plate, capacity, operational_status (enum), notes
    - Add soft deletes
    - Add indexes on truck_number
    - _Requirements: 1.1, 1.2, 1.3, 2.2, 3.1_
  

  - [x] 1.2 Create assignments table migration

    - Add columns: truck_id (FK), user_id (FK), route_id (FK), assignment_date, status (enum), notes
    - Add foreign key constraints to trucks, users, and routes tables
    - Add unique constraints for active assignments (truck_id + date, user_id + date)
    - Add indexes on assignment_date, truck_id, user_id, route_id
    - _Requirements: 4.1, 4.4, 4.5, 6.4_
  
  - [x] 1.3 Create truck_status_history table migration


    - Add columns: truck_id (FK), old_status, new_status, changed_by (FK to users), notes, created_at
    - Add foreign key constraint to trucks table
    - Add index on truck_id
    - _Requirements: 3.4, 3.5_

- [x] 2. Create Eloquent models with relationships






  - [x] 2.1 Create Truck model

    - Add fillable fields and casts
    - Add SoftDeletes trait
    - Define status constants
    - Define assignments() and activeAssignments() relationships
    - Define statusHistory() relationship
    - Implement isOperational() method
    - Implement hasAssignmentOn() method
    - Implement getAssignmentOn() method
    - Implement hasFutureAssignments() method
    - Implement getAssignmentHistory() method
    - Implement getUtilizationRate() method
    - _Requirements: 1.1, 1.5, 2.1, 2.2, 2.3, 2.5, 3.1, 8.2, 8.3, 11.2, 11.4_
  

  - [x] 2.2 Create Assignment model

    - Add fillable fields and casts
    - Define status constants
    - Define truck(), user(), and route() relationships
    - Implement active() scope
    - Implement forDate() scope
    - Implement upcoming() scope
    - Implement isActive() method
    - Implement cancel() method
    - Implement hasConflictWith() method
    - _Requirements: 4.1, 4.2, 5.4, 6.3, 6.4_
  

  - [x] 2.3 Create TruckStatusHistory model

    - Add fillable fields and casts
    - Define truck() and changedBy() relationships
    - Disable default timestamps, use only created_at
    - _Requirements: 3.4, 3.5_

- [x] 3. Create AssignmentService for business logic





  - Implement createAssignment() method with validation
  - Implement updateAssignment() method with conflict checking
  - Implement cancelAssignment() method
  - Implement checkConflicts() method for truck and crew
  - Implement copyAssignments() method with conflict detection
  - Implement getAssignmentsForDate() method
  - Implement getAssignmentsInRange() method
  - Implement getUnassignedRoutes() method
  - Implement getTruckAvailability() method
  - Implement getCrewAssignments() method
  - _Requirements: 4.2, 4.3, 4.4, 4.5, 5.1, 6.2, 7.4, 8.1, 8.2, 8.3, 9.1, 9.2, 10.3, 10.4, 11.2_

- [x] 4. Create AlertService for dashboard alerts





  - Implement getAssignmentAlerts() method
  - Implement getUnassignedRoutesAlert() method (3-day lookahead)
  - Implement getUnderutilizedTrucksAlert() method (7-day lookahead)
  - Implement dismissAlert() method with user tracking
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [x] 5. Create form request validation classes





  - [x] 5.1 Create StoreTruckRequest


    - Validate truck_number (required, unique, max 50)
    - Validate license_plate (required, max 50)
    - Validate capacity (required, numeric, min 0)
    - Validate operational_status (required, enum)
    - Validate notes (nullable)
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [x] 5.2 Create UpdateTruckRequest


    - Same validations as StoreTruckRequest
    - Exclude current truck from unique check
    - _Requirements: 2.4_
  
  - [x] 5.3 Create UpdateTruckStatusRequest


    - Validate operational_status (required, enum)
    - Validate notes (nullable)
    - _Requirements: 3.1, 3.2, 3.4_
  
  - [x] 5.4 Create StoreAssignmentRequest


    - Validate truck_id (required, exists)
    - Validate user_id (required, exists)
    - Validate route_id (required, exists)
    - Validate assignment_date (required, date, not in past)
    - Validate notes (nullable)
    - Add custom validation for truck operational status
    - Add custom validation for user role (must be collection_crew)
    - Add custom validation for conflicts using AssignmentService
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  
  - [x] 5.5 Create UpdateAssignmentRequest


    - Same validations as StoreAssignmentRequest
    - Allow past dates for existing assignments
    - _Requirements: 6.1, 6.2_
  
  - [x] 5.6 Create CopyAssignmentsRequest


    - Validate source_date (required, date)
    - Validate target_date (required, date, not in past, different from source)
    - Validate truck_ids (nullable array for selective copying)
    - _Requirements: 10.1, 10.2, 10.4_

- [x] 6. Implement TruckController for administrator truck management





  - [x] 6.1 Implement truck listing


    - Create index() method with search by truck number/license plate
    - Add filter for operational status
    - Include assignment count for each truck
    - Add pagination
    - _Requirements: 2.1, 2.2_
  
  - [x] 6.2 Implement truck registration

    - Create create() method to show form
    - Create store() method with validation
    - Handle duplicate truck number errors
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [x] 6.3 Implement truck viewing and editing

    - Create show() method with assignment history
    - Create edit() method to show form
    - Create update() method with validation
    - _Requirements: 2.3, 2.4_
  
  - [x] 6.4 Implement truck deletion with protection

    - Create destroy() method
    - Check for future assignments before deletion
    - Display error if future assignments exist
    - Use soft delete
    - _Requirements: 2.5_
  
  - [x] 6.5 Implement truck status management

    - Create updateStatus() method
    - Log status change to truck_status_history
    - Check for future assignments and display warning
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [x] 6.6 Implement truck assignment history view

    - Create history() method
    - Display past assignments with date range filter
    - Calculate and display utilization statistics
    - Add export functionality
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_

- [x] 7. Implement AssignmentController for administrator assignment management





  - [x] 7.1 Implement assignment calendar view


    - Create index() method to display calendar interface
    - Create getCalendarData() AJAX endpoint
    - Use AssignmentService to get assignments in date range
    - Return JSON data for calendar rendering
    - Add filters for truck and crew
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_
  
  - [x] 7.2 Implement assignment creation


    - Create create() method to show form
    - Populate dropdowns (trucks filtered by operational, crew filtered by role, routes)
    - Create store() method with validation
    - Use AssignmentService to create assignment
    - Handle conflict errors with clear messages
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_
  
  - [x] 7.3 Implement assignment viewing and editing


    - Create show() method with details
    - Create edit() method to show form
    - Create update() method with conflict checking
    - Use AssignmentService for updates
    - _Requirements: 6.1, 6.2_
  
  - [x] 7.4 Implement assignment cancellation


    - Create cancel() method
    - Update status to cancelled (not delete)
    - Add optional cancellation reason
    - _Requirements: 6.3, 6.4, 6.5_
  
  - [x] 7.5 Implement assignment copying


    - Create copy() method to show copy form
    - Use AssignmentService to copy assignments
    - Handle conflicts and display list of issues
    - Allow selective copying if conflicts exist
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_
  
  - [x] 7.6 Implement unassigned routes view


    - Create unassignedRoutes() method
    - Use AssignmentService to get routes without assignments
    - Add date range filter (default: next 7 days)
    - Display route details with schedule information
    - Add quick "Create Assignment" links
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 8. Implement TruckAvailabilityController for availability checking





  - [x] 8.1 Implement truck availability view

    - Create index() method to show availability interface
    - Create getAvailability() AJAX endpoint
    - Use AssignmentService to get truck availability for date
    - Display operational trucks with assignment status
    - Show maintenance and out-of-service trucks separately
    - Add quick assign functionality
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 9. Implement CrewAssignmentController for crew assignment viewing






  - [x] 9.1 Implement today's assignment view

    - Create index() method to show current day assignment
    - Display truck details and route information
    - Show collection time from schedule
    - Display special instructions
    - Show "no assignment" message if none
    - _Requirements: 7.1, 7.2, 7.3, 7.5_
  

  - [x] 9.2 Implement upcoming assignments view


    - Create upcoming() method to show next 14 days
    - Use AssignmentService to get crew assignments
    - Group by date
    - Display truck, route, and time for each
    - Show empty state if no upcoming assignments
    - _Requirements: 7.4_

- [x] 10. Update DashboardController to include assignment alerts





  - Integrate AlertService into administrator dashboard
  - Display unassigned routes alert card
  - Display underutilized trucks alert card
  - Add dismiss functionality for alerts
  - Include alert count badges
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [x] 11. Define application routes




  - Define administrator routes for trucks with role middleware
  - Define administrator routes for assignments with role middleware
  - Define administrator routes for truck availability
  - Define crew routes for viewing assignments
  - Apply authentication middleware to all routes
  - _Requirements: All_

- [x] 12. Create administrator views for truck management





  - [x] 12.1 Create trucks index view


    - Build data table with search and filters
    - Add status badges with color coding
    - Add action buttons (View, Edit, Delete, Update Status)
    - Display assignment count for each truck
    - Style with Bootstrap 5 and SWEEP colors
    - _Requirements: 2.1, 2.2_
  
  - [x] 12.2 Create truck create/edit form views


    - Build form with truck number, license plate, capacity, status, notes fields
    - Add unit label for capacity
    - Style with SWEEP design system
    - Add client-side validation
    - _Requirements: 1.1, 1.2, 1.5, 2.4_
  
  - [x] 12.3 Create truck status update modal

    - Build modal with current status display
    - Add new status dropdown
    - Add status change notes textarea
    - Display warning if future assignments exist
    - Style confirm button with Amber for warnings
    - _Requirements: 3.1, 3.2, 3.3, 3.4_
  
  - [x] 12.4 Create truck details and history view


    - Display truck information card
    - Build assignment history table with date range filter
    - Display utilization statistics
    - Show status change history timeline
    - Add "Edit Truck" and "Update Status" buttons
    - Add export functionality for history
    - _Requirements: 2.3, 11.1, 11.2, 11.3, 11.4, 11.5_

- [x] 13. Create administrator views for assignment management





  - [x] 13.1 Create assignments calendar view


    - Integrate FullCalendar.js for calendar display
    - Display assignments as events with truck number and route
    - Add color coding by truck or route
    - Implement click handler to view/edit assignment
    - Add date navigation
    - Add filter dropdowns for truck and crew
    - Add "Create Assignment" button
    - Add "Copy Assignments" button
    - Load calendar data via AJAX
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_
  
  - [x] 13.2 Create assignment create/edit form views


    - Build form with truck, crew, route, date dropdowns
    - Filter trucks by operational status
    - Filter crew by collection_crew role
    - Add date picker
    - Add notes textarea
    - Display conflict warnings dynamically
    - Style with SWEEP design system
    - _Requirements: 4.1, 4.2, 6.1, 6.2_
  
  - [x] 13.3 Create assignment copy form view


    - Build form with source date and target date pickers
    - Add optional truck filter for selective copying
    - Display preview of assignments to be copied
    - Show conflict warnings
    - Add confirmation button
    - _Requirements: 10.1, 10.2, 10.4, 10.5_
  
  - [x] 13.4 Create assignment show view


    - Display assignment details with truck, crew, route information
    - Show assignment date and status
    - Add action buttons (Edit, Cancel)
    - Display cancellation reason if cancelled
    - _Requirements: 6.3_

- [x] 14. Create truck availability view





  - Build date selector interface
  - Display grid or list of all trucks
  - Show status indicators (Available, Assigned, Maintenance, Out of Service)
  - For assigned trucks, display route and crew
  - Add quick assign button for available trucks
  - Style with SWEEP design system
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 15. Create unassigned routes view





  - Build date range selector (default: next 7 days)
  - Display list of routes with schedules but no assignments
  - Show route name, zone, scheduled date, collection time for each
  - Add "Create Assignment" button for each route
  - Display warning count badge
  - Style with SWEEP design system
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 16. Create crew views for assignment viewing






  - [x] 16.1 Create today's assignment view

    - Build large card for today's assignment
    - Display truck number and details prominently
    - Show route name and zone
    - Display collection time
    - Show special instructions
    - Display "No assignment today" message if none
    - Style for mobile optimization
    - _Requirements: 7.1, 7.2, 7.3, 7.5_
  

  - [x] 16.2 Create upcoming assignments view

    - Build list view for next 14 days
    - Group assignments by date
    - Display truck, route, and time for each
    - Show empty state if no upcoming assignments
    - _Requirements: 7.4_

- [ ] 17. Create dashboard alert components
  - [ ] 17.1 Create unassigned routes alert card
    - Build alert card with Amber background
    - Display count of routes without assignments in next 3 days
    - Add "View Unassigned Routes" link
    - Add dismiss button
    - _Requirements: 12.1, 12.3, 12.4, 12.5_
  
  - [ ] 17.2 Create underutilized trucks alert card
    - Build alert card with Teal background
    - Display count of operational trucks with no assignments in next 7 days
    - Add "View Truck Availability" link
    - Add dismiss button
    - _Requirements: 12.2, 12.3, 12.4, 12.5_

- [ ] 18. Implement error handling and user feedback
  - Add flash messages for success/error notifications
  - Implement validation error display in all forms
  - Add confirmation modals for deletions and cancellations
  - Handle "truck has future assignments" deletion error
  - Handle assignment conflict errors with clear messages
  - Display status change warnings
  - Handle copy conflicts with detailed list
  - Display user-friendly error messages
  - _Requirements: 1.4, 2.5, 3.3, 4.4, 4.5, 6.2, 10.5_

- [ ] 19. Add navigation links to role-specific dashboards
  - Add "Trucks" link to administrator sidebar
  - Add "Assignments" link to administrator sidebar
  - Add "Truck Availability" link to administrator sidebar
  - Add "My Assignments" link to crew navigation
  - Update dashboard controllers to include navigation
  - _Requirements: All_

- [ ] 20. Create database seeders for testing
  - [ ] 20.1 Create TruckSeeder
    - Create 8-12 sample trucks with different statuses
    - Mix of operational, maintenance, and out-of-service trucks
    - Vary capacity values
    - _Requirements: 1.1, 3.1_
  
  - [ ] 20.2 Create AssignmentSeeder
    - Create assignments for current and upcoming dates
    - Assign trucks to routes with crew members
    - Leave some routes unassigned for testing
    - Include some cancelled assignments
    - _Requirements: 4.1_
  
  - [ ] 20.3 Create TruckStatusHistorySeeder
    - Create sample status change history for trucks
    - Include various status transitions
    - _Requirements: 3.5_

- [ ] 21. Write feature tests for truck management
  - [ ] 21.1 Test truck CRUD operations
    - Test truck registration by administrator
    - Test duplicate truck number validation
    - Test truck listing with search and filters
    - Test truck editing
    - Test truck deletion with future assignment protection
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5_
  
  - [ ] 21.2 Test truck status management
    - Test status updates
    - Test status history logging
    - Test warning display for future assignments
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [ ] 21.3 Test truck authorization
    - Test that only administrators can manage trucks
    - Test that crew cannot access truck management
    - _Requirements: 2.1_

- [ ] 22. Write feature tests for assignment management
  - [ ] 22.1 Test assignment CRUD operations
    - Test assignment creation with validation
    - Test truck conflict detection
    - Test crew conflict detection
    - Test truck operational status validation
    - Test crew role validation
    - Test assignment editing with conflict checking
    - Test assignment cancellation
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 6.1, 6.2, 6.3, 6.4_
  
  - [ ] 22.2 Test assignment copying
    - Test successful copying to different date
    - Test conflict detection during copying
    - Test selective copying with filters
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_
  
  - [ ] 22.3 Test assignment authorization
    - Test that only administrators can manage assignments
    - _Requirements: 4.1_

- [ ] 23. Write feature tests for crew assignment viewing
  - [ ] 23.1 Test crew assignment viewing
    - Test today's assignment display
    - Test upcoming assignments display
    - Test "no assignment" message display
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [ ] 23.2 Test crew authorization
    - Test that crew can only view their own assignments
    - Test that crew cannot view other crew assignments
    - _Requirements: 7.1_

- [ ] 24. Write feature tests for availability and alerts
  - [ ] 24.1 Test truck availability checking
    - Test availability display for selected date
    - Test operational truck filtering
    - Test assignment status display
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_
  
  - [ ] 24.2 Test unassigned routes detection
    - Test unassigned routes listing
    - Test date range filtering
    - Test route details display
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_
  
  - [ ] 24.3 Test dashboard alerts
    - Test unassigned routes alert generation
    - Test underutilized trucks alert generation
    - Test alert dismissal
    - Test alert links
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [ ] 25. Write feature tests for truck history and reporting
  - Test assignment history display
  - Test date range filtering
  - Test utilization rate calculation
  - Test history export functionality
  - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
