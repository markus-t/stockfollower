<?php
  $site="update";


include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'functions/update.php';
include 'setVar.php';
loggedInCheck();
include 'pageTop.php';


echo "<pre>";


updateBitstamp();
$mysqli->commit();

include 'pageBottom.php';


?>
