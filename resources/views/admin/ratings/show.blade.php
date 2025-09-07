<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rating Details') }}: (ID: {{ $rating->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.ratings.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; {{ __('Back to Ratings List') }}</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Rating ID') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $rating->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Rater') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($rating->rater)
                                    <a href="{{ route('admin.users.show', $rating->rater) }}" class="text-blue-600 hover:underline">{{ $rating->rater->name }}</a> ({{ $rating->rater->email }})
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Rated Entity') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($rating->rateable)
                                    {{ class_basename($rating->rateable_type) }}: {{ $rating->rateable->name ?? $rating->rateable->title ?? $rating->rateable->id }}
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Rating') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $rating->rating }} / 5</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">{{ __('Comment') }}:</p>
                            <div class="mt-2 p-4 bg-gray-50 rounded-md border border-gray-200">
                                <p class="text-base text-gray-800 whitespace-pre-wrap">{{ $rating->comment ?? __('N/A') }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Status') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($rating->status == 'approved') bg-green-100 text-green-800
                                    @elseif($rating->status == 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($rating->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Created At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $rating->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Updated At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $rating->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex">
                        <a href="{{ route('admin.ratings.edit', $rating) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit Rating') }}
                        </a>
                        <form action="{{ route('admin.ratings.destroy', $rating) }}" method="POST" class="inline ms-3" onsubmit="return confirm('{{ __('Are you sure you want to delete this rating? This action cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete Rating') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
