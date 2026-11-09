<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AvatarController extends Controller
{
    /**
     * POST /avatar (AS-01). Content-sniffed image types only (no SVG), size-limited,
     * random filename, stored on the private disk outside the web root.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
        ]);

        $user = $request->user();
        $oldPath = $user->avatar_path;

        $user->avatar_path = $request->file('avatar')->store('avatars');
        $user->save();

        if ($oldPath) {
            Storage::delete($oldPath);
        }

        return back()->with('status', 'Foto profil diperbarui.');
    }

    public function show(User $user): StreamedResponse
    {
        abort_unless($user->avatar_path && Storage::exists($user->avatar_path), 404);

        return Storage::response($user->avatar_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }
}
