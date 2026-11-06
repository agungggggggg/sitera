(() => {
    'use strict';
    const descendants = {
        provinsi: ['kabupaten', 'kecamatan', 'desa', 'kelompok', 'kelompok_tani', 'nama_di'],
        kabupaten: ['kecamatan', 'desa', 'kelompok', 'kelompok_tani', 'nama_di'],
        kecamatan: ['desa', 'kelompok', 'kelompok_tani', 'nama_di'],
        desa: ['kelompok', 'kelompok_tani', 'nama_di'],
    };
    document.querySelectorAll('form[data-cascade-filters]').forEach(form => {
        form.addEventListener('change', event => {
            const parent = event.target;
            if (!(parent instanceof HTMLSelectElement) || !descendants[parent.name]) return;
            descendants[parent.name].forEach(name => {
                const child = form.elements.namedItem(name);
                if (child instanceof HTMLSelectElement) child.value = '';
            });
            form.requestSubmit();
        });
    });
})();
