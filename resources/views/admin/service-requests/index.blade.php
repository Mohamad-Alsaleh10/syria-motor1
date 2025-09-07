<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Service Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('List of Service Requests') }}</h3>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Request ID') }}</th>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('User') }}</th>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Service Type') }}</th>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Assigned Workshop') }}</th>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Requested At') }}</th>
                                    <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($serviceRequests as $serviceRequest)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $serviceRequest->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($serviceRequest->user)
                                                {{ $serviceRequest->user->name }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $serviceRequest->service_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($serviceRequest->workshop)
                                                {{ $serviceRequest->workshop->name }}
                                            @else
                                                {{ __('Not Assigned') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($serviceRequest->status == 'completed') bg-green-100 text-green-800
                                                @elseif($serviceRequest->status == 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($serviceRequest->status == 'in_progress') bg-blue-100 text-blue-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($serviceRequest->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $serviceRequest->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.service-requests.show', $serviceRequest) }}" class="text-indigo-600 hover:text-indigo-900 me-3">{{ __('View') }}</a>
                                            <a href="{{ route('admin.service-requests.edit', $serviceRequest) }}" class="text-green-600 hover:text-green-900 me-3">{{ __('Edit') }}</a>
                                            <form action="{{ route('admin.service-requests.destroy', $serviceRequest) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this service request?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $serviceRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
