<?php


// SERVER CONNECTION
// $hostname = 'sql206.ezyro.com';
// $user = 'ezyro_29068185';
// $pass = 'mc3a3pix';
// $db = 'ezyro_29068185_apply';

// // Create connection (no socket needed for remote host)
// $conn = new mysqli($hostname, $user, $pass, $db);


// LOCAL CONNECTION
$hostname = 'localhost';
$user = 'root';
$pass = '';
$db = 'db_kings_reign';

// Use XAMPP's MySQL socket
$conn = new mysqli($hostname, $user, $pass, $db, 3306, '/opt/lampp/var/mysql/mysql.sock');


if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}