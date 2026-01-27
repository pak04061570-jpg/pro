<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Stock - คลังสินค้าหลัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #0f172a; --accent: #3b82f6; --bg: #f1f5f9; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--bg); color: #334155; }
        
        /* Sidebar Styling */
        .sidebar { background: var(--primary); min-height: 100vh; width: 260px; position: fixed; top: 0; left: 0; padding-top: 20px; z-index: 1000; }
        .nav-link { color: #94a3b8; padding: 12px 25px; margin: 4px 16px; border-radius: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-link.active { background: var(--accent); }
        
        .main-content { margin-left: 260px; padding: 30px; }
        
        /* Card Styling */
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; overflow: hidden; }
        .card-header-custom { background: #fff; padding: 20px 25px; border-bottom: 1px solid #f1f5f9; }
        
        /* Input Styling */
        .form-control-lg, .form-select-lg { border-radius: 10px; font-size: 1rem; padding: 12px 15px; border: 2px solid #e2e8f0; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        
        .btn-add { background: var(--accent); color: white; border-radius: 10px; font-weight: 600; padding: 12px 20px; border: none; transition: 0.2s; }
        .btn-add:hover { background: #2563eb; transform: translateY(-2px); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    
    <div class="card card-custom mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0 text-primary"><i class="fas fa-plus-circle me-2 text-success"></i>รับสินค้าเข้าคลัง (Register Stock)</h5>
            <button class="btn btn-warning text-dark btn-sm fw-bold px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#newProductModal">
                <i class="fas fa-plus me-1"></i> เพิ่มสินค้าใหม่
            </button>
        </div>
        
        <div class="card-body p-4">
            
            <div class="mb-4">
                <label class="form-label fw-bold text-muted small"><i class="fas fa-tag me-2"></i>1. เลือกสินค้า</label>
                <select id="productSelect" class="form-select form-select-lg bg-light border-2">
                    <option value="">-- กรุณาเลือกสินค้า --</option>
                    <?php
                    $products = $conn->query("SELECT * FROM products ORDER BY name ASC");
                    while($p = $products->fetch_assoc()){
                        echo "<option value='{$p['barcode']}'>{$p['name']} (SKU: {$p['barcode']})</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-muted small"><i class="fas fa-barcode me-2"></i>2. ยิง Serial Number (S/N)</label>
                <div class="input-group input-group-lg">
                    <input type="text" id="scanSerial" class="form-control border-2" placeholder="ยิงบาร์โค้ด S/N ที่นี่..." disabled>
                    <button class="btn btn-primary px-5 fw-bold" id="btnSave" onclick="addToQueue()" disabled>
                        <i class="fas fa-plus me-2"></i> บันทึก
                    </button>
                </div>
                <div class="form-text text-muted">* เลือกสินค้าด้านบนก่อน ช่องสแกนถึงจะทำงาน</div>
            </div>

            <div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold text-secondary">รายการที่รอการบันทึก (Pending List)</h6>
        <button class="btn btn-success" id="btnConfirmBatch" onclick="saveBatchStock()" disabled>
            <i class="fas fa-check-double me-2"></i> ยืนยันบันทึกทั้งหมด (<span id="countPending">0</span>)
        </button>
    </div>
    
    <table class="table table-bordered table-sm bg-white">
        <thead class="table-light">
            <tr>
                <th>ชื่อสินค้า</th>
                <th>Serial Number</th>
                <th class="text-center" width="100">ลบ</th>
            </tr>
        </thead>
        <tbody id="pendingTableBody">
            </tbody>
    </table>
</div>

            <div id="msg" class="mt-3"></div>
        </div>
    </div>

    <div class="card card-custom">
        <div class="card-header-custom">
            <h5 class="fw-bold m-0 text-secondary"><i class="fas fa-boxes me-2"></i>สินค้าคงเหลือในคลัง</h5>
        </div>
        <div class="card-body p-0">
            <?php include 'load_table_pro.php'; ?>
        </div>
    </div>

</div>

<div class="modal fade" id="newProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-box-open me-2"></i>สร้างสินค้าใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
<div class="modal-body">
    <form id="formNewProduct">
        <div class="mb-3">
            <label class="fw-bold">รหัสสินค้า / SKU / Barcode หลัก</label>
            <input type="text" id="new_barcode" class="form-control" placeholder="เช่น MODEL-001" required>
        </div>
        <div class="mb-3">
            <label class="fw-bold">ชื่อสินค้า</label>
            <input type="text" id="new_name" class="form-control" placeholder="เช่น กล้องวงจรปิด รุ่น X" required>
        </div>
        
        <div class="col mb-3">
    <label class="fw-bold">ประเภทสินค้า</label>
    <input type="text" id="new_type" class="form-control" list="type_options" placeholder="เลือกหรือพิมพ์ใหม่...">
    
        <datalist id="type_options">
        <?php
        // ดึงข้อมูลประเภทที่มีอยู่แล้วมาแสดงเป็นตัวเลือก
        $types = $conn->query("SELECT name FROM product_types ORDER BY name ASC");
        while($t = $types->fetch_assoc()){
            echo "<option value='{$t['name']}'>"; 
        }
        ?>
        </datalist>
        </div>
            <div class="col mb-3">
    <label class="fw-bold">ซัพพลายเออร์</label>
    <input type="text" id="new_supplier" class="form-control" list="supplier_options" placeholder="เลือกหรือพิมพ์เจ้าใหม่...">
    
        <datalist id="supplier_options">
        <?php
        // ดึงรายชื่อ Supplier จากตารางใหม่
        $sup_query = $conn->query("SELECT name FROM suppliers ORDER BY name ASC");
        while($s = $sup_query->fetch_assoc()){
            echo "<option value='{$s['name']}'>";
        }
        ?>
        </datalist>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label class="fw-bold">ราคาซื้อ</label>
                <input type="number" id="new_price" class="form-control" value="0">
            </div>
            <div class="col mb-3">
                <label class="fw-bold">หน่วยนับ</label>
                <input type="text" id="new_unit" class="form-control" placeholder="ชิ้น/ตัว/ชุด">
            </div>
        </div>
    </form>
</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="saveNewProduct()">บันทึกสินค้าใหม่</button>
            </div>
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
                <input type="hidden" id="edit_id">
                <div class="mb-3"><label>ชื่อสินค้า</label><input type="text" id="edit_name" class="form-control"></div>
                <div class="row mb-3">
                <div class="col"><label>ประเภทสินค้า</label><input type="text" id="edit_type" class="form-control"></div>
                <div class="col"><label>Supplier</label><input type="text" id="edit_supplier" class="form-control"></div>
                <div class="row">
                <div class="col"><label>ราคาซื้อ</label><input type="number" id="edit_price" class="form-control"></div>
                <div class="col"><label>หน่วยนับ</label><input type="text" id="edit_unit" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveEdit()">บันทึกการแก้ไข</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. รับสต็อกเข้า (Stock In)
    $('#productSelect').change(function(){
        let hasVal = $(this).val() != "";
        $('#scanSerial').prop('disabled', !hasVal);
        $('#btnSave').prop('disabled', !hasVal);
        if(hasVal) $('#scanSerial').focus();
    });

    /*$('#scanSerial').keypress(function(e){
        if(e.which == 13) addStock();
    }); */
    
        $('#scanSerial').keypress(function(e){
        if(e.which == 13) addToQueue(); 
    });

    // ตัวแปรเก็บรายการชั่วคราว
    let pendingItems = [];

    // เมื่อกด Enter ที่ช่องสแกน
        $('#scanSerial').keypress(function(e) {
        if (e.which == 13) {
        addToQueue();
      }
     });

    function addToQueue() {
        let barcode = $('#productSelect').val();
        let productName = $("#productSelect option:selected").text(); // ดึงชื่อสินค้ามาแสดง
        let sn = $('#scanSerial').val().trim();

      if (sn == "") return;

    // เช็คว่ายิง S/N ซ้ำกับที่มีไหม?
        let isDuplicate = pendingItems.some(item => item.sn === sn);
     if (isDuplicate) {
            Swal.fire('ซ้ำ!', 'S/N นี้อยู่ในรายการรอแล้ว', 'warning');
            $('#scanSerial').val('').focus();
            return;
        }
    // เพิ่มข้อมูลลง Array
        pendingItems.push({
          barcode: barcode,
          name: productName,
          sn: sn
     });

        renderPendingTable();
        $('#scanSerial').val('').focus(); // เคลียร์ช่องพร้อมยิงต่อ
    }

     // ฟังก์ชันวาดตาราง
    function renderPendingTable() {
     let html = '';
     pendingItems.forEach((item, index) => {
        html += `<tr>
                    <td>${item.name}</td>
                    <td class="text-primary fw-bold">${item.sn}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFromQueue(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                 </tr>`;
     });

     if (pendingItems.length === 0) {
        html = '<tr><td colspan="3" class="text-center text-muted">ยังไม่มีรายการ</td></tr>';
        $('#btnConfirmBatch').prop('disabled', true);
     } else {
        $('#btnConfirmBatch').prop('disabled', false);
     }

     $('#pendingTableBody').html(html);
     $('#countPending').text(pendingItems.length);
    }

 // ลบรายการออกจากคิว (กรณีสแกนผิด)
    function removeFromQueue(index) {
     pendingItems.splice(index, 1);
     renderPendingTable();
    }

 // ส่งข้อมูลทั้งหมดไปบันทึกทีเดียว
    function saveBatchStock() {
     if (pendingItems.length === 0) return;

    Swal.fire({
        title: 'ยืนยันบันทึก?',
        text: `ต้องการบันทึกสินค้าจำนวน ${pendingItems.length} รายการ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'บันทึกเลย'
     }).then((result) => {
        if (result.isConfirmed) {
            
            // ส่ง AJAX ไปหา api_receive_batch.php
            fetch('api_receive_batch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(pendingItems),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status == 'success') {
                    Swal.fire('สำเร็จ', data.msg, 'success');
                    pendingItems = []; // เคลียร์คิว
                    renderPendingTable();
                    $("#proTable").load(location.href + " #proTable"); // รีโหลดตารางหลัก
                } else if (data.status == 'partial_error') {
                    // กรณีมีบางตัว Error
                    let errorMsg = data.errors.join('<br>');
                    Swal.fire({
                        title: 'บันทึกเสร็จสิ้น (แต่มีข้อผิดพลาด)',
                        html: errorMsg,
                        icon: 'warning'
                    });
                    pendingItems = []; 
                    renderPendingTable();
                    $("#proTable").load(location.href + " #proTable");
                } else {
                    Swal.fire('Error', data.msg, 'error');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            });
        }
     });
    }

     /*function addStock() {
        let barcode = $('#productSelect').val();
        let sn = $('#scanSerial').val();
        if(sn == "") return;

        $.post("api_receive.php", { barcode: barcode, sn: sn }, function(res){
            let data = JSON.parse(res);
            if(data.status == 'success'){
                const Toast = Swal.mixin({toast: true, position: 'top-end', showConfirmButton: false, timer: 2000});
                Toast.fire({icon: 'success', title: 'รับเข้า: ' + sn + ' เรียบร้อย'});
                $('#scanSerial').val("").focus();
                $("#proTable").load(location.href + " #proTable"); 
            } else {
                Swal.fire('Error', data.msg, 'error');
                $('#scanSerial').val("");
            }
        });
     }*/

        
    // 2. บันทึกสินค้าใหม่ (Create Product)
function saveNewProduct() {
    let barcode = $('#new_barcode').val();
    let name = $('#new_name').val();
    // รับค่าจากช่องใหม่
    let type = $('#new_type').val();
    let supplier = $('#new_supplier').val();
    
    let price = $('#new_price').val();
    let unit = $('#new_unit').val();

    if(barcode == "" || name == "") {
        Swal.fire('แจ้งเตือน', 'กรุณากรอกรหัสและชื่อสินค้า', 'warning');
        return;
    }

    // ส่ง type และ supplier ไปด้วย
    $.post("api_add_product.php", { 
        barcode: barcode, 
        name: name, 
        type: type,          // <-- เพิ่ม
        supplier: supplier,  // <-- เพิ่ม
        price: price, 
        unit: unit 
    }, function(res){
        let data = JSON.parse(res);
        if(data.status == 'success') {
            Swal.fire('สำเร็จ', 'เพิ่มสินค้าใหม่เรียบร้อย', 'success').then(() => {
                location.reload(); 
            });
        } else {
            Swal.fire('Error', data.msg, 'error');
        }
    });
}

    // 3. แก้ไขสินค้า (Edit)
    // ฟังก์ชันเปิด Modal (รับค่าเพิ่ม: type, supplier)
window.openEditModal = function(id, name, type, supplier, price, qty, unit) {
    $('#edit_id').val(id);
    $('#edit_name').val(name);
    
    // ใส่ค่าลงในช่องใหม่
    $('#edit_type').val(type);
    $('#edit_supplier').val(supplier);
    
    $('#edit_price').val(price);
    $('#edit_unit').val(unit);
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// ฟังก์ชันบันทึก
function saveEdit(){
    $.post("update_item.php", {
        id: $('#edit_id').val(),
        name: $('#edit_name').val(),
        
        // ส่งค่าใหม่ไปบันทึก
        type: $('#edit_type').val(),
        supplier: $('#edit_supplier').val(),
        
        price: $('#edit_price').val(),
        qty: 0, // ค่านี้ไม่ได้ใช้อัปเดต แต่ส่งไปกัน Error เดิม
        unit: $('#edit_unit').val()
    }, function(){
        location.reload();
    });
}

</script>
</body>
</html>