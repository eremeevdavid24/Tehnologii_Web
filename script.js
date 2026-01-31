// ===== LOGIN =====
function login() {
  const emailInput = document.getElementById('email');
  const passInput = document.getElementById('pass');
  
  fetch("api.php?action=login", {
    method: "POST",
    body: new URLSearchParams({
      email: emailInput.value,
      password: passInput.value
    })
  }).then(r=>r.json()).then(d=>{
    if(d.ok) {
      location.reload();
    } else {
      alert("❌ Date incorecte! Verifică email-ul și parola.");
    }
  });
}

// Support Enter key on password field
document.addEventListener('DOMContentLoaded', function() {
  const passField = document.getElementById('pass');
  if (passField) {
    passField.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') login();
    });
  }
});

// ===== TAB SWITCHING =====
if (document.querySelectorAll('.nav-btn').length > 0) {
  document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const tabName = this.dataset.tab;
      
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });
      
      // Remove active class from all buttons
      document.querySelectorAll('.nav-btn').forEach(b => {
        b.classList.remove('active');
      });
      
      // Show selected tab
      const selectedTab = document.getElementById(tabName + '-tab');
      if (selectedTab) {
        selectedTab.classList.add('active');
      }
      
      // Add active class to button
      this.classList.add('active');
      
      // Load data for the tab
      if (tabName === 'books') {
        loadBooks();
      } else if (tabName === 'myloans') {
        loadMyLoans();
      } else if (tabName === 'allloans') {
        loadAllLoans();
      } else if (tabName === 'manage') {
        loadManageBooks();
      } else if (tabName === 'stats') {
        loadStats();
      }
    });
  });
}

// ===== LOAD BOOKS =====
function loadBooks(search = '') {
  const url = search ? `api.php?action=books&search=${encodeURIComponent(search)}` : 'api.php?action=books';
  
  fetch(url)
    .then(r=>r.json())
    .then(books=>{
      let html = '';
      books.forEach(book=>{
        const available = book.available > 0;
        html += `
          <div class="book-card">
            <h3>${book.title}</h3>
            <p class="book-author">📖 ${book.author}</p>
            <div class="book-footer">
              <span class="availability ${!available ? 'unavailable' : ''}">
                ${available ? '✓' : '✗'} ${book.available} disponibil${book.available !== 1 ? 'e' : 'ă'}
              </span>
              <button onclick="borrowBook(${book.id})" class="btn-borrow" ${!available ? 'disabled' : ''}>
                ${available ? 'Reservă' : 'Indisponibil'}
              </button>
            </div>
          </div>
        `;
      });
      document.getElementById('books').innerHTML = html || '<p style="color: white;">Nicio carte găsită.</p>';
    });
}

// ===== SEARCH BOOKS =====
const searchInput = document.getElementById('search-input');
if (searchInput) {
  let searchTimeout;
  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      loadBooks(this.value);
    }, 300);
  });
}

// Load books on page load
if (document.getElementById('books')) {
  loadBooks();
}

// ===== BORROW BOOK =====
function borrowBook(id) {
  if (confirm('Sigur vrei să rezervi această carte?')) {
    fetch("api.php?action=borrow", {
      method: "POST",
      body: new URLSearchParams({ book: id })
    }).then(r=>r.json()).then(d=>{
      if (d.ok) {
        alert('✅ Carte rezervată cu succes!');
        loadBooks();
        if (document.getElementById('myloans')) {
          loadMyLoans();
        }
      } else {
        alert('❌ ' + d.msg);
      }
    });
  }
}

// ===== LOAD MY LOANS =====
function loadMyLoans() {
  fetch('api.php?action=myloans')
    .then(r=>r.json())
    .then(loans=>{
      if (loans.length === 0) {
        document.getElementById('myloans').innerHTML = '<p style="color: white; text-align: center; padding: 40px;">📭 Nu ai nici o carte rezervată.</p>';
        return;
      }
      
      let html = `
        <table>
          <thead>
            <tr>
              <th>📚 Titlu</th>
              <th>✍️ Autor</th>
              <th>📅 Rezervat la</th>
              <th>📋 Returnat la</th>
              <th>Status</th>
              <th>📦 Data preluării</th>
              <th>Acțiune</th>
            </tr>
          </thead>
          <tbody>
      `;
      
      loans.forEach(loan=>{
        // Status: prioritizăm stările derivate din date
        let statusBadge;
        if (loan.status === 'anulata') {
          statusBadge = '<span class="status-badge anulata">❌ Anulată</span>';
        } else if (loan.return_date) {
          statusBadge = '<span class="status-badge returnata">✅ Returnată</span>';
        } else {
          statusBadge = '<span class="status-badge imprumutata">📤 Rezervată</span>';
        }

        const borrowDate = new Date(loan.borrow_date).toLocaleDateString('ro-RO');
        const returnDate = (loan.status !== 'anulata' && loan.return_date)
          ? new Date(loan.return_date).toLocaleDateString('ro-RO')
          : '<span style="color: #9ca3af;">-</span>';

        // Buton acțiune: se poate anula doar dacă nu a fost returnată/anulată
        let actionBtn;
        if (loan.status === 'imprumutata' && !loan.return_date) {
          actionBtn = `<button class="btn-return" onclick="returnBook(${loan.id})">❌ Anulează rezervarea</button>`;
        } else if (loan.status === 'anulata') {
          actionBtn = '<span style="color: #6b7280;">Anulată</span>';
        } else {
          actionBtn = '<span style="color: #6b7280;">Returnată</span>';
        }

        // Data preluării: editor doar pentru rezervări active ne-returnate
        let pickupDate = '';
        if (loan.status === 'imprumutata' && !loan.return_date) {
          let dateValue = '';
          if (loan.pickup_date) {
            const dateObj = new Date(loan.pickup_date);
            dateValue = dateObj.toISOString().split('T')[0];
          }
          pickupDate = `<input type="date" class="date-input" id="pickup-${loan.id}" value="${dateValue}" onchange="updatePickupDate(${loan.id}, this.value)" min="${new Date().toISOString().split('T')[0]}">`;
        } else {
          pickupDate = loan.pickup_date ? new Date(loan.pickup_date).toLocaleDateString('ro-RO') : '<span style="color: #9ca3af;">-</span>';
        }
        
        html += `
          <tr>
            <td><strong>${loan.title}</strong></td>
            <td>${loan.author}</td>
            <td>${borrowDate}</td>
            <td>${returnDate}</td>
            <td>${statusBadge}</td>
            <td>${pickupDate}</td>
            <td>${actionBtn}</td>
          </tr>
        `;
      });
      
      html += '</tbody></table>';
      document.getElementById('myloans').innerHTML = html;
    });
}

// ===== LOAD ALL LOANS (ADMIN) =====
let currentLoanFilter = 'imprumutata_nemarcat';

function loadAllLoans() {
  fetch(`api.php?action=allloans&filter=${currentLoanFilter}`)
    .then(r=>r.json())
    .then(loans=>{
      if (!loans || loans.length === 0) {
        document.getElementById('allloans').innerHTML = '<p style="color: white;">Nicio rezervare găsită.</p>';
        return;
      }
      
      // Determină titlul coloanei și butonului în funcție de filtru
      const showImprumutaBtn = currentLoanFilter === 'imprumutata_nemarcat';
      
      let html = '';
      
      // Secțiunea Împrumutate are coloane diferite
      if (currentLoanFilter === 'imprumutata') {
        html = `
          <table>
            <thead>
              <tr>
                <th>👤 Utilizator</th>
                <th>📧 Email</th>
                <th>📚 Carte</th>
                <th>📅 Data rezervării</th>
                <th>📅 Data împrumutării</th>
                <th>Status</th>
                <th>Acțiune</th>
              </tr>
            </thead>
            <tbody>
        `;
      } else if (currentLoanFilter === 'returnata') {
        // Secțiunea Returnate
        html = `
          <table>
            <thead>
              <tr>
                <th>👤 Utilizator</th>
                <th>📧 Email</th>
                <th>📚 Carte</th>
                <th>📅 Data rezervării</th>
                <th>📅 Data returnării</th>
                <th>Status</th>
                <th>Acțiune</th>
              </tr>
            </thead>
            <tbody>
        `;
      } else if (currentLoanFilter === 'imprumutata_nemarcat') {
        // Secțiunea Rezervate - fără Data împrumutării și Data returnării
        html = `
          <table>
            <thead>
              <tr>
                <th>👤 Utilizator</th>
                <th>📧 Email</th>
                <th>📚 Carte</th>
                <th>📅 Data rezervare</th>
                <th>📦 Data preluării</th>
                <th>Status</th>
                <th>Acțiune</th>
              </tr>
            </thead>
            <tbody>
        `;
      } else {
        // Celelalte secțiuni păstrează structura originală
        html = `
          <table>
            <thead>
              <tr>
                <th>👤 Utilizator</th>
                <th>📧 Email</th>
                <th>📚 Carte</th>
                <th>📅 Data rezervare</th>
                <th>📦 Data preluării</th>
                <th>📅 Data împrumutării</th>
                <th>📅 Data returnării</th>
                <th>Status</th>
                <th>Acțiune</th>
              </tr>
            </thead>
            <tbody>
        `;
      }
      
      loans.forEach(loan=>{
        // Determină statusul corect bazat pe context
        let statusBadge;
        if (currentLoanFilter === 'imprumutata') {
          // În secțiunea Împrumutate, afișează gol până se marchează împrumutul
          if (loan.imprumut_date) {
            statusBadge = '<span class="status-badge imprumutata">📤 Împrumutată</span>';
          } else {
            statusBadge = '<span style="color: #6b7280;">-</span>';
          }
        } else if (loan.status === 'imprumutata') {
          if (loan.imprumut_date && loan.return_date) {
            // Cărți cu return_date = returnate efectiv
            statusBadge = '<span class="status-badge returnata">✅ Returnată</span>';
          } else if (loan.imprumut_date) {
            // Cărți cu imprumut_date = deja împrumutate
            statusBadge = '<span class="status-badge imprumutata">📤 Împrumutată</span>';
          } else {
            // Cărți fără imprumut_date = doar rezervate
            statusBadge = '<span class="status-badge imprumutata">📋 Rezervată</span>';
          }
        } else if (loan.status === 'anulata') {
          statusBadge = '<span class="status-badge anulata">❌ Anulată</span>';
        } else {
          statusBadge = '<span class="status-badge returnata">✅ Returnată</span>';
        }
        
        const borrowDate = new Date(loan.borrow_date).toLocaleDateString('ro-RO');
        const pickupDate = loan.pickup_date ? new Date(loan.pickup_date).toLocaleDateString('ro-RO') : '<span style="color: #9ca3af;">-</span>';
        const imprumutDate = loan.imprumut_date ? new Date(loan.imprumut_date).toLocaleDateString('ro-RO') : '<span style="color: #9ca3af;">-</span>';
        const returnDate = loan.return_date ? new Date(loan.return_date).toLocaleDateString('ro-RO') : '<span style="color: #9ca3af;">-</span>';
        
        let actionBtn;
        if (showImprumutaBtn && loan.status === 'imprumutata') {
          // Secțiunea Rezervate - doar buton de anulare
          actionBtn = `<button class="btn-return" onclick="adminCancelReservation(${loan.id})">❌ Anulează rezervarea</button>`;
        } else if (currentLoanFilter === 'imprumutata' && loan.status === 'imprumutata' && !loan.imprumut_date) {
          // Secțiunea Împrumutate - buton de marcare pentru cărțile nepreluate
          actionBtn = `<button class="btn-return" onclick="markAsImprumut(${loan.id})">📤 Împrumută</button>`;
        } else if (currentLoanFilter === 'imprumutata' && loan.status === 'imprumutata' && loan.imprumut_date && !loan.return_date) {
          // Secțiunea Împrumutate - fără buton Returnează
          actionBtn = '<span style="color: #6b7280;">-</span>';
        } else if (currentLoanFilter === 'returnata' && loan.status === 'imprumutata' && loan.imprumut_date && !loan.return_date) {
          // Secțiunea Returnate (Așteptând returnare) - buton de returnare
          actionBtn = `<button class="btn-return" onclick="returnLoan(${loan.id})">📥 Returnează</button>`;
        } else {
          actionBtn = '<span style="color: #6b7280;">-</span>';
        }
        
        // Alege datele corecte în funcție de secțiune
        let dateColumn1, dateColumn2;
        if (currentLoanFilter === 'imprumutata') {
          dateColumn1 = borrowDate;
          dateColumn2 = imprumutDate;
        } else if (currentLoanFilter === 'returnata') {
          dateColumn1 = imprumutDate;
          dateColumn2 = returnDate;
        } else {
          // Pentru "Toate" și altele - afișează toate coloanele
          dateColumn1 = borrowDate;
          dateColumn2 = returnDate;
        }
        
        // Construiește randurile în funcție de secțiune
        if (currentLoanFilter === 'imprumutata_nemarcat') {
          // Secțiunea "Rezervate" - afișează data rezervării și data preluării
          html += `
            <tr>
              <td>${loan.name}</td>
              <td>${loan.email}</td>
              <td>${loan.title}</td>
              <td>${borrowDate}</td>
              <td>${pickupDate}</td>
              <td>${statusBadge}</td>
              <td>${actionBtn}</td>
            </tr>
          `;
        } else {
          // Celelalte secțiuni - structura normală
          html += `
            <tr>
              <td>${loan.name}</td>
              <td>${loan.email}</td>
              <td>${loan.title}</td>
              <td>${dateColumn1}</td>
              <td>${dateColumn2}</td>
              <td>${statusBadge}</td>
              <td>${actionBtn}</td>
            </tr>
          `;
        }
      });
      
      html += '</tbody></table>';
      document.getElementById('allloans').innerHTML = html;
    });
}

function filterLoans(status) {
  currentLoanFilter = status;
  document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  loadAllLoans();
}

// ===== MANAGE BOOKS (ADMIN) =====
function loadManageBooks() {
  fetch('api.php?action=books')
    .then(r=>r.json())
    .then(books=>{
      let html = '';
      books.forEach(book=>{
        html += `
          <div class="book-card">
            <h3>${book.title}</h3>
            <p class="book-author">✍️ ${book.author}</p>
            <p style="color: #6b7280; font-size: 14px;">Disponibile: ${book.available}</p>
            <div class="book-footer">
              <button class="btn-edit" onclick="editBook(${book.id}, '${book.title}', '${book.author}', ${book.available})">✏️ Editează</button>
              <button class="btn-delete" onclick="deleteBook(${book.id})">🗑️ Șterge</button>
            </div>
          </div>
        `;
      });
      document.getElementById('manage-books').innerHTML = html;
    });
}

// ===== ADD BOOK (ADMIN) =====
function showAddBookForm() {
  document.getElementById('add-book-form').classList.remove('hidden');
}

function closeAddBookForm() {
  document.getElementById('add-book-form').classList.add('hidden');
  document.getElementById('new-title').value = '';
  document.getElementById('new-author').value = '';
  document.getElementById('new-available').value = '1';
}

function addBook() {
  const title = document.getElementById('new-title').value;
  const author = document.getElementById('new-author').value;
  const available = document.getElementById('new-available').value;
  
  if (!title || !author) {
    alert('❌ Completează toate câmpurile!');
    return;
  }
  
  fetch('api.php?action=add_book', {
    method: 'POST',
    body: new URLSearchParams({ title, author, available })
  }).then(r=>r.json()).then(d=>{
    if (d.ok) {
      alert('✅ ' + d.msg);
      closeAddBookForm();
      loadManageBooks();
    } else {
      alert('❌ ' + d.msg);
    }
  });
}

// ===== EDIT BOOK (ADMIN) =====
function editBook(id, title, author, available) {
  const newTitle = prompt('Titlu:', title);
  if (newTitle === null) return;
  
  const newAuthor = prompt('Autor:', author);
  if (newAuthor === null) return;
  
  const newAvailable = prompt('Exemplare disponibile:', available);
  if (newAvailable === null) return;
  
  fetch('api.php?action=edit_book', {
    method: 'POST',
    body: new URLSearchParams({ id, title: newTitle, author: newAuthor, available: newAvailable })
  }).then(r=>r.json()).then(d=>{
    if (d.ok) {
      alert('✅ ' + d.msg);
      loadManageBooks();
    } else {
      alert('❌ ' + d.msg);
    }
  });
}

// ===== DELETE BOOK (ADMIN) =====
function deleteBook(id) {
  if (confirm('Sigur vrei să ștergi această carte? Aceasta va șterge și toate împrumuturile asociate!')) {
    fetch('api.php?action=delete_book', {
      method: 'POST',
      body: new URLSearchParams({ id })
    }).then(r=>r.json()).then(d=>{
      if (d.ok) {
        alert('✅ ' + d.msg);
        loadManageBooks();
      } else {
        alert('❌ ' + d.msg);
      }
    });
  }
}

// ===== RETURN BOOK =====
function returnBook(id) {
  if (confirm('Sigur vrei să anulezi această rezervare?')) {
    fetch('api.php?action=return', {
      method: 'POST',
      body: new URLSearchParams({ loan: id })
    }).then(r=>r.json()).then(d=>{
      if (d.ok) {
        alert('✅ ' + d.msg);
        loadMyLoans();
        loadBooks();
      } else {
        alert('❌ ' + d.msg);
      }
    });
  }
}

// ===== ADMIN CANCEL RESERVATION =====
function adminCancelReservation(id) {
  if (confirm('Sigur vrei să anulezi această rezervare?')) {
    fetch('api.php?action=return', {
      method: 'POST',
      body: new URLSearchParams({ loan: id })
    }).then(r=>r.json()).then(d=>{
      if (d.ok) {
        alert('✅ ' + d.msg);
        loadAllLoans();
        loadBooks();
      } else {
        alert('❌ ' + d.msg);
      }
    });
  }
}

// ===== MARK AS IMPRUMUT =====
function markAsImprumut(id) {
  if (confirm('Sigur vrei să marchezi această carte ca împrumutată?')) {
    fetch('api.php?action=mark_imprumut', {
      method: 'POST',
      body: new URLSearchParams({ loan_id: id })
    }).then(r=>r.json()).then(d=>{
      if (d.ok) {
        alert('✅ ' + d.msg);
        // După împrumut comută pe secțiunea "Împrumutate" și afișează statutul "Împrumutată"
        currentLoanFilter = 'imprumutata';
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        const targetBtn = document.querySelector(`.filter-btn[onclick*="filterLoans('imprumutata')"]`);
        if (targetBtn) targetBtn.classList.add('active');
        loadAllLoans();
      } else {
        alert('❌ ' + d.msg);
      }
    });
  }
}

// ===== RETURN LOAN =====
function returnLoan(id) {
  if (confirm('Sigur vrei să marchezi această carte ca returnată?')) {
    fetch('api.php?action=mark_returned', {
      method: 'POST',
      body: new URLSearchParams({ loan_id: id })
    }).then(r=>r.json()).then(d=>{
      if (d.ok) {
        alert('✅ ' + d.msg);
        loadAllLoans();
        // Reîmprospătează și lista de cărți, pentru a vedea stocul actualizat
        if (document.getElementById('books')) {
          loadBooks();
        }
      } else {
        alert('❌ ' + d.msg);
      }
    });
  }
}

// ===== UPDATE PICKUP DATE =====
function updatePickupDate(loanId, date) {
  if (!date) return;
  
  console.log('Salvare data preluării:', loanId, date);
  
  fetch('api.php?action=update_pickup', {
    method: 'POST',
    body: new URLSearchParams({ loan_id: loanId, pickup_date: date })
  }).then(r=>r.json()).then(d=>{
    console.log('Răspuns API:', d);
    if (d.ok) {
      // Reîncarcă datele pentru a afișa schimbările
      loadMyLoans();
    } else {
      alert('❌ ' + d.msg);
    }
  }).catch(err => {
    console.error('Eroare:', err);
    alert('❌ Eroare la salvarea datei');
  });
}

// ===== CLEAR PICKUP DATE =====
function clearPickupDate(loanId) {
  fetch('api.php?action=update_pickup', {
    method: 'POST',
    body: new URLSearchParams({ loan_id: loanId, pickup_date: '' })
  }).then(r=>r.json()).then(d=>{
    loadMyLoans();
  });
}

// ===== LOAD STATS (ADMIN) =====
function loadStats() {
  fetch('api.php?action=stats')
    .then(r=>r.json())
    .then(stats=>{
      let html = `
        <div class="stat-card total">
          <div class="stat-label">📚 Total Cărți</div>
          <div class="stat-value">${stats.total_books}</div>
        </div>
        <div class="stat-card available">
          <div class="stat-label">✓ Cărți Disponibile</div>
          <div class="stat-value">${stats.available_books}</div>
        </div>
        <div class="stat-card users">
          <div class="stat-label">👥 Total Cititori</div>
          <div class="stat-value">${stats.total_users}</div>
        </div>
        <div class="stat-card active">
          <div class="stat-label">📤 Rezervări Active</div>
          <div class="stat-value">${stats.active_loans}</div>
        </div>
        <div class="stat-card returned">
          <div class="stat-label">📥 Cărți Returnate</div>
          <div class="stat-value">${stats.returned_loans}</div>
        </div>
      `;
      document.getElementById('stats').innerHTML = html;
    });
}
