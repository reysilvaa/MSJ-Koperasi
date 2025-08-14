<script>
$(document).ready(function() {
    // Get current page context
    const currentUrl = window.location.pathname;
    const isListPage = currentUrl.includes('/list') || currentUrl.endsWith('pengajuanPinjaman');
    const isAddPage = currentUrl.includes('/add');
    const isEditPage = currentUrl.includes('/edit');
    const isShowPage = currentUrl.includes('/show');

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
    }

    // === SHOW PAGE FUNCTIONALITY ===
    function initializeShowPage() {
        // Show page specific functionality
        setupShowPageHandlers();
    }

    // === SHARED CALCULATION FUNCTIONS ===
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

            // Business logic calculation sesuai docs/PENGAJUAN_PINJAMAN_FIX.md
            const nilaiPerPaket = 500000;
            const jumlahPinjaman = jumlahPaket * nilaiPerPaket;

            // Perhitungan Bunga Flat (CORRECTED)
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

            const nilaiPerPaket = 500000;
            const jumlahPinjaman = jumlahPaket * nilaiPerPaket;

            // Perhitungan Bunga Flat (CORRECTED)
            const cicilanPokok = jumlahPinjaman / tenor;
            const bungaFlat = jumlahPinjaman * (bunga / 100);
            const cicilanPerBulan = cicilanPokok + bungaFlat;
            const totalPembayaran = cicilanPerBulan * tenor;

            // Update display
            if (document.getElementById('display-jumlah-pinjaman')) {
                document.getElementById('display-jumlah-pinjaman').textContent = formatCurrency(jumlahPinjaman);
            }
            if (document.getElementById('display-bunga')) {
                document.getElementById('display-bunga').textContent = bunga + '%';
            }
            if (document.getElementById('display-cicilan')) {
                document.getElementById('display-cicilan').textContent = formatCurrency(cicilanPerBulan);
            }
            if (document.getElementById('display-total')) {
                document.getElementById('display-total').textContent = formatCurrency(totalPembayaran);
            }

            // Validate stock
            if (jumlahPaket > stock && document.getElementById('display-stock')) {
                document.getElementById('display-stock').innerHTML =
                    '<span class="text-danger">' + stock + ' paket (Tidak mencukupi!)</span>';
            } else if (document.getElementById('display-stock')) {
                document.getElementById('display-stock').textContent = stock + ' paket';
            }
        }
    }

    function updateCalculationDisplay(jumlahPinjaman, bunga, cicilanPerBulan, totalPembayaran) {
        if ($('#display-jumlah-pinjaman').length) {
            $('#display-jumlah-pinjaman').text(formatCurrency(jumlahPinjaman));
        }
        if ($('#display-bunga').length) {
            $('#display-bunga').text(bunga + '%');
        }
        if ($('#display-cicilan').length) {
            $('#display-cicilan').text(formatCurrency(Math.round(cicilanPerBulan)));
        }
        if ($('#display-total').length) {
            $('#display-total').text(formatCurrency(Math.round(totalPembayaran)));
        }
    }

    function updateStockDisplay(stockAvailable, stockLimit, stockTerpakai, jumlahPaket) {
        if ($('#stock-display').length) {
            const stockTersisaSetelahPinjam = stockAvailable - jumlahPaket;

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
                    <span class="text-sm font-weight-bold ${stockTersisaSetelahPinjam >= 0 ? 'text-success' : 'text-danger'}">
                        ${Math.max(0, stockTersisaSetelahPinjam)} paket
                    </span>
                </div>
            `);
        }
    }

    function validateStock(jumlahPaket, stockAvailable) {
        const submitButton = $('button[type="submit"]');
        const stockInfo = $('#stock-info');

        if (jumlahPaket > stockAvailable) {
            if (stockInfo.length) {
                stockInfo.text('⚠️ Stock tidak mencukupi! Tersedia: ' + stockAvailable + ' paket')
                         .removeClass('text-info text-success')
                         .addClass('text-danger');
            }
            submitButton.prop('disabled', true);
        } else {
            if (stockInfo.length) {
                stockInfo.text('✅ Stock mencukupi (' + stockAvailable + ' paket tersedia)')
                         .removeClass('text-danger text-info')
                         .addClass('text-success');
            }
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
        if ($('#stock-info').length) {
            $('#stock-info').text('').removeClass('text-success text-danger text-info');
        }
    }

    function setupShowPageHandlers() {
        // Specific handlers for show page
        if ($('#pengajuanPinjaman-form').length) {
            //set disable all input form
            $('#pengajuanPinjaman-form').find('label').addClass('disabled');
            $('#pengajuanPinjaman-form').find('input').attr('disabled', 'disabled');
            $('#pengajuanPinjaman-form').find('select').attr('disabled', 'disabled');
            $('#pengajuanPinjaman-form').find('textarea').attr('disabled', 'disabled');
            $('#pengajuanPinjaman-form').find('input[key="true"]').parent('.form-group').css('display', '');
            $('#pengajuanPinjaman-form').find('select[key="true"]').parent('.form-group').css('display', '');
            $('.icon-modal-search').css('display', 'none');

            // function enable input form
            function enable_text() {
                $('#pengajuanPinjaman-form').find('label').removeClass('disabled');
                $('#pengajuanPinjaman-form').find('input').removeAttr('disabled');
                $('#pengajuanPinjaman-form').find('select').removeAttr('disabled');
                $('#pengajuanPinjaman-form').find('textarea').removeAttr('disabled');
                $('#pengajuanPinjaman-form').find('input[key="true"]').parent('.form-group').css('display', 'none');
                $('#pengajuanPinjaman-form').find('select[key="true"]').parent('.form-group').css('display', 'none');
                $('.icon-modal-search').css('display', '');
            }

            //event button edit
            $('#pengajuanPinjaman-edit').click(function() {
                enable_text();
                $(this).css('display', 'none');
                $('#pengajuanPinjaman-save').css('display', '');
            });
        }
    }

    // === SHARED UTILITY FUNCTIONS ===
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    // === SHARED DELETE CONFIRMATION ===
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
                // Create form and submit
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

    // === MODAL HANDLERS FOR SEARCH ===
    window.select_modal = function(id, name) {
        $('input[name="' + arguments.callee.caller.name + '"]').val(id);
        $('#searchModal' + arguments.callee.caller.name).modal('hide');
    }

    // === IMAGE PREVIEW HANDLERS ===
    $('input[type="file"]').each(function() {
        const fieldName = $(this).attr('name');
        if (fieldName) {
            this.onchange = function(evt) {
                const [file] = this.files;
                if (file) {
                    const preview = document.getElementById(fieldName + 'preview');
                    if (preview) {
                        preview.src = URL.createObjectURL(file);
                    }
                }
            };

            $('#' + fieldName + 'edit').click(function() {
                $('input[name="' + fieldName + '"]').click();
            });
        }
    });

    // === CHARACTER COUNTER FOR TEXTAREA ===
    $('textarea[name="tujuan_pinjaman"]').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;

        let counterHtml = `<small class="text-muted">${currentLength}/${maxLength} karakter`;
        if (remaining < 50) {
            counterHtml = `<small class="text-warning">${currentLength}/${maxLength} karakter (sisa: ${remaining})`;
        }
        if (remaining <= 0) {
            counterHtml = `<small class="text-danger">${currentLength}/${maxLength} karakter (melebihi batas!)`;
        }
        counterHtml += '</small>';

        // Remove existing counter and add new one
        $(this).next('small').remove();
        $(this).after(counterHtml);
    });

    console.log('PengajuanPinjaman JavaScript initialized successfully for:',
                isListPage ? 'List' : isAddPage ? 'Add' : isEditPage ? 'Edit' : isShowPage ? 'Show' : 'Unknown');

});
</script>
