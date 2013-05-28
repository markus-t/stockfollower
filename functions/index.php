<?php

### Resolve name
function indexResName($isin) {
	$query="SELECT * FROM indexname 
			WHERE ISIN = '$isin'";
	$result=mysql_query($query) or die(mysql_error());;
	$var = mysql_fetch_row($result);
	if(!empty($var[0]))
	$output['name'] = $var[1];
	return $output['name'];
}

function indexGetRange($lowDate, $highDate, $indexID, $startValue, $weight) {
	$query="SELECT * FROM `indexprice`
			WHERE ISIN = '$indexID'
			AND date >= '$lowDate'
			AND date <= '$highDate'";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$output[] = $row;
	}
	return $output;
}

function indexGetValue($date, $isin) {
	$query="SELECT * FROM `indexprice`
			WHERE ISIN = '$isin'
			AND date <= '$date'
			ORDER BY date DESC
			LIMIT 1";
	$result=mysql_query($query) or die(mysql_error());;
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	return $row;
}

function indexGetList() {
	$query="SELECT ISIN, name FROM indexname";
	$result=mysql_query($query) or die(mysql_error());;
	$i = '0';

	
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$output[$i]['ISIN'] = $row['ISIN'];
		$output[$i]['name'] = $row['name'];
		$i++;
	}

	return $output;
}



?>