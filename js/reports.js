// Reports JavaScript
let currentReportType = 'daily_sales';

async function loadReport(reportType) {
    currentReportType = reportType;
    const resultContainer = document.getElementById(reportType.replace('_', '-') + '-result');

    // Show loading
    resultContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> กำลังโหลดข้อมูล...</div>';

    try {
        // Get date range values
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // Build URL with date parameters
        let url = `api/reports.php?type=${reportType}`;
        if (startDate) url += `&start_date=${startDate}`;
        if (endDate) url += `&end_date=${endDate}`;

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            if (result.data && result.data.length > 0) {
                let tableHTML = '<div class="table-responsive"><table class="table table-striped table-hover">';

                // Table header
                tableHTML += '<thead class="table-dark"><tr>';
                Object.keys(result.data[0]).forEach(key => {
                    tableHTML += `<th>${key}</th>`;
                });
                tableHTML += '</tr></thead>';

                // Table body
                tableHTML += '<tbody>';
                result.data.forEach(row => {
                    tableHTML += '<tr>';
                    Object.values(row).forEach(value => {
                        // Format numbers if they look like currency
                        if (typeof value === 'string' && !isNaN(value) && value.includes('.')) {
                            const num = parseFloat(value);
                            if (num > 0) {
                                value = `฿${num.toFixed(2)}`;
                            }
                        }
                        tableHTML += `<td>${value || '-'}</td>`;
                    });
                    tableHTML += '</tr>';
                });
                tableHTML += '</tbody></table></div>';

                tableHTML += '</div>';

                // Add summary if available
                if (result.summary) {
                    tableHTML += `<div class="alert alert-info mt-3"><strong>สรุป:</strong> ${result.summary}</div>`;
                }

                // Add export button
                tableHTML += `
                    <div class="mt-3">
                        <button class="btn btn-outline-success btn-sm" onclick="exportReport('${reportType}')">
                            <i class="fas fa-download"></i> ส่งออก CSV
                        </button>
                        <small class="text-muted ms-2">ช่วงเวลา: ${getDateRangeText()}</small>
                    </div>
                `;

                resultContainer.innerHTML = tableHTML;
            } else {
                resultContainer.innerHTML = '<div class="alert alert-warning">ไม่พบข้อมูลสำหรับรายงานนี้</div>';
            }
        } else {
            resultContainer.innerHTML = `<div class="alert alert-danger">เกิดข้อผิดพลาด: ${result.message}</div>`;
        }
    } catch (error) {
        console.error('Error loading report:', error);
        resultContainer.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการโหลดรายงาน</div>';
    }
}

// Date range management functions
function setPredefinedRange() {
    const rangeSelect = document.getElementById('predefinedRange');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const today = new Date();

    let startDate, endDate;

    switch (rangeSelect.value) {
        case 'today':
            startDate = endDate = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = endDate = yesterday.toISOString().split('T')[0];
            break;
        case 'last7days':
            const last7days = new Date(today);
            last7days.setDate(last7days.getDate() - 7);
            startDate = last7days.toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        case 'last30days':
            const last30days = new Date(today);
            last30days.setDate(last30days.getDate() - 30);
            startDate = last30days.toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        case 'thismonth':
            startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        case 'lastmonth':
            const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            startDate = lastMonthStart.toISOString().split('T')[0];
            endDate = lastMonthEnd.toISOString().split('T')[0];
            break;
        case 'thisyear':
            startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            endDate = today.toISOString().split('T')[0];
            break;
        default:
            return; // Custom range, don't change inputs
    }

    startDateInput.value = startDate;
    endDateInput.value = endDate;
}

function updateDateRange() {
    // Reset predefined range to custom when dates are manually changed
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (startDate || endDate) {
        document.getElementById('predefinedRange').value = '';
    }
}

function refreshCurrentReport() {
    if (currentReportType) {
        loadReport(currentReportType);
    }
}

function getDateRangeText() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const predefinedRange = document.getElementById('predefinedRange').value;

    if (predefinedRange) {
        const rangeLabels = {
            'today': 'วันนี้',
            'yesterday': 'เมื่อวาน',
            'last7days': '7 วันล่าสุด',
            'last30days': '30 วันล่าสุด',
            'thismonth': 'เดือนนี้',
            'lastmonth': 'เดือนที่แล้ว',
            'thisyear': 'ปีนี้'
        };
        return rangeLabels[predefinedRange] || 'ไม่ระบุ';
    }

    if (startDate && endDate) {
        return `${startDate} ถึง ${endDate}`;
    } else if (startDate) {
        return `ตั้งแต่ ${startDate}`;
    } else if (endDate) {
        return `ถึง ${endDate}`;
    }

    return '7 วันล่าสุด (ค่าเริ่มต้น)';
}

// Auto-load first report when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set default date range (last 7 days)
    setPredefinedRange();

    // Initialize SQL queries
    updateSqlQueries();

    // Load first report
    loadReport('daily_sales');

    // Add click events to report tabs
    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target').replace('#', '').replace('-', '_');
            currentReportType = targetId;
            loadReport(targetId);
        });
    });

    // Add explanation popup for learning
    addSqlExplanationFeature();

    // Add copy SQL buttons
    addCopySqlButtons();
});

// Format currency function
function formatCurrency(amount) {
    return new Intl.NumberFormat('th-TH', {
        style: 'currency',
        currency: 'THB'
    }).format(amount);
}

// Export report function
function exportReport(reportType) {
    const table = document.querySelector(`#${reportType.replace('_', '-')}-result table`);
    if (!table) {
        alert('ไม่มีข้อมูลให้ส่งออก');
        return;
    }

    let csv = '';
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.replace(/"/g, '""') + '"';
        });
        csv += rowData.join(',') + '\n';
    });

    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${reportType}_${new Date().toISOString().slice(0, 10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add syntax highlighting for better learning experience
function highlightSqlSyntax() {
    document.querySelectorAll('.sql-dynamic').forEach(container => {
        // This function can be extended to add more sophisticated highlighting
        // For now, the HTML templates already include syntax highlighting classes
    });
}

// Function to copy SQL to clipboard
function copySqlToClipboard(reportType) {
    const sqlContainer = document.getElementById(`${reportType.replace('_', '-')}-sql`);
    if (sqlContainer) {
        const sqlText = sqlContainer.textContent;
        navigator.clipboard.writeText(sqlText).then(() => {
            // Show success notification
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4caf50;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s;
            `;
            notification.textContent = 'คัดลอก SQL แล้ว!';
            document.body.appendChild(notification);

            setTimeout(() => notification.style.opacity = '1', 100);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 2000);
        });
    }
}

// Add copy SQL button functionality
function addCopySqlButtons() {
    Object.keys(sqlTemplates).forEach(reportType => {
        const sqlContainer = document.getElementById(`${reportType.replace('_', '-')}-sql`);
        if (sqlContainer && !sqlContainer.nextElementSibling?.classList.contains('sql-controls')) {
            const controls = document.createElement('div');
            controls.className = 'sql-controls mt-2';
            controls.innerHTML = `
                <button class="btn btn-outline-secondary btn-sm me-2" onclick="copySqlToClipboard('${reportType}')">
                    <i class="fas fa-copy"></i> คัดลอก SQL
                </button>
                <button class="btn btn-outline-info btn-sm" onclick="showSqlExplanation('${reportType}')">
                    <i class="fas fa-question-circle"></i> อธิบาย SQL
                </button>
            `;
            sqlContainer.parentNode.insertBefore(controls, sqlContainer.nextSibling);
        }
    });
}