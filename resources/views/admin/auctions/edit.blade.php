<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Auction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.auctions.update', $auction) }}">
                        @csrf
                        @method('PUT')

                        <!-- Car -->
                        <div class="mt-4">
                            <x-input-label for="car_id" :value="__('Car')" />
                            <select name="car_id" id="car_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="{{ $auction->car_id }}">{{ $auction->car->name ?? 'Car #' . $auction->car_id }}</option>
                                {{-- يمكنك إضافة باقي السيارات هنا إذا أردت --}}
                            </select>
                            <x-input-error :messages="$errors->get('car_id')" class="mt-2" />
                        </div>

                        <!-- Seller -->
                        <div class="mt-4">
                            <x-input-label for="user_id" :value="__('Seller')" />
                            <select name="user_id" id="user_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="{{ $auction->user_id }}">{{ $auction->user->name ?? 'User #' . $auction->user_id }}</option>
                                {{-- يمكنك إضافة باقي المستخدمين هنا إذا أردت --}}
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>

                        <!-- Start Price -->
                        <div class="mt-4">
                            <x-input-label for="start_price" :value="__('Start Price')" />
                            <x-text-input id="start_price" class="block mt-1 w-full" type="number" name="start_price" step="0.01" value="{{ old('start_price', $auction->starting_price) }}" required />
                            <x-input-error :messages="$errors->get('start_price')" class="mt-2" />
                        </div>

                        <!-- Current Price -->
                        <div class="mt-4">
                            <x-input-label for="current_price" :value="__('Current Price')" />
                            <x-text-input id="current_price" class="block mt-1 w-full" type="number" name="current_price" step="0.01" value="{{ old('current_price', $auction->current_price) }}" />
                            <x-input-error :messages="$errors->get('current_price')" class="mt-2" />
                        </div>

                        <!-- Start Time -->
                        <div class="mt-4">
                            <x-input-label for="start_time" :value="__('Start Time')" />
                            <x-text-input id="start_time" class="block mt-1 w-full" type="datetime-local" name="start_time" value="{{ old('start_time', $auction->start_time->format('Y-m-d\TH:i')) }}" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>

                        <!-- End Time -->
                        <div class="mt-4">
                            <x-input-label for="end_time" :value="__('End Time')" />
                            <x-text-input id="end_time" class="block mt-1 w-full" type="datetime-local" name="end_time" value="{{ old('end_time', $auction->end_time->format('Y-m-d\TH:i')) }}" required />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>

                        <!-- Winner -->
                        <div class="mt-4">
                            <x-input-label for="winner_id" :value="__('Winner (optional)')" />
                            <select name="winner_id" id="winner_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- {{ __('No winner yet') }} --</option>
                                @foreach(\App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}" {{ old('winner_id', $auction->winner_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('winner_id')" class="mt-2" />
                        </div>

                        <!-- Status -->
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" id="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $auction->status) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Update Auction') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
