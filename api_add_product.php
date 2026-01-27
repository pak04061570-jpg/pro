<?php
include 'db_connect.php';

$barcode = $_POST['barcode'];
$name = $_POST['name'];
$price = $_POST['price'];
$unit = $_POST['unit'];

// รับค่าที่ User พิมพ์มา (อาจจะเป็นชื่อใหม่ หรือชื่อที่มีอยู่แล้ว)
$type_input = trim($_POST['type']);
$supplier_input = trim($_POST['supplier']);

// 
// ฟังก์ชัน: หา ID ก่อน ถ้าไม่เจอให้สร้างใหม่
// 
function getOrCreateID($conn, $table, $col, $val) {
    if(empty($val)) return "NULL"; // ถ้าไม่ได้กรอกมา

    // 1. ลองค้นหา
    $check = $conn->query("SELECT id FROM $table WHERE $col = '$val'");
    if($check->num_rows > 0) {
        return $check->fetch_assoc()['id']; // เจอแล้ว! เอา ID ไป
    } else {
        // 2. ไม่เจอ! สร้างใหม่
        $conn->query("INSERT INTO $table ($col) VALUES ('$val')");
        return $conn->insert_id; // เอา ID ใหม่ไป
    }
}

// เรียกใช้ฟังก์ชัน
$type_id = getOrCreateID($conn, 'product_types', 'name', $type_input);
$supplier_id = getOrCreateID($conn, 'suppliers', 'name', $supplier_input);

// -------------------------------------------------------

// เช็ค Barcode ซ้ำ
$chk_dup = $conn->query("SELECT * FROM products WHERE barcode = '$barcode'");
if($chk_dup->num_rows > 0) {
    echo json_encode(['status'=>'error', 'msg'=>'รหัสสินค้านี้มีอยู่แล้ว']);
    exit;
}

// บันทึกข้อมูล (เปลี่ยนไปเก็บ ID แทนชื่อ)
// หมายเหตุ: ตรงนี้เราใช้ type_id และ supplier_id ที่ได้จากฟังก์ชันด้านบน
$sql = "INSERT INTO products (barcode, name, type_id, supplier_id, price_sell, unit, quantity) 
        VALUES ('$barcode', '$name', $type_id, $supplier_id, '$price', '$unit', 0)";

if($conn->query($sql)) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error', 'msg'=>$conn->error]);
}
?>