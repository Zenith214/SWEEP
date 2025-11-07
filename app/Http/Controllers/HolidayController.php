<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is applied in routes/web.php
    }

    /**
     * Display a listing of holidays.
     */
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'desc')->paginate(15);

        return view('admin.holidays.index', compact('holidays'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        return view('admin.holidays.create');
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(StoreHolidayRequest $request)
    {
        $data = $request->validated();
        
        // Set default value for is_collection_skipped if not provided
        $data['is_collection_skipped'] = $data['is_collection_skipped'] ?? true;

        Holiday::create($data);

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    /**
     * Display the specified holiday.
     */
    public function show(Holiday $holiday)
    {
        return view('admin.holidays.show', compact('holiday'));
    }

    /**
     * Show the form for editing the specified holiday.
     */
    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    {
        $data = $request->validated();
        
        // Set default value for is_collection_skipped if not provided
        $data['is_collection_skipped'] = $data['is_collection_skipped'] ?? true;

        $holiday->update($data);

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()
            ->route('admin.holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }
}
