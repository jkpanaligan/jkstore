<?php 
    require_once 'init.php';

    include 'admin_head.php';
    include 'admin_navbar.php';

    // get brands from database
    $sql = "SELECT * FROM brands";
    $results = $db->query($sql);
    $errors = array();

    //Edit brand
    if(isset($_GET['edit']) && !empty($_GET['edit'])){
        $edit_id = (int)$_GET['edit'];
        $edit_id = sanitize($edit_id);
        $sql2 = "SELECT * FROM brands WHERE id = '$edit_id'";
        $edit_result = $db->query($sql2);
        $eBrand = mysqli_fetch_assoc($edit_result);
    }

    //Delete Brand
    if(isset($_GET['delete']) && !empty($_GET['delete'])){
        $delete_id = (int)$_GET['delete'];
        $delete_id = sanitize($delete_id);
        $sql = "DELETE FROM brands WHERE id = '$delete_id'";
        $db->query($sql);
        header('Location: admin_brand.php');
    }

    //if add form is submitted
    if(isset($_POST['add_submit'])){
        $brand = sanitize($_POST['brand']);

        //check if brand is blank
        if($_POST['brand'] == ''){
        $errors[] .= 'You must enter a brand!';
        }

         // check if brand exist in database
        $sql = "SELECT * FROM brands WHERE brand = '$brand'";
        if(isset($_GET['edit'])){
        $sql = "SELECT * FROM brands WHERE brand = '$brand' AND id != '$edit_id'";
        }
        $result = $db->query($sql);
        $count =mysqli_num_rows($result);
        if($count > 0){
        $errors[] .= $brand.' already exists. Please Choose another brand name...';
        }

        if(!empty($errors)){
            //display errors
            $display = display_errors($errors); ?>
            <script>
              jQuery('document').ready(function(){
                  jQuery('#errors').html('<?=$display; ?>');
              });
            </script>
        <?php }else{
            
        //Add brand to database
        $sql = "INSERT INTO brands (brand) VALUES ('$brand')";
        if(isset($_GET['edit'])){
            // upate brand
            $sql = "UPDATE brands SET brand = '$brand' WHERE id = '$edit_id'";
        }
        $db->query($sql);
        header('Location: admin_brand.php');
        }
    }
?>
    
<div class="brands-section">
    <div class="brand-container">
        <div class="form">
            <form action="admin_brand.php<?=((isset($_GET['edit']))?'?edit='.$edit_id:'');?>" class="form-wrapper" method="post">
            <?php
                $brand_value = '';
                if(isset($_GET['edit'])){
                    $brand_value = $eBrand['brand'];
                }else{
                    if(isset($_POST['brand'])){
                    $brand_value = sanitize(($_POST['brand']));
                    }
                }
            ?>
                <div id="errors"></div>
                <label for=""><?=((isset($_GET['edit']))?'Edit':'Add'); ?> Brand:</label>
                <input type="text" name="brand" id="brand" value="<?=$brand_value; ?>" placeholder="Brand">
                <input type="submit" name="add_submit" value="<?=((isset($_GET['edit']))?'Update':'Add'); ?> Brand" id="button">
                <?php if(isset($_GET['edit'])): ?>
                    <a href="admin_brand.php"><input type="button" value="Cancel" id="cancel"></a>      
                <?php endif; ?>
            </form>
        </div>
        <div class="brand-table">
            <h1><span>Brands</span></h1>
            <table id="brands">
                <thead>
                    <th>Brand</th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody>
                    <?php while($brand = mysqli_fetch_assoc($results)): ?>
                    <tr>
                        <td><?= $brand['brand']; ?></td>
                        <td><a href="admin_brand.php?edit=<?=$brand['id'];?>"><span>Edit</span></a></td>
                        <td><a href="admin_brand.php?delete=<?=$brand['id'];?>"><span style="color: red;">Delete</span></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>    
</div>

<?php include 'admin_footer.php'; ?>