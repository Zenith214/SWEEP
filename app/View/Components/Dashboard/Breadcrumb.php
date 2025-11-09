<?php

namespace App\View\Components\Dashboard;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    public array $items;

    /**
     * Create a new component instance.
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard.breadcrumb');
    }

    /**
     * Build breadcrumb items from request context.
     */
    public static function fromRequest(): array
    {
        $items = [];
        $returnUrl = request('return_url');

        // Add dashboard as first item if we have a return URL
        if ($returnUrl) {
            $items[] = [
                'label' => 'Dashboard',
                'url' => $returnUrl,
                'icon' => 'speedometer2'
            ];
        }

        return $items;
    }
}
