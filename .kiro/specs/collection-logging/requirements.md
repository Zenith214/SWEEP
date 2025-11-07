# Requirements Document

## Introduction

The Collection Logging feature enables Collection Crew members to record completed waste collection activities in real-time during their routes. This feature provides accountability, tracks operational progress, and allows crews to document issues or special circumstances encountered during collection. Administrators can monitor collection completion rates and review logs for quality assurance and operational planning.

## Glossary

- **SWEEP System**: The Solid Waste Evaluation and Efficiency Platform web application
- **Collection Log**: A record documenting the completion of waste collection for a specific Assignment
- **Assignment**: A record linking a Truck, Collection Crew member, Route, and date
- **Collection Crew**: A user who performs waste collection and creates Collection Logs
- **Administrator**: A user who monitors collection activities and reviews Collection Logs
- **Completion Status**: The state of a collection activity (pending, completed, incomplete, issue reported)
- **Proof Photo**: An image uploaded by Collection Crew as evidence of collection completion
- **Route Issue**: A problem encountered during collection that affects route completion

## Requirements

### Requirement 1

**User Story:** As a Collection Crew member, I want to view my assigned route for the day, so that I know which collection to perform.

#### Acceptance Criteria

1. THE SWEEP System SHALL display the current day's Assignment to Collection Crew members on their dashboard
2. WHEN a Collection Crew member has an Assignment for the current date, THE SWEEP System SHALL display the Route name, zone, and collection time
3. THE SWEEP System SHALL display the Assignment status (pending, completed, incomplete, issue reported)
4. THE SWEEP System SHALL provide a button to start logging collection activities
5. WHEN a Collection Crew member has no Assignment for the current date, THE SWEEP System SHALL display a message indicating no assignment

### Requirement 2

**User Story:** As a Collection Crew member, I want to mark my route as completed, so that I can record successful collection.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a collection completion form accessible from the Assignment view
2. THE SWEEP System SHALL require completion time when marking a route as completed
3. WHEN a Collection Crew member submits the completion form, THE SWEEP System SHALL create a Collection Log record with status completed
4. THE SWEEP System SHALL record the actual completion time provided by the crew member
5. THE SWEEP System SHALL allow the Collection Crew member to add optional notes about the collection

### Requirement 3

**User Story:** As a Collection Crew member, I want to upload photos as proof of collection, so that I can document completed work.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow Collection Crew members to upload up to five photos per Collection Log
2. THE SWEEP System SHALL accept image files in JPEG, PNG, and WEBP formats
3. THE SWEEP System SHALL validate that each uploaded file does not exceed five megabytes
4. WHEN a Collection Crew member uploads a photo, THE SWEEP System SHALL store the image and associate it with the Collection Log
5. THE SWEEP System SHALL display thumbnails of uploaded photos in the Collection Log view

### Requirement 4

**User Story:** As a Collection Crew member, I want to report issues encountered during collection, so that administrators are aware of problems.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an issue reporting option when logging collection activities
2. THE SWEEP System SHALL allow Collection Crew members to select an issue type from predefined options
3. THE SWEEP System SHALL require a description when reporting an issue
4. WHEN a Collection Crew member reports an issue, THE SWEEP System SHALL create a Collection Log with status issue reported
5. THE SWEEP System SHALL allow photo uploads to document the reported issue

### Requirement 5

**User Story:** As a Collection Crew member, I want to mark a route as incomplete, so that I can record partial collection due to circumstances beyond my control.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an incomplete option when logging collection activities
2. THE SWEEP System SHALL require a reason when marking a route as incomplete
3. WHEN a Collection Crew member marks a route as incomplete, THE SWEEP System SHALL create a Collection Log with status incomplete
4. THE SWEEP System SHALL record the percentage of route completed if provided
5. THE SWEEP System SHALL allow the Collection Crew member to add notes explaining the incomplete status

### Requirement 6

**User Story:** As a Collection Crew member, I want to view my past collection logs, so that I can review my work history.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a collection history view for Collection Crew members
2. THE SWEEP System SHALL display all Collection Logs created by the crew member
3. THE SWEEP System SHALL allow filtering by date range
4. THE SWEEP System SHALL display Route name, date, status, and completion time for each log
5. THE SWEEP System SHALL allow the Collection Crew member to view details and photos of past logs

### Requirement 7

**User Story:** As an Administrator, I want to view all collection logs, so that I can monitor collection operations.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a list of all Collection Logs to the Administrator
2. THE SWEEP System SHALL allow the Administrator to filter logs by date range
3. THE SWEEP System SHALL allow the Administrator to filter logs by completion status
4. THE SWEEP System SHALL allow the Administrator to filter logs by Route or crew member
5. THE SWEEP System SHALL display Route name, crew member, date, status, and completion time for each log

### Requirement 8

**User Story:** As an Administrator, I want to view detailed collection log information, so that I can review collection activities and issues.

#### Acceptance Criteria

1. WHEN the Administrator selects a Collection Log, THE SWEEP System SHALL display complete log details
2. THE SWEEP System SHALL display the Assignment information including Truck, crew, Route, and date
3. THE SWEEP System SHALL display completion time, status, and notes
4. THE SWEEP System SHALL display all uploaded photos with full-size viewing capability
5. THE SWEEP System SHALL display issue type and description if an issue was reported

### Requirement 9

**User Story:** As an Administrator, I want to see collection completion rates, so that I can assess operational performance.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a collection completion dashboard for the Administrator
2. THE SWEEP System SHALL calculate and display the percentage of completed collections for a selected date range
3. THE SWEEP System SHALL display the count of collections by status (completed, incomplete, issue reported, pending)
4. THE SWEEP System SHALL allow the Administrator to filter statistics by date range
5. THE SWEEP System SHALL display completion rates grouped by Route or crew member

### Requirement 10

**User Story:** As an Administrator, I want to identify routes with recurring issues, so that I can address systemic problems.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an issue analysis view for the Administrator
2. THE SWEEP System SHALL display Routes with multiple issue reports within a selected time period
3. THE SWEEP System SHALL group issues by issue type
4. THE SWEEP System SHALL display the count of issues per Route
5. THE SWEEP System SHALL allow the Administrator to view all issue logs for a specific Route

### Requirement 11

**User Story:** As an Administrator, I want to add notes to collection logs, so that I can document follow-up actions or observations.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow the Administrator to add administrative notes to any Collection Log
2. THE SWEEP System SHALL distinguish administrative notes from crew notes
3. WHEN the Administrator adds a note, THE SWEEP System SHALL record the administrator's name and timestamp
4. THE SWEEP System SHALL display administrative notes separately from crew notes in the log view
5. THE SWEEP System SHALL allow multiple administrative notes per Collection Log

### Requirement 12

**User Story:** As a Collection Crew member, I want to edit my collection log within a time window, so that I can correct mistakes.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow Collection Crew members to edit their Collection Logs within two hours of creation
2. WHEN the two-hour window has passed, THE SWEEP System SHALL prevent editing by the crew member
3. THE SWEEP System SHALL allow editing of completion time, notes, and status
4. THE SWEEP System SHALL allow adding or removing photos within the edit window
5. WHEN a Collection Log is edited, THE SWEEP System SHALL record the edit timestamp
