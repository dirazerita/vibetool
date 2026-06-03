<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SoftwareRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoftwareRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SoftwareRequest::query()
            ->with(['user:id,name,email,whatsapp_number'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            if (array_key_exists($status, SoftwareRequest::STATUSES)) {
                $query->where('status', $status);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('whatsapp_number', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->paginate(20)->withQueryString();

        $statusCounts = SoftwareRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('admin.software-requests.index', [
            'requests' => $requests,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    public function show(SoftwareRequest $softwareRequest)
    {
        $softwareRequest->load(['user', 'product']);

        $products = Product::query()
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        return view('admin.software-requests.show', [
            'softwareRequest' => $softwareRequest,
            'products' => $products,
        ]);
    }

    public function update(Request $request, SoftwareRequest $softwareRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(SoftwareRequest::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:10000'],
            'admin_response' => ['nullable', 'string', 'max:5000'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $oldResponse = (string) $softwareRequest->admin_response;
        $newResponse = (string) ($data['admin_response'] ?? '');

        $softwareRequest->fill([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
            'admin_response' => $data['admin_response'] ?? null,
            'product_id' => $data['product_id'] ?? null,
        ]);

        if ($newResponse !== '' && $newResponse !== $oldResponse) {
            $softwareRequest->admin_responded_at = now();
            $softwareRequest->user_seen_response_at = null;
        }

        $softwareRequest->save();

        return redirect()
            ->route('admin.software-requests.show', $softwareRequest)
            ->with('success', 'Request berhasil diperbarui.');
    }

    public function attachment(SoftwareRequest $softwareRequest): StreamedResponse
    {
        if (! $softwareRequest->hasAttachment() || ! Storage::disk('local')->exists($softwareRequest->attachment_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $softwareRequest->attachment_path,
            $softwareRequest->attachment_name ?? basename($softwareRequest->attachment_path)
        );
    }

    public function destroy(SoftwareRequest $softwareRequest)
    {
        if ($softwareRequest->hasAttachment()) {
            Storage::disk('local')->delete($softwareRequest->attachment_path);
        }
        $softwareRequest->delete();

        return redirect()
            ->route('admin.software-requests.index')
            ->with('success', 'Request berhasil dihapus.');
    }
}
