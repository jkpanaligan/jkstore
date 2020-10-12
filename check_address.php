<?php
  require_once $_SERVER['DOCUMENT_ROOT'].'/jkstore/init.php';
  $fullname = sanitize($_POST['full_name']);
  $contact = sanitize($_POST['contact_number']);
  $email = sanitize($_POST['email']);
  $city = sanitize($_POST['city']);
  $barangay = sanitize($_POST['barangay']);
  $street = sanitize($_POST['street']);
  $zip_code = sanitize($_POST['zip_code']);
  $errors = array();
  $required = array(
    'full_name'       => 'Contact Name',
    'contact_number'  => 'Contact Number',
    'email'           => 'Email',
    'city'            => 'City',
    'barangay'        => 'Barangay',
    'street'          => 'Street Address',
    'zip_code'        => 'Zip Code',
  );

  //Check if all requied fields are filled out
  foreach($required as $f => $d){
    if(empty($_POST[$f]) || $_POST[$f] == ''){
      $errors[] = $d.' is required.';
    }
  }

  //check if valid email address
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    $errors[] = 'Please enter a valid email.';
  }

  if(!empty($errors)){
    echo display_errors($errors);
  }else{
    echo 'passed';
  }
?>
