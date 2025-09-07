<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Service Request Details') }}: (ID: {{ $serviceRequest->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.service-requests.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; {{ __('Back to Service Requests List') }}</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Request ID') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('User') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($serviceRequest->user)
                                    <a href="{{ route('admin.users.show', $serviceRequest->user) }}" class="text-blue-600 hover:underline">{{ $serviceRequest->user->name }}</a> ({{ $serviceRequest->user->email }})
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Service Type') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->service_type }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Description') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->description ?? __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Assigned Workshop') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($serviceRequest->workshop)
                                    <a href="{{ route('admin.workshops.show', $serviceRequest->workshop) }}" class="text-blue-600 hover:underline">{{ $serviceRequest->workshop->name }}</a>
                                @else
                                    {{ __('Not Assigned') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Status') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($serviceRequest->status == 'completed') bg-green-100 text-green-800
                                    @elseif($serviceRequest->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($serviceRequest->status == 'in_progress') bg-blue-100 text-blue-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($serviceRequest->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Scheduled At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->scheduled_at ? $serviceRequest->scheduled_at->format('Y-m-d H:i') : __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Completed At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->completed_at ? $serviceRequest->completed_at->format('Y-m-d H:i') : __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Created At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Updated At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $serviceRequest->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex">
                        <a href="{{ route('admin.service-requests.edit', $serviceRequest) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit Service Request') }}
                        </a>
                        <form action="{{ route('admin.service-requests.destroy', $serviceRequest) }}" method="POST" class="inline ms-3" onsubmit="return confirm('{{ __('Are you sure you want to delete this service request? This action cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete Service Request') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
