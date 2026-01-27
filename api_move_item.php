<?php
include 'db_connect.php';

$sn = $_POST['sn'];
$pid = $_POST['project_id'];

// 🛡️ ด่านที่ 1: เช็คว่าโปรเจกต์ "ปิดงาน" ไปหรือยัง?
$proj = $conn->query("SELECT status FROM projects WHERE id = $pid")->fetch_assoc();
if ($proj['status'] == 'Closed') {
    echo json_encode(['status'=>'error', 'msg'=>'❌ โปรเจกต์นี้ปิดงานแล้ว ไม่สามารถเบิกของเพิ่มได้']);
    exit;
}

// 🛡️ ด่านที่ 2: เช็คว่า S/N นี้ "ว่าง" จริงไหม? (ป้องกันการแย่งของ หรือเบิกของที่ขายไปแล้ว)
$check_item = $conn->query("SELECT status FROM product_serials WHERE serial_number = '$sn'");
if ($check_item->num_rows == 0) {
    echo json_encode(['status'=>'error', 'msg'=>'❌ ไม่พบ S/N นี้ในระบบ']);
    exit;
}
$item = $check_item->fetch_assoc();

if ($item['status'] != 'available') {
    // ถ้าสถานะไม่ใช่ available แสดงว่าของไม่อยู่ให้เบิกแล้ว
    echo json_encode(['status'=>'error', 'msg'=>'❌ สินค้านี้ถูกเบิกไปแล้ว (สถานะไม่ว่าง)']);
    exit;
}

// ✅ ผ่านทุกด่าน -> ทำการบันทึก
// เพิ่ม date_added = NOW() เพื่อรีเซ็ตเวลาเป็นปัจจุบัน
$sql = "UPDATE product_serials 
        SET project_id = $pid, 
            status = 'sold', 
            date_added = NOW() 
        WHERE serial_number = '$sn'";

if($conn->query($sql)) {
    // ตัดสต็อกสินค้าหลัก (-1)
    $get_barcode = $conn->query("SELECT product_barcode FROM product_serials WHERE serial_number = '$sn'")->fetch_assoc();
    $barcode = $get_barcode['product_barcode'];
    $conn->query("UPDATE products SET quantity = quantity - 1 WHERE barcode = '$barcode'");

    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error', 'msg'=>$conn->error]);
}
?>