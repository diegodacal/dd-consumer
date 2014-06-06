<?php
$username = "WRITE IT HERE!";
$password = "WRITE IT HERE!";
$hostname = "WRITE IT HERE!"; 
$database = "WRITE IT HERE!";
//connection to the database
$dbhandle = mysqli_connect($hostname, $username, $password, $database)
  or die("Unable to connect to MySQL");
 mysqli_select_db($dbhandle, $database);
?>