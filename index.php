<?php 
session_start();
require "db.php"; 
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bibliotecă Online</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php if (!isset($_SESSION['user'])): ?>
<div class="login-container">
  <div class="card">
    <div class="card-header">
      <div class="book-icon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M4 19.5C4 18.837 4.5 18 5.5 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M6.5 2H20V22H6.5C5.5 22 4 21.163 4 19.5V4.5C4 2.837 5.5 2 6.5 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M8 6H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M8 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          <path d="M8 14H12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <h2>Bine ai venit!</h2>
      <p class="subtitle">Conectează-te la Biblioteca Online</p>
    </div>
    <div class="card-body">
      <div class="input-group">
        <label for="email">Nume de utilizator</label>
        <input id="email" type="email" autocomplete="email">
      </div>
      <div class="input-group">
        <label for="pass">Parola</label>
        <input id="pass" type="password"  autocomplete="current-password">
      </div>
      <button onclick="login()" class="btn-primary">
        <span>Autentificare</span>
        <svg class="arrow" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>
</div>

<?php else: ?>
<!-- DASHBOARD -->
<div class="dashboard">
  <!-- HEADER -->
  <header class="dashboard-header">
    <div class="container">
      <div class="header-content">
        <h1>📚 Biblioteca Online</h1>
        <div class="user-menu">
          <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?></div>
            <div>
              <p class="user-name"><?= $_SESSION['user']['name'] ?></p>
              <p class="user-role"><?= $_SESSION['user']['role'] == 'bibliotecar' ? '👨‍💼 Bibliotecar' : '👤 Cititor' ?></p>
            </div>
          </div>
          <a href="logout.php" class="btn-logout">Deconectare</a>
        </div>
      </div>
    </div>
  </header>
  
  <!-- NAVIGATION -->
  <nav class="dashboard-nav">
    <div class="container">
      <button class="nav-btn active" data-tab="books">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M4 19.5C4 18.837 4.5 18 5.5 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M6.5 2H20V22H6.5C5.5 22 4 21.163 4 19.5V4.5C4 2.837 5.5 2 6.5 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Cărți
      </button>
      
      <?php if ($_SESSION['user']['role'] == 'cititor'): ?>
        <button class="nav-btn" data-tab="myloans">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Rezervările mele
        </button>
      <?php else: ?>
        <button class="nav-btn" data-tab="allloans">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Toate rezervările
        </button>
        <button class="nav-btn" data-tab="manage">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Gestionare cărți
        </button>
        <button class="nav-btn" data-tab="stats">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 3v18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18 17V9M12 17V5M6 17v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Statistici
        </button>
      <?php endif; ?>
    </div>
  </nav>
  
  <!-- MAIN CONTENT -->
  <main class="container dashboard-main">
    <!-- BOOKS TAB -->
    <div id="books-tab" class="tab-content active">
      <div class="search-box">
        <input id="search-input" type="text" placeholder="🔍 Caută cărți după titlu sau autor...">
      </div>
      <div id="books" class="books-grid"></div>
    </div>
    
    <!-- MY LOANS TAB (READER) -->
    <?php if ($_SESSION['user']['role'] == 'cititor'): ?>
    <div id="myloans-tab" class="tab-content">
      <h2>Rezervările mele</h2>
      <div id="myloans" class="loans-table"></div>
    </div>
    <?php else: ?>
    <!-- ALL LOANS TAB (ADMIN) -->
    <div id="allloans-tab" class="tab-content">
      <h2>Toate rezervările</h2>
      <div class="filter-buttons">
        <button onclick="filterLoans('imprumutata_nemarcat')" class="filter-btn active">Rezervate</button>
        <button onclick="filterLoans('imprumutata')" class="filter-btn">Împrumutate</button>
        <button onclick="filterLoans('returnata')" class="filter-btn">Returnate</button>
      </div>
      <div id="allloans" class="loans-table"></div>
    </div>
    
    <!-- MANAGE BOOKS TAB (ADMIN) -->
    <div id="manage-tab" class="tab-content">
      <h2>Gestionare cărți</h2>
      <button onclick="showAddBookForm()" class="btn-primary btn-add">
        <span>➕ Adaugă carte nouă</span>
      </button>
      <div id="add-book-form" class="modal hidden">
        <div class="modal-content">
          <span class="close" onclick="closeAddBookForm()">&times;</span>
          <h3>Adaugă carte nouă</h3>
          <input id="new-title" placeholder="Titlu" type="text">
          <input id="new-author" placeholder="Autor" type="text">
          <input id="new-available" placeholder="Exemplare disponibile" type="number" value="1">
          <button onclick="addBook()" class="btn-primary">Adaugă</button>
        </div>
      </div>
      <div id="manage-books" class="books-grid"></div>
    </div>
    
    <!-- STATS TAB (ADMIN) -->
    <div id="stats-tab" class="tab-content">
      <h2>Statistici</h2>
      <div id="stats" class="stats-grid"></div>
    </div>
    <?php endif; ?>
  </main>
</div>
<?php endif; ?>

<script>
  window.USER_ROLE = '<?= $_SESSION['user']['role'] ?>';
</script>
<script src="script.js?v=10"></script>
</body>
</html>
