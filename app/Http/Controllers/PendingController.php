<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsApp;
use Illuminate\Http\Request;

class PendingController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $data = session('pending_user_data');

        if ($user) {
            $name = $user->name;
            $email = $user->email;
            $whatsappNumber = $user->whatsapp_number;
        } elseif (is_array($data)) {
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $whatsappNumber = $data['whatsapp_number'] ?? null;
        } else {
            return redirect()->route('home');
        }

        $activationLink = WhatsApp::activationLink($name, $email, $whatsappNumber);

        return view('auth.pending', [
            'name' => $name,
            'email' => $email,
            'whatsappNumber' => $whatsappNumber,
            'activationLink' => $activationLink,
        ]);
    }
}
