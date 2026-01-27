<?php 
include 'db_connect.php'; 

// 1. คำนวณยอดสรุป (Stats)
$stat_items = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$stat_qty   = $conn->query("SELECT SUM(quantity) as s FROM products")->fetch_assoc()['s'];
$stat_value = $conn->query("SELECT SUM(price_sell * quantity) as v FROM products")->fetch_assoc()['v'];
$stat_low   = $conn->query("SELECT COUNT(*) as c FROM products WHERE quantity < 5")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้าทั้งหมด - Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #0f172a; --accent: #3b82f6; --bg: #f1f5f9; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--bg); color: #334155; }
        
        .sidebar { background: var(--primary); min-height: 100vh; width: 260px; position: fixed; top: 0; left: 0; padding-top: 20px; z-index: 1000; }
        .nav-link { color: #94a3b8; padding: 12px 25px; margin: 4px 16px; border-radius: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: var(--accent); }
        
        .main-content { margin-left: 260px; padding: 30px; }
        
        .stat-card { border: none; border-radius: 12px; padding: 20px; color: white; position: relative; overflow: hidden; height: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .stat-card h2 { font-weight: bold; margin: 0; }
        .stat-icon { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 3rem; opacity: 0.2; }
        
        .table-custom thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .table-custom tbody td { vertical-align: middle; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    
    <h4 class="fw-bold mb-4 text-secondary"><i class="fas fa-chart-pie me-2"></i>ภาพรวมคลังสินค้า</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"><p>รายการสินค้า (SKUs)</p><h2><?php echo number_format($stat_items); ?></h2><i class="fas fa-tags stat-icon"></i></div></div>
        <div class="col-md-3"><div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);"><p>จำนวนชิ้นรวม (Units)</p><h2><?php echo number_format($stat_qty); ?></h2><i class="fas fa-cubes stat-icon"></i></div></div>
        <div class="col-md-3"><div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><p>มูลค่าสินค้าคงคลัง (บาท)</p><h2><?php echo number_format($stat_value); ?></h2><i class="fas fa-coins stat-icon"></i></div></div>
        <div class="col-md-3"><div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);"><p>สินค้าใกล้หมด (<5)</p><h2><?php echo number_format($stat_low); ?></h2><i class="fas fa-exclamation-triangle stat-icon"></i></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-primary"><i class="fas fa-list me-2"></i>รายการสินค้าทั้งหมด (Supply List)</h5>
                <button class="btn btn-outline-success btn-sm rounded-pill" onclick="window.print()"><i class="fas fa-print me-1"></i> พิมพ์รายงาน</button>
            </div>
            
            <table id="allProductTable" class="table table-hover table-custom w-100">
<thead>
    <tr>
        <th>SKU / รหัส</th>
        <th>ชื่อสินค้า</th>
        <th>ประเภท</th>      
        <th>Supplier</th>   <th class="text-end">ราคาขาย</th>
        <th class="text-center">คงเหลือ</th>
        <th class="text-end">มูลค่ารวม</th>
        <th class="text-center">สถานะ</th>
        <th class="text-center" width="15%">จัดการ</th>
    </tr>
</thead>
<tbody>
   <?php 
// 1. แก้ SQL
$sql = "SELECT p.*, t.name AS type_name, s.name AS supplier_name 
        FROM products p 
        LEFT JOIN product_types t ON p.type_id = t.id 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        ORDER BY p.quantity ASC";

$result = $conn->query($sql);

while($row = $result->fetch_assoc()):
    $total_val = $row['price_sell'] * $row['quantity'];
    // ... (ส่วนเช็คสถานะสินค้าคงเหลือ เอาไว้เหมือนเดิม) ...
    if($row['quantity'] == 0) $status = '<span class="badge bg-danger-subtle text-danger border border-danger">สินค้าหมด</span>';
    elseif($row['quantity'] < 5) $status = '<span class="badge bg-warning-subtle text-warning border border-warning">ใกล้หมด</span>';
    else $status = '<span class="badge bg-success-subtle text-success border border-success">ปกติ</span>';
?>
<tr>
    <td><span class="fw-bold text-dark"><?php echo $row['barcode']; ?></span></td>
        <td>
            <div class="fw-bold"><?php echo $row['name']; ?></div>
            <small class="text-muted">หน่วย: <?php echo $row['unit']; ?></small>
        </td>
        
        <td><?php echo $row['type_name']; ?></td>
    <td><?php echo $row['supplier_name']; ?></td>
        
        <td class="text-end"><?php echo number_format($row['price_sell'], 2); ?></td>
        <td class="text-center"><span class="fw-bold fs-5"><?php echo $row['quantity']; ?></span></td>
        <td class="text-end text-muted small"><?php echo number_format($total_val, 2); ?></td>
        <td class="text-center"><?php echo $status; ?></td>
        
        <td class="text-center">
             <a href="product_details.php?barcode=<?php echo $row['barcode']; ?>" 
               class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                <i class="fas fa-eye me-1"></i> ดู/แก้ไข
            </a>
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                    onclick="deleteProduct(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>')">
                <i class="fas fa-trash-alt me-1"></i> ลบ
            </button>
        </td>
    </tr>
    <?php endwhile; ?>
    
</tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function(){
        $('#allProductTable').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" },
            "pageLength": 10,
            "order": [[ 3, "asc" ]] 
        });
    });

    // ฟังก์ชันลบสินค้า
    function deleteProduct(id, name) {
        Swal.fire({
            title: 'ลบสินค้า: ' + name + '?',
            text: "หากลบแล้วข้อมูลจะหายไปถาวร! (ลบได้เฉพาะสินค้าที่ไม่มี S/N ค้างอยู่เท่านั้น)",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ยืนยันลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("api_delete_product.php", { id: id }, function(res) {
                    let data = JSON.parse(res);
                    if(data.status == 'success') {
                        Swal.fire('ลบสำเร็จ', 'สินค้าถูกลบออกจากระบบแล้ว', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('ลบไม่ได้!', data.msg, 'error');
                    }
                });
            }
        })
    }
</script>

</body>
</html>