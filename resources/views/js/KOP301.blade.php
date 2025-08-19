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
