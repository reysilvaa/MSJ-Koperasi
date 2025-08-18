<script>
let columnAbjad = '';

// Override global DataTable initialization for complex header table
$(document).ready(function() {
    // Form validation for filter form
    $('#filterForm').on('submit', function(e) {
        const tahun = $('#tahun').val();
        if (!tahun || !$.isNumeric(tahun)) {
            e.preventDefault();
            alert('Tahun harus berupa angka');
            $('#tahun').focus();
            return false;
        }
    });

    // Focus on tahun input when page loads
    $('#tahun').focus();

    // Initialize DataTable for list page
    if ($('#iuranTable').length > 0) {
        $('#iuranTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "language": {
                "search": "Cari :",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "zeroRecords": "Maaf - Data tidak ada",
                "info": "Data _START_ - _END_ dari _TOTAL_",
                "infoEmpty": "Tidak ada data",
                "infoFiltered": "(pencarian dari _MAX_ data)"
            },
            "scrollX": true,
            "dom": 'Bfrtip',
            "buttons": []
        });
    }

    // Handle complex header table - prevent DataTable initialization
    if ($('#iuran_table_{{ $dmenu ?? 'KOP401' }}').length > 0) {
        // For complex header tables, we'll add export buttons manually without DataTable
        let $table = $('#iuran_table_{{ $dmenu ?? 'KOP401' }}');
        
        // Create button container
        let buttonContainer = '<div class="dt-buttons mb-3">' +
            '<button class="btn btn-secondary buttons-excel" type="button">' +
                '<i class="fas fa-file-excel me-1 text-lg text-success"></i>' +
                '<span class="font-weight-bold">Excel</span>' +
            '</button> ' +
            '<button class="btn btn-secondary buttons-pdf" type="button">' +
                '<i class="fas fa-file-pdf me-1 text-lg text-danger"></i>' +
                '<span class="font-weight-bold">PDF</span>' +
            '</button> ' +
            '<button class="btn btn-secondary buttons-print" type="button">' +
                '<i class="fas fa-print me-1 text-lg text-info"></i>' +
                '<span class="font-weight-bold">Print</span>' +
            '</button>' +
        '</div>';
        
        // Add buttons before the table
        $table.before(buttonContainer);
        
        // Add export functionality
        $('.buttons-excel').on('click', function() {
            // Simple table to Excel export
            let tableHtml = $table.prop('outerHTML');
            let blob = new Blob([tableHtml], {type: 'application/vnd.ms-excel'});
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'Laporan_Iuran_Anggota_{{ $filter['tahun'] ?? date('Y') }}.xls';
            a.click();
            window.URL.revokeObjectURL(url);
        });
        
        $('.buttons-pdf').on('click', function() {
            window.print();
        });
        
        $('.buttons-print').on('click', function() {
            window.print();
        });
        
        // Check authorize button datatables
        @if(isset($authorize))
            {{ $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' }}
            {{ $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' }}
            {{ $authorize->print == '0' ? "$('.buttons-print').remove();" : '' }}
        @endif
        
        //check note for resign members
        if ($('*').hasClass('not')) {
            $('#noted').html(`<code>Note : <i aria-hidden="true" style="color: #ffc2cd;" class="fas fa-circle"></i> Data not active</code>`)
        }
    }
});

//function delete
function deleteData(name, msg) {
    pesan = confirm('Apakah Anda Yakin ' + msg + ' Data ' + name + ' ini ?');
    if (pesan) return true
    else return false
}
</script>