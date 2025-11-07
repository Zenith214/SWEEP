# Requirements Document

## Introduction

The Resident Reporting System enables residents to submit complaints or reports about missed pickups, uncollected waste, or other waste management issues. This feature provides a communication channel between residents and administrators, tracks issue resolution, and helps identify service gaps. Administrators can review, update, and respond to resident reports, ensuring accountability and improving service quality.

## Glossary

- **SWEEP System**: The Solid Waste Evaluation and Efficiency Platform web application
- **Report**: A complaint or issue submitted by a Resident regarding waste collection
- **Resident**: A user who submits Reports and tracks their status
- **Administrator**: A user who reviews, updates, and responds to Reports
- **Report Status**: The current state of a Report (pending, in progress, resolved, closed)
- **Report Type**: The category of issue being reported (missed pickup, uncollected waste, illegal dumping, other)
- **Report Photo**: An image uploaded by a Resident to document the reported issue
- **Location**: The address or zone where the reported issue occurred

## Requirements

### Requirement 1

**User Story:** As a Resident, I want to submit a report about waste collection issues, so that administrators are aware of problems in my area.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a report submission form accessible to Residents
2. THE SWEEP System SHALL require report type, location, and description when submitting a report
3. WHEN a Resident submits a valid report form, THE SWEEP System SHALL create a new Report record with status pending
4. THE SWEEP System SHALL assign a unique reference number to each Report
5. THE SWEEP System SHALL display the reference number to the Resident after submission

### Requirement 2

**User Story:** As a Resident, I want to upload photos with my report, so that I can provide visual evidence of the issue.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow Residents to upload up to three photos per Report
2. THE SWEEP System SHALL accept image files in JPEG, PNG, and WEBP formats
3. THE SWEEP System SHALL validate that each uploaded file does not exceed five megabytes
4. WHEN a Resident uploads a photo, THE SWEEP System SHALL store the image and associate it with the Report
5. THE SWEEP System SHALL display thumbnails of uploaded photos in the Report view

### Requirement 3

**User Story:** As a Resident, I want to specify the location of the issue, so that administrators know where to address the problem.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a location input field in the report submission form
2. THE SWEEP System SHALL allow Residents to enter an address or zone identifier
3. THE SWEEP System SHALL validate that the location field is not empty
4. THE SWEEP System SHALL store the location information with the Report
5. THE SWEEP System SHALL display the location prominently in the Report view

### Requirement 4

**User Story:** As a Resident, I want to view all my submitted reports, so that I can track their status.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a reports list view for Residents
2. THE SWEEP System SHALL display all Reports submitted by the Resident
3. THE SWEEP System SHALL display reference number, report type, location, submission date, and status for each Report
4. THE SWEEP System SHALL allow Residents to filter Reports by status
5. THE SWEEP System SHALL display Reports in reverse chronological order (newest first)

### Requirement 5

**User Story:** As a Resident, I want to view detailed information about my reports, so that I can see updates and responses.

#### Acceptance Criteria

1. WHEN a Resident selects a Report, THE SWEEP System SHALL display complete Report details
2. THE SWEEP System SHALL display report type, location, description, submission date, and current status
3. THE SWEEP System SHALL display all uploaded photos with full-size viewing capability
4. THE SWEEP System SHALL display administrator responses if any exist
5. THE SWEEP System SHALL display status change history with timestamps

### Requirement 6

**User Story:** As a Resident, I want to receive updates when my report status changes, so that I know the progress of my issue.

#### Acceptance Criteria

1. WHEN an Administrator changes a Report status, THE SWEEP System SHALL record the status change with timestamp
2. THE SWEEP System SHALL display status change history in the Report details view
3. THE SWEEP System SHALL highlight the most recent status change
4. THE SWEEP System SHALL display the administrator name who made the status change
5. THE SWEEP System SHALL show the date and time of each status change

### Requirement 7

**User Story:** As an Administrator, I want to view all resident reports, so that I can monitor and address issues.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a list of all Reports to the Administrator
2. THE SWEEP System SHALL allow the Administrator to filter Reports by status
3. THE SWEEP System SHALL allow the Administrator to filter Reports by report type
4. THE SWEEP System SHALL allow the Administrator to filter Reports by date range
5. THE SWEEP System SHALL display reference number, resident name, report type, location, date, and status for each Report

### Requirement 8

**User Story:** As an Administrator, I want to view detailed report information, so that I can understand and address the issue.

#### Acceptance Criteria

1. WHEN the Administrator selects a Report, THE SWEEP System SHALL display complete Report details
2. THE SWEEP System SHALL display resident information including name and contact details
3. THE SWEEP System SHALL display report type, location, description, and submission date
4. THE SWEEP System SHALL display all uploaded photos with full-size viewing capability
5. THE SWEEP System SHALL display status change history and administrator responses

### Requirement 9

**User Story:** As an Administrator, I want to update report status, so that I can track the progress of issue resolution.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow the Administrator to change Report status
2. THE SWEEP System SHALL provide four status options: pending, in progress, resolved, closed
3. WHEN the Administrator changes status, THE SWEEP System SHALL record the change with administrator name and timestamp
4. THE SWEEP System SHALL require a status update note when changing status
5. THE SWEEP System SHALL display the updated status immediately to both Administrator and Resident

### Requirement 10

**User Story:** As an Administrator, I want to add responses to reports, so that I can communicate with residents about their issues.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow the Administrator to add response messages to any Report
2. THE SWEEP System SHALL record the administrator name and timestamp with each response
3. WHEN the Administrator adds a response, THE SWEEP System SHALL associate it with the Report
4. THE SWEEP System SHALL display all responses in chronological order in the Report view
5. THE SWEEP System SHALL allow multiple responses per Report

### Requirement 11

**User Story:** As an Administrator, I want to assign reports to specific routes or collection crews, so that issues can be addressed during collection.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow the Administrator to link a Report to a specific Route
2. THE SWEEP System SHALL allow the Administrator to link a Report to a specific Collection Crew member
3. WHEN a Report is assigned, THE SWEEP System SHALL record the assignment with timestamp
4. THE SWEEP System SHALL display assignment information in the Report details view
5. THE SWEEP System SHALL allow the Administrator to change or remove assignments

### Requirement 12

**User Story:** As an Administrator, I want to see reports grouped by location, so that I can identify problem areas.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a location-based report analysis view
2. THE SWEEP System SHALL group Reports by location or zone
3. THE SWEEP System SHALL display the count of Reports per location
4. THE SWEEP System SHALL allow the Administrator to filter by date range
5. THE SWEEP System SHALL highlight locations with multiple Reports

### Requirement 13

**User Story:** As an Administrator, I want to see reports grouped by type, so that I can identify common issues.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a report type analysis view
2. THE SWEEP System SHALL display the count of Reports by type
3. THE SWEEP System SHALL calculate the percentage of each report type
4. THE SWEEP System SHALL allow the Administrator to filter by date range
5. THE SWEEP System SHALL display a chart or graph showing report type distribution

### Requirement 14

**User Story:** As an Administrator, I want to see average resolution time for reports, so that I can assess response performance.

#### Acceptance Criteria

1. THE SWEEP System SHALL calculate the time between Report submission and resolution
2. THE SWEEP System SHALL display the average resolution time for a selected date range
3. THE SWEEP System SHALL display resolution time grouped by report type
4. THE SWEEP System SHALL identify Reports that exceed a target resolution time
5. THE SWEEP System SHALL display resolution time trends over time

### Requirement 15

**User Story:** As a Resident, I want to search for my reports by reference number, so that I can quickly find a specific report.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a search function on the Resident reports list view
2. THE SWEEP System SHALL allow searching by reference number
3. WHEN a Resident enters a valid reference number, THE SWEEP System SHALL display the matching Report
4. WHEN a Resident enters an invalid reference number, THE SWEEP System SHALL display a "not found" message
5. THE SWEEP System SHALL only allow Residents to search their own Reports
