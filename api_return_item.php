<?php
include 'db_connect.php';

$sn = $_POST['sn'];

// 1. เช็คข้อมูล S/N ก่อน
$check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
if($check->num_rows == 0) {
    echo json_encode(['status'=>'error', 'msg'=>'ไม่พบ S/N นี้ในระบบ']);
    exit;
}
$item = $check->fetch_assoc();

// 2. เช็คว่าคืนไปแล้วหรือยัง? (กันเหนียว)
if($item['status'] == 'available') {
    echo json_encode(['status'=>'error', 'msg'=>'สินค้านี้สถานะว่างอยู่แล้ว (อาจถูกคืนไปแล้ว)']);
    exit;
}

// 3. เริ่มขั้นตอนการคืนของ
// 3.1 ปรับสถานะเป็น available และล้าง Project ID ออก (SET NULL)
$sql = "UPDATE product_serials SET project_id = NULL, status = 'available' WHERE serial_number = '$sn'";

if($conn->query($sql)) {
    // 3.2 คืนยอดสต็อกให้สินค้าหลัก (+1)
    $barcode = $item['product_barcode'];
    $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$barcode'");
    
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error', 'msg'=>$conn->error]);
}
?>