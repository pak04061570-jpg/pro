<?php
include 'db_connect.php';
$pid = $_GET['id'];
$proj = $conn->query("SELECT * FROM projects WHERE id = $pid")->fetch_assoc();
$items = $conn->query("SELECT s.*, p.name, p.unit FROM product_serials s JOIN products p ON s.product_barcode = p.barcode WHERE s.project_id = $pid ORDER BY p.name ASC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบรายการสินค้า - <?php echo $proj['project_name']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; padding: 40px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px 12px; text-align: left; }
        th { background-color: #f0f0f0; text-align: center; }
        .text-center { text-align: center; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
        .sign-line { border-bottom: 1px solid #000; width: 200px; display: inline-block; margin-bottom: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <button class="no-print" onclick="window.print()" style="padding: 10px 20px; cursor: pointer; margin-bottom: 20px;">🖨️ พิมพ์หน้านี้</button>

    <div class="header">
        <h1>ใบรายการเบิกสินค้า / Job Sheet</h1>
        <p>บริษัท ซี.เอ็ม.เอส. คอนโทรล ซิสเต็ม จำกัด</p>
    </div>

    <div class="info-box">
        <div>
            <strong>ชื่อโครงการ:</strong> <?php echo $proj['project_name']; ?><br>
            <strong>วันที่สร้าง:</strong> <?php echo date('d/m/Y', strtotime($proj['created_at'])); ?>
        </div>
        <div>
            <strong>เลขที่เอกสาร:</strong> JOB-<?php echo str_pad($proj['id'], 4, '0', STR_PAD_LEFT); ?><br>
            <strong>สถานะ:</strong> <?php echo $proj['status'] == 'Closed' ? 'ปิดงานแล้ว' : 'กำลังดำเนินการ'; ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="50">ลำดับ</th>
                <th>รายการสินค้า (Product)</th>
                <th width="150">Serial Number</th>
                <th width="80">หน่วย</th>
                <th width="100">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 0;
            if($items->num_rows > 0):
                while($row = $items->fetch_assoc()): 
                    $i++;
            ?>
            <tr>
                <td class="text-center"><?php echo $i; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td class="text-center"><?php echo $row['serial_number']; ?></td>
                <td class="text-center"><?php echo $row['unit']; ?></td>
                <td></td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="5" class="text-center">-- ไม่มีรายการสินค้า --</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <div>
            <br><span class="sign-line"></span><br>
            ( ผู้เบิกสินค้า )<br>วันที่ ____/____/____
        </div>
        <div>
            <br><span class="sign-line"></span><br>
            ( ผู้อนุมัติ/จ่ายของ )<br>วันที่ ____/____/____
        </div>
    </div>

</body>
</html>