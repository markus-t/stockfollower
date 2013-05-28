<?php

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';

if(strtotime($FROM) <= strtotime('-4 year', strtotime($TO))) {
	$av  = '+4 day';
	$ig  = '+16 day';
} else if(strtotime($FROM) <= strtotime('-2 year', strtotime($TO))) {
	$av  = '+2 day';
	$ig  = '+10 days';
} else if(strtotime($FROM) <= strtotime('-1 year', strtotime($TO))) {
	$av  = '+2 day';
	$ig  = '+6 days';
} else if(strtotime($FROM) <= strtotime('-1 week', strtotime($TO))) {
	$av  = '+1 day';
	$ig  = '+2 day';
} else {
	$av  = '+1 day';
	$ig  = '+1 day';
}

if($_REQUEST['chart'] == 'area') {
	$output = array();
	 
	### Datecolumn
		  $output['cols'][] = array(
								'id'    => '',
								'label' => 'year',
								'type'  => 'string');
	
	foreach ($stockPapers as $key => $stockID) {
		$name = stockInfo($stockID);
		$output['cols'][] = array(
					'id'    => '',
					'label' => $name['shortName'],
					'type'  => 'number');
	}  

	$fromTime = $FROM;
	$i = 0;
	while (strtotime($fromTime) <= strtotime($TODAY)) {
		$output['rows'][$i]['c'][] = array( "v" => $fromTime);	
		$countInvesteratOverTime = count($stockPapers);
		foreach ($stockPapers as $key => $stockID) {
		  $portGetQuantity = portGetQuantity($stockID, $fromTime, $userID);
		  $price = stockGetValue($stockID, $fromTime);
		  $marketValue = $portGetQuantity['quantity'] * $price['value'];
		  $output['rows'][$i]['c'][] = array( "v" => round($marketValue));
		}
	  $fromTime = date('Y-m-d',strtotime ( $ig , strtotime ($fromTime) ) );
      $i++;
	}

	echo json_encode($output);
} else if($_REQUEST['chart'] == 'line') {
	$output = array();
	$output['cols'][] = array('id'  => '',
							'label' => 'year',
							'type'  => 'string');
							
	$output['cols'][] = array('id'  => '',
							'label' => 'Portfölj',
							'type'  => 'number');

	
	$fT = $FROM;
	$tT = $TO;
	$aAm = "0";
	$mAm = "0";
	$i = '0';
	$var = array();

	$temp_var['utv'] = '0';
	foreach($stockPapers as $key) {
		$valueLow[$key] = portCacheGetHoldingSum($FROM, $key, $userID);
		$temp_var['utv'] -= $valueLow[$key]['utv'] + $valueLow[$key]['diravk'] + $valueLow[$key]['rea'];
	}

	$orStockList = '';
	$firstRun = true;	
	foreach($stockPapers as $key) {
		if($firstRun) 
		  $orStockList .= "stockID = $key ";
		else
		  $orStockList .= "OR stockID = $key ";
		$firstRun = false;
	}
	while($fT <= $tT) {
		$totAm = '0';
		$diravkkr = '0';

		$query="SELECT SUM(utv) AS utv, SUM(diravk) AS diravk, SUM(rea) AS rea FROM choldingsum
				WHERE date    = '$fT'
				AND userID = '$userID'
				AND ( $orStockList )";
		$result=mysql_query($query) or die(mysql_error());;
		$row = mysql_fetch_array($result, MYSQL_ASSOC); 

		$totAm += $row['utv'] + $row['diravk'] + $row['rea'] + $temp_var['utv'];
		$var[$i]['utv'] = $totAm;
		$var[$i]['date'] = $fT;
		$i++;
		$fT = strtotime ( $av , strtotime ($fT) );
		$fT = date ( 'Y-m-d' , $fT ); 
	}

		  if($compareToIndex) {
			$arr = portGetStockTransactions($stockPapers, $userID);
			if($FROM < $arr['0']['date']) {
				$dateLowest = $FROM;
			} else {
				$dateLowest = $arr['0']['date'];
			}
			
			
			$indexData = portSimIndex($arr, $indexISIN, $dateLowest);

			
			$nameISIN =  indexResName($indexISIN);
					$output['cols'][] = array('id'  => '',
								'label' => $nameISIN,
								'type'  => 'number');
		  }

		  $chartData = '';
		  $first_run = 1;
		  
	if($compareToIndex) {
		foreach($var as $key) {
			if($first_run == 1) {
				$first_run = 0;
				$norm = $indexData[$key['date']]['utv'];
			} 
			$utv = $indexData[$key['date']]['utv'] - $norm;
			$output['rows'][]['c'] = array(
							array(	'v' => date('y-m-d', strtotime($key['date']))),
							array(	'v' => round($key['utv'])), 
							array(	'v'  => round($utv)));
		}
	} else {
		foreach($var as $key) {
			$output['rows'][]['c'] = array(
									array(	'v' => date('y-m-d', strtotime($key['date']))), 
									array(	'v'  => round($key['utv'])));
		}
	}
	echo json_encode($output);
}
?>