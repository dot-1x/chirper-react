<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("Chirps/Index", ['chirps' => Chirp::with('user:id,name')->latest()->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        if ($request->hasFile("image")) {
            if (!Storage::exists($request->user()->id))
                Storage::createDirectory($request->user()->id);
            $path = Storage::putFile($request->user()->id, $request->file("image"));
            $validated["image"] = basename($path);
        }
        $request->user()->chirps()->create($validated);
        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        Gate::authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Chirp $chirp): RedirectResponse
    {
        Gate::authorize('delete', $chirp);
        if ($chirp->image) {
            $userid = $request->user()->id;
            Storage::delete(storage_path("app/private/{$userid}/asw.png"));
        }
        $chirp->delete();

        return redirect(route('chirps.index'));
    }

    public function image(Request $request, String $filename)
    {
        $userid = $request->user()->id;
        try {
            return response()->file(storage_path("app/private/{$userid}/{$filename}"));
        } catch (FileNotFoundException) {
            abort(404);
        }
    }
}
