# Requirements Document

## Introduction

The Recycling Tracker feature enables Collection Crew members to record the types and quantities of recyclable materials collected during their routes. This feature provides data for tracking recycling performance, identifying high-performing zones, and supporting sustainability reporting. Administrators can view recycling statistics, analyze trends, and generate reports to demonstrate environmental impact and program effectiveness.

## Glossary

- **SWEEP System**: The Solid Waste Evaluation and Efficiency Platform web application
- **Recycling Log**: A record documenting recyclable materials collected during a specific collection activity
- **Material Type**: A category of recyclable material (plastic, paper, glass, metal, cardboard, organic)
- **Collection Crew**: A user role with permission to record recycling data during collection activities
- **Administrator**: A user role with permission to view and analyze recycling data across the system
- **Weight**: The quantity of recyclable material measured in kilograms
- **Zone**: A geographic area where recyclable materials are collected
- **Recycling Rate**: The percentage or amount of recyclables collected relative to total waste
- **Assignment**: A work assignment linking a Collection Crew member to a specific route or zone
- **Valid Recycling Log Form**: A form submission containing all required fields (material type, weight, collection date) with values meeting validation rules
- **Edit Window**: A two-hour time period following Recycling Log creation during which the creator may modify the record

## Requirements

### Requirement 1

**User Story:** As a Collection Crew member, I want to record recyclable materials collected during my route, so that recycling data is tracked.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a recycling log form accessible to users with the Collection Crew role
2. THE SWEEP System SHALL require material type, weight in kilograms, and collection date fields when creating a Recycling Log
3. WHEN a Collection Crew member submits a Valid Recycling Log Form, THE SWEEP System SHALL create a new Recycling Log record within 3 seconds
4. WHEN a Collection Crew member has an active Assignment, THE SWEEP System SHALL associate the new Recycling Log with that Assignment
5. THE SWEEP System SHALL allow selection of one to six Material Types in a single Recycling Log entry

### Requirement 2

**User Story:** As a Collection Crew member, I want to select from predefined material types, so that data is consistent across the system.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide exactly six Material Type options: plastic, paper, glass, metal, cardboard, organic
2. THE SWEEP System SHALL display all six Material Types as selectable options in the recycling log form
3. THE SWEEP System SHALL allow the Collection Crew member to select between one and six Material Types per Recycling Log entry
4. WHEN a Collection Crew member attempts to submit a recycling log form without selecting a Material Type, THE SWEEP System SHALL display a validation error message
5. THE SWEEP System SHALL store each selected Material Type with its corresponding Weight value in kilograms

### Requirement 3

**User Story:** As a Collection Crew member, I want to enter the weight of collected materials, so that quantities are accurately recorded.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a Weight input field for each selected Material Type
2. THE SWEEP System SHALL accept Weight values in kilograms with precision up to two decimal places
3. WHEN a Collection Crew member enters a Weight value less than or equal to zero, THE SWEEP System SHALL display a validation error message
4. WHEN a Collection Crew member enters a Weight value less than 0.01 kg or greater than 10000 kg, THE SWEEP System SHALL display a validation error message
5. THE SWEEP System SHALL calculate and display the sum of all Weight values across selected Material Types

### Requirement 4

**User Story:** As a Collection Crew member, I want to add notes to recycling logs, so that I can document special circumstances or observations.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an optional notes text field in the recycling log form
2. THE SWEEP System SHALL accept notes containing up to 500 characters
3. WHEN a Collection Crew member submits a recycling log form with notes, THE SWEEP System SHALL store the notes with the Recycling Log record
4. THE SWEEP System SHALL display notes when viewing a Recycling Log
5. WHILE within the Edit Window, THE SWEEP System SHALL allow the Collection Crew member to modify notes

### Requirement 5

**User Story:** As a Collection Crew member, I want to view my recycling logs, so that I can review what I've recorded.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide a recycling logs list view accessible to users with the Collection Crew role
2. THE SWEEP System SHALL display all Recycling Logs created by the authenticated Collection Crew member
3. THE SWEEP System SHALL display collection date, route identifier, Material Types, total Weight, and notes for each Recycling Log
4. THE SWEEP System SHALL allow the Collection Crew member to filter displayed Recycling Logs by specifying a start date and end date
5. THE SWEEP System SHALL display Recycling Logs ordered by collection date with the most recent date first

### Requirement 6

**User Story:** As an Administrator, I want to view all recycling logs, so that I can monitor recycling activities across the system.

#### Acceptance Criteria

1. THE SWEEP System SHALL display a list of all Recycling Logs to users with the Administrator role
2. THE SWEEP System SHALL allow the Administrator to filter displayed Recycling Logs by specifying a start date and end date
3. THE SWEEP System SHALL allow the Administrator to filter displayed Recycling Logs by selecting one or more Material Types
4. THE SWEEP System SHALL allow the Administrator to filter displayed Recycling Logs by selecting a route identifier or Zone
5. THE SWEEP System SHALL display Collection Crew member name, collection date, route identifier, Material Types, and total Weight for each Recycling Log

### Requirement 7

**User Story:** As an Administrator, I want to see total recycling quantities by material type, so that I can understand what is being recycled most.

#### Acceptance Criteria

1. THE SWEEP System SHALL calculate the sum of Weight values for each Material Type across all Recycling Logs
2. WHEN an Administrator specifies a date range, THE SWEEP System SHALL calculate totals using only Recycling Logs within that date range
3. THE SWEEP System SHALL display Material Types ordered by total Weight with the highest Weight first
4. THE SWEEP System SHALL calculate the percentage each Material Type represents of the sum of all Material Type weights
5. THE SWEEP System SHALL display Material Type totals in both tabular format and graphical chart format

### Requirement 8

**User Story:** As an Administrator, I want to see recycling data by zone, so that I can identify high-performing areas.

#### Acceptance Criteria

1. THE SWEEP System SHALL group Recycling Log data by Zone or route identifier
2. THE SWEEP System SHALL calculate the sum of Weight values for each Zone across all Recycling Logs
3. THE SWEEP System SHALL display Zones ordered by total Weight with the highest Weight first
4. THE SWEEP System SHALL allow the Administrator to filter Zone data by specifying a date range and selecting Material Types
5. WHEN a Zone's total Weight exceeds the average Weight across all Zones, THE SWEEP System SHALL visually highlight that Zone

### Requirement 9

**User Story:** As an Administrator, I want to see recycling trends over time, so that I can track program growth or decline.

#### Acceptance Criteria

1. THE SWEEP System SHALL display Recycling Log Weight data aggregated by time intervals
2. THE SWEEP System SHALL allow the Administrator to select time interval aggregation of daily, weekly, or monthly
3. THE SWEEP System SHALL display total Weight collected for each time interval in a line chart format
4. THE SWEEP System SHALL allow the Administrator to filter time series data by selecting one or more Material Types
5. THE SWEEP System SHALL calculate and display the percentage change in total Weight between consecutive time intervals

### Requirement 10

**User Story:** As an Administrator, I want to see crew performance in recycling collection, so that I can recognize high performers.

#### Acceptance Criteria

1. THE SWEEP System SHALL calculate the sum of Weight values across all Recycling Logs created by each Collection Crew member
2. THE SWEEP System SHALL display Collection Crew members ordered by total Weight with the highest Weight first
3. THE SWEEP System SHALL display the count of Recycling Logs created by each Collection Crew member
4. THE SWEEP System SHALL allow the Administrator to filter crew performance data by specifying a start date and end date
5. THE SWEEP System SHALL calculate the average Weight per Recycling Log for each Collection Crew member by dividing total Weight by Recycling Log count

### Requirement 11

**User Story:** As an Administrator, I want to export recycling data, so that I can use it for external reporting.

#### Acceptance Criteria

1. THE SWEEP System SHALL provide an export function accessible to users with the Administrator role
2. WHEN an Administrator initiates an export, THE SWEEP System SHALL generate a file in CSV format
3. THE SWEEP System SHALL include collection date, Collection Crew member name, route identifier, Zone, Material Types, Weight values, and notes in the exported CSV file
4. THE SWEEP System SHALL include only Recycling Logs matching currently applied filters in the exported CSV file
5. THE SWEEP System SHALL generate the CSV filename containing the text "recycling-export" and the start date and end date of the filtered data

### Requirement 12

**User Story:** As an Administrator, I want to set recycling targets, so that I can measure performance against goals.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow the Administrator to set a monthly target Weight value in kilograms for each Material Type
2. THE SWEEP System SHALL allow the Administrator to set a monthly target Weight value in kilograms for total recyclables across all Material Types
3. THE SWEEP System SHALL calculate and display progress toward each target as a percentage by dividing current month Weight by target Weight
4. WHEN current month Weight equals or exceeds the target Weight, THE SWEEP System SHALL visually highlight that target
5. THE SWEEP System SHALL display all configured targets and their progress percentages on the recycling dashboard view

### Requirement 13

**User Story:** As an Administrator, I want to see recycling rate calculations, so that I can report on program effectiveness.

#### Acceptance Criteria

1. WHEN an Administrator specifies a date range, THE SWEEP System SHALL calculate the sum of all Weight values across Recycling Logs within that date range
2. THE SWEEP System SHALL display Recycling Rate calculated as total Weight divided by number of Recycling Logs or total Weight divided by number of Zones
3. THE SWEEP System SHALL calculate average Recycling Rate by dividing total Weight by the count of Recycling Logs across all data
4. THE SWEEP System SHALL calculate and display the percentage difference between the current date range total Weight and the previous equivalent date range total Weight
5. THE SWEEP System SHALL display Recycling Rate values aggregated by time intervals in a trend chart

### Requirement 14

**User Story:** As a Collection Crew member, I want to edit my recycling logs within a time window, so that I can correct mistakes.

#### Acceptance Criteria

1. WHILE within the Edit Window, THE SWEEP System SHALL allow the Collection Crew member who created a Recycling Log to modify that record
2. WHEN the Edit Window has elapsed, THE SWEEP System SHALL prevent the Collection Crew member from modifying the Recycling Log
3. WHILE within the Edit Window, THE SWEEP System SHALL allow modification of Weight values, Material Type selections, and notes fields
4. WHEN a Collection Crew member saves modifications to a Recycling Log, THE SWEEP System SHALL record the modification timestamp
5. THE SWEEP System SHALL display modification timestamps to users with the Administrator role

### Requirement 15

**User Story:** As an Administrator, I want to see contamination or quality notes, so that I can identify areas needing education.

#### Acceptance Criteria

1. THE SWEEP System SHALL allow Collection Crew members to mark a Recycling Log with a quality issue flag
2. THE SWEEP System SHALL allow Administrators to filter displayed Recycling Logs to show only records with quality issue flags
3. THE SWEEP System SHALL visually distinguish Recycling Logs marked with quality issue flags in list views
4. THE SWEEP System SHALL group Recycling Logs with quality issue flags by Zone for analysis display
5. THE SWEEP System SHALL display the count of quality issue flags aggregated by time intervals in a trend chart
