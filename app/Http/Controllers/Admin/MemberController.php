<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index()
    {
        $members = User::where('role', 'member')
            ->with(['upline', 'intendedProduct'])
            ->withCount('downlines')
            ->latest()
            ->paginate(15);

        return view('admin.members', compact('members'));
    }

    public function edit(User $user)
    {
        $members = User::where('role', 'member')
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        return view('admin.members-edit', compact('user', 'members'));
    }

    public function update(Request $request, User $user)
    {
        $normalizedWhatsapp = PhoneNumber::normalize($request->input('whatsapp_number'));
        $request->merge(['whatsapp_number' => $normalizedWhatsapp]);

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'whatsapp_number' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('users', 'whatsapp_number')->ignore($user->id),
                ],
                'referral_code' => ['required', 'string', 'max:50', Rule::unique('users', 'referral_code')->ignore($user->id)],
                'password' => 'nullable|string|min:8',
                'upline_id' => 'nullable|exists:users,id',
            ],
            [
                'whatsapp_number.unique' => 'Nomor WhatsApp ini sudah terdaftar. Gunakan nomor yang berbeda.',
            ]
        );

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $normalizedWhatsapp,
            'referral_code' => strtoupper($request->referral_code),
            'upline_id' => $request->upline_id,
            'can_upload_product' => $request->boolean('can_upload_product'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.members')->with('success', 'Member berhasil diperbarui.');
    }

    public function activate(User $user)
    {
        $user->update(['status' => 'active']);

        return redirect()->back()->with('success', "Member {$user->name} berhasil diaktifkan.");
    }

    public function deactivate(User $user)
    {
        $user->update(['status' => 'pending']);

        return redirect()->back()->with('success', "Member {$user->name} dinonaktifkan (status pending).");
    }

    public function destroy(User $user)
    {
        $user->update([
            'upline_id' => null,
        ]);

        User::where('upline_id', $user->id)->update(['upline_id' => null]);

        $user->delete();

        return redirect()->route('admin.members')->with('success', 'Member berhasil dihapus.');
    }
}
