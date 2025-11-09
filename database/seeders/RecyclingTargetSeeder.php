<?php

namespace Database\Seeders;

use App\Models\RecyclingTarget;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecyclingTargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create targets for current month and previous 2 months
        $months = [
            Carbon::now()->startOfMonth(),
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonths(2)->startOfMonth(),
        ];

        foreach ($months as $month) {
            $this->createTargetsForMonth($month);
        }

        $this->command->info('Created recycling targets for current and previous 2 months');
    }

    /**
     * Create targets for a specific month.
     */
    private function createTargetsForMonth(Carbon $month): void
    {
        // Material-specific targets
        $materialTargets = [
            RecyclingTarget::TYPE_PLASTIC => $this->getTargetWeight(RecyclingTarget::TYPE_PLASTIC),
            RecyclingTarget::TYPE_PAPER => $this->getTargetWeight(RecyclingTarget::TYPE_PAPER),
            RecyclingTarget::TYPE_GLASS => $this->getTargetWeight(RecyclingTarget::TYPE_GLASS),
            RecyclingTarget::TYPE_METAL => $this->getTargetWeight(RecyclingTarget::TYPE_METAL),
            RecyclingTarget::TYPE_CARDBOARD => $this->getTargetWeight(RecyclingTarget::TYPE_CARDBOARD),
            RecyclingTarget::TYPE_ORGANIC => $this->getTargetWeight(RecyclingTarget::TYPE_ORGANIC),
        ];

        // Create material-specific targets
        foreach ($materialTargets as $materialType => $targetWeight) {
            RecyclingTarget::create([
                'material_type' => $materialType,
                'target_weight' => $targetWeight,
                'month' => $month,
            ]);
        }

        // Create total recyclables target (sum of all material targets)
        $totalTarget = array_sum($materialTargets);
        
        RecyclingTarget::create([
            'material_type' => RecyclingTarget::TYPE_ALL,
            'target_weight' => $totalTarget,
            'month' => $month,
        ]);
    }

    /**
     * Get realistic target weight for a material type.
     * Based on expected collection volumes from typical routes.
     */
    private function getTargetWeight(string $materialType): float
    {
        // Realistic monthly targets in kg based on material type
        // Assuming ~20-25 collection days per month with multiple routes
        $baseTargets = [
            RecyclingTarget::TYPE_PLASTIC => 800,   // Lighter but high volume
            RecyclingTarget::TYPE_PAPER => 1500,    // Common and relatively heavy
            RecyclingTarget::TYPE_GLASS => 1200,    // Heavy but less volume
            RecyclingTarget::TYPE_METAL => 600,     // Less common but heavy
            RecyclingTarget::TYPE_CARDBOARD => 1800, // Very common, moderate weight
            RecyclingTarget::TYPE_ORGANIC => 2000,  // Highest volume
        ];

        $baseTarget = $baseTargets[$materialType] ?? 1000;
        
        // Add some variation (+/- 10%) to make targets more realistic
        $variation = rand(-10, 10) / 100;
        $targetWeight = $baseTarget * (1 + $variation);
        
        return round($targetWeight, 2);
    }
}
