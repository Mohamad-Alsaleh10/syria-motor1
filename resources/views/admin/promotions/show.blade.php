<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Promotion Details') }}: {{ $promotion->title }} (ID: {{ $promotion->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.promotions.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; {{ __('Back to Promotions List') }}</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Promotion ID') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Title') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Description') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->description ?? __('N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Discount Percentage') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->discount_percentage ?? '-' }}%</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Fixed Discount Amount') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ number_format($promotion->fixed_discount_amount, 2) ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Start Date') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->start_date->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('End Date') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->end_date->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Is Active') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($promotion->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('Yes') }}</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ __('No') }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Created At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Updated At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $promotion->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex">
                        <a href="{{ route('admin.promotions.edit', $promotion) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit Promotion') }}
                        </a>
                        <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="inline ms-3" onsubmit="return confirm('{{ __('Are you sure you want to delete this promotion? This action cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete Promotion') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
