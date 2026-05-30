{{--
    Packages repeater (compare_at + tiered pricing).

    Required vars:
      - $existingPackages: array of associative arrays with keys
        id?, label, duration_type, price, compare_at_price?, is_active.
      - $hasPackages: bool — initial state of the toggle.
      - $productType: 'digital' | 'software' | 'free' — used to decide initial visibility.
--}}

<div id="packages-section" style="{{ $productType === 'free' ? 'display:none;' : '' }}">
    <div class="dk-card" style="padding:16px; border-left:4px solid #6366f1;">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" id="packages_enabled" {{ $hasPackages ? 'checked' : '' }} class="dk-checkbox">
            <span class="text-sm font-semibold dk-heading">Aktifkan paket harga per durasi lisensi</span>
        </label>
        <p class="text-xs dk-text-muted mt-2">
            Kalau diaktifkan, admin bisa input beberapa paket (mis. 1 Bulan, 6 Bulan, 1 Tahun) dengan harga masing-masing.
            Member akan memilih paket di landing page. Field "Harga Jual" + "Masa Berlaku Lisensi" di atas akan diabaikan kalau paket aktif.
        </p>

        <div id="packages-list" class="mt-4 space-y-3" style="{{ $hasPackages ? '' : 'display:none;' }}">
            @forelse ($existingPackages as $idx => $pkg)
                <div class="dk-card package-row" data-index="{{ $idx }}" style="padding:12px; background:#0f1828;">
                    @if (! empty($pkg['id']))
                        <input type="hidden" name="packages[{{ $idx }}][id]" value="{{ $pkg['id'] }}">
                    @endif
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs uppercase tracking-wide font-semibold" style="color:#a5b4fc">Paket #{{ $idx + 1 }}</p>
                        <button type="button" class="text-xs" style="color:#ef4444" onclick="removePackageRow(this)">Hapus</button>
                    </div>
                    <div class="gap-3" style="display:grid;grid-template-columns:repeat(2,1fr);">
                        <div>
                            <label class="dk-label" style="font-size:12px">Label (opsional)</label>
                            <input type="text" name="packages[{{ $idx }}][label]" value="{{ $pkg['label'] ?? '' }}" placeholder="mis. Paket 1 Tahun" class="w-full dk-input">
                        </div>
                        <div>
                            <label class="dk-label" style="font-size:12px">Durasi Lisensi</label>
                            <select name="packages[{{ $idx }}][duration_type]" class="w-full dk-input">
                                <option value="1_month" {{ ($pkg['duration_type'] ?? '') === '1_month' ? 'selected' : '' }}>1 Bulan</option>
                                <option value="6_months" {{ ($pkg['duration_type'] ?? '') === '6_months' ? 'selected' : '' }}>6 Bulan</option>
                                <option value="1_year" {{ ($pkg['duration_type'] ?? '') === '1_year' ? 'selected' : '' }}>1 Tahun</option>
                                <option value="lifetime" {{ ($pkg['duration_type'] ?? '') === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                            </select>
                        </div>
                        <div>
                            <label class="dk-label" style="font-size:12px">Harga Jual (Rp)</label>
                            <input type="number" name="packages[{{ $idx }}][price]" value="{{ $pkg['price'] ?? '' }}" step="1" min="0" class="w-full dk-input" required>
                        </div>
                        <div>
                            <label class="dk-label" style="font-size:12px">Harga Coret (Rp) <span class="dk-text-muted font-normal">— opsional</span></label>
                            <input type="number" name="packages[{{ $idx }}][compare_at_price]" value="{{ $pkg['compare_at_price'] ?? '' }}" step="1" min="0" class="w-full dk-input">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 mt-3 cursor-pointer">
                        <input type="checkbox" name="packages[{{ $idx }}][is_active]" value="1" {{ ($pkg['is_active'] ?? true) ? 'checked' : '' }} class="dk-checkbox">
                        <span class="text-xs dk-text-muted">Aktif (tampil ke member)</span>
                    </label>
                </div>
            @empty
                {{-- Empty: button below akan add row pertama --}}
            @endforelse
        </div>

        <button type="button" id="add-package-btn" class="dk-btn dk-btn-outline mt-3" style="{{ $hasPackages ? '' : 'display:none;' }}" onclick="addPackageRow()">+ Tambah Paket</button>
        <button type="button" id="add-first-package-btn" class="dk-btn dk-btn-outline mt-3" style="{{ $hasPackages ? 'display:none;' : '' }}" onclick="enablePackagesAndAddRow()">+ Tambah Paket</button>
    </div>
</div>

<script>
    (function () {
        var packagesList = document.getElementById('packages-list');
        var addBtn = document.getElementById('add-package-btn');
        var firstBtn = document.getElementById('add-first-package-btn');
        var toggle = document.getElementById('packages_enabled');

        window.removePackageRow = function (btn) {
            var row = btn.closest('.package-row');
            if (row) row.remove();
            if (packagesList.querySelectorAll('.package-row').length === 0) {
                if (toggle) {
                    toggle.checked = false;
                    toggle.dispatchEvent(new Event('change'));
                }
                packagesList.style.display = 'none';
                addBtn.style.display = 'none';
                firstBtn.style.display = '';
            }
        };

        function nextIndex() {
            var rows = packagesList.querySelectorAll('.package-row');
            return rows.length;
        }

        function buildRow(idx) {
            var html = ''
                + '<div class="dk-card package-row" data-index="' + idx + '" style="padding:12px; background:#0f1828;">'
                + '  <div class="flex items-center justify-between mb-2">'
                + '    <p class="text-xs uppercase tracking-wide font-semibold" style="color:#a5b4fc">Paket #' + (idx + 1) + '</p>'
                + '    <button type="button" class="text-xs" style="color:#ef4444" onclick="removePackageRow(this)">Hapus</button>'
                + '  </div>'
                + '  <div class="gap-3" style="display:grid;grid-template-columns:repeat(2,1fr);">'
                + '    <div><label class="dk-label" style="font-size:12px">Label (opsional)</label>'
                + '      <input type="text" name="packages[' + idx + '][label]" placeholder="mis. Paket 1 Tahun" class="w-full dk-input"></div>'
                + '    <div><label class="dk-label" style="font-size:12px">Durasi Lisensi</label>'
                + '      <select name="packages[' + idx + '][duration_type]" class="w-full dk-input">'
                + '        <option value="1_month">1 Bulan</option>'
                + '        <option value="6_months">6 Bulan</option>'
                + '        <option value="1_year">1 Tahun</option>'
                + '        <option value="lifetime">Lifetime</option>'
                + '      </select></div>'
                + '    <div><label class="dk-label" style="font-size:12px">Harga Jual (Rp)</label>'
                + '      <input type="number" name="packages[' + idx + '][price]" step="1" min="0" class="w-full dk-input" required></div>'
                + '    <div><label class="dk-label" style="font-size:12px">Harga Coret (Rp) <span class="dk-text-muted font-normal">— opsional</span></label>'
                + '      <input type="number" name="packages[' + idx + '][compare_at_price]" step="1" min="0" class="w-full dk-input"></div>'
                + '  </div>'
                + '  <label class="flex items-center gap-2 mt-3 cursor-pointer">'
                + '    <input type="checkbox" name="packages[' + idx + '][is_active]" value="1" checked class="dk-checkbox">'
                + '    <span class="text-xs dk-text-muted">Aktif (tampil ke member)</span>'
                + '  </label>'
                + '</div>';
            var temp = document.createElement('div');
            temp.innerHTML = html;
            return temp.firstChild;
        }

        window.addPackageRow = function () {
            var idx = nextIndex();
            packagesList.appendChild(buildRow(idx));
        };

        window.enablePackagesAndAddRow = function () {
            if (toggle) {
                toggle.checked = true;
                toggle.dispatchEvent(new Event('change'));
            }
            packagesList.style.display = '';
            addBtn.style.display = '';
            firstBtn.style.display = 'none';
            addPackageRow();
        };

        if (toggle) {
            toggle.addEventListener('change', function () {
                if (this.checked) {
                    packagesList.style.display = '';
                    addBtn.style.display = '';
                    firstBtn.style.display = 'none';
                    if (packagesList.querySelectorAll('.package-row').length === 0) {
                        addPackageRow();
                    }
                } else {
                    packagesList.style.display = 'none';
                    addBtn.style.display = 'none';
                    firstBtn.style.display = '';
                    packagesList.innerHTML = '';
                }
            });
        }
    })();
</script>
