<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\ReferralCodeHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'member')
            ->with(['upline', 'intendedProduct'])
            ->withCount('downlines');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%")
                    ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        $members = $query->latest()->paginate(15)->withQueryString();

        return view('admin.members', compact('members'));
    }

    public function edit(User $user)
    {
        $members = User::where('role', 'member')
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        $referralCodeHistories = $user->referralCodeHistories()
            ->with('changedBy:id,name,email,role')
            ->limit(50)
            ->get();

        return view('admin.members-edit', compact('user', 'members', 'referralCodeHistories'));
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

        $oldCode = $user->referral_code;
        $newCode = strtoupper($request->referral_code);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $normalizedWhatsapp,
            'referral_code' => $newCode,
            'upline_id' => $request->upline_id,
            'can_upload_product' => $request->boolean('can_upload_product'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($oldCode !== $newCode) {
            ReferralCodeHistory::create([
                'user_id' => $user->id,
                'old_code' => $oldCode,
                'new_code' => $newCode,
                'changed_by_id' => $request->user()?->id,
                'changed_by_role' => ReferralCodeHistory::ROLE_ADMIN,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);
        }

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
