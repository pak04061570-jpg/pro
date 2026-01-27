<?php
include 'db_connect.php';

// 1. แก้ไข SQL ให้ดึงชื่อจากตารางลูก (JOIN)
$sql = "SELECT p.*, 
               t.name AS type_name, 
               s.name AS supplier_name 
        FROM products p 
        LEFT JOIN product_types t ON p.type_id = t.id 
        LEFT JOIN suppliers s ON p.supplier_id = s.id 
        ORDER BY p.last_updated DESC";

$result = $conn->query($sql);
?>
<table id="proTable" class="table table-hover align-middle w-100">
    <thead class="table-light">
        <tr>
            <th width="15%">SKU / Code</th>
            <th width="30%">สินค้า</th>
            <th width="15%">ประเภท</th>
            <th width="15%">Supplier</th>
            
            <th class="text-end">ราคา</th>
            <th class="text-center">สถานะ</th>
            <th class="text-center">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): 
            if($row['quantity'] == 0) $bg = 'bg-danger';
            elseif($row['quantity'] < 5) $bg = 'bg-warning text-dark';
            else $bg = 'bg-success';
        ?>
        <tr>
            <td><span class="badge bg-light text-dark border"><?php echo $row['barcode']; ?></span></td>
            <td>
                <div class="fw-bold"><?php echo $row['name']; ?></div>
                <small class="text-muted">หน่วย: <?php echo $row['unit']; ?></small>
            </td>
            
            <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle"><?php echo $row['product_type']; ?></span></td>
            <td><small class="text-secondary"><i class="fas fa-store me-1"></i><?php echo $row['supplier']; ?></small></td>

            <td class="text-end text-primary fw-bold"><?php echo number_format($row['price_sell'], 2); ?></td>
            <td class="text-center"><span class="badge <?php echo $bg; ?> rounded-pill"><?php echo $row['quantity']; ?></span></td>
            <td class="text-center">
                <a href="product_details.php?barcode=<?php echo $row['barcode']; ?>" 
   class="btn btn-sm btn-outline-primary rounded-pill me-1" 
   title="ดู Serial Number">
    <i class="fas fa-barcode"></i> S/N
</a>
                
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" 
    onclick="openEditModal(
        <?php echo $row['id']; ?>, 
        '<?php echo $row['name']; ?>', 
        '<?php echo $row['product_type']; ?>',  /* เพิ่ม Type */
        '<?php echo $row['supplier']; ?>',      /* เพิ่ม Supplier */
        <?php echo $row['price_sell']; ?>, 
        <?php echo $row['quantity']; ?>, 
        '<?php echo $row['unit']; ?>'
    )">
    <i class="fas fa-pen me-1"></i> แก้ไข
</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>