<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workshop Details') }}: {{ $workshop->name }} (ID: {{ $workshop->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.workshops.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; {{ __('Back to Workshops List') }}</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Workshop ID') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Name') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Owner') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($workshop->user)
                                    <a href="{{ route('admin.users.show', $workshop->user) }}" class="text-blue-600 hover:underline">{{ $workshop->user->name }}</a> ({{ $workshop->user->email }})
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Location') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->location }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Phone Number') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->phone_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Description') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->description ?? __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Status') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($workshop->status == 'active') bg-green-100 text-green-800
                                    @elseif($workshop->status == 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($workshop->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Created At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Updated At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $workshop->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">{{ __('Service Requests for this Workshop') }}</h4>
                        @if($workshop->serviceRequests->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Request ID') }}</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('User') }}</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Service Type') }}</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                            <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Requested At') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($workshop->serviceRequests->sortByDesc('created_at') as $serviceRequest)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $serviceRequest->id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($serviceRequest->user)
                                                        <a href="{{ route('admin.users.show', $serviceRequest->user) }}" class="text-blue-600 hover:underline">{{ $serviceRequest->user->name }}</a>
                                                    @else
                                                        {{ __('N/A') }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $serviceRequest->service_type }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($serviceRequest->status == 'completed') bg-green-100 text-green-800
                                                        @elseif($serviceRequest->status == 'pending') bg-yellow-100 text-yellow-800
                                                        @else bg-red-100 text-red-800 @endif">
                                                        {{ ucfirst($serviceRequest->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $serviceRequest->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-600">{{ __('No service requests for this workshop yet.') }}</p>
                        @endif
                    </div>

                    <div class="mt-6 flex">
                        <a href="{{ route('admin.workshops.edit', $workshop) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit Workshop') }}
                        </a>
                        <form action="{{ route('admin.workshops.destroy', $workshop) }}" method="POST" class="inline ms-3" onsubmit="return confirm('{{ __('Are you sure you want to delete this workshop? This action cannot be undone and will also delete associated service requests.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete Workshop') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
