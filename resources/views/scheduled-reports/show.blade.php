<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $scheduledReport->name }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.scheduled-reports.edit', $scheduledReport) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit
                </a>
                <a href="{{ route('admin.scheduled-reports.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Report Configuration</h3>
                    
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($scheduledReport->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Frequency</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($scheduledReport->frequency) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Format</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ strtoupper($scheduledReport->format) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $scheduledReport->created_at->format('M d, Y') }}</dd>
                        </div>

                        @if ($scheduledReport->last_generated_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Generated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $scheduledReport->last_generated_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif

                        @if ($scheduledReport->is_active && $scheduledReport->next_generation_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Next Generation</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $scheduledReport->next_generation_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-6">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Included Metrics</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($scheduledReport->metrics as $metric)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ \App\Models\ScheduledReport::getAvailableMetrics()[$metric] ?? $metric }}
                                    </span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Generated Reports</h3>
                    
                    @if ($scheduledReport->generatedReports->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No reports have been generated yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Period
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Generated
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Size
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($scheduledReport->generatedReports as $generated)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $generated->getPeriodDescription() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $generated->generated_at->format('M d, Y g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $generated->getFormattedFileSize() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('reports.download', $generated) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                                    Download
                                                </a>
                                                <form action="{{ route('reports.delete-generated', $generated) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
