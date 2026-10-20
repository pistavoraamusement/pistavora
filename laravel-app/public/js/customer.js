// Customer and booking data
let customers = JSON.parse(localStorage.getItem('customers')) || [];
let bookings = JSON.parse(localStorage.getItem('bookings')) || [
  { id: 1, name: 'Maria Garcia', email: 'maria@email.com', phone: '09123456789', date: '2026-05-10', time: '18:00', guests: 4, service: 'Dine-in', status: 'Confirmed', requests: 'Window seat preferred' }
];

let chatMessages = JSON.parse(localStorage.getItem('chatMessages')) || [
  { type: 'admin', text: 'Hello! How can I assist you today?' }
];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  loadBookings();
  loadChatMessages();
});

// Switch tabs
function switchTab(tabName) {
  // Hide all tab contents
  document.querySelectorAll('.tab-content').forEach(content => {
    content.classList.remove('active');
  });

  // Remove active class from all tabs
  document.querySelectorAll('.tab').forEach(tab => {
    tab.classList.remove('active');
  });

  // Show selected tab content
  document.getElementById(tabName).classList.add('active');
  event.target.classList.add('active');
}

// Add booking
function addBooking(event) {
  event.preventDefault();

  const booking = {
    id: Math.max(...bookings.map(b => b.id || 0), 0) + 1,
    name: document.getElementById('customerName').value,
    email: document.getElementById('customerEmail').value,
    phone: document.getElementById('customerPhone').value,
    date: document.getElementById('bookingDate').value,
    time: document.getElementById('bookingTime').value,
    guests: document.getElementById('guestCount').value,
    service: document.getElementById('serviceType').value,
    status: 'Pending',
    requests: document.getElementById('specialRequests').value
  };

  bookings.push(booking);
  localStorage.setItem('bookings', JSON.stringify(bookings));

  // Reset form
  event.target.reset();

  // Switch to bookings tab
  switchTab('all-bookings');
  loadBookings();

  alert('Booking created successfully!');
}

// Load bookings
function loadBookings() {
  const bookingsList = document.getElementById('bookingsList');
  
  if (bookings.length === 0) {
    bookingsList.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-calendar"></i>
        <p>No bookings yet</p>
      </div>
    `;
    return;
  }

  bookingsList.innerHTML = bookings.map(booking => `
    <div class="booking-card">
      <div class="booking-header">
        <div class="booking-name">${booking.name}</div>
        <span class="booking-status ${booking.status === 'Confirmed' ? 'status-confirmed' : booking.status === 'Pending' ? 'status-pending' : 'status-cancelled'}">
          ${booking.status}
        </span>
      </div>
      <div class="booking-details">
        <div>📅 ${booking.date} at ${booking.time}</div>
        <div>👥 ${booking.guests} guests • ${booking.service}</div>
        <div>📱 ${booking.phone}</div>
        <div>✉️ ${booking.email}</div>
        ${booking.requests ? `<div>📝 ${booking.requests}</div>` : ''}
      </div>
      <div class="booking-actions">
        <button onclick="confirmBooking(${booking.id})">Confirm</button>
        <button onclick="cancelBooking(${booking.id})">Cancel</button>
        <button onclick="editBooking(${booking.id})">Edit</button>
      </div>
    </div>
  `).join('');
}

// Filter bookings
function filterBookings() {
  const searchValue = document.getElementById('searchBooking').value.toLowerCase();
  const bookingsList = document.getElementById('bookingsList');

  const filtered = bookings.filter(b => b.name.toLowerCase().includes(searchValue));

  if (filtered.length === 0) {
    bookingsList.innerHTML = '<div class="empty-state">No bookings found</div>';
    return;
  }

  bookingsList.innerHTML = filtered.map(booking => `
    <div class="booking-card">
      <div class="booking-header">
        <div class="booking-name">${booking.name}</div>
        <span class="booking-status ${booking.status === 'Confirmed' ? 'status-confirmed' : 'status-pending'}">
          ${booking.status}
        </span>
      </div>
      <div class="booking-details">
        <div>📅 ${booking.date} at ${booking.time}</div>
        <div>👥 ${booking.guests} guests • ${booking.service}</div>
        <div>📱 ${booking.phone}</div>
      </div>
      <div class="booking-actions">
        <button onclick="confirmBooking(${booking.id})">Confirm</button>
        <button onclick="cancelBooking(${booking.id})">Cancel</button>
      </div>
    </div>
  `).join('');
}

// Confirm booking
function confirmBooking(id) {
  const booking = bookings.find(b => b.id === id);
  if (booking) {
    booking.status = 'Confirmed';
    localStorage.setItem('bookings', JSON.stringify(bookings));
    loadBookings();
    alert('Booking confirmed!');
  }
}

// Cancel booking
function cancelBooking(id) {
  const booking = bookings.find(b => b.id === id);
  if (booking) {
    booking.status = 'Cancelled';
    localStorage.setItem('bookings', JSON.stringify(bookings));
    loadBookings();
    alert('Booking cancelled!');
  }
}

// Edit booking
function editBooking(id) {
  alert('Edit functionality - can be implemented with a modal form');
}

// Load chat messages
function loadChatMessages() {
  const chatWindow = document.getElementById('chatWindow');
  chatWindow.innerHTML = '';

  chatMessages.forEach(msg => {
    const msgDiv = document.createElement('div');
    msgDiv.className = `chat-message ${msg.type}`;
    msgDiv.innerHTML = `${msg.type === 'admin' ? '<strong>Support: </strong>' : ''}${msg.text}`;
    chatWindow.appendChild(msgDiv);
  });

  // Scroll to bottom
  chatWindow.scrollTop = chatWindow.scrollHeight;
}

// Send message
function sendMessage() {
  const input = document.getElementById('chatInput');
  const text = input.value.trim();

  if (!text) return;

  // Add user message
  chatMessages.push({ type: 'user', text: text });
  input.value = '';

  // Simulate admin response
  setTimeout(() => {
    const responses = [
      'Thank you for your message! We\'ll get back to you shortly.',
      'I can help you with that. Can you provide more details?',
      'Great question! Our team is looking into it.',
      'Is there anything else I can assist you with?',
      'We appreciate your inquiry. Let me check that for you.'
    ];
    const randomResponse = responses[Math.floor(Math.random() * responses.length)];
    chatMessages.push({ type: 'admin', text: randomResponse });
  }, 1000);

  localStorage.setItem('chatMessages', JSON.stringify(chatMessages));
  loadChatMessages();
}

// Send quick reply
function sendQuickReply(text) {
  document.getElementById('chatInput').value = text;
  sendMessage();
}

// Handle chat keypress
function handleChatKeypress(event) {
  if (event.key === 'Enter') {
    sendMessage();
  }
}

// Filter customers
function filterCustomers() {
  const searchValue = document.getElementById('searchCustomer').value.toLowerCase();
  const customersList = document.getElementById('customersList');

  const filtered = customers.filter(c => 
    c.name.toLowerCase().includes(searchValue) ||
    c.email.toLowerCase().includes(searchValue)
  );

  if (filtered.length === 0) {
    customersList.innerHTML = '<div class="empty-state" style="grid-column: 1/-1;"><i class="fas fa-user"></i><p>No customers found</p></div>';
    return;
  }

  renderCustomers(filtered);
}

// Render customers
function renderCustomers(customersToShow) {
  const customersList = document.getElementById('customersList');
  customersList.innerHTML = customersToShow.map(customer => `
    <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 4px solid #667eea;">
      <div style="font-weight: bold; margin-bottom: 8px;">${customer.name}</div>
      <div style="font-size: 12px; color: #666; line-height: 1.6;">
        <div>📧 ${customer.email}</div>
        <div>📱 ${customer.phone}</div>
        <div>📅 Joined: ${customer.joinDate || 'N/A'}</div>
      </div>
    </div>
  `).join('');
}