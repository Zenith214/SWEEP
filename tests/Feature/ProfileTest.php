<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated Name',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile')
            ->assertSessionHas('success', 'Profile updated successfully.');

        $user->refresh();

        $this->assertSame('Updated Name', $user->name);
    }

    public function test_profile_name_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => '',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_user_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('current-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/password', [
                'current_password' => 'current-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile')
            ->assertSessionHas('success', 'Password updated successfully.');

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_password_update_requires_current_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('current-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('current-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/password', [
                'current_password' => 'current-password',
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_must_be_confirmed(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('current-password'),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/password', [
                'current_password' => 'current-password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
