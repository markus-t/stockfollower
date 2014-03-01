<?php


include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';
loggedInCheck(false);

#Add stock Activity
if(isset($_REQUEST['ownAmount'])){	
	$data = portGetQuantity($addStockID, $addDate, $userID);
	echo '<hr><div style="background-color:#9F9; border-radius: 4px; padding:5px;">Vid angiven tidpunkt ägde du '.$data['quantity'].' av denna tillgång till ett genomsnitligt inköpspris av '.round($data['aprice']).' kr/st</div>';

} else if(isset($_REQUEST['stockPriceAdd'])) {
	if(stockUpdatePrice($addStockID, $addDate, $addPrice) && portCacheDividendSum() && portCacheHoldingSum($addDate, 0, array($addStockID))) {
		$mysqli->commit();
		echo '<h2>Prisuppgift tillagd</h2>';
		$stockInfo = stockInfo($addStockID);
		echo '<table width="100%" > ';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Papper</th>';
		echo '    <td>'.$stockInfo['shortName'].'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Datum</th>';
		echo '    <td>'.$addDate.'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Kurs</th>';
		echo '    <td>'.$addPrice.'</td>';
		echo '  </tr>';
		echo '</table>';
		echo '<hr>Uppdatera sidan för att se uppdaterad information. <br><input type="submit" id="reload" value="STÄNG">';
	} else {
		$mysqli->rollback();
		echo '<div class="error">Ett fel inträffade</div>';
	}
} else if(isset($_REQUEST['portActivityAdd'])) {
    $portTransactionAdd = portTransactionAdd($addStockID, $addDate, $addQuantity, $addPrice, $courtage, $addUserID, $_REQUEST['activity'], $addTime);
	if($portTransactionAdd && portCacheDividendSum() && portCacheHoldingSum($addDate, array($addUserID), array($addStockID))){
		$stockInfo = stockInfo($addStockID);
		$mysqli->commit();
		echo '<h2>Tillagt</h2>';
		echo '<table width="100%" >';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Papper</th>';
		echo '    <td>'.$stockInfo['shortName'].'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Datum</th>';
		echo '    <td>'.$addDate.'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Tid</th>';
		echo '    <td>'.$addTime.'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Händelse</th>';
		echo '    <td>'.$_REQUEST['activity'].'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Antal</th>';
		echo '    <td>'.$addQuantity.'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Kurs</th>';
		echo '    <td>'.$addPrice.'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Courtage</th>';
		echo '    <td>'.$courtage.'</td>';
		echo '  </tr>';
		echo '  <tr>';
		echo '    <th style="text-align:left;">Summa</th>';
		echo '    <td>'.($courtage + $addPrice * $addQuantity).'</td>';
		echo '  </tr>';
		echo '</table>';
		echo '<hr><br><input type="submit" id="reload" value="STÄNG">';
	} else {
		$mysqli->rollback();
		echo '<div class="error">Ett fel inträffade</div>';
	}
} else if(isset($_REQUEST['stockDividendAdd'])) {
	$quantity = portGetQuantity($_REQUEST['stockID'], $_REQUEST['date'], $userID);
	if($_REQUEST['dividendCalc'] == "perinnehav") {
		if($quantity['quantity'] <> 0){
			$dividend_per_enhet = $_REQUEST['dividend'] / $quantity['quantity'];
			$dividend_per_innehav = $_REQUEST['dividend'];
		} else {
			$dividend_per_enhet = 0;
			$dividend_per_innehav = 0;
		}
	} else if($_REQUEST['dividendCalc'] == "perenhet") {
		$dividend_per_enhet = $_REQUEST['dividend'];
		$dividend_per_innehav = $_REQUEST['dividend'] * $quantity['quantity'];
	}
	echo '<h2>Lägg till utdelning</h2>';
	echo 'Vill du verkligen lägga till denna utdelning till databasen?<br><br>';
	echo '
	<form action="" method="post" id="form_process">
	<table width="100%"  > 
		<tr>
		<th style="text-align:left;">Datum</th>
		<td>'.$_REQUEST['date'].'</td>
		</tr>
		<tr>
		<th style="text-align:left;">Utdelning per enhet</th>
		<td>'.$dividend_per_enhet.'</td>
		</tr>
		<tr>
		<th style="text-align:left;">Utdelning för ditt innehav</th>
		<td>'.$dividend_per_innehav.'</td>
		</tr>
		<tr>
		<th></th>
		<td>
		<input type="hidden" value="1" name="addDividend"/>
		<input type="hidden" value="'.$dividend_per_enhet.'" name="amount"/>
		<input type="hidden" value="'.$_REQUEST['stockID'].'" name="stockID"/>
		<input type="hidden" value="'.$_REQUEST['date'].'" name="date"/>
		<input type="submit" value="Bekräfta" name="addDividend" class="button2"/></td>
		</tr>
	</table>
	</form>
	<div id="form_error"></div>';

} else if(isset($_REQUEST['div']) && $_REQUEST['div'] == 'stockActivityEnableW'){
	echo '<h2>Lägg till köp</h2>';
	echo '<form action="" method="post" id="form_process">';
	echo '<table width="100%"> ';
	echo '<tr>';
	echo '<th style="text-align:left;">Fond eller Aktie</th>';
	echo '<td>';
	if(empty($stockID)) { 
		echo '<select name="stockID" onchange="ownAmount();">';
		echo '<option value="0"> -</option>';
		$indexList = stockGetList();
		
		foreach($indexList as $index)
			echo '<option value="'.$index['stockID'].'">'.$index['shortName'].'</option>';
	
		echo '</select>';
	} else { 
		$output = portGetStock($FROM, $TO, $userID);
		$output2 = portGetStockSummary('2000-01-01', $TODAY, array($stockID), $userID);
		
		echo ''.$output2['0']['shortName'].'';
		echo '<input type="hidden" name="stockID"  value="'.$stockID.'" size="8" />';
	} 
	echo '</td>';
	echo '</tr>'; 
	echo '<tr>';
	echo '<th style="text-align:left;">Datum</th>';
	echo '<td><input type="text" name="date" onchange="ownAmount();" id="date" size="8" style="text-align:right;" onkeyup="ownAmount();" value="'.$TODAY.'"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Tid <abbr title="Behövs endast om du har flera köp och sälj under samma dag">?</abbr></th>';
	echo '<td><input type="text" name="time" onchange="ownAmount();" id="time" size="8" style="text-align:right;" value="00:00"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Händelse</th>';
	echo '<td><select name="activity" ><option value="bought">Köp</option><option value="sold">Sälj</option></select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Konto</th>';
	echo '<td><select name="konto" ><option value="ISK">Investeringsparkonto</option><option value="KF">Kapitalförsäkring</option><option value="VP">Vanligt konto</option></select></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Antal</th>';
	echo '<td><input type="text" name="antal" id="antal" size="8" onkeyup="recalculateSum();" style="text-align:right;" value="0"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Kurs</th>';
	echo '<td><input type="text" name="value" id="value" size="8" onkeyup="recalculateSum();" style="text-align:right;" value="0"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Courtage</th>';
	echo '<td><input type="text" name="courtage"  id="courtage" size="8" value="0" onkeyup="recalculateSum();" style="text-align:right;"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Summa</th>';
	echo '<td><input type="text" name="sum"  id="sum" size="8" disabled value="0" style="text-align:right;"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th></th>';
	echo '<td>';
	echo '<input type="hidden" value="Lägg till" id="close" name="portActivityAdd"/>';
	echo '<input type="submit" value="Lägg till" id="close" name="portActivityAdd" class="button"/></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
	echo '<div id="form_info"></div>';
	echo '<div id="form_error"></div>';

} else if(isset($_REQUEST['div']) && $_REQUEST['div'] == 'removeActivity'){
	if($data = portTransInformation($_REQUEST['transid'])){
		echo '<h2>Ta bort aktivitet</h2>';
		echo 'Vill du verkligen ta bort denna aktivitet från databasen?<br><br>';
		echo '
		<form action="" method="post" id="form_process">
		<table width="100%"  > 
			<tr>
			<th style="text-align:left;">Datum</th>
			<td>'.$data['date'].'</td>
			</tr>
			<tr>
			<th style="text-align:left;">Händelse</th>
			<td>'.$data['action'].'</td>
			</tr>
			<tr>
			<th style="text-align:left;">Antal</th>
			<td>'.$data['quantity'].'</td>
			</tr>
			<tr>
			<th style="text-align:left;">Courtage</th>
			<td>'.$data['courtage'].'</td>
			</tr>
			<tr>
			<th style="text-align:left;">Kurs</th>
			<td>'.$data['price'].'</td>
			</tr>
			<tr>
			<th></th>
			<td>
			<input type="hidden" value="'.$_REQUEST['transid'].'" name="transid"/>
			<input type="hidden" value="1" name="removeActivity"/>
			<input type="hidden" value="'.$data['stockID'].'" name="stockID"/>
			<input type="hidden" value="'.$data['date'].'" name="date"/>
			<input type="submit" value="Bekräfta" name="removeActivity" class="button2"/></td>
			</tr>
		</table>
		</form>
		<div id="form_error"></div>';
	} else {
		echo '<div class="error">Ett fel inträffade</div>';
	}
	
} else if(isset($_REQUEST['div']) && $_REQUEST['div']  == 'addDividend'){
	echo '<h2>Lägg till utdelning</h2>';
	echo '<form action="" method="post" id="form_process">';
	echo '<table width="100%"> ';
	echo '<tr>';
	echo '<th style="text-align:left;">Fond eller Aktie</th>';
	echo '<td>';
	if(empty($stockID)) { 
		echo '<select name="stockID">';
		echo '<option value="0"> -</option>';
		$indexList = stockGetList();
		
		foreach($indexList as $index)
			echo '<option value="'.$index['stockID'].'">'.$index['shortName'].'</option>';
	
		echo '</select>';
	} else { 
		$output = portGetStock($FROM, $TO, $userID);
		$output2 = portGetStockSummary('2000-01-01', $TODAY, array($stockID), $userID);
		
		echo ''.$output2['0']['shortName'].'';
		echo '<input type="hidden" name="stockID"  value="'.$stockID.'" size="8" />';
	} 
	echo '</td>';
	echo '</tr>'; 
	echo '<tr>';
	echo '<th style="text-align:left;">Datum</th>';
	echo '<td><input type="text" name="date" id="date" size="8" style="text-align:right;" value="'.$TODAY.'"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Utdelning</th>';
	echo '<td><input type="text" name="dividend" id="antal" size="8" style="text-align:right;" value="0"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Total utdelning för ditt innehav</th>';
	echo '<td><input type="radio" name="dividendCalc" id="perinnehav" size="8" value="perinnehav"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Utdelning per aktie</th>';
	echo '<td><input type="radio" name="dividendCalc"  id="perenhet" size="8" value="perenhet"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Fast utdelning oavsett innehav (går ej att välja än)</th>';
	echo '<td></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th></th>';
	echo '<td>';
	echo '<input type="hidden" value="Lägg till" id="close" name="stockDividendAdd"/>';
	echo '<input type="submit" value="Lägg till" id="close" name="stockDividendAdd" class="buttondividend"/></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
	echo '<div id="form_info"></div>';
	echo '<div id="form_error"></div>';
} else if(isset($_REQUEST['div']) && $_REQUEST['div']  == 'stockAddPrice'){
	echo '
	<h2>Uppdatera pris manuellt</h2>
	<form action="" method="post" id="form_process">
	<table width="100%"  >';
	echo '<tr>';
	echo '<th style="text-align:left;">Fond eller Aktie</th><td>';
	if(empty($stockID)) {
		echo '<select name="stockID" >'; 
		echo '<option value="0"> -</option>';
		$indexList = stockGetList();
		foreach($indexList as $index){
		  echo '<option value="'.$index['stockID'].'">'.$index['shortName'].'</option>';
		}
		echo '</select>';
	} else { 
		$output = portGetStock($FROM, $TO, $userID);
		$output2 = portGetStockSummary('2000-01-01', $TODAY, $stockID, $userID);
		echo $output2['shortName'];
		echo '<input type="hidden" name="stockID"  value="'.$stockID.'" size="8" />';
	
	 }
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Datum</th>';
	echo '<td><input type="text" name="date" id="date" size="8" style="text-align:right;" value="'.$TODAY.'"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th style="text-align:left;">Kurs</th>';
	echo '<td><input type="text" name="value" id="value" size="8" style="text-align:right;" value="0"/></td>';
	echo '</tr>';
	echo '<tr>';
	echo '<th></th>';
	echo '<td>';
	echo '<input type="hidden" value="Lägg till" id="close" name="stockPriceAdd"/>';
	echo '<input type="submit" value="Lägg till" id="close" name="stockPriceAdd" class="button2"/></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
	echo '<div id="form_error"></div>';
	
} else if(isset($_REQUEST['removeActivity']) ){
	if(deleteTransaction($_REQUEST['transid']) && portCacheHoldingSum($addDate, 0, array($addStockID)) && portCacheDividendSum()) {
		$mysqli->commit();
		echo "Aktivitet har raderats";
	} else {
		$mysqli->rollback();
		echo '<div class="error">Ett fel inträffade</div>';
  }
} else if(isset($_REQUEST['addDividend']) ){
	if(stockAddDividend($_REQUEST['stockID'], $_REQUEST['date'], $_REQUEST['amount'])) {
		$mysqli->commit();
		echo "Lyckades";
	} else {
		$mysqli->rollback();
		echo '<div class="error">Ett fel inträffade</div>';
	}
}
?>