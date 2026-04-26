<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return view('dashboard.settings', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
        ]);

        $request->user()->update($request->only('name', 'bank_name', 'bank_account'));

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
