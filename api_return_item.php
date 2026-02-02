<?php
include 'db_connect.php';

$sn = $_POST['sn'];
$note = $_POST['note']; // รับค่าหมายเหตุมาด้วย

// 1. เช็คข้อมูล S/N
$check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
if($check->num_rows == 0) {
    echo json_encode(['status'=>'error', 'msg'=>'ไม่พบ S/N นี้ในระบบ']);
    exit;
}
$item = $check->fetch_assoc();

// 2. เช็คกันเหนียว (ถ้าสถานะเป็น available อยู่แล้ว แปลว่าคืนไปแล้ว)
if($item['status'] == 'available') {
    echo json_encode(['status'=>'error', 'msg'=>'สินค้านี้สถานะว่างอยู่แล้ว (อาจถูกคืนไปแล้ว)']);
    exit;
}

// 3. เริ่มกระบวนการคืน
// 3.1 อัปเดตตาราง Serial: ล้าง Project ID และปรับสถานะเป็น available
$sql = "UPDATE product_serials SET project_id = NULL, status = 'available' WHERE serial_number = '$sn'";

if($conn->query($sql)) {
    // 3.2 คืนยอดสต็อกให้สินค้าหลัก (+1)
    $barcode = $item['product_barcode'];
    $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$barcode'");
    
    // 3.3 [สำคัญ] บันทึกลงตารางประวัติ (History)
    $project_id = $item['project_id']; // เก็บไว้ก่อนว่าคืนมาจากไหน
    $conn->query("INSERT INTO product_history (serial_number, project_id, action_type, note) 
                  VALUES ('$sn', '$project_id', 'return', '$note')");
    
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error', 'msg'=>$conn->error]);
}
?>