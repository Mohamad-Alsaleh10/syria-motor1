<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    /**
     * Display a listing of the promotions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $promotions = Promotion::paginate(10);
        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.promotions.create');
    }

    /**
     * Store a newly created promotion in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'type' => 'required|in:percentage,fixed', // <--- add this
    'discount_percentage' => 'nullable|numeric|min:0|max:100',
    'fixed_discount_amount' => 'nullable|numeric|min:0',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after_or_equal:start_date',
    'is_active' => 'boolean',
]);

Promotion::create([
    'user_id' => auth()->id(),
    'title' => $request->title,
    'description' => $request->description,
    'type' => $request->type,  // <--- include it
    'discount_percentage' => $request->discount_percentage,
    'fixed_discount_amount' => $request->fixed_discount_amount,
    'start_date' => $request->start_date,
    'end_date' => $request->end_date,
    'is_active' => $request->is_active ?? true,
]);
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion created successfully.');
    }

    /**
     * Display the specified promotion.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\View\View
     */
    public function show(Promotion $promotion)
    {
        return view('admin.promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified promotion.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\View\View
     */
    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    /**
     * Update the specified promotion in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'fixed_discount_amount' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        $promotion->update($request->all());

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion updated successfully.');
    }

    /**
     * Remove the specified promotion from storage.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('admin.promotions.index')->with('success', 'Promotion deleted successfully.');
    }
}
