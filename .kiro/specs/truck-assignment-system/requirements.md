# Requirements Document

## Introduction

The Truck Assignment System enables administrators to manage the fleet of collection trucks and assign them along with drivers to specific routes and schedules. This feature bridges the gap between route planning and actual collection operations, ensuring that each route has the necessary resources (truck and crew) allocated. The system tracks truck operational status and maintains assignment history for accountability and planning purposes.

## Glossary

- **SWEEP System**: The Solid Waste Evaluation and Efficiency Platform web application
- **Truck**: A waste collection vehicle with identifying information and operational status
- **Assignment**: A record linking a Truck, Collection Crew member, Route, and date
- **Administrator**: A user with permissions to manage Trucks and create Assignments
- **Collection Crew**: A user who operates a Truck and performs waste collection
- **Route**: A defined geographic path where waste collection occurs
- **Schedule**: A time-based plan specifying when collection occurs on a Route
- **Operational Status**: The current condition of a Truck (operational, maintenance, out of service)
- **Assignment Date**: The specific date when a Truck and crew are assigned to a Route

## Requirements

### Requirement 1

**User Story:** As an Administrator, I want to register trucks in the system, so that I can track and assign them to collection routes.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a truck registration form requiring truck number, license plate, and capacity
2. WHEN the Administrator submits a valid truck registration form, THE SWEEP System SHALL create a new Truck record
3. THE SWEEP System SHALL validate that the truck number is unique within the system
4. WHEN the Administrator attempts to register a Truck with a duplicate truck number, THE SWEEP System SHALL display an error message and prevent registration
5. THE SWEEP System SHALL allow the Administrator to include optional notes about each Truck

### Requirement 2

**User Story:** As an Administrator, I want to view and manage all trucks in the fleet, so that I can maintain accurate truck information.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a list of all Trucks to the Administrator
2. THE SWEEP System SHALL show the operational status for each Truck
3. WHEN the Administrator selects a Truck, THE SWEEP System SHALL display truck details including number, license plate, capacity, status, and notes
4. THE SWEEP System SHALL allow the Administrator to edit Truck information
5. THE SWEEP System SHALL allow the Administrator to delete Trucks that have no future assignments

### Requirement 3

**User Story:** As an Administrator, I want to set and update truck operational status, so that I can track which trucks are available for assignment.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide three operational status options: operational, maintenance, and out of service
2. THE SWEEP System SHALL allow the Administrator to change a Truck's operational status
3. WHEN a Truck status is changed to maintenance or out of service, THE SWEEP System SHALL display a warning if the Truck has future assignments
4. THE SWEEP System SHALL allow the Administrator to add status change notes
5. THE SWEEP System SHALL record the date and time of each status change

### Requirement 4

**User Story:** As an Administrator, I want to assign trucks and drivers to routes for specific dates, so that collection operations are properly resourced.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an assignment creation form requiring Truck selection, Collection Crew selection, Route selection, and assignment date
2. WHEN the Administrator submits a valid assignment form, THE SWEEP System SHALL create a new Assignment record
3. THE SWEEP System SHALL validate that the selected Truck is operational before creating the Assignment
4. THE SWEEP System SHALL validate that a Truck is not assigned to multiple Routes on the same date
5. THE SWEEP System SHALL validate that a Collection Crew member is not assigned to multiple Routes on the same date

### Requirement 5

**User Story:** As an Administrator, I want to view all assignments in a schedule format, so that I can see which trucks and crews are assigned to which routes.

#### Acceptance Criteria

1. THE SWEEP System SHALL display assignments in a calendar or schedule view
2. THE SWEEP System SHALL allow the Administrator to filter assignments by date range
3. THE SWEEP System SHALL allow the Administrator to filter assignments by Truck or Collection Crew member
4. THE SWEEP System SHALL display Route name, Truck number, and crew member name for each Assignment
5. THE SWEEP System SHALL highlight assignments for the current date

### Requirement 6

**User Story:** As an Administrator, I want to edit or cancel assignments, so that I can adjust to changing operational needs.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow the Administrator to modify Assignment details including Truck, crew, Route, and date
2. WHEN the Administrator updates an Assignment, THE SWEEP System SHALL validate that no conflicts exist
3. THE SWEEP System SHALL allow the Administrator to cancel an Assignment
4. WHEN an Assignment is cancelled, THE SWEEP System SHALL mark it as cancelled rather than deleting it
5. THE SWEEP System SHALL display cancelled assignments separately from active assignments

### Requirement 7

**User Story:** As a Collection Crew member, I want to see my truck assignments, so that I know which truck I'm assigned to and which route to follow.

#### Acceptance Criteria

1. THE SWEEP System SHALL display current day assignments to Collection Crew members on their dashboard
2. WHEN a Collection Crew member has an assignment for the current date, THE SWEEP System SHALL display the Truck number and Route details
3. THE SWEEP System SHALL display the collection schedule time for the assigned Route
4. THE SWEEP System SHALL allow Collection Crew members to view their upcoming assignments for the next fourteen days
5. WHEN a Collection Crew member has no assignments, THE SWEEP System SHALL display a message indicating no assignments

### Requirement 8

**User Story:** As an Administrator, I want to see which trucks are available for assignment on a specific date, so that I can efficiently allocate resources.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a truck availability view for a selected date
2. THE SWEEP System SHALL display all operational Trucks
3. THE SWEEP System SHALL indicate which Trucks already have assignments for the selected date
4. THE SWEEP System SHALL indicate which Trucks are in maintenance or out of service
5. THE SWEEP System SHALL allow the Administrator to create assignments directly from the availability view

### Requirement 9

**User Story:** As an Administrator, I want to see which routes have no truck assignments for upcoming dates, so that I can ensure all routes are covered.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a list of Routes with scheduled collections
2. THE SWEEP System SHALL indicate which Routes lack truck assignments for the next seven days
3. THE SWEEP System SHALL allow the Administrator to filter by date
4. THE SWEEP System SHALL display a warning count of unassigned Routes
5. THE SWEEP System SHALL allow the Administrator to create assignments directly from the unassigned routes view

### Requirement 10

**User Story:** As an Administrator, I want to copy assignments from one date to another, so that I can quickly set up recurring assignment patterns.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an assignment copy function accessible from the assignment schedule view
2. WHEN the Administrator initiates assignment copying, THE SWEEP System SHALL prompt for the source date and target date
3. WHEN the Administrator confirms copying, THE SWEEP System SHALL create new Assignments for the target date with the same Truck, crew, and Route combinations
4. THE SWEEP System SHALL validate that copied assignments do not create conflicts on the target date
5. WHEN copying would create conflicts, THE SWEEP System SHALL display a list of conflicts and allow selective copying

### Requirement 11

**User Story:** As an Administrator, I want to view assignment history for a truck, so that I can track utilization and maintenance scheduling.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an assignment history view for each Truck
2. THE SWEEP System SHALL display all past assignments including date, Route, and crew member
3. THE SWEEP System SHALL allow the Administrator to filter assignment history by date range
4. THE SWEEP System SHALL display the total number of assignments for the selected period
5. THE SWEEP System SHALL allow the Administrator to export assignment history data

### Requirement 12

**User Story:** As an Administrator, I want to receive alerts about assignment gaps, so that I can proactively address scheduling issues.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a dashboard alert when Routes with scheduled collections lack assignments within three days
2. THE SWEEP System SHALL display a dashboard alert when operational Trucks have no assignments for the next seven days
3. THE SWEEP System SHALL display the count of unassigned Routes on the administrator dashboard
4. THE SWEEP System SHALL allow the Administrator to dismiss alerts
5. THE SWEEP System SHALL provide a link from each alert to the relevant assignment creation page
