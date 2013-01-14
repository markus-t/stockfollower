<?php
 $site="port";


include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'pageTop.php';




?>
<table width="100%" class="sortable">
<tr class="row">
<th style="text-align: left;">Papper</th>
<th>Antal</th>
<th>Kurs</th>
<th>Ans. Kost.</th>
<th>Ing. Värde</th>
<th>Mark. Värde</th>
<th>Utv kr</th>
<th>Rea</th>
<th>Diravk. kr</th>
<th>Totalt</th>
</tr>	

 
  <?php

  $nodata = '';
  if(is_array($stockList)) {
    foreach ($stockList as $stockID) 
      $each[] = portGetStockSummary($FROM, $TO, $stockID);
    if (!empty($each)) {
	  foreach ($each as $key => $row) {
        $volume[$key]  = $row['mvalue'];
        $edition[$key] = $row['utv'];
      }
    

      $sum = array(
        "mvalue" => "0",
	    "avalue" => "0",
	    "tprice" => "0",
	    "utvkr" => "0",
	    "diravkkr" => "0",
	    "diravk" => "0",
	    "rea" => "0"
      );
      array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $each);
      foreach ($each as $output2) 
        {
          $humanDate = sysHumanDate(date("Y-m-d",  strtotime($output2['date'])));
 
          echo "  <tr>\n";
    	  echo "    <td style=\"text-align: left;\"><a href=\"stockinfo.php?stockID=".$output2['id']."\">".$output2['shortName']."</a></td>\n"; 
	      echo "    <td>".sysNumber_readable($output2['q'], 2, ',', ' ')."</td>\n";
	      echo "    <td><abbr title=\"Updateringsdatum: ".$humanDate."\">".number_format($output2['ltrade'], 2, ',', ' ')."</abbr></td>\n";
	      echo "    <td>".sysNumber_readable($output2['aprice'], 2, ',', ' ')."</td>\n";
          echo "    <td>".sysNumber_readable(round($output2['tprice']), 0, ',', ' ')."</td>\n";
	      echo "    <td>".sysNumber_readable(round($output2['mvalue']), 0, ',', ' ')."</td>\n";
	      echo "    <td>".sysNumber_readable(round($output2['utvkr']), 0, ',', ' ', true)."</td>\n";
	      echo "    <td>".sysNumber_readable($output2['rea'], 0, ',', ' ', true)."</td>\n";
	      echo "    <td>".sysNumber_readable(round($output2['diravkkr']), 0, ',', ' ', true)."</td>\n";
	      echo "    <td>".sysNumber_readable(round($output2['diravkkr'] + $output2['rea'] + $output2['utvkr']), 0, ',', ' ', true)."</td>\n";
	      echo "  </tr>\n";
		
	      $sum['mvalue'] += $output2['mvalue'];
	      $sum['avalue'] += $output2['aprice'] * $output2['q'];
	      $sum['utvkr']  += $output2['utvkr'] ;
	      $sum['tprice']  += $output2['tprice'] ;
	      $sum['diravkkr']  += $output2['diravkkr'] ;
	      $sum['rea']  += $output2['rea'] ;
          #Quick fix for loans and stuff
		  if ($output2['mvalue'] > 0) {
    	      $arr[] = array(
	           "shortName" => $output2['shortName'],
	           "mvalue"    => round($output2['mvalue']) );
	      }
	     }
        echo "  <tfoot>\n ";
        echo "    <tr>\n";
	    echo "      <td colspan=\"3\" style=\"text-align: left;\">Totalt</td>\n"; 
	    echo "      <td>".number_format($sum['avalue'], 0, ',', ' ')."</td>\n";
	    echo "      <td>".number_format($sum['tprice'], 0, ',', ' ')."</td>\n";
	    echo "      <td>".number_format($sum['mvalue'], 0, ',', ' ')."</td>\n";
	    echo "      <td>".number_format($sum['utvkr'], 0, ',', ' ')."</td>\n";
	    echo "      <td>".number_format($sum['rea'], 0, ',', ' ')."</td>\n";
	    echo "      <td>".number_format($sum['diravkkr'], 0, ',', ' ')."</td>\n";
	    echo "      <td>".sysNumber_readable(($sum['diravkkr'] + $sum['rea'] + $sum['utvkr']), 0, ',', ' ', true)."</td>\n";
	    echo "    </tr>\n";
        echo "  </tfoot>\n";
      }  else {
    $nodata = "Kunde inte hitta några poster";
    }
    echo "</table> ";
  }
  echo $nodata;
 ?>

  <div id="utv" class="chart" style="width: 100%; height: 220px; margin: 10px 0 0 0; z-index:1;"></div>
  <div id="visualization" class="chart" style="width: 100%; height: 400px; margin: 10px 0 0 0; z-index:1;"></div>	
  <div id="chart_div" style="width: 800px; height: 400px; "></div>
  <?php sysFlush_page();  ?>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Task');
        data.addColumn('number', 'Hours per Day');
        data.addRows([<?php

        $chart_data = '';
        foreach ($arr as $out) {
		  $chart_data .= "
          ['$out[shortName]', $out[mvalue]],";
		}
        $chart_data = substr_replace($chart_data ,"",-1	);
        echo $chart_data;
		
        ?>]);

        var options = {
          title: 'Utgående fördelning investeringar',
		  chartArea:{left:80,top:40, bottom:0, height:"75%"}
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>


  <?php

  $span = portGetStock($FROM, $TO, '1');
  $fT = $FROM;
  $tT = $TO;
  $aAm = "0";
  $mAm = "0";
  $i = '0';

  $var = array();
  if(strtotime($FROM) <= strtotime('-2 year', strtotime($TO))) {
    $av  = '+3 day';
	$ig  = '+10 day';
  } else if(strtotime($FROM) <= strtotime('-1 year', strtotime($TO))) {
    $av  = '+2 day';
	$ig  = '+4 days';
  } else if(strtotime($FROM) <= strtotime('-10 week', strtotime($TO))) {
    $av  = '+1 day';
	$ig  = '+2 day';
  } else {
    $av  = '+1 day';
	$ig  = '+1 day';
  }
  $temp_var['utv'] = '0';
  foreach($stockList as $key) {
    $valueLow[$key] = portCacheGetHoldingSum($FROM, $key);
    $temp_var['utv'] -= $valueLow[$key]['utv'] + $valueLow[$key]['diravk'] + $valueLow[$key]['rea'];
  }
  
  while($fT <= $tT) {
    $totAm = '0';
    $diravkkr = '0';
    $orStockList = '';
    $firstRun = true;	
    foreach($stockList as $key) {
	  if($firstRun) 
	  	$orStockList .= "stockID = $key ";
      else
        $orStockList .= "OR stockID = $key ";
	  $firstRun = false;
    }

    $query="SELECT SUM(utv) AS utv, SUM(diravk) AS diravk, SUM(rea) AS rea FROM choldingsum
            WHERE date    = '$fT'
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



 ?>
<?php
          if($compareToIndex) {
            $arr = portGetStockTransactionsOld($stockList);
            $indexData = portSimIndex($arr, $indexISIN);
            $nameISIN =  indexResName($indexISIN);
            $chartColumns['1'] = "data.addColumn('number', '".$nameISIN."');\n";
          }

          $chartData = '';
          $first_run = 1;
          foreach($var as $key) {
            if($compareToIndex) {
              if($first_run == 1) {
                $first_run = 0;
                $norm = $indexData[$key['date']]['utv'];
              } 
              $utv = $indexData[$key['date']]['utv'] - $norm;
              $chartData .= "\n['".date('y-m-d', strtotime($key['date']))."', ".round($key['utv']).", ".$utv.",  undefined, undefined],";
            } else {
              $chartData .= "\n['".date('y-m-d', strtotime($key['date']))."', ".round($key['utv']).",  undefined, undefined],";
            }
           }
           $chartColumns['0'] = "data.addColumn('number', 'Portfölj');\n";
           ksort($chartColumns);
		 ?>

  <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();

        data.addColumn('string', 'Year');
        <?php foreach($chartColumns as $arr) echo $arr; ?>
		data.addColumn({type:'string', role:'annotation'});
		data.addColumn({type:'string', role:'annotationText'});
        data.addRows([<?php echo substr_replace($chartData ,"",-1	); ?>]);

        var options = {
         title: 'Utveckling',
		 annotation: {'1': {style: 'letter'}},
		 chartArea:{left:80,top:40, bottom:0, height:"75%"},
        };

        var chart = new google.visualization.LineChart(document.getElementById('utv'));
        chart.draw(data, options);
      }
    </script>
<?php
	### First column with names
$output = "[
            ['Datum'";
foreach ($stockList as $key => $stockID) {
  if ($stockID < 1003 or 1006 < $stockID) {
	  $name = stockResName($stockID);
	  $output .= ", '";
	  $output .= $name['shortName'];
	  $output .= "'";
  }
}  
$output .= "], \n";

$fromTime = $FROM;
while (strtotime($fromTime) <= strtotime($TODAY)) {
	$output .= "[";	
	$output .= "'";
	$output .= $fromTime;
	$output .= "'";
		
	$first = false;
	foreach ($stockList as $key => $stockID) {
	  if ($stockID < 1003 or 1006 < $stockID) {
		  $portGetQuantity = portGetQuantity($stockID, $fromTime);
		  $price = stockGetValue($stockID, $fromTime);
		  $marketValue = $portGetQuantity['quantity'] * $price['value'];
		  
		  if ($first == true) {
			$first = false;
		  } else {
			$output .= ",";
		  }
		  $output .= round($marketValue);
	  }
	}
	$output .= "],";
  $fromTime = date('Y-m-d',strtotime ( $ig , strtotime ($fromTime) ) );
}


$output .= "]";
?>
	
    <script type="text/javascript">
      function drawVisualization() {
        // Some raw data (not necessarily accurate)
        var data = google.visualization.arrayToDataTable(<?php echo $output ?>);
      
        // Create and draw the visualization.
        var ac = new google.visualization.AreaChart(document.getElementById('visualization'));
        ac.draw(data, {
          title : 'Investerat kapital över tid',
          isStacked: true,
		  chartArea:{left:80,top:40, bottom:0, height:"75%"},
		  		 areaOpacity: 0.7
        });
      }

      google.setOnLoadCallback(drawVisualization);
    </script>
  </head>
  <body style="font-family: Arial;border: 0 none;">
  


 
	
	
	
<!--  STOCK CHOOSER  -->
<div id="stockChooser" style="display:none;">
<form name="stock" action="" method="post">
<?php
foreach(portGetStock($FROM, $TO, "1") as $stockID) {
  if(in_array($stockID, $stockList)){
    $checked = 'checked="checked"';
  } else {
    $checked = '';
  }
  echo '<input type="checkbox" name="stockList[]" value="'.$stockID.'" '.$checked.' />' ;
  $res = stockResName($stockID);
  print_r($res['shortName']);
  echo "<br />\n";
}
?>
  <input type="submit" value="OK" id="close" />
</form>
</div>
<!--  END STOCK CHOOSER  -->   



  <?php


include 'pageBottom.php';

?>


