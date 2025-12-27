<?php $servername = "localhost";
 $username = "root";
  $password = "";
   $dbname = "quanlynhansu"; 
   $conn = mysqli_connect($servername, $username, $password, $dbname); 
   if (!$conn) {
     die("Kết nối thất bại: " . mysqli_connect_error()); 
    } 
    mysqli_set_charset($conn, "utf8"); ?>