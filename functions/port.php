<?php

### Get a list of every paper between dates.
function portGetStock($dateLow, $dateHigh, $user = '1') {
	$output = array();
	$query="
SELECT stockID,SUM(quantity) AS totalBought FROM stockBought 
WHERE date <= '$dateHigh'
GROUP BY stockID;";
	$result=mysql_query($query) or die(mysql_error());;
	while($row = mysql_fetch_array($result)) {
		$query="
	SELECT stockID,SUM(quantity) AS totalBought FROM stockBought 
	WHERE date <= '$dateLow'
	AND stockID = '$row[stockID]';";
		$resultBought=mysql_query($query) or die(mysql_error());;

		$query="
	SELECT stockID,SUM(quantity) AS totalSold FROM stockSold 
	WHERE date <= '$dateLow'
	AND stockID = '$row[stockID]';";
		$resultSold=mysql_query($query) or die(mysql_error());;

		$sold   = mysql_fetch_row($resultSold);
		$bought = mysql_fetch_row($resultBought);

		if(empty($bought['1']))
		$bought['1'] = '0';
		
		if(empty($sold['1'])) 
		$sold['1'] = '0';
		
		if(!($bought['1'] - $sold['1'] == '0' && $row['totalBought'] - $sold['1'] == '0' && !empty($row['totalBought']) )) 
		$output[] = $row['stockID'];
		
	}
	return $output;
} 

function portGetStockTransactions($stockID, $date) {
	$query=" SELECT stockBought.price,
				stockBought.quantity AS quantity,
				date as date,
				courtage,
				'bought' AS action
			FROM `stockBought`
			WHERE stockBought.stockID    = $stockID
			AND   stockBought.date      <= '$date'
			UNION ALL SELECT stockSold.price AS price,
				stockSold.quantity AS quantity,
				date AS date,
				courtage,
				'sold' AS action
			FROM `stockSold`
			WHERE stockSold.stockID    = $stockID
			AND   stockSold.date      <= '$date'";
	$result=mysql_query($query) or die(mysql_error());;
	$listBoughtSold = array();
	while($row = mysql_fetch_assoc($result)) 
	$listBoughtSold[] = $row;
	
	#If we have more than one element we need to make them in order.
	if(count($listBoughtSold) > 1) {
		foreach($listBoughtSold as $key => $row)
		$dates[$key]  = $row['date']; 	
		array_multisort($dates, SORT_ASC, $listBoughtSold);
	}
	
	return $listBoughtSold;
}

### Get quantity, market value, what is rea and how much, reference SKV 332 edition 13.
function portGetQuantity($stockID, $date) {
	$output = array(
	"ID"         => $stockID,
	"aquantity"  => "0",
	"dquantity"  => "0",
	"quantity"   => "0",
	"aprice"     => "0",
	"dprice"     => "0",
	"price"      => "0",
	"rea"        => "0"); #Realisation

	$listBoughtSold = portGetStockTransactions($stockID, $date);

	if($listBoughtSold) {
		$current   = array(
		'quantity' => '0', #Antal
		'aprice'   => '0', #Totalt omkostnadsbelopp (Anskaffninspris)
		'staprice' => '0', #Omkostnadsbelopp per antal
		'dprice'   => '0', #Försäljningspris 
		);
		
		foreach($listBoughtSold as $arr) {
			if($arr['action'] == 'bought') {
				$output['aquantity'] += $arr['quantity'];
				$current['quantity'] += $arr['quantity'];
				$current['aprice']   += ($arr['quantity']   * $arr['price'])      + $arr['courtage'];
				$current['staprice']  = ($current['aprice'] - $current['dprice']) / $current['quantity'];
			} else { # sold
				$output['dquantity'] += $arr['quantity'];
				$current['quantity'] -= $arr['quantity'];
				$current['dprice']   += ($arr['quantity']   * $arr['price'])      - $arr['courtage'];
				$output['rea']	    += ($arr['quantity']   * $arr['price'])      - ($current['staprice'] * $arr['quantity']);
			}

			if($current['quantity'] == '0'){
				$current['aprice'] = '0';
				$current['dprice'] = '0';
			}
		}
		$output['quantity'] = $current['quantity'];
		
		if($current['aprice'] && $current['quantity'])
		$output['aprice']   = $current['staprice'];
		
		$output['dprice']   = $current['dprice'];
	}
	return $output;
}

### Get dividend total amount
function portGetDividend($lowDate, $highDate, $dividend) {
	$output = array(
	'sum' => "0");
	foreach($dividend as $row) {
		###fel funktion
		$temp = portGetQuantity($row['stockID'], $row['date']);
		$output['sum'] += $temp['aquantity'] * $row['price'];
	}
	return($output);
}

function portGetStockSpan($stockID, $user) {
	$query="SELECT date FROM stockBought
			WHERE stockID = $stockID
			ORDER BY date ASC LIMIT 1;";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_row($result);
	$output[0] = $row[0];
	return $output;
}

### Get transactionlist for given stock in given range.
function portGetStockTransactionsOld($arr_stockID, $lowDate = '2000-01-01',$highDate = '2020-01-01'){
	$output = array();
	$fr = true;
	$qs = '';
	foreach($arr_stockID as $stockID) {
		if($fr){
			$qs .= "WHERE stockID= '" . $stockID . "'\n";
			$fr = false;
		} else {
			$qs .= "OR stockID = '" . $stockID . "'\n";
		}
	} 
	$query="SELECT ID,date,quantity,price,'bought' AS info FROM stockBought 
							$qs 
							AND date > '$lowDate'
							AND date < '$highDate'
			UNION ALL SELECT ID,date,quantity,price,'sold' FROM stockSold 
							$qs
							AND date > '$lowDate'
							AND date < '$highDate'
							ORDER BY date ASC";
	$result=mysql_query($query) or die(mysql_error());;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
	$output[] = $row;

	return $output;
}

### Simulate index graph based on array of transactions
function portSimIndex($transactions, $indexID) {
	$output  = array();
	$ID      = '1';
	$a       = '0';
	$fT      = '2000-01-01';
	$tT      = '2013-01-01';
	$mValue  = '0';
	$aValue  = '0';
	$q       = '0';
	$left    = true;
	#Go trough every day
	while($fT <= $tT) {
		$indexPrice = indexGetValue($fT, $indexID);
		#For every day go trough each transaction that happens that day

		while($left == true && $transactions[$a]['date'] == $fT){
			$row = $transactions[$a];

			if($row['info'] == 'bought' && $fT == $row['date']) { 
				@$q      += ($row['quantity'] * $row['price']) / $indexPrice['price'];
				$aValue += $row['quantity']  * $row['price'];
			} else if($row['info'] == 'sold' && $fT == $row['date']){
				$q      -= ($row['quantity'] * $row['price']) / $indexPrice['price'];
				$aValue -= $row['quantity']  * $row['price'];
			}
			#Next transaction
			if($fT == $row['date'] ) $a++;
			#Next transaction does not exist?
			if(count($transactions) == $a) $left = false; 
		}
		#Calculate values to return
		$mV32 = $q * $indexPrice['price'];
		$aV32 = $aValue;

		#Return values
		$output[$fT] = array( 
		"mValue" => $mV32,
		"aValue" => $aV32,
		"utv"    => round($mV32 - $aV32)
		);

		#Set next day
		$fT = strtotime ( '+1 day' , strtotime ($fT) );
		$fT = date ( 'Y-m-d' , $fT );  
	}
	return $output;
}

### Gives summary information of stock between dates from cache table.
function portGetStockSummary($lowDate, $highDate, $stockID) {
	$output = array(
	"id"          => "$stockID",
	"shortName"   => "",
	"name"        => "",
	"q"           => "",
	"ltrade"      => "",
	"aprice"      => "0",
	"tprice"      => "0",
	"mvalue"      => "0",
	"utv"         => "",
	"diravk"      => "0",
	"utvkr"       => "",
	"diravkkr"    => "0",
	"rea"         => "0",
	"utvreamedel" => "0",
	"date"        => ""
	);

	$valueHigh = stockGetValue($stockID, $highDate);
	$valueLow  = stockGetValue($stockID, $lowDate);
	$output['date']   = $valueHigh['date'];
	$output['ltrade'] = $valueHigh['value'];
	
	$name = stockResName($stockID);
	$output['shortName'] = $name['shortName'];
	$output['name']      = $name['name'];

	$QuantityHigh = portGetQuantity($stockID, $highDate);
	$output['q'] = $QuantityHigh['aquantity'] - $QuantityHigh['dquantity'];
	$output['aprice'] = $QuantityHigh['aprice'];
	
	$QuantityLow = portGetQuantity($stockID, $lowDate);
	$output['tq'] = $QuantityLow['aquantity'] - $QuantityLow['dquantity'];	

	$output['mvalue'] = $valueHigh['value'] * $output['q'];
	$output['diravkkr'] =  portCacheGetDividendSum($lowDate, $highDate, $stockID);
	
	$sumLow  = portCacheGetHoldingSum($lowDate, $stockID);
	$sumHigh = portCacheGetHoldingSum($highDate, $stockID);

	$output['rea'] = $sumHigh['rea'] - $sumLow['rea'] ;
	$output['utvkr'] = $sumHigh['utv'] - $sumLow['utv'] ;

	$output['tprice'] = $output['mvalue'] - $output['utvkr'];

	if( '0' <> $output['utvkr'] || '0' <> $output['mvalue'] || $output['utvkr'] != '0')
	@$output['utv'] = (($output['utvkr'] + $output['diravkkr'] +  $output['rea'] )/ $output['mvalue']) * 100;
	else
	$output['utv'] = '0';

	return $output;
}

### Get activty of stock
function portGetStockActivityOld($lowDate, $highDate, $stockID) {
	$query="SELECT date,price,quantity,'bought' FROM `stockBought`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		UNION SELECT date,price,quantity,'sold' FROM `stockSold`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		UNION SELECT date,dividend AS price,'0','dividend' FROM `stockDividend`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		ORDER BY date";
	$output = array();
	$result=mysql_query($query) or die(mysql_error());;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
	$output[] = $row;
	return $output;
}

### Get actions att given date 
function portGetStockAction($stockID, $date) {
	$query="SELECT * FROM `stockBought`
			WHERE stockID = '$stockID'
			AND date = '$date'";
	$output = array();
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	/*
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$output[] = $row;
	}*/
	return $row;
}

function portCacheDividendSum() {
	global $daysInBankYear;
	$output = portGetStock('2011-05-01', '2013-01-01', "1");
	foreach($output as $stockID) {
		$dividend = stockGetDividendRange('2010-01-01', '2013-01-01', $stockID);
		if(!empty($dividend)) {
			##den det här ska bytas ut mot domnya type definitionera. Dividend type 2 calculations.
			if( $stockID <= 999) {
				foreach($dividend as $key){
					$tmValue = 0;
					$qAtDividend = portGetQuantity($stockID, $key['date']);
					$tmValue = $key['dividend'] * $qAtDividend['quantity'] ;
					$query="INSERT INTO cDividendSum 
					VALUES ( '$stockID', '$key[date]', '$tmValue' ) 
					ON DUPLICATE KEY UPDATE 
					sum = '$tmValue'";
					$result=mysql_query($query) or die(mysql_error());;
				} 
				### Dividend type 1 calculations
			} else {
				$fromTime    = $dividend['0']['date'];
				$toTime      = '2013-01-01';
				$dividendKey = 0;
				$dividendSum = 0;
				while ($fromTime < $toTime) {
					###Check for new interest rate
					if($dividend[$dividendKey]['date'] == $fromTime) {
						$interest = $dividend[$dividendKey]['dividend'];
					}
					$qAtDividend  = portGetQuantity($stockID, $fromTime);
					
					$dividendSum = (($interest / 100) / $daysInBankYear) * $qAtDividend['quantity'] ;
					$query="INSERT INTO cDividendSum 
					VALUES ( '$stockID', '$fromTime', '$dividendSum' ) 
					ON DUPLICATE KEY UPDATE 
					sum = '$dividendSum'";
					$result=mysql_query($query) or die(mysql_error());;
					
					$fromTime = strtotime ( '+1 day' , strtotime ($fromTime) );
					$fromTime = date ( 'Y-m-d' , $fromTime );  

				}			
			}
		}
	}
	return 1;
}

function portCacheGetDividendSum($lowDate, $highDate, $stockID) {
	$query="SELECT SUM(sum) as sum FROM `cDividendSum`
		WHERE ID = '$stockID'
		AND date <= '$highDate'
		AND date > '$lowDate'";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$output=  $row['sum'];
	return $output;
}

function portCacheHoldingSum() {
	$span = portGetStock('2000-01-01', '2015-01-01', '1');
	$fT = '2011-05-01';
	$tT = '2013-01-01';
	$aAm = "0";
	$mAm = "0";
	$i = '0';
	$FROM = '2011-05-01';
	$TO = '2013-01-01';
	$av  = '+1 day';

	foreach($span as $key) 
	while($fT <= $tT) {
		$totAm = '0';
		foreach($span as $key) {
			$rea = '0';
			$Quantity = portGetQuantity($key, $fT);
			$Value    = stockGetValue($key, $fT);
			$output['q'] = $Quantity['quantity'];
			$output['aprice'] = $Quantity['aprice'];
			$output['mValue'] = $Value['value'];
			$output['tmValue'] = $Value['value'] * $Quantity['quantity'];
			$output['taValue'] = $Quantity['aprice'] * $Quantity['quantity'];

			$output['utv'] = $output['tmValue'] - $output['taValue']; 
			
			$output['diravk']   =  portCacheGetDividendSum('2010-01-01', $fT, $key);
			$output['tmValue'] +=  $output['diravk'];

			$output['date'] = $fT;

			$query = "INSERT INTO cHoldingSum 
					VALUES ('$key', 
						'$fT',
						'$output[aprice]', 
						'$output[mValue]', 
						'$output[tmValue]', 
						'$output[taValue]', 
						'$output[utv]',
						'$Quantity[rea]',
						'$output[diravk]') 
					ON DUPLICATE KEY UPDATE
						aprice  =   '$output[aprice]',
						mValue  =   '$output[mValue]',
						tmValue =   '$output[tmValue]',
						taValue =   '$output[taValue]',
						utv     =   '$output[utv]',
						rea     =   '$Quantity[rea]',
						diravk  =   '$output[diravk]'";
			$result=mysql_query($query) or die(mysql_error());;
			$totAm += $output['utv'];
		}
		$var[$i]['utv'] = $totAm;
		$var[$i]['date'] = $fT;
		$i++;
		$fT = strtotime ( $av , strtotime ($fT) );
		$fT = date ( 'Y-m-d' , $fT ); 
	}
	return 1;
}

function portCacheGetHoldingSum($date, $stockID) {
	$query="SELECT * FROM cHoldingSum
			WHERE date    = '$date'
			AND   stockID = '$stockID'";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC); 
	$output = $row;
	return $output;
}

function portCreateTempTableHoldingSum() {
	$query="CREATE TEMPORARY TABLE cHoldingSum
	(
		stockID   int,
		date      date,
		aprice    dec(11,5),
		mValue    dec(11,5),
		tmValue   dec(11,5),
		taValue   dec(11,5),
		utv       dec(11,5),
		)"; 
	$result=mysql_query($query) or die(mysql_error());;
	
}

function portCreateTempTableDividendSum() {
	$query="CREATE TEMPORARY TABLE cDividendSum
	(
		ID     int,
		date   date,
		sum    dec(10,5)
		)"; 
	$result=mysql_query($query) or die(mysql_error());;
}  


?>