<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $lastMessages = DB::table('messages')
            ->select('user_id', DB::raw('MAX(id) as last_id'), DB::raw('MAX(created_at) as last_at'))
            ->groupBy('user_id');

        $query = User::where('role', 'member')
            ->joinSub($lastMessages, 'lm', 'lm.user_id', '=', 'users.id')
            ->leftJoin('messages as m', 'm.id', '=', 'lm.last_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.whatsapp_number',
                'm.body as last_body',
                'm.sender_role as last_sender_role',
                'm.attachment_path as last_attachment_path',
                'lm.last_at'
            )
            ->selectSub(
                Message::query()
                    ->whereColumn('messages.user_id', 'users.id')
                    ->where('sender_role', Message::ROLE_MEMBER)
                    ->whereNull('read_at')
                    ->selectRaw('count(*)'),
                'unread_count'
            )
            ->orderByDesc('lm.last_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.whatsapp_number', 'like', "%{$search}%");
            });
        }

        $conversations = $query->paginate(20)->withQueryString();

        return view('admin.messages.index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, User $user)
    {
        if ($user->role !== 'member') {
            abort(404);
        }

        $messages = $user->messages()
            ->with('sender:id,name,role')
            ->orderBy('created_at')
            ->get();

        $user->messages()
            ->where('sender_role', Message::ROLE_MEMBER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.messages.show', [
            'member' => $user,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, User $user)
    {
        if ($user->role !== 'member') {
            abort(404);
        }

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
            'attachment.mimes' => 'Format lampiran tidak didukung.',
        ]);

        if (! $request->filled('body') && ! $request->hasFile('attachment')) {
            return back()->withErrors(['body' => 'Tulis pesan atau lampirkan file.'])->withInput();
        }

        $data = [
            'user_id' => $user->id,
            'sender_role' => Message::ROLE_ADMIN,
            'sender_id' => $request->user()->id,
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

        return redirect()->route('admin.messages.show', $user)->with('success', 'Pesan terkirim.');
    }

    public function attachment(Request $request, Message $message): StreamedResponse
    {
        if (! $message->hasAttachment() || ! Storage::disk('local')->exists($message->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_name ?? basename($message->attachment_path));
    }
}
