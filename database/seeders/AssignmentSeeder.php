<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Truck;
use App\Models\User;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get operational trucks
        $operationalTrucks = Truck::where('operational_status', Truck::STATUS_OPERATIONAL)->get();
        
        // Get collection crew members
        $crewMembers = User::role('collection_crew')->get();
        
        // If no crew members exist, create some test crew members
        if ($crewMembers->isEmpty()) {
            $this->createTestCrewMembers();
            $crewMembers = User::role('collection_crew')->get();
        }
        
        // Get active routes
        $routes = Route::where('is_active', true)->get();

        // Ensure we have the necessary data
        if ($operationalTrucks->isEmpty() || $crewMembers->isEmpty() || $routes->isEmpty()) {
            $this->command->warn('Skipping AssignmentSeeder: Missing required data (trucks, crew members, or routes)');
            return;
        }

        // Create assignments for the past 7 days
        $this->createPastAssignments($operationalTrucks, $crewMembers, $routes);

        // Create assignments for today
        $this->createTodayAssignments($operationalTrucks, $crewMembers, $routes);

        // Create assignments for the next 14 days
        $this->createFutureAssignments($operationalTrucks, $crewMembers, $routes);

        // Create some cancelled assignments
        $this->createCancelledAssignments($operationalTrucks, $crewMembers, $routes);
    }

    /**
     * Create assignments for the past 7 days.
     */
    private function createPastAssignments($trucks, $crewMembers, $routes): void
    {
        for ($i = 7; $i >= 1; $i--) {
            $date = Carbon::today()->subDays($i);
            $this->createAssignmentsForDate($date, $trucks, $crewMembers, $routes, 4);
        }
    }

    /**
     * Create assignments for today.
     */
    private function createTodayAssignments($trucks, $crewMembers, $routes): void
    {
        $date = Carbon::today();
        $this->createAssignmentsForDate($date, $trucks, $crewMembers, $routes, 5);
    }

    /**
     * Create assignments for the next 14 days.
     */
    private function createFutureAssignments($trucks, $crewMembers, $routes): void
    {
        for ($i = 1; $i <= 14; $i++) {
            $date = Carbon::today()->addDays($i);
            // Leave some routes unassigned for testing (only assign 60-70% of available trucks)
            $assignmentCount = rand(3, 5);
            $this->createAssignmentsForDate($date, $trucks, $crewMembers, $routes, $assignmentCount);
        }
    }

    /**
     * Create assignments for a specific date.
     */
    private function createAssignmentsForDate(Carbon $date, $trucks, $crewMembers, $routes, int $count): void
    {
        $usedTrucks = [];
        $usedCrew = [];
        $usedRoutes = [];

        $availableTrucks = $trucks->shuffle();
        $availableCrew = $crewMembers->shuffle();
        $availableRoutes = $routes->shuffle();

        $created = 0;
        $truckIndex = 0;
        $crewIndex = 0;
        $routeIndex = 0;

        while ($created < $count && $truckIndex < $availableTrucks->count() && 
               $crewIndex < $availableCrew->count() && $routeIndex < $availableRoutes->count()) {
            
            $truck = $availableTrucks[$truckIndex];
            $crew = $availableCrew[$crewIndex];
            $route = $availableRoutes[$routeIndex];

            // Skip if already used
            if (in_array($truck->id, $usedTrucks) || 
                in_array($crew->id, $usedCrew) || 
                in_array($route->id, $usedRoutes)) {
                $truckIndex++;
                $crewIndex++;
                $routeIndex++;
                continue;
            }

            Assignment::create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $date,
                'status' => Assignment::STATUS_ACTIVE,
                'notes' => $this->getRandomNote(),
            ]);

            $usedTrucks[] = $truck->id;
            $usedCrew[] = $crew->id;
            $usedRoutes[] = $route->id;

            $created++;
            $truckIndex++;
            $crewIndex++;
            $routeIndex++;
        }
    }

    /**
     * Create some cancelled assignments for testing.
     */
    private function createCancelledAssignments($trucks, $crewMembers, $routes): void
    {
        // Create 3-5 cancelled assignments from the past
        $cancelledCount = rand(3, 5);

        for ($i = 0; $i < $cancelledCount; $i++) {
            $date = Carbon::today()->subDays(rand(3, 10));
            
            $truck = $trucks->random();
            $crew = $crewMembers->random();
            $route = $routes->random();

            Assignment::create([
                'truck_id' => $truck->id,
                'user_id' => $crew->id,
                'route_id' => $route->id,
                'assignment_date' => $date,
                'status' => Assignment::STATUS_CANCELLED,
                'notes' => 'Original assignment',
                'cancellation_reason' => $this->getRandomCancellationReason(),
            ]);
        }
    }

    /**
     * Get a random note for assignments.
     */
    private function getRandomNote(): ?string
    {
        $notes = [
            null,
            null,
            null, // More likely to have no notes
            'Regular scheduled collection',
            'Extra pickup requested',
            'Check for bulk items',
            'New route assignment',
            'Covering for another crew',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Get a random cancellation reason.
     */
    private function getRandomCancellationReason(): string
    {
        $reasons = [
            'Truck breakdown',
            'Crew member called in sick',
            'Weather conditions',
            'Route rescheduled',
            'Holiday adjustment',
            'Emergency maintenance required',
        ];

        return $reasons[array_rand($reasons)];
    }

    /**
     * Create test crew members if none exist.
     */
    private function createTestCrewMembers(): void
    {
        $crewNames = [
            'John Smith',
            'Maria Garcia',
            'David Johnson',
            'Sarah Williams',
            'Michael Brown',
            'Jennifer Davis',
            'Robert Miller',
            'Lisa Wilson',
        ];

        foreach ($crewNames as $name) {
            $email = strtolower(str_replace(' ', '.', $name)) . '@sweep.local';
            
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('collection_crew');
        }

        $this->command->info('Created test crew members for assignment seeding');
    }
}
