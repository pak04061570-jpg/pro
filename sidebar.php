<?php
// หาชื่อไฟล์ปัจจุบัน เช่น "index.php", "projects.php"
$page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <h4 class="text-white text-center fw-bold mb-5 mt-2"><i class="fas fa-cubes me-2"></i>Stock</h4>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo ($page == 'index.php') ? 'active' : ''; ?>" href="index.php">
            <i class="fas fa-warehouse"></i> คลังสินค้า (รับเข้า)
        </a>
        
        <a class="nav-link <?php echo ($page == 'projects.php' || $page == 'project_manage.php' || $page == 'print_job.php') ? 'active' : ''; ?>" href="projects.php">
            <i class="fas fa-hard-hat"></i> โปรเจกต์ (เบิกออก)
        </a>
        
        <a class="nav-link <?php echo ($page == 'products.php' || $page == 'product_details.php') ? 'active' : ''; ?>" href="products.php">
            <i class="fas fa-boxes"></i> สินค้าทั้งหมด (รายงาน)
        </a>
</div>
    </nav>
</div>
