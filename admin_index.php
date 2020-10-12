<?php 
    require_once 'init.php' ;

    include 'admin_head.php';
    include 'admin_navbar.php';
    include 'link_script.php';

?>

<!--Orders To Fill-->
<?php
  $txnQuery = "SELECT t.id, t.cart_id, t.full_name, t.description, t.txn_date, t.grand_total, c.items, c.paid, c.shipped
                FROM transactions t
                LEFT JOIN cart c ON t.cart_id = c.id
                WHERE c.paid = 0 AND c.shipped = 0
                ORDER BY t.txn_date";
  $txnResults = $db->query($txnQuery);

  $txnQuery1 = "SELECT t.id, t.cart_id, t.full_name, t.description, t.txn_date, t.grand_total, c.items, c.paid, c.shipped
                FROM transactions t
                LEFT JOIN cart c ON t.cart_id = c.id
                WHERE c.paid = 0 AND c.shipped = 1
                ORDER BY t.txn_date";
  $txnResults1 = $db->query($txnQuery1);
?>
<div class="col-md-12">
  <h3 class="text-center">Orders To Ship</h3>
  <table class="table table-condensed table-bordered table-striped">
    <thead>
      <th></th><th>Name</th><th>Description</th><th>Total</th><th>Date</th>
    </thead>
    <tbody>
      <?php while($order = mysqli_fetch_assoc($txnResults)): ?>
        <tr>
          <td><a href="admin_orders.php?txn_id=<?=$order['id'];?>" class="btn btn-xs btn-info">Details</a></td>
          <td><?=$order['full_name'];?></td>
          <td><?=$order['description'];?></td>
          <td><?=money($order['grand_total']);?></td>
          <td><?=pretty_date($order['txn_date']);?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'admin_footer.php';