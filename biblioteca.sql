CREATE DATABASE biblioteca;
USE biblioteca;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('cititor','bibliotecar'),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150),
  author VARCHAR(100),
  available INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  book_id INT,
  status ENUM('imprumutata','returnata','anulata') DEFAULT 'imprumutata',
  borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  return_date DATETIME,
  pickup_date DATE,
  imprumut_date DATE,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (book_id) REFERENCES books(id)
);


INSERT INTO users (name, email, password, role) VALUES
('Bibliotecar','admin@lib.md','$2y$10$21iwCR665JXd3Vj/Nw71LOHkUga7creYAYWgyp7rBQy55/mgdQeiC','bibliotecar'),
('Ion Popa','user@lib.md','$2y$10$LEPWa6jIeEze0qrU3tD9suBIvoVYVg.t6PcW9O6m0t9MuadLuFkD2','cititor'),
('Maria Ionescu','maria@lib.md','$2y$10$LEPWa6jIeEze0qrU3tD9suBIvoVYVg.t6PcW9O6m0t9MuadLuFkD2','cititor');

INSERT INTO books (title, author, available) VALUES
('Baltagul','M. Sadoveanu',3),
('Amintiri din copilarie','I. Creanga',2),
('Muma Padurii','O. Goga',2),
('Ion','Liviu Rebreanu',1),
('Craiasele din Prapastii','Mircea Eliade',2),
('Harap Alb','Ion Creanga',1);

INSERT INTO loans (user_id, book_id, status, borrow_date, return_date) VALUES
(2, 1, 'imprumutata', DATE_SUB(NOW(), INTERVAL 5 DAY), NULL),
(2, 3, 'imprumutata', DATE_SUB(NOW(), INTERVAL 2 DAY), NULL),
(2, 5, 'returnata', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 2, 'imprumutata', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL),
(3, 4, 'returnata', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));
