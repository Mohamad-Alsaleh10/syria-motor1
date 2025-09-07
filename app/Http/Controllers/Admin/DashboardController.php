<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ad;
use App\Models\Auction;
use App\Models\ServiceRequest;
use App\Models\Transaction;
use App\Models\Workshop;

class DashboardController extends Controller
{
    public function index()
    {
        // جلب الإحصائيات الرئيسية لعرضها في لوحة التحكم
        $totalUsers = User::count();
        $verifiedUsers = User::where('is_verified', true)->count();
        $unverifiedUsers = User::where('is_verified', false)->count();
        $activeAds = Ad::where('status', 'active')->count();
        $pendingAds = Ad::where('status', 'pending')->count();
        $activeAuctions = Auction::where('status', 'active')->count();
        $pendingServiceRequests = ServiceRequest::where('status', 'pending')->count();
        $totalWorkshops = Workshop::count();
        $totalTransactions = Transaction::sum('amount'); // مجموع كل المعاملات

        return view('admin.dashboard', compact(
            'totalUsers',
            'verifiedUsers',
            'unverifiedUsers',
            'activeAds',
            'pendingAds',
            'activeAuctions',
            'pendingServiceRequests',
            'totalWorkshops',
            'totalTransactions'
        ));
    }
}
