<?php
// config/db.php
function get_db() {
  $host = getenv('DB_HOST') ?: '127.0.0.1';
  $db   = getenv('DB_DATABASE') ?: 'pos_enterprise';
  $user = getenv('DB_USERNAME') ?: 'root';
  $pass = getenv('DB_PASSWORD') ?: '';
  $port = getenv('DB_PORT') ?: '3306';
  $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT         => false,
  ];
  return new PDO($dsn, $user, $pass, $options);
}
?>
