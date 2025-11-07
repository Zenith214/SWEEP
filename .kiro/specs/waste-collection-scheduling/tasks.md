# Implementation Plan: Waste Collection Scheduling

- [x] 1. Create database migrations for routes, schedules, and holidays





  - [x] 1.1 Create routes table migration


    - Add columns: name (unique), zone, description, notes, is_active
    - Add soft deletes
    - Add indexes on name and zone columns
    - _Requirements: 1.1, 1.2, 1.3, 2.4_
  
  - [x] 1.2 Create schedules table migration


    - Add columns: route_id (FK), collection_time, start_date, end_date, is_active
    - Add foreign key constraint to routes table
    - Add soft deletes
    - Add index on route_id
    - _Requirements: 3.1, 3.5, 4.3_
  
  - [x] 1.3 Create schedule_days pivot table migration


    - Add columns: schedule_id (FK), day_of_week
    - Add foreign key constraint to schedules table
    - Add unique constraint on (schedule_id, day_of_week)
    - _Requirements: 3.2_
  
  - [x] 1.4 Create holidays table migration


    - Add columns: name, date, is_collection_skipped, reschedule_date
    - Add unique constraint on date column
    - Add index on date column
    - _Requirements: 10.1, 10.2, 10.3_

- [x] 2. Create Eloquent models with relationships






  - [x] 2.1 Create Route model

    - Add fillable fields and casts
    - Add SoftDeletes trait
    - Define schedules() relationship
    - Define activeSchedules() relationship
    - Implement hasActiveSchedules() method
    - Implement getNextCollectionDate() method
    - _Requirements: 1.1, 1.5, 2.1, 2.3, 8.1, 8.4_
  

  - [x] 2.2 Create Schedule model

    - Add fillable fields and casts
    - Add SoftDeletes trait
    - Define route() relationship
    - Define scheduleDays() relationship
    - Implement getDaysOfWeek() method
    - Implement isActiveOn() method
    - Implement getCollectionDatesInRange() method
    - Implement hasConflictWith() method
    - _Requirements: 3.1, 3.2, 3.5, 4.1, 4.2_
  
  - [x] 2.3 Create ScheduleDay model


    - Add fillable fields
    - Define schedule() relationship
    - Add DAYS constant array for day name mapping
    - _Requirements: 3.2_
  

  - [x] 2.4 Create Holiday model

    - Add fillable fields and casts
    - Implement isHoliday() static method
    - Implement getRescheduledDate() static method
    - Implement getHolidaysInRange() static method
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 3. Create ScheduleService for business logic





  - Implement createSchedule() method with days creation
  - Implement updateSchedule() method with conflict checking
  - Implement duplicateSchedule() method
  - Implement checkConflicts() method
  - Implement getCollectionDatesForZone() method
  - Implement getNextCollectionForRoute() method
  - Implement applyHolidayExceptions() method
  - Implement getRoutesWithoutSchedules() method
  - Implement getScheduleCoverage() method
  - _Requirements: 3.3, 3.4, 4.2, 5.3, 7.4, 8.2, 8.4, 8.5, 9.3, 9.4, 10.3_

- [x] 4. Create form request validation classes




  - [x] 4.1 Create StoreRouteRequest


    - Validate name (required, unique, max 255)
    - Validate zone (required, max 100)
    - Validate description and notes (nullable)
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [x] 4.2 Create UpdateRouteRequest


    - Validate name (required, unique except current, max 255)
    - Validate zone (required, max 100)
    - Validate description and notes (nullable)
    - _Requirements: 2.4_
  
  - [x] 4.3 Create StoreScheduleRequest


    - Validate route_id (required, exists)
    - Validate collection_time (required, time format)
    - Validate start_date (required, date, not in past)
    - Validate end_date (nullable, date, after start_date)
    - Validate days_of_week (required array, min 1, values 0-6)
    - _Requirements: 3.1, 3.2, 3.5_
  
  - [x] 4.4 Create UpdateScheduleRequest


    - Same validations as StoreScheduleRequest
    - _Requirements: 4.1, 4.2_
  
  - [x] 4.5 Create DuplicateScheduleRequest


    - Validate target_route_id (required, exists, different from source)
    - _Requirements: 9.2, 9.4_
  
  - [x] 4.6 Create StoreHolidayRequest and UpdateHolidayRequest


    - Validate name (required, max 255)
    - Validate date (required, date, unique)
    - Validate is_collection_skipped (boolean)
    - Validate reschedule_date (nullable, date, different from date)
    - _Requirements: 10.1, 10.2_

- [x] 5. Implement RouteController for administrator route management





  - [x] 5.1 Implement route listing with search and filters


    - Create index() method with search by name/zone
    - Add filter for active/inactive routes
    - Include schedule count and status indicators
    - Add pagination
    - _Requirements: 2.1, 2.2, 8.1, 8.2, 8.3_
  
  - [x] 5.2 Implement route creation

    - Create create() method to show form
    - Create store() method with validation
    - Handle duplicate name errors
    - _Requirements: 1.1, 1.2, 1.3, 1.4_
  
  - [x] 5.3 Implement route viewing and editing

    - Create show() method with schedule details
    - Create edit() method to show form
    - Create update() method with validation
    - _Requirements: 2.3, 2.4_
  
  - [x] 5.4 Implement route deletion with protection

    - Create destroy() method
    - Check for active schedules before deletion
    - Display error if active schedules exist
    - Use soft delete
    - _Requirements: 2.5_

- [x] 6. Implement ScheduleController for administrator schedule management





  - [x] 6.1 Implement schedule listing


    - Create index() method with route information
    - Add filter by route
    - Add filter by active/inactive
    - Display days as badges
    - Add pagination
    - _Requirements: 3.1, 4.3_
  
  - [x] 6.2 Implement schedule creation with days

    - Create create() method to show form with route dropdown
    - Create store() method with validation
    - Use ScheduleService to create schedule with days
    - Check for conflicts and display errors
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_
  
  - [x] 6.3 Implement schedule editing

    - Create show() method with details
    - Create edit() method to show form with current days selected
    - Create update() method with conflict checking
    - Use ScheduleService for updates
    - _Requirements: 4.1, 4.2_
  
  - [x] 6.4 Implement schedule activation/deactivation

    - Create toggleActive() method
    - Update is_active status
    - Return JSON response for AJAX
    - _Requirements: 4.3, 4.4_
  
  - [x] 6.5 Implement schedule deletion

    - Create destroy() method with soft delete
    - _Requirements: 4.5_
  
  - [x] 6.6 Implement schedule duplication

    - Create duplicate() method to show duplication form
    - Create storeDuplicate() method
    - Use ScheduleService to duplicate with conflict checking
    - Display error if duplication would create conflict
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 7. Implement HolidayController for holiday management




  - Create index() method to list all holidays
  - Create create() method to show form
  - Create store() method with validation
  - Create edit() method to show form
  - Create update() method with validation
  - Create destroy() method to delete holiday
  - _Requirements: 10.1, 10.2_

- [x] 8. Implement ResidentScheduleController for resident schedule viewing






  - [x] 8.1 Implement zone search interface

    - Create index() method to show search form
    - Create search() method to find schedules by zone
    - Display results with next collection date
    - Handle "zone not found" errors
    - _Requirements: 5.1, 5.2, 5.3_
  

  - [x] 8.2 Implement calendar view


    - Create calendar() method to display calendar interface
    - Create getCalendarData() AJAX endpoint
    - Use ScheduleService to get collection dates for zone
    - Apply holiday exceptions
    - Return JSON data for calendar rendering
    - _Requirements: 5.3, 5.4, 5.5, 6.1, 6.2, 6.3, 6.4, 6.5, 10.4, 10.5_

- [x] 9. Implement CrewScheduleController for crew schedule viewing





  - [x] 9.1 Implement today's routes view


    - Create index() method to show current day's assigned routes
    - Display route details with zone and collection time
    - Show special instructions/notes
    - _Requirements: 7.1, 7.2, 7.3_
  
  - [x] 9.2 Implement upcoming routes view

    - Create upcoming() method to show next 7 days
    - Group routes by date
    - Display route cards with essential information
    - _Requirements: 7.4_
  
  - [x] 9.3 Implement route details view

    - Create show() method for route details
    - Display zone, schedule, and instructions
    - _Requirements: 7.3, 7.5_

- [x] 10. Define application routes




  - Define administrator routes for routes, schedules, and holidays with role middleware
  - Define resident routes for schedule search and calendar
  - Define crew routes for schedule viewing
  - Apply authentication middleware to all routes
  - _Requirements: All_

- [x] 11. Create administrator views for route management





  - [x] 11.1 Create routes index view


    - Build data table with search and filters
    - Add schedule status indicators and badges
    - Add action buttons (View, Edit, Delete)
    - Show warning icon for routes without schedules
    - Style with Bootstrap 5 and SWEEP colors
    - _Requirements: 2.1, 2.2, 8.1, 8.2, 8.3, 8.4_
  
  - [x] 11.2 Create route create/edit form views


    - Build form with name, zone, description, notes fields
    - Add active checkbox
    - Style with SWEEP design system
    - Add client-side validation
    - _Requirements: 1.1, 1.2, 1.5, 2.4_
  
  - [x] 11.3 Create route show view


    - Display route details
    - Show associated schedules in table
    - Add "Create Schedule" button
    - _Requirements: 2.3_

- [x] 12. Create administrator views for schedule management





  - [x] 12.1 Create schedules index view


    - Build data table with route, days, time, date range columns
    - Display days as colored badges
    - Add filters for route and active status
    - Add action buttons (View, Edit, Duplicate, Delete, Toggle Active)
    - _Requirements: 3.1, 4.3_
  
  - [x] 12.2 Create schedule create/edit form views


    - Build form with route dropdown
    - Add day checkboxes with visual selection (Sun-Sat)
    - Add time picker for collection time
    - Add date pickers for start and end dates
    - Add active checkbox
    - Display conflict warnings dynamically
    - Style with SWEEP design system
    - _Requirements: 3.1, 3.2, 3.5, 4.1, 4.2_
  
  - [x] 12.3 Create schedule duplication form view


    - Show source schedule details
    - Add target route dropdown
    - Add confirmation button
    - Display conflict warnings
    - _Requirements: 9.1, 9.2_
  
  - [x] 12.4 Create schedule show view


    - Display schedule details with route information
    - Show collection days and time
    - Show date range
    - Add action buttons (Edit, Duplicate, Delete)
    - _Requirements: 3.1_

- [x] 13. Create administrator views for holiday management





  - Create holidays index view with calendar or list display
  - Create holiday create/edit form views
  - Add conditional reschedule date field
  - Style with SWEEP design system
  - _Requirements: 10.1, 10.2, 10.4_

- [x] 14. Create resident views for schedule viewing




  - [x] 14.1 Create zone search view


    - Build search form with zone input
    - Add search button (Teal accent)
    - Display instructions for finding zone
    - Show recent searches
    - _Requirements: 5.1, 5.2_
  
  - [x] 14.2 Create schedule results view


    - Display found schedules for zone
    - Show next collection date prominently
    - Display collection days and times
    - Add link to calendar view
    - _Requirements: 5.3, 5.5_
  
  - [x] 14.3 Create calendar view


    - Integrate FullCalendar.js or build custom calendar component
    - Highlight collection days in Forest Green
    - Show holiday indicators in Amber
    - Add month navigation
    - Display legend for color coding
    - Show next collection date at top
    - Implement click handler to show route and time details
    - Load calendar data via AJAX
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 10.4, 10.5_

- [x] 15. Create crew views for schedule viewing






  - [x] 15.1 Create today's routes view

    - Build card layout for assigned routes
    - Display route name, zone, and collection time prominently
    - Show special instructions/notes
    - Add "View Details" button for each route
    - Style for mobile optimization
    - _Requirements: 7.1, 7.2, 7.3_

  


  - [x] 15.2 Create upcoming routes view

    - Display 7-day schedule view
    - Group routes by date
    - Use card layout for each route
    - Show empty state if no assignments
    - _Requirements: 7.4_

  
  - [x] 15.3 Create route details view

    - Display full route information
    - Show zone and schedule details
    - Display special instructions
    - Add placeholder for map (future feature)
    - _Requirements: 7.3, 7.5_

- [x] 16. Implement error handling and user feedback





  - Add flash messages for success/error notifications
  - Implement validation error display in all forms
  - Add confirmation modals for deletions
  - Handle "route has active schedules" deletion error
  - Handle schedule conflict errors
  - Handle "zone not found" errors
  - Display user-friendly error messages
  - _Requirements: 1.4, 2.5, 3.4, 9.5_

- [x] 17. Add navigation links to role-specific dashboards





  - Add "Routes" and "Schedules" links to administrator sidebar
  - Add "Holidays" link to administrator sidebar
  - Add "My Schedule" link to resident navigation
  - Add "My Routes" link to crew navigation
  - Update dashboard controllers to include navigation
  - _Requirements: All_

- [x] 18. Create database seeders for testing






  - [x] 18.1 Create RouteSeeder

    - Create 5-10 sample routes with different zones
    - Mix of active and inactive routes
    - _Requirements: 1.1_
  

  - [x] 18.2 Create ScheduleSeeder

    - Create schedules for most routes
    - Leave some routes without schedules for testing
    - Include various day combinations
    - Include schedules with end dates
    - _Requirements: 3.1, 3.2_
  
  - [x] 18.3 Create HolidaySeeder


    - Create sample holidays for current year
    - Include both skipped and rescheduled holidays
    - _Requirements: 10.1, 10.2_

- [x] 19. Write feature tests for route management






  - [x] 19.1 Test route CRUD operations


    - Test route creation by administrator
    - Test duplicate name validation
    - Test route listing with search and filters
    - Test route editing
    - Test route deletion with active schedule protection
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5_
  
  - [x] 19.2 Test route authorization


    - Test that only administrators can manage routes
    - Test that residents and crew cannot access route management
    - _Requirements: 2.1_

- [x] 20. Write feature tests for schedule management





  - [x] 20.1 Test schedule CRUD operations


    - Test schedule creation with multiple days
    - Test schedule conflict detection
    - Test schedule editing with conflict checking
    - Test schedule activation/deactivation
    - Test schedule deletion
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 4.1, 4.2, 4.3, 4.4_
  
  - [x] 20.2 Test schedule duplication


    - Test successful duplication to different route
    - Test duplication conflict prevention
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_
  
  - [x] 20.3 Test schedule authorization



    - Test that only administrators can manage schedules
    - _Requirements: 3.1_

- [x] 21. Write feature tests for resident schedule viewing





  - [x] 21.1 Test zone search functionality


    - Test successful zone search
    - Test "zone not found" handling
    - Test display of next collection date
    - _Requirements: 5.1, 5.2, 5.3, 5.5_
  
  - [x] 21.2 Test calendar data generation


    - Test calendar data API endpoint
    - Test holiday exception application
    - Test date range filtering
    - _Requirements: 6.1, 6.2, 6.3, 10.4, 10.5_

- [x] 22. Write feature tests for crew schedule viewing





  - [x] 22.1 Test crew route viewing


    - Test today's routes display
    - Test upcoming routes display
    - Test route details view
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
  
  - [x] 22.2 Test crew authorization


    - Test that crew can only view assigned routes
    - _Requirements: 7.1_

- [x] 23. Write feature tests for holiday management





  - [x] 23.1 Test holiday CRUD operations


    - Test holiday creation
    - Test duplicate date validation
    - Test holiday editing
    - Test holiday deletion
    - _Requirements: 10.1, 10.2_
  
  - [x] 23.2 Test holiday application


    - Test that holidays affect calendar display
    - Test rescheduled date display
    - _Requirements: 10.3, 10.4, 10.5_
