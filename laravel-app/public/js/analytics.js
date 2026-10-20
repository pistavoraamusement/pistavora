// Sample data
let salesData = [
  { date: '2026-05-01', amount: 5200, items: 12, category: 'Food', payment: 'cash' },
  { date: '2026-05-02', amount: 6800, items: 18, category: 'Beverages', payment: 'card' },
  { date: '2026-05-03', amount: 4300, items: 10, category: 'Snacks', payment: 'cash' },
  { date: '2026-05-04', amount: 7100, items: 15, category: 'Food', payment: 'card' },
  { date: '2026-05-05', amount: 5900, items: 14, category: 'Beverages', payment: 'cash' }
];

let attendanceData = [
  { date: '2026-05-01', employee: 'John Doe', timeIn: '08:00', timeOut: '17:00', status: 'Present' },
  { date: '2026-05-02', employee: 'Jane Smith', timeIn: '08:15', timeOut: '17:30', status: 'Present' },
  { date: '2026-05-03', employee: 'Bob Johnson', timeIn: '08:00', timeOut: '17:00', status: 'Present' },
  { date: '2026-05-04', employee: 'John Doe', timeIn: '09:00', timeOut: '17:00', status: 'Late' },
  { date: '2026-05-05', employee: 'Jane Smith', timeIn: '08:00', timeOut: '17:00', status: 'Present' }
];

let charts = {};

// Pagination
const itemsPerPage = 5;
let salesCurrentPage = 1;
let attendanceCurrentPage = 1;

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
  initCharts();
  loadReports();
  setDefaultDates();
});

// Set default date range
function setDefaultDates() {
  const today = new Date();
  const sevenDaysAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
  
  document.getElementById('dateFrom').valueAsDate = sevenDaysAgo;
  document.getElementById('dateTo').valueAsDate = today;
}

// Initialize all charts
function initCharts() {
  // Sales Trend Chart
  const salesCtx = document.getElementById('salesChart').getContext('2d');
  charts.sales = new Chart(salesCtx, {
    type: 'line',
    data: {
      labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
      datasets: [{
        label: 'Sales (₱)',
        data: [5200, 6800, 4300, 7100, 5900, 6400, 7500],
        borderColor: '#667eea',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointBackgroundColor: '#667eea',
        pointBorderColor: '#fff',
        pointBorderWidth: 2
      }]
    },
   options: {
  responsive: true,
  maintainAspectRatio: true,
  aspectRatio: 2,
  plugins: {
    legend: {
      position: 'bottom'
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
}
  });

  // Category Chart
  const categoryCtx = document.getElementById('categoryChart').getContext('2d');
  charts.category = new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
      labels: ['Food', 'Beverages', 'Snacks', 'Others'],
      datasets: [{
        data: [35, 25, 25, 15],
        backgroundColor: [
          '#667eea',
          '#764ba2',
          '#f59e0b',
          '#10b981'
        ],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: false,
      width: 300,
      height: 200,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  // Attendance Chart
  const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
  charts.attendance = new Chart(attendanceCtx, {
    type: 'bar',
    data: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
      datasets: [{
        label: 'Present',
        data: [10, 12, 11, 10, 12],
        backgroundColor: '#10b981',
        borderRadius: 5
      }, {
        label: 'Absent',
        data: [2, 1, 2, 3, 1],
        backgroundColor: '#ef4444',
        borderRadius: 5
      }]
    },
    options: {
      responsive: false,
      width: 300,
      height: 200,
      indexAxis: 'x',
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // Payment Methods Chart
  const paymentCtx = document.getElementById('paymentChart').getContext('2d');
  charts.payment = new Chart(paymentCtx, {
    type: 'pie',
    data: {
      labels: ['Cash', 'Card', 'Online'],
      datasets: [{
        data: [40, 35, 25],
        backgroundColor: [
          '#667eea',
          '#f59e0b',
          '#10b981'
        ],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: false,
      width: 300,
      height: 200,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  updateMetrics();
}

// Update metrics
function updateMetrics() {
  const totalSales = salesData.reduce((sum, item) => sum + item.amount, 0);
  const avgOrder = salesData.length > 0 ? totalSales / salesData.length : 0;
  const totalOrders = salesData.reduce((sum, item) => sum + item.items, 0);
  const attendanceRate = 93;

  document.getElementById('totalSales').textContent = totalSales.toFixed(2);
  document.getElementById('totalOrders').textContent = totalOrders;
  document.getElementById('avgOrder').textContent = avgOrder.toFixed(2);
  document.getElementById('attendanceRate').textContent = attendanceRate;
}

// Load reports with pagination
function loadReports() {
  loadSalesReport();
  loadAttendanceReport();
}

// Load sales report
function loadSalesReport() {
  const totalPages = Math.ceil(salesData.length / itemsPerPage);
  const start = (salesCurrentPage - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  const pageData = salesData.slice(start, end);

  const tbody = document.getElementById('salesReport');
  tbody.innerHTML = '';

  pageData.forEach((sale, index) => {
    const row = `
      <tr>
        <td>${sale.date}</td>
        <td>#ORD${String(start + index + 1).padStart(4, '0')}</td>
        <td>${sale.category}</td>
        <td>${sale.items} items</td>
        <td>₱${sale.amount.toFixed(2)}</td>
        <td>${sale.payment}</td>
      </tr>
    `;
    tbody.innerHTML += row;
  });

  generatePagination('sales', totalPages);
}

// Load attendance report
function loadAttendanceReport() {
  const totalPages = Math.ceil(attendanceData.length / itemsPerPage);
  const start = (attendanceCurrentPage - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  const pageData = attendanceData.slice(start, end);

  const tbody = document.getElementById('attendanceReport');
  tbody.innerHTML = '';

  pageData.forEach(att => {
    const row = `
      <tr>
        <td>${att.date}</td>
        <td>${att.employee}</td>
        <td>${att.timeIn}</td>
        <td>${att.timeOut}</td>
        <td><span class="status-badge" style="background: ${att.status === 'Present' ? '#d1fae5' : '#fef3c7'}; color: ${att.status === 'Present' ? '#065f46' : '#92400e'}">${att.status}</span></td>
      </tr>
    `;
    tbody.innerHTML += row;
  });

  generatePagination('attendance', totalPages);
}

// Generate pagination buttons
function generatePagination(type, totalPages) {
  const container = document.getElementById(type === 'sales' ? 'salesPagination' : 'attendancePagination');
  const infoDiv = document.getElementById(type === 'sales' ? 'salesPageInfo' : 'attendancePageInfo');
  const currentPage = type === 'sales' ? salesCurrentPage : attendanceCurrentPage;

  container.innerHTML = '';

  if (totalPages <= 1) {
    infoDiv.textContent = '';
    return;
  }

  // Previous button
  if (currentPage > 1) {
    const prevBtn = document.createElement('button');
    prevBtn.textContent = '← Prev';
    prevBtn.onclick = () => {
      if (type === 'sales') {
        salesCurrentPage--;
        loadSalesReport();
      } else {
        attendanceCurrentPage--;
        loadAttendanceReport();
      }
    };
    container.appendChild(prevBtn);
  }

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    if (i === currentPage) btn.classList.add('active');
    btn.onclick = () => {
      if (type === 'sales') {
        salesCurrentPage = i;
        loadSalesReport();
      } else {
        attendanceCurrentPage = i;
        loadAttendanceReport();
      }
    };
    container.appendChild(btn);
  }

  // Next button
  if (currentPage < totalPages) {
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next →';
    nextBtn.onclick = () => {
      if (type === 'sales') {
        salesCurrentPage++;
        loadSalesReport();
      } else {
        attendanceCurrentPage++;
        loadAttendanceReport();
      }
    };
    container.appendChild(nextBtn);
  }

  infoDiv.textContent = `Page ${currentPage} of ${totalPages}`;
}

// Apply date filter
function applyDateFilter() {
  const from = document.getElementById('dateFrom').value;
  const to = document.getElementById('dateTo').value;
  
  if (from && to) {
    alert(`Filtered data from ${from} to ${to}`);
  }
}

// Export to PDF
function exportToPDF() {
  alert('PDF export functionality - integrate a PDF library like jsPDF');
}

// Export to CSV
function exportToCSV() {
  let csv = 'Date,Order ID,Category,Items,Amount,Payment\n';
  salesData.forEach((sale, index) => {
    csv += `${sale.date},ORD${String(index + 1).padStart(4, '0')},${sale.category},${sale.items},${sale.amount},${sale.payment}\n`;
  });

  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'sales-report.csv';
  a.click();
}

// Print report
function printReport() {
  window.print();
}