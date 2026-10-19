<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman registrasi.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran Perusahaan (Tenant) sekaligus Akun Admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Gunakan DB::transaction agar pembuatan Tenant, User, dan SLA Policy
        // berjalan secara atomik (semua berhasil atau batal semua jika ada error)
        $user = DB::transaction(function () use ($request) {
            // 1. Buat data Perusahaan (Tenant) baru
            $tenant = Tenant::create([
                'name' => $request->company_name,
                'slug' => Str::slug($request->company_name) . '-' . Str::lower(Str::random(5)),
                'plan' => 'free',
                'is_active' => true,
            ]);

            // 2. Buat akun User sebagai Admin dari perusahaan tersebut
            $newUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'admin',
                'password' => Hash::make($request->password),
            ]);

            $defaultPolicies = [
                ['name' => 'Low Priority', 'priority' => 'low', 'first_response_time_minutes' => 240, 'resolution_time_minutes' => 1440],
                ['name' => 'Medium Priority', 'priority' => 'medium', 'first_response_time_minutes' => 120, 'resolution_time_minutes' => 480],
                ['name' => 'High Priority', 'priority' => 'high', 'first_response_time_minutes' => 30, 'resolution_time_minutes' => 120],
                ['name' => 'Critical Priority', 'priority' => 'critical', 'first_response_time_minutes' => 15, 'resolution_time_minutes' => 60],
            ];

            foreach ($defaultPolicies as $policy) {
                SlaPolicy::create([
                    'tenant_id' => $tenant->id,
                    'name' => $policy['name'],
                    'priority' => $policy['priority'],
                    'first_response_time_minutes' => $policy['first_response_time_minutes'],
                    'resolution_time_minutes' => $policy['resolution_time_minutes'],
                ]);
            }

            return $newUser;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
