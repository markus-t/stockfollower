<?php

### Get a list of every paper between dates.
function portGetStock($dateLow, $dateHigh, $user = '1') {
	$output = array();
	$query="SELECT stockID,SUM(quantity) AS totalBought FROM stockbought 
			WHERE date <= '$dateHigh'
			GROUP BY stockID;";
	$result=mysql_query($query) or die(mysql_error());;
	while($row = mysql_fetch_array($result)) {
		$query="SELECT stockID,SUM(quantity) AS totalBought FROM stockbought 
				WHERE date <= '$dateLow'
				AND stockID = '$row[stockID]';";
		$resultBought=mysql_query($query) or die(mysql_error());;

		$query="SELECT stockID,SUM(quantity) AS totalSold FROM stocksold 
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
	$query=" SELECT stockbought.price,
				stockbought.quantity AS quantity,
				date as date,
				courtage,
				'bought' AS action
			FROM `stockbought`
			WHERE stockbought.stockID    = $stockID
			AND   stockbought.date      <= '$date'
			UNION ALL SELECT stocksold.price AS price,
				stocksold.quantity AS quantity,
				date AS date,
				courtage,
				'sold' AS action
			FROM `stocksold`
			WHERE stocksold.stockID    = $stockID
			AND   stocksold.date      <= '$date'";
	$result=mysql_query($query) or die(mysql_error());;
	$listStockTransactions = array();
	while($row = mysql_fetch_assoc($result)) 
	$listStockTransactions[] = $row;
	
	#If we have more than one element we need to make them in order.
	if(count($listStockTransactions) > 1) {
		foreach($listStockTransactions as $key => $row)
		$dates[$key]  = $row['date']; 	
		array_multisort($dates, SORT_ASC, $listStockTransactions);
	}
	
	return $listStockTransactions;
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

	$listStockTransactions = portGetStockTransactions($stockID, $date);

	if($listStockTransactions) {
		$current   = array(
		'quantity' => '0', #Antal
		'aprice'   => '0', #Totalt omkostnadsbelopp (Anskaffninspris)
		'staprice' => '0', #Omkostnadsbelopp per antal
		'dprice'   => '0', #Försäljningspris 
		);
		
		foreach($listStockTransactions as $arr) {
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
	$output = array('sum' => "0");
	foreach($dividend as $row) {
		###fel funktion
		$temp = portGetQuantity($row['stockID'], $row['date']);
		$output['sum'] += $temp['aquantity'] * $row['price'];
	}
	return($output);
}

function portGetStockSpan($stockID, $user) {
	$query="SELECT date FROM stockbought
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
	$query="SELECT ID,date,quantity,price,'bought' AS info FROM stockbought 
							$qs 
							AND date > '$lowDate'
							AND date < '$highDate'
			UNION ALL SELECT ID,date,quantity,price,'sold' FROM stocksold 
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
	$output  		= array();
	$a       		= '0';
	$fromTime      	= '2000-01-01';
	$toTime      	= '2013-01-01';
	$marketValue  	= '0';
	$acquireValue  	= '0';
	$quantity       = '0';
	$left    = true;
	#Go trough every day
	while($fromTime <= $toTime) {
		$indexPrice = indexGetValue($fromTime, $indexID);
		#For every day go trough each transaction that happens that day

		while($left == true && $transactions[$a]['date'] == $fromTime){
			$row = $transactions[$a];

			if($row['info'] == 'bought' && $fromTime == $row['date']) { 
				@$quantity      += ($row['quantity'] * $row['price']) / $indexPrice['price'];
				$acquireValue += $row['quantity']  * $row['price'];
			} else if($row['info'] == 'sold' && $fromTime == $row['date']){
				$quantity      -= ($row['quantity'] * $row['price']) / $indexPrice['price'];
				$acquireValue -= $row['quantity']  * $row['price'];
			}
			#Next transaction
			if($fromTime == $row['date'] ) $a++;
			#Next transaction does not exist?
			if(count($transactions) == $a) $left = false; 
		}
		#Calculate values to return
		$marketValue = $quantity * $indexPrice['price'];
		$acquireValue = $acquireValue;

		#Return values
		$output[$fromTime] = array( 
		"mValue" => $marketValue,
		"aValue" => $acquireValue,
		"utv"    => round($marketValue - $acquireValue)
		);

		#Set next day
		$fromTime = strtotime ( '+1 day' , strtotime ($fromTime) );
		$fromTime = date ( 'Y-m-d' , $fromTime );  
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

	$quantityHigh = portGetQuantity($stockID, $highDate);
	$output['q'] = $quantityHigh['aquantity'] - $quantityHigh['dquantity'];
	$output['aprice'] = $quantityHigh['aprice'];
	
	$quantityLow = portGetQuantity($stockID, $lowDate);
	$output['tq'] = $quantityLow['aquantity'] - $quantityLow['dquantity'];	

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
	$query="SELECT date,price,quantity,'bought' FROM `stockbought`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		UNION SELECT date,price,quantity,'sold' FROM `stocksold`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		UNION SELECT date,dividend AS price,'0','dividend' FROM `stockdividend`
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
	$query="SELECT * FROM `stockbought`
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
	mysql_query("BEGIN");
	global $daysInBankYear;
	$output = portGetStock('2011-05-01', '2013-01-01', "1");
	foreach($output as $stockID) {
		$dividend = stockGetDividendRange('2010-01-01', '2013-01-01', $stockID);
		if(!empty($dividend)) {
			##den det här ska bytas ut mot dom nya type definitionera. Dividend type 2 calculations.
			if( $stockID <= 999) {
				foreach($dividend as $key){
					$tmValue = 0;
					$qAtDividend = portGetQuantity($stockID, $key['date']);
					$tmValue = $key['dividend'] * $qAtDividend['quantity'] ;
					$query="REPLACE INTO cdividendsum 
							VALUES ( '$stockID', '$key[date]', '$tmValue' )";
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
					$query="REPLACE INTO cdividendsum 
									VALUES ( '$stockID', '$fromTime', '$dividendSum' )";
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
	$query="SELECT SUM(sum) as sum FROM `cdividendsum`
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
	$aAm      = "0";
	$mAm      = "0";
	$i        = '0';
	$fromTime = '2011-05-01';
	$toTime   = '2013-01-01';
	$av       = '+1 day';

	foreach($span as $key) 
	while($fromTime <= $toTime) {
		$totAm = '0';
		foreach($span as $key) {
			$rea = '0';
			$quantity = portGetQuantity($key, $fromTime);
			$Value    = stockGetValue($key, $fromTime);
			$output['q'] = $quantity['quantity'];
			$output['aprice'] = $quantity['aprice'];
			$output['mValue'] = $Value['value'];
			$output['tmValue'] = $Value['value'] * $quantity['quantity'];
			$output['taValue'] = $quantity['aprice'] * $quantity['quantity'];

			$output['utv'] = $output['tmValue'] - $output['taValue']; 
			
			$output['diravk']   =  portCacheGetDividendSum('2010-01-01', $fromTime, $key);
			$output['tmValue'] +=  $output['diravk'];

			$output['date'] = $fromTime;

			$query = "REPLACE INTO choldingsum 
						VALUES ('$key', 
								'$fromTime',
								'$output[aprice]', 
								'$output[mValue]', 
								'$output[tmValue]', 
								'$output[taValue]', 
								'$output[utv]',
								'$quantity[rea]',
								'$output[diravk]')";
			$result=mysql_query($query) or die(mysql_error());;
			$totAm += $output['utv'];
		}
		$var[$i]['utv'] = $totAm;
		$var[$i]['date'] = $fromTime;
		$i++;
		$fromTime = strtotime ( $av , strtotime ($fromTime) );
		$fromTime = date ( 'Y-m-d' , $fromTime ); 
	}
	return 1;
}

function portCacheGetHoldingSum($date, $stockID) {
	$query="SELECT * FROM choldingsum
			WHERE date    = '$date'
			AND   stockID = '$stockID'";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC); 
	$output = $row;
	return $output;
}

function portCreateTempTableHoldingSum() {
	$query="CREATE TEMPORARY TABLE choldingsum
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
	$query="CREATE TEMPORARY TABLE cdividendsum
	(
		ID     int,
		date   date,
		sum    dec(10,5)
		)"; 
	$result=mysql_query($query) or die(mysql_error());;
}  


?>