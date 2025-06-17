<?php
session_start();

$host = "localhost";     
$dbname = "Rent_Ease";    
$username = "root";      
$password = "";           

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "conn suss";
?>
