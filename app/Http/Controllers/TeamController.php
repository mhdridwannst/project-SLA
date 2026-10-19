<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index()
    {
        $members = User::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->get();

        return view('team.index', compact('members'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Hanya Workspace Admin yang dapat menambahkan anggota.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', "Akun {$validated['name']} berhasil ditambahkan ke workspace.");
    }
}
