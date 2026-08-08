<?php 
$host = "sql103.infinityfree.com";
$dbname = "if0_41637652_logicloop";
$username = "if0_41637652";
$password = "6UNBshZDeH7A";
try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("Location: database_error");
    exit;
}
?>