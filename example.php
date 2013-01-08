<?php

require_once('PHPPwinty.php');

$pwinty = new PHPPwinty();

// create the order
$order = $pwinty->createOrder('John Doe', '123 Evergreen Terrace', '', 'Beverly Hills', 'CA', '90210', 'United States', 'Photos by Picisto');

// add some photos
$photo1 = $pwinty->addPhoto($order, '4x6', 'http://www.picisto.com/photos/picisto-20120608100135-925870.jpg', '1', 'ShrinkToFit');
$photo2 = $pwinty->addPhoto($order, '6x6', 'http://www.picisto.com/photos/picisto-20120601010631-895061.jpg', '4', 'ShrinkToFit');
$photo3 = $pwinty->addPhoto($order, '5x7', 'http://www.picisto.com/photos/picisto-20120626210525-763773.jpg', '3', 'ShrinkToFit');

// add a document
$document = $pwinty->addDocument($order, 'invoice.pdf');

// add a sticker
$document = $pwinty->addSticker($order, 'sticker.jpg');

// view the order, make sure it's all there
$order_details = $pwinty->getOrder($order);
print_r($order_details);

// submit the order
$pwinty->submitOrder($order);
?>

