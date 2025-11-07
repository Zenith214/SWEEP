<?php

namespace App\Services;

use App\Models\RoleChangeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Create a new user with the specified role.
     *
     * @param array $data User data (name, email, password)
     * @param string $role Role to assign to the user
     * @return User
     * @throws ValidationException
     */
    public function createUser(array $data, string $role): User
    {
        // Validate that the email is unique
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email address is already registered.'],
            ]);
        }

        // Create the user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign the role
        $user->assignRole($role);

        return $user;
    }

    /**
     * Update an existing user's information.
     *
     * @param User $user User to update
     * @param array $data Data to update (name, password optional)
     * @return User
     */
    public function updateUser(User $user, array $data): User
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Change a user's role and log the change.
     *
     * @param User $user User whose role is being changed
     * @param string $newRole New role to assign
     * @param User $admin Administrator making the change
     * @return void
     * @throws ValidationException
     */
    public function changeUserRole(User $user, string $newRole, User $admin): void
    {
        // Prevent users from changing their own role
        if ($user->id === $admin->id) {
            throw ValidationException::withMessages([
                'role' => ['You cannot change your own role.'],
            ]);
        }

        // Get the current role
        $oldRole = $user->roles->first()?->name ?? 'none';

        // Prevent removal of the last administrator
        if ($oldRole === 'administrator' && $newRole !== 'administrator') {
            $this->ensureAdminExists($user);
        }

        DB::transaction(function () use ($user, $newRole, $oldRole, $admin) {
            // Remove all existing roles and assign the new one
            $user->syncRoles([$newRole]);

            // Log the role change
            RoleChangeLog::create([
                'user_id' => $user->id,
                'changed_by' => $admin->id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
            ]);
        });
    }

    /**
     * Soft delete a user.
     *
     * @param User $user User to delete
     * @return bool
     * @throws ValidationException
     */
    public function deleteUser(User $user): bool
    {
        // Prevent deletion of the last administrator
        if ($user->hasRole('administrator')) {
            $this->ensureAdminExists($user);
        }

        return $user->delete();
    }

    /**
     * Ensure at least one administrator exists besides the specified user.
     *
     * @param User|null $excludeUser User to exclude from the count
     * @return void
     * @throws ValidationException
     */
    public function ensureAdminExists(?User $excludeUser = null): void
    {
        $query = User::role('administrator');

        if ($excludeUser) {
            $query->where('id', '!=', $excludeUser->id);
        }

        $adminCount = $query->count();

        if ($adminCount === 0) {
            throw ValidationException::withMessages([
                'role' => ['Cannot perform this action. At least one administrator must remain in the system.'],
            ]);
        }
    }
}
