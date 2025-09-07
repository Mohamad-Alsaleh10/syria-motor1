<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Promotions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('List of Promotions') }}</h3>
                        <a href="{{ route('admin.promotions.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent
                                  rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                  hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                  transition ease-in-out duration-150">
                            {{ __('Add New Promotion') }}
                        </a>
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
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ID') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Title') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Discount %') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Fixed Discount') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Start Date') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('End Date') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Active') }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($promotions as $promotion)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promotion->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promotion->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">{{ $promotion->type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $promotion->discount_percentage !== null ? $promotion->discount_percentage . '%' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $promotion->fixed_discount_amount !== null ? number_format($promotion->fixed_discount_amount, 2) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promotion->start_date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $promotion->end_date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($promotion->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('Yes') }}</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ __('No') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.promotions.show', $promotion) }}" class="text-indigo-600 hover:text-indigo-900 me-3">{{ __('View') }}</a>
                                            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="text-green-600 hover:text-green-900 me-3">{{ __('Edit') }}</a>
                                            <form action="{{ route('admin.promotions.destroy', $promotion) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this promotion?') }}');">
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
                        {{ $promotions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
