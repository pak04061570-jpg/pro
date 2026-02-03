<?php
include 'db_connect.php';

// 1. รับค่า (ใส่ตัวเช็ค isset เพื่อป้องกัน Error)
$sn = isset($_POST['sn']) ? $_POST['sn'] : '';
$note = isset($_POST['note']) ? $_POST['note'] : ''; 

if(empty($sn)) {
    echo json_encode(['status'=>'error', 'msg'=>'ไม่พบข้อมูล Serial Number']);
    exit;
}

// 2. เช็คข้อมูลสินค้า
$check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
if($check->num_rows == 0) {
    echo json_encode(['status'=>'error', 'msg'=>'ไม่พบ S/N นี้ในระบบ']);
    exit;
}
$item = $check->fetch_assoc();

// 3. เช็คสถานะ (ถ้า available แปลว่าคืนไปแล้ว)
if($item['status'] == 'available') {
    echo json_encode(['status'=>'error', 'msg'=>'สินค้านี้สถานะว่างอยู่แล้ว (อาจคืนไปแล้ว)']);
    exit;
}

// เก็บ project_id ไว้ก่อนอัปเดต (ถ้าไม่มีให้เป็น NULL)
$project_id = $item['project_id'];
$safe_pid = empty($project_id) ? "NULL" : "'$project_id'";
$safe_note = $conn->real_escape_string($note);

// 4. เริ่มกระบวนการคืน
// 4.1 อัปเดตสถานะเป็น available
$sql = "UPDATE product_serials SET project_id = NULL, status = 'available' WHERE serial_number = '$sn'";

if($conn->query($sql)) {
    // 4.2 คืนยอดสต็อก (+1)
    $barcode = $item['product_barcode'];
    $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$barcode'");
    
    // 4.3 บันทึกประวัติ (History)
    $log_sql = "INSERT INTO product_history (serial_number, project_id, action_type, note) 
                VALUES ('$sn', $safe_pid, 'return', '$safe_note')";
    
    if($conn->query($log_sql)) {
        echo json_encode(['status'=>'success']);
    } else {
        // คืนของได้ แต่บันทึกประวัติไม่ได้ (แจ้งเตือน)
        echo json_encode(['status'=>'success', 'warning'=>'คืนของสำเร็จ แต่บันทึกประวัติล้มเหลว: ' . $conn->error]);
    }
} else {
    echo json_encode(['status'=>'error', 'msg'=>$conn->error]);
}
?>