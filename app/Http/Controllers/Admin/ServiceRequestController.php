<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\User; // Make sure to import the User model
use App\Models\Workshop; // Make sure to import the Workshop model
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the service requests.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch service requests with their associated user and workshop
        $serviceRequests = ServiceRequest::with(['user', 'workshop'])->paginate(10);
        return view('admin.service-requests.index', compact('serviceRequests'));
    }

    /**
     * Display the specified service request.
     *
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\View\View
     */
    public function show(ServiceRequest $serviceRequest)
    {
        // Load relationships for detailed view
        $serviceRequest->load('user', 'workshop'); // Load user who made the request and the assigned workshop
        return view('admin.service-requests.show', compact('serviceRequest'));
    }

    /**
     * Show the form for editing the specified service request.
     *
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\View\View
     */
    public function edit(ServiceRequest $serviceRequest)
    {
        // Define possible statuses for a service request
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        // You might also want to pass a list of workshops for assignment
        $workshops = Workshop::all();
        return view('admin.service-requests.edit', compact('serviceRequest', 'statuses', 'workshops'));
    }

    /**
     * Update the specified service request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'service_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'user_id' => 'required|exists:users,id',
            'workshop_id' => 'nullable|exists:workshops,id', // Workshop can be null if not assigned yet
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after_or_equal:scheduled_at',
        ]);

        $serviceRequest->update([
            'service_type' => $request->service_type,
            'description' => $request->description,
            'status' => $request->status,
            'user_id' => $request->user_id,
            'workshop_id' => $request->workshop_id,
            'scheduled_at' => $request->scheduled_at,
            'completed_at' => $request->completed_at,
        ]);

        return redirect()->route('admin.service-requests.index')->with('success', 'Service Request updated successfully.');
    }

    /**
     * Remove the specified service request from storage.
     *
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        $serviceRequest->delete();
        return redirect()->route('admin.service-requests.index')->with('success', 'Service Request deleted successfully.');
    }
}
