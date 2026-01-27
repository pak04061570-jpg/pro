<?php
include 'db_connect.php';

$id = $_POST['id'];
$barcode = $_POST['barcode'];

// ลบ S/N
$conn->query("DELETE FROM product_serials WHERE id = $id");

// ลดจำนวนสินค้าลง 1
$conn->query("UPDATE products SET quantity = quantity - 1 WHERE barcode = '$barcode'");

echo "success";
?>