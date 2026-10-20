// Attendance data storage
let attendanceRecords = JSON.parse(localStorage.getItem('attendanceRecords')) || [
  { id: 1, date: '2026-05-05', empId: 'E001', name: 'John Doe', timeIn: '08:00', timeOut: '17:00', status: 'Present' },
  { id: 2, date: '2026-05-05', empId: 'E002', name: 'Jane Smith', timeIn: '08:15', timeOut: '17:30', status: 'Present' },
  { id: 3, date: '2026-05-05', empId: 'E003', name: 'Bob Johnson', timeIn: '09:00', timeOut: '17:00', status: 'Late' }
];

let importedData = [];

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
  loadAttendanceRecords();
  updateStats();
});

// Handle file upload
function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const content = e.target.result;
      importedData = parseCSVData(content);
      showMessage(`File loaded successfully! ${importedData.length} records found.`, 'success');
    } catch (error) {
      showMessage('Error parsing file: ' + error.message, 'error');
    }
  };
  reader.readAsText(file);
}

// Parse CSV data
function parseCSVData(content) {
  const lines = content.trim().split('\n');
  const data = [];

  for (let i = 1; i < lines.length; i++) {
    const [empId, name, timeIn, timeOut, status] = lines[i].split(',').map(s => s.trim());
    if (empId && name) {
      data.push({ empId, name, timeIn, timeOut, status: status || 'Present' });
    }
  }

  return data;
}

// Process attendance
function processAttendance() {
  if (importedData.length === 0) {
    showMessage('Please import a file first', 'error');
    return;
  }

  const today = new Date().toISOString().split('T')[0];

  importedData.forEach(record => {
    const existing = attendanceRecords.find(
      r => r.empId === record.empId && r.date === today
    );

    if (!existing) {
      attendanceRecords.push({
        id: Math.max(...attendanceRecords.map(r => r.id || 0)) + 1,
        date: today,
        empId: record.empId,
        name: record.name,
        timeIn: record.timeIn || '',
        timeOut: record.timeOut || '',
        status: record.status
      });
    }
  });

  localStorage.setItem('attendanceRecords', JSON.stringify(attendanceRecords));
  showMessage(`${importedData.length} records processed successfully!`, 'success');
  loadAttendanceRecords();
  updateStats();
  importedData = [];
}

// Time In
function timeIn() {
  const empId = document.getElementById('empId').value;
  const empName = document.getElementById('empName').value;

  if (!empId || !empName) {
    showMessage('Please enter Employee ID and Name', 'error');
    return;
  }

  const today = new Date().toISOString().split('T')[0];
  const timeInStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

  let record = attendanceRecords.find(r => r.empId === empId && r.date === today);

  if (record) {
    showMessage('Employee already timed in today', 'error');
    return;
  }

  attendanceRecords.push({
    id: Math.max(...attendanceRecords.map(r => r.id || 0)) + 1,
    date: today,
    empId: empId,
    name: empName,
    timeIn: timeInStr,
    timeOut: '',
    status: 'Present'
  });

  addLogEntry(empName, 'Time In', timeInStr, 'in');
  showMessage(`${empName} timed in at ${timeInStr}`, 'success');
  
  document.getElementById('empId').value = '';
  document.getElementById('empName').value = '';
  
  localStorage.setItem('attendanceRecords', JSON.stringify(attendanceRecords));
  updateStats();
}

// Time Out
function timeOut() {
  const empId = document.getElementById('empId').value;

  if (!empId) {
    showMessage('Please enter Employee ID', 'error');
    return;
  }

  const today = new Date().toISOString().split('T')[0];
  const timeOutStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

  let record = attendanceRecords.find(r => r.empId === empId && r.date === today);

  if (!record) {
    showMessage('Employee not found. Please time in first', 'error');
    return;
  }

  record.timeOut = timeOutStr;

  const timeInDate = new Date(`${today} ${record.timeIn}`);
  const timeOutDate = new Date(`${today} ${timeOutStr}`);
  const hours = (timeOutDate - timeInDate) / (1000 * 60 * 60);

  addLogEntry(record.name, 'Time Out', timeOutStr, 'out');
  showMessage(`${record.name} timed out at ${timeOutStr} (${hours.toFixed(1)} hrs)`, 'success');

  document.getElementById('empId').value = '';
  document.getElementById('empName').value = '';

  localStorage.setItem('attendanceRecords', JSON.stringify(attendanceRecords));
  loadAttendanceRecords();
  updateStats();
}

// Add log entry
function addLogEntry(name, action, time, type) {
  const logs = document.getElementById('logs');
  if (logs.innerHTML.includes('No entries')) {
    logs.innerHTML = '';
  }

  const entry = document.createElement('div');
  entry.className = 'log-entry';
  entry.innerHTML = `
    <span>${name}</span>
    <span class="log-time">${time}</span>
    <span class="log-status ${type === 'in' ? 'status-in' : 'status-out'}">${action}</span>
  `;
  logs.insertBefore(entry, logs.firstChild);
}

// Load attendance records
function loadAttendanceRecords() {
  const tbody = document.getElementById('attendanceRecords');
  tbody.innerHTML = '';

  attendanceRecords.forEach(record => {
    const hours = record.timeOut && record.timeIn 
      ? ((new Date(`2000-01-01 ${record.timeOut}`) - new Date(`2000-01-01 ${record.timeIn}`)) / (1000 * 60 * 60)).toFixed(1)
      : '–';

    const row = `
      <tr>
        <td>${record.date}</td>
        <td>${record.empId}</td>
        <td>${record.name}</td>
        <td>${record.timeIn || '–'}</td>
        <td>${record.timeOut || '–'}</td>
        <td><span style="background: ${record.status === 'Present' ? '#d1fae5' : '#fef3c7'}; color: ${record.status === 'Present' ? '#065f46' : '#92400e'}; padding: 4px 8px; border-radius: 15px; font-size: 11px; font-weight: bold;">${record.status}</span></td>
        <td>${hours} hrs</td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
}

// Update stats
function updateStats() {
  const today = new Date().toISOString().split('T')[0];
  const todayRecords = attendanceRecords.filter(r => r.date === today);
  
  document.getElementById('presentCount').textContent = todayRecords.filter(r => r.timeIn).length;
  document.getElementById('absentCount').textContent = Math.max(0, 15 - todayRecords.length);
}

// Filter by date
function filterByDate() {
  const filterDate = document.getElementById('filterDate').value;
  const tbody = document.getElementById('attendanceRecords');
  
  if (!filterDate) {
    loadAttendanceRecords();
    return;
  }

  tbody.innerHTML = '';
  const filtered = attendanceRecords.filter(r => r.date === filterDate);

  filtered.forEach(record => {
    const hours = record.timeOut && record.timeIn 
      ? ((new Date(`2000-01-01 ${record.timeOut}`) - new Date(`2000-01-01 ${record.timeIn}`)) / (1000 * 60 * 60)).toFixed(1)
      : '–';

    const row = `
      <tr>
        <td>${record.date}</td>
        <td>${record.empId}</td>
        <td>${record.name}</td>
        <td>${record.timeIn || '–'}</td>
        <td>${record.timeOut || '–'}</td>
        <td><span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 15px; font-size: 11px; font-weight: bold;">${record.status}</span></td>
        <td>${hours} hrs</td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
}

// Filter by employee
function filterByEmployee() {
  const searchValue = document.getElementById('searchEmployee').value.toLowerCase();
  const tbody = document.getElementById('attendanceRecords');
  
  tbody.innerHTML = '';
  const filtered = attendanceRecords.filter(r => r.name.toLowerCase().includes(searchValue) || r.empId.toLowerCase().includes(searchValue));

  filtered.forEach(record => {
    const hours = record.timeOut && record.timeIn 
      ? ((new Date(`2000-01-01 ${record.timeOut}`) - new Date(`2000-01-01 ${record.timeIn}`)) / (1000 * 60 * 60)).toFixed(1)
      : '–';

    const row = `
      <tr>
        <td>${record.date}</td>
        <td>${record.empId}</td>
        <td>${record.name}</td>
        <td>${record.timeIn || '–'}</td>
        <td>${record.timeOut || '–'}</td>
        <td><span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 15px; font-size: 11px; font-weight: bold;">${record.status}</span></td>
        <td>${hours} hrs</td>
      </tr>
    `;
    tbody.innerHTML += row;
  });
}

// Show message
function showMessage(text, type) {
  const messageDiv = document.getElementById('message');
  messageDiv.innerHTML = `<div class="message ${type}">${text}</div>`;
  setTimeout(() => {
    messageDiv.innerHTML = '';
  }, 4000);
}