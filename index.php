<?php


 $site="port";


include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';
loggedInCheck();
include 'pageTop.php';


echo '<table class="sortable contentbox" style="width:100%;">';
echo '<tr>';
echo '<th style="text-align: left;">Papper</th>';
echo '<th>Antal</th>';
echo '<th>Kurs</th>';
echo '<th>Ans. Kurs</th>';
echo '<th>Ing. Värde</th>';
echo '<th>Nuv. Värde</th>';
echo '<th>Utv kr</th>';
echo '<th>Rea</th>';
echo '<th>Diravk. kr</th>';
echo '<th>Totalt</th>';
echo '</tr>';

 
  $nodata = '';
  if(is_array($stockPapers)) {
    foreach ($stockPapers as $stockID) 
      $each[] = portGetStockSummary($FROM, $TO, $stockID, $userID);
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
		  if($output2['close']){
		    $out_date = $humanDate . " stängningskurs";
		  } else {
		    $out_date = $humanDate . " klockan " . $output2['time'] ;
		  }
 
          echo "  <tr>\n";
    	  echo "    <td style=\"text-align: left;\"><a href=\"stockinfo.php?stockID=".$output2['id']."\" target=\"_self\">".$output2['shortName']."</a></td>\n"; 
	      echo "    <td>".sysNumber_readable($output2['q'], 2, ',', ' ')."</td>\n";
	      echo "    <td><abbr title=\" ".$out_date." \">".number_format($output2['ltrade'], 2, ',', ' ')."</abbr></td>\n";
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
		echo '    <tr><td colspan=10><form style="display: inline;" action="showTransactions.php"><input type="submit"  value="Visa transaktionslista" id="showTransactionList" class="menuObjectRight" /></form><input type="submit" value="Prisuppdatering" id="stockAddPrice" class="menuObjectRight popup" /><input type="submit" value="Lägg till köp" id="stockActivityEnableW" class="menuObjectRight popup" /></td></tr>';
        echo "  </tfoot>\n";
      }  else {
	    echo "  <tfoot>\n ";
		echo '    <tr><td colspan=10><form style="display: inline;" action="showTransactions.php"><input type="submit"  value="Visa transaktionslista" id="showTransactionList" class="menuObjectRight" /></form><input type="submit" value="Prisuppdatering" id="stockAddPrice" class="menuObjectRight popup" /><input type="submit" value="Lägg till köp" id="stockActivityEnableW" class="menuObjectRight popup" /></td></tr>';
        echo "  </tfoot>\n";
    $nodata = "Kunde inte hitta några poster";
    }
    echo "</table>";
  }
  echo $nodata;
  
if(!empty($arr)) {
 ?>

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
		  chartArea:{left:80,top:40, bottom:0, height:"75%", width:"90%"}
        };

        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>


  <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        // Data
		$.ajax({
			  url: "chart.php?chart=line",
			  dataType:"json",
			  success: function(jsonData){
			  
				var data = new google.visualization.DataTable(jsonData);
			
				var options = {
				 title: 'Utveckling',
				 annotation: {'1': {style: 'letter'}},
				 chartArea:{left:80,top:40, bottom:30, height:"75%"},
				 hAxis: { textPosition: 'none' },
				 lineWidth: 1
				};

				var chart = new google.visualization.LineChart(document.getElementById('utv'));
				chart.draw(data, options);
		},
			  error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#utv').html('Ett fel har uppstått<br>Felkod: ' + errorThrown);
			}
	  })
	  };
    </script>
  
      <script type="text/javascript">
      function drawVisualization() {
        // Data
		  $.ajax({
			  url: "chart.php?chart=area",
			  dataType:"json",
			  success: function(jsonData){
				var data = new google.visualization.DataTable(jsonData);
				  
				// Create and draw the visualization.
				var ac = new google.visualization.AreaChart(document.getElementById('visualization'));
				ac.draw(data, {
				  title : 'Investerat kapital över tid',
				  chartArea:{left:100,top:40, bottom:1000, height:"75%"},
				  isStacked: true,
				  areaOpacity: 0.7,
				  hAxis: { textPosition: 'none' },
				  lineWidth: 1
				});
			  },
			  error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('#visualization').html('Ett fel har uppstått<br>Felkod: ' + errorThrown);
			}
		  });
      }

      google.setOnLoadCallback(drawVisualization);
    </script>

 
  <?php

  
  $dasStock = portGetStock('2011-05-20', $TODAY, $userID);
  $output = array();
  $output['1'] = '';
  $output['2'] = '';
  $output['3'] = '';
  foreach($dasStock as $key) {
    $array = portGetQuantity($key, $TODAY, $userID);
	$value = stockGetValue($key, $TODAY);
	$type  = stockGetType($key);
    $output[$type] = round(($array['quantity'] * $value['value']) + $output[$type]);
  }
  @$readableOutput[1] = sysNumber_readable($output[1], 0, ',', ' ');
  @$readableOutput[2] = sysNumber_readable($output[2], 0, ',', ' ');
  @$readableOutput[3] = sysNumber_readable($output[3], 0, ',', ' ');
  
  $outputJ['cols'] = array(array('id'=>'date', 'label'=>'Hejsan','pattern'=>'','type'=>'string'),
								array('id'=>'summa', 'label'=>'Summa','pattern'=>'','type'=>'number')
								);
  $outputJ['rows'] = array(array('c' => array(array('v' => '', 'f' => 'Aktier'),array('v' => $output[1], 'f' => "$readableOutput[1] kr"))),
						   array('c' => array(array('v' => '', 'f' => 'Fonder'),array('v' => $output[2], 'f' => "$readableOutput[2] kr"))),
						   array('c' => array(array('v' => '', 'f' => 'Pengar'),array('v' => $output[3], 'f' => "$readableOutput[3] kr"))));
 
  $jsonData = json_encode($outputJ);
  
  if(count($stockPapers) < 5 )
    $areaHeight = 180;
  else
    $areaHeight = 250;
  
  echo '<div id="utv" class="contentbox" style="height: 200px; z-index:1;">Utveckling<img class="loading" src="../stock/img/load1.gif" ></div>';
  echo '<div id="visualization" class="contentbox" style="height: '.$areaHeight.'px; z-index:1;">Investerat över tid<img class="loading" src="../stock/img/load1.gif" ></div>';
  echo '<div class="contentbox" style="z-index:1; overflow: hidden;">';
  echo ' <div id="chart_div" style="width:49%; height: 300px; float: left;"><img class="loading" src="../stock/img/load1.gif" ></div>';
?>
    <script type="text/javascript">
    google.load('visualization', '1', {'packages':['corechart']});
    google.setOnLoadCallback(drawChart);
      
    function drawChart() {
	  var jsonData = '<?php echo $jsonData?>';
      var data = new google.visualization.DataTable(jsonData);

      var chart = new google.visualization.ColumnChart(document.getElementById('map'));
      chart.draw(data, {
				  title : 'Fördelning gruperade',
				  legend: {position: 'none'},
				  chartArea:{left:10,top:20, width: '90%'}
				});
    }
    </script>

<div id="map" style="float:right; width:49%; height: 300px;"></div>
</div>
<?php
}
include 'pageBottom.php';

?>
