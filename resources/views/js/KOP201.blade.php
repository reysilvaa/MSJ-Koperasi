<script>
$(document).ready(function() {
    // Initialize page functionality
    initializePage();

    function initializePage() {
        const currentUrl = window.location.pathname;

        if (currentUrl.includes('/add') || currentUrl.includes('/edit')) {
            initializeFormPage();
        }
    }

    function initializeFormPage() {
        // Initialize search modals
        initializeSearchModals();

        // Initialize form calculations
        initializeCalculations();

        // Initialize form validation
        initializeFormValidation();

        // Initialize character counter
        initializeCharacterCounter();
    }

    // === SEARCH MODALS ===
    function initializeSearchModals() {
        // Initialize DataTables
        initializeDataTables();

        // Handle modal selections
        handleAnggotaSelection();
        handlePaketSelection();
        handlePeriodeSelection();
    }

    function initializeDataTables() {
        const tableConfigs = {
            pageLength: 10,
            searching: true,
            ordering: true,
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        };

        // Initialize each table if exists
        ['#list_anggota_search', '#list_paket_search', '#list_periode_search'].forEach(selector => {
            if ($(selector).length) {
                $(selector).DataTable(tableConfigs);
            }
        });
    }

    function handleAnggotaSelection() {
        $(document).on('click', '.select-anggota', function(e) {
            e.preventDefault();

            // Get data from button
            const data = getButtonData(this);

            if (!validateSelectionData(data, 'anggota')) {
                return false;
            }

            // Set form values
            setFormValues('anggota', data);

            // Close modal properly
            closeModal('searchModalAnggota', this);

            console.log('Anggota dipilih:', data);
        });
    }

    function handlePaketSelection() {
        $(document).on('click', '.select-paket', function(e) {
            e.preventDefault();

            // Get data from button
            const data = getButtonData(this);

            if (!validateSelectionData(data, 'paket')) {
                return false;
            }

            // Set form values
            setFormValues('paket', data);

            // Set additional data for calculations
            const selectElement = $('#paket_pinjaman_id');
            selectElement.attr({
                'data-bunga': data.bunga || '1.0',
                'data-stock-limit': data.stockLimit || '0',
                'data-stock-terpakai': data.stockTerpakai || '0'
            });

            // Trigger calculation
            $('#paket_pinjaman_id').trigger('change');

            // Close modal properly
            closeModal('searchModalPaket', this);

            console.log('Paket dipilih:', data);
        });
    }

    function handlePeriodeSelection() {
        $(document).on('click', '.select-periode', function(e) {
            e.preventDefault();

            // Get data from button
            const data = getButtonData(this);

            if (!validateSelectionData(data, 'periode')) {
                return false;
            }

            // Set form values
            setFormValues('periode', data);

            // Close modal properly
            closeModal('searchModalPeriode', this);

            console.log('Periode dipilih:', data);
        });
    }

    // === UTILITY FUNCTIONS ===
    function getButtonData(button) {
        const $button = $(button);

        return {
            id: $button.attr('data-id') || $button.data('id'),
            display: $button.attr('data-display') || $button.data('display'),
            bunga: $button.attr('data-bunga') || $button.data('bunga'),
            stockLimit: $button.attr('data-stock-limit') || $button.data('stock-limit'),
            stockTerpakai: $button.attr('data-stock-terpakai') || $button.data('stock-terpakai')
        };
    }

    function validateSelectionData(data, type) {
        if (!data.id || !data.display) {
            console.error(`Data ${type} tidak lengkap:`, data);
            alert(`Data ${type} tidak lengkap. Silakan refresh halaman dan coba lagi.`);
            return false;
        }
        return true;
    }

    function setFormValues(type, data) {
        const fieldMappings = {
            anggota: {
                id: '#user_id',
                display: '#user_id_display'
            },
            paket: {
                id: '#paket_pinjaman_id',
                display: '#paket_pinjaman_id_display'
            },
            periode: {
                id: '#periode_pencairan_id',
                display: '#periode_pencairan_id_display'
            }
        };

        const fields = fieldMappings[type];
        if (fields) {
            $(fields.id).val(data.id).trigger('change');
            $(fields.display).val(data.display);
        }
    }

    function closeModal(modalId, button) {
        // Remove focus from button to prevent aria-hidden warning
        $(button).blur();

        // Close modal
        const modal = $(`#${modalId}`);
        modal.modal('hide');

        // Clean up after modal close
        setTimeout(() => {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            modal.find('*').blur(); // Remove focus from all elements inside modal
        }, 150);
    }

    // === CALCULATIONS ===
    function initializeCalculations() {
        // Real-time calculation triggers
        $('#paket_pinjaman_id, #jumlah_paket_dipilih, #tenor_pinjaman').on('change input', calculateLoan);

        // Package selection handler
        $('#paket_pinjaman_id').on('change', function() {
            if (!$(this).val()) {
                resetStockDisplay();
            }
        });

        // Initial calculation
        setTimeout(calculateLoan, 500);
    }

    function calculateLoan() {
        const paketSelect = $('#paket_pinjaman_id');
        const jumlahPaket = parseInt($('#jumlah_paket_dipilih').val()) || 1;
        const tenorSelect = $('#tenor_pinjaman');

        if (!paketSelect.val() || !tenorSelect.val()) {
            resetCalculationDisplay();
            return;
        }

        // Get calculation data
        const calculationData = getCalculationData(paketSelect, tenorSelect);

        // Calculate loan amounts
        const results = performLoanCalculation(jumlahPaket, calculationData);

        // Update displays
        updateCalculationDisplay(results);
        updateStockDisplay(calculationData.stock, jumlahPaket);
    }

    function getCalculationData(paketSelect, tenorSelect) {
        let bunga, tenorBulan, stockLimit, stockTerpakai;

        // Get data from attributes (set by search modal) or select options
        if (paketSelect.attr('data-bunga')) {
            bunga = parseFloat(paketSelect.attr('data-bunga')) || 1.0;
            stockLimit = parseInt(paketSelect.attr('data-stock-limit')) || 0;
            stockTerpakai = parseInt(paketSelect.attr('data-stock-terpakai')) || 0;
        } else {
            bunga = parseFloat(paketSelect.find(':selected').data('bunga')) || 1.0;
            stockLimit = parseInt(paketSelect.find(':selected').data('stock-limit')) || 0;
            stockTerpakai = parseInt(paketSelect.find(':selected').data('stock-terpakai')) || 0;
        }

        tenorBulan = parseInt(tenorSelect.find(':selected').data('bulan')) || 1;

        return {
            bunga,
            tenorBulan,
            stock: {
                limit: stockLimit,
                terpakai: stockTerpakai,
                available: Math.max(0, stockLimit - stockTerpakai)
            }
        };
    }

    function performLoanCalculation(jumlahPaket, data) {
        const nilaiPerPaket = 500000;
        const jumlahPinjaman = jumlahPaket * nilaiPerPaket;
        const cicilanPokok = jumlahPinjaman / data.tenorBulan;
        const bungaFlat = jumlahPinjaman * (data.bunga / 100);
        const cicilanPerBulan = cicilanPokok + bungaFlat;
        const totalPembayaran = cicilanPerBulan * data.tenorBulan;

        return {
            jumlahPinjaman,
            bunga: data.bunga,
            cicilanPerBulan,
            totalPembayaran
        };
    }

    function updateCalculationDisplay(results) {
        $('#display-jumlah-pinjaman').text(formatCurrency(results.jumlahPinjaman));
        $('#display-bunga').text(results.bunga + '%');
        $('#display-cicilan').text(formatCurrency(Math.round(results.cicilanPerBulan)));
        $('#display-total').text(formatCurrency(Math.round(results.totalPembayaran)));
    }

    function updateStockDisplay(stock, jumlahPaket) {
        const panel = $('#stock-information-panel');

        if (!panel.length) return;

        // Show stock panel
        panel.show();
        $('#stock-no-selection').hide();

        // Update stock values
        $('#display-stock-available').text(stock.available + ' paket');
        $('#display-stock-limit').text(stock.limit + ' paket');
        $('#display-stock-used').text(stock.terpakai + ' paket');
        $('#display-requested-amount').text(jumlahPaket + ' paket');

        // Update progress bar
        const usagePercentage = stock.limit > 0 ? (stock.terpakai / stock.limit) * 100 : 0;
        $('#stock-progress-bar').css('width', usagePercentage + '%');

        // Determine status
        const isInsufficient = stock.available <= 0 || jumlahPaket > stock.available;
        const statusClass = isInsufficient ? 'bg-danger' : 'bg-success';
        const panelClass = isInsufficient ? 'stock-status-danger' : 'stock-status-good';

        let statusText;
        if (isInsufficient) {
            statusText = stock.available <= 0
                ? `Stok tidak tersedia untuk memenuhi permintaan ${jumlahPaket} paket`
                : `Permintaan ${jumlahPaket} paket melebihi stok tersedia (${stock.available} paket)`;
        } else {
            statusText = `Stok mencukupi - ${stock.available} paket tersedia untuk permintaan ${jumlahPaket} paket`;
        }

        // Update UI
        $('#stock-progress-bar').removeClass('bg-success bg-danger').addClass(statusClass);
        $('#stock-progress-text').text(statusText);
        $('#stock-usage-percentage').text(`${usagePercentage.toFixed(1)}% terpakai`);
        panel.find('.info-item').removeClass('stock-status-good stock-status-danger').addClass(panelClass);
    }

    function resetStockDisplay() {
        const panel = $('#stock-information-panel');

        if (!panel.length) return;

        panel.hide();
        $('#stock-no-selection').show();

        // Reset values
        ['#display-stock-available', '#display-stock-limit', '#display-stock-used', '#display-requested-amount'].forEach(selector => {
            $(selector).text('-');
        });

        $('#stock-progress-bar').css('width', '0%').removeClass('bg-success bg-danger');
        $('#stock-progress-text').text('Pilih paket untuk melihat informasi stok');
        $('#stock-usage-percentage').text('');
    }

    function resetCalculationDisplay() {
        $('#display-jumlah-pinjaman').text('Rp 0');
        $('#display-bunga').text('0%');
        $('#display-cicilan').text('Rp 0');
        $('#display-total').text('Rp 0');
        resetStockDisplay();
    }

    // === FORM VALIDATION ===
    function initializeFormValidation() {
        $('#pengajuan-form').on('submit', function(e) {
            const anggotaId = $('#user_id').val();

            if (!anggotaId || anggotaId === '0' || anggotaId === '') {
                alert('Anggota harus dipilih untuk melanjutkan pengajuan pinjaman.');
                e.preventDefault();
                return false;
            }
        });
    }

    // === CHARACTER COUNTER ===
    function initializeCharacterCounter() {
        const textarea = $('textarea[name="tujuan_pinjaman"]');

        if (textarea.length) {
            textarea.on('input', updateCharacterCounter);
            updateCharacterCounter(); // Initial update
        }
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

        // Update or create counter
        let counter = textarea.next('small.character-counter');
        if (!counter.length) {
            counter = $(`<small class="character-counter ${counterClass}">${counterText}</small>`);
            textarea.after(counter);
        } else {
            counter.attr('class', `character-counter ${counterClass}`).text(counterText);
        }
    }

    // === UTILITY FUNCTIONS ===
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    // === DELETE CONFIRMATION ===
    window.deleteData = function(event, name, msg) {
        event.preventDefault();
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
                event.target.closest('form').submit();
            }
        });
    };
});
</script>
