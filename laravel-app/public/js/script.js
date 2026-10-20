function openModule(module) {
  const content = document.getElementById("moduleContent");

  if (module === "dashboard") {
    content.innerHTML = `
      <h2>Dashboard</h2>
      <p>Total Sales: ₱<span id="sales">0</span></p>
      <button onclick="increaseSales()">Add Sale</button>
    `;
  }

  else if (module === "pos") {
    content.innerHTML = `
      <h2>Point of Sale</h2>
      <input type="number" id="price" placeholder="Enter price">
      <button onclick="addToCart()">Add</button>
      <p>Total: ₱<span id="total">0</span></p>
    `;
  }

  else if (module === "calculation") {
    content.innerHTML = `
      <h2>Calculator</h2>
      <input id="num1" type="number">
      <input id="num2" type="number">
      <button onclick="calculate()">Add</button>
      <p>Result: <span id="result"></span></p>
    `;
  }

  else if (module === "customer") {
    content.innerHTML = `
      <h2>Customer Management</h2>
      <input id="customerName" placeholder="Customer Name">
      <button onclick="addCustomer()">Add</button>
      <ul id="customerList"></ul>
    `;
  }

  else if (module === "attendance") {
    content.innerHTML = `
      <h2>Attendance</h2>
      <button onclick="timeIn()">Time In</button>
      <ul id="attendanceList"></ul>
    `;
  }

  else if (module === "analytics") {
    content.innerHTML = `
      <h2>Analytics</h2>
      <button onclick="generateReport()">Generate Report</button>
      <p id="report"></p>
    `;
  }
}

/* DASHBOARD */
let sales = 0;
function increaseSales() {
  sales += 100;
  document.getElementById("sales").innerText = sales;
}

/* POS */
let total = 0;
function addToCart() {
  let price = parseFloat(document.getElementById("price").value);
  if (!isNaN(price)) {
    total += price;
    document.getElementById("total").innerText = total;
  }
}

/* CALCULATOR */
function calculate() {
  let n1 = parseFloat(document.getElementById("num1").value);
  let n2 = parseFloat(document.getElementById("num2").value);
  document.getElementById("result").innerText = n1 + n2;
}

/* CUSTOMER */
function addCustomer() {
  let name = document.getElementById("customerName").value;
  let li = document.createElement("li");
  li.textContent = name;
  document.getElementById("customerList").appendChild(li);
}

/* ATTENDANCE */
function timeIn() {
  let now = new Date().toLocaleTimeString();
  let li = document.createElement("li");
  li.textContent = "Time In: " + now;
  document.getElementById("attendanceList").appendChild(li);
}

/* ANALYTICS */
function generateReport() {
  document.getElementById("report").innerText =
    "Sales today: ₱" + total + " | Customers recorded.";
}