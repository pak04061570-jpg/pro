<?php 
include 'db_connect.php'; 


$pid = $_GET['id'];
$proj = $conn->query("SELECT * FROM projects WHERE id = $pid")->fetch_assoc();
if(!$proj) die("ไม่พบข้อมูลโปรเจกต์");

// เช็คสถานะว่าปิดงานหรือยัง?
$is_closed = ($proj['status'] == 'Closed');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการ: <?php echo $proj['project_name']; ?></title>
    
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
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; overflow: hidden; }
        .card-header-custom { background: #fff; padding: 20px 25px; border-bottom: 1px solid #f1f5f9; }
        .form-control-lg { border-radius: 10px; border: 2px solid #e2e8f0; }
        .form-control-lg:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        
        /* ถ้าปิดงานแล้ว ให้หน้าจอดูจางลง */
        .closed-job { opacity: 0.6; pointer-events: none; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="projects.php" class="text-muted text-decoration-none small"><i class="fas fa-arrow-left"></i> กลับหน้ารวม</a>
            <div class="d-flex align-items-center mt-1">
                <h3 class="fw-bold text-primary m-0 me-2">
                    <i class="fas fa-clipboard-check me-2"></i><?php echo $proj['project_name']; ?>
                </h3>
                <?php if(!$is_closed): ?>
                <button class="btn btn-sm btn-light text-muted" onclick="editProjectInfo()">
                      <i class="fas fa-pen"></i> แก้ไขข้อมูล
                </button>
                <?php endif; ?>
            </div>
            
            <span class="badge bg-secondary fs-6">Job: <?php echo $proj['project_code']; ?></span>
            <?php if($is_closed): ?>
                <span class="badge bg-danger"><i class="fas fa-lock"></i> ปิดงานแล้ว (Closed)</span>
            <?php else: ?>
                <span class="badge bg-success">กำลังดำเนินการ (Open)</span>
            <?php endif; ?>
        </div>

        <div>
            <a href="print_job.php?id=<?php echo $pid; ?>" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 me-2">
                <i class="fas fa-print me-2"></i> พิมพ์ใบเบิก
            </a>

            <a href="export_excel.php?id=<?php echo $pid; ?>" target="_blank" class="btn btn-success rounded-pill px-3 me-2">
                <i class="fas fa-file-excel me-2"></i> Export Excel
            </a>

            <?php if(!$is_closed): ?>
            <button class="btn btn-danger rounded-pill px-3" onclick="closeJob()">
                <i class="fas fa-flag-checkered me-2"></i> ปิดจ็อบงานนี้
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4 <?php echo $is_closed ? 'closed-job' : ''; ?>">
        
        <div class="col-md-4">
            <div class="card card-custom h-100">
                <div class="card-header-custom bg-primary text-white">
                    <h5 class="m-0 fw-bold"><i class="fas fa-dolly me-2"></i>เบิกของเข้าไซต์งานนี้</h5>
                </div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold text-muted">วิธีที่ 1: ยิง Serial Number</label>
                    <div class="input-group input-group-lg mb-4">
                        <input type="text" id="scanInput" class="form-control" placeholder="ยิง S/N..." autofocus autocomplete="off">
                        <button class="btn btn-primary" onclick="assignItem()"><i class="fas fa-plus"></i></button>
                    </div>
                    
                    <div class="text-center position-relative mb-4">
                        <hr><span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">หรือ</span>
                    </div>

                    <label class="form-label fw-bold text-muted">วิธีที่ 2: เลือกจากคลัง</label>
                    <button class="btn btn-outline-primary w-100 py-2 border-2 fw-bold rounded-3" data-bs-toggle="modal" data-bs-target="#browseModal">
                        <i class="fas fa-search me-2"></i> ค้นหาและเลือกสินค้า
                    </button>
                    
                    <div class="alert alert-info small border-0 bg-info-subtle text-info-emphasis mt-4">
                        <i class="fas fa-info-circle me-1"></i> 
                        ระบบจะแสดงเฉพาะสินค้าที่มีสถานะ <b>"ว่าง (Available)"</b> เท่านั้น
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-custom h-100">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-secondary"><i class="fas fa-list-ul me-2"></i>รายการสินค้าในไซต์งาน</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ชื่อสินค้า</th>
                                    <th>Serial Number</th>
                                    <th class="text-center">วันที่เบิก</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sql_items = "SELECT s.*, p.name FROM product_serials s JOIN products p ON s.product_barcode = p.barcode WHERE s.project_id = $pid ORDER BY s.id DESC";
                                $items = $conn->query($sql_items);
                                if($items->num_rows > 0):
                                    while($item = $items->fetch_assoc()):
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo $item['name']; ?></td>
                                    <td><span class="badge bg-light text-dark border px-3 py-2 fs-6"><?php echo $item['serial_number']; ?></span></td>
                                    <td class="text-center text-muted small"><?php echo date('d/m/Y H:i', strtotime($item['date_added'])); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="returnItem('<?php echo $item['serial_number']; ?>')"><i class="fas fa-undo"></i> คืน</button>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">ยังไม่มีสินค้าในโปรเจกต์นี้</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="browseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">เลือกสินค้าจากคลัง</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <input type="text" id="filterInput" class="form-control mb-3" placeholder="ค้นหาชื่อสินค้า...">
                <div class="accordion" id="stockAccordion">
                    <?php
                    $av_items = $conn->query("SELECT s.*, p.name, p.price_sell FROM product_serials s JOIN products p ON s.product_barcode = p.barcode WHERE s.status = 'available' ORDER BY p.name ASC");
                    $groups = [];
                    while($row = $av_items->fetch_assoc()) $groups[$row['name']][] = $row;
                    
                    if(count($groups) > 0): $i=0; foreach($groups as $name => $list): $i++;
                    ?>
                    <div class="accordion-item search-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c<?php echo $i; ?>">
                                <span class="prod-name fw-bold"><?php echo $name; ?></span>
                                <span class="badge bg-success ms-auto"><?php echo count($list); ?></span>
                            </button>
                        </h2>
                        <div id="c<?php echo $i; ?>" class="accordion-collapse collapse" data-bs-parent="#stockAccordion">
                            <div class="accordion-body p-0">
                                <table class="table table-sm mb-0">
                                    <?php foreach($list as $it): ?>
                                    <tr>
                                        <td class="ps-4"><?php echo $it['serial_number']; ?></td>
                                        <td class="text-end"><button class="btn btn-sm btn-primary rounded-pill" onclick="selectFromModal('<?php echo $it['serial_number']; ?>')">เลือก</button></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="text-center py-5">ไม่มีสินค้าว่าง</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let pid = <?php echo $pid; ?>;

    // ค้นหาใน Modal
    $('#filterInput').on('keyup', function() {
        let val = $(this).val().toLowerCase();
        $('.search-item').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1)
        });
    });

    $('#scanInput').keypress(function(e){ if(e.which == 13) assignItem(); });

    function selectFromModal(sn) {
        $('#browseModal').modal('hide');
        $('#scanInput').val(sn);
        assignItem();
    }

    function assignItem() {
        let sn = $('#scanInput').val();
        if(sn == "") return;
        $.post("api_move_item.php", { sn: sn, project_id: pid }, function(res){
            let data = JSON.parse(res);
            if(data.status == 'success') {
                const Toast = Swal.mixin({toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
                Toast.fire({icon: 'success', title: 'สำเร็จ'});
                setTimeout(() => location.reload(), 500); 
            } else {
                Swal.fire('Error', data.msg, 'error');
                $('#scanInput').val("");
            }
        });
    }

    // ฟังก์ชันแก้ไขชื่อโปรเจกต์
    // ฟังก์ชันแก้ไขทั้ง ชื่อ และ รหัสงาน (แบบใหม่)
    async function editProjectInfo() {
        const { value: formValues } = await Swal.fire({
            title: 'แก้ไขข้อมูลโปรเจกต์',
            html:
                '<div class="text-start mb-2"><label class="fw-bold">รหัสโปรเจกต์</label></div>' +
                '<input id="swal-input1" class="swal2-input mb-3 w-100 m-0" placeholder="เช่น JOB-2024-A01" value="<?php echo $proj['project_code']; ?>">' +
                '<div class="text-start mb-2"><label class="fw-bold">ชื่อโปรเจกต์</label></div>' +
                '<input id="swal-input2" class="swal2-input w-100 m-0" placeholder="ชื่อโครงการ" value="<?php echo $proj['project_name']; ?>">',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                return [
                    document.getElementById('swal-input1').value,
                    document.getElementById('swal-input2').value
                ]
            }
        });

        if (formValues) {
            let newCode = formValues[0];
            let newName = formValues[1];

            if(!newCode || !newName) {
                Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลให้ครบ', 'warning');
                return;
            }

            // ส่งข้อมูลไปบันทึกที่ api_project.php
            $.post("api_project.php", { 
                action: 'edit_info', 
                id: pid, 
                code: newCode,       
                name: newName        
            }, function(res) {
                Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อย', 'success').then(() => {
                    location.reload();
                });
            }).fail(function(xhr) {
                Swal.fire('Error', xhr.responseText, 'error');
            });
        }
    }

    // ฟังก์ชันปิดงาน
    function closeJob() {
        Swal.fire({
            title: 'ยืนยันปิดจ็อบงานนี้?',
            text: "เมื่อปิดแล้วจะไม่สามารถเบิกของเพิ่ม หรือแก้ไขได้อีก (แต่ยังดูประวัติและพิมพ์เอกสารได้)",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ยืนยัน ปิดงาน!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("api_project.php", { action: 'close_job', id: pid }, function() {
                    location.reload();
                });
            }
        })
    }

    function returnItem(sn) {
        // ใช้ SweetAlert ถามยืนยันก่อนคืน
        Swal.fire({
            title: 'ยืนยันคืนสินค้า?',
            text: "ต้องการคืนสินค้า S/N: " + sn + " กลับเข้าคลังใช่หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33', // สีแดงเตือนใจ
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, คืนของ!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ส่งค่าไปหา API ที่เราเพิ่งสร้าง
                $.post("api_return_item.php", { sn: sn }, function(res){
                    let data = JSON.parse(res);
                    if(data.status == 'success') {
                        Swal.fire('สำเร็จ', 'คืนสินค้าเข้าคลังเรียบร้อย', 'success').then(() => {
                            location.reload(); // รีโหลดหน้าเพื่ออัปเดตตาราง
                        });
                    } else {
                        Swal.fire('Error', data.msg, 'error');
                    }
                });
            }
        })
    }
</script>
</body>
</html>