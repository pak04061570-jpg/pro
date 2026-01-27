<?php include 'db_connect.php';

// รับค่า Barcode ที่ส่งมา
$barcode = $_GET['barcode'];

// 1. ดึงข้อมูลสินค้าหลัก (พร้อมชื่อประเภทและ Supplier)
$sql_pro = "SELECT p.*, 
                   t.name AS type_name, 
                   s.name AS supplier_name 
            FROM products p 
            LEFT JOIN product_types t ON p.type_id = t.id 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            WHERE p.barcode = '$barcode'";

$product = $conn->query($sql_pro)->fetch_assoc();
if(!$product) die("ไม่พบสินค้า");

// 2. ดึงรายการ S/N ทั้งหมดของสินค้านี้ (พร้อมชื่อโปรเจกต์ ถ้ามี)
$sql_sn = "SELECT s.*, p.project_name 
           FROM product_serials s 
           LEFT JOIN projects p ON s.project_id = p.id 
           WHERE s.product_barcode = '$barcode' 
           ORDER BY s.id DESC";
$serials = $conn->query($sql_sn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียด: <?php echo $product['name']; ?></title>
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
        .main-content { margin-left: 260px; padding: 30px; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); background: #fff; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    
    <a href="products.php" class="btn btn-outline-secondary mb-3 rounded-pill"><i class="fas fa-arrow-left me-1"></i> ย้อนกลับ</a>

    <div class="card card-custom mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold text-primary mb-1"><?php echo $product['name']; ?></h4>
        <span class="badge bg-secondary fs-6 fw-normal">SKU: <?php echo $product['barcode']; ?></span>
        <span class="badge bg-info text-dark fs-6 fw-normal">หน่วย: <?php echo $product['unit']; ?></span>
        
        <div class="mt-2 text-muted small">
    <span class="me-3">
        <i class="fas fa-tags me-1"></i> ประเภท: <?php echo $product['type_name']; ?></span>
    
    <span>
        <i class="fas fa-store me-1"></i> Supplier: <?php echo $product['supplier_name']; ?></span>
</div>

    </div>
    <div class="text-end">
                <h3 class="fw-bold mb-0"><?php echo number_format($product['price_sell'], 2); ?> <small class="fs-6 text-muted">บาท</small></h3>
                <button class="btn btn-warning btn-sm mt-2 fw-bold px-3 rounded-pill" onclick="openEditModal()">
                    <i class="fas fa-pen me-1"></i> แก้ไขข้อมูลสินค้า
                </button>
            </div>
        </div>
    </div>

    <div class="card card-custom">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold m-0"><i class="fas fa-barcode me-2"></i>รายการ Serial Number ทั้งหมด</h5>
        </div>
        <div class="card-body">
            <table id="snTable" class="table table-hover w-100">
                <thead class="table-light">
                    <tr>
                        <th>Serial Number</th>
                        <th class="text-center">วันที่รับเข้า</th>
                        <th class="text-center">สถานะ</th>
                        <th>ตำแหน่งปัจจุบัน</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($sn = $serials->fetch_assoc()): 
                        // จัดการสีสถานะ
                        if($sn['status'] == 'available') {
                            $status_badge = '<span class="badge bg-success">ว่าง / พร้อมขาย</span>';
                            $loc = '<span class="text-muted">- คลังสินค้า -</span>';
                        } else {
                            $status_badge = '<span class="badge bg-danger">ถูกเบิกแล้ว</span>';
                            $loc = '<strong class="text-primary"><i class="fas fa-hard-hat me-1"></i> '.$sn['project_name'].'</strong>';
                        }
                    ?>
                    <tr>
                        <td class="fw-bold"><?php echo $sn['serial_number']; ?></td>
                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($sn['date_added'])); ?></td> <td class="text-center"><?php echo $status_badge; ?></td>
                        <td><?php echo $loc; ?></td>
                        <td class="text-center">
                            <?php if($sn['status'] == 'available'): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSN(<?php echo $sn['id']; ?>, '<?php echo $sn['serial_number']; ?>')">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-light text-muted" disabled><i class="fas fa-lock"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขข้อมูลสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
    <input type="hidden" id="edit_id" value="<?php echo $product['id']; ?>">
    <div class="mb-3">
        <label>ชื่อสินค้า</label>
        <input type="text" id="edit_name" class="form-control" value="<?php echo $product['name']; ?>"></div>

    <div class="row mb-3">
        <div class="col"><label>ประเภทสินค้า</label>
            <input type="text" id="edit_type" class="form-control" value="<?php echo $product['product_type']; ?>"></div>
        <div class="col"><label>Supplier</label>
            <input type="text" id="edit_supplier" class="form-control" value="<?php echo $product['supplier']; ?>">
        </div>
    </div>
    <div class="row">
        <div class="col"><label>ราคาซื้อ</label>
            <input type="number" id="edit_price" class="form-control" value="<?php echo $product['price_sell']; ?>">
        </div>
        <div class="col">
            <label>หน่วยนับ</label>
            <input type="text" id="edit_unit" class="form-control" value="<?php echo $product['unit']; ?>">
        </div>
    </div>
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveEdit()">บันทึก</button>
            </div>
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
        $('#snTable').DataTable();
    });

    function openEditModal() {
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function saveEdit(){
    $.post("update_item.php", {
        id: $('#edit_id').val(),
        name: $('#edit_name').val(),
        
        // เพิ่ม 2 ค่านี้
        type: $('#edit_type').val(), 
        supplier: $('#edit_supplier').val(),
        
        price: $('#edit_price').val(),
        unit: $('#edit_unit').val()
    }, function(res){
        if(res.trim() == 'success') {
            Swal.fire('สำเร็จ', 'บันทึกข้อมูลแล้ว', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', res, 'error');
        }
    });
}

    function deleteSN(id, sn) {
        Swal.fire({
            title: 'ยืนยันลบ S/N?',
            text: "คุณต้องการลบ " + sn + " ออกจากระบบหรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบเลย!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("api_delete_sn.php", { id: id, barcode: '<?php echo $barcode; ?>' }, function(res){
                    location.reload();
                });
            }
        })
    }
</script>

</body>
</html>