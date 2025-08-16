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

        // Handle package selection for stock information display
        $('#paket_pinjaman_id').on('change', function() {
            if (!$(this).val()) {
                // Reset stock display when no package selected
                if ($('#stock-information-panel').length) {
                    $('#stock-information-panel').hide();
                    $('#stock-no-selection').show();
                }
            }
        });

        // Initial calculation
        setTimeout(calculateLoan, 500);

        // Character counter for tujuan pinjaman
        $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

        // Form validation - no blocking validation as per system preferences
        $('#pengajuan-form').on('submit', function(e) {
            // Allow all submissions - auto-approve system
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
        $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', calculateLoan);

        // Initial calculation
        setTimeout(calculateLoan, 500);

        // Character counter for tujuan pinjaman
        $('textarea[name="tujuan_pinjaman"]').on('input', updateCharacterCounter);

        // Initialize character counter
        updateCharacterCounter();

        // Form validation for edit - no blocking validation
        $('#pengajuan-form').on('submit', function(e) {
            // Allow all submissions - auto-approve system
        });
    }

    // === SHOW PAGE FUNCTIONALITY ===
    function initializeShowPage() {
        // No specific functionality needed for show page
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



    function updateCalculationDisplay(jumlahPinjaman, bunga, cicilanPerBulan, totalPembayaran) {
        $('#display-jumlah-pinjaman').text(formatCurrency(jumlahPinjaman));
        $('#display-bunga').text(bunga + '%');
        $('#display-cicilan').text(formatCurrency(Math.round(cicilanPerBulan)));
        $('#display-total').text(formatCurrency(Math.round(totalPembayaran)));
    }

    function updateStockDisplay(stockAvailable, stockLimit, stockTerpakai, jumlahPaket) {
        // Check if stock information panel exists (only for non-member roles)
        if ($('#stock-information-panel').length) {
            // Show the stock information panel
            $('#stock-information-panel').show();
            $('#stock-no-selection').hide();

            // Update stock values
            $('#display-stock-available').text(stockAvailable + ' paket');
            $('#display-stock-limit').text(stockLimit + ' paket');
            $('#display-stock-used').text(stockTerpakai + ' paket');
            $('#display-requested-amount').text(jumlahPaket + ' paket');

            // Calculate stock usage percentage
            const usagePercentage = stockLimit > 0 ? (stockTerpakai / stockLimit) * 100 : 0;

            // Update progress bar
            $('#stock-progress-bar').css('width', usagePercentage + '%');

            // Simplified binary stock status determination - sufficient vs insufficient
            let statusBadge = '';
            let progressBarClass = 'bg-success';
            let statusText = '';
            let panelClass = 'stock-status-good';

            // Simplified binary stock status - only two states: sufficient or insufficient
            if (stockAvailable <= 0 || jumlahPaket > stockAvailable) {
                // Insufficient stock - red badge
                statusBadge = '<span class="badge bg-danger">Stok Tidak Cukup</span>';
                progressBarClass = 'bg-danger';
                panelClass = 'stock-status-danger';

                if (stockAvailable <= 0) {
                    statusText = `Stok tidak tersedia untuk memenuhi permintaan ${jumlahPaket} paket`;
                } else {
                    statusText = `Permintaan ${jumlahPaket} paket melebihi stok tersedia (${stockAvailable} paket)`;
                }
            } else {
                // Sufficient stock - green badge
                statusBadge = '<span class="badge bg-success">Stok Cukup</span>';
                progressBarClass = 'bg-success';
                statusText = `Stok mencukupi - ${stockAvailable} paket tersedia untuk permintaan ${jumlahPaket} paket`;
                panelClass = 'stock-status-good';
            }

            // Update status badge and progress bar class
            $('#stock-status-badge').html(statusBadge);
            $('#stock-progress-bar').removeClass('bg-success bg-danger').addClass(progressBarClass);
            $('#stock-progress-text').text(statusText);
            $('#stock-usage-percentage').text(`${usagePercentage.toFixed(1)}% terpakai`);

            // Update integrated validation feedback within the stock card
            updateIntegratedValidationFeedback(jumlahPaket, stockAvailable, panelClass);

            // Apply panel styling based on stock status
            $('#stock-information-panel .info-item').removeClass('stock-status-good stock-status-danger').addClass(panelClass);

            // Clear any external stock info text since everything is now integrated in the card
            clearExternalStockInfo();
        }
    }

    function validateStock(jumlahPaket, stockAvailable) {
        // Always enable submit button - no blocking validation
        const submitButton = $('button[type="submit"]');
        submitButton.prop('disabled', false);

        // Clear any external stock info
        clearExternalStockInfo();
    }

    function updateIntegratedValidationFeedback(jumlahPaket, stockAvailable, panelClass) {
        const feedbackElement = $('#stock-validation-feedback');
        const messageElement = $('#stock-validation-message');

        if (!feedbackElement.length) return; // Exit if feedback element doesn't exist

        let message = '';
        let alertClass = 'alert-info';
        let iconClass = 'fas fa-info-circle';

        // Simplified binary validation message
        if (stockAvailable <= 0 || jumlahPaket > stockAvailable) {
            // Insufficient stock
            if (stockAvailable <= 0) {
                message = `Stok tidak tersedia untuk memenuhi permintaan ${jumlahPaket} paket. Aplikasi tetap dapat diproses sesuai kebijakan sistem.`;
            } else {
                message = `Permintaan ${jumlahPaket} paket melebihi stok tersedia (${stockAvailable} paket). Aplikasi tetap dapat diproses sesuai kebijakan sistem.`;
            }
            alertClass = 'alert-danger';
            iconClass = 'fas fa-exclamation-circle';
        } else {
            // Sufficient stock
            message = `Stok mencukupi untuk memenuhi permintaan ${jumlahPaket} paket dari ${stockAvailable} paket tersedia.`;
            alertClass = 'alert-success';
            iconClass = 'fas fa-check-circle';
        }

        // Update feedback display
        feedbackElement.removeClass('alert-success alert-danger d-none')
                      .addClass(alertClass + ' show');

        messageElement.html(`<i class="${iconClass} me-2"></i>${message}`);
    }

    function clearExternalStockInfo() {
        // Clear any external stock info text elements
        const stockInfo = $('#stock-info');
        if (stockInfo.length) {
            stockInfo.text('').removeClass('text-danger text-success');
        }

        // Clear any other external stock warning elements that might exist
        $('.stock-warning-external').remove();
    }

    function resetCalculationDisplay() {
        $('#display-jumlah-pinjaman').text('Rp 0');
        $('#display-bunga').text('0%');
        $('#display-cicilan').text('Rp 0');
        $('#display-total').text('Rp 0');

        // Reset stock information panel for non-member roles
        if ($('#stock-information-panel').length) {
            $('#stock-information-panel').hide();
            $('#stock-no-selection').show();

            // Reset all stock display values
            $('#display-stock-available').text('-');
            $('#display-stock-limit').text('-');
            $('#display-stock-used').text('-');
            $('#display-requested-amount').text('-');
            $('#stock-status-badge').html('<span class="badge bg-secondary">Pilih paket terlebih dahulu</span>');
            $('#stock-progress-bar').css('width', '0%').removeClass('bg-success bg-danger');
            $('#stock-progress-text').text('Pilih paket untuk melihat informasi stok');
            $('#stock-usage-percentage').text('');

            // Reset integrated validation feedback
            $('#stock-validation-feedback').removeClass('alert-success alert-danger show').addClass('d-none');
            $('#stock-validation-message').text('Informasi validasi stok akan ditampilkan di sini');
        }

        // Clear any external stock info
        clearExternalStockInfo();
    }

    // === UTILITY FUNCTIONS ===

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

    // === DELETE CONFIRMATION - MSJ Framework Pattern ===
    // Note: Delete function is now handled in the view file for list pages
    // This is kept for compatibility with other pages (add, edit, show)
    window.deleteData = function(event, name, msg) {
        event.preventDefault(); // Prevent default form submission
        Swal.fire({
            title: 'Konfirmasi',
            text: `Apakah Anda Yakin ${msg} Data ${name} ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Ya, ${msg}`,
            cancelButtonText: 'Batal',
            confirmButtonColor: '#028284'
        }).then((result) => {
            if (result.isConfirmed) {
                // Find the closest form element and submit it manually
                event.target.closest('form').submit();
            }
        });
    };




});
</script>
