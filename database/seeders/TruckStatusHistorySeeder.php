<?php

namespace Database\Seeders;

use App\Models\Truck;
use App\Models\TruckStatusHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TruckStatusHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get an administrator user to be the one who changed statuses
        $admin = User::role('administrator')->first();

        if (!$admin) {
            $this->command->warn('Skipping TruckStatusHistorySeeder: No administrator user found');
            return;
        }

        $trucks = Truck::all();

        if ($trucks->isEmpty()) {
            $this->command->warn('Skipping TruckStatusHistorySeeder: No trucks found');
            return;
        }

        // Create status history for trucks that are currently in maintenance or out of service
        $this->createMaintenanceTruckHistory($trucks, $admin);
        $this->createOutOfServiceTruckHistory($trucks, $admin);
        
        // Create some historical status changes for operational trucks
        $this->createOperationalTruckHistory($trucks, $admin);
    }

    /**
     * Create status history for trucks currently in maintenance.
     */
    private function createMaintenanceTruckHistory($trucks, $admin): void
    {
        $maintenanceTrucks = $trucks->where('operational_status', Truck::STATUS_MAINTENANCE);

        foreach ($maintenanceTrucks as $truck) {
            // Initial status when truck was added (30-60 days ago)
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => null,
                'new_status' => Truck::STATUS_OPERATIONAL,
                'changed_by' => $admin->id,
                'notes' => 'Truck added to fleet',
                'created_at' => Carbon::now()->subDays(rand(30, 60)),
            ]);

            // Changed to maintenance (1-5 days ago)
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => Truck::STATUS_OPERATIONAL,
                'new_status' => Truck::STATUS_MAINTENANCE,
                'changed_by' => $admin->id,
                'notes' => $this->getMaintenanceNote(),
                'created_at' => Carbon::now()->subDays(rand(1, 5)),
            ]);
        }
    }

    /**
     * Create status history for trucks currently out of service.
     */
    private function createOutOfServiceTruckHistory($trucks, $admin): void
    {
        $outOfServiceTrucks = $trucks->where('operational_status', Truck::STATUS_OUT_OF_SERVICE);

        foreach ($outOfServiceTrucks as $truck) {
            // Initial status when truck was added (60-90 days ago)
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => null,
                'new_status' => Truck::STATUS_OPERATIONAL,
                'changed_by' => $admin->id,
                'notes' => 'Truck added to fleet',
                'created_at' => Carbon::now()->subDays(rand(60, 90)),
            ]);

            // Changed to maintenance (20-30 days ago)
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => Truck::STATUS_OPERATIONAL,
                'new_status' => Truck::STATUS_MAINTENANCE,
                'changed_by' => $admin->id,
                'notes' => 'Routine maintenance check revealed issues',
                'created_at' => Carbon::now()->subDays(rand(20, 30)),
            ]);

            // Changed to out of service (10-15 days ago)
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => Truck::STATUS_MAINTENANCE,
                'new_status' => Truck::STATUS_OUT_OF_SERVICE,
                'changed_by' => $admin->id,
                'notes' => $this->getOutOfServiceNote(),
                'created_at' => Carbon::now()->subDays(rand(10, 15)),
            ]);
        }
    }

    /**
     * Create some historical status changes for operational trucks.
     */
    private function createOperationalTruckHistory($trucks, $admin): void
    {
        $operationalTrucks = $trucks->where('operational_status', Truck::STATUS_OPERATIONAL);

        // Select a few operational trucks to have history
        $trucksWithHistory = $operationalTrucks->random(min(4, $operationalTrucks->count()));

        foreach ($trucksWithHistory as $truck) {
            // Initial status when truck was added (90-180 days ago)
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => null,
                'new_status' => Truck::STATUS_OPERATIONAL,
                'changed_by' => $admin->id,
                'notes' => 'Truck added to fleet',
                'created_at' => Carbon::now()->subDays(rand(90, 180)),
            ]);

            // Some trucks had maintenance in the past
            if (rand(0, 1)) {
                // Changed to maintenance (30-60 days ago)
                TruckStatusHistory::create([
                    'truck_id' => $truck->id,
                    'old_status' => Truck::STATUS_OPERATIONAL,
                    'new_status' => Truck::STATUS_MAINTENANCE,
                    'changed_by' => $admin->id,
                    'notes' => 'Scheduled maintenance',
                    'created_at' => Carbon::now()->subDays(rand(30, 60)),
                ]);

                // Changed back to operational (25-55 days ago)
                TruckStatusHistory::create([
                    'truck_id' => $truck->id,
                    'old_status' => Truck::STATUS_MAINTENANCE,
                    'new_status' => Truck::STATUS_OPERATIONAL,
                    'changed_by' => $admin->id,
                    'notes' => 'Maintenance completed - all systems operational',
                    'created_at' => Carbon::now()->subDays(rand(25, 55)),
                ]);
            }

            // Some trucks had another maintenance cycle
            if (rand(0, 2) === 0) {
                // Changed to maintenance (10-20 days ago)
                TruckStatusHistory::create([
                    'truck_id' => $truck->id,
                    'old_status' => Truck::STATUS_OPERATIONAL,
                    'new_status' => Truck::STATUS_MAINTENANCE,
                    'changed_by' => $admin->id,
                    'notes' => 'Routine inspection and service',
                    'created_at' => Carbon::now()->subDays(rand(10, 20)),
                ]);

                // Changed back to operational (5-15 days ago)
                TruckStatusHistory::create([
                    'truck_id' => $truck->id,
                    'old_status' => Truck::STATUS_MAINTENANCE,
                    'new_status' => Truck::STATUS_OPERATIONAL,
                    'changed_by' => $admin->id,
                    'notes' => 'Service completed - ready for operation',
                    'created_at' => Carbon::now()->subDays(rand(5, 15)),
                ]);
            }
        }
    }

    /**
     * Get a random maintenance note.
     */
    private function getMaintenanceNote(): string
    {
        $notes = [
            'Scheduled maintenance - hydraulic system check',
            'Routine oil change and filter replacement',
            'Brake system inspection and repair',
            'Tire replacement required',
            'Engine diagnostic check',
            'Transmission service',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Get a random out of service note.
     */
    private function getOutOfServiceNote(): string
    {
        $notes = [
            'Major engine repair required - awaiting parts',
            'Transmission failure - extensive repairs needed',
            'Structural damage - safety inspection failed',
            'Hydraulic system failure - major overhaul required',
            'Electrical system issues - awaiting specialist',
        ];

        return $notes[array_rand($notes)];
    }
}
