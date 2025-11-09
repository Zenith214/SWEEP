<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that skip to content link is present
     */
    public function test_skip_to_content_link_is_present(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Skip to main content', false);
        $response->assertSee('href="#main-content"', false);
    }

    /**
     * Test that main content has proper ARIA landmarks
     */
    public function test_main_content_has_aria_landmarks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('role="main"', false);
    }

    /**
     * Test that navigation has proper ARIA labels
     */
    public function test_navigation_has_aria_labels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('role="navigation"', false);
        $response->assertSee('aria-label="Main navigation"', false);
    }

    /**
     * Test that metric cards have proper ARIA attributes
     */
    public function test_metric_cards_have_aria_attributes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Check for ARIA labels on metric cards
        $response->assertSee('aria-label=', false);
    }

    /**
     * Test that charts have text alternatives
     */
    public function test_charts_have_text_alternatives(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Check for chart canvas with role="img"
        $response->assertSee('role="img"', false);
        // Check for visually-hidden data table
        $response->assertSee('visually-hidden', false);
    }

    /**
     * Test that forms have associated labels
     */
    public function test_forms_have_associated_labels(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Check that form controls have labels
        $response->assertSee('<label', false);
    }

    /**
     * Test that buttons have descriptive text or ARIA labels
     */
    public function test_buttons_have_descriptive_text(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Buttons should have text content or aria-label
        $response->assertDontSee('<button></button>', false);
    }

    /**
     * Test that images have alt text or ARIA labels
     */
    public function test_images_have_alt_text(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Icons should have aria-hidden="true" since they're decorative
        $response->assertSee('aria-hidden="true"', false);
    }

    /**
     * Test that tables have proper structure
     */
    public function test_tables_have_proper_structure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Tables should have role="table" and proper headers
        $response->assertSee('role="table"', false);
    }

    /**
     * Test that alert messages have proper ARIA attributes
     */
    public function test_alert_messages_have_aria_attributes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)
            ->withSession(['success' => 'Test success message'])
            ->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('role="alert"', false);
        $response->assertSee('aria-live=', false);
    }

    /**
     * Test that accessibility CSS is loaded
     */
    public function test_accessibility_css_is_loaded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('css/accessibility.css', false);
    }

    /**
     * Test that accessibility JavaScript is loaded
     */
    public function test_accessibility_javascript_is_loaded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('js/accessibility-helper.js', false);
    }

    /**
     * Test that clickable elements have tabindex
     */
    public function test_clickable_elements_have_tabindex(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertStatus(200);
        // Clickable cards should have tabindex="0"
        $response->assertSee('tabindex=', false);
    }

    /**
     * Test crew dashboard accessibility
     */
    public function test_crew_dashboard_has_accessibility_features(): void
    {
        $crew = User::factory()->create(['role' => 'collectioncrew']);
        
        $response = $this->actingAs($crew)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('role="main"', false);
    }

    /**
     * Test resident dashboard accessibility
     */
    public function test_resident_dashboard_has_accessibility_features(): void
    {
        $resident = User::factory()->create(['role' => 'resident']);
        
        $response = $this->actingAs($resident)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('role="main"', false);
    }
}
