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
        // Auto-check eligibility when anggota selected (only if dropdown exists)
        if ($('#anggota_id').is('select')) {
            $('#anggota_id').on('change', function() {
                // Just reset to default without AJAX call
                $('#jenis_pengajuan').val('baru');
                $('#jenis_pengajuan_display').val('Pinjaman Baru');
                $('#jenis_pengajuan_info').html('*) Jenis pengajuan akan ditentukan sistem saat menyimpan data');
            });
        }

        // Real-time calculation
        $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', calculateLoan);

        // Initial calculation
        setTimeout(calculateLoan, 500);

        // Character counter for tujuan pinjaman
        $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

        // Form validation - Stock validation removed as per koperasi system preferences
        // Stock information is for display only, no blocking validation
        $('#pengajuan-form').on('submit', function(e) {
            // No stock validation - allow all submissions regardless of stock availability
            // This follows koperasi system preference: "auto-approve loan applications without stock validation"
        });
    }

    // === EDIT PAGE FUNCTIONALITY ===
    function initializeEditPage() {
        // Auto-check eligibility when anggota selected (for edit page, only if dropdown exists)
        if ($('#anggota_id').is('select')) {
            $('#anggota_id').on('change', function() {
                // Just reset to default without AJAX call
                $('#jenis_pengajuan').val('baru');
                $('#jenis_pengajuan_display').val('Pinjaman Baru');
                $('#jenis_pengajuan_info').html('*) Jenis pengajuan akan ditentukan sistem saat menyimpan data');
            });
        }

        // Real-time calculation for edit page
        $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', calculateLoanEdit);

        // Initial calculation
        setTimeout(calculateLoanEdit, 500);

        // Character counter for tujuan pinjaman
        $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

        // Initialize character counter
        updateCharacterCounter();

        // Form validation for edit - All validations removed as per koperasi system preferences
        $('#pengajuan-form').on('submit', function(e) {
            // No validation - auto-approve all loan applications
            // This follows koperasi system preference: "auto-approve loan applications without stock validation"
            // Range validation also removed for maximum flexibility
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
            validateStock(jumlahPaket, stockAvailable); // Now only shows info, never blocks
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

            // Stock display removed - no stock information shown
            if (document.getElementById('display-stock')) {
                document.getElementById('display-stock').innerHTML = '';
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
        // Stock display removed - no stock information shown
        if ($('#stock-display').length) {
            $('#stock-display').html('');
        }
    }

    function validateStock(jumlahPaket, stockAvailable) {
        // Stock validation disabled - auto-approve without stock validation
        // This follows koperasi system preference: "auto-approve loan applications without stock validation"
        const submitButton = $('button[type="submit"]');
        const stockInfo = $('#stock-info');

        // Hide stock info completely - no stock information displayed
        stockInfo.text('')
                 .removeClass('text-danger text-success text-info');

        // Always enable submit button regardless of stock
        submitButton.prop('disabled', false);
    }

    function resetCalculationDisplay() {
        $('#display-jumlah-pinjaman').text('Rp 0');
        $('#display-bunga').text('0%');
        $('#display-cicilan').text('Rp 0');
        $('#display-total').text('Rp 0');

        if ($('#stock-display').length) {
            $('#stock-display').html('');
        }

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

    // === ANGGOTA ELIGIBILITY CHECK ===
    // This function is no longer used - eligibility check now happens during form submission
    function checkAnggotaEligibility() {
        const anggotaId = $('#anggota_id').val();

        if (!anggotaId) {
            // Reset to default
            $('#jenis_pengajuan').val('baru');
            $('#jenis_pengajuan_display').val('Pinjaman Baru');
            $('#jenis_pengajuan_info').html('*) Jenis pengajuan akan ditentukan sistem saat menyimpan data');
            return;
        }

        // Set default without AJAX call
        $('#jenis_pengajuan').val('baru');
        $('#jenis_pengajuan_display').val('Pinjaman Baru');
        $('#jenis_pengajuan_info').html('*) Jenis pengajuan akan ditentukan sistem saat menyimpan data');
    }

    console.log('PengajuanPinjaman JavaScript initialized for:',
                isListPage ? 'List' : isAddPage ? 'Add' : isEditPage ? 'Edit' : isShowPage ? 'Show' : 'Unknown');
});
</script>
