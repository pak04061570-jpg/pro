<?php
include 'db_connect.php';

$action = $_POST['action'];

if($action == 'create') {
    $name = $_POST['name'];
    $type = $_POST['type']; // รับค่า type
    $conn->query("INSERT INTO projects (project_name, type) VALUES ('$name', '$type')");
    echo 'success';
}

if($action == 'assign') {
    $sn = $_POST['sn'];
    $pid = $_POST['pid'];
    $type = $_POST['type']; // รับค่า type มาเช็ค
    
    // ================== กรณีรับของเข้า (INBOUND) ==================
    if($type == 'inbound') {
        $p_barcode = $_POST['product_barcode']; // ต้องมีบาร์โค้ดสินค้าหลัก
        
        // 1. ห้าม S/N ซ้ำ
        $check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
        if($check->num_rows > 0) {
            echo json_encode(['status'=>'error', 'msg'=>'S/N นี้มีในระบบแล้ว']);
            exit;
        }

        // 2. เพิ่มลงตาราง S/N (ผูกกับ Project นี้เลย)
        $conn->query("INSERT INTO product_serials (product_barcode, serial_number, status, project_id) VALUES ('$p_barcode', '$sn', 'available', $pid)");
        
        // 3. เพิ่มจำนวนสต็อก (+1)
        $conn->query("UPDATE products SET quantity = quantity + 1 WHERE barcode = '$p_barcode'");
        
        echo json_encode(['status'=>'success', 'msg'=>'รับเข้าเรียบร้อย']);
    } 
    
    // ================== กรณีเบิกออก (OUTBOUND) ==================
    else {
        // 1. เช็คว่ามี S/N นี้ไหม
        $check = $conn->query("SELECT * FROM product_serials WHERE serial_number = '$sn'");
        if($check->num_rows == 0) {
            echo json_encode(['status'=>'error', 'msg'=>'ไม่พบ S/N นี้']);
            exit;
        }
        
        $item = $check->fetch_assoc();
        if($item['project_id'] != NULL && $item['status'] == 'sold') {
             echo json_encode(['status'=>'error', 'msg'=>'S/N นี้ถูกใช้ไปแล้ว']);
             exit;
        }

        // 2. ตัดของเข้าโปรเจกต์
        $conn->query("UPDATE product_serials SET project_id = $pid, status = 'sold' WHERE serial_number = '$sn'");
        
        // 3. ลดจำนวนสต็อก (-1)
        $barcode = $item['product_barcode'];
        $conn->query("UPDATE products SET quantity = quantity - 1 WHERE barcode = '$barcode'");
        
        echo json_encode(['status'=>'success', 'msg'=>'เบิกออกเรียบร้อย']);
    }
}
// ฟังก์ชันแก้ไขชื่อโปรเจกต์
if ($_POST['action'] == 'edit_info') {  // <-- สังเกตชื่อ action ต้องตรงกับ JS
    $id = $_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $code = $conn->real_escape_string($_POST['code']); // รับค่า code

    // ตรวจสอบว่าแก้ไขได้สำเร็จหรือไม่
    $sql = "UPDATE projects SET project_name = '$name', project_code = '$code' WHERE id = $id";
    
    if($conn->query($sql)){
        echo "success";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Error: " . $conn->error; // ส่ง Error กลับไปบอกหน้าเว็บ
    }
}

// ฟังก์ชันปิดงาน
if($action == 'close_job') {
    $id = $_POST['id'];
    // เปลี่ยนสถานะเป็น Closed
    $conn->query("UPDATE projects SET status = 'Closed' WHERE id = $id");
    echo 'success';
}
// ... (ต่อจากโค้ดเดิม)

if($action == 'delete_project') {
    $id = $_POST['id'];
    
    // เช็คอีกทีฝั่ง Server เพื่อความชัวร์ ว่าไม่มีของค้าง
    $check = $conn->query("SELECT COUNT(*) as c FROM product_serials WHERE project_id = $id")->fetch_assoc()['c'];
    
    if($check > 0) {
        echo json_encode(['status'=>'error', 'msg'=>'ยังมีสินค้าคงค้างในโปรเจกต์ ไม่สามารถลบได้']);
    } else {
        $conn->query("DELETE FROM projects WHERE id = $id");
        echo json_encode(['status'=>'success']);
    }
}
?>