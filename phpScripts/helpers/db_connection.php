<?php
$config = include('./helpers/config.php');

// Create connection
$conn = new mysqli($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

return $conn;