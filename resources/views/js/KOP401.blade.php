<script>
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

    // Initialize DataTable if table exists
    if ($('#iuranTable').length > 0) {
        $('#iuranTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            "scrollX": true,
            "dom": 'Bfrtip',
            "buttons": []
        });
    }
});
</script>
