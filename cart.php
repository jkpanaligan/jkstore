<?php
  require_once 'init.php' ;
  include 'head.php';
  include 'navbar.php';
  include 'link_script.php';

  if($cart_id != ''){
    $cartQ = $db->query("SELECT * FROM cart WHERE id = '{$cart_id}'");
    $result = mysqli_fetch_assoc($cartQ);
    $items = json_decode($result['items'],true);
    $i = 1;
    $sub_total = 0;
    $item_count = 0;
  }

?>

<div class="col-md-12 main">
  <div class="row">
    <h2 class="text-center">My Shopping Cart</h2><hr>
    <?php if($cart_id == ''): ?>
      <div class="bg-danger">
        <p class="text-center text-danger">
          Your shopping cart is empty!
        </p>
      </div>
    <?php else: ?>
      <table class="table tablt-bordered table-condensed table-striped">
        <thead><th>#</th><th>Item</th><th>Price</th><th>Quantity</th><th>Size</th><th>Sub Total</th></thead>
        <tbody>
          <?php
            foreach ($items as $item) {
              $product_id = $item['id'];
              $productQ = $db->query("SELECT * FROM products WHERE id = '{$product_id}'");
              $product = mysqli_fetch_assoc($productQ);
              $sArray = explode(',',$product['sizes']);
              foreach($sArray as $sizeString) {
                $s = explode(':',$sizeString);
                if($s[0] == $item['size']){
                  $available = $s[1];
                }
              }
            ?>
            <tr>
              <td><?=$i;?></td>
              <td><?=$product['title'];?></td>
              <td><?=money($product['price']);?></td>
              <td>
                <button class="btn btn-xs btn-default" onclick="update_cart('removeone','<?=$product['id'];?>','<?=$item['size'];?>');">-</button>
                <?=$item['quantity'];?>
                <?php if($item['quantity'] < $available): ?>
                  <button class="btn btn-xs btn-default" onclick="update_cart('addone','<?=$product['id'];?>','<?=$item['size'];?>');">+</button>
                <?php else: ?>
                  <span class="text-danger">Max</span>
                <?php endif; ?>
              </td>
              <td><?=$item['size'];?></td>
              <td><?=money($item['quantity'] * $product['price']);?></td>
            </tr>
          <?php
            $i++;
            $item_count += $item["quantity"];
            $sub_total += ($product['price'] * $item['quantity']);
          }
          $tax = TAXRATE * $sub_total;
//          $tax = number_format($tax,2);
          $grand_total = $tax + $sub_total;
          ?>
        </tbody>
      </table>
      <table class="table table-bordered table-condensed text-right">
        <legend>Totals</legend>
        <thead class="totals-table-header"><th>Total Items</th><th>Sub Total</th><th>Tax</th><th>Grand Total</th></thead>
        <tbody>
          <tr>
            <td><?=$item_count;?></td>
            <td><?=money($sub_total);?></td>
            <td><?=money($tax);?></td>
            <td class="bg-success"><?=money($grand_total);?></td>
          </tr>
        </tbody>
      </table>
      <!-- Check Out Button-->
      <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#checkoutModal">
      <span class="glyphicon glyphicon-shopping-cart"></span>  Check Out >>
      </button>

      <!-- Modal -->
      <div class="modal fade" id="checkoutModal" tableindex="-1" role="dialog" aria-labelledby="checkoutModalLabel">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="checkoutModalLabel">Shipping Address</h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <form action="thank_you.php" method="post" id="payment-form">
                  <span class="bg-danger" id="payment-errors"></span>
                  <input type="hidden" name="tax" value="<?=$tax;?>">
                  <input type="hidden" name="sub_total" value="<?=$sub_total;?>">
                  <input type="hidden" name="grand_total" value="<?=$grand_total;?>">
                  <input type="hidden" name="cart_id" value="<?=$cart_id;?>">
                  <input type="hidden" name="description" value="<?=$item_count.' item'.(($item_count>1)?'s':'').' from JK STORE.';?>">
                  <div id="step1" style="display:block;">
                    <div class="form-group col-md-6">
                      <label for="full_name">Contact Name:</label>
                      <input type="text" name="full_name" class="form-control" id="full_name">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="contact_number">Contact Number:</label>
                      <input type="text" name="contact_number" class="form-control" id="contact_number">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="email">Email:</label>
                      <input type="email" name="email" class="form-control" id="email">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="city">City:</label>
                      <input type="text" name="city" class="form-control" id="city">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="barangay">Barangay:</label>
                      <input type="text" name="barangay" class="form-control" id="barangay">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="street">Street Address:</label>
                      <input type="text" name="street" class="form-control" id="street">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="zip_code">Zip Code:</label>
                      <input type="text" name="zip_code" class="form-control" id="zip_code">
                    </div>
                  </div>
                  <div id="step2" style="display:none;">
                    <div class="form-group col-md-6">
                      <label for="mode_payment">Mode Of Payment:</label>
                      <select class="form-control" name="mode_payment">
                        <option value="COD">Cash On Delivery</option>
                        <option value="Cheque">Cheque</option>
                      </select>
                    </div>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" onclick="check_address();" id="next_button">Next >></button>
              <button type="button" class="btn btn-primary" onclick="back_address();" id="back_button" style="display:none;"><< Back</button>
              <button type="submit" class="btn btn-primary" id="checkout_button" style="display:none;">Check Out >></button>
            </form>
            </div>
          </div>
        </div>
      </div>

    <?php endif; ?>
  </div>
</div>
<script>
  function back_address(){
    jQuery('#payment-errors').html("");
    jQuery('#step1').css("display","block");
    jQuery('#step2').css("display","none");
    jQuery('#next_button').css("display","inline-block");
    jQuery('#back_button').css("display","none");
    jQuery('#checkout_button').css("display","none");
    jQuery('#checkoutModalLabel').html("Shipping Address");
  }

  function check_address(){
    var data = {
      'full_name' : jQuery('#full_name').val(),
      'contact_number' : jQuery('#contact_number').val(),
      'email' : jQuery('#email').val(),
      'city' : jQuery('#city').val(),
      'barangay' : jQuery('#barangay').val(),
      'street' : jQuery('#street').val(),
      'zip_code' : jQuery('#zip_code').val(),
      'mode_payment' : jQuery('#mode_payment').val(),
    };
    jQuery.ajax({
      url : '/jkstore/check_address.php',
      method : 'POST',
      data : data,
      success : function(data){
        if(data != 'passed'){
          jQuery('#payment-errors').html(data);
        }
        if(data = 'passed'){
          jQuery('#payment-errors').html("");
          jQuery('#step1').css("display","none");
          jQuery('#step2').css("display","block");
          jQuery('#next_button').css("display","none");
          jQuery('#back_button').css("display","inline-block");
          jQuery('#checkout_button').css("display","inline-block");
          jQuery('#checkoutModalLabel').html("Select Mode Of Payment");
        }
      },
      error : function(){alert("Something Went Wrong");},
    });
  }

jQuery(function($) {
  $('#payment-form').submit(function(event) {
    var $form = $(this);

    //disable the submit button to prevent repeated clicks
    $form.find('button').prop('disabled', true);

  });
});

</script>

<?php include 'footer.php'; ?>
