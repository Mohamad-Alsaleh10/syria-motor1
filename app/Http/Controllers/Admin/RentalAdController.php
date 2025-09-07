<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalAd;
use App\Models\Car; // Make sure to import the Car model
use App\Models\User; // Make sure to import the User model
use Illuminate\Validation\Rule;

class RentalAdController extends Controller
{
    /**
     * Display a listing of the rental ads.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch rental ads with their associated car and user (renter)
        // Use with() to avoid N+1 query problem
        $rentalAds = RentalAd::with(['car', 'user'])->paginate(10);
        return view('admin.rental-ads.index', compact('rentalAds'));
    }

    /**
     * Display the specified rental ad.
     *
     * @param  \App\Models\RentalAd  $rentalAd
     * @return \Illuminate\View\View
     */
    public function show(RentalAd $rentalAd)
    {
        // Load relationships for detailed view
        $rentalAd->load('car.user', 'user'); // Load car and its owner, and the rental ad's renter
        return view('admin.rental-ads.show', compact('rentalAd'));
    }

    /**
     * Show the form for editing the specified rental ad.
     *
     * @param  \App\Models\RentalAd  $rentalAd
     * @return \Illuminate\View\View
     */
    public function edit(RentalAd $rentalAd)
    {
        // Define possible statuses for a rental ad
        $statuses = ['pending', 'active', 'rented', 'rejected'];
        return view('admin.rental-ads.edit', compact('rentalAd', 'statuses'));
    }

    /**
     * Update the specified rental ad in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RentalAd  $rentalAd
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, RentalAd $rentalAd)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'rented', 'rejected'])],
            'daily_price' => 'nullable|numeric|min:0',
            'monthly_price' => 'nullable|numeric|min:0',
            'rental_conditions' => 'nullable|string',
            'location' => 'required|string|max:255',
        ]);

        $rentalAd->update([
            'status' => $request->status,
            'daily_price' => $request->daily_price,
            'monthly_price' => $request->monthly_price,
            'rental_conditions' => $request->rental_conditions,
            'location' => $request->location,
        ]);

        return redirect()->route('admin.rental-ads.index')->with('success', 'Rental Ad updated successfully.');
    }

    /**
     * Remove the specified rental ad from storage.
     *
     * @param  \App\Models\RentalAd  $rentalAd
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(RentalAd $rentalAd)
    {
        $rentalAd->delete();
        return redirect()->route('admin.rental-ads.index')->with('success', 'Rental Ad deleted successfully.');
    }
}
