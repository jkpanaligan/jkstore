<?php
require_once 'init.php';
$full_name = sanitize($_POST['full_name']);
$contact = sanitize($_POST['contact_number']);
$email = sanitize($_POST['email']);
$city = sanitize($_POST['city']);
$barangay = sanitize($_POST['barangay']);
$street = sanitize($_POST['street']);
$zip_code = sanitize($_POST['zip_code']);
$mode_payment = sanitize($_POST['mode_payment']);
$tax = sanitize($_POST['tax']);
$sub_total = sanitize($_POST['sub_total']);
$grand_total = sanitize($_POST['grand_total']);
$cart_id = sanitize($_POST['cart_id']);
$description = sanitize($_POST['description']);
// $charge_amount = number_format($grand_total,2) * 100;
$metadata = array(
  "cart_id"   => $cart_id,
  "tax"       => $tax,
  "sub_total" => $sub_total,
);

//adjust inventory
$itemQ = $db->query("SELECT * FROM cart WHERE id = '{$cart_id}'");
$iresults = mysqli_fetch_assoc($itemQ);
$items = json_decode($iresults['items'],true);
foreach($items as $item){
  $newSizes = array();
  $item_id = $item['id'];
  $productQ = $db->query("SELECT sizes FROM products WHERE id = '{$item_id}'");
  $product = mysqli_fetch_assoc($productQ);
  $sizes = sizesToArray($product['sizes']);
  foreach($sizes as $size){
    if($size['size'] == $item['size']){
      $q = $size['quantity'] - $item['quantity'];
      $newSizes[] = array('size' => $size['size'],'quantity' => $q);
    }else{
      $newSizes[] = array('size' => $size['size'],'quantity' => $size['quantity']);
    }
  }
  $sizeString = sizesToString($newSizes);
  $db->query("UPDATE products SET sizes = '{$sizeString}' WHERE id = '{$item_id}'");
}

//$db->query("UPDATE cart SET paid = 1 WHERE id = '{$cart_id}'");
$db->query("INSERT INTO transactions
  (cart_id,full_name,contact_number,email,city,barangay,street,zip_code,mode_payment,sub_total,tax,grand_total,description) VALUES
  ('$cart_id','$full_name','$contact','$email','$city','$barangay','$street','$zip_code','$mode_payment','$sub_total','$tax','$grand_total','$description')");

$domain = ($_SERVER['HTTP_HOST'] != 'localhost:8080')? '.'.$_SERVER['HTTP_HOST']:false;
setcookie(CART_COOKIE,'',1,"/",$domain,false);
include 'head.php';
include 'navbar.php';

// $txn_id = sanitize((int)$_GET['txn_id']);
// $txnQuery = $db->query("SELECT * FROM transactions WHERE id = '{$txn_id}'");
// $txn = mysqli_fetch_assoc($txnQuery);
// $cart_id = $txn['cart_id'];
$cartQ = $db->query("SELECT * FROM cart WHERE id = '{$cart_id}'");
$cart = mysqli_fetch_assoc($cartQ);
$items = json_decode($cart['items'],true);
$idArray = array();
$products = array();
foreach($items as $item){
  $idArray[] = $item['id'];
}
$ids = implode(',',$idArray);
$productQ = $db->query(
  "SELECT i.id as 'id', i.title as 'title', c.id as 'cid', c.category as 'child', p.category as 'parent'
  FROM products i
  LEFT JOIN categories c ON i.categories = c.id
  LEFT JOIN categories p ON c.parent = p.id
  WHERE i.id IN ({$ids})
  ");
while($p = mysqli_fetch_assoc($productQ)){
    foreach($items as $item){
      if($item['id'] == $p['id']){
        $x = $item;
        continue;
      }
    }
  $products[] = array_merge($x,$p);
}
?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/fotorama/4.6.4/fotorama.js"></script>
    
  <h1 class="text-center text-success">Thank You!</h1>
  <h2 class="text-center">Your receipt number is: <strong><?=$cart_id;?></strong></h2>
  <h3 class="text-center">Items Ordered</h3>
  <div>
    <table class="table table-bordered table-striped table-auto table-condensed">
      <thead>
        <th>Quantity</th>
        <th>Title</th>
        <th>Category</th>
        <th>Size</th>
      </thead>
      <tbody>
        <?php foreach($products as $product): ?>
          <tr>
            <td><?=$product['quantity'];?></td>
            <td><?=$product['title'];?></td>
            <td><?=$product['parent'].' ~ '.$product['child'];?></td>
            <td><?=$product['size'];?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h3 class="text-center">Sub Total is: P<strong><?=$sub_total;?></strong></h3>
  <h3 class="text-center">Tax is: P<strong><?=$tax;?></strong></h3>
  <h3 class="text-center">Grand Total is: P<strong><?=$grand_total;?></strong></h3>
  <h3 class="text-center">Your order will be shipped to the address below:</h3>
  <h3><address class="text-center"><strong>
    <?=$full_name;?><br>
    <?=$street;?><br>
    <?=$barangay;?><br>
    <?=$city. ', '.$zip_code;?><br></strong>
  </address></h3>
<?php
?>
