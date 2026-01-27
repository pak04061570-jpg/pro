<?php
include 'db_connect.php';

// รับค่า ID โปรเจกต์
$pid = $_GET['id'];

// ดึงข้อมูลโปรเจกต์
$proj = $conn->query("SELECT * FROM projects WHERE id = $pid")->fetch_assoc();
if(!$proj) die("ไม่พบข้อมูล");

// ตั้งชื่อไฟล์ที่จะดาวน์โหลด (เช่น Job_Export_บ้านกูเอง.xls)
$filename = "Job_Export_" . $proj['project_name'] . "_" . date('Ymd') . ".xls";

// สั่งให้ Browser รู้ว่าเป็นไฟล์ Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// ดึงรายการสินค้า
$sql = "SELECT s.serial_number, p.name, p.barcode, p.unit, p.price_sell, s.date_added 
        FROM product_serials s 
        JOIN products p ON s.product_barcode = p.barcode 
        WHERE s.project_id = $pid 
        ORDER BY s.id DESC";
$result = $conn->query($sql);
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    /* จัดสไตล์ตารางใน Excel นิดหน่อย */
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 5px; }
    th { background-color: #f0f0f0; text-align: center; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
</style>
</head>
<body>
    <h3>รายการเบิกสินค้า: <?php echo $proj['project_name']; ?></h3>
    <p>วันที่เบิก: <?php echo date('d/m/Y', strtotime($proj['created_at'])); ?> | สถานะ: <?php echo $proj['status']; ?></p>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">ลำดับ</th>
                <th style="width: 120px;">รหัสสินค้า (SKU)</th>
                <th style="width: 250px;">ชื่อสินค้า</th>
                <th style="width: 150px;">Serial Number</th>
                <th style="width: 80px;">หน่วย</th>
                <th style="width: 100px;">วันที่เบิก</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 0;
            while($row = $result->fetch_assoc()): 
                $i++;
            ?>
            <tr>
                <td class="text-center"><?php echo $i; ?></td>
                <td class="text-center" style="mso-number-format:'@'"><?php echo $row['barcode']; ?></td> <td><?php echo $row['name']; ?></td>
                <td class="text-center" style="mso-number-format:'@'"><?php echo $row['serial_number']; ?></td>
                <td class="text-center"><?php echo $row['unit']; ?></td>
                <td class="text-center"><?php echo date('d/m/Y', strtotime($row['date_added'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>