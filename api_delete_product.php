<?php
include 'db_connect.php';

$id = $_POST['id'];

// 1. ดึง Barcode ของสินค้านี้มาก่อน
$query = $conn->query("SELECT barcode FROM products WHERE id=$id");
if($query->num_rows == 0) {
    echo json_encode(['status'=>'error', 'msg'=>'ไม่พบข้อมูลสินค้า']);
    exit;
}
$p = $query->fetch_assoc();
$barcode = $p['barcode'];

// 2. เช็คความปลอดภัย: มี S/N ค้างอยู่ในระบบไหม?
$check = $conn->query("SELECT * FROM product_serials WHERE product_barcode='$barcode'");

if($check->num_rows > 0) {
    // ถ้าเจอ S/N แม้แต่ตัวเดียว (ไม่ว่าจะวางอยู่ หรือขายไปแล้ว) ห้ามลบ!
    echo json_encode(['status'=>'error', 'msg'=>'ไม่สามารถลบได้! เนื่องจากมีประวัติ Serial Number ของสินค้านี้อยู่ในระบบ']);
} else {
    // ถ้าโล่งโจ้ง ลบได้เลย
    $conn->query("DELETE FROM products WHERE id=$id");
    echo json_encode(['status'=>'success']);
}
?>