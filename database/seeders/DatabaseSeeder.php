<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin PRODIG',
            'email' => 'admin@prodig.id',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'referral_code' => 'ADMIN001',
            'email_verified_at' => now(),
        ]);

        Product::create([
            'title' => 'E-Book Panduan Digital Marketing',
            'slug' => 'ebook-panduan-digital-marketing',
            'description' => 'Panduan lengkap digital marketing dari nol hingga mahir. Cocok untuk pemula yang ingin memulai bisnis online dan meningkatkan penjualan melalui strategi digital marketing yang terbukti efektif.',
            'price' => 150000,
            'commission_percent' => 30,
            'upline_percent' => 10,
            'file_path' => 'products/ebook-digital-marketing.pdf',
            'is_active' => true,
        ]);

        Product::create([
            'title' => 'Template Website Premium',
            'slug' => 'template-website-premium',
            'description' => 'Koleksi 50+ template website premium siap pakai untuk berbagai kebutuhan bisnis. Sudah responsive dan SEO-friendly. Termasuk template landing page, company profile, dan toko online.',
            'price' => 250000,
            'commission_percent' => 30,
            'upline_percent' => 10,
            'file_path' => 'products/template-website-premium.zip',
            'is_active' => true,
        ]);

        Product::create([
            'title' => 'Video Course Laravel Mastery',
            'slug' => 'video-course-laravel-mastery',
            'description' => 'Kursus video lengkap belajar Laravel dari dasar hingga mahir. Termasuk 100+ video tutorial, source code, dan akses ke grup diskusi eksklusif. Update gratis selamanya.',
            'price' => 500000,
            'commission_percent' => 30,
            'upline_percent' => 10,
            'file_path' => 'products/laravel-mastery-course.zip',
            'is_active' => true,
        ]);
    }
}
