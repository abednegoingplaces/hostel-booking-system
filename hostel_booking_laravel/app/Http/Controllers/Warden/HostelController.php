<?php

namespace App\Http\Controllers\Warden;

use App\Http\Controllers\Controller;
use App\Http\Requests\HostelRequest;
use App\Models\Hostel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Warden: full CRUD on hostels (resource controller). */
class HostelController extends Controller
{
    public function index(): View
    {
        $hostels = Hostel::withCount('rooms')->orderBy('name')->get();

        return view('warden.hostels.index', compact('hostels'));
    }

    public function create(): View
    {
        return view('warden.hostels.form', ['hostel' => new Hostel(['gender' => 'mixed'])]);
    }

    public function store(HostelRequest $request): RedirectResponse
    {
        Hostel::create($request->validated());

        return redirect()->route('warden.hostels.index')->with('success', 'Hostel added.');
    }

    public function edit(Hostel $hostel): View
    {
        return view('warden.hostels.form', compact('hostel'));
    }

    public function update(HostelRequest $request, Hostel $hostel): RedirectResponse
    {
        $hostel->update($request->validated());

        return redirect()->route('warden.hostels.index')->with('success', 'Hostel updated.');
    }

    public function destroy(Hostel $hostel): RedirectResponse
    {
        if ($hostel->bookings()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'This hostel has pending or approved bookings. Reject or wait for them before deleting it.');
        }

        // Remove room photos from storage; the rooms themselves go with the hostel (cascade).
        Storage::disk('public')->delete($hostel->rooms()->whereNotNull('photo_path')->pluck('photo_path')->all());
        $hostel->delete();

        return redirect()->route('warden.hostels.index')->with('success', 'Hostel deleted.');
    }
}
