# SWEEP User Manual
## Solid Waste Evaluation and Efficiency Platform

**Version 1.0**  
**Last Updated: December 2024**

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Getting Started](#2-getting-started)
3. [User Roles and Access](#3-user-roles-and-access)
4. [Administrator Guide](#4-administrator-guide)
5. [Collection Crew Guide](#5-collection-crew-guide)
6. [Resident Guide](#6-resident-guide)
7. [Common Tasks](#7-common-tasks)
8. [Troubleshooting](#8-troubleshooting)
9. [Frequently Asked Questions](#9-frequently-asked-questions)
10. [Support and Contact](#10-support-and-contact)

---

## 1. Introduction

### 1.1 What is SWEEP?

SWEEP (Solid Waste Evaluation and Efficiency Platform) is a comprehensive web-based waste management system designed to streamline community waste collection operations. The platform enables administrators, collection crews, and residents to collaborate efficiently in managing waste collection schedules, routes, reporting, and recycling analytics.

### 1.2 Key Benefits

- **Centralized Management**: All waste collection operations in one platform
- **Real-Time Tracking**: Monitor collection progress and crew performance
- **Transparent Communication**: Residents can view schedules and report issues
- **Data-Driven Decisions**: Comprehensive analytics and reporting
- **Improved Efficiency**: Optimized route planning and resource allocation
- **Environmental Impact**: Track recycling rates and sustainability goals

### 1.3 System Requirements

**For Users:**
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Internet connection
- Minimum screen resolution: 1024x768 (responsive design supports mobile devices)

**For Administrators:**
- All user requirements plus
- Access to system configuration
- Understanding of waste collection operations


---

## 2. Getting Started

### 2.1 Accessing the System

1. Open your web browser
2. Navigate to the SWEEP URL provided by your administrator
3. You will see the landing page with a login option

### 2.2 Logging In

1. Click the **Login** button on the landing page
2. Enter your email address
3. Enter your password
4. Click **Log In**

**First-Time Login:**
- You will receive login credentials from your administrator
- It is recommended to change your password after first login

### 2.3 Dashboard Overview

After logging in, you will be directed to your role-specific dashboard:

- **Administrator Dashboard**: Comprehensive metrics, analytics, and system management
- **Collection Crew Dashboard**: Assigned routes, upcoming collections, and performance metrics
- **Resident Dashboard**: Collection schedules, report tracking, and announcements

### 2.4 Navigation

The system uses a sidebar navigation menu with the following sections (varies by role):

- **Dashboard**: Main overview and metrics
- **Routes**: Route management and viewing
- **Schedules**: Collection schedules
- **Assignments**: Crew and truck assignments
- **Reports**: Issue reporting and tracking
- **Recycling**: Recycling logs and analytics
- **Users**: User management (admin only)
- **Profile**: Personal settings and password change

### 2.5 Changing Your Password

1. Click on your name in the top-right corner
2. Select **Profile**
3. Scroll to the **Update Password** section
4. Enter your current password
5. Enter your new password
6. Confirm your new password
7. Click **Save**


---

## 3. User Roles and Access

### 3.1 Administrator

**Access Level**: Full system access

**Responsibilities:**
- Manage user accounts and roles
- Create and manage collection routes
- Schedule waste collection operations
- Assign trucks and crews to routes
- Monitor system performance and analytics
- Review and respond to resident reports
- Generate reports and export data
- Configure system settings

**Key Features:**
- User management
- Route planning and optimization
- Schedule creation and management
- Truck fleet management
- Assignment coordination
- Report management and resolution
- Analytics and reporting dashboards
- Recycling tracking and targets

### 3.2 Collection Crew

**Access Level**: Limited to assigned tasks and personal data

**Responsibilities:**
- View assigned routes and schedules
- Log completed collections
- Upload proof of collection (photos)
- Report route issues or obstacles
- Record recycling materials collected
- Track personal performance metrics

**Key Features:**
- View assigned routes
- Collection logging with photo upload
- Recycling log entry
- Performance tracking
- Route history viewing
- Issue reporting

### 3.3 Resident

**Access Level**: Limited to personal reports and public schedules

**Responsibilities:**
- View waste collection schedules for their area
- Submit reports for missed pickups or issues
- Track status of submitted reports
- View community announcements

**Key Features:**
- Schedule viewing by zone/area
- Interactive calendar view
- Report submission with photo upload
- Report tracking and status updates
- Search schedules by location


---

## 4. Administrator Guide

### 4.1 Dashboard Overview

The administrator dashboard provides a comprehensive view of system operations:

**Key Metrics Displayed:**
- Total collections completed (today, this week, this month)
- Pending resident reports
- Active routes and assignments
- Recycling rates and targets
- Crew performance metrics
- Geographic distribution of collections

**Interactive Features:**
- Date range filtering
- Drill-down navigation for detailed insights
- Real-time data refresh
- Export capabilities (PDF, Excel, CSV)
- Alert notifications for critical issues

### 4.2 User Management

#### Creating a New User

1. Navigate to **Users** in the sidebar
2. Click **Create New User**
3. Fill in the required information:
   - Name
   - Email address
   - Password
   - Role (Administrator, Collection Crew, or Resident)
4. Click **Create User**

**Note**: The user will receive their login credentials via email (if email is configured).

#### Editing User Information

1. Navigate to **Users**
2. Find the user in the list
3. Click **Edit** next to their name
4. Update the necessary information
5. Click **Save Changes**

#### Changing User Roles

1. Navigate to **Users**
2. Find the user in the list
3. Click **Change Role**
4. Select the new role from the dropdown
5. Click **Update Role**

#### Deactivating/Deleting Users

1. Navigate to **Users**
2. Find the user in the list
3. Click **Delete** next to their name
4. Confirm the deletion

**Warning**: Deleting a user is permanent and cannot be undone.


### 4.3 Route Management

#### Creating a New Route

1. Navigate to **Routes** in the sidebar
2. Click **Create New Route**
3. Enter route details:
   - Route name (e.g., "Zone A - North District")
   - Zone/area identifier
   - Description
   - Waypoints or coverage area
4. Click **Create Route**

#### Editing Routes

1. Navigate to **Routes**
2. Find the route in the list
3. Click **Edit**
4. Update the necessary information
5. Click **Save Changes**

#### Viewing Route Details

1. Navigate to **Routes**
2. Click on a route name to view:
   - Route information
   - Assigned schedules
   - Collection history
   - Performance metrics
   - Issue reports

#### Deleting Routes

1. Navigate to **Routes**
2. Find the route in the list
3. Click **Delete**
4. Confirm the deletion

**Note**: Routes with active assignments cannot be deleted.

### 4.4 Schedule Management

#### Creating a Collection Schedule

1. Navigate to **Schedules** in the sidebar
2. Click **Create New Schedule**
3. Fill in schedule details:
   - Schedule name
   - Select route
   - Collection days (Monday, Tuesday, etc.)
   - Start time
   - Estimated duration
   - Frequency (weekly, bi-weekly, monthly)
   - Active status
4. Click **Create Schedule**

#### Duplicating Schedules

1. Navigate to **Schedules**
2. Find the schedule to duplicate
3. Click **Duplicate**
4. Modify the details as needed
5. Click **Save**

**Use Case**: Quickly create similar schedules for different zones.

#### Activating/Deactivating Schedules

1. Navigate to **Schedules**
2. Find the schedule in the list
3. Toggle the **Active** switch
4. Confirm the change

**Note**: Inactive schedules will not appear in crew assignments or resident views.


### 4.5 Holiday Management

#### Adding Holidays

1. Navigate to **Holidays** in the sidebar
2. Click **Add Holiday**
3. Enter holiday details:
   - Holiday name
   - Date
   - Description (optional)
4. Click **Save**

**Effect**: Collections scheduled on holidays will be automatically flagged, and alternative dates can be assigned.

#### Managing Holidays

1. Navigate to **Holidays**
2. View all upcoming and past holidays
3. Edit or delete holidays as needed

### 4.6 Truck Management

#### Adding a New Truck

1. Navigate to **Trucks** in the sidebar
2. Click **Add New Truck**
3. Enter truck details:
   - Truck number/identifier
   - License plate
   - Capacity
   - Status (operational, maintenance, out of service)
   - Notes
4. Click **Save**

#### Updating Truck Status

1. Navigate to **Trucks**
2. Find the truck in the list
3. Click **Update Status**
4. Select new status:
   - Operational
   - Under Maintenance
   - Out of Service
5. Add notes if necessary
6. Click **Update**

#### Viewing Truck History

1. Navigate to **Trucks**
2. Click on a truck to view:
   - Assignment history
   - Maintenance records
   - Performance metrics
   - Current status

### 4.7 Assignment Management

#### Creating Assignments

1. Navigate to **Assignments** in the sidebar
2. Click **Create New Assignment**
3. Fill in assignment details:
   - Select date
   - Select route
   - Select truck
   - Select crew member(s)
   - Add notes (optional)
4. Click **Create Assignment**

**Validation**: The system will check for:
- Truck availability
- Crew availability
- Schedule conflicts

#### Viewing Assignments Calendar

1. Navigate to **Assignments**
2. Click **Calendar View**
3. View assignments by:
   - Day
   - Week
   - Month
4. Click on any assignment to view details

#### Copying Assignments

1. Navigate to **Assignments**
2. Click **Copy Assignments**
3. Select source date range
4. Select target date range
5. Choose which assignments to copy
6. Click **Copy**

**Use Case**: Quickly replicate weekly schedules.

#### Canceling Assignments

1. Navigate to **Assignments**
2. Find the assignment
3. Click **Cancel**
4. Enter cancellation reason
5. Confirm cancellation

**Note**: Crew members will be notified of cancellations.


### 4.8 Collection Log Management

#### Viewing Collection Logs

1. Navigate to **Collection Logs** in the sidebar
2. View all collection logs with filters:
   - Date range
   - Route
   - Crew member
   - Status (completed, incomplete, issues reported)
3. Click on any log to view details

#### Reviewing Collection Details

Each collection log shows:
- Assignment information
- Completion status
- Photos uploaded by crew
- Issues reported
- Notes and comments
- Timestamp

#### Adding Administrative Notes

1. Open a collection log
2. Scroll to the **Notes** section
3. Enter your note
4. Click **Add Note**

**Use Case**: Document follow-up actions or administrative decisions.

#### Issue Analysis

1. Navigate to **Collection Logs**
2. Click **Issue Analysis**
3. View:
   - Common issues by route
   - Issue frequency trends
   - Resolution status
   - Geographic distribution

### 4.9 Report Management

#### Viewing Resident Reports

1. Navigate to **Reports** in the sidebar
2. View all reports with filters:
   - Status (pending, in progress, resolved, closed)
   - Type (missed pickup, illegal dumping, damaged bin, etc.)
   - Date range
   - Location/zone
3. Click on any report to view details

#### Updating Report Status

1. Open a report
2. Click **Update Status**
3. Select new status:
   - Pending
   - In Progress
   - Resolved
   - Closed
4. Add status notes
5. Click **Update**

#### Responding to Reports

1. Open a report
2. Scroll to the **Responses** section
3. Enter your response message
4. Click **Add Response**

**Note**: Residents can view responses in their dashboard.

#### Assigning Reports

1. Open a report
2. Click **Assign**
3. Select crew member or department
4. Add assignment notes
5. Click **Assign**

**Effect**: The assigned person will see the report in their dashboard.


### 4.10 Analytics and Reporting

#### Collection Analytics

1. Navigate to **Analytics** → **Collections**
2. View comprehensive metrics:
   - Completion rates by route
   - Status breakdown (completed, incomplete, issues)
   - Crew performance comparison
   - Route performance trends
3. Filter by:
   - Date range
   - Route
   - Crew member
4. Export data as PDF, Excel, or CSV

#### Report Analytics

1. Navigate to **Analytics** → **Reports**
2. View analysis of resident reports:
   - Type distribution (pie chart)
   - Resolution times (bar chart)
   - Status trends over time
   - Location analysis
3. Identify problem areas and patterns
4. Export analytics for presentations

#### Recycling Analytics

1. Navigate to **Recycling** → **Analytics**
2. View recycling performance:
   - Material breakdown (plastic, paper, glass, metal)
   - Zone performance comparison
   - Trend analysis over time
   - Crew performance metrics
   - Target achievement progress
3. Set and track recycling targets
4. Export recycling reports

### 4.11 Recycling Target Management

#### Setting Recycling Targets

1. Navigate to **Recycling** → **Targets**
2. Click **Create New Target**
3. Enter target details:
   - Material type
   - Target weight/volume
   - Time period (monthly, quarterly, yearly)
   - Zone (optional)
4. Click **Save**

#### Monitoring Target Progress

1. Navigate to **Recycling** → **Targets**
2. View progress bars for each target
3. See percentage achieved
4. View historical performance

### 4.12 Scheduled Reports

#### Creating Automated Reports

1. Navigate to **Scheduled Reports**
2. Click **Create New Scheduled Report**
3. Configure report:
   - Report name
   - Report type (collections, recycling, resident reports)
   - Frequency (daily, weekly, monthly)
   - Format (PDF, Excel, CSV)
   - Recipients (email addresses)
   - Filters and parameters
4. Click **Create**

**Effect**: Reports will be automatically generated and emailed according to schedule.

#### Managing Scheduled Reports

1. Navigate to **Scheduled Reports**
2. View all scheduled reports
3. Toggle active/inactive status
4. Edit or delete reports
5. View generated report history
6. Download past reports


### 4.13 Truck Availability

#### Checking Truck Availability

1. Navigate to **Truck Availability**
2. Select date range
3. View availability calendar showing:
   - Available trucks (green)
   - Assigned trucks (blue)
   - Under maintenance (yellow)
   - Out of service (red)
4. Click on any truck to see assignment details

**Use Case**: Plan assignments and identify scheduling conflicts.

### 4.14 Exporting Data

#### Dashboard Export

1. From any dashboard view
2. Click **Export** button
3. Select format:
   - PDF (for presentations)
   - Excel (for data analysis)
   - CSV (for database import)
4. Choose date range and filters
5. Click **Download**

#### Bulk Data Export

1. Navigate to the relevant section (Collections, Reports, Recycling)
2. Apply desired filters
3. Click **Export All**
4. Select format
5. Download file

---

## 5. Collection Crew Guide

### 5.1 Crew Dashboard

The crew dashboard displays:
- Today's assignments
- Upcoming collections
- Personal performance metrics
- Recent collection history
- Pending tasks

### 5.2 Viewing Assigned Routes

#### Today's Assignments

1. Log in to your account
2. View **Today's Assignments** on the dashboard
3. Click on an assignment to view:
   - Route details
   - Collection points
   - Scheduled time
   - Truck assigned
   - Special instructions

#### Upcoming Assignments

1. Navigate to **Assignments** in the sidebar
2. Click **Upcoming**
3. View all future assignments
4. Filter by date range

### 5.3 Logging Collections

#### Starting a Collection

1. Navigate to **Collections**
2. Find today's assignment
3. Click **Log Collection**
4. The system will record the start time

#### Completing a Collection

1. Open the active collection log
2. Select completion status:
   - **Completed**: All collection points serviced
   - **Partially Completed**: Some points missed
   - **Incomplete**: Unable to complete route
3. Add notes about the collection
4. Upload photos (required for proof)
5. Click **Submit**


### 5.4 Uploading Photos

#### Adding Collection Photos

1. Open the collection log
2. Click **Upload Photo**
3. Select photo from your device
4. Add caption (optional)
5. Click **Upload**

**Best Practices:**
- Take clear, well-lit photos
- Capture before and after shots
- Document any issues or obstacles
- Include location context

**Photo Requirements:**
- Maximum file size: 5MB
- Supported formats: JPG, PNG
- Minimum resolution: 800x600

#### Managing Photos

1. View uploaded photos in the collection log
2. Click on a photo to view full size
3. Delete photos if needed (before submission)

**Note**: Photos cannot be deleted after the collection log is submitted.

### 5.5 Reporting Issues

#### Logging Route Issues

1. Open the collection log
2. Check **Issues Encountered**
3. Select issue type:
   - Blocked access
   - Truck malfunction
   - Weather conditions
   - Safety hazard
   - Other
4. Describe the issue in detail
5. Upload supporting photos
6. Submit the log

**Effect**: Issues are immediately visible to administrators for follow-up.

### 5.6 Recording Recycling Materials

#### Creating a Recycling Log

1. Navigate to **Recycling Logs**
2. Click **Create New Log**
3. Enter collection details:
   - Date
   - Route/zone
   - Material type (plastic, paper, glass, metal, other)
   - Weight or volume
   - Notes
4. Click **Save**

#### Editing Recycling Logs

1. Navigate to **Recycling Logs**
2. Find the log to edit
3. Click **Edit**
4. Update information
5. Click **Save Changes**

**Note**: Logs can only be edited on the same day they were created.

### 5.7 Viewing Performance Metrics

#### Personal Performance Dashboard

1. Navigate to **Dashboard**
2. View your metrics:
   - Collections completed this week/month
   - On-time completion rate
   - Average collection time
   - Issues reported
   - Recycling materials collected
3. Compare with team averages

#### Collection History

1. Navigate to **Collections** → **History**
2. View all past collections
3. Filter by:
   - Date range
   - Route
   - Status
4. Click on any collection to view details


---

## 6. Resident Guide

### 6.1 Resident Dashboard

The resident dashboard displays:
- Upcoming collection schedule for your area
- Status of submitted reports
- Recent announcements
- Quick links to submit reports

### 6.2 Viewing Collection Schedules

#### Finding Your Collection Schedule

1. Log in to your account
2. Navigate to **Schedules**
3. Your area's schedule is displayed automatically
4. View:
   - Next collection date
   - Collection type (regular waste, recycling)
   - Estimated time window

#### Searching by Location

1. Navigate to **Schedules**
2. Click **Search by Location**
3. Enter your:
   - Street address, or
   - Zone identifier, or
   - Postal code
4. Click **Search**
5. View matching schedules

#### Calendar View

1. Navigate to **Schedules**
2. Click **Calendar View**
3. View all collection dates for the month
4. Click on any date to see details
5. Color coding:
   - Green: Regular waste collection
   - Blue: Recycling collection
   - Yellow: Special collection
   - Red: Holiday (no collection)

### 6.3 Submitting Reports

#### Creating a New Report

1. Navigate to **Reports**
2. Click **Submit New Report**
3. Fill in report details:
   - Report type:
     - Missed Pickup
     - Illegal Dumping
     - Damaged Bin
     - Overflowing Bin
     - Other Issue
   - Location (address or zone)
   - Description (be specific)
   - Upload photos (optional but recommended)
4. Click **Submit Report**

**Confirmation**: You will receive a report reference number for tracking.

#### Best Practices for Reporting

- **Be Specific**: Include exact location and time
- **Add Photos**: Visual evidence helps resolution
- **Provide Context**: Explain the issue clearly
- **Include Contact Info**: Ensure your profile has current contact details

### 6.4 Tracking Reports

#### Viewing Your Reports

1. Navigate to **Reports**
2. View all your submitted reports
3. See status for each:
   - **Pending**: Report received, awaiting review
   - **In Progress**: Being addressed by crew
   - **Resolved**: Issue has been fixed
   - **Closed**: Report completed

#### Checking Report Details

1. Click on any report to view:
   - Report details and photos
   - Current status
   - Administrator responses
   - Resolution notes
   - Timeline of updates

#### Searching Reports

1. Navigate to **Reports**
2. Click **Search**
3. Filter by:
   - Status
   - Date range
   - Report type
4. View filtered results


### 6.5 Understanding Report Status

**Pending**
- Your report has been received
- Awaiting administrator review
- Typical response time: 24-48 hours

**In Progress**
- Issue has been assigned to crew
- Work is being scheduled or underway
- Check for updates regularly

**Resolved**
- Issue has been addressed
- Review resolution notes
- Confirm if issue is fixed

**Closed**
- Report is complete
- No further action needed
- Can be reopened if issue persists

### 6.6 Viewing Announcements

1. Check the dashboard for announcements
2. View important notices about:
   - Schedule changes
   - Holiday adjustments
   - Service interruptions
   - Community initiatives
   - Recycling programs

---

## 7. Common Tasks

### 7.1 Changing Your Profile Information

1. Click on your name in the top-right corner
2. Select **Profile**
3. Update your information:
   - Name
   - Email
   - Contact number
   - Address (for residents)
4. Click **Save**

### 7.2 Updating Your Password

1. Navigate to **Profile**
2. Scroll to **Update Password**
3. Enter current password
4. Enter new password (minimum 8 characters)
5. Confirm new password
6. Click **Save**

**Password Requirements:**
- Minimum 8 characters
- Mix of letters and numbers recommended
- Avoid common words or personal information

### 7.3 Logging Out

1. Click on your name in the top-right corner
2. Select **Logout**
3. You will be redirected to the login page

**Security Tip**: Always log out when using shared computers.

### 7.4 Recovering Your Password

1. On the login page, click **Forgot Password?**
2. Enter your email address
3. Click **Send Reset Link**
4. Check your email for reset instructions
5. Click the link in the email
6. Enter your new password
7. Confirm the password
8. Click **Reset Password**

**Note**: Reset links expire after 60 minutes.


### 7.5 Using Filters and Search

Most list views include filtering options:

1. Look for the **Filter** or **Search** section
2. Select filter criteria:
   - Date range
   - Status
   - Category
   - Location
3. Click **Apply Filters**
4. Results update automatically
5. Click **Clear Filters** to reset

### 7.6 Exporting Data

Where available, you can export data:

1. Apply desired filters
2. Click **Export** button
3. Select format (PDF, Excel, CSV)
4. Click **Download**
5. File will download to your device

### 7.7 Printing Reports

1. Navigate to the report or page you want to print
2. Click **Print** button (if available), or
3. Use browser print function (Ctrl+P or Cmd+P)
4. Adjust print settings
5. Click **Print**

---

## 8. Troubleshooting

### 8.1 Login Issues

**Problem**: Cannot log in

**Solutions**:
- Verify email and password are correct
- Check Caps Lock is off
- Clear browser cache and cookies
- Try a different browser
- Use password reset if forgotten
- Contact administrator if account is locked

**Problem**: "Account not verified" message

**Solutions**:
- Check email for verification link
- Click the verification link
- Request new verification email
- Contact administrator for manual verification

### 8.2 Page Loading Issues

**Problem**: Pages load slowly or not at all

**Solutions**:
- Check internet connection
- Refresh the page (F5 or Ctrl+R)
- Clear browser cache
- Try a different browser
- Disable browser extensions temporarily
- Check if system is under maintenance

**Problem**: Images not displaying

**Solutions**:
- Wait for images to load (may take time on slow connections)
- Refresh the page
- Check browser settings allow images
- Try a different browser

### 8.3 Photo Upload Issues

**Problem**: Cannot upload photos

**Solutions**:
- Check file size (maximum 5MB)
- Verify file format (JPG or PNG only)
- Try compressing the image
- Check internet connection
- Try a different photo
- Clear browser cache

**Problem**: Photo upload fails

**Solutions**:
- Ensure stable internet connection
- Reduce image file size
- Try uploading one photo at a time
- Check available storage space
- Contact administrator if problem persists


### 8.4 Data Not Updating

**Problem**: Dashboard or data appears outdated

**Solutions**:
- Refresh the page
- Clear browser cache
- Log out and log back in
- Check system status with administrator
- Verify you have the latest data permissions

### 8.5 Form Submission Errors

**Problem**: Cannot submit forms

**Solutions**:
- Check all required fields are filled
- Verify data format (dates, numbers, etc.)
- Check for error messages on the form
- Try submitting again
- Clear browser cache
- Contact administrator if error persists

### 8.6 Permission Errors

**Problem**: "Access Denied" or "Unauthorized" messages

**Solutions**:
- Verify you're logged in
- Check you have the correct role/permissions
- Log out and log back in
- Contact administrator to verify access rights
- Ensure your account is active

### 8.7 Mobile Device Issues

**Problem**: Layout appears broken on mobile

**Solutions**:
- Rotate device to landscape mode
- Zoom out if content is too large
- Update mobile browser to latest version
- Try a different mobile browser
- Use desktop version if mobile view is problematic

### 8.8 Browser Compatibility

**Recommended Browsers**:
- Google Chrome (latest version)
- Mozilla Firefox (latest version)
- Safari (latest version)
- Microsoft Edge (latest version)

**Not Recommended**:
- Internet Explorer (any version)
- Outdated browser versions

### 8.9 Getting Additional Help

If you cannot resolve an issue:

1. Note the exact error message
2. Take a screenshot if possible
3. Document steps that led to the issue
4. Contact your administrator with:
   - Your username
   - Description of the problem
   - Error messages
   - Screenshots
   - Browser and device information

---

## 9. Frequently Asked Questions

### 9.1 General Questions

**Q: How do I know which role I have?**

A: Your role is displayed in the top-right corner of the dashboard. You can also check your profile page.

**Q: Can I have multiple roles?**

A: No, each user account has one primary role. Contact your administrator if you need different access.

**Q: How often is data updated?**

A: Most data updates in real-time. Dashboard metrics may cache for 5-30 minutes for performance.

**Q: Can I access SWEEP from my mobile phone?**

A: Yes, SWEEP is fully responsive and works on mobile devices, tablets, and desktops.

**Q: Is my data secure?**

A: Yes, SWEEP uses industry-standard security measures including encrypted connections, secure authentication, and role-based access control.


### 9.2 Administrator Questions

**Q: How many users can I create?**

A: There is no hard limit, but performance is optimized for typical municipal operations (hundreds to thousands of users).

**Q: Can I bulk import users?**

A: Currently, users must be created individually. Contact support for bulk import options.

**Q: How do I handle crew schedule changes?**

A: Cancel the existing assignment and create a new one, or edit the assignment if it hasn't started.

**Q: Can I restore deleted data?**

A: No, deletions are permanent. Use caution when deleting routes, schedules, or users.

**Q: How long is data retained?**

A: All data is retained indefinitely unless manually deleted. Configure automated cleanup for old logs if needed.

### 9.3 Collection Crew Questions

**Q: What if I can't complete a route?**

A: Log the collection as "Incomplete," document the reason, and upload photos. Notify your supervisor immediately.

**Q: Can I edit a collection log after submission?**

A: Logs can only be edited on the same day they were created. Contact your administrator for corrections after that.

**Q: What if the truck breaks down?**

A: Report the issue in the collection log, mark as incomplete, and contact your supervisor immediately.

**Q: Do I need to upload photos for every collection?**

A: Yes, photos serve as proof of collection and are required for completed logs.

**Q: Can I see other crew members' performance?**

A: No, you can only view your own performance metrics. Administrators can view all crew performance.

### 9.4 Resident Questions

**Q: How do I find my collection schedule?**

A: Log in and navigate to Schedules. Your area's schedule is displayed automatically, or use the search function.

**Q: What if my pickup was missed?**

A: Submit a "Missed Pickup" report with your location and details. Include photos if possible.

**Q: How long does it take to resolve a report?**

A: Response times vary by issue type and severity. Typical resolution is 24-72 hours. Check your report status for updates.

**Q: Can I submit anonymous reports?**

A: No, you must be logged in to submit reports. This ensures proper follow-up and communication.

**Q: What if my issue isn't listed in report types?**

A: Select "Other Issue" and provide a detailed description in the comments.

**Q: Will I be notified when my report is resolved?**

A: If email notifications are enabled, you'll receive updates. Otherwise, check your dashboard regularly.


### 9.5 Technical Questions

**Q: What browsers are supported?**

A: Chrome, Firefox, Safari, and Edge (latest versions). Internet Explorer is not supported.

**Q: Do I need to install any software?**

A: No, SWEEP is web-based and requires only a browser and internet connection.

**Q: Can I use SWEEP offline?**

A: No, SWEEP requires an internet connection to function.

**Q: What happens if I lose internet connection while submitting data?**

A: Your submission may fail. Check your connection and try again. Data is not saved until successfully submitted.

**Q: How do I report a bug or suggest a feature?**

A: Contact your administrator, who will forward feedback to the development team.

---

## 10. Support and Contact

### 10.1 Getting Help

**For Technical Issues:**
- Check this user manual first
- Review the Troubleshooting section
- Contact your system administrator

**For Account Issues:**
- Password resets: Use "Forgot Password" on login page
- Account access: Contact your administrator
- Role changes: Request through your administrator

**For Operational Questions:**
- Schedule inquiries: Check Schedules section or contact administrator
- Report status: Check your Reports dashboard
- Collection issues: Submit a report or contact crew supervisor

### 10.2 Administrator Contact

Your system administrator contact information:

- **Name**: [To be filled by organization]
- **Email**: [To be filled by organization]
- **Phone**: [To be filled by organization]
- **Office Hours**: [To be filled by organization]

### 10.3 Emergency Contacts

For urgent waste management issues:

- **Emergency Hotline**: [To be filled by organization]
- **After Hours**: [To be filled by organization]
- **Hazardous Waste**: [To be filled by organization]

### 10.4 System Status

To check system status and scheduled maintenance:

- **Status Page**: [To be filled by organization]
- **Maintenance Schedule**: Check dashboard announcements
- **Downtime Notifications**: Via email (if configured)

### 10.5 Training and Resources

**Available Resources:**
- This User Manual (PDF version available)
- Video tutorials (if available)
- Quick reference guides
- Training sessions (contact administrator)

**Training Requests:**
- Contact your administrator to schedule training
- Group training available for new users
- Role-specific training sessions


### 10.6 Feedback and Suggestions

We value your feedback to improve SWEEP:

**How to Provide Feedback:**
- Contact your administrator with suggestions
- Report bugs or issues through proper channels
- Participate in user surveys when available
- Join user feedback sessions

**What to Include:**
- Clear description of suggestion or issue
- Screenshots if applicable
- Steps to reproduce (for bugs)
- Impact on your workflow
- Suggested improvements

---

## Appendix A: Glossary

**Administrator**: User with full system access and management capabilities

**Assignment**: Scheduled task linking a crew, truck, and route for a specific date

**Collection Crew**: Users responsible for waste collection operations

**Collection Log**: Record of a completed or attempted waste collection

**Dashboard**: Main overview page showing key metrics and information

**Holiday**: Non-collection day that affects regular schedules

**Recycling Log**: Record of recyclable materials collected

**Report**: Issue or complaint submitted by a resident

**Resident**: Community member using the system to view schedules and submit reports

**Route**: Defined path or area for waste collection

**Schedule**: Recurring collection plan for a specific route

**Truck**: Vehicle used for waste collection

**Zone**: Geographic area or district for collection organization

---

## Appendix B: Keyboard Shortcuts

**General Navigation:**
- `Ctrl + /` or `Cmd + /`: Open search
- `Esc`: Close modal or dialog
- `Tab`: Navigate between form fields
- `Enter`: Submit form (when in text field)

**Dashboard:**
- `Ctrl + R` or `Cmd + R`: Refresh data
- `Ctrl + E` or `Cmd + E`: Export data (when available)

**Lists and Tables:**
- `Arrow Keys`: Navigate through items
- `Page Up/Down`: Scroll through pages
- `Home/End`: Jump to first/last item

**Note**: Keyboard shortcuts may vary by browser and operating system.

---

## Appendix C: Report Types Reference

**Missed Pickup**
- Use when scheduled collection did not occur
- Include date and time of missed pickup
- Upload photo of uncollected waste

**Illegal Dumping**
- Report unauthorized waste disposal
- Include exact location
- Upload photos of dumped materials
- Note any identifying information

**Damaged Bin**
- Report broken or damaged waste containers
- Include bin identifier if available
- Upload photos showing damage
- Specify if bin is unusable

**Overflowing Bin**
- Report bins that are too full
- Include location and bin identifier
- Upload photos
- Note if this is a recurring issue

**Blocked Access**
- Report obstacles preventing collection
- Include location and nature of blockage
- Upload photos
- Suggest alternative access if known

**Other Issue**
- Use for issues not covered by other types
- Provide detailed description
- Include all relevant information
- Upload supporting photos


---

## Appendix D: Collection Status Definitions

**Completed**
- All collection points serviced successfully
- No issues encountered
- Photos uploaded as proof
- Route finished within scheduled time

**Partially Completed**
- Some collection points serviced
- Some points missed due to obstacles or issues
- Reasons documented in notes
- Photos uploaded for completed sections

**Incomplete**
- Route could not be completed
- Major obstacle or equipment failure
- Detailed explanation required
- Supervisor notification needed

**Pending**
- Collection scheduled but not yet started
- Awaiting crew arrival
- Default status for future collections

**Cancelled**
- Collection cancelled by administrator
- Reason documented
- Alternative arrangements may be scheduled

---

## Appendix E: Best Practices

### For Administrators

1. **Regular Monitoring**: Check dashboard daily for alerts and issues
2. **Timely Responses**: Respond to resident reports within 24 hours
3. **Proactive Planning**: Schedule assignments at least one week in advance
4. **Data Review**: Analyze performance metrics weekly
5. **Communication**: Keep users informed of schedule changes
6. **Maintenance**: Regularly update truck status and availability
7. **Training**: Ensure all users understand their roles
8. **Backup**: Export critical data regularly

### For Collection Crews

1. **Punctuality**: Start routes on time
2. **Documentation**: Upload clear photos for every collection
3. **Communication**: Report issues immediately
4. **Safety**: Follow all safety protocols
5. **Accuracy**: Log collections promptly and accurately
6. **Equipment**: Perform pre-trip truck inspections
7. **Professionalism**: Maintain courteous interaction with residents
8. **Recycling**: Accurately record recycling materials

### For Residents

1. **Preparation**: Place bins out before scheduled collection time
2. **Accessibility**: Ensure clear access to collection points
3. **Reporting**: Submit reports promptly with detailed information
4. **Photos**: Include clear photos with reports
5. **Follow-up**: Check report status regularly
6. **Patience**: Allow reasonable time for issue resolution
7. **Accuracy**: Provide correct location information
8. **Recycling**: Follow community recycling guidelines

---

## Appendix F: System Limits and Specifications

**File Uploads:**
- Maximum photo size: 5MB
- Supported formats: JPG, PNG
- Maximum photos per collection: 10
- Maximum photos per report: 5

**Data Retention:**
- Collection logs: Indefinite
- Reports: Indefinite
- Recycling logs: Indefinite
- User sessions: 2 hours of inactivity
- Password reset links: 60 minutes

**Performance:**
- Dashboard cache: 5-30 minutes
- Maximum concurrent users: Optimized for 1000+
- Report response time: < 2 seconds (typical)
- Photo upload time: Varies by connection speed

**Scheduling:**
- Maximum assignments per day: Unlimited
- Schedule advance planning: Recommended 1-4 weeks
- Holiday management: Up to 2 years in advance
- Recurring schedules: Daily, weekly, bi-weekly, monthly

---

## Appendix G: Security and Privacy

### Data Security

- **Encryption**: All data transmitted over HTTPS
- **Authentication**: Secure password-based login
- **Authorization**: Role-based access control
- **Session Management**: Automatic timeout after inactivity
- **Password Policy**: Minimum 8 characters required

### Privacy

- **Personal Data**: Collected only as necessary for service
- **Data Access**: Limited to authorized users based on role
- **Data Sharing**: Not shared with third parties
- **Data Retention**: Retained as per organizational policy
- **User Rights**: Contact administrator for data access requests

### User Responsibilities

- Keep login credentials confidential
- Log out when using shared computers
- Report suspicious activity immediately
- Use strong, unique passwords
- Do not share accounts with others


---

## Appendix H: Quick Reference Cards

### Administrator Quick Reference

**Daily Tasks:**
- [ ] Check dashboard for alerts
- [ ] Review new resident reports
- [ ] Verify today's assignments are covered
- [ ] Monitor collection progress

**Weekly Tasks:**
- [ ] Create next week's assignments
- [ ] Review crew performance metrics
- [ ] Respond to pending reports
- [ ] Check truck maintenance schedules
- [ ] Analyze collection completion rates

**Monthly Tasks:**
- [ ] Review recycling targets
- [ ] Generate performance reports
- [ ] Update schedules for next month
- [ ] Conduct user account audit
- [ ] Export data for records

### Collection Crew Quick Reference

**Before Starting:**
- [ ] Log in to SWEEP
- [ ] Check today's assignment
- [ ] Review route details
- [ ] Inspect truck
- [ ] Prepare camera/phone for photos

**During Collection:**
- [ ] Follow route as assigned
- [ ] Take photos at key points
- [ ] Note any issues encountered
- [ ] Record recycling materials

**After Completion:**
- [ ] Log collection in SWEEP
- [ ] Upload all photos
- [ ] Mark status (completed/incomplete)
- [ ] Add notes about issues
- [ ] Submit recycling log if applicable

### Resident Quick Reference

**Viewing Schedule:**
1. Log in to SWEEP
2. Go to Schedules
3. View your area's collection days
4. Note next collection date

**Submitting Report:**
1. Go to Reports → Submit New Report
2. Select report type
3. Enter location and description
4. Upload photos
5. Submit and save reference number

**Checking Report Status:**
1. Go to Reports
2. Find your report
3. Check current status
4. Read any responses

---

## Appendix I: System Color Coding

### Status Colors

**Green**: Completed, Active, Available, On-time
**Blue**: In Progress, Assigned, Scheduled
**Yellow**: Warning, Under Maintenance, Attention Needed
**Red**: Incomplete, Out of Service, Overdue, Critical
**Gray**: Inactive, Cancelled, Archived

### Dashboard Indicators

**Collection Status:**
- Green checkmark: Completed successfully
- Blue clock: In progress
- Yellow warning: Issues reported
- Red X: Incomplete or failed

**Report Status:**
- Gray: Pending
- Blue: In Progress
- Green: Resolved
- Dark gray: Closed

**Truck Status:**
- Green: Operational
- Yellow: Under Maintenance
- Red: Out of Service

---

## Appendix J: Mobile App Usage Tips

### Optimizing Mobile Experience

**Photo Uploads:**
- Use device camera directly for best quality
- Ensure good lighting
- Hold device steady
- Compress large photos before upload

**Data Usage:**
- Photos consume significant data
- Use Wi-Fi when available
- Reduce photo quality if on limited data plan
- Sync data when connected to Wi-Fi

**Battery Conservation:**
- Close app when not in use
- Reduce screen brightness
- Disable location services when not needed
- Keep device charged during long routes

**Offline Considerations:**
- SWEEP requires internet connection
- Download route details before starting
- Save data frequently
- Note areas with poor signal

### Mobile Browser Tips

**For Best Performance:**
- Use Chrome or Safari on mobile
- Clear browser cache regularly
- Update browser to latest version
- Enable JavaScript
- Allow location access for map features

---

## Appendix K: Accessibility Features

SWEEP is designed to be accessible to all users:

**Keyboard Navigation:**
- Full keyboard support for all functions
- Tab through interactive elements
- Enter to activate buttons
- Escape to close dialogs

**Screen Reader Support:**
- ARIA labels on all interactive elements
- Semantic HTML structure
- Alt text for images
- Descriptive link text

**Visual Accessibility:**
- High contrast mode available
- Resizable text
- Clear visual hierarchy
- Color is not the only indicator

**Assistive Technology:**
- Compatible with major screen readers
- Keyboard-only navigation supported
- Focus indicators visible
- Skip navigation links available

**Need Assistance?**
Contact your administrator for accessibility accommodations or alternative formats of this manual.


---

## Appendix L: Version History

### Version 1.0 (December 2024)
- Initial release of SWEEP User Manual
- Complete documentation for all three user roles
- Comprehensive feature coverage
- Troubleshooting and FAQ sections
- Quick reference guides and appendices

### Future Updates
This manual will be updated as new features are added to SWEEP. Check with your administrator for the latest version.

---

## Appendix M: Document Information

**Document Title**: SWEEP User Manual  
**Version**: 1.0  
**Last Updated**: December 2024  
**Prepared For**: SWEEP System Users  
**Document Type**: User Guide  
**Format**: Markdown / PDF  

**Copyright Notice**:
This document is proprietary and confidential. Unauthorized distribution or reproduction is prohibited.

**Feedback**:
To suggest improvements to this manual, contact your system administrator.

---

## Index

**A**
- Accessibility Features, Appendix K
- Administrator Guide, Section 4
- Analytics and Reporting, 4.10
- Assignments, 4.7

**B**
- Best Practices, Appendix E
- Browser Compatibility, 8.8

**C**
- Collection Crew Guide, Section 5
- Collection Logs, 4.8, 5.3
- Color Coding, Appendix I

**D**
- Dashboard Overview, 2.3, 4.1, 5.1, 6.1
- Data Export, 4.14

**E**
- Emergency Contacts, 10.3

**F**
- FAQ, Section 9
- Filters and Search, 7.5

**G**
- Getting Started, Section 2
- Glossary, Appendix A

**H**
- Holidays, 4.5
- Help and Support, Section 10

**K**
- Keyboard Shortcuts, Appendix B

**L**
- Logging In, 2.2
- Logging Out, 7.3

**M**
- Mobile Usage, Appendix J

**P**
- Password Recovery, 7.4
- Password Update, 7.2
- Performance Metrics, 5.7
- Photo Upload, 5.4, 8.3
- Profile Management, 7.1

**Q**
- Quick Reference, Appendix H

**R**
- Recycling Logs, 5.6
- Recycling Targets, 4.11
- Report Management, 4.9
- Report Submission, 6.3
- Report Tracking, 6.4
- Resident Guide, Section 6
- Routes, 4.3

**S**
- Schedules, 4.4, 6.2
- Scheduled Reports, 4.12
- Security and Privacy, Appendix G
- System Requirements, 1.3

**T**
- Troubleshooting, Section 8
- Trucks, 4.6
- Truck Availability, 4.13

**U**
- User Management, 4.2
- User Roles, Section 3

---

**END OF USER MANUAL**

---

*For technical support, system administration, or additional information, please contact your SWEEP system administrator.*

*This manual is designed to be comprehensive yet user-friendly. If you have suggestions for improvement or find any errors, please report them to your administrator.*

**Thank you for using SWEEP - Making waste management efficient, transparent, and sustainable.**
