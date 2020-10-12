<?php 
    require_once 'init.php';

    include 'admin_head.php';
    include 'admin_navbar.php';

    //Add Product
    if(isset($_GET['add'])){
        $id = sanitize($_GET['add']);
        $db->query("UPDATE products SET deleted = 0 WHERE id = '$id'");
        header('Location: admin_archived.php');
    }

    $sql = "SELECT * FROM products WHERE deleted = 1 ";
    $presults = $db->query($sql);
?>

<div class="archived-products-section">
    <div class="archived-product-table">
        <div class="archived-product-div"> 
                <h1><span>Archived Products</span></h1>
        </div>
        <table id="categories">
            <thead>
                <th>Product</th>
                <th>Price</th>
                <th>Category</th>
                <th>Add</th>
            </thead>
            <tbody>
            <?php while($product = mysqli_fetch_assoc($presults)):
                $childID = $product['categories'];
                $catSql = "SELECT * FROM categories WHERE id = $childID";
                $result = $db->query($catSql);
                $child = mysqli_fetch_assoc($result);
                $parentID = $child['parent'];
                $pSql = "SELECT * FROM categories WHERE id = $parentID";
                $presult = $db->query($pSql);
                $parent = mysqli_fetch_assoc($presult);
                $category = $parent['category'].'-'.$child['category'];
            ?>
                <tr>
                    
                    <td><?=$product['title'];?></td>
                    <td><?=money($product['price']);?></td>
                    <td><?=$category;?></td>
                    <td>
                        <a href="admin_archived.php?add=<?=$product['id'];?>"><span><strong>+</strong></span></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'admin_footer.php'; ?>
