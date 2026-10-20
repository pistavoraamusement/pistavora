// Dashboard data
let dashboardData = {
  todaySales: 0,
  monthlyRevenue: 125500,
  newCustomers: 12,
  attendanceRate: 92,
  topProducts: [
    { name: 'Coffee', sales: 250, amount: 30000 },
    { name: 'Burger', sales: 180, amount: 45000 },
    { name: 'Iced Tea', sales: 200, amount: 16000 },
    { name: 'Pizza', sales: 120, amount: 18000 }
  ],
  recentActivity: [
    { icon: '🛒', title: 'New order received', time: '5 minutes ago', status: 'success' },
    { icon: '👥', title: 'New customer booking', time: '15 minutes ago', status: 'success' },
    { icon: '✅', title: 'Inventory updated', time: '30 minutes ago', status: 'success' },
    { icon: '⚠️', title: 'Low stock alert', time: '1 hour ago', status: 'warning' },
    { icon: '📊', title: 'Daily report generated', time: '2 hours ago', status: 'success' }
  ]
};

let charts = {};

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
  updateTime();
  setInterval(updateTime, 1000);
  loadMetrics();
  initCharts();
  loadRecentActivity();
  loadTopProducts();
  generateSampleNotifications();
});

// Update time
function updateTime() {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
  const dateStr = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
  document.getElementById('currentTime').textContent = `${timeStr} • ${dateStr}`;
}

// Load metrics
function loadMetrics() {
  // Simulate daily sales accumulation
  const hours = new Date().getHours();
  const dailySales = Math.floor(Math.random() * 8000) + (hours * 500);

  document.getElementById("ridesSales").innerText = 12500;
  document.getElementById("rollerSales").innerText = 9800;
  document.getElementById("dinoSales").innerText = 7600;

  document.getElementById("monthlyRevenue").innerText = dashboardData.monthlyRevenue;
  document.getElementById("newCustomers").innerText = dashboardData.newCustomers;
  document.getElementById("attendanceRate").innerText = dashboardData.attendanceRate;

  // Quick stats
  document.getElementById('pendingOrders').textContent = 7;
  document.getElementById('totalStaff').textContent = 15;
  document.getElementById('activeBookings').textContent = 12;
  document.getElementById('inventoryCount').textContent = 245;
}

/* 
  document.getElementById("ridesSales").innerText = 12500;
  document.getElementById("rollerSales").innerText = 9800;
  document.getElementById("dinoSales").innerText = 7600;
  document.getElementById("monthlyRevenue").innerText = 152000;
  document.getElementById("newCustomers").innerText = 320;
  document.getElementById("attendanceRate").innerText = 94;


  document.getElementById('todaySales').textContent = dailySales.toFixed(2);
  document.getElementById('monthlyRevenue').textContent = dashboardData.monthlyRevenue.toFixed(2);
  document.getElementById('newCustomers').textContent = dashboardData.newCustomers;
  document.getElementById('attendanceRate').textContent = dashboardData.attendanceRate;

  // Quick stats
  document.getElementById('pendingOrders').textContent = Math.floor(Math.random() * 10) + 3;
  document.getElementById('totalStaff').textContent = 15;
  document.getElementById('activeBookings').textContent = Math.floor(Math.random() * 20) + 8;
  document.getElementById('inventoryCount').textContent = 245;
}
*/

// Initialize charts
function initCharts() {
  // Sales Chart
  const salesCtx = document.getElementById('salesChart').getContext('2d');
  charts.sales = new Chart(salesCtx, {
    type: 'line',
    data: {
      labels: ['6 AM', '9 AM', '12 PM', '3 PM', '6 PM', '9 PM', 'Now'],
      datasets: [{
        label: 'Sales (₱)',
        data: [2000, 5000, 8500, 7200, 9500, 6000, Math.random() * 10000],
        borderColor: '#667eea',
        backgroundColor: 'rgba(102, 126, 234, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointBackgroundColor: '#667eea'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  // Revenue Chart
  const revenueCtx = document.getElementById('revenueChart').getContext('2d');
  charts.revenue = new Chart(revenueCtx, {
    type: 'doughnut',
    data: {
      labels: ['Food', 'Beverages', 'Snacks', 'Others'],
      datasets: [{
        data: [40, 25, 20, 15],
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
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
}

// Load recent activity
function loadRecentActivity() {
  const activityContainer = document.getElementById('recentActivity');
  activityContainer.innerHTML = dashboardData.recentActivity.map(activity => `
    <div class="activity-item">
      <div class="activity-icon">${activity.icon}</div>
      <div class="activity-content">
        <div class="activity-title">${activity.title}</div>
        <div class="activity-time">${activity.time}</div>
      </div>
    </div>
  `).join('');
}

// Load top products
function loadTopProducts() {
  const container = document.getElementById('topProducts');
  container.innerHTML = dashboardData.topProducts.map(product => `
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
      <div style="font-weight: bold; margin-bottom: 5px;">${product.name}</div>
      <div style="font-size: 12px; opacity: 0.9;">${product.sales} sold</div>
      <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">₱${product.amount.toLocaleString()}</div>
    </div>
  `).join('');
}

// Generate sample notifications
function generateSampleNotifications() {
  const container = document.getElementById('notifications');
  const notifications = [
    { type: 'success', icon: '✅', title: 'System Status', message: 'All systems operating normally' },
    { type: 'warning', icon: '⚠️', title: 'Low Stock Alert', message: 'Coffee beans inventory is running low' },
    { type: 'info', icon: 'ℹ️', title: 'New Feature', message: 'Check out the new analytics dashboard' }
  ];

  container.innerHTML = notifications.map((notif, index) => `
    <div class="notification ${notif.type}" style="animation: slideIn 0.3s ease-in;">
      <div class="notification-icon">${notif.icon}</div>
      <div class="notification-content">
        <strong>${notif.title}</strong>
        <div>${notif.message}</div>
      </div>
      <button class="notification-close" onclick="this.parentElement.remove()">✕</button>
    </div>
  `).join('');
}

// Navigate to page
function navigateTo(page) {
  window.location.href = page;
}

// Add some CSS for notification animation
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(-100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
`;
document.head.appendChild(style);