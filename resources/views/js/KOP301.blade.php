<script>
$(document).ready(function() {
    // Get current page context
    const currentUrl = window.location.pathname;
    const isListPage = currentUrl.includes('/list') || currentUrl.endsWith('periodePencairan');
    const isShowPage = currentUrl.includes('/show');
    const isAddPage = currentUrl.includes('/add');
    const isEditPage = currentUrl.includes('/edit');

    // Initialize page-specific functionality
    if (isListPage) {
        initializePeriodeListPage();
    } else if (isShowPage) {
        initializePeriodeShowPage();
    } else if (isAddPage || isEditPage) {
        initializePeriodeFormPage();
    }

    // === LIST PAGE FUNCTIONALITY ===
    function initializePeriodeListPage() {
        // Initialize DataTables with proper column configuration
        if ($('#list_KOP301').length) {
            // Wait for DOM to be fully ready
            setTimeout(function() {
                try {
                    // Destroy existing DataTable if exists
                    if ($.fn.DataTable.isDataTable('#list_KOP301')) {
                        $('#list_KOP301').DataTable().destroy();
                    }

                    // Initialize DataTable with explicit column configuration
                    $('#list_KOP301').DataTable({
                        "pageLength": 25,
                        "order": [[ 2, "desc" ], [ 3, "asc" ]], // Order by Tahun desc, Bulan asc
                        "columnDefs": [
                            { "orderable": false, "targets": [0] }, // Disable sorting for Action column
                            { "width": "110px", "targets": [0] }    // Set width for Action column
                        ],
                        "columns": [
                            null, // Action
                            null, // No
                            null, // Tahun
                            null, // Bulan
                            null, // Nama Periode
                            null, // Status
                            null  // Dibuat
                        ],
                        "responsive": true,
                        "destroy": true,
                        "language": {
                            "emptyTable": "Belum ada periode pencairan",
                            "zeroRecords": "Tidak ada data yang cocok",
                            "search": "Cari:",
                            "lengthMenu": "Tampilkan _MENU_ data per halaman",
                            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                            "infoFiltered": "(difilter dari _MAX_ total data)",
                            "paginate": {
                                "first": "Pertama",
                                "last": "Terakhir",
                                "next": "Selanjutnya",
                                "previous": "Sebelumnya"
                            }
                        }
                    });
                } catch (error) {
                    console.log('DataTable initialization error:', error);
                }
            }, 100);
        }

        console.log('Periode Pencairan list page initialized with DataTables');
    }

    // === SHOW PAGE FUNCTIONALITY ===
    function initializePeriodeShowPage() {
        // Show page specific functionality if needed
        console.log('Periode Pencairan show page initialized');
    }

    // === FORM PAGE FUNCTIONALITY ===
    function initializePeriodeFormPage() {
        // Form validation for generate periode
        $('#generate-periode-form').on('submit', function(e) {
            const tahun = $('input[name="tahun"]').val();

            if (!tahun || tahun < 2020 || tahun > 2030) {
                e.preventDefault();
                Swal.fire({
                    title: 'Input Tidak Valid!',
                    text: 'Silakan masukkan tahun antara 2020-2030.',
                    icon: 'warning',
                    confirmButtonColor: '#028284'
                });
                return false;
            }

            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Generate Periode',
                text: `Apakah Anda yakin akan membuat periode untuk tahun ${tahun}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Generate!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#028284'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        console.log('Periode Pencairan form page initialized');
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
    window.deleteData = function(event, id, action) {
        event.preventDefault();

        Swal.fire({
            title: 'Konfirmasi',
            text: `Apakah Anda yakin ingin ${action.toLowerCase()} periode ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Ya, ${action}!`,
            cancelButtonText: 'Batal',
            confirmButtonColor: action.toLowerCase().includes('aktif') ? '#28a745' : '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form
                event.target.closest('form').submit();
            }
        });

        return false;
    };

    console.log('PeriodePencairan JavaScript initialized for:',
                isListPage ? 'List' : isShowPage ? 'Show' : isAddPage ? 'Add' : isEditPage ? 'Edit' : 'Unknown');
});
</script>
