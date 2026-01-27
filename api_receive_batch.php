<?php
include 'db_connect.php';

// รับข้อมูล JSON ที่ส่งมาจาก JavaScript
$json_data = file_get_contents('php://input');
$items = json_decode($json_data, true);

if (empty($items)) {
    echo json_encode(['status' => 'error', 'msg' => 'ไม่พบข้อมูลที่ส่งมา']);
    exit;
}

$success_count = 0;
$errors = [];

foreach ($items as $item) {
    $barcode = $item['barcode'];
    $sn = $item['sn'];

    // 1. เช็คว่า S/N ซ้ำในระบบไหม
    $check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
    if ($check->num_rows > 0) {
        $errors[] = "S/N: $sn มีในระบบแล้ว";
        continue; // ข้ามตัวนี้ไปทำตัวถัดไป
    }

    // 2. บันทึกลงตาราง S/N
    $insert = $conn->query("INSERT INTO product_serials (product_barcode, serial_number, status) VALUES ('$barcode', '$sn', 'available')");

    if ($insert) {
        // 3. เพิ่มจำนวนในสินค้าหลัก
        $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$barcode'");
        $success_count++;
    } else {
        $errors[] = "บันทึก $sn ไม่สำเร็จ: " . $conn->error;
    }
}

// สรุปผลลัพธ์ส่งกลับไป
if (count($errors) == 0) {
    echo json_encode(['status' => 'success', 'msg' => "บันทึกสำเร็จทั้งหมด $success_count รายการ"]);
} else {
    echo json_encode([
        'status' => 'partial_error', 
        'msg' => "บันทึกได้ $success_count รายการ, ผิดพลาด " . count($errors) . " รายการ",
        'errors' => $errors
    ]);
}
?>