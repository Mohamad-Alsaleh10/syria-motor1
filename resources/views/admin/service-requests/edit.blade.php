<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Service Request') }}: (ID: {{ $serviceRequest->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.service-requests.update', $serviceRequest) }}">
                        @csrf
                        @method('PUT')

                        <!-- User Information (Display Only) -->
                        <div class="mb-6 border-b pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('User Information') }}</h3>
                            <p class="text-gray-700"><strong>{{ __('Name') }}:</strong> {{ $serviceRequest->user->name ?? 'N/A' }}</p>
                            <p class="text-gray-700"><strong>{{ __('Email') }}:</strong> {{ $serviceRequest->user->email ?? 'N/A' }}</p>
                            <p class="text-gray-700"><strong>{{ __('Phone') }}:</strong> {{ $serviceRequest->user->phone_number ?? 'N/A' }}</p>
                        </div>

                        <!-- Service Type -->
                        <div>
                            <x-input-label for="service_type" :value="__('Service Type')" />
                            <x-text-input id="service_type" class="block mt-1 w-full" type="text" name="service_type" :value="old('service_type', $serviceRequest->service_type)" required autofocus />
                            <x-input-error :messages="$errors->get('service_type')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('description', $serviceRequest->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Assigned Workshop -->
                        <div class="mt-4">
                            <x-input-label for="workshop_id" :value="__('Assign Workshop')" />
                            <select id="workshop_id" name="workshop_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">{{ __('Select a Workshop (Optional)') }}</option>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}" {{ old('workshop_id', $serviceRequest->workshop_id) == $workshop->id ? 'selected' : '' }}>
                                        {{ $workshop->name }} ({{ $workshop->location }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('workshop_id')" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption }}" {{ old('status', $serviceRequest->status) == $statusOption ? 'selected' : '' }}>
                                        {{ ucfirst($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Scheduled At -->
                        <div class="mt-4">
                            <x-input-label for="scheduled_at" :value="__('Scheduled At (Optional)')" />
                            <x-text-input id="scheduled_at" class="block mt-1 w-full" type="datetime-local" name="scheduled_at" :value="old('scheduled_at', $serviceRequest->scheduled_at ? $serviceRequest->scheduled_at->format('Y-m-d\TH:i') : '')" />
                            <x-input-error :messages="$errors->get('scheduled_at')" class="mt-2" />
                        </div>

                        <!-- Completed At -->
                        <div class="mt-4">
                            <x-input-label for="completed_at" :value="__('Completed At (Optional)')" />
                            <x-text-input id="completed_at" class="block mt-1 w-full" type="datetime-local" name="completed_at" :value="old('completed_at', $serviceRequest->completed_at ? $serviceRequest->completed_at->format('Y-m-d\TH:i') : '')" />
                            <x-input-error :messages="$errors->get('completed_at')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Service Request') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
