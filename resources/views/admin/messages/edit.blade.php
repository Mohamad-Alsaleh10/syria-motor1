<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Message') }}: {{ $message->subject }} (ID: {{ $message->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.messages.update', $message) }}">
                        @csrf
                        @method('PUT')

                        <!-- Sender/Receiver Information (Display Only) -->
                        <div class="mb-6 border-b pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Message Information') }}</h3>
                            <p class="text-gray-700"><strong>{{ __('From') }}:</strong> {{ $message->sender->name ?? 'System' }} ({{ $message->sender->email ?? 'N/A' }})</p>
                            <p class="text-gray-700"><strong>{{ __('To') }}:</strong> {{ $message->receiver->name ?? 'N/A' }} ({{ $message->receiver->email ?? 'N/A' }})</p>
                            <p class="text-gray-700"><strong>{{ __('Sent At') }}:</strong> {{ $message->created_at->format('Y-m-d H:i') }}</p>
                        </div>

                        <!-- Subject -->
                        <div>
                            <x-input-label for="subject" :value="__('Subject')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject', $message->subject)" required autofocus />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <!-- Body -->
                        <div class="mt-4">
                            <x-input-label for="body" :value="__('Message Body')" />
                            <textarea id="body" name="body" rows="6" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>{{ old('body', $message->body) }}</textarea>
                            <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        </div>

                        <!-- Read At -->
                        <div class="mt-4">
                            <x-input-label for="read_at" :value="__('Mark as Read At (Optional)')" />
                            <x-text-input id="read_at" class="block mt-1 w-full" type="datetime-local" name="read_at" :value="old('read_at', $message->read_at ? $message->read_at->format('Y-m-d\TH:i') : '')" />
                            <x-input-error :messages="$errors->get('read_at')" class="mt-2" />
                            <p class="text-sm text-gray-600 mt-1">{{ __('Leave empty to mark as unread.') }}</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Message') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
