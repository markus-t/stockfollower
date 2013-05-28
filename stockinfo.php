<?php
$site="port";
##Bug, jämförelse simulering går ej välja

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';
loggedInCheck();
include 'pageTop.php';

$date = portGetStockSpan($stockID, $userID);
if($date)
	$startdate2 = $date;
else
	$startdate2 = $FROM;

if(stockInfo($stockID) == 1){
	sysError("Aktien du försökte titta på verkar inte finnas.");
	exit;
}
 
$output = portGetStock($FROM, $TO, $userID);
$output2 = portGetStockSummary($STARTDATE, $TODAY, $stockID, $userID);


    echo '<div class="contentbox" style="overflow: hidden; background-color: transparent; border:0; padding:0; "><div style="float: left;width: 240px; " ><table width="100%">';
    echo '<caption style="text-align: left; font-size:15px;">Data</caption>';
    echo "<tr>";
	echo '<th style="text-align:left;">Namn</th>';
	echo "<td>".$output2['shortName']."</td>"; 
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Antal</th>';
	echo "<td>".number_format($output2['q'], 2, ',', ' ')."</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Senaste kursen</th>';
	echo "<td>".number_format($output2['ltrade'], 2, ',', ' ')."</td>";	
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Kursdatum</th>';
	echo "<td>".sysHumanDate($output2['date'])."</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Anskaffningspris</th>';
	echo "<td>".number_format($output2['aprice'], 2, ',', ' ')."</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Marknadsvärde</th>';
	echo "<td>".number_format($output2['mvalue'], 0, ',', ' ')."</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Utveckling %</th>';
	echo "<td>".number_format(@($output2['utvkr'] / @$output2['mvalue'] * 100) , 2, ',', ' ')."%</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Utveckling kr</th>';
	echo "<td>".number_format($output2['utvkr'], 0, ',', ' ')."</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Direktavkastning</th>';
	echo "<td>".number_format($output2['diravkkr'], 0, ',', ' ')."</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Realiserad s:a</th>';
	echo "<td>".number_format($output2['rea'], 0, ',', ' ')."</td>";
	echo "</tr>";
	echo "<!--<tr>";
	echo '<th style="text-align:left;">Uppdatering Avanza</th>';
	echo "<td>Ja</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Uppdatering Morningstar</th>';
	echo "<td>Ja</td>";
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Uppdatering Nordnet</th>';
	echo "<td>Ja</td>";
	echo "</tr>-->";
    echo "</table></div>";


echo '<div style=" margin-left: 250px;" ><table class="sortable" width="100%"> 
		<caption style="text-align: left; font-size:15px;">Historik</caption>
			<tr>
			<th width="10px">Del</th>
			<th width="10px">ID</th>
			<th>Datum</th>
			<th>Händelse</th>
			<th>Konto</th>
			<th>Antal</th>
			<th>Courtage</th>
			<th>Kurs</th>
			<th>Summa</th>
			</tr>';


  $activity = portGetStockActivityOld('2000-01-01', $TO, $stockID, $userID);

    $tID  = "1";
    $amount = "0";
    foreach($activity as $key ){
	if($key['ID'] != 0) {
        echo '<tr id="trans'.$key['ID'].'">';
		echo '<td><a class="popup" href="#" id="removeActivity" data-transid="'.$key['ID'].'" data-stockid="'.$stockID.'"><img style="margin:0;padding:0;" src="img/stop.gif"></a></td>';
	 } else {
        echo '<tr>';
		echo '<td></td>';
	}
	  echo "<td>".$tID."</td>";
      echo "<td>".$key['date']."</td>";
	  if($key['action'] == 'bought') {
	    echo "<td>köpt</td>"; 
        $amount += $key['quantity']; 
	  } else if($key['action'] == 'sold') {
	    echo "<td>sålt</td>";
        $amount -= $key['quantity'];
      } else if($key['action'] == 'dividend') {
	    echo "<td>utdelning</td>"; 	
        $key['quantity'] =   $amount;
	  } 
	  echo "<td>".$key['account']."</td>";
	  echo "<td>".$key['quantity']."</td>";
	  echo "<td>".$key['courtage']."</td>";
	  echo "<td>".$key['price']."</td>";
	  echo "<td>".round($key['price'] * $key['quantity'])."</td>";
	  echo "</tr>";
	  $tID = $tID + 1;
      
    }
	
  echo '<tfoot><td colspan="9" style="text-align:left;"><form action="showpricelist.php" method="get"><input type="hidden" value="'.$stockID.'" name="stockID"><input type="submit" value="Visa lagrade kurser"  /></form><input type="submit" value="Lägg till utdelning" id="addDividend" class="popup" /><input type="submit" value="Lägg till köp eller försäljning" id="stockActivityEnableW" data-stockid="'.$stockID.'" class="popup" /></td></tfoot>';
  echo "</table></div>";

  $chartColumns['3'] = "data.addColumn('number', 'Kurs');\n";
  $chartColumns['2'] = "data.addColumn({type:'string', role:'annotation'});\n";
  $chartColumns['1'] = "data.addColumn({type:'string', role:'annotationText'});\n";

  if($compareToIndex) {
    $chartColumns['0'] = "data.addColumn('number', '".indexResName($indexISIN)."');\n";
    $indexToScale = indexGetValue($startdate2, $indexISIN);
    $scaleAgainst = stockGetValue($stockID, $startdate2);
    @$scale = $scaleAgainst['value'] / $indexToScale['price'];
  }

  $current = '0';
  $annotation = 1;
  $chart_data = '';
  $startdate = $startdate2;
  while(strtotime($startdate) <= strtotime($TODAY)) {
    $output = stockGetValue($stockID, $startdate);
    $ann = portGetStockAction($stockID, $startdate );
    $data = '';
    if($compareToIndex) {
      $index = indexGetValue($startdate, $indexISIN);
      $data  = "," . round($index['price'] * $scale, 2);
    }

    if(is_array($activity) && $activity[$current]['date'] == $startdate) {
      ### Display entry ID.
      $chart_data .= "
      ['".date('y-m-d', strtotime($startdate))."',  $output[value], '$annotation', '$ann[price]'".$data."],";
      $annotation++;
 
      ### Check if we have duplicate entries on same day and skip if.
      if(array_key_exists($current + 1, $activity)) {
        while(array_key_exists($current + 1, $activity) && $activity[$current]['date'] == $activity[$current + 1]['date']) {
          $current++;
          $annotation++;
        }
        ### Next entry
        $current++;
      }

    } else {
      $chart_data .= "
      ['".date('y-m-d', strtotime($startdate))."', $output[value],  undefined, undefined".$data."],";
    }
      $startdate = strtotime ( '+1 day' , strtotime ($startdate) ) ;
      $startdate = date ( 'Y-m-d' , $startdate );
    }
    $chart_data = substr_replace($chart_data ,"",-1	);

?>
   <!--Load the AJAX API-->
 <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Year');
       <?php foreach($chartColumns as $arr) echo $arr; ?>

        data.addRows([<?php echo $chart_data; ?>]);
        var options = {
          title: 'Kurs',
		  annotation: {'2': {style: 'letter'}},
		  lineWidth: 1
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
<?php
sysFlush_page();
 
$startdate = $startdate2;
while($startdate <= $TO) {

  $var[] = portCacheGetHoldingSum($startdate, $stockID, $userID);
  $startdate = strtotime ( '+1 day' , strtotime ($startdate) ) ;
  $startdate = date ( 'Y-m-d' , $startdate ); 
}


if($compareToIndex) {
  $transactions = portGetStockTransactions(array('1' => $stockID), $userID);
  $omx = portSimIndex($transactions,$indexISIN);
}

?>

 <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Year');
        data.addColumn('number', 'Utveckling');
        <?php if($compareToIndex) { ?>
        data.addColumn('number', 'Utveckling s:a i <?php echo indexResName($indexISIN);?>');
        <?php } ?>
		data.addColumn({type:'string', role:'annotation'});
		data.addColumn({type:'string', role:'annotationText'});
        data.addRows([<?php
        $chart_data = '';
        $first_run = 1;
          foreach($var as $key) {
			if($compareToIndex) {
              if($first_run == 1 ) {
                $first_run = 0;
                @$norm = $omx[$key['date']]['utv'];
              } 
              @$utv = $omx[$key['date']]['utv'] - $norm . ",";
            } else $utv = '';
            $primary = round($key['utv'] + $key['diravk'] + $key['rea']);
            $chart_data .= "
            ['".date('y-m-d', strtotime($key['date']))."',".$primary.",".$utv." undefined, undefined],";
          }
        $chart_data = substr_replace($chart_data ,"",-1	);
        echo $chart_data;
        ?>]);

        var options = {
          title: 'Utveckling',
		  annotation: {'1': {style: 'letter'}},
		  lineWidth: 1
        };

        var chart = new google.visualization.LineChart(document.getElementById('char_div'));
        chart.draw(data, options);
      }
    </script>
</div>
<?php
 echo '

<div id="char_div" class="contentbox" style="height: 220px; z-index:1;"></div>
<div id="chart_div" class="contentbox" style="height: 220px; z-index:1;"></div>

'; 
  $out = rssReadStockID($stockID);
  if(!empty($out)) {
 
echo '

	<table class="sortable contentbox" style="width: 100%;">
	<tr class="row">
	<th style="text-align: left;">Datum</th>
	<th style="text-align: left;">Titel</th>
	</tr>';

    foreach($out as $each) {
      $stockName = stockInfo($each['stockID']);
      echo "<tr>";
      echo "<td style=\"text-align:left;\">" . $each['pubDate'] . '</td>';
      echo '<td style="text-align:left;">';
      if($each['new'] == '1')
        echo '<img src="unread.png" width="12px"><b>';
      echo '<a href="rss.php?load=' . $each['ID'] . '&link='.$each['link'].'">' . $each['title'] . '</a>';
      if($each['new'] == '1')
        echo '</b>';
      echo '</td>';
      echo '</tr>';
    }

    echo "</table>";
  }
  
 include 'pageBottom.php';
 
?>
  
  
