<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Workshop;
use App\Models\User; // تأكد من استيراد نموذج المستخدم (مالك الورشة)
use Illuminate\Validation\Rule;

class WorkshopController extends Controller
{
    /**
     * Display a listing of the workshops.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch workshops with their associated user (owner)
        $workshops = Workshop::with('user')->paginate(10);
        return view('admin.workshops.index', compact('workshops'));
    }

    /**
     * Display the specified workshop.
     *
     * @param  \App\Models\Workshop  $workshop
     * @return \Illuminate\View\View
     */
    public function show(Workshop $workshop)
    {
        // Load relationships for detailed view
        $workshop->load('user', 'serviceRequests'); // Load owner and service requests
        return view('admin.workshops.show', compact('workshop'));
    }

    /**
     * Show the form for editing the specified workshop.
     *
     * @param  \App\Models\Workshop  $workshop
     * @return \Illuminate\View\View
     */
    public function edit(Workshop $workshop)
    {
        // Define possible statuses for a workshop
        $statuses = ['pending', 'active', 'suspended'];
        return view('admin.workshops.edit', compact('workshop', 'statuses'));
    }

    /**
     * Update the specified workshop in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Workshop  $workshop
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Workshop $workshop)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone_number' => ['required', 'string', 'max:255', Rule::unique('workshops')->ignore($workshop->id)],
            'description' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'suspended'])],
            'user_id' => 'required|exists:users,id', // Ensure user_id is valid
        ]);

        $workshop->update([
            'name' => $request->name,
            'location' => $request->location,
            'phone_number' => $request->phone_number,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('admin.workshops.index')->with('success', 'Workshop updated successfully.');
    }

    /**
     * Remove the specified workshop from storage.
     *
     * @param  \App\Models\Workshop  $workshop
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Workshop $workshop)
    {
        // Optionally, delete associated service requests first if they don't cascade automatically
        // $workshop->serviceRequests()->delete();
        $workshop->delete();
        return redirect()->route('admin.workshops.index')->with('success', 'Workshop deleted successfully.');
    }
}
