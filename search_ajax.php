<?php
require_once 'ketnoi.php';

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";

$sql = "SELECT HoTen FROM nhanvien WHERE HoTen LIKE ? LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);

if(!$stmt){
    die("Lỗi SQL prepare: " . mysqli_error($conn));
}

$keywordLike = "%" . $keyword . "%";
mysqli_stmt_bind_param($stmt, "s", $keywordLike);

if(!mysqli_stmt_execute($stmt)){
    die("Lỗi SQL execute: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

if(!$result){
    die("Lỗi lấy dữ liệu: " . mysqli_error($conn));
}

while($row = mysqli_fetch_assoc($result)){
    echo "<div>" . htmlspecialchars($row['HoTen'], ENT_QUOTES, 'UTF-8') . "</div>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>