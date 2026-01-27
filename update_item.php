<?php
include 'db_connect.php';

if(isset($_POST['id'])){   // รับค่า
    $id = $_POST['id'];
    $name = $_POST['name'];
    
  
    $type = $_POST['type'];      
    $supplier = $_POST['supplier']; 
    
    $price = $_POST['price'];
    $unit = $_POST['unit'];

    // เพิ่มการอัปเดต product_type และ supplier ใน SQL
    $sql = "UPDATE products SET 
            name='$name', 
            product_type='$type', 
            supplier='$supplier', 
            price_sell='$price', 
            unit='$unit' 
            WHERE id=$id";

    if($conn->query($sql) === TRUE) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>