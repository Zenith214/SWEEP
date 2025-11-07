<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = [
            [
                'name' => 'North District Route A',
                'zone' => 'ND-A',
                'description' => 'Covers residential areas in the northern district, including Maple Street and Oak Avenue.',
                'notes' => 'Heavy traffic area - start early',
                'is_active' => true,
            ],
            [
                'name' => 'North District Route B',
                'zone' => 'ND-B',
                'description' => 'Northern commercial district and apartment complexes.',
                'notes' => 'Multiple large dumpsters',
                'is_active' => true,
            ],
            [
                'name' => 'South District Route A',
                'zone' => 'SD-A',
                'description' => 'Southern residential neighborhoods including Pine Hills and Cedar Grove.',
                'notes' => null,
                'is_active' => true,
            ],
            [
                'name' => 'East District Route A',
                'zone' => 'ED-A',
                'description' => 'Eastern industrial and commercial zones.',
                'notes' => 'Requires large capacity truck',
                'is_active' => true,
            ],
            [
                'name' => 'East District Route B',
                'zone' => 'ED-B',
                'description' => 'Eastern residential areas near the river.',
                'notes' => null,
                'is_active' => false,
            ],
            [
                'name' => 'West District Route A',
                'zone' => 'WD-A',
                'description' => 'Western suburbs and new developments.',
                'notes' => 'Narrow streets - use smaller truck',
                'is_active' => true,
            ],
            [
                'name' => 'Central District Route A',
                'zone' => 'CD-A',
                'description' => 'Downtown core and business district.',
                'notes' => 'Early morning collection required',
                'is_active' => true,
            ],
            [
                'name' => 'Central District Route B',
                'zone' => 'CD-B',
                'description' => 'Central residential areas and parks.',
                'notes' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Rural Route A',
                'zone' => 'RR-A',
                'description' => 'Rural areas and farmland on the outskirts.',
                'notes' => 'Long distances between stops',
                'is_active' => false,
            ],
            [
                'name' => 'Special Events Route',
                'zone' => 'SE-1',
                'description' => 'Temporary route for special events and festivals.',
                'notes' => 'Only active during events',
                'is_active' => false,
            ],
        ];

        foreach ($routes as $route) {
            Route::create($route);
        }
    }
}
