<?php
  $site="edit";

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'functions/update.php';
include 'setVar.php';
loggedInCheck();
include 'pageTop.php';

if($stockID) {
	$stockInfo = stockInfo($stockID);
	echo '<form name="updateStock" action="" method="get">';
    echo '<table width="100%" class="tdright contentbox">';
    echo '<caption style="text-align: left; font-size:15px;">Redigera Typ</caption>';
    echo "<tr>";
	echo '<th style="text-align:left;">ID</th>';
	echo "<td>".$stockID."</td>"; 
	echo "</tr>";
    echo "<tr>";
	echo '<th style="text-align:left;">Kortnamn</th>';
	echo '<td><input type="text" name="type" size="90" value="'.$stockInfo['shortName'].'"></td>'; 
	echo "</tr>";
    echo "<tr>";
	echo '<th style="text-align:left;">Namn</th>';
	echo '<td><input type="text" name="type" size="90" value="'.$stockInfo['name'].'"></td>'; 
	echo "</tr>";
	echo "<tr>";
    echo "<tr>";
	echo '<th style="text-align:left;">Typ</th>';
	echo '<td><input type="text" name="type" size="90" value="'.$stockInfo['type'].'"></td>'; 
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Länk för RSS</th>';
	echo '<td><input type="text" name="type" size="90" value="'. $stockInfo['rss'] .'"></td>';
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Länk för Avanza</th>';
	echo '<td><input type="text" name="type" size="90" value="'. $stockInfo['avanza'] .'"></td>';
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Länk för Nordnet</th>';
	echo '<td><input type="text" name="type" size="90" value="'. $stockInfo['nordnet'] .'"></td>';
	echo "</tr>";
	echo "<tr>";
	echo '<th style="text-align:left;">Länk för Morningstar</th>';
	echo '<td><input type="text" name="type" size="90" value="'. $stockInfo['morningstar'] .'"></td>';
	echo "</tr>";
    echo "</table>";
	echo '<input type="submit" name="updateDb" value="Uppdatera">
	<input type="submit" name="updateDb" value="Radera"></form>';
} else {
    $stockListAll2 = stockGetList();
	if(is_array($stockListAll)) {
		echo '<table width="100%" class="sortable contentbox" style="white-space: nowrap; ">
				<tr class="row">
				  <th style="text-align: left;">ID</th>
				  <th style="text-align: left;">Kortnamn</th>
				  <th style="text-align: left;">Namn</th>
				  <th style="text-align: left;">Typ</th>
				  <th style="text-align: left;">Länk för RSS</th>
				  <th style="text-align: left;">Avanza</th>
				  <th style="text-align: left;">Nordnet</th>
				  <th style="text-align: left;">Morningstar</th>
				</tr>	';
		foreach  ($stockListAll2 as $key) {
			echo "<tr>";
			echo '<td style="text-align: left;">' . $key['stockID'] . "</td>";
			$stockInfo = stockInfo($key['stockID']);
			echo  '<td style="text-align: left;"><a href="edit.php?stockID=' . $key['stockID'] . '" ><img src="img/edit.png" height="11" width="11"></a>' . $stockInfo['shortName'] . "</td>";
			echo  '<td style="text-align: left;">' . $stockInfo['name'] . "</td>";
			echo  '<td style="text-align: left;">' . $stockInfo['type'] . "</td>";
			echo  '<td style="text-align: left;">' . ((empty($stockInfo['rss']))?'<span class="red">NEJ</span>':'<span class="green">JA</span>') . "</td>";
			echo  '<td style="text-align: left;">' . ((empty($stockInfo['avanza']))?'<span class="red">NEJ</span>':'<span class="green">JA</span>') . "</td>";
			echo  '<td style="text-align: left;">' . ((empty($stockInfo['nordnet']))?'<span class="red">NEJ</span>':'<span class="green">JA</span>') . "</td>";
			echo  '<td style="text-align: left;">' . ((empty($stockInfo['morningstar']))?'<span class="red">NEJ</span>':'<span class="green">JA</span>') . "</td>";
			echo  "</tr>";
		}
		echo "</table>";
	}

	$str_time = strtotime ( "-1 week" , strtotime ($TODAY) );
	$fromTime = date ( 'Y-m-d' , $str_time ); 

	if(!isset($_POST['up']) && !isset($_POST['sumV']) && !isset($_POST['sumD']) && !isset($_POST['updateRss']) && !isset($_POST['backupDb'])) {
	?>
	<div class="contentbox">
	<h2>Data</h2>
	<hr />
	<form name="update" action="./edit.php" method="post">
	  <input type="checkbox" name="AVA" value="Bike" checked="checked" class="radio"/>Avanza<br />
	  <input type="checkbox" name="MS" value="Bike" checked="checked" />Morningstar<br />
	  <input type="checkbox" name="NN" value="Bike" checked="checked" />Nordnet<br />
	  <input type="checkbox" name="BS" value="Bike" checked="checked" />Bitstamp<br /><br />
	  <input type="checkbox" name="OI" value="Bike" checked="checked" />OMX INDEX<br /><br />
	  <input type="checkbox" name="sumD" value="Bike" checked="checked" />Summering Utdelning<br />
	  Från datum<input type="text" name="dateFrom" value="<?php echo $fromTime ?>" /><br />
	  <input type="checkbox" name="sumV" value="Bike" checked="checked" />Summering Värde<br />
	  <br />
	  <input type="checkbox" name="updateRss" value="Bike" checked="checked" />Pressmedelanden<br />
	  <br />
	  <input type="submit" name="up" value="Kör manuell uppdatering">
	</form> 


	<form name="update1" action="./update.php" method="post">
	  <input type="submit" name="sumD" value="Uppdatera tabell för utdelning">
	</form> 

	<form name="update2" action="./update.php" method="post">
	  <input type="submit" name="sumV" value="Uppdatera tabell för summa">
	</form> 

	<form name="updateRss" action="./update.php" method="post">
	  <input type="submit" name="updateRss" value="Uppdatera RSS">
	</form> 
	</div>
	<?php
	} else {
    echo '<div class="contentbox">';

	if(isset($_POST['NN'])) {
	  echo "NORDNET:"; 
	  sysFlush_page();
	  $nordnet = stockGetUpdateList('nordnet');
	  if(updateNordnet($nordnet))
		echo "<span style=\"color: green\"> OK</span>";
	  echo '<br />';
	}

	if(isset($_POST['MS'])) {
	  echo "MORNINGSTAR:";
	  sysFlush_page();
	  $morningstar = stockGetUpdateList('morningstar');
	  if(updateMorningstar($morningstar)) 
		echo "<span style=\"color: green\"> OK</span>";
	  echo '<br />';
	}

	if(isset($_POST['AVA'])) {
	  echo "AVANZA:";
	  sysFlush_page();
	  $avanza = stockGetUpdateList('avanza');
	  if(updateAvanza($avanza))
		echo "<span style=\"color: green\"> OK</span>";
	  echo '<br />';
	}

	if(isset($_POST['OI'])) {
	  ###Takes fetch from Database.
	  echo "NASDAQ:";
	  sysFlush_page();
	  if(updateNasdaq($TODAY, '2013-01-01'))
		echo "<span style=\"color: green\"> OK</span>";
	  else 
		echo "<span style=\"color: red\"> EJ OK</span>";
	  echo '<br />';
	}
	
	if(isset($_POST['BS'])) {
	  ###Takes fetch from Database.
	  echo " BITSTAMP:";
	  sysFlush_page();
	  if(updateBitstamp())
		echo "<span style=\"color: green\"> OK</span>";
	  else 
		echo "<span style=\"color: red\"> EJ OK</span>";
	  echo '<br />';
	}

	if(isset($_POST['sumD'])) {
	  echo "Sumering utdelning:";
	  sysFlush_page();
	  if(portCacheDividendSum())
		echo "<span style=\"color: green\"> OK</span>";
	  echo '<br />';
	}

	if(isset($_POST['sumV'])) {
	  echo "Sumering värde:";
	  sysFlush_page();
	  if(portCacheHoldingSum($_POST['dateFrom']))
		echo "<span style=\"color: green\"> OK</span>";
	  echo '<br />';
	}

	if(isset($_POST['updateRss'])) {
	  echo "Uppdatering RSS:";
	  sysFlush_page();
	  $array = rssGetList();
	  if(rssUpdate($array))
		echo "<span style=\"color: green\"> OK</span>";
	  echo '<br />';
	}

	echo "Kör uppdateringsordrar databas:";
	sysFlush_page();
	if(mysql_query("COMMIT") && $mysqli->commit()) 
		echo "<span style=\"color: green\"> OK</span>";
	else
		echo "<span style=\"color: red\"> INTE OK</span>";
	echo '<br />';
		
	if(isset($_POST['backupDb'])) {
	  echo "Uppdatering RSS:";
	  sysFlush_page();
	  $array = rssGetList();
	  if($var = mysql_dump('stock'))
		echo mysql_dump('stock');	
	  echo '<br />';
	}
		

	echo "<br /><br /> KLART! </div>";
	}
}
include 'pageBottom.php';

?>
