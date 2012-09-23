<?php

### Stock class


### Resolve name
function stockResName($stockID) {
	$query="SELECT * FROM stockname 
		WHERE ID = $stockID";
	$result=mysql_query($query) or die(mysql_error());;
	$var = mysql_fetch_row($result);
	if(!empty($var[0])){
		$output['shortName'] = $var[1];
		$output['name'] = $var[2];
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

### Get value of stock at given date
function stockGetValue($stockID, $date) {
	$output = array(
	"ID"     => $stockID,
	"date"   => "0",
	"value"  => "0");
	$query="SELECT date, price,'2' AS priority FROM `stockprice`
			WHERE stockID = '$stockID'
			AND   date <= '$date'
	UNION SELECT date, price,'1' AS priority FROM `stockbought`
			WHERE stockID = '$stockID'
			AND   date <= '$date'
			ORDER BY date DESC, priority LIMIT 1";
	$result=mysql_query($query) or die(mysql_error());;
	$market = mysql_fetch_row($result);
	if(!empty($market[0])) {
		$output['date'] = $market[0];
		$output['value'] = $market[1];
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


?>
