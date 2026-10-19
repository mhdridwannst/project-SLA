<?php

namespace App\Http\Controllers;

use App\Models\SlaPolicy;
use Illuminate\Http\Request;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $policies = SlaPolicy::orderBy('resolution_time_minutes', 'asc')->get();

        return view('sla-policies.index', compact('policies'));
    }

    public function update(Request $request, SlaPolicy $slaPolicy)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Hanya Workspace Admin yang dapat mengubah kebijakan SLA.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_response_time_minutes' => ['required', 'integer', 'min:5', 'max:10080'],
            'resolution_time_minutes' => ['required', 'integer', 'min:10', 'max:43200', 'gte:first_response_time_minutes'],
        ]);

        $slaPolicy->update($validated);

        return back()->with('success', "Kebijakan SLA [{$slaPolicy->priority->label()}] berhasil diperbarui.");
    }
}
