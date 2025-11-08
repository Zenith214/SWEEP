<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionLogRequest;
use App\Http\Requests\UpdateCollectionLogRequest;
use App\Http\Requests\UploadPhotoRequest;
use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\CollectionPhoto;
use App\Services\CollectionLogService;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CollectionLogController extends Controller
{
    protected CollectionLogService $collectionLogService;
    protected PhotoService $photoService;

    public function __construct(CollectionLogService $collectionLogService, PhotoService $photoService)
    {
        $this->collectionLogService = $collectionLogService;
        $this->photoService = $photoService;
    }

    /**
     * Display today's assignment with logging option.
     * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Get today's assignment for the current user
        $assignment = Assignment::with(['truck', 'route'])
            ->where('user_id', $user->id)
            ->forDate($today)
            ->active()
            ->first();

        // Check if a collection log exists for this assignment
        $collectionLog = null;
        if ($assignment) {
            $collectionLog = CollectionLog::where('assignment_id', $assignment->id)->first();
        }

        return view('crew.collections.index', compact('assignment', 'collectionLog'));
    }

    /**
     * Show the collection logging form.
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.4, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4
     */
    public function create(Assignment $assignment)
    {
        $user = auth()->user();

        // Verify the assignment belongs to the current user
        if ($assignment->user_id !== $user->id) {
            return redirect()->route('crew.collections')
                ->with('error', 'You can only log collections for your own assignments.');
        }

        // Check if assignment is active
        if (!$assignment->isActive()) {
            return redirect()->route('crew.collections')
                ->with('error', 'Cannot log collection for a cancelled assignment.');
        }

        // Check if a log already exists
        $existingLog = CollectionLog::where('assignment_id', $assignment->id)->first();
        if ($existingLog) {
            return redirect()->route('crew.collections.show', $existingLog)
                ->with('info', 'A collection log already exists for this assignment.');
        }

        $assignment->load(['truck', 'route']);

        return view('crew.collections.create', compact('assignment'));
    }

    /**
     * Store a new collection log with photos.
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.4, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4
     */
    public function store(StoreCollectionLogRequest $request, Assignment $assignment)
    {
        $user = auth()->user();

        try {
            DB::beginTransaction();

            // Create the collection log
            $collectionLog = $this->collectionLogService->createLog(
                $assignment,
                $request->validated(),
                $user
            );

            // Upload photos if provided
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $this->photoService->uploadPhoto($photo, $collectionLog);
                }
            }

            DB::commit();

            return redirect()->route('crew.collections.show', $collectionLog)
                ->with('success', 'Collection log created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display collection log details.
     * Requirements: 6.5, 8.3, 8.4, 8.5
     */
    public function show(CollectionLog $collectionLog)
    {
        $user = auth()->user();

        // Load relationships
        $collectionLog->load([
            'assignment.truck',
            'assignment.route',
            'assignment.user',
            'creator',
            'photos'
        ]);

        // Check if user can view this log (crew can only view their own, admins can view all)
        if (!$user->hasRole('administrator') && $collectionLog->created_by !== $user->id) {
            return redirect()->route('crew.collections')
                ->with('error', 'You can only view your own collection logs.');
        }

        // Calculate remaining edit time
        $editTimeRemaining = $collectionLog->getEditTimeRemaining();
        $canEdit = $collectionLog->canBeEditedBy($user);

        return view('crew.collections.show', compact('collectionLog', 'editTimeRemaining', 'canEdit'));
    }

    /**
     * Show the edit form for a collection log.
     * Requirements: 12.1, 12.2, 12.3, 12.5
     */
    public function edit(CollectionLog $collectionLog)
    {
        // Middleware handles edit window and ownership checks
        
        // Load relationships
        $collectionLog->load([
            'assignment.truck',
            'assignment.route',
            'photos'
        ]);

        // Calculate remaining edit time
        $editTimeRemaining = $collectionLog->getEditTimeRemaining();

        return view('crew.collections.edit', compact('collectionLog', 'editTimeRemaining'));
    }

    /**
     * Update a collection log.
     * Requirements: 12.1, 12.2, 12.3, 12.5
     */
    public function update(UpdateCollectionLogRequest $request, CollectionLog $collectionLog)
    {
        // Middleware and form request handle edit window and ownership checks

        try {
            // Update the collection log
            $this->collectionLogService->updateLog($collectionLog, $request->validated());

            return redirect()->route('crew.collections.show', $collectionLog)
                ->with('success', 'Collection log updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Upload an additional photo to a collection log (AJAX).
     * Requirements: 3.1, 3.4, 12.4
     */
    public function uploadPhoto(UploadPhotoRequest $request, CollectionLog $collectionLog)
    {
        // Middleware and form request handle edit window, ownership, and photo count checks

        try {
            $photo = $this->photoService->uploadPhoto($request->file('photo'), $collectionLog);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully.',
                'photo' => [
                    'id' => $photo->id,
                    'url' => $this->photoService->getPhotoUrl($photo),
                    'thumbnail_url' => $this->photoService->getThumbnailUrl($photo),
                    'file_name' => $photo->file_name,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete a photo from a collection log.
     * Requirements: 3.1, 3.4, 12.4
     */
    public function deletePhoto(CollectionPhoto $photo)
    {
        $user = auth()->user();
        $collectionLog = $photo->collectionLog;

        // Check if the log can be edited by the current user
        if (!$collectionLog->canBeEditedBy($user)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This photo can no longer be deleted. The 2-hour edit window has expired.'
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'This photo can no longer be deleted. The 2-hour edit window has expired.');
        }

        try {
            $this->photoService->deletePhoto($photo);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Photo deleted successfully.'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Photo deleted successfully.');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display collection history for the current crew member.
     * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get collection logs for the user
        $logs = $this->collectionLogService->getCrewHistory($user, $startDate, $endDate);

        // Paginate the results
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $logs->slice($offset, $perPage),
            $logs->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('crew.collections.history', [
            'logs' => $paginatedLogs,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
