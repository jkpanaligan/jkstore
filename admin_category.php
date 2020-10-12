<?php 
    require_once 'init.php';

    include 'admin_head.php';
    include 'admin_navbar.php';

    $sql = "SELECT * FROM categories WHERE parent = '0'";
    $result = $db->query($sql);
    $errors = array();
    $category = '';
    $post_parent = '';

    //Edit category
    if(isset($_GET['edit']) && !empty($_GET['edit'])){
        $edit_id = (int)$_GET['edit'];
        $edit_id = sanitize($edit_id);
        $edit_sql = "SELECT * FROM categories WHERE id = '$edit_id'";
        $edit_result = $db->query($edit_sql);
        $edit_category = mysqli_fetch_assoc($edit_result);
    }

    //Delete category
    if(isset($_GET['delete']) && !empty($_GET['delete'])){
        $delete_id = (int)$_GET['delete'];
        $delete_id = sanitize($delete_id);
        $sql = "SELECT * FROM categories WHERE id = '$delete_id'";
        $result = $db->query($sql);
        $category = mysqli_fetch_assoc($result);
        if($category['parent'] == 0){
        $sql = "DELETE FROM categories WHERE parent = '$delete_id'";
        $db->query($sql);
        }
        $dsql = "DELETE FROM categories WHERE id = '$delete_id'";
        $db->query($dsql);
        header('Location: admin_category.php');
    }

    //Process Form
    if(isset($_POST) && !empty($_POST)){
        $post_parent = sanitize($_POST['parent']);
        $category = sanitize($_POST['category']);
        $sqlform = "SELECT * FROM categories WHERE category = '$category' AND parent = '$post_parent'";
        if(isset($_GET['edit'])){
        $id = $edit_category['id'];
        $sqlform = "SELECT * FROM categories WHERE category = '$category' AND parent = '$post_parent' AND id != '$id'";
        }
        $fresult = $db->query($sqlform);
        $count = mysqli_num_rows($fresult);
        //if category is blank
        if($category == ''){
        $errors[] .= 'The category cannot be left blank.';
        }

        //If exist in the Database
        if($count > 0){
        $errors[] .= $category. ' already exists. Please choose a new category.';
        }

        //Display errors or update Database
        if(!empty($errors)){
        //display errors
        $display = display_errors($errors); ?>
        <script>
            jQuery('document').ready(function(){
                jQuery('#errors').html('<?=$display; ?>');
            });
        </script>
        <?php }else{
        //update database
        $updatesql = "INSERT INTO categories (category, parent) VALUES ('$category','$post_parent')";
        if(isset($_GET['edit'])){
            $updatesql = "UPDATE categories SET category = '$category', parent = '$post_parent' WHERE id = '$edit_id'";
        }
        $db->query($updatesql);
        header('Location: admin_category.php');
        }
    }

    $category_value = '';
    $parent_value = 0;

    if(isset($_GET['edit'])){
        $category_value = $edit_category['category'];
        $parent_value = $edit_category['parent'];
      }else{
        if(isset($_POST)){
          $category_value = $category;
          $parent_value = $post_parent;
        }
      }
?>
    
<div class="categories-section">
    <div class="category-container">
        <div class="form">
            <form action="admin_category.php<?=((isset($_GET['edit']))?'?edit='.$edit_id:'');?>" class="form-wrapper" method="post">
                <div id="errors"></div>
                    <label for=""> Parent:</label>
                    <select name="parent" id="parent">
                    <option value="0"<?=(($parent_value == 0)?' selected="selected"':'');?>>Parent</option>
                        <?php while($parent = mysqli_fetch_assoc($result)) : ?>
                        <option value="<?=$parent['id'];?>"<?=(($parent_value == $parent['id'])?' selected="selected"':'');?>><?=$parent['category'];?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="category">Category:</label>
                    <input type="text" class="form-control" id="category" name="category" value="<?=$category_value; ?>" placeholder="Category">
                    <input type="submit" value="<?=((isset($_GET['edit']))?'Update':'Add'); ?> Category" id="button">
                    <?php if(isset($_GET['edit'])): ?>
                        <a href="admin_category.php"><input type="button" value="Cancel" id="cancel"></a>      
                    <?php endif; ?>
            </form>
        </div>
        <div class="category-table">
            <h1><span>Categories</span></h1>
            <table id="categories">
                <thead>
                    <th>Category</th>
                    <th>Parent</th>
                    <th></th>
                    <th></th>
                </thead>
                <tbody>
                    
                    <?php
                        $sql = "SELECT * FROM categories WHERE parent = '0'";
                        $result = $db->query($sql);
                        while($parent = mysqli_fetch_assoc($result)):
                        $parent_id = (int)$parent['id'];
                        $sql2 = "SELECT * FROM categories WHERE parent ='$parent_id'";
                        $cresult = $db->query($sql2);
                    ?>

                    <tr id="bg-primary">
                        <td><?=$parent['category'];?></td>
                        <td>Parent</td>
                        <td></td>
                        <td></td>
                    </tr>
                        <?php while($child = mysqli_fetch_assoc($cresult)): ?>

                    <tr id="bg-info">
                        <td><?=$child['category'];?></td>
                        <td><?=$parent['category'];?></td>
                        <td><a href="admin_category.php?edit=<?=$child['id'];?>"><span>Edit</span></a></td>
                        <td><a href="admin_category.php?delete=<?=$child['id'];?>"><span style="color: red;">Delete</span></a></td>
                    </tr>
                
                        <?php endwhile; ?>
                        <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>    
</div>

<?php include 'admin_footer.php'; ?>