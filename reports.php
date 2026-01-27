<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงาน - MyStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f7f9fc; }
        .main-content { margin-left: 250px; padding: 30px; }
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background: white; border-right: 1px solid #e1e4e8; padding-top: 20px; }
        .sidebar .nav-link { color: #555; padding: 12px 20px; margin: 4px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover { background-color: #e8f5e9; color: #2e7d32; }
    </style>
<head> 
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h3>📊 รายงานและเอกสาร</h3>
    <p class="text-muted">เลือกประเภทรายงานที่ต้องการ Export</p>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-5">
                    <i class="fas fa-boxes text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5>รายงานสินค้าคงเหลือ</h5>
                    <p class="text-muted small">แสดงรายการสินค้า จำนวนคงเหลือ และหน่วยนับ สำหรับการตรวจนับสต็อก</p>
                    <a href="export.php" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-file-excel"></i> ดาวน์โหลด Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-5">
                    <i class="fas fa-money-bill-wave text-success mb-3" style="font-size: 3rem;"></i>
                    <h5>รายงานมูลค่าสินค้า</h5>
                    <p class="text-muted small">แสดงราคาขาย และมูลค่ารวมของสินค้าทั้งหมดในคลัง</p>
                    <button class="btn btn-outline-secondary w-100 mt-3" disabled>
                        (กำลังพัฒนา...)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
<html>