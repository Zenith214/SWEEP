<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users with filtering and search.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filtering
        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        // Pagination
        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        try {
            $this->userService->createUser([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ], $validated['role']);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $user->load('roles');
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        try {
            $this->userService->updateUser($user, $validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Remove the specified user from storage (soft delete).
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->userService->deleteUser($user);

            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Update the role of the specified user.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        try {
            $this->userService->changeUserRole(
                $user,
                $validated['role'],
                Auth::user()
            );

            return redirect()->route('admin.users.index')
                ->with('success', 'User role updated successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
