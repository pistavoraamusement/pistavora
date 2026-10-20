// Spreadsheet data
let sheetData = [];
let rows = 10;
let cols = 5;

// Templates
const templates = {
  sales: [
    ['Date', 'Product', 'Quantity', 'Price', 'Total'],
    ['2026-05-01', 'Coffee', '50', '120', '=B2*C2*D2'],
    ['2026-05-02', 'Tea', '30', '80', '=B3*C3*D3'],
    ['2026-05-03', 'Juice', '40', '100', '=B4*C4*D4']
  ],
  expenses: [
    ['Date', 'Category', 'Description', 'Amount', 'Status'],
    ['2026-05-01', 'Supplies', 'Office supplies', '5000', 'Paid'],
    ['2026-05-02', 'Utilities', 'Electricity', '3000', 'Pending'],
    ['2026-05-03', 'Rent', 'Office rent', '20000', 'Paid']
  ],
  inventory: [
    ['Item', 'Category', 'Quantity', 'Unit Price', 'Stock Value'],
    ['Coffee Beans', 'Beverages', '100', '500', '=C2*D2'],
    ['Tea Leaves', 'Beverages', '150', '200', '=C3*D3'],
    ['Flour', 'Supplies', '200', '50', '=C4*D4']
  ],
  blank: Array(10).fill(null).map(() => Array(5).fill(''))
};

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
  loadTemplate('blank');
});

// Load template
function loadTemplate(templateName) {
  const template = templates[templateName];
  sheetData = template.map(row => [...row]);
  rows = sheetData.length;
  cols = sheetData[0] ? sheetData[0].length : 5;
  renderSpreadsheet();
}

// Render spreadsheet
function renderSpreadsheet() {
  const spreadsheet = document.getElementById('spreadsheet');
  spreadsheet.innerHTML = '';

  // Header row
  const headerRow = document.createElement('tr');
  headerRow.innerHTML = '<th></th>';
  for (let i = 0; i < cols; i++) {
    headerRow.innerHTML += `<th>${String.fromCharCode(65 + i)}</th>`;
  }
  spreadsheet.appendChild(headerRow);

  // Data rows
  for (let i = 0; i < rows; i++) {
    const row = document.createElement('tr');
    row.innerHTML = `<th class="row-header">${i + 1}</th>`;

    for (let j = 0; j < cols; j++) {
      const cell = document.createElement('td');
      const input = document.createElement('input');

      input.value = sheetData[i]?.[j] || '';
      input.onchange = () => updateCell(i, j, input.value);
      input.onblur = () => calculateSummary();

      if (input.value.startsWith('=')) {
        input.classList.add('cell-formula');
      }

      cell.appendChild(input);
      row.appendChild(cell);
    }

    spreadsheet.appendChild(row);
  }

  calculateSummary();
}

// Update cell
function updateCell(row, col, value) {
  if (!sheetData[row]) sheetData[row] = [];
  sheetData[row][col] = value;
}

// Calculate summary
function calculateSummary() {
  let numbers = [];

  for (let i = 0; i < rows; i++) {
    for (let j = 0; j < cols; j++) {
      const value = sheetData[i]?.[j];
      let num = parseFloat(value);

      // Try to evaluate formula
      if (typeof value === 'string' && value.startsWith('=')) {
        try {
          const formula = value.substring(1);
          const result = evaluateFormula(formula, sheetData);
          num = result;
        } catch (e) {
          num = NaN;
        }
      }

      if (!isNaN(num)) {
        numbers.push(num);
      }
    }
  }

  const sum = numbers.reduce((a, b) => a + b, 0);
  const avg = numbers.length > 0 ? sum / numbers.length : 0;
  const min = numbers.length > 0 ? Math.min(...numbers) : 0;
  const max = numbers.length > 0 ? Math.max(...numbers) : 0;

  document.getElementById('sumValue').textContent = sum.toFixed(2);
  document.getElementById('avgValue').textContent = avg.toFixed(2);
  document.getElementById('minValue').textContent = min.toFixed(2);
  document.getElementById('maxValue').textContent = max.toFixed(2);
  document.getElementById('countValue').textContent = numbers.length;
}

// Evaluate formula
function evaluateFormula(formula, data) {
  // Replace cell references like B2, C3 with actual values
  let result = formula;

  // Match patterns like A1, B2, etc.
  const cellRegex = /([A-Z])(\d+)/g;
  result = result.replace(cellRegex, (match, col, row) => {
    const colIndex = col.charCodeAt(0) - 65;
    const rowIndex = parseInt(row) - 1;
    const value = data[rowIndex]?.[colIndex] || 0;
    const num = parseFloat(value);
    return isNaN(num) ? 0 : num;
  });

  try {
    return eval(result);
  } catch (e) {
    return NaN;
  }
}

// Insert row
function insertRow() {
  sheetData.push(Array(cols).fill(''));
  rows++;
  renderSpreadsheet();
}

// Insert column
function insertColumn() {
  sheetData.forEach(row => {
    row.push('');
  });
  cols++;
  renderSpreadsheet();
}

// Delete row
function deleteRow() {
  if (rows > 1) {
    sheetData.pop();
    rows--;
    renderSpreadsheet();
  }
}

// Clear sheet
function clearSheet() {
  if (confirm('Are you sure you want to clear all data?')) {
    sheetData = Array(rows).fill(null).map(() => Array(cols).fill(''));
    renderSpreadsheet();
  }
}

// Export to CSV
function exportCSV() {
  let csv = '';
  sheetData.forEach(row => {
    csv += row.join(',') + '\n';
  });

  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'spreadsheet.csv';
  a.click();
}

// Save sheet
function saveSheet() {
  document.getElementById('saveModal').classList.add('show');
}

// Confirm save
function confirmSave() {
  const fileName = document.getElementById('fileName').value || 'spreadsheet';
  const data = JSON.stringify(sheetData);
  localStorage.setItem('sheet_' + fileName, data);
  alert('Spreadsheet saved as: ' + fileName);
  closeModal('saveModal');
}

// Close modal
function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('show');
}

// Print sheet
function printSheet() {
  window.print();
}