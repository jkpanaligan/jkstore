<?php 
    require_once 'init.php' ;
    include 'head.php';
    include 'navbar.php';
    include 'link_script.php';

    $sql = "SELECT * FROM products WHERE featured = 1";
    $featured = $db->query($sql);
?>

    

<div class="products-section">
    <h2>START YOUR <span>ORDER TODAY</span></h2>
    <div class="products-list">
        <?php while($product = mysqli_fetch_assoc($featured)) : ?>
        <div class="product" onclick="detailsmodal(<?= $product['id']; ?>)">
            <div class="hover-details">
              <div class="price-container">
                <p><span><?= $product['title']; ?></span></p>
                <p>PHP 1500.00</p>
              </div>
              <div class="image-container">
                <img
                  src="./assets/images/products-cart-icon.png"
                  alt="product cart icon"
                />

              </div>
            </div>
            <div class="shoes-container">
                <div class="image-container">
                    <?php $photos = explode(',',$product['image']); ?>
                    <img height="150" width="200" src="<?= $photos[0]; ?>" />
                </div>                
                <p><span><?= $product['title']; ?></span></p>
            </div>
            <div class="price-container">
                <p>PHP <?= $product['price']; ?></p>
                <div class="image-container">
                <img
                  src="./assets/images/products-cart-icon.png"
                  alt="product cart icon"
                />
              </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>    
</div>

<?php include 'footer.php';