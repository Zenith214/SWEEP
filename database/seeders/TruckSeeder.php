<?php

namespace Database\Seeders;

use App\Models\Truck;
use Illuminate\Database\Seeder;

class TruckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trucks = [
            [
                'truck_number' => 'T-001',
                'license_plate' => 'ABC-1234',
                'capacity' => 5.50,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Primary collection truck for Zone A',
            ],
            [
                'truck_number' => 'T-002',
                'license_plate' => 'ABC-1235',
                'capacity' => 6.00,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Primary collection truck for Zone B',
            ],
            [
                'truck_number' => 'T-003',
                'license_plate' => 'ABC-1236',
                'capacity' => 5.75,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Primary collection truck for Zone C',
            ],
            [
                'truck_number' => 'T-004',
                'license_plate' => 'ABC-1237',
                'capacity' => 7.00,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Large capacity truck for commercial routes',
            ],
            [
                'truck_number' => 'T-005',
                'license_plate' => 'ABC-1238',
                'capacity' => 5.25,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Backup truck for residential routes',
            ],
            [
                'truck_number' => 'T-006',
                'license_plate' => 'ABC-1239',
                'capacity' => 6.50,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Recently serviced and ready for operation',
            ],
            [
                'truck_number' => 'T-007',
                'license_plate' => 'ABC-1240',
                'capacity' => 5.00,
                'operational_status' => Truck::STATUS_MAINTENANCE,
                'notes' => 'Scheduled maintenance - hydraulic system repair',
            ],
            [
                'truck_number' => 'T-008',
                'license_plate' => 'ABC-1241',
                'capacity' => 6.25,
                'operational_status' => Truck::STATUS_MAINTENANCE,
                'notes' => 'Routine maintenance - oil change and inspection',
            ],
            [
                'truck_number' => 'T-009',
                'license_plate' => 'ABC-1242',
                'capacity' => 5.50,
                'operational_status' => Truck::STATUS_OUT_OF_SERVICE,
                'notes' => 'Major engine repair required - awaiting parts',
            ],
            [
                'truck_number' => 'T-010',
                'license_plate' => 'ABC-1243',
                'capacity' => 7.50,
                'operational_status' => Truck::STATUS_OPERATIONAL,
                'notes' => 'Newest truck in fleet - high capacity',
            ],
        ];

        foreach ($trucks as $truckData) {
            Truck::create($truckData);
        }
    }
}
