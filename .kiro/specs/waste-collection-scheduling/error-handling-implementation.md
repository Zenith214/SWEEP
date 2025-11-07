# Error Handling and User Feedback Implementation

## Overview

This document details the comprehensive error handling and user feedback system implemented for the Waste Collection Scheduling feature. All error scenarios are handled with user-friendly messages and appropriate visual feedback.

## Flash Messages

### Implementation Location
- **Layout File**: `resources/views/layouts/app.blade.php`

### Supported Message Types
1. **Success** (green) - Successful operations
2. **Error** (red) - Error conditions
3. **Warning** (yellow) - Warning messages
4. **Info** (blue) - Informational messages

### Usage in Controllers
```php
// Success message
return redirect()->route('admin.routes.index')
    ->with('success', 'Route created successfully.');

// Error message
return back()->with('error', 'Cannot delete route with active schedules.');

// Warning message
return back()->with('warning', 'This action requires confirmation.');

// Info message
return redirect()->route('admin.schedules.index')
    ->with('info', 'Schedule has been deactivated.');
```

### Visual Display
- Auto-dismissible alerts with close button
- Icons for each message type
- Positioned at the top of the main content area
- Consistent styling with SWEEP design system

## Validation Error Display

### Form-Level Validation
All forms include validation error display using Laravel's `@error` directive:

```blade
<input 
    type="text" 
    class="form-control @error('name') is-invalid @enderror" 
    name="name" 
    value="{{ old('name') }}"
>
@error('name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

### Implemented in Forms
1. **Route Forms**
   - `resources/views/admin/routes/create.blade.php`
   - `resources/views/admin/routes/edit.blade.php`

2. **Schedule Forms**
   - `resources/views/admin/schedules/create.blade.php`
   - `resources/views/admin/schedules/edit.blade.php`
   - `resources/views/admin/schedules/duplicate.blade.php`

3. **Holiday Forms**
   - `resources/views/admin/holidays/create.blade.php`
   - `resources/views/admin/holidays/edit.blade.php`

### Client-Side Validation
All forms include JavaScript validation to provide immediate feedback:
- Required field validation
- Format validation (dates, times)
- Length validation (character limits)
- Relationship validation (end date after start date)

## Confirmation Modals

### Delete Confirmation Modals
Implemented for all deletion operations to prevent accidental data loss.

#### Route Deletion
**Location**: `resources/views/admin/routes/index.blade.php`

**Features**:
- Shows route name being deleted
- Displays warning if route has active schedules
- Prevents deletion if active schedules exist
- Clear Cancel/Delete buttons

**Error Handling**:
```php
// In RouteController::destroy()
if ($route->hasActiveSchedules()) {
    return back()->with('error', 'Cannot delete route with active schedules. Please deactivate or delete schedules first.');
}
```

#### Schedule Deletion
**Location**: `resources/views/admin/schedules/index.blade.php`

**Features**:
- Shows schedule details (route name)
- Warning about permanent deletion
- Clear Cancel/Delete buttons

#### Holiday Deletion
**Location**: `resources/views/admin/holidays/index.blade.php`

**Features**:
- Shows holiday name being deleted
- Explains impact on schedules
- Clear Cancel/Delete buttons

### Duplication Confirmation
**Location**: `resources/views/admin/schedules/duplicate.blade.php`

**Features**:
- JavaScript confirmation before submission
- Displays source schedule details
- Shows what will be copied
- Conflict warning if applicable

## Specific Error Scenarios

### 1. Route Has Active Schedules Error

**Scenario**: Administrator attempts to delete a route with active schedules

**Implementation**: `app/Http/Controllers/RouteController.php`

```php
public function destroy(Route $route): RedirectResponse
{
    if ($route->hasActiveSchedules()) {
        return back()->with('error', 'Cannot delete route with active schedules. Please deactivate or delete schedules first.');
    }
    
    $route->delete();
    return redirect()->route('admin.routes.index')
        ->with('success', 'Route deleted successfully.');
}
```

**User Experience**:
- Red error alert displayed at top of page
- User remains on the same page
- Clear explanation of why deletion failed
- Actionable guidance (deactivate or delete schedules first)

### 2. Schedule Conflict Errors

**Scenario**: Administrator creates/updates a schedule that conflicts with existing schedules

**Implementation**: `app/Http/Controllers/ScheduleController.php`

```php
public function store(StoreScheduleRequest $request): RedirectResponse
{
    try {
        $schedule = $this->scheduleService->createSchedule($request->validated());
        
        if ($this->scheduleService->checkConflicts($schedule)) {
            $schedule->delete();
            return back()
                ->withInput()
                ->with('error', 'This schedule conflicts with an existing schedule on the same route.');
        }
        
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully.');
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}
```

**User Experience**:
- Red error alert displayed
- Form data preserved (withInput)
- User can modify and resubmit
- Clear explanation of conflict

**Service Layer**: `app/Services/ScheduleService.php`

```php
public function updateSchedule(Schedule $schedule, array $data): Schedule
{
    // ... update logic ...
    
    if ($this->checkConflicts($schedule, $schedule)) {
        throw new \Exception('This schedule conflicts with an existing schedule on the same route');
    }
    
    return $schedule;
}
```

### 3. Zone Not Found Errors

**Scenario**: Resident searches for a zone that doesn't exist

**Implementation**: `app/Http/Controllers/ResidentScheduleController.php`

```php
public function search(Request $request): View
{
    $request->validate([
        'zone' => 'required|string|max:100',
    ]);
    
    $zone = $request->input('zone');
    $routes = Route::where('zone', $zone)
        ->where('is_active', true)
        ->with(['activeSchedules.scheduleDays'])
        ->get();
    
    if ($routes->isEmpty()) {
        return view('resident.schedules.index')
            ->with('error', 'No collection schedules found for this zone. Please check your zone identifier and try again.');
    }
    
    // ... continue with results ...
}
```

**User Experience**:
- Red error alert displayed on search page
- Helpful message suggesting to check zone identifier
- User can immediately try another search
- Search form remains visible

### 4. Duplication Conflict Errors

**Scenario**: Administrator attempts to duplicate a schedule that would create conflicts

**Implementation**: `app/Services/ScheduleService.php`

```php
public function duplicateSchedule(Schedule $schedule, Route $targetRoute): Schedule
{
    return DB::transaction(function () use ($schedule, $targetRoute) {
        // ... duplication logic ...
        
        if ($this->checkConflicts($newSchedule)) {
            throw new \Exception('Cannot duplicate schedule - would create conflict on target route');
        }
        
        return $newSchedule;
    });
}
```

**Controller Handling**: `app/Http/Controllers/ScheduleController.php`

```php
public function storeDuplicate(DuplicateScheduleRequest $request, Schedule $schedule): RedirectResponse
{
    try {
        $targetRoute = Route::findOrFail($request->input('target_route_id'));
        $newSchedule = $this->scheduleService->duplicateSchedule($schedule, $targetRoute);
        
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule duplicated successfully to route: ' . $targetRoute->name);
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}
```

**User Experience**:
- Red error alert with clear message
- Form data preserved
- User can select different target route
- Explanation of why duplication failed

## Validation Rules

### Route Validation

**Create**: `app/Http/Requests/StoreRouteRequest.php`
```php
'name' => 'required|string|max:255|unique:routes,name',
'zone' => 'required|string|max:100',
'description' => 'nullable|string',
'notes' => 'nullable|string',
'is_active' => 'boolean'
```

**Update**: `app/Http/Requests/UpdateRouteRequest.php`
```php
'name' => 'required|string|max:255|unique:routes,name,' . $this->route->id,
// ... other fields same as create
```

**Error Messages**:
- "The name field is required."
- "A route with this name already exists."
- "The name must not exceed 255 characters."
- "The zone field is required."

### Schedule Validation

**Create/Update**: `app/Http/Requests/StoreScheduleRequest.php`
```php
'route_id' => 'required|exists:routes,id',
'collection_time' => 'required|date_format:H:i',
'start_date' => 'required|date|after_or_equal:today',
'end_date' => 'nullable|date|after:start_date',
'days_of_week' => 'required|array|min:1',
'days_of_week.*' => 'integer|between:0,6',
'is_active' => 'boolean'
```

**Error Messages**:
- "Please select a route."
- "The collection time must be a valid time."
- "The start date cannot be in the past."
- "The end date must be after the start date."
- "Please select at least one collection day."

### Holiday Validation

**Create/Update**: `app/Http/Requests/StoreHolidayRequest.php`
```php
'name' => 'required|string|max:255',
'date' => 'required|date|unique:holidays,date',
'is_collection_skipped' => 'boolean',
'reschedule_date' => 'nullable|date|different:date'
```

**Error Messages**:
- "The name field is required."
- "A holiday already exists for this date."
- "The reschedule date must be different from the holiday date."

## JavaScript Error Handling

### Error Handling Utilities
**Location**: `public/js/error-handling.js`

**Features**:
1. **Toast Notifications**: Dynamic toast messages for AJAX operations
2. **AJAX Error Handler**: Consistent error handling for fetch/axios requests
3. **Form Validation**: Client-side validation before submission
4. **Loading States**: Disable buttons and show loading indicators during submission

**Usage Example**:
```javascript
// Show success toast
showToast('Schedule updated successfully!', 'success');

// Handle AJAX error
fetch('/api/schedules')
    .then(response => response.json())
    .catch(error => handleAjaxError(error));

// Validate form before submission
if (validateForm(document.getElementById('myForm'))) {
    // Submit form
}
```

## User Feedback Best Practices

### 1. Immediate Feedback
- Client-side validation provides instant feedback
- Field-level error messages appear as user types
- Character counters for length-limited fields

### 2. Clear Error Messages
- Avoid technical jargon
- Explain what went wrong
- Provide actionable guidance
- Use consistent language

### 3. Visual Indicators
- Red borders for invalid fields
- Icons for different message types
- Color-coded alerts (success=green, error=red, etc.)
- Warning badges for routes without schedules

### 4. Preserve User Input
- Use `withInput()` to preserve form data on errors
- Use `old()` helper to repopulate fields
- Prevents frustration from losing work

### 5. Contextual Help
- Help text under form fields
- Info cards with guidance
- Tooltips for additional information
- Examples in placeholder text

## Testing Error Scenarios

### Manual Testing Checklist

#### Route Management
- [ ] Try to create route with duplicate name
- [ ] Try to delete route with active schedules
- [ ] Submit empty route form
- [ ] Enter name exceeding 255 characters
- [ ] Enter zone exceeding 100 characters

#### Schedule Management
- [ ] Try to create schedule without selecting days
- [ ] Try to create conflicting schedule
- [ ] Set end date before start date
- [ ] Try to duplicate to route with conflict
- [ ] Submit schedule form with past start date

#### Holiday Management
- [ ] Try to create holiday with duplicate date
- [ ] Set reschedule date same as holiday date
- [ ] Submit empty holiday form

#### Resident Views
- [ ] Search for non-existent zone
- [ ] Search with empty zone field
- [ ] View calendar for invalid zone

## Summary

The error handling implementation provides:

✅ **Flash Messages**: Success, error, warning, and info messages in all controllers
✅ **Validation Errors**: Field-level validation errors in all forms
✅ **Confirmation Modals**: Delete confirmations for routes, schedules, and holidays
✅ **Route Deletion Protection**: Prevents deletion of routes with active schedules
✅ **Schedule Conflict Detection**: Prevents conflicting schedules with clear messages
✅ **Zone Not Found Handling**: User-friendly message when zone doesn't exist
✅ **Duplication Conflict Prevention**: Prevents duplicate schedules that would conflict
✅ **Client-Side Validation**: Immediate feedback before form submission
✅ **JavaScript Utilities**: Reusable error handling functions
✅ **Consistent UX**: Uniform error handling across all features

All requirements from task 16 have been successfully implemented.
