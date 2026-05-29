<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = $user->messages()
            ->with('sender:id,name,role')
            ->orderBy('created_at')
            ->get();

        $user->messages()
            ->where('sender_role', Message::ROLE_ADMIN)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('dashboard.messages', [
            'messages' => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,csv,txt',
            ],
        ], [
            'attachment.max' => 'Lampiran maksimal 5 MB.',
            'attachment.mimes' => 'Format lampiran tidak didukung. Gunakan gambar, PDF, dokumen Office, ZIP, CSV, atau TXT.',
        ]);

        if (! $request->filled('body') && ! $request->hasFile('attachment')) {
            return back()->withErrors(['body' => 'Tulis pesan atau lampirkan file.'])->withInput();
        }

        $user = $request->user();
        $data = [
            'user_id' => $user->id,
            'sender_role' => Message::ROLE_MEMBER,
            'sender_id' => $user->id,
            'body' => $request->input('body'),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('messages/'.$user->id, 'local');
            $data['attachment_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
            $data['attachment_mime'] = $file->getMimeType();
            $data['attachment_size'] = $file->getSize();
        }

        Message::create($data);

        return redirect()->route('dashboard.messages')->with('success', 'Pesan terkirim.');
    }

    public function attachment(Request $request, Message $message): StreamedResponse
    {
        $user = $request->user();

        if ($message->user_id !== $user->id) {
            abort(403);
        }

        if (! $message->hasAttachment() || ! Storage::disk('local')->exists($message->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name ?? basename($message->attachment_path));
    }
}
