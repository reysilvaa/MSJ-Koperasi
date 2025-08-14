<script>
$(document).ready(function() {
    // Get current page context
    const currentUrl = window.location.pathname;
    const isListPage = (currentUrl.includes('/list') || currentUrl.endsWith('pengajuanPinjaman')) && !currentUrl.includes('approval');
    const isAddPage = currentUrl.includes('/add') && !currentUrl.includes('approval');
    const isEditPage = currentUrl.includes('/edit') && !currentUrl.includes('approval');
    const isShowPage = currentUrl.includes('/show') && !currentUrl.includes('approval');

    // Initialize page-specific functionality
    if (isListPage) {
        initializeListPage();
    } else if (isAddPage) {
        initializeAddPage();
    } else if (isEditPage) {
        initializeEditPage();
    } else if (isShowPage) {
        initializeShowPage();
    }

    // === LIST PAGE FUNCTIONALITY ===
    function initializeListPage() {
        // Initialize DataTable if exists
        if ($('#pengajuan-table').length > 0) {
            $('#pengajuan-table').DataTable({
                "language": {
                    "search": "Cari :",
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "zeroRecords": "Tidak ada pengajuan",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengajuan",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total pengajuan)"
                },
                "pageLength": 10,
                "responsive": true,
                "order": [[5, "desc"]], // Sort by tanggal
                "columnDefs": [
                    { "orderable": false, "targets": -1 } // Disable sorting on Action column
                ]
            });
        }
    }

    // === ADD PAGE FUNCTIONALITY ===
    function initializeAddPage() {
        // Real-time calculation
        $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', calculateLoan);

        // Initial calculation
        setTimeout(calculateLoan, 500);

        // Character counter for tujuan pinjaman
        $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

        // Form validation
        $('#pengajuan-form').on('submit', function(e) {
            const stockAvailable = parseInt($('#paket_pinjaman_id').find(':selected').data('stock')) || 0;
            const jumlahPaket = parseInt($('#jumlah_paket_dipilih').val()) || 1;

            if (jumlahPaket > stockAvailable) {
                e.preventDefault();
                Swal.fire({
                    title: 'Stock Tidak Mencukupi!',
                    text: `Stock tersedia: ${stockAvailable} paket, Anda meminta: ${jumlahPaket} paket`,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
                return false;
            }
        });
    }

    // === EDIT PAGE FUNCTIONALITY ===
    function initializeEditPage() {
        // Real-time calculation for edit page
        $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', calculateLoanEdit);

        // Initial calculation
        setTimeout(calculateLoanEdit, 500);

        // Character counter for tujuan pinjaman
        $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

        // Initialize character counter
        updateCharacterCounter();

        // Form validation for edit
        $('#pengajuan-form').on('submit', function(e) {
            const stockAvailable = parseInt($('#paket_pinjaman_id').find(':selected').data('stock')) || 0;
            const jumlahPaket = parseInt($('#jumlah_paket_dipilih').val()) || 1;

            if (jumlahPaket > stockAvailable) {
                e.preventDefault();
                Swal.fire({
                    title: 'Stock Tidak Mencukupi!',
                    text: `Stock tersedia: ${stockAvailable} paket, Anda meminta: ${jumlahPaket} paket`,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
                return false;
            }

            // Additional validation
            if (jumlahPaket < 1 || jumlahPaket > 40) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validasi Error',
                    text: 'Jumlah paket harus antara 1-40 paket!',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
                return false;
            }
        });
    }

    // === SHOW PAGE FUNCTIONALITY ===
    function initializeShowPage() {
        // No specific functionality needed for show page
        console.log('Show page initialized');
    }

    // === CALCULATION FUNCTIONS ===
    function calculateLoan() {
        const paketSelect = $('#paket_pinjaman_id');
        const jumlahPaket = parseInt($('#jumlah_paket_dipilih').val()) || 1;
        const tenorSelect = $('#tenor_pinjaman');

        if (paketSelect.val() && tenorSelect.val()) {
            const bunga = parseFloat(paketSelect.find(':selected').data('bunga')) || 0;
            const tenorBulan = parseInt(tenorSelect.find(':selected').data('bulan')) || 1;
            const stockAvailable = parseInt(paketSelect.find(':selected').data('stock')) || 0;
            const stockLimit = parseInt(paketSelect.find(':selected').data('stock-limit')) || 0;
            const stockTerpakai = parseInt(paketSelect.find(':selected').data('stock-terpakai')) || 0;

            // Business logic calculation - Bunga Flat
            const nilaiPerPaket = 500000;
            const jumlahPinjaman = jumlahPaket * nilaiPerPaket;
            const cicilanPokok = jumlahPinjaman / tenorBulan;
            const bungaFlat = jumlahPinjaman * (bunga / 100);
            const cicilanPerBulan = cicilanPokok + bungaFlat;
            const totalPembayaran = cicilanPerBulan * tenorBulan;

            // Update display
            updateCalculationDisplay(jumlahPinjaman, bunga, cicilanPerBulan, totalPembayaran);
            updateStockDisplay(stockAvailable, stockLimit, stockTerpakai, jumlahPaket);
            validateStock(jumlahPaket, stockAvailable);
        } else {
            resetCalculationDisplay();
        }
    }

    function calculateLoanEdit() {
        const paketSelect = document.getElementById('paket_pinjaman_id');
        const jumlahPaket = parseInt(document.getElementById('jumlah_paket_dipilih').value) || 1;
        const tenorSelect = document.getElementById('tenor_pinjaman');

        if (paketSelect && paketSelect.value && tenorSelect && tenorSelect.value) {
            const selectedPaket = paketSelect.options[paketSelect.selectedIndex];
            const selectedTenor = tenorSelect.options[tenorSelect.selectedIndex];
            const bunga = parseFloat(selectedPaket.dataset.bunga) || 0;
            const stock = parseInt(selectedPaket.dataset.stock) || 0;
            const tenor = parseInt(selectedTenor.dataset.bulan) || 1;

            // Business logic calculation - Bunga Flat
            const nilaiPerPaket = 500000;
            const jumlahPinjaman = jumlahPaket * nilaiPerPaket;
            const cicilanPokok = jumlahPinjaman / tenor;
            const bungaFlat = jumlahPinjaman * (bunga / 100);
            const cicilanPerBulan = cicilanPokok + bungaFlat;
            const totalPembayaran = cicilanPerBulan * tenor;

            // Update display elements
            updateElement('display-jumlah-pinjaman', formatCurrency(jumlahPinjaman));
            updateElement('display-bunga', bunga + '%');
            updateElement('display-cicilan', formatCurrency(cicilanPerBulan));
            updateElement('display-total', formatCurrency(totalPembayaran));

            // Update stock display
            if (document.getElementById('display-stock')) {
                const stockText = jumlahPaket > stock ?
                    `<span class="text-danger">${stock} paket (Tidak mencukupi!)</span>` :
                    `${stock} paket`;
                document.getElementById('display-stock').innerHTML = stockText;
            }
        }
    }

    function updateCalculationDisplay(jumlahPinjaman, bunga, cicilanPerBulan, totalPembayaran) {
        $('#display-jumlah-pinjaman').text(formatCurrency(jumlahPinjaman));
        $('#display-bunga').text(bunga + '%');
        $('#display-cicilan').text(formatCurrency(Math.round(cicilanPerBulan)));
        $('#display-total').text(formatCurrency(Math.round(totalPembayaran)));
    }

    function updateStockDisplay(stockAvailable, stockLimit, stockTerpakai, jumlahPaket) {
        if ($('#stock-display').length) {
            const stockTersisa = stockAvailable - jumlahPaket;
            $('#stock-display').html(`
                <div class="d-flex justify-content-between">
                    <span class="text-sm">Stock Saat Ini:</span>
                    <span class="text-sm font-weight-bold">${stockAvailable} paket</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-sm">Akan Dipinjam:</span>
                    <span class="text-sm text-warning">${jumlahPaket} paket</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-sm">Stock Tersisa:</span>
                    <span class="text-sm font-weight-bold ${stockTersisa >= 0 ? 'text-success' : 'text-danger'}">
                        ${Math.max(0, stockTersisa)} paket
                    </span>
                </div>
            `);
        }
    }

    function validateStock(jumlahPaket, stockAvailable) {
        const submitButton = $('button[type="submit"]');
        const stockInfo = $('#stock-info');

        if (jumlahPaket > stockAvailable) {
            stockInfo.text(`⚠️ Stock tidak mencukupi! Tersedia: ${stockAvailable} paket`)
                     .removeClass('text-info text-success')
                     .addClass('text-danger');
            submitButton.prop('disabled', true);
        } else {
            stockInfo.text(`✅ Stock mencukupi (${stockAvailable} paket tersedia)`)
                     .removeClass('text-danger text-info')
                     .addClass('text-success');
            submitButton.prop('disabled', false);
        }
    }

    function resetCalculationDisplay() {
        $('#display-jumlah-pinjaman').text('Rp 0');
        $('#display-bunga').text('0%');
        $('#display-cicilan').text('Rp 0');
        $('#display-total').text('Rp 0');

        if ($('#stock-display').length) {
            $('#stock-display').html('<p class="text-sm text-secondary">Pilih paket untuk melihat stock tersedia</p>');
        }

        $('#stock-info').text('').removeClass('text-success text-danger text-info');
    }

    // === UTILITY FUNCTIONS ===
    function updateElement(id, content) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = content;
        }
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    function updateCharacterCounter() {
        const textarea = $('textarea[name="tujuan_pinjaman"]');
        const maxLength = 500;
        const currentLength = textarea.val().length;
        const remaining = maxLength - currentLength;

        let counterClass = 'text-muted';
        let counterText = `${currentLength}/${maxLength} karakter`;

        if (remaining < 50) {
            counterClass = 'text-warning';
            counterText += ` (sisa: ${remaining})`;
        }
        if (remaining <= 0) {
            counterClass = 'text-danger';
            counterText += ' (melebihi batas!)';
        }

        const counterHtml = `<small class="${counterClass}">${counterText}</small>`;

        // Remove existing counter and add new one
        textarea.next('small').remove();
        textarea.after(counterHtml);
    }

    // === DELETE CONFIRMATION ===
    window.confirmDelete = function(id) {
        Swal.fire({
            title: 'Hapus Pengajuan?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url($url_menu ?? '') }}/${id}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    console.log('PengajuanPinjaman JavaScript initialized for:',
                isListPage ? 'List' : isAddPage ? 'Add' : isEditPage ? 'Edit' : isShowPage ? 'Show' : 'Unknown');
});
</script>
