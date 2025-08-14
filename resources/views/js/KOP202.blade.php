<script>
$(document).ready(function() {
    // Get current page context
    const currentUrl = window.location.pathname;
    const isListPage = (currentUrl.includes('/list') || currentUrl.endsWith('approvalPinjaman')) && currentUrl.includes('approval');
    const isShowPage = currentUrl.includes('/show') && currentUrl.includes('approval');

    // Initialize page-specific functionality
    if (isListPage) {
        initializeApprovalListPage();
    } else if (isShowPage) {
        initializeApprovalShowPage();
    }

    // === LIST PAGE FUNCTIONALITY ===
    function initializeApprovalListPage() {
        // Initialize DataTable for approval list
        if ($('#approval-table').length > 0) {
            // // Check if DataTable is already initialized and destroy it first
            // if ($.fn.DataTable.isDataTable('#approval-table')) {
            //     $('#approval-table').DataTable().destroy();
            // }

            $('#approval-table').DataTable({
                "language": {
                    "search": "Cari :",
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "zeroRecords": "Tidak ada pengajuan untuk direview",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengajuan",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total pengajuan)"
                },
                "pageLength": 10,
                "responsive": true,
                "order": [[5, "desc"]], // Sort by tanggal
                "columnDefs": [
                    { "orderable": false, "targets": 6 } // Disable sorting on Action column
                ]
            });
        }
    }

    // === SHOW PAGE FUNCTIONALITY ===
    function initializeApprovalShowPage() {
        // Form validation for approval process
        $('#approval-form').on('submit', function(e) {
            const action = $('input[name="action"]:checked').val();

            if (!action) {
                e.preventDefault();
                Swal.fire({
                    title: 'Pilih Keputusan!',
                    text: 'Silakan pilih apakah akan menyetujui atau menolak pengajuan ini.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
            const confirmText = action === 'approve' ? 'Setujui' : 'Tolak';

            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Approval',
                text: `Apakah Anda yakin akan ${actionText} pengajuan ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                confirmButtonColor: action === 'approve' ? '#28a745' : '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
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
    window.confirmDelete = function(id) {
        Swal.fire({
            title: 'Hapus Data Approval?',
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

    console.log('ApprovalPinjaman JavaScript initialized for:',
                isListPage ? 'List' : isShowPage ? 'Show' : 'Unknown');
});
</script>
