<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoTemplate;
use App\Models\PromoTemplateMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PromoTemplateController extends Controller
{
    /**
     * Limit upload per file. Gambar dan video punya limit berbeda.
     */
    public const MAX_IMAGE_KB = 8 * 1024;       // 8 MB per gambar

    public const MAX_VIDEO_KB = 50 * 1024;      // 50 MB per video

    public const MAX_MEDIA_PER_TEMPLATE = 8;    // total file per template

    public const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public const VIDEO_MIMES = ['mp4', 'webm', 'mov'];

    public function index(Request $request): View
    {
        $query = PromoTemplate::query()->with(['product:id,title,slug', 'creator:id,name,email'])->withCount('media');

        if ($category = $request->string('category')->toString()) {
            if (array_key_exists($category, PromoTemplate::CATEGORIES)) {
                $query->where('category', $category);
            }
        }

        if ($status = $request->string('status')->toString()) {
            if (array_key_exists($status, PromoTemplate::STATUSES)) {
                $query->where('approval_status', $status);
            }
        }

        $source = $request->string('source')->toString();
        if ($source === 'member') {
            $query->whereNotNull('created_by_user_id');
        } elseif ($source === 'admin') {
            $query->whereNull('created_by_user_id');
        }

        $templates = $query->orderByRaw("CASE WHEN approval_status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('category')
            ->orderBy('order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.promo-templates.index', [
            'templates' => $templates,
            'category' => $category ?? '',
            'status' => $status ?? '',
            'source' => $source ?? '',
            'counts' => [
                'all' => PromoTemplate::count(),
                'member' => PromoTemplate::where('category', PromoTemplate::CATEGORY_MEMBER)->count(),
                'product' => PromoTemplate::where('category', PromoTemplate::CATEGORY_PRODUCT)->count(),
                'pending' => PromoTemplate::where('approval_status', PromoTemplate::STATUS_PENDING)->count(),
                'member_submitted' => PromoTemplate::whereNotNull('created_by_user_id')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.promo-templates.form', [
            'template' => new PromoTemplate(['category' => PromoTemplate::CATEGORY_MEMBER, 'is_active' => true, 'order' => 0]),
            'products' => Product::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $template = PromoTemplate::create($data);

        $this->saveUploadedMedia($request, $template);

        return redirect()->route('admin.promo-templates.index')
            ->with('success', 'Template promosi berhasil dibuat.');
    }

    public function edit(PromoTemplate $promoTemplate): View
    {
        $promoTemplate->load('media');

        return view('admin.promo-templates.form', [
            'template' => $promoTemplate,
            'products' => Product::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, PromoTemplate $promoTemplate): RedirectResponse
    {
        $data = $this->validateData($request);
        $promoTemplate->update($data);

        $this->saveUploadedMedia($request, $promoTemplate);

        return redirect()->route('admin.promo-templates.index')
            ->with('success', 'Template promosi berhasil diperbarui.');
    }

    public function destroy(PromoTemplate $promoTemplate): RedirectResponse
    {
        $promoTemplate->delete();

        return redirect()->route('admin.promo-templates.index')
            ->with('success', 'Template promosi berhasil dihapus.');
    }

    public function destroyMedia(PromoTemplate $promoTemplate, PromoTemplateMedia $media): RedirectResponse
    {
        if ($media->promo_template_id !== $promoTemplate->id) {
            abort(404);
        }

        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return redirect()->route('admin.promo-templates.edit', $promoTemplate)
            ->with('success', 'File media dihapus.');
    }

    public function approve(Request $request, PromoTemplate $promoTemplate): RedirectResponse
    {
        $promoTemplate->update([
            'approval_status' => PromoTemplate::STATUS_APPROVED,
            'rejection_reason' => null,
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Template disetujui dan akan tampil di halaman promo public.');
    }

    public function reject(Request $request, PromoTemplate $promoTemplate): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi (member akan melihat ini).',
        ]);

        $promoTemplate->update([
            'approval_status' => PromoTemplate::STATUS_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by_user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Template ditolak. Member akan melihat alasan penolakan.');
    }

    private function validateData(Request $request): array
    {
        $imageRule = 'mimes:'.implode(',', self::IMAGE_MIMES).'|max:'.self::MAX_IMAGE_KB;
        $videoRule = 'mimes:'.implode(',', self::VIDEO_MIMES).'|max:'.self::MAX_VIDEO_KB;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'in:'.implode(',', array_keys(PromoTemplate::CATEGORIES))],
            'product_id' => ['nullable', 'exists:products,id'],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'images' => ['nullable', 'array', 'max:'.self::MAX_MEDIA_PER_TEMPLATE],
            'images.*' => ['file', $imageRule],
            'videos' => ['nullable', 'array', 'max:'.self::MAX_MEDIA_PER_TEMPLATE],
            'videos.*' => ['file', $videoRule],
        ], [
            'title.required' => 'Judul template wajib diisi.',
            'category.required' => 'Pilih kategori (Promo Member atau Promo Produk).',
            'category.in' => 'Kategori tidak valid.',
            'body.required' => 'Isi template wajib diisi.',
            'body.max' => 'Isi template terlalu panjang (maks 5000 karakter).',
            'product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            'images.*.mimes' => 'Gambar harus berformat: '.implode(', ', self::IMAGE_MIMES).'.',
            'images.*.max' => 'Ukuran gambar terlalu besar (maks '.(self::MAX_IMAGE_KB / 1024).' MB).',
            'videos.*.mimes' => 'Video harus berformat: '.implode(', ', self::VIDEO_MIMES).'.',
            'videos.*.max' => 'Ukuran video terlalu besar (maks '.(self::MAX_VIDEO_KB / 1024).' MB).',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['order'] = (int) ($data['order'] ?? 0);
        if ($data['category'] !== PromoTemplate::CATEGORY_PRODUCT) {
            $data['product_id'] = null;
        }

        // Hanya field model — file ditangani terpisah.
        return collect($data)->only(['title', 'category', 'product_id', 'body', 'is_active', 'order'])->all();
    }

    private function saveUploadedMedia(Request $request, PromoTemplate $template): void
    {
        $existing = $template->media()->count();
        $allowedSlots = max(0, self::MAX_MEDIA_PER_TEMPLATE - $existing);
        if ($allowedSlots === 0) {
            return;
        }

        $images = collect($request->file('images', []))->filter()->take($allowedSlots);
        $remaining = $allowedSlots - $images->count();
        $videos = collect($request->file('videos', []))->filter()->take(max(0, $remaining));

        $nextOrder = (int) $template->media()->max('sort_order') + 1;

        foreach ($images as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $this->storeMediaFile($template, $file, PromoTemplateMedia::TYPE_IMAGE, $nextOrder++);
        }

        foreach ($videos as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $this->storeMediaFile($template, $file, PromoTemplateMedia::TYPE_VIDEO, $nextOrder++);
        }
    }

    private function storeMediaFile(PromoTemplate $template, UploadedFile $file, string $type, int $sortOrder): void
    {
        $dir = 'promo-template-media/'.$template->id;
        $path = $file->store($dir, 'public');

        if (! $path) {
            return;
        }

        $template->media()->create([
            'type' => $type,
            'path' => $path,
            'original_name' => mb_substr((string) $file->getClientOriginalName(), 0, 200),
            'mime' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'sort_order' => $sortOrder,
        ]);
    }
}
