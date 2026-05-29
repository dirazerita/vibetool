<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Broadcast;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = Broadcast::with('admin:id,name')
            ->orderByDesc('sent_at')
            ->paginate(20);

        $audienceCounts = [
            Broadcast::SCOPE_ALL => User::where('role', 'member')->count(),
            Broadcast::SCOPE_ACTIVE => User::where('role', 'member')->where('status', 'active')->count(),
        ];

        return view('admin.broadcasts.index', [
            'broadcasts' => $broadcasts,
            'audienceCounts' => $audienceCounts,
        ]);
    }

    public function create()
    {
        $audienceCounts = [
            Broadcast::SCOPE_ALL => User::where('role', 'member')->count(),
            Broadcast::SCOPE_ACTIVE => User::where('role', 'member')->where('status', 'active')->count(),
        ];

        return view('admin.broadcasts.create', [
            'audienceCounts' => $audienceCounts,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'audience_scope' => ['required', 'in:'.Broadcast::SCOPE_ALL.','.Broadcast::SCOPE_ACTIVE],
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

        $audienceScope = $request->input('audience_scope');
        $recipientIds = $this->resolveRecipientIds($audienceScope);

        if (empty($recipientIds)) {
            return back()->withErrors(['audience_scope' => 'Tidak ada member yang masuk audience ini.'])->withInput();
        }

        $broadcastData = [
            'admin_id' => $request->user()->id,
            'body' => $request->input('body'),
            'audience_scope' => $audienceScope,
            'recipients_count' => count($recipientIds),
            'sent_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $broadcastData['attachment_path'] = $file->store('broadcasts/'.now()->format('Y/m'), 'local');
            $broadcastData['attachment_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
            $broadcastData['attachment_mime'] = $file->getMimeType();
            $broadcastData['attachment_size'] = $file->getSize();
        }

        $broadcast = DB::transaction(function () use ($broadcastData, $recipientIds, $request) {
            $broadcast = Broadcast::create($broadcastData);

            $now = now();
            $rows = [];
            foreach ($recipientIds as $userId) {
                $rows[] = [
                    'user_id' => $userId,
                    'sender_role' => Message::ROLE_ADMIN,
                    'sender_id' => $request->user()->id,
                    'broadcast_id' => $broadcast->id,
                    'body' => $broadcast->body,
                    'attachment_path' => $broadcast->attachment_path,
                    'attachment_name' => $broadcast->attachment_name,
                    'attachment_mime' => $broadcast->attachment_mime,
                    'attachment_size' => $broadcast->attachment_size,
                    'read_at' => null,
                    'created_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 200) as $chunk) {
                Message::insert($chunk);
            }

            return $broadcast;
        });

        return redirect()
            ->route('admin.broadcasts.show', $broadcast)
            ->with('success', 'Broadcast terkirim ke '.$broadcast->recipients_count.' member.');
    }

    public function show(Broadcast $broadcast)
    {
        $broadcast->load('admin:id,name');

        $readCount = Message::where('broadcast_id', $broadcast->id)
            ->whereNotNull('read_at')
            ->count();

        $recipients = Message::where('broadcast_id', $broadcast->id)
            ->with('user:id,name,email,whatsapp_number')
            ->orderByDesc('read_at')
            ->orderBy('id')
            ->paginate(50);

        return view('admin.broadcasts.show', [
            'broadcast' => $broadcast,
            'readCount' => $readCount,
            'recipients' => $recipients,
        ]);
    }

    public function attachment(Broadcast $broadcast): StreamedResponse
    {
        if (! $broadcast->hasAttachment() || ! Storage::disk('local')->exists($broadcast->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $broadcast->attachment_path,
            $broadcast->attachment_name ?? basename($broadcast->attachment_path)
        );
    }

    /**
     * @return array<int>
     */
    protected function resolveRecipientIds(string $scope): array
    {
        $query = User::where('role', 'member');

        if ($scope === Broadcast::SCOPE_ACTIVE) {
            $query->where('status', 'active');
        }

        return $query->pluck('id')->all();
    }
}
