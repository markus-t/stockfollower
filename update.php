<?php
  $site="update";

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/sys.php';
include 'functions/rss.php';
include 'functions/update.php';



include 'pageTop.php'; 



if(!isset($_POST['up']) && !isset($_POST['sumV']) && !isset($_POST['sumD']) && !isset($_POST['updateRss']) && !isset($_POST['backupDb'])) {
?>
<h2>Data</h2>
<hr />
<form name="update" action="./update.php" method="post">
  <input type="checkbox" name="AVA" value="Bike" checked="checked" class="radio"/>Avanza<br />
  <input type="checkbox" name="MS" value="Bike" checked="checked" />Morningstar<br />
  <input type="checkbox" name="NN" value="Bike" checked="checked" />Nordnet<br />
  <input type="checkbox" name="OI" value="Bike" checked="checked" />OMX INDEX<br /><br />
  <input type="checkbox" name="sumD" value="Bike" checked="checked" />Summering Utdelning<br />
  <input type="checkbox" name="sumV" value="Bike" checked="checked" />Summering Värde<br />
  <br />
  <input type="checkbox" name="updateRss" value="Bike" />Pressmedelanden<br />
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

<hr />
<h2>Databashantering<h2>

<form name="backupDb" action="./update.php" method="post">
  <input type="submit" name="backupDb" value="Backup av databas">
</form> 
<form name="reloadDb" action="./update.php" method="post">
  <input type="file" name="reloadDb" value="Backup av databas">
  <input type="submit" name="reloadDb" value="Återställ från tidigare backup">
</form> 
<h3>Användaruppgifter till databas</h3>
<form name="credDb" action="./update.php" method="post">
  <input type="text" name="username" value="Användarnam">
  <input type="text" name="password" value="Lösenord">
  <input type="submit" name="updateDb" value="OK">
</form> 

<!--
<hr \>

<br>
<br>
<form name="updateS" action="./update.php" method="POST">
  <input type="checkbox" name="dividend" value="Bike" checked />Kalkylera utdelningar direkt<br />
  <input type="checkbox" name="sum" value="Bike" checked />Kalkylera summa direkt<br />
  <input type="submit" value="Skicka">
</form> -->


<?php
} else {

mysql_query("BEGIN");

if(isset($_POST['NN'])) {
  echo "NORDNET:"; 
  sysFlush_page();
  if(updateNordnet($fetch_nordnet))
    echo "<span style=\"color: green\"> OK</span>";
  echo '<br />';
}

if(isset($_POST['MS'])) {
  echo "MORNINGSTAR:";
  sysFlush_page();
  if(updateMorningstar($fetch_morningstar)) 
    echo "<span style=\"color: green\"> OK</span>";
  echo '<br />';
}

if(isset($_POST['AVA'])) {
  echo "AVANZA:";
  sysFlush_page();
  $query = "SELECT stockID, link FROM updateavanza";
  $result=mysql_query($query) or die(mysql_error());;
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
     $output[$row['stockID']] = $row['link'];

  if(updateAvanza($output))
    echo "<span style=\"color: green\"> OK</span>";
  echo '<br />';
}

if(isset($_POST['OI'])) {
  ###Takes fetch from Database.
  echo "NASDAQ:";
  sysFlush_page();
  if(updateNasdaq($TODAY))
    echo "<span style=\"color: green\"> OK</span>";
  else 
    echo "<span style=\"color: red\"> EJ OK</span>";
  echo '<br />';
}

if(isset($_POST['sumD'])) {
  echo "Sumering udelning:";
  sysFlush_page();
  if(portCacheDividendSum())
    echo "<span style=\"color: green\"> OK</span>";
  echo '<br />';
}

if(isset($_POST['sumV'])) {
  echo "Sumering värde:";
  sysFlush_page();
  if(portCacheHoldingSum())
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
if(mysql_query("COMMIT")) 
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
	

echo "<br /><br /> KLART!";
}
include 'pageBottom.php';

?>
