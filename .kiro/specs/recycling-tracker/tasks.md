# Implementation Plan

- [ ] 1. Create database schema and models
  - [ ] 1.1 Create RecyclingLog model and migration
    - Write migration for `recycling_logs` table with columns: user_id, assignment_id, route_id, collection_date, notes, quality_issue, timestamps, soft deletes
    - Add indexes on user_id, collection_date, route_id, assignment_id
    - Add foreign key constraints with appropriate cascade rules
    - Create RecyclingLog model with fillable attributes, casts, and soft deletes trait
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 4.1, 4.2, 4.3, 15.1_

  - [ ] 1.2 Create RecyclingLogMaterial model and migration
    - Write migration for `recycling_log_materials` table with columns: recycling_log_id, material_type (enum), weight (decimal 8,2), timestamps
    - Add index on recycling_log_id and material_type
    - Add foreign key to recycling_logs with cascade delete
    - Create RecyclingLogMaterial model with fillable attributes and validation
    - _Requirements: 2.1, 2.2, 2.3, 2.5, 3.1, 3.2_

  - [ ] 1.3 Create RecyclingTarget model and migration
    - Write migration for `recycling_targets` table with columns: material_type (nullable enum), target_weight (decimal 10,2), month (date), timestamps
    - Add unique index on month + material_type combination
    - Create RecyclingTarget model with fillable attributes and casts
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

  - [ ] 1.4 Add model relationships and scopes
    - Add relationships to RecyclingLog: belongsTo User, Assignment, Route; hasMany RecyclingLogMaterial
    - Add relationship to RecyclingLogMaterial: belongsTo RecyclingLog
    - Implement scopes on RecyclingLog: forUser, forDateRange, forRoute, forZone, withQualityIssues, recent
    - Add relationships to existing User, Route, Assignment models for recycling logs
    - _Requirements: 1.4, 5.1, 5.2, 6.1, 6.4, 15.2_

  - [ ] 1.5 Implement RecyclingLog business logic methods
    - Write `isWithinEditWindow()` method to check if created_at is within 2 hours
    - Write `canBeEditedBy(User $user)` method to verify ownership and edit window
    - Write `getTotalWeight()` method to sum all material weights
    - Write `getMaterialBreakdown()` method to return materials with weights and percentages
    - _Requirements: 3.5, 14.1, 14.2, 14.3_

  - [ ] 1.6 Implement RecyclingTarget business logic methods
    - Write `getCurrentProgress()` method to calculate percentage of target achieved for current month
    - Write `isAchieved()` method to check if current month weight meets or exceeds target
    - _Requirements: 12.3, 12.4_

- [ ] 2. Create form request validation
  - [ ] 2.1 Create RecyclingLogRequest form request class
    - Define validation rules for collection_date (required, date, before_or_equal:today)
    - Define validation rules for notes (nullable, string, max:500)
    - Define validation rules for quality_issue (boolean)
    - Define validation rules for materials array (required, array, min:1, max:6)
    - Define validation rules for materials.*.material_type (required, in:plastic,paper,glass,metal,cardboard,organic)
    - Define validation rules for materials.*.weight (required, numeric, min:0.01, max:10000)
    - _Requirements: 1.2, 2.4, 3.3, 3.4, 4.2_

  - [ ] 2.2 Add custom validation logic to RecyclingLogRequest
    - Implement custom rule to ensure unique material types within single log submission
    - Implement authorization check for edit window validation on updates
    - Implement authorization check to verify user owns the log being updated
    - Add custom error messages for validation failures
    - _Requirements: 2.3, 14.1, 14.2_

- [ ] 3. Create RecyclingAnalyticsService
  - [ ] 3.1 Create service class with material analysis methods
    - Write `getMaterialTotals($startDate, $endDate)` to calculate sum and percentage for each material type
    - Implement query to group by material_type and sum weights across date range
    - Return array with material types sorted by weight descending
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [ ] 3.2 Implement zone performance analysis methods
    - Write `getZonePerformance($startDate, $endDate, $materialTypes)` to group logs by zone
    - Calculate sum of weights per zone with filtering by material types
    - Calculate average weight across all zones for highlighting
    - Return zones sorted by total weight descending with highlight flag
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [ ] 3.3 Implement trend analysis methods
    - Write `getTrendData($startDate, $endDate, $interval, $materialTypes)` to aggregate by time intervals
    - Support daily, weekly, and monthly interval aggregation
    - Calculate percentage change between consecutive intervals
    - Filter by material types if specified
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

  - [ ] 3.4 Implement crew performance analysis methods
    - Write `getCrewPerformance($startDate, $endDate)` to calculate totals per crew member
    - Calculate sum of weights, count of logs, and average weight per log for each crew member
    - Return crew members sorted by total weight descending
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ] 3.5 Implement recycling rate calculation methods
    - Write `getRecyclingRate($startDate, $endDate)` to calculate various rate metrics
    - Calculate total weight divided by number of logs
    - Calculate total weight divided by number of zones
    - Write `compareWithPreviousPeriod($startDate, $endDate)` to calculate percentage change
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

  - [ ] 3.6 Implement target tracking methods
    - Write `getTargetProgress($month)` to retrieve all targets for specified month
    - Calculate current progress percentage for each target
    - Query recycling logs for the target month and calculate totals by material type
    - Return targets with progress data and achievement status
    - _Requirements: 12.3, 12.4, 12.5_

  - [ ] 3.7 Add caching to analytics service methods
    - Implement cache wrapper for all analytics methods with 15-minute TTL
    - Generate cache keys including date range and filter parameters
    - Add cache invalidation logic to be called on log creation/update
    - _Requirements: Performance optimization from design_

- [ ] 4. Create RecyclingExportService
  - [ ] 4.1 Implement CSV export functionality
    - Write `exportLogs($logs, $filename)` method to generate CSV response
    - Include columns: collection_date, crew_member_name, route_identifier, zone, material_types, weight_values, notes
    - Write `formatLogForExport(RecyclingLog $log)` to convert log to CSV row format
    - Generate filename with format "recycling-export-{start_date}-{end_date}.csv"
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_

- [ ] 5. Create Collection Crew controllers and routes
  - [ ] 5.1 Create RecyclingLogController for crew operations
    - Create controller with auth and role:collection_crew middleware
    - Implement `index()` method to display crew member's logs with date filtering
    - Implement `create()` method to show form with active assignment pre-filled
    - Implement `store()` method to validate and create log with materials in transaction
    - Implement `edit()` method to show edit form with edit window check
    - Implement `update()` method to update log with edit window and ownership checks
    - _Requirements: 1.1, 1.2, 1.3, 5.1, 5.2, 14.1, 14.2, 14.3_

  - [ ] 5.2 Define crew routes in routes/web.php
    - Add route group with auth and role:collection_crew middleware
    - Define GET /crew/recycling-logs for index
    - Define GET /crew/recycling-logs/create for create form
    - Define POST /crew/recycling-logs for store
    - Define GET /crew/recycling-logs/{id}/edit for edit form
    - Define PUT /crew/recycling-logs/{id} for update
    - _Requirements: 1.1, 5.1, 14.1_

- [ ] 6. Create Administrator controllers and routes
  - [ ] 6.1 Create Admin\RecyclingLogController for admin operations
    - Create controller with auth and role:administrator middleware
    - Implement `index()` method with advanced filtering (date, material, route, zone, quality issues)
    - Implement `show()` method to display detailed log view with edit history
    - Implement `export()` method to generate CSV using RecyclingExportService
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 11.1, 11.4, 15.2_

  - [ ] 6.2 Create Admin\RecyclingAnalyticsController for analytics
    - Create controller with auth and role:administrator middleware
    - Implement `dashboard()` method for overview with key metrics using RecyclingAnalyticsService
    - Implement `materialAnalysis()` method for material breakdown with charts
    - Implement `zonePerformance()` method for zone-based analytics
    - Implement `trendAnalysis()` method for time-series data with interval selector
    - Implement `crewPerformance()` method for crew statistics and rankings
    - _Requirements: 7.1, 7.5, 8.1, 9.1, 9.3, 10.1, 10.2, 13.5_

  - [ ] 6.3 Create Admin\RecyclingTargetController for target management
    - Create controller with auth and role:administrator middleware
    - Implement `index()` method to list targets with progress using RecyclingAnalyticsService
    - Implement `store()` method to create new target with validation
    - Implement `update()` method to modify existing target
    - Implement `destroy()` method to delete target
    - _Requirements: 12.1, 12.2, 12.5_

  - [ ] 6.4 Define admin routes in routes/web.php
    - Add route group with auth and role:administrator middleware prefix /admin
    - Define recycling log routes (index, show, export)
    - Define analytics routes (dashboard, materials, zones, trends, crew)
    - Define target management routes (index, store, update, destroy)
    - _Requirements: 6.1, 7.5, 8.1, 9.1, 10.1, 11.1, 12.1_

- [ ] 7. Create Blade views for Collection Crew
  - [ ] 7.1 Create recycling logs index view for crew
    - Create view at resources/views/crew/recycling-logs/index.blade.php
    - Add page header "My Recycling Logs" with "Create New Log" button
    - Add date range filter form (start_date, end_date inputs)
    - Create table displaying: collection_date, route/zone, materials (color-coded badges), total_weight, actions
    - Add pagination links (20 per page)
    - Add empty state message when no logs found
    - Disable edit button with tooltip if edit window expired
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [ ] 7.2 Create recycling log create/edit form view for crew
    - Create view at resources/views/crew/recycling-logs/form.blade.php
    - Add Collection Details section with collection_date picker and route/zone display
    - Add Materials Collected section with dynamic material entry rows (material_type dropdown, weight input)
    - Implement "Add Material" button with JavaScript (max 6 materials)
    - Add auto-calculated total weight display
    - Add Additional Information section with notes textarea (500 char counter) and quality_issue checkbox
    - Add form validation error display
    - Add edit mode warning banner showing time remaining
    - _Requirements: 1.1, 1.2, 1.5, 2.1, 2.2, 3.1, 3.5, 4.1, 15.1_

  - [ ] 7.3 Add JavaScript for dynamic material form functionality
    - Write JavaScript to add/remove material entry rows
    - Implement real-time total weight calculation
    - Add character counter for notes field
    - Implement material type uniqueness validation
    - Add weight range validation (0.01 to 10000)
    - _Requirements: 1.5, 2.3, 3.3, 3.4, 3.5_

- [ ] 8. Create Blade views for Administrator
  - [ ] 8.1 Create recycling logs index view for admin
    - Create view at resources/views/admin/recycling-logs/index.blade.php
    - Add page header "All Recycling Logs" with export button
    - Add collapsible advanced filter panel (date range, material types, route/zone, crew member, quality issues)
    - Create table displaying: collection_date, crew_member, route/zone, materials, total_weight, quality_issue icon, actions
    - Highlight rows with quality issues using amber background
    - Add pagination links (50 per page)
    - Display results count
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 15.2, 15.3_

  - [ ] 8.2 Create recycling log detail view for admin
    - Create view at resources/views/admin/recycling-logs/show.blade.php
    - Display all log details including crew member, collection date, route, materials with weights
    - Show notes and quality issue flag
    - Display modification timestamps if log was edited
    - Add back to list button
    - _Requirements: 6.5, 14.4, 14.5_

  - [ ] 8.3 Create analytics dashboard overview view
    - Create view at resources/views/admin/recycling/analytics/dashboard.blade.php
    - Add date range selector (global filter)
    - Create tab navigation (Overview, Materials, Zones, Trends, Crew Performance)
    - Add key metrics cards: total weight, total logs, average per log, recycling rate
    - Add material breakdown pie chart using Laravel Charts
    - Add weekly trend line chart (last 12 weeks)
    - Add target progress section with progress bars
    - _Requirements: 7.5, 9.3, 12.5, 13.2, 13.3_

  - [ ] 8.4 Create material analysis view
    - Create view at resources/views/admin/recycling/analytics/materials.blade.php
    - Add material totals table with columns: material_type, total_weight, percentage, log_count
    - Add horizontal bar chart for materials by weight (sorted descending)
    - Add comparison with previous period (percentage change indicators)
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 13.4_

  - [ ] 8.5 Create zone performance view
    - Create view at resources/views/admin/recycling/analytics/zones.blade.php
    - Add material type filter (multi-select)
    - Create zone performance table: zone/route, total_weight, log_count, average_per_log, highlight indicator
    - Highlight zones above average with visual indicator
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

  - [ ] 8.6 Create trend analysis view
    - Create view at resources/views/admin/recycling/analytics/trends.blade.php
    - Add interval selector (Daily/Weekly/Monthly radio buttons)
    - Add material type filter (multi-select checkboxes)
    - Add line chart showing weight over time using Laravel Charts
    - Add percentage change indicators between intervals
    - Add data table below chart with detailed numbers
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 13.5_

  - [ ] 8.7 Create crew performance view
    - Create view at resources/views/admin/recycling/analytics/crew.blade.php
    - Create crew ranking table: rank, crew_member_name, total_weight, log_count, average_per_log
    - Add top performer badge (gold medal icon) for rank #1
    - Add date range filter
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ] 8.8 Create targets management view
    - Create view at resources/views/admin/recycling/targets/index.blade.php
    - Add page header "Recycling Targets"
    - Display current month targets with progress bars (color-coded: green if achieved, amber if close, gray otherwise)
    - Add checkmark icon for achieved targets
    - Add "Create New Target" form with month selector, material_type dropdown, target_weight input
    - Add edit/delete actions for existing targets
    - _Requirements: 12.1, 12.2, 12.4, 12.5_

- [ ] 9. Integrate with existing dashboard navigation
  - [ ] 9.1 Add recycling links to crew dashboard sidebar
    - Update crew dashboard layout to include "My Recycling Logs" navigation link
    - Add icon for recycling section
    - Ensure active state highlighting works correctly
    - _Requirements: 1.1, 5.1_

  - [ ] 9.2 Add recycling links to admin dashboard sidebar
    - Update admin dashboard layout to include "Recycling" section with sub-items
    - Add navigation links: All Logs, Analytics, Targets
    - Add icon for recycling section
    - Ensure active state highlighting works correctly
    - _Requirements: 6.1, 7.5, 12.1_

- [ ] 10. Implement authorization policies
  - [ ] 10.1 Create RecyclingLogPolicy
    - Create policy class with methods: view, create, update, delete
    - Implement `view()` to allow crew to view own logs, admin to view all
    - Implement `create()` to allow only collection_crew role
    - Implement `update()` to check ownership, edit window, and collection_crew role
    - Implement `delete()` to allow only administrator role
    - Register policy in AuthServiceProvider
    - _Requirements: 5.1, 5.2, 14.1, 14.2_

  - [ ] 10.2 Apply policy checks in controllers
    - Add `authorize()` calls in RecyclingLogController methods
    - Add `authorize()` calls in Admin\RecyclingLogController methods
    - Ensure proper 403 responses for unauthorized access
    - _Requirements: 5.1, 6.1, 14.1, 14.2_

- [ ] 11. Add cache invalidation hooks
  - [ ] 11.1 Implement cache invalidation on log creation
    - Add cache invalidation call in RecyclingLogController@store after successful creation
    - Clear analytics cache tags
    - _Requirements: Performance optimization from design_

  - [ ] 11.2 Implement cache invalidation on log update
    - Add cache invalidation call in RecyclingLogController@update after successful update
    - Clear analytics cache tags
    - _Requirements: Performance optimization from design_

- [ ] 12. Create database seeders for testing
  - [ ] 12.1 Create RecyclingLogSeeder
    - Create seeder to generate sample recycling logs with materials
    - Associate logs with existing users (collection_crew role)
    - Associate logs with existing routes and assignments
    - Generate logs across date range for testing analytics
    - Include some logs with quality issues
    - _Requirements: Testing support_

  - [ ] 12.2 Create RecyclingTargetSeeder
    - Create seeder to generate sample targets for current and previous months
    - Include material-specific targets and total recyclables target
    - _Requirements: 12.1, 12.2, Testing support_

- [ ] 13. Implement responsive design and accessibility
  - [ ] 13.1 Add responsive CSS for mobile and tablet views
    - Convert tables to card layout on small screens using CSS media queries
    - Make filter panels full-screen overlays on mobile
    - Stack material entry form vertically on mobile
    - Ensure touch-friendly button sizes (min 44px)
    - Test charts resize properly on mobile
    - _Requirements: Accessibility from design_

  - [ ] 13.2 Add ARIA labels and accessibility attributes
    - Add semantic HTML structure to all views
    - Add ARIA labels to interactive elements (buttons, form inputs, charts)
    - Ensure keyboard navigation works for all forms
    - Add proper form label associations
    - Ensure error messages are announced to screen readers
    - Add alt text descriptions for charts
    - Verify color contrast ratios meet WCAG 2.1 AA standards
    - _Requirements: Accessibility from design_

- [ ] 14. Run migrations and seed database
  - [ ] 14.1 Execute migrations
    - Run `php artisan migrate` to create new tables
    - Verify tables created successfully with correct schema
    - _Requirements: All database-related requirements_

  - [ ] 14.2 Execute seeders
    - Run `php artisan db:seed --class=RecyclingLogSeeder`
    - Run `php artisan db:seed --class=RecyclingTargetSeeder`
    - Verify sample data created successfully
    - _Requirements: Testing support_

- [ ] 15. Manual testing and validation
  - [ ] 15.1 Test crew workflows
    - Login as collection_crew user
    - Create new recycling log with multiple materials
    - Verify total weight calculation
    - Edit log within 2-hour window
    - Verify edit disabled after 2-hour window
    - Filter logs by date range
    - Verify only own logs are visible
    - _Requirements: 1.1, 1.2, 1.3, 1.5, 3.5, 5.1, 5.4, 14.1, 14.2_

  - [ ] 15.2 Test admin workflows
    - Login as administrator user
    - View all recycling logs with filters
    - Apply date range, material type, and zone filters
    - View log details
    - Export filtered data to CSV and verify file contents
    - View analytics dashboard and verify metrics
    - View material breakdown and verify calculations
    - View zone performance and verify highlighting
    - View trend analysis with different intervals
    - View crew performance rankings
    - Create recycling target
    - Verify target progress displays correctly
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3, 8.4, 8.5, 9.1, 9.2, 9.3, 9.4, 9.5, 10.1, 10.2, 10.3, 10.4, 10.5, 11.1, 11.2, 11.3, 11.4, 11.5, 12.1, 12.2, 12.3, 12.4, 12.5, 13.1, 13.2, 13.3, 13.4, 13.5_
