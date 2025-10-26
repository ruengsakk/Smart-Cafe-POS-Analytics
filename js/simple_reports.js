// Simple Reports JS for new report pages
function loadReport(reportType, containerId) {
    const container = document.getElementById(containerId);

    if (!container) {
        console.error('Container not found:', containerId);
        return;
    }

    // Show loading spinner
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">กำลังโหลด...</span>
            </div>
            <p class="mt-3 text-muted">กำลังโหลดข้อมูล...</p>
        </div>
    `;

    // Get date range
    const startDate = document.getElementById('startDate')?.value || '';
    const endDate = document.getElementById('endDate')?.value || '';

    // Build API URL
    const params = new URLSearchParams({
        type: reportType,
        start_date: startDate,
        end_date: endDate
    });

    // Fetch report data
    fetch(`api/reports.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                displayReportTable(data, container);
            } else {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> ไม่พบข้อมูลในช่วงเวลาที่เลือก
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading report:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> เกิดข้อผิดพลาดในการโหลดข้อมูล: ${error.message}
                </div>
            `;
        });
}

function displayReportTable(data, container) {
    if (!data.data || data.data.length === 0) {
        container.innerHTML = '<div class="alert alert-info">ไม่พบข้อมูล</div>';
        return;
    }

    // Get column names from first row
    const columns = Object.keys(data.data[0]);

    // Build table HTML
    let tableHtml = `
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
    `;

    // Add column headers
    columns.forEach(col => {
        tableHtml += `<th>${col}</th>`;
    });

    tableHtml += `
                    </tr>
                </thead>
                <tbody>
    `;

    // Add data rows
    data.data.forEach(row => {
        tableHtml += '<tr>';
        columns.forEach(col => {
            let value = row[col];

            // Check if it's a date column and format accordingly
            if (col.includes('วันที่') || col.includes('date') || col.includes('Date')) {
                const dateValue = new Date(value);
                if (!isNaN(dateValue.getTime())) {
                    value = dateValue.toLocaleDateString('th-TH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }
            // Format numbers
            else if (value !== null && value !== '' && !isNaN(parseFloat(value))) {
                const numValue = parseFloat(value);
                if (col.includes('ราคา') || col.includes('ยอด') || col.includes('รวม') || col.includes('เงิน')) {
                    value = '฿' + numValue.toFixed(2);
                } else if (!isNaN(numValue) && numValue % 1 !== 0) {
                    // Has decimal
                    value = numValue.toFixed(2);
                } else {
                    // Whole number - no comma separator
                    value = Math.round(numValue).toString();
                }
            }

            // Highlight important values
            let cellClass = '';
            if (col.includes('อันดับ') && value == 1) {
                cellClass = 'class="text-warning fw-bold"';
            } else if (col.includes('อันดับ') && value == 2) {
                cellClass = 'class="text-secondary fw-bold"';
            } else if (col.includes('อันดับ') && value == 3) {
                cellClass = 'class="text-danger fw-bold"';
            }

            tableHtml += `<td ${cellClass}>${value !== null ? value : '-'}</td>`;
        });
        tableHtml += '</tr>';
    });

    tableHtml += `
                </tbody>
            </table>
        </div>
    `;

    // Add summary if available
    if (data.summary) {
        tableHtml += '<div class="alert alert-info mt-3">';
        tableHtml += '<strong><i class="fas fa-info-circle"></i> สรุป:</strong><br>';

        // Check if summary is a string or object
        if (typeof data.summary === 'string') {
            tableHtml += data.summary;
        } else if (typeof data.summary === 'object') {
            for (let key in data.summary) {
                tableHtml += `<strong>${key}:</strong> ${data.summary[key]}<br>`;
            }
        }

        tableHtml += '</div>';
    }

    // Add export button
    tableHtml += `
        <div class="mt-3">
            <button class="btn btn-success" onclick="exportToCSV('${container.id}')">
                <i class="fas fa-file-excel"></i> ส่งออก Excel
            </button>
        </div>
    `;

    container.innerHTML = tableHtml;
}

function exportToCSV(containerId) {
    const container = document.getElementById(containerId);
    const table = container.querySelector('table');

    if (!table) {
        alert('ไม่พบตารางข้อมูล');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(csvRow.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', 'report_' + new Date().getTime() + '.csv');
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
