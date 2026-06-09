<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class PromoTemplate extends Model
{
    const CATEGORY_MEMBER = 'member';

    const CATEGORY_PRODUCT = 'product';

    const CATEGORIES = [
        self::CATEGORY_MEMBER => 'Promo Member',
        self::CATEGORY_PRODUCT => 'Promo Produk',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUSES = [
        self::STATUS_PENDING => 'Menunggu Review',
        self::STATUS_APPROVED => 'Disetujui',
        self::STATUS_REJECTED => 'Ditolak',
    ];

    /**
     * Daftar placeholder yang didukung + deskripsi singkatnya. Ditampilkan di
     * form admin sebagai bantuan saat menyusun body template.
     */
    const PLACEHOLDERS_MEMBER = [
        '{nama_member}' => 'Nama member yang share',
        '{kode_referral}' => 'Kode referral member',
        '{link_referral}' => 'URL beranda + ?ref=KODE',
    ];

    const PLACEHOLDERS_PRODUCT = [
        '{nama_member}' => 'Nama member yang share',
        '{kode_referral}' => 'Kode referral member',
        '{link_referral}' => 'URL beranda + ?ref=KODE',
        '{nama_produk}' => 'Judul produk',
        '{harga}' => 'Harga produk diformat (Rp xx)',
        '{harga_coret}' => 'Harga coret kalau ada (Rp xx) / kosong',
        '{link_produk}' => 'URL landing produk + ?ref=KODE',
        '{deskripsi}' => 'Deskripsi produk (di-strip dari HTML)',
    ];

    protected $fillable = [
        'title',
        'category',
        'product_id',
        'created_by_user_id',
        'approval_status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by_user_id',
        'body',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function isMemberSubmitted(): bool
    {
        return $this->created_by_user_id !== null;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->approval_status === self::STATUS_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->approval_status] ?? $this->approval_status;
    }

    public function media(): HasMany
    {
        return $this->hasMany(PromoTemplateMedia::class, 'promo_template_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Hapus semua file media dari storage saat template dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (PromoTemplate $template) {
            foreach ($template->media as $media) {
                if ($media->path) {
                    Storage::disk('public')->delete($media->path);
                }
            }
        });
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function placeholders(): array
    {
        return $this->category === self::CATEGORY_PRODUCT
            ? self::PLACEHOLDERS_PRODUCT
            : self::PLACEHOLDERS_MEMBER;
    }

    /**
     * Render template body dengan data member (dan produk kalau category=product).
     * Selalu kembalikan string siap salin/share.
     */
    public function renderFor(User $member): string
    {
        $code = $member->referral_code ?? '';
        $homeRef = $code ? rtrim(url('/'), '/').'?ref='.$code : url('/');

        $vars = [
            '{nama_member}' => (string) ($member->name ?? ''),
            '{kode_referral}' => $code,
            '{link_referral}' => $homeRef,
        ];

        if ($this->category === self::CATEGORY_PRODUCT && $this->product) {
            $product = $this->product;
            $productUrl = route('product.show', $product->slug);
            if ($code) {
                $productUrl .= '?ref='.$code;
            }
            $vars['{nama_produk}'] = (string) ($product->title ?? '');
            $vars['{harga}'] = self::formatRupiah((float) ($product->price ?? 0));
            $vars['{harga_coret}'] = $product->compare_at_price
                ? self::formatRupiah((float) $product->compare_at_price)
                : '';
            $vars['{link_produk}'] = $productUrl;
            $vars['{deskripsi}'] = self::stripDescription($product->description ?? '');
        }

        return strtr($this->body, $vars);
    }

    private static function formatRupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private static function stripDescription(string $html): string
    {
        $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $html));

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
