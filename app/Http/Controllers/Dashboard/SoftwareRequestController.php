<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SoftwareRequest;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoftwareRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = SoftwareRequest::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('dashboard.software-requests.index', [
            'requests' => $requests,
        ]);
    }

    public function create()
    {
        return view('dashboard.software-requests.create');
    }

    public function store(Request $request, TelegramService $telegram)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'purpose' => ['required', 'string', 'max:5000'],
            'target_users' => ['required', 'string', 'max:500'],
            'problem_to_solve' => ['required', 'string', 'max:5000'],
            'similar_apps' => ['nullable', 'string', 'max:500'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['string', 'in:'.implode(',', array_keys(SoftwareRequest::PLATFORMS))],
            'key_features' => ['required', 'string', 'max:5000'],
            'budget_range' => ['nullable', 'string', 'in:'.implode(',', array_keys(SoftwareRequest::BUDGETS))],
            'urgency' => ['nullable', 'string', 'in:'.implode(',', array_keys(SoftwareRequest::URGENCIES))],
            'additional_notes' => ['nullable', 'string', 'max:5000'],
            'attachment' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,csv,txt',
            ],
        ], [
            'title.required' => 'Tolong tulis nama aplikasinya.',
            'purpose.required' => 'Cerita ya, aplikasinya untuk apa.',
            'target_users.required' => 'Tolong tulis siapa yang akan pakai.',
            'problem_to_solve.required' => 'Tolong cerita masalah yang ingin diselesaikan.',
            'platforms.required' => 'Pilih minimal satu platform (Android/iOS/Web/dll).',
            'platforms.min' => 'Pilih minimal satu platform.',
            'key_features.required' => 'Tolong tulis fitur penting yang harus ada.',
            'attachment.max' => 'Lampiran maksimal 5 MB.',
            'attachment.mimes' => 'Format lampiran tidak didukung.',
        ]);

        $payload = [
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'purpose' => $data['purpose'],
            'target_users' => $data['target_users'],
            'problem_to_solve' => $data['problem_to_solve'],
            'similar_apps' => $data['similar_apps'] ?? null,
            'platforms' => $data['platforms'],
            'key_features' => $data['key_features'],
            'budget_range' => $data['budget_range'] ?? null,
            'urgency' => $data['urgency'] ?? null,
            'additional_notes' => $data['additional_notes'] ?? null,
            'status' => SoftwareRequest::STATUS_PENDING,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $payload['attachment_path'] = $file->store('software-requests/'.$request->user()->id, 'local');
            $payload['attachment_name'] = mb_substr($file->getClientOriginalName(), 0, 255);
            $payload['attachment_mime'] = $file->getMimeType();
            $payload['attachment_size'] = $file->getSize();
        }

        $softwareRequest = SoftwareRequest::create($payload);

        try {
            $userName = e($request->user()->name);
            $title = e($softwareRequest->title);
            $platforms = e(implode(', ', $softwareRequest->platformLabels()));
            $telegram->sendMessage(
                "🆕 <b>Request Software Baru</b>\n\n".
                "👤 Dari: {$userName}\n".
                "📦 Aplikasi: <b>{$title}</b>\n".
                "💻 Platform: {$platforms}\n\n".
                'Buka panel admin untuk lihat detail.'
            );
        } catch (\Throwable $e) {
            // Notifikasi gagal tidak boleh ganggu submit user.
        }

        return redirect()
            ->route('dashboard.software-requests.show', $softwareRequest)
            ->with('success', 'Request kamu sudah masuk. Tim kami akan review dan kabari kamu di sini.');
    }

    public function show(Request $request, SoftwareRequest $softwareRequest)
    {
        if ($softwareRequest->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($softwareRequest->admin_responded_at && ! $softwareRequest->user_seen_response_at) {
            $softwareRequest->forceFill(['user_seen_response_at' => now()])->save();
        }

        return view('dashboard.software-requests.show', [
            'softwareRequest' => $softwareRequest,
        ]);
    }

    public function attachment(Request $request, SoftwareRequest $softwareRequest): StreamedResponse
    {
        if ($softwareRequest->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $softwareRequest->hasAttachment() || ! Storage::disk('local')->exists($softwareRequest->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $softwareRequest->attachment_path,
            $softwareRequest->attachment_name ?? basename($softwareRequest->attachment_path)
        );
    }
}
