<?php

### Stock class


### Resolve name
function stockInfo($stockID) {
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT * FROM stockname WHERE ID = ?");
	$stmt->bind_param('i', $stockID);
	$stmt->execute();
	$stmt->bind_result($var[0], $var[1], $var[2], $var[3], $var[4], $var[5], $var[6], $var[7], $var[8]);
	$stmt->fetch();
	if(!empty($var[1])){
		$output['shortName']   = $var[1];
		$output['name']        = $var[2];
		$output['type']        = $var[3];
		$output['rss']         = $var[4];
		$output['avanza']      = $var[5];
		$output['nordnet']     = $var[6];
		$output['nordnetcurrent']     = $var[7];
		$output['morningstar'] = $var[8];
		return $output;
	} else {
		return 1;
	}
}

### Get stock list
function stockGetList() {
	$query="SELECT * FROM stockname
	        ORDER BY shortName";
	$result=mysql_query($query) or die(mysql_error());;
	$i = 0;
	while ($line = mysql_fetch_array($result, MYSQL_NUM)) {
		$output[$i]['stockID']   = $line[0];
		$output[$i]['shortName'] = $line[1];
		$output[$i]['name']      = $line[2];
		$output[$i]['type']      = $line[3];
		$output[$i]['rss']       = $line[4];
		$i++;
	}
	return $output;
}

### Resovle type
function stockGetType($stockID) {
	$query="
	SELECT type from stockname 
	WHERE ID = '$stockID' ;";
	$r=mysql_query($query) or die(mysql_error());;
	$output = mysql_fetch_row($r);
	return $output['0'];
}

### Get value of stock at given date ### FIXA TILL NYA ANNARS FUNKAR DET INTE
function stockGetValue($stockID, $date) {
	global $mysqli;
	$output = array(
	"ID"     => $stockID,
	"date"   => "0",
	"value"  => "0",
	"time"  => "",
	"close"  => "");
	
	$stmt = $mysqli->prepare("
	(SELECT date, price,'1' AS priority, DATE_FORMAT(time, '%H:%i'), close FROM `stockprice`
			WHERE stockID = ?
			AND   date <= ?
			ORDER BY date DESC LIMIT 1)
	UNION (SELECT date, price,'2' AS priority, '00:00' as time, '0' as close FROM `stocktransactions`
			WHERE stockID = ?
			AND   date <= ?
			ORDER BY date DESC LIMIT 1)
			ORDER BY date DESC, priority LIMIT 1");
	$stmt->bind_param('isis', $stockID, $date, $stockID, $date);
	$stmt->execute();
	$stmt->bind_result($market[0], $market[1], $market[2], $market[3], $market[4]);
	$stmt->fetch();

	if(!empty($market[0])) {
		$output['date']  = $market[0];
		$output['value'] = $market[1];
		$output['time']  = $market[3];
		$output['close'] = $market[4];
		return $output;
	} else {
		return 1;
	}
}

### Get a range of dividends in given range. 
function stockGetDividendRange($lowDate, $highDate, $stockID) {
	$query="SELECT dividend, date, stockID FROM `stockdividend`
	WHERE stockID = '$stockID'
	AND date >= '$lowDate'
	AND date <= '$highDate'";
	$output = '';
	$result=mysql_query($query) or die(mysql_error());;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
		$output[] = $row;
	return($output);
}

function stockUpdatePrice($stockID, $date, $price, $close = 1, $time = '00:00:00') {
	global $mysqli;
	$stmt = $mysqli->prepare("REPLACE INTO stockprice (date, price, stockID, time, close)
								VALUES (?, ?, ?, ?, ?)");
	$stmt->bind_param('ssisi', $date, $price, $stockID, $time, $close);
	$stmt->execute();
	return true;
}

function stockGetValueList($stockID) {
	global $mysqli;
	$stmt = $mysqli->prepare("SELECT date, price, stockID FROM stockprice WHERE stockID = ?");
	$stmt->bind_param('i', $stockID);
	$stmt->execute();
    $stmt->bind_result($date, $price, $stockID);
	$output = array();
	while ($stmt->fetch()) 
        $output[] = array('date' => $date, 'price' => $price, 'stockID' => $stockID);
    
	return $output;
}

function stockGetUpdateList($server)  {
	  $query = "SELECT ID, $server AS link FROM stockname WHERE $server !='' ";
	  $result=mysql_query($query) or die(mysql_error());;
	  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
		 $output[$row['ID']] = $row['link'];
	  return $output;
}

function stockAddDividend($stockID, $date, $amount) {
	global $mysqli;
	echo $stockID;
	echo $date;
	echo $amount;
	$stmt = $mysqli->prepare("REPLACE INTO stockdividend (date, stockID, dividend)
								VALUES (?, ?, ?)");
	$stmt->bind_param('sss', $date, $stockID, $amount);
	if ($stmt->execute()) { 
		return true;
	} else {
		return false;
	}
}
?>
