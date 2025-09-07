<x-app-layout> {{-- استخدم x-app-layout الموجودة --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('لوحة تحكم المدير') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">إجمالي المستخدمين</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $totalUsers }}</p>
                    <div class="mt-4 text-sm text-gray-600">
                        <p>مستخدمين موثقين: {{ $verifiedUsers }}</p>
                        <p>مستخدمين غير موثقين: {{ $unverifiedUsers }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">الإعلانات النشطة</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ $activeAds }}</p>
                    <div class="mt-4 text-sm text-gray-600">
                        <p>إعلانات قيد المراجعة: {{ $pendingAds }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">المزادات النشطة</h3>
                    <p class="mt-2 text-3xl font-bold text-purple-600">{{ $activeAuctions }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">طلبات الخدمة المعلقة</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-600">{{ $pendingServiceRequests }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">إجمالي الورش</h3>
                    <p class="mt-2 text-3xl font-bold text-blue-600">{{ $totalWorkshops }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900">إجمالي قيمة المعاملات</h3>
                    <p class="mt-2 text-3xl font-bold text-red-600">{{ number_format($totalTransactions, 2) }}</p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
