<?php

 $site="else";

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';
loggedInCheck();
include 'pageTop.php';



$data = portGetAllStockTransactions($TODAY, $userID);

echo '   <table width="100%">';
echo '     <tr>';
echo '       <th style="text-align:left;">Papper</th>';
echo '       <th>Pris</th>';
echo '       <th>Antal</th>';
echo '       <th>Courtage</th>';
echo '       <th>Konto</th>';
echo '       <th>Summa</th>';
echo '       <th>Datum</th>';
echo '       <th>Händelse</th>';
echo '     </tr>';
foreach ($data as $row) : 
	$stockInfo = stockInfo($row['stockID']); 
	echo '     <tr>';
	echo '       <td style="text-align:left;">'.$stockInfo['shortName'].'</td>';
	echo '       <td>'.$row['price'].'</td>';
	echo '       <td>'.$row['quantity'].'</td>';
	echo '       <td>'.$row['courtage'].'</td>';
	echo '       <td>'.$row['account'].'</td>';
	echo '       <td>'.sysNumber_readable(($row['courtage'] + $row['quantity'] * $row['price']), 0, ',', ' ', true).'</td>';
	echo '       <td>'.$row['date'].'</td>';
	echo '       <td>'.$row['action'].'</td>';
	echo '     </tr>';
endforeach; 
echo '   </table>';

include 'pageBottom.php';
