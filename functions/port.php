<?php

### Get a list of every paper between dates.
function portGetStock($dateLow, $dateHigh, $userID) {
	$output = array();
	$query="SELECT DISTINCT stockID FROM stocktransactions 
			WHERE date <= '$dateHigh'
			AND userId = '$userID'
			GROUP BY stockID";
	$result=mysql_query($query) or die(mysql_error());;
	while($row = mysql_fetch_array($result)) {
		$query="SELECT SUM(quantity) AS sum FROM stocktransactions 
				WHERE userID = $userID
				AND date <= '$dateLow'
				AND stockID = $row[stockID]
				GROUP BY action
				HAVING action = 'bought'";
		$resultBought=mysql_query($query) or die(mysql_error());;

		$query="SELECT SUM(quantity) AS sum FROM stocktransactions 
				WHERE userID = $userID
				AND date <= '$dateLow'
				AND stockID = $row[stockID]
				GROUP BY action
				HAVING action = 'sold'";
		$resultSold=mysql_query($query) or die(mysql_error());;

		$query="SELECT SUM(quantity) AS sum FROM stocktransactions 
				WHERE userID = $userID
				AND date <= '$dateHigh'
				AND date >= '$dateLow'
				AND stockID = $row[stockID]
				GROUP BY action
				HAVING action = 'bought'";
		$totalt=mysql_query($query) or die(mysql_error());;
		
		$total   = mysql_fetch_row($totalt);
		$sold   = mysql_fetch_row($resultSold);
		$bought = mysql_fetch_row($resultBought);

		if(empty($total['0'])) $total['0'] = '0';
		if(empty($bought['0'])) $bought['0'] = '0';
		if(empty($sold['0'])) $sold['0'] = '0';
		
		if(($bought['0'] - $sold['0']) != 0 || $total['0'] != 0)
			$output[] = $row['stockID'];
		
	}
	return $output;
} 

function portGetAllStockTransactions($date, $userID) {
	$query=" SELECT price,
				quantity,
				date,
				courtage,
				stockID,
				action,
				account
			FROM `stocktransactions`
			WHERE   stocktransactions.date      <= '$date'
			AND   userID = '$userID'
			ORDER BY date ASC";
	$result=mysql_query($query) or die(mysql_error());;
	$listStockTransactions = array();
	while($row = mysql_fetch_assoc($result)) 
		$listStockTransactions[] = $row;
	
	return $listStockTransactions;
}

function portGetStockTransactions($arr_stockID, $userID, $highDate = '2020-01-01', $lowDate = '2000-01-01') {
	global $mysqli;
	$queryParams = array();
	$queryTypes = '';
	foreach($arr_stockID as $stockID) {
		if($stockID === reset($arr_stockID))
			$qs = "WHERE (stockID = ? \n";
		else 
			$qs .= "OR stockID = ? \n";
		$queryTypes .="i";
	} 
	$queryTypes .="si";
	
	$queryParams[] = &$queryTypes;
	foreach($arr_stockID as &$stockID)	{
		$queryParams[] = &$stockID;
	}
	$queryParams[] = &$highDate;
	$queryParams[] = &$userID;
	
	$query="SELECT ID, price, quantity, date, courtage, action  
			FROM `stocktransactions`
			$qs )
			AND   date  <= ?
			AND   userID = ?
			ORDER by date";

	$stmt = $mysqli->prepare($query);

	call_user_func_array(array($stmt,'bind_param'),$queryParams);
	$stmt->execute();
	$stmt->bind_result($ID, $price, $quantity, $date, $courtage, $action);
	
	$listStockTransactions = array();
	
	while( $stmt->fetch() ) {
		$listStockTransactions[] = array('ID' => $ID,
										'price' => $price,
										'quantity' => $quantity,
										'date' => $date,
										'courtage' => $courtage,
										'action' => $action,
										);
	}
	return $listStockTransactions;
}

### Get quantity, market value, what is rea and how much, reference SKV 332 edition 13.
function portGetQuantity($stockID, $date, $userID) {
	$output = array(
	"ID"         => $stockID,
	"aquantity"  => "0",
	"dquantity"  => "0",
	"quantity"   => "0",
	"aprice"     => "0",
	"dprice"     => "0",
	"price"      => "0",
	"rea"        => "0"); #Realisation

	$listStockTransactions = portGetStockTransactions(array($stockID), $userID, $date);

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
	$query="SELECT date FROM stocktransactions
			WHERE stockID = $stockID
			ORDER BY date ASC LIMIT 1;";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_row($result);
	return $row[0];
}

### Simulate index graph based on array of transactions
function portSimIndex($transactions, $indexID, $fromTime = '2000-01-01') {
	global $ENDDATE;
	$output  		= array();
	$a       		= '0';
	$toTime      	= $ENDDATE;
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

			if($row['action'] == 'bought' && $fromTime == $row['date']) { 
				@$quantity      += ($row['quantity'] * $row['price']) / $indexPrice['price'];
				$acquireValue += $row['quantity']  * $row['price'];
			} else if($row['action'] == 'sold' && $fromTime == $row['date']){
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
function portGetStockSummary($lowDate, $highDate, $stockID, $userID) {
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
		"date"        => "",
		"time"        => "",
		"close"       => "",
		"type"		  => "",
	);

	$valueHigh = stockGetValue($stockID, $highDate);
	$valueLow  = stockGetValue($stockID, $lowDate);
	$output['date']   = $valueHigh['date'];
	$output['time']   = $valueHigh['time'];
	$output['close']  = $valueHigh['close'];
	$output['ltrade'] = $valueHigh['value'];
	
	$name = stockInfo($stockID);
	$output['type'] = $name['type'];
	
	$output['shortName'] = $name['shortName'];
	$output['name']      = $name['name'];

	$quantityHigh = portGetQuantity($stockID, $highDate, $userID);
	$output['q'] = $quantityHigh['aquantity'] - $quantityHigh['dquantity'];
	$output['aprice'] = $quantityHigh['aprice'];
	
	$quantityLow = portGetQuantity($stockID, $lowDate, $userID);
	$output['tq'] = $quantityLow['aquantity'] - $quantityLow['dquantity'];	

	$output['mvalue'] = $valueHigh['value'] * $output['q'];
	$output['diravkkr'] =  portCacheGetDividendSum($lowDate, $highDate, $stockID, $userID);
	
	$sumLow  = portCacheGetHoldingSum($lowDate, $stockID, $userID);
	$sumHigh = portCacheGetHoldingSum($highDate, $stockID, $userID);

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
function portGetStockActivityOld($lowDate, $highDate, $stockID, $userID) {
	$query="SELECT date,price,quantity,action,courtage,ID,account FROM `stocktransactions`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		AND   userID  = ' $userID'
		UNION SELECT date,dividend AS price,'0','dividend' AS action,'-' AS dividend,'0' AS ID, '-' AS account FROM `stockdividend`
		WHERE stockID = '$stockID'
		AND   date   >= '$lowDate'
		AND   date   <= '$highDate'
		ORDER BY date";
	$output = array();
	$result=mysql_query($query) or die(mysql_error());;

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $output[] = $row;
	}
	return $output;
}

### Get actions att given date 
function portGetStockAction($stockID, $date) {
	$query="SELECT * FROM `stocktransactions`
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
	global $mysqli;
	global $daysInBankYear;
	global $ENDDATE;
	global $USERS;
	###Prepare replace into query
	$stmt = $mysqli->prepare("REPLACE INTO cdividendsum VALUES ( ?, ?, ?, ? )");
	foreach($USERS as $userID){
	    $output = portGetStock('2011-05-01', $ENDDATE, $userID);
		foreach($output as $stockID) {
			$dividend = stockGetDividendRange('2010-01-01', $ENDDATE, $stockID);
			if(!empty($dividend)) {
				##den det här ska bytas ut mot dom nya type definitionera. Dividend type 2 calculations.
				$stockInfo = stockInfo($stockID);
				if( $stockInfo['type'] <= 2) {
					foreach($dividend as $key){
						$tmValue = 0;
						$qAtDividend = portGetQuantity($stockID, $key['date'], $userID);
						$tmValue = $key['dividend'] * $qAtDividend['quantity'] ;
					
						$stmt->bind_param('isis', $stockID, $key['date'], $userID, $tmValue);
						$stmt->execute();	
													
					} 
					### Dividend type 1 calculations
				} else {
					$fromTime    = $dividend['0']['date'];
					$toTime      = $ENDDATE;
					$dividendKey = 0;
					$dividendSum = 0;
					while ($fromTime < $toTime) {
						###Check for new interest rate
						if($dividend[$dividendKey]['date'] == $fromTime) {
							$interest = $dividend[$dividendKey]['dividend'];
						}
						$qAtDividend  = portGetQuantity($stockID, $fromTime, $userID);
						
						$dividendSum = (($interest / 100) / $daysInBankYear) * $qAtDividend['quantity'] ;

						$stmt->bind_param('isis', $stockID, $fromTime, $userID, $dividendSum);
						$stmt->execute();	
						
						$fromTime = strtotime ( '+1 day' , strtotime ($fromTime) );
						$fromTime = date ( 'Y-m-d' , $fromTime );  

					}			
				}
			}
		}
	}
	return 1;
}

function portCacheGetDividendSum($lowDate, $highDate, $stockID, $userID) {
	$query="SELECT IFNULL(SUM(sum),0) as sum FROM `cdividendsum`
		WHERE ID = '$stockID'
		AND date <= '$highDate'
		AND date > '$lowDate'
		AND userID = '$userID'";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$output =  $row['sum'];

	return $output;
}

function portCacheHoldingSum($fromTime2 = '2013-05-21', $userID = 0, $stockID = 0) {
	global $ENDDATE;
	global $mysqli;
	global $USERS;

	$stmt = $mysqli->prepare("REPLACE INTO choldingsum VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ");
	
	$us = ($userID == 0) ? $USERS : $userID;
	
	foreach($us as $userID){
		$fromTime = $fromTime2;
		$span = ($stockID == 0) ? portGetStock('2000-01-01', $ENDDATE, $userID) :  $stockID;
		$i        = 0;
		$toTime   = $ENDDATE;
		foreach($span as $key) 
		while($fromTime <= $toTime) {
			$totAm = '0';
			foreach($span as $key) {
				$rea = '0';
				$quantity = portGetQuantity($key, $fromTime, $userID);
				$stockGetValue = stockGetValue($key, $fromTime);
				if($stockGetValue == 1)
					$Value['value'] = '0';
				else 
					$Value = $stockGetValue;
				
				$output['q'] = $quantity['quantity'];
				$output['aprice'] = $quantity['aprice'];
				$output['mValue'] = $Value['value'];
				$output['tmValue'] = $Value['value'] * $quantity['quantity'];
				$output['taValue'] = $quantity['aprice'] * $quantity['quantity'];

				$output['utv'] = $output['tmValue'] - $output['taValue']; 
				
				$output['diravk']   =  portCacheGetDividendSum('2010-01-01', $fromTime, $key, $userID);
				$output['tmValue'] +=  $output['diravk'];

				$output['date'] = $fromTime;
				
				$stmt->bind_param('isiddddddd', $key, 
												$fromTime,
												$userID,
												$output['aprice'], 
												$output['mValue'], 
												$output['tmValue'], 
												$output['taValue'], 
												$output['utv'],
												$quantity['rea'],
												$output['diravk']);
				$stmt->execute();

				$totAm += $output['utv'];
			}
			$var[$i]['utv'] = $totAm;
			$var[$i]['date'] = $fromTime;
			$i++;
			$fromTime = strtotime ( '+1 day' , strtotime ($fromTime) );
			$fromTime = date ( 'Y-m-d' , $fromTime ); 
		}
	}
	return 1;
}


function portCacheGetHoldingSum($date, $stockID, $userID) {
	$query="SELECT * FROM choldingsum
			WHERE date    = '$date'
			AND   stockID = '$stockID'
			AND   userID  = '$userID'";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC); 
	### I det fall row är tom bör en array med värdet datum, stockid, userid samt nollvärde på alla övriga parametrar returneras
	$output = $row;
	return $output;
}

function portTransactionAdd($stockID, $date, $quantity, $price, $courtage, $userID, $action) {
  global $mysqli;
  $stmt = $mysqli->prepare("INSERT INTO stocktransactions 
                          (userID, date, stockID, quantity, price, courtage, action)
			               VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('isissss', $userID, $date, $stockID, $quantity, $price, $courtage, $action);
  $stmt->execute();
  return $stmt->insert_id;
}

function portTransInformation($id) {
	$query="SELECT * FROM stocktransactions
	        WHERE ID = $id";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC); 
	$output = $row;
	return $output;
}

function deleteTransaction($id) {
  global $mysqli;
  $stmt = $mysqli->prepare("DELETE FROM stocktransactions
							WHERE ID = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  return $stmt->affected_rows;
}


?>