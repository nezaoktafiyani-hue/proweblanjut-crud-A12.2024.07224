<?php 
session_start(); 
if (!isset($_SESSION["username"])) { 
    if (isset($_COOKIE["username"])) { 
        $_SESSION["username"] = $_COOKIE["username"]; 
    } else { 
        header("Location: login.php"); 
        exit(); 
    } 
} 
?> 
<h2>Selamat datang, <?php echo $_SESSION["username"]; ?>!</h2> 
<a href="logout.php">Logout</a> 