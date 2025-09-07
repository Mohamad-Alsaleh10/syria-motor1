<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Rating') }}: (ID: {{ $rating->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.ratings.update', $rating) }}">
                        @csrf
                        @method('PUT')

                        <!-- Rater/Rated Entity Information (Display Only) -->
                        <div class="mb-6 border-b pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Rating Information') }}</h3>
                            <p class="text-gray-700"><strong>{{ __('Rater') }}:</strong> {{ $rating->rater->name ?? 'N/A' }} ({{ $rating->rater->email ?? 'N/A' }})</p>
                            <p class="text-gray-700"><strong>{{ __('Rated Entity') }}:</strong>
                                @if($rating->rateable)
                                    {{ class_basename($rating->rateable_type) }}: {{ $rating->rateable->name ?? $rating->rateable->title ?? $rating->rateable->id }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                            <p class="text-gray-700"><strong>{{ __('Given At') }}:</strong> {{ $rating->created_at->format('Y-m-d H:i') }}</p>
                        </div>

                        <!-- Rating Value -->
                        <div>
                            <x-input-label for="rating" :value="__('Rating (1-5)')" />
                            <x-text-input id="rating" class="block mt-1 w-full" type="number" name="rating" :value="old('rating', $rating->rating)" min="1" max="5" required autofocus />
                            <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                        </div>

                        <!-- Comment -->
                        <div class="mt-4">
                            <x-input-label for="comment" :value="__('Comment')" />
                            <textarea id="comment" name="comment" rows="4" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('comment', $rating->comment) }}</textarea>
                            <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                @foreach($statuses as $statusOption)
                                    <option value="{{ $statusOption }}" {{ old('status', $rating->status) == $statusOption ? 'selected' : '' }}>
                                        {{ ucfirst($statusOption) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Rating') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
