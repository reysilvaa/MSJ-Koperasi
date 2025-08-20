<script>
let columnAbjad = '';

$(document).ready(function() {
    // Form validation for filter form - updated for new KOP401 (Laporan Iuran Tahunan)
    $('#{{ $dmenu ?? 'KOP401' }}-form').on('submit', function(e) {
        const tahun = $('#tahun').val();
        const currentYear = new Date().getFullYear();
        
        // Validasi tahun
        if (!tahun || !$.isNumeric(tahun)) {
            e.preventDefault();
            Swal.fire({
                title: 'Validasi Error!',
                text: 'Tahun harus berupa angka',
                icon: 'error',
                confirmButtonColor: '#028284'
            });
            $('#tahun').focus();
            return false;
        }
        
        // Validasi range tahun
        if (tahun < 2000 || tahun > currentYear) {
            e.preventDefault();
            Swal.fire({
                title: 'Validasi Error!',
                text: 'Tahun harus antara 2000 - ' + currentYear,
                icon: 'error',
                confirmButtonColor: '#028284'
            });
            $('#tahun').focus();
            return false;
        }
        
        // Show loading saat submit
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang generate laporan iuran tahunan untuk tahun ' + tahun,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Focus on tahun input when page loads
    $('#tahun').focus();

    // Initialize DataTable for list page
    if ($('#iuranTable').length > 0) {
        // Check if DataTable is already initialized and destroy it first
        if ($.fn.DataTable.isDataTable('#iuranTable')) {
            $('#iuranTable').DataTable().destroy();
        }
        
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

    // Initialize DataTable for result page (dataTable ID from result.blade.php)
    if ($('#dataTable').length > 0) {
        // Use setTimeout to ensure this runs after global DataTable initialization
        setTimeout(function() {
            // Check if DataTable is already initialized and destroy it first
            if ($.fn.DataTable.isDataTable('#dataTable')) {
                $('#dataTable').DataTable().destroy();
            }
            
            let table = $('#dataTable').DataTable({
                "pageLength": 25,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                "language": {
                    "search": "Cari :",
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "zeroRecords": "Tidak ada data iuran",
                    "info": "Data _START_ - _END_ dari _TOTAL_",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(pencarian dari _MAX_ data)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "order": [[ 0, "asc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0] }, // No column tidak bisa di-sort
                    { "className": "text-center", "targets": [0, 3, 4, 5] }, // Center align untuk kolom tertentu
                    { "className": "text-left", "targets": [1, 2] } // Left align untuk nama
                ],
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i>Excel',
                        className: 'btn btn-success btn-sm',
                        autoFilter: true,
                        sheetName: 'Laporan Iuran Bulanan',
                        title: function() {
                            var filterInfo = '';
                            if ($('.card-header p').length > 0) {
                                filterInfo = $('.card-header p').text().replace('Filter: ', '');
                            }
                            return 'Laporan Iuran Bulanan - ' + filterInfo;
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf me-1"></i>PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: function() {
                            var filterInfo = '';
                            if ($('.card-header p').length > 0) {
                                filterInfo = $('.card-header p').text().replace('Filter: ', '');
                            }
                            return 'Laporan Iuran Bulanan - ' + filterInfo;
                        },
                        exportOptions: {
                            columns: ':visible'
                        },
                        customize: function(doc) {
                            // Style the document
                            doc.defaultStyle.fontSize = 9;
                            doc.styles.tableHeader.fontSize = 10;
                            doc.styles.tableHeader.bold = true;
                            doc.styles.tableHeader.alignment = 'center';
                            
                            // Add filter info to document
                            if ($('.card-header p').length > 0) {
                                var filterText = $('.card-header p').text();
                                doc.content.splice(1, 0, {
                                    text: filterText,
                                    style: 'subheader',
                                    alignment: 'center',
                                    margin: [0, 0, 0, 10]
                                });
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i>Print',
                        className: 'btn btn-info btn-sm',
                        title: function() {
                            var filterInfo = '';
                            if ($('.card-header p').length > 0) {
                                filterInfo = $('.card-header p').text().replace('Filter: ', '');
                            }
                            return 'Laporan Iuran Bulanan - ' + filterInfo;
                        },
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                "initComplete": function(settings, json) {
                    // Custom styling after DataTable initialization
                    $('.dt-button').removeClass('dt-button');
                    
                    // Add summary info if available
                    if ($('.card .card-body .row .col-xl-3').length > 0) {
                        var totalRecords = this.api().data().length;
                        console.log('Total records in table: ' + totalRecords);
                    }
                }
            });

            // Check authorize button datatables
            @if(isset($authorize))
                {{ $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' }}
                {{ $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' }}
                {{ $authorize->print == '0' ? "$('.buttons-print').remove();" : '' }}
            @endif
        }, 300);
    }

    // Initialize DataTable for legacy result page - with delay to avoid global conflict
    if ($('#list_{{ $dmenu ?? 'KOP401' }}').length > 0) {
        // Use setTimeout to ensure this runs after global DataTable initialization
        setTimeout(function() {
            // Check if DataTable is already initialized and destroy it first
            if ($.fn.DataTable.isDataTable('#list_{{ $dmenu ?? 'KOP401' }}')) {
                $('#list_{{ $dmenu ?? 'KOP401' }}').DataTable().destroy();
            }
            
            let table = $('#list_{{ $dmenu ?? 'KOP401' }}').DataTable({
                "language": {
                    "search": "Cari :",
                    "lengthMenu": "Tampilkan _MENU_ baris",
                    "zeroRecords": "Maaf - Data tidak ada",
                    "info": "Data _START_ - _END_ dari _TOTAL_",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(pencarian dari _MAX_ data)"
                },
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1 text-lg text-success"></i><span class="font-weight-bold">Excel</span>',
                        autoFilter: true,
                        sheetName: 'Laporan Iuran Anggota',
                        title: 'Laporan Iuran Anggota',
                        action: function(e, dt, button, config) {
                            // Get main table data
                            var mainData = dt.buttons.exportData({
                                columns: ':visible'
                            });
                            
                            // Get summary table data
                            var summaryRows = [];
                            $('#summary_table tbody tr').each(function() {
                                var row = [];
                                $(this).find('td').each(function() {
                                    row.push($(this).text().trim());
                                });
                                summaryRows.push(row);
                            });
                            
                            // Combine data
                            var combinedData = {
                                header: mainData.header,
                                body: mainData.body.concat([''], summaryRows), // Add empty row as separator
                                footer: mainData.footer
                            };
                            
                            // Create Excel export with combined data
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, $.extend({}, config, {
                                exportOptions: {
                                    format: {
                                        body: function(data, row, column, node) {
                                            return data;
                                        }
                                    }
                                },
                                customize: function(xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                    
                                    // Add summary data to the sheet
                                    var sheetData = sheet.getElementsByTagName('sheetData')[0];
                                    var rows = sheetData.getElementsByTagName('row');
                                    var lastRowNum = rows.length;
                                    
                                    // Add summary rows
                                    summaryRows.forEach(function(rowData, index) {
                                        var newRow = sheet.createElement('row');
                                        newRow.setAttribute('r', lastRowNum + index + 2);
                                        
                                        rowData.forEach(function(cellData, cellIndex) {
                                            var cell = sheet.createElement('c');
                                            cell.setAttribute('r', String.fromCharCode(65 + cellIndex) + (lastRowNum + index + 2));
                                            cell.setAttribute('t', 'inlineStr');
                                            
                                            var inlineStr = sheet.createElement('is');
                                            var text = sheet.createElement('t');
                                            text.textContent = cellData;
                                            inlineStr.appendChild(text);
                                            cell.appendChild(inlineStr);
                                            newRow.appendChild(cell);
                                        });
                                        
                                        sheetData.appendChild(newRow);
                                    });
                                }
                            }));
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf me-1 text-lg text-danger"></i><span class="font-weight-bold">PDF</span>',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Laporan Iuran Anggota',
                        exportOptions: {
                            columns: ':visible'
                        },
                        customize: function(doc) {
                            // Get summary data and add to PDF
                            var summaryRows = [];
                            $('#summary_table tbody tr').each(function() {
                                var row = [];
                                $(this).find('td').each(function() {
                                    var cellText = $(this).text().trim();
                                    row.push(cellText || '');
                                });
                                summaryRows.push(row);
                            });
                            
                            // Add summary rows to PDF table
                            if (summaryRows.length > 0 && doc.content[1] && doc.content[1].table) {
                                // Add empty row as separator
                                var columnCount = doc.content[1].table.body[0].length;
                                var emptyRow = new Array(columnCount).fill('');
                                doc.content[1].table.body.push(emptyRow);
                                
                                // Add summary rows
                                summaryRows.forEach(function(row) {
                                    // Ensure row has same number of columns
                                    while (row.length < columnCount) {
                                        row.push('');
                                    }
                                    doc.content[1].table.body.push(row);
                                });
                            }
                            
                            // Style the document for better readability
                            doc.defaultStyle.fontSize = 8;
                            if (doc.styles.tableHeader) {
                                doc.styles.tableHeader.fontSize = 9;
                                doc.styles.tableHeader.bold = true;
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1 text-lg text-info"></i><span class="font-weight-bold">Print</span>',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Laporan Iuran Anggota',
                        exportOptions: {
                            columns: ':visible'
                        },
                        customize: function(win) {
                            // Get main table data
                            var mainData = $('#list_{{ $dmenu ?? 'KOP401' }}').DataTable().buttons.exportData({
                                columns: ':visible'
                            });
                            
                            // Get summary table data
                            var summaryRows = [];
                            $('#summary_table tbody tr').each(function() {
                                var row = [];
                                $(this).find('td').each(function() {
                                    var cellText = $(this).text().trim();
                                    row.push(cellText || '');
                                });
                                summaryRows.push(row);
                            });
                            
                            // Build complete table HTML like PDF
                            var tableHtml = '<table style="border-collapse: collapse; width: 100%; font-size: 8pt;">';
                            
                            // Add header
                            tableHtml += '<thead>';
                            tableHtml += '<tr>';
                            mainData.header.forEach(function(header) {
                                tableHtml += '<th style="border: 1px solid #000; padding: 4px; background-color: #f2f2f2; font-weight: bold; text-align: center;">' + header + '</th>';
                            });
                            tableHtml += '</tr>';
                            tableHtml += '</thead>';
                            
                            // Add body data
                            tableHtml += '<tbody>';
                            mainData.body.forEach(function(row) {
                                tableHtml += '<tr>';
                                row.forEach(function(cell, index) {
                                    var textAlign = index === 1 ? 'left' : 'center'; // Name column left, others center
                                    tableHtml += '<td style="border: 1px solid #000; padding: 4px; text-align: ' + textAlign + ';">' + cell + '</td>';
                                });
                                tableHtml += '</tr>';
                            });
                            
                            // Add empty separator row
                            tableHtml += '<tr>';
                            for (var i = 0; i < mainData.header.length; i++) {
                                tableHtml += '<td style="border: 1px solid #000; padding: 4px;">&nbsp;</td>';
                            }
                            tableHtml += '</tr>';
                            
                            // Add summary rows
                            summaryRows.forEach(function(row, rowIndex) {
                                var bgColor = '';
                                if (rowIndex === 0) bgColor = '#e9ecef'; // TOTAL row
                                else if (rowIndex === 1) bgColor = '#fff3cd'; // SP row
                                else if (rowIndex === 2) bgColor = '#d1ecf1'; // SW row
                                
                                tableHtml += '<tr style="background-color: ' + bgColor + ';">';
                                row.forEach(function(cell, index) {
                                    var textAlign = index === 1 ? 'left' : 'center'; // Name column left, others center
                                    var fontWeight = (index === 1 || index === row.length - 1) ? 'bold' : 'normal'; // Name and total columns bold
                                    var specialBg = '';
                                    if (index === row.length - 1) { // Last column (total)
                                        if (rowIndex === 0) specialBg = 'background-color: #d4edda;'; // TOTAL column
                                        else if (rowIndex === 1) specialBg = 'background-color: #ffeaa7;'; // SP total
                                        else if (rowIndex === 2) specialBg = 'background-color: #74b9ff;'; // SW total
                                    }
                                    tableHtml += '<td style="border: 1px solid #000; padding: 4px; text-align: ' + textAlign + '; font-weight: ' + fontWeight + '; ' + specialBg + '">' + cell + '</td>';
                                });
                                tableHtml += '</tr>';
                            });
                            
                            tableHtml += '</tbody>';
                            tableHtml += '</table>';
                            
                            // Replace the default table with our custom table
                            $(win.document.body).find('table').replaceWith(tableHtml);
                            
                            // Add custom styles to match PDF exactly
                            $(win.document.head).append(`
                                <style>
                                    @page { 
                                        size: A4 landscape; 
                                        margin: 0.5in; 
                                    }
                                    body { 
                                        font-family: Arial, sans-serif; 
                                        font-size: 8pt;
                                        margin: 0;
                                        padding: 20px;
                                    }
                                    h1 { 
                                        text-align: center; 
                                        font-size: 14pt; 
                                        margin-bottom: 20px;
                                        font-weight: bold;
                                    }
                                    table { 
                                        border-collapse: collapse; 
                                        width: 100%; 
                                        font-size: 8pt;
                                    }
                                    th, td { 
                                        border: 1px solid #000; 
                                        padding: 4px; 
                                        vertical-align: middle;
                                    }
                                    th { 
                                        background-color: #f2f2f2 !important; 
                                        font-weight: bold; 
                                        text-align: center;
                                    }
                                    .dt-print-view { 
                                        display: block !important; 
                                    }
                                    @media print {
                                        body { margin: 0; padding: 10px; }
                                        .dt-buttons { display: none !important; }
                                        * { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
                                    }
                                </style>
                            `);
                        }
                    },
                ],
                "initComplete": function(settings, json) {
                    // Custom styling after DataTable initialization like auto report
                    $('.dt-button').addClass('btn btn-secondary');
                    $('.dt-button').removeClass('dt-button');
                    
                    // Check for resign members and update note
                    if ($('*').hasClass('not')) {
                        $('#noted').html(`<code>Note: Data iuran anggota <i aria-hidden="true" style="color: #ffc2cd;" class="fas fa-circle"></i> Data not active</code>`);
                    }
                }
            });

            // Check authorize button datatables (if authorization data exists)
            @if(isset($authorize))
                {{ $authorize->excel == '0' ? "$('.buttons-excel').remove();" : '' }}
                {{ $authorize->pdf == '0' ? "$('.buttons-pdf').remove();" : '' }}
                {{ $authorize->print == '0' ? "$('.buttons-print').remove();" : '' }}
            @endif
        }, 200); // 200ms delay to ensure global script runs first
    }
});

// Initialize Chart if canvas exists (from result.blade.php)
    if ($('#chartIuran').length > 0 && typeof chartData !== 'undefined') {
        setTimeout(function() {
            initializeIuranChart();
        }, 500);
    }

    // Auto-close loading swal when page is fully loaded
    $(window).on('load', function() {
        if (Swal.isVisible()) {
            Swal.close();
        }
    });
});

// Function to initialize Chart.js for iuran data
function initializeIuranChart() {
    const ctx = document.getElementById('chartIuran');
    if (!ctx) return;
    
    // Get chart data from global variable or from result page
    let data = [];
    if (typeof chartData !== 'undefined') {
        data = chartData;
    } else if (typeof window.chartData !== 'undefined') {
        data = window.chartData;
    }
    
    if (data.length === 0) {
        console.log('No chart data available');
        return;
    }
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.nama_bulan),
            datasets: [{
                label: 'Total Nominal (Rp)',
                data: data.map(item => item.total_nominal),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Jumlah Transaksi',
                data: data.map(item => item.total_transaksi),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Bulan'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Total Nominal (Rp)'
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Grafik Iuran per Bulan'
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            } else {
                                label += context.parsed.y + ' transaksi';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

// Function to export data (called from result.blade.php)
function exportData(type) {
    if (document.getElementById('exportForm')) {
        document.getElementById('exportType').value = type;
        document.getElementById('exportForm').submit();
    } else {
        console.error('Export form not found');
    }
}

// Function to format currency for display
function formatCurrency(amount) {
    return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}

// Function to format number with thousand separator
function formatNumber(number) {
    return parseInt(number).toLocaleString('id-ID');
}

// Function to show success message
function showSuccessMessage(message) {
    Swal.fire({
        title: 'Berhasil!',
        text: message,
        icon: 'success',
        confirmButtonColor: '#028284',
        timer: 3000,
        timerProgressBar: true
    });
}

// Function to show error message
function showErrorMessage(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonColor: '#028284'
    });
}

// Function to show loading
function showLoading(message = 'Memproses...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Function delete (legacy support)
function deleteData(name, msg) {
    return Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ' + msg + ' data ' + name + ' ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        return result.isConfirmed;
    });
}

// Function to handle responsive table on mobile
function handleResponsiveTable() {
    if ($(window).width() < 768) {
        $('.table-responsive').addClass('table-responsive-sm');
        $('.card-body').addClass('px-2');
    } else {
        $('.table-responsive').removeClass('table-responsive-sm');
        $('.card-body').removeClass('px-2');
    }
}

// Handle window resize for responsive table
$(window).resize(function() {
    handleResponsiveTable();
});

// Initialize responsive table on load
$(document).ready(function() {
    handleResponsiveTable();
});
</script>