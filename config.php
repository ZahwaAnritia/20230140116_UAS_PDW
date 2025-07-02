<?php

define('DB_SERVER', '127.0.0.1:3306');

define('DB_USERNAME', 'root'); 


define('DB_PASSWORD', ''); 


define('DB_NAME', 'uas_simprakt_db');


$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);


if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>
