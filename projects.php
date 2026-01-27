<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการโปรเจกต์ - ProStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; position: relative; transition: 0.2s; }
        .card-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        
        /* ปุ่มลบที่มุมการ์ด */
        .btn-delete-project {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #ef4444; /* สีแดง */
            background: rgba(239, 68, 68, 0.1);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-delete-project:hover { background: #ef4444; color: white; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-folder-open me-2 text-warning"></i>รายการโปรเจกต์ (Projects)</h4>
        <button class="btn btn-primary rounded-pill px-4" onclick="createProject()">
            <i class="fas fa-plus me-2"></i> เปิดไซต์งานใหม่
        </button>
    </div>

    <div class="row" id="projectList">
        <?php
        $result = $conn->query("SELECT * FROM projects WHERE type='outbound' ORDER BY id DESC");
        while($row = $result->fetch_assoc()):
            $pid = $row['id'];
            $count = $conn->query("SELECT COUNT(*) as c FROM product_serials WHERE project_id = $pid")->fetch_assoc()['c'];
            
            // เช็คสถานะเพื่อเปลี่ยนสี
            $status_badge = ($row['status'] == 'Closed') 
                ? '<span class="badge bg-danger ms-2">Closed</span>' 
                : '<span class="badge bg-success ms-2">Open</span>';
        ?>
        <div class="col-md-4 mb-4">
            <div class="card card-custom h-100">
                
                <button class="btn-delete-project" onclick="deleteProject(<?php echo $pid; ?>, '<?php echo $row['project_name']; ?>', <?php echo $count; ?>)" title="ลบโปรเจกต์">
                    <i class="fas fa-trash-alt"></i>
                </button>

                <div class="card-body">
                    <h5 class="fw-bold mb-1 pe-4 text-truncate"><?php echo $row['project_name']; ?></h5>
                    <div class="mb-3">
                        <small class="text-muted">สร้างเมื่อ: <?php echo date('d/m/Y', strtotime($row['created_at'])); ?></small>
                        <?php echo $status_badge; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3">
                        <span class="text-secondary">จำนวนของในไซต์</span>
                        <span class="fw-bold text-primary fs-5"><?php echo $count; ?> ชิ้น</span>
                    </div>

                    <a href="project_manage.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary w-100 rounded-pill">
                        <i class="fas fa-box-open me-2"></i> จัดการสินค้า / เบิกของ
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    async function createProject() {
        const { value: text } = await Swal.fire({
            title: 'ตั้งชื่อโปรเจกต์ใหม่',
            input: 'text',
            inputPlaceholder: 'เช่น ติดตั้งกล้อง หมู่บ้าน A...',
            showCancelButton: true
        });

        if (text) {
            $.post("api_project.php", { action: 'create', name: text, type: 'outbound' }, function(res){
                location.reload();
            });
        }
    }

    // ฟังก์ชันลบโปรเจกต์
    function deleteProject(id, name, count) {
        // 1. เช็คก่อนว่ามีของค้างไหม
        if(count > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'ลบไม่ได้!',
                text: 'โปรเจกต์ "' + name + '" ยังมีสินค้าค้างอยู่ ' + count + ' ชิ้น กรุณาคืนของเข้าคลังให้หมดก่อนลบครับ'
            });
            return;
        }

        // 2. ยืนยันการลบ
        Swal.fire({
            title: 'ลบโปรเจกต์: ' + name + '?',
            text: "คุณต้องการลบโปรเจกต์นี้ถาวรใช่ไหม?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("api_project.php", { action: 'delete_project', id: id }, function(res){
                    let data = JSON.parse(res);
                    if(data.status == 'success') {
                        Swal.fire('ลบสำเร็จ', '', 'success').then(() => location.reload());
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