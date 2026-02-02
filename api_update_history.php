<?php
include 'db_connect.php';

$id = $_POST['id'];
$note = $_POST['note'];

// อัปเดตเฉพาะหมายเหตุ
$sql = "UPDATE product_history SET note = '$note' WHERE id = $id";

if($conn->query($sql)) {
    echo "success";
} else {
    echo "error: " . $conn->error;
}
?>