<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Message Details') }}: {{ $message->subject }} (ID: {{ $message->id }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.messages.index') }}" class="text-indigo-600 hover:text-indigo-900">&larr; {{ __('Back to Messages List') }}</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Message ID') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $message->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Sender') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($message->sender)
                                    <a href="{{ route('admin.users.show', $message->sender) }}" class="text-blue-600 hover:underline">{{ $message->sender->name }}</a> ({{ $message->sender->email }})
                                @else
                                    {{ __('System') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Receiver') }}:</p>
                            <p class="text-lg font-medium text-gray-900">
                                @if($message->receiver)
                                    <a href="{{ route('admin.users.show', $message->receiver) }}" class="text-blue-600 hover:underline">{{ $message->receiver->name }}</a> ({{ $message->receiver->email }})
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Subject') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $message->subject }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">{{ __('Message Body') }}:</p>
                            <div class="mt-2 p-4 bg-gray-50 rounded-md border border-gray-200">
                                <p class="text-base text-gray-800 whitespace-pre-wrap">{{ $message->body }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Sent At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $message->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Read At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $message->read_at ? $message->read_at->format('Y-m-d H:i') : __('Unread') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Updated At') }}:</p>
                            <p class="text-lg font-medium text-gray-900">{{ $message->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex">
                        <a href="{{ route('admin.messages.edit', $message) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit Message Status') }}
                        </a>
                        <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="inline ms-3" onsubmit="return confirm('{{ __('Are you sure you want to delete this message? This action cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete Message') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
