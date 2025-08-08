// Blood Management System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle Functionality
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    // Load saved theme from localStorage
    const savedTheme = localStorage.getItem('theme') || 'light';
    body.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
    
    function updateThemeIcon(theme) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        }, 5000);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Search functionality
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.table-responsive').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Filter functionality
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const filterValue = this.value;
            const table = this.closest('.table-responsive').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const cell = row.querySelector(`[data-filter="${this.dataset.filterField}"]`);
                if (filterValue === '' || cell.textContent === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Export functionality
    const exportButtons = document.querySelectorAll('.export-btn');
    exportButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const format = this.dataset.format;
            const tableId = this.dataset.table;
            
            if (format === 'csv') {
                exportToCSV(tableId);
            } else if (format === 'pdf') {
                exportToPDF(tableId);
            }
        });
    });
    
    // Modal confirmations
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm;
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Real-time updates for dashboard
    if (document.querySelector('.dashboard-stats')) {
        updateDashboardStats();
        setInterval(updateDashboardStats, 30000); // Update every 30 seconds
    }
});

// Export to CSV function
function exportToCSV(tableId) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', tableId + '_export.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Export to PDF function (basic implementation)
function exportToPDF(tableId) {
    // This would require a PDF library like jsPDF
    // For now, we'll just show an alert
    alert('PDF export functionality requires additional libraries. Please implement jsPDF or similar.');
}

// Update dashboard stats
function updateDashboardStats() {
    // This would make an AJAX call to get updated stats
    // For now, we'll just add a visual indicator
    const stats = document.querySelectorAll('.dashboard-stat');
    stats.forEach(stat => {
        stat.style.transition = 'color 0.3s ease';
        stat.style.color = '#28a745';
        setTimeout(() => {
            stat.style.color = '';
        }, 1000);
    });
}

// Chart.js configurations
function createBloodTypeChart(canvasId, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
            datasets: [{
                data: data,
                backgroundColor: [
                    '#ff6b6b', '#ff8e8e', '#4ecdc4', '#6ee7df',
                    '#45b7d1', '#6bc5d8', '#96ceb4', '#b8e0c8'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createTrendChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Donations',
                data: data,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Notification system
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Loading spinner
function showLoading(element) {
    element.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
}

function hideLoading(element, originalContent) {
    element.innerHTML = originalContent;
} 