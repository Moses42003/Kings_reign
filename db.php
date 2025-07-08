<?php

$hostname = 'localhost';
$user = 'root';
$pass = '';
$db = 'kings_reign';

$conn = new mysqli($hostname, $user, $pass, $db);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}