<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Car; // تأكد من استيراد نموذج السيارة
use App\Models\User; // تأكد من استيراد نموذج المستخدم (البائع)
use Illuminate\Validation\Rule;

class AuctionController extends Controller
{
    /**
     * Display a listing of the auctions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch auctions with their associated car and user (seller)
        $auctions = Auction::with(['car', 'user'])->paginate(10);
        return view('admin.auctions.index', compact('auctions'));
    }

    /**
     * Display the specified auction.
     *
     * @param  \App\Models\Auction  $auction
     * @return \Illuminate\View\View
     */
    public function show(Auction $auction)
    {
        // Load relationships for detailed view, including bids
        $auction->load('car.user', 'user', 'bids.user'); // Load car, seller, and bids with their users
        return view('admin.auctions.show', compact('auction'));
    }

    /**
     * Show the form for editing the specified auction.
     *
     * @param  \App\Models\Auction  $auction
     * @return \Illuminate\View\View
     */
    public function edit(Auction $auction)
    {
        // Define possible statuses for an auction
        $statuses = ['pending', 'active', 'completed', 'cancelled'];
        return view('admin.auctions.edit', compact('auction', 'statuses'));
    }

    /**
     * Update the specified auction in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Auction  $auction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Auction $auction)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'completed', 'cancelled'])],
            'start_price' => 'required|numeric|min:0',
            'current_price' => 'nullable|numeric|min:0|gte:start_price', // Current price should be >= start price
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time', // End time must be after start time
            'winner_id' => 'nullable|exists:users,id', // Optional winner ID
        ]);

        $auction->update([
            'status' => $request->status,
            'start_price' => $request->start_price,
            'current_price' => $request->current_price,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'winner_id' => $request->winner_id,
        ]);

        return redirect()->route('admin.auctions.index')->with('success', 'Auction updated successfully.');
    }

    /**
     * Remove the specified auction from storage.
     *
     * @param  \App\Models\Auction  $auction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Auction $auction)
    {
        // Optionally, delete associated bids first if they don't cascade automatically
        // $auction->bids()->delete();
        $auction->delete();
        return redirect()->route('admin.auctions.index')->with('success', 'Auction deleted successfully.');
    }
}
