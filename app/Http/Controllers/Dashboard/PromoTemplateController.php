<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Admin\PromoTemplateController as AdminPromoTemplateController;
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
     * Pastikan user adalah vendor (member yang bisa upload produk). Member
     * biasa tidak boleh akses CRUD template promo (cukup view di /dashboard/promo).
     */
    private function ensureVendor(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! $user->canUploadProduct()) {
            abort(403, 'Hanya member yang bisa upload produk yang dapat membuat template promo.');
        }
    }

    /**
     * Pastikan template milik user yang sedang login. Member tidak boleh edit/hapus
     * template milik admin atau member lain.
     */
    private function ensureOwner(Request $request, PromoTemplate $template): void
    {
        if ((int) $template->created_by_user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak boleh mengubah template milik orang lain.');
        }
    }

    public function index(Request $request): View
    {
        $this->ensureVendor($request);

        $templates = PromoTemplate::query()
            ->where('created_by_user_id', $request->user()->id)
            ->with('product:id,title,slug')
            ->withCount('media')
            ->orderByDesc('id')
            ->paginate(15);

        return view('dashboard.promo-templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureVendor($request);

        return view('dashboard.promo-templates.form', [
            'template' => new PromoTemplate([
                'category' => PromoTemplate::CATEGORY_PRODUCT,
                'is_active' => true,
                'order' => 0,
                'approval_status' => PromoTemplate::STATUS_PENDING,
            ]),
            'products' => $this->ownProducts($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureVendor($request);

        $data = $this->validateData($request);
        $data['created_by_user_id'] = $request->user()->id;
        $data['approval_status'] = PromoTemplate::STATUS_PENDING;
        $data['rejection_reason'] = null;
        $data['reviewed_at'] = null;
        $data['reviewed_by_user_id'] = null;

        $template = PromoTemplate::create($data);

        $this->saveUploadedMedia($request, $template);

        return redirect()->route('dashboard.promo-templates.index')
            ->with('success', 'Template promo berhasil dikirim. Menunggu review admin sebelum tampil di halaman promo.');
    }

    public function edit(Request $request, PromoTemplate $promoTemplate): View
    {
        $this->ensureVendor($request);
        $this->ensureOwner($request, $promoTemplate);

        $promoTemplate->load('media');

        return view('dashboard.promo-templates.form', [
            'template' => $promoTemplate,
            'products' => $this->ownProducts($request),
        ]);
    }

    public function update(Request $request, PromoTemplate $promoTemplate): RedirectResponse
    {
        $this->ensureVendor($request);
        $this->ensureOwner($request, $promoTemplate);

        $data = $this->validateData($request);

        // Saat member edit, status dikembalikan ke pending (kecuali admin yang
        // approved sebelumnya — masuk lagi ke antrian review).
        if ($promoTemplate->isApproved() || $promoTemplate->isRejected()) {
            $data['approval_status'] = PromoTemplate::STATUS_PENDING;
            $data['rejection_reason'] = null;
            $data['reviewed_at'] = null;
            $data['reviewed_by_user_id'] = null;
        }

        $promoTemplate->update($data);

        $this->saveUploadedMedia($request, $promoTemplate);

        return redirect()->route('dashboard.promo-templates.index')
            ->with('success', 'Template diperbarui. Menunggu review admin.');
    }

    public function destroy(Request $request, PromoTemplate $promoTemplate): RedirectResponse
    {
        $this->ensureVendor($request);
        $this->ensureOwner($request, $promoTemplate);

        $promoTemplate->delete();

        return redirect()->route('dashboard.promo-templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    public function destroyMedia(Request $request, PromoTemplate $promoTemplate, PromoTemplateMedia $media): RedirectResponse
    {
        $this->ensureVendor($request);
        $this->ensureOwner($request, $promoTemplate);

        if ($media->promo_template_id !== $promoTemplate->id) {
            abort(404);
        }

        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return redirect()->route('dashboard.promo-templates.edit', $promoTemplate)
            ->with('success', 'File media dihapus.');
    }

    private function ownProducts(Request $request)
    {
        return Product::where('created_by', $request->user()->id)
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    private function validateData(Request $request): array
    {
        $imageRule = 'mimes:'.implode(',', AdminPromoTemplateController::IMAGE_MIMES).'|max:'.AdminPromoTemplateController::MAX_IMAGE_KB;
        $videoRule = 'mimes:'.implode(',', AdminPromoTemplateController::VIDEO_MIMES).'|max:'.AdminPromoTemplateController::MAX_VIDEO_KB;

        $ownProductIds = $request->user()->createdProducts()->pluck('id')->all();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'product_id' => ['required', 'integer', 'in:'.implode(',', $ownProductIds ?: [0])],
            'body' => ['required', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'images' => ['nullable', 'array', 'max:'.AdminPromoTemplateController::MAX_MEDIA_PER_TEMPLATE],
            'images.*' => ['file', $imageRule],
            'videos' => ['nullable', 'array', 'max:'.AdminPromoTemplateController::MAX_MEDIA_PER_TEMPLATE],
            'videos.*' => ['file', $videoRule],
        ], [
            'title.required' => 'Judul template wajib diisi.',
            'product_id.required' => 'Pilih salah satu produk Anda.',
            'product_id.in' => 'Anda hanya boleh membuat template untuk produk milik sendiri.',
            'body.required' => 'Isi template wajib diisi.',
            'body.max' => 'Isi template terlalu panjang (maks 5000 karakter).',
            'images.*.mimes' => 'Gambar harus berformat: '.implode(', ', AdminPromoTemplateController::IMAGE_MIMES).'.',
            'images.*.max' => 'Ukuran gambar terlalu besar (maks '.(AdminPromoTemplateController::MAX_IMAGE_KB / 1024).' MB).',
            'videos.*.mimes' => 'Video harus berformat: '.implode(', ', AdminPromoTemplateController::VIDEO_MIMES).'.',
            'videos.*.max' => 'Ukuran video terlalu besar (maks '.(AdminPromoTemplateController::MAX_VIDEO_KB / 1024).' MB).',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['order'] = (int) ($data['order'] ?? 0);
        // Member hanya boleh kategori product (untuk produk yang dia upload).
        $data['category'] = PromoTemplate::CATEGORY_PRODUCT;

        return collect($data)->only(['title', 'category', 'product_id', 'body', 'is_active', 'order'])->all();
    }

    private function saveUploadedMedia(Request $request, PromoTemplate $template): void
    {
        $existing = $template->media()->count();
        $allowedSlots = max(0, AdminPromoTemplateController::MAX_MEDIA_PER_TEMPLATE - $existing);
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
