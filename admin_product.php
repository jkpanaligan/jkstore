<?php 
    require_once 'init.php';

    include 'admin_head.php';
    include 'admin_navbar.php';
?>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<?php
    //Delete Product
    if(isset($_GET['delete'])){
        $id = sanitize($_GET['delete']);
        $db->query("UPDATE products SET deleted = 1 WHERE id = '$id'");
        header('Location: admin_product.php');
    }

     $dbpath = '';

    if(isset($_GET['add']) || isset($_GET['edit'])){
    
        $brandQuery = $db->query("SELECT * FROM brands ORDER BY brand");
        $parentQuery = $db->query("SELECT * FROM categories WHERE parent = 0 ORDER BY category");
        $title = ((isset($_POST['title']) && $_POST['title'] != '')?sanitize($_POST['title']):'');
        $brand = ((isset($_POST['brand']) && !empty($_POST['brand']))?sanitize($_POST['brand']):'');
        $parent = ((isset($_POST['parent']) && !empty($_POST['parent']))?sanitize($_POST['parent']):'');
        $category = ((isset($_POST['child'])) && !empty($_POST['child'])?sanitize($_POST['child']):'');
        $price = ((isset($_POST['price']) && $_POST['price'] != '')?sanitize($_POST['price']):'');
        $list_price = ((isset($_POST['list_price']) && $_POST['list_price'] != '')?sanitize($_POST['list_price']):'');
        $description = ((isset($_POST['description']) && $_POST['description'] != '')?sanitize($_POST['description']):'');
        $sizes = ((isset($_POST['sizes']) && $_POST['sizes'] != '')?sanitize($_POST['sizes']):'');
        $sizes = rtrim($sizes,',');
        $saved_image = '';

        if(isset($_GET['edit'])){
            $edit_id = (int)$_GET['edit'];
            $productResults = $db->query("SELECT * FROM products WHERE id = '$edit_id'");
            $product = mysqli_fetch_assoc($productResults);
            if(isset($_GET['delete_image'])){
              $imgi = (int)$_GET['imgi'] - 1;
              $images = explode(',',$product['image']);
              $image_url = $_SERVER['DOCUMENT_ROOT'].$images[$imgi];
              unlink($image_url);
              unset($images[$imgi]);
              $imageString = implode(',',$images);
              $db->query("UPDATE products SET image = '{$imageString}' WHERE id = '$edit_id'");
              header('Location: admin_product.php?edit='.$edit_id);
            }
            $category = ((isset($_POST['child']) && $_POST['child'] != '')?sanitize($_POST['child']):$product['categories']);
            $title = ((isset($_POST['title']) && $_POST['title'] != '')?sanitize($_POST['title']):$product['title']);
            $brand = ((isset($_POST['brand']) && $_POST['brand'] != '')?sanitize($_POST['brand']):$product['brand']);
            $parentQ = $db->query("SELECT * FROM categories WHERE id = '$category'");
            $parentResult = mysqli_fetch_assoc($parentQ);
            $parent = ((isset($_POST['parent']) && $_POST['parent'] != '')?sanitize($_POST['parent']):$parentResult['parent']);
            $price = ((isset($_POST['price']) && $_POST['price'] != '')?sanitize($_POST['price']):$product['price']);
            $list_price = ((isset($_POST['list_price']))?sanitize($_POST['list_price']):$product['list_price']);
            $description = ((isset($_POST['description']))?sanitize($_POST['description']):$product['description']);
            $sizes = ((isset($_POST['sizes']) && $_POST['sizes'] != '')?sanitize($_POST['sizes']):$product['sizes']);
            $sizes = rtrim($sizes,',');
            $saved_image = (($product['image'] != '')?$product['image']:'');
            $dbpath = $saved_image;
        }

        if (!empty($sizes)) {
        $sizeString = sanitize($sizes);
        $sizeString = rtrim($sizeString,',');
        $sizesArray  = explode(',',$sizeString);
        $sArray = array();
        $qArray = array();
        // $tArray = array();
        foreach ($sizesArray as $ss) {
            $s = explode(':', $ss);
            $sArray[] = $s[0];
            $qArray[] = $s[1];
            // $tArray[] = $s[2];
        }
        }else{$sizeArray = array();}  

        if ($_POST) {
            $errors = array();
            $required = array('title', 'brand', 'price', 'parent', 'child', 'sizes');
            $allowed = array('png', 'jpg', 'jpeg', 'gif');
            $uploadPath = array();
            $tmpLoc = array();
            foreach($required as $field){
              if($_POST[$field] == ''){
                $errors[] = 'All Fields With and Astrisk are required.';
                break;
              }
            }
            $photoCount = count($_FILES['photo']['name']);
             if ($photoCount > 0) {
               for($i = 0; $i < $photoCount; $i++){echo $i;
                $name = $_FILES['photo']['name'][$i];
                $nameArray = explode('.',$name);
                $fileName = $nameArray[0];
                $fileExt = $nameArray[1];
                $mime = explode('/',$_FILES['photo']['type'][$i]);
                $mimeType = $mime[0];
                $mimeExt = $mime[1];
                $tmpLoc[] = $_FILES['photo']['tmp_name'][$i];
                $fileSize = $_FILES['photo']['size'][$i];
                $uploadName = md5(microtime()).'.'.$fileExt;
                $uploadPath[] = BASEURL.'assets/images/product_images/'.$uploadName;
                if($i != 0){
                  $dbpath .= ',';
                }
                $dbpath .= '/jkstore/assets/images/product_images/'.$uploadName;
                if($mimeType != 'image'){
                  $errors[] = 'The file must be an image.';
                }
                if(!in_array($fileExt, $allowed)){
                  $errors[] = 'The file extension must be a png, jpg, jpeg, or jif.';
                }
                if($fileSize > 15000000){
                  $errors[] = 'The files size must be under 15MB.';
                }
                if($fileExt != $mimeExt && ($mimeExt == 'jpeg' && $fileExt != 'jpg')){
                  $errors[] = 'File extension does not match the file.';
                }
              }
            }
            if(!empty($errors)){
                //display errors
                $display = display_errors($errors); ?>
                <script>
                jQuery('document').ready(function(){
                    jQuery('#errors').html('<?=$display; ?>');
                });
                </script> <?php
            }else{
              if($photoCount > 0){
              // upload file and insert into database
                for($i = 0; $i < $photoCount; $i++){
                  move_uploaded_file($tmpLoc[$i], $uploadPath[$i]);
                }
              }
              $insertSql = "INSERT INTO products (`title`, `price`, `list_price`, `brand`, `categories`, `sizes`, `image`, `description`)
              VALUES ('$title', '$price', '$list_price', '$brand', '$category', '$sizes', '$dbpath', '$description')";
              if(isset($_GET['edit'])){
                $insertSql = "UPDATE products SET title = '$title', price = '$price', list_price = '$list_price',
                brand = '$brand', categories = '$category', sizes = '$sizes', image = '$dbpath', description = '$description'
                WHERE id = '$edit_id'";
              }
              $db->query($insertSql);
              header('Location: admin_product.php');
            }
          }

        ?>

    <div class="products-section">
        <div class="product-container">
    <div id="errors"></div>
            <form action="admin_product.php?<?=((isset($_GET['edit']))?'edit='.$edit_id:'add=1');?>" class="form-wrapper" method="post" enctype="multipart/form-data">
                <div class="groupone">
                    <div>
                        <label for="title">Title*:</label>
                        <input type="text" name="title" class="form-control" id="title" value="<?=$title;?>">
                    </div>
                    <div>
                        <label for="brand">Brand*:</label>
                        <select id="brand" name="brand">
                            <option value="<?=(($brand == '')?:'');?>"></option>
                            <?php while($b = mysqli_fetch_assoc($brandQuery)): ?>
                                <option value="<?=$b['id'];?>"<?=(($brand == $b['id'])?' selected':'');?>><?=$b['brand'];?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="parent">Parent Category*:</label>
                        <select id="parent" name="parent">
                        <option value="<?=(($parent == '')?' selected':'');?>"></option>
                            <?php while($p = mysqli_fetch_assoc($parentQuery)): ?>
                            <option value="<?=$p['id'];?>"<?=(($parent == $p['id'])?' selected':'');?>><?=$p['category'];?></option>
                        <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="child">Child Category*:</label>
                        <select id="child" name="child">
                            <option value="">Child Category</option>
                        </select>
                    </div>
                </div>    
                <div class="grouptwo">
                    <div>
                        <label for="price">Price*:</label>
                        <input type="text" id="price" name="price" value="<?=$price;?>">
                    </div>
                    <div>
                        <label for="list_price">List Price:</label>
                        <input type="text" id="list_price" name="list_price" value="<?=$list_price;?>">
                    </div>
                    <div>
                        <label>Qty & Sizes*:</label>
                        <button onclick="jQuery('#sizesModal').modal('toggle');return false">Quantity & Sizes</button>
                    </div>
                    <div>
                        <label for="sizes">Sizes & Qty Preview</label>
                        <input type="text" id="sizes" name="sizes" value="<?=$sizes;?>" readonly>
                    </div>
                </div>
                <div class="group3">
                    <div>
                    <?php if($saved_image != ''): ?>
                    <?php
                        $imgi =1;
                        $images = explode(',',$saved_image); ?>
                        <?php foreach($images as $image) : ?>
                    <div class="saved-image col-md-4">
                        <img src="<?=$image;?>" alt="saved image"/><br>
                        <a href="admin_product.php?delete_image=1&edit=<?=$edit_id;?>&imgi=<?=$imgi;?>" class="text-danger">Delete Image</a>
                    </div>
                    <?php
                        $imgi++;
                        endforeach; 
                    ?>
                    <?php else: ?>
                        <label for="photo">Product Photo:</label>
                        <input type="file" id="photo" name="photo[]" multiple>
                    <?php endif; ?>    
                    </div>
                    <div>
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="6"><?=$description;?></textarea>
                    </div>
                </div>    
                <div class="group4">
                    <div>
                        <input type="submit" value="<?=((isset($_GET['edit']))?'Update':'Add'); ?> Product" id="button">
                        <a href="admin_product.php"><input type="button" value="Cancel" id="cancel"></a>   
                    </div>
                </div>
            </form>

            <!--Modal-->
            <div class="modal" id="sizesModal">
                <div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="sizesModalLabel">Size & Quantity</h4>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <?php for($i=1; $i <= 12; $i++): ?>
                                    <div>
                                        <label for="size<?=$i;?>">Size:</label>
                                        <input type="text" name="size<?=$i;?>" id="size<?=$i;?>" value="<?=((!empty($sArray[$i-1]))?$sArray[$i-1]:'')?>" class="form-control">

                                    </div>
                                    <div class="right-div">
                                        <label for="qty<?=$i;?>">Quantity:</label>
                                        <input type="number" name="qty<?=$i;?>" id="qty<?=$i;?>" value="<?=((!empty($qArray[$i-1]))?$qArray[$i-1]:'')?>" min="0" class="form-control">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-dismiss="modal">Close</button>
                            <button type="button" onclick="updateSizes();jQuery('#sizesModal').modal('toggle');return false;">Save changes</button>
                        </div>
                </div>
            </div>
            </div>
        </div>  
    </div>
<?php
    
    } else {    

    $sql = "SELECT * FROM products WHERE deleted = 0 ";
    $presults = $db->query($sql);
    if(isset($_GET['featured'])){
        $id = (int)$_GET['id'];
        $featured = (int)$_GET['featured'];
        $featuredSql = "UPDATE products SET featured = '$featured' WHERE id = '$id'";
        $db->query($featuredSql);
        header('Location: admin_product.php');
    }
?>
    
<div class="products-section">
    <div class="product-table">
        <div class="product-div"> 
                <h1><span>Products</span></h1>
                <a href="admin_product.php?add=1">Add Product</a>
        </div>
        <table id="categories">
            <thead>
                <th>Product</th>
                <th>Price</th>
                <th>Category</th>
                <th>Featured</th>
                <th></th>
                <th></th>
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
                        <a href="admin_product.php?featured=<?=(($product['featured'] == 0)?'1':'0');?>&id=<?=$product['id'];?>">
                        <span><?=(($product['featured'] == 1)?'-':'+');?></span>
                        &nbsp <?=(($product['featured'] == 1)?'Featured Product':'');?>
                        </a>
                    </td>
                    <td><a href="admin_product.php?edit=<?=$product['id'];?>"><span>Edit</span></a></td>
                    <td><a href="admin_product.php?delete=<?=$product['id'];?>"><span style="color: red;">Delete</span></a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
 <?php } include 'admin_footer.php'; ?>

 <script>
  jQuery('document').ready(function(){
    get_child_options('<?=$category;?>');
  });
</script>