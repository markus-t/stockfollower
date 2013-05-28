<?php
  $site="update";

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';
loggedInCheck();
include 'pageTop.php';

echo '<table class="sortable contentbox"><tr><th>Datum</th><th>Pris</th></tr>';
$valueList = stockGetValueList($stockID);
foreach($valueList as $value){
	echo '<tr>';
	echo '<td>'.$value['date'].'</td>';
	echo '<td>'.$value['price'].'</td>';
	echo '</tr>';
}

echo '</table>';
include 'pageBottom.php';


?>
