<?php
include 'db_connect.php';

$barcode = $_POST['barcode'];
$sn = $_POST['sn'];

// 1. เช็คว่า S/N ซ้ำไหม
$check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
if($check->num_rows > 0) {
    echo json_encode(['status'=>'error', 'msg'=>'S/N นี้มีในระบบแล้ว!']);
    exit;
}

// 2. บันทึกลงตาราง S/N (สถานะ Available)
$insert = $conn->query("INSERT INTO product_serials (product_barcode, serial_number, status) VALUES ('$barcode', '$sn', 'available')");

if($insert) {
    // 3. เพิ่มจำนวนในตารางสินค้าหลัก (products)
    $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$barcode'");
    
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error', 'msg'=>'บันทึกไม่สำเร็จ: ' . $conn->error]);
}
?>