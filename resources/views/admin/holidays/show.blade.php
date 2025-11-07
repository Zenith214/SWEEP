<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Holiday Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">{{ $holiday->name }}</h3>
                    </div>

                    <div class="space-y-2">
                        <p><strong>Date:</strong> {{ $holiday->date->format('Y-m-d') }}</p>
                        <p><strong>Collection Skipped:</strong> {{ $holiday->is_collection_skipped ? 'Yes' : 'No' }}</p>
                        @if($holiday->reschedule_date)
                            <p><strong>Rescheduled To:</strong> {{ $holiday->reschedule_date->format('Y-m-d') }}</p>
                        @endif
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('admin.holidays.index') }}" class="text-blue-600 hover:text-blue-800">
                            Back to Holidays
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
