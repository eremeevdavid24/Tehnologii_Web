<?php
session_start();
require "db.php";
header("Content-Type: application/json");

$action = $_GET['action'] ?? '';
$user = $_SESSION['user'] ?? null;

// ===== AUTENTIFICARE =====
if ($action == "login") {
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';

  $q = $conn->prepare("SELECT * FROM users WHERE email=?");
  $q->bind_param("s",$email);
  $q->execute();
  $u = $q->get_result()->fetch_assoc();

  if ($u && password_verify($pass, $u['password'])) {
    $_SESSION['user'] = $u;
    echo json_encode(["ok"=>1,"role"=>$u['role']]);
  } else {
    echo json_encode(["ok"=>0]);
  }
  exit;
}

// ===== CĂRȚI =====
if ($action == "books") {
  $search = $_GET['search'] ?? '';
  $query = "SELECT id, title, author, available FROM books WHERE 1=1";
  
  if ($search) {
    $search = '%' . $conn->real_escape_string($search) . '%';
    $query .= " AND (title LIKE '$search' OR author LIKE '$search')";
  }
  
  $r = $conn->query($query);
  echo json_encode($r->fetch_all(MYSQLI_ASSOC));
  exit;
}

// ===== DETALII CARTE =====
if ($action == "book") {
  $id = intval($_GET['id']);
  $r = $conn->query("SELECT * FROM books WHERE id=$id");
  echo json_encode($r->fetch_assoc() ?: []);
  exit;
}

// ===== ÎMPRUMUTA CARTE =====
if ($action == "borrow") {
  if (!$user) { echo json_encode(["ok"=>0,"msg"=>"Trebuie să fii autentificat"]); exit; }
  
  $bid = intval($_POST['book']);
  $uid = $user['id'];

  // Verifică dacă cartea este disponibilă
  $check = $conn->query("SELECT available FROM books WHERE id=$bid");
  $book = $check->fetch_assoc();
  
  if (!$book || $book['available'] <= 0) {
    echo json_encode(["ok"=>0,"msg"=>"Cartea nu este disponibilă"]);
    exit;
  }

  // Verifică dacă utilizatorul o are deja împrumutată
  $check = $conn->query("SELECT id FROM loans WHERE user_id=$uid AND book_id=$bid AND status='imprumutata'");
  if ($check->num_rows > 0) {
    echo json_encode(["ok"=>0,"msg"=>"Ai deja această carte împrumutată"]);
    exit;
  }

  $conn->query("UPDATE books SET available=available-1 WHERE id=$bid");
  $conn->query("INSERT INTO loans (user_id,book_id,status,borrow_date) VALUES($uid,$bid,'imprumutata',NOW())");
  
  echo json_encode(["ok"=>1,"msg"=>"Carte împrumutată cu succes"]);
  exit;
}

// ===== RETURNEAZĂ CARTE =====
if ($action == "return") {
  if (!$user) { echo json_encode(["ok"=>0,"msg"=>"Trebuie să fii autentificat"]); exit; }
  
  $lid = intval($_POST['loan']);
  $uid = $user['id'];

  // Obține cartea din împrumut
  // Bibliotecarul poate anula orice rezervare, cititorul doar pe ale sale
  if ($user['role'] == 'bibliotecar') {
    $q = $conn->query("SELECT book_id FROM loans WHERE id=$lid AND status='imprumutata'");
  } else {
    $q = $conn->query("SELECT book_id FROM loans WHERE id=$lid AND user_id=$uid AND status='imprumutata'");
  }
  
  $loan = $q->fetch_assoc();
  
  if (!$loan || !$loan['book_id']) {
    echo json_encode(["ok"=>0,"msg"=>"Împrumut invalid sau cartea nu a fost găsită"]);
    exit;
  }
  
  $book_id = intval($loan['book_id']);

  $conn->query("UPDATE loans SET status='anulata',return_date=NOW() WHERE id=$lid");
  $conn->query("UPDATE books SET available=available+1 WHERE id=$book_id");
  
  echo json_encode(["ok"=>1,"msg"=>"Rezervare anulată cu succes"]);
  exit;
}

// ===== ÎMPRUMUTURILE MELE =====
if ($action == "myloans") {
  if (!$user) { echo json_encode([]); exit; }
  
  $uid = $user['id'];
  $r = $conn->query("
    SELECT l.id, b.title, b.author, l.status, l.borrow_date, l.return_date, l.imprumut_date, l.pickup_date 
    FROM loans l 
    JOIN books b ON l.book_id=b.id 
    WHERE l.user_id=$uid 
    ORDER BY l.borrow_date DESC
  ");
  
  echo json_encode($r->fetch_all(MYSQLI_ASSOC));
  exit;
}

// ===== TOATE ÎMPRUMUTURILE (ADMIN) =====
if ($action == "allloans") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }
  
  // Verific și adaug coloana dacă nu există
  $checkColumn = $conn->query("SHOW COLUMNS FROM loans LIKE 'imprumut_date'");
  if ($checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE loans ADD COLUMN imprumut_date DATE DEFAULT NULL");
  }
  
  $filter = $_GET['filter'] ?? 'imprumutata_nemarcat';
  
  // Filtrare după status și imprumut_date
  if ($filter == 'all') {
    $whereClause = "l.status IN('imprumutata','returnata','anulata')";
  } elseif ($filter == 'imprumutata_nemarcat') {
    // Rezervate: status imprumutata dar fără imprumut_date
    $whereClause = "l.status='imprumutata' AND (l.imprumut_date IS NULL OR l.imprumut_date = '0000-00-00')";
  } elseif ($filter == 'imprumutata') {
    // Împrumutate: toate cărțile cu status imprumutata (inclusiv cele fără imprumut_date)
    $whereClause = "l.status='imprumutata'";
  } elseif ($filter == 'returnata') {
    // Returnate: cărți cu status imprumutata și imprumut_date setat (deja împrumutate, așteptând/returnate)
    $whereClause = "l.status='imprumutata' AND l.imprumut_date IS NOT NULL AND l.imprumut_date != '0000-00-00'";
  } else {
    $whereClause = "l.status='$filter'";
  }
  
  $r = $conn->query("
    SELECT l.id, l.user_id, u.name, u.email, b.id as book_id, b.title, l.status, l.borrow_date, l.return_date, l.imprumut_date, l.pickup_date 
    FROM loans l 
    JOIN books b ON l.book_id=b.id 
    JOIN users u ON l.user_id=u.id 
    WHERE $whereClause
    ORDER BY l.borrow_date DESC
  ");
  
  echo json_encode($r->fetch_all(MYSQLI_ASSOC));
  exit;
}

// ===== ADAUGA CARTE (ADMIN) =====
if ($action == "add_book") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }
  
  $title = $conn->real_escape_string($_POST['title'] ?? '');
  $author = $conn->real_escape_string($_POST['author'] ?? '');
  $available = intval($_POST['available'] ?? 1);
  
  if (!$title || !$author) {
    echo json_encode(["ok"=>0,"msg"=>"Completează toate câmpurile"]);
    exit;
  }
  
  $conn->query("INSERT INTO books (title, author, available) VALUES ('$title', '$author', $available)");
  
  echo json_encode(["ok"=>1,"msg"=>"Carte adăugată cu succes","id"=>$conn->insert_id]);
  exit;
}

// ===== EDITEAZĂ CARTE (ADMIN) =====
if ($action == "edit_book") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }
  
  $id = intval($_POST['id']);
  $title = $conn->real_escape_string($_POST['title'] ?? '');
  $author = $conn->real_escape_string($_POST['author'] ?? '');
  $available = intval($_POST['available'] ?? 0);
  
  if (!$title || !$author) {
    echo json_encode(["ok"=>0,"msg"=>"Completează toate câmpurile"]);
    exit;
  }
  
  $conn->query("UPDATE books SET title='$title', author='$author', available=$available WHERE id=$id");
  
  echo json_encode(["ok"=>1,"msg"=>"Carte actualizată cu succes"]);
  exit;
}

// ===== ȘTERGE CARTE (ADMIN) =====
if ($action == "delete_book") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }
  
  $id = intval($_POST['id']);
  $conn->query("DELETE FROM loans WHERE book_id=$id");
  $conn->query("DELETE FROM books WHERE id=$id");
  
  echo json_encode(["ok"=>1,"msg"=>"Carte ștearsă cu succes"]);
  exit;
}

// ===== STATISTICI (ADMIN) =====
if ($action == "stats") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }
  
  $stats = [];
  
  // Total cărți
  $r = $conn->query("SELECT COUNT(*) as count FROM books");
  $stats['total_books'] = $r->fetch_assoc()['count'];
  
  // Cărți disponibile
  $r = $conn->query("SELECT SUM(available) as count FROM books");
  $stats['available_books'] = $r->fetch_assoc()['count'] ?? 0;
  
  // Total utilizatori
  $r = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='cititor'");
  $stats['total_users'] = $r->fetch_assoc()['count'];
  
  // Împrumutări active
  $r = $conn->query("SELECT COUNT(*) as count FROM loans WHERE status='imprumutata'");
  $stats['active_loans'] = $r->fetch_assoc()['count'];
  
  // Împrumutări returnate
  $r = $conn->query("SELECT COUNT(*) as count FROM loans WHERE status='returnata'");
  $stats['returned_loans'] = $r->fetch_assoc()['count'];
  
  echo json_encode($stats);
  exit;
}

// ===== UPDATE PICKUP DATE =====
if ($action == "update_pickup") {
  if (!$user) { echo json_encode(["ok"=>0,"msg"=>"Nu ești autentificat"]); exit; }
  
  $loan_id = intval($_POST['loan_id'] ?? 0);
  $pickup_date = $_POST['pickup_date'] ?? '';
  $uid = $user['id'];
  
  // Verify the loan belongs to the user
  $check = $conn->query("SELECT id FROM loans WHERE id=$loan_id AND user_id=$uid AND status='imprumutata'");
  if ($check->num_rows == 0) {
    echo json_encode(["ok"=>0,"msg"=>"Rezervare invalidă"]);
    exit;
  }
  
  // Update pickup date (allow NULL if empty)
  if (empty($pickup_date)) {
    $stmt = $conn->prepare("UPDATE loans SET pickup_date=NULL WHERE id=?");
    $stmt->bind_param("i", $loan_id);
  } else {
    $stmt = $conn->prepare("UPDATE loans SET pickup_date=? WHERE id=?");
    $stmt->bind_param("si", $pickup_date, $loan_id);
  }
  $stmt->execute();
  
  echo json_encode(["ok"=>1,"msg"=>"Data preluării a fost salvată"]);
  exit;
}
// ===== MARCA ÎMPRUMUTARE (ADMIN) =====
if ($action == "mark_imprumut") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }
  
  $loan_id = intval($_POST['loan_id'] ?? 0);
  
  // Update imprumut_date to today
  $today = date('Y-m-d');
  $stmt = $conn->prepare("UPDATE loans SET imprumut_date=? WHERE id=?");
  $stmt->bind_param("si", $today, $loan_id);
  $stmt->execute();
  
  echo json_encode(["ok"=>1,"msg"=>"Carte marcată ca împrumutată"]);
  exit;
}

// ===== MARCA RETURNARE (ADMIN) =====
if ($action == "mark_returned") {
  if (!$user || $user['role'] != 'bibliotecar') { 
    echo json_encode(["ok"=>0,"msg"=>"Acces neautorizat"]); 
    exit; 
  }

  $loan_id = intval($_POST['loan_id'] ?? 0);

  // 1) Marchează returnarea doar dacă nu este deja marcată
  $today = date('Y-m-d H:i:s');
  $stmt = $conn->prepare("UPDATE loans SET return_date=? WHERE id=? AND (return_date IS NULL OR return_date='0000-00-00 00:00:00')");
  $stmt->bind_param("si", $today, $loan_id);
  $stmt->execute();

  // 2) Dacă s-a actualizat efectiv (nu era deja returnată), adaugă cartea înapoi în stoc
  if ($stmt->affected_rows > 0) {
    $stmt2 = $conn->prepare("SELECT book_id FROM loans WHERE id=?");
    $stmt2->bind_param("i", $loan_id);
    $stmt2->execute();
    $res = $stmt2->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if ($row && !empty($row['book_id'])) {
      $bookId = intval($row['book_id']);
      $conn->query("UPDATE books SET available=available+1 WHERE id=".$bookId);
    }
  }

  echo json_encode(["ok"=>1,"msg"=>"Cartea a fost marcată ca returnată"]);
  exit;
}