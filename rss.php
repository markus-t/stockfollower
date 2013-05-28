<?php

$site="rss";


include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'setVar.php';
include 'functions/sys.php';

if(isset($_GET['load'])) {
  rssSetRead($_GET['load']);
  header( "Location: $_REQUEST[link]" ) ;
} else {
  include 'pageTop.php';
  $out = rssReadAll();
	
?>
<table width="100%" class="sortable">
<tr class="row">
<th style="text-align: left;">Datum</th>
<th style="text-align: left;">Bolag</th>
<th style="text-align: left;">Titel</th>

</tr>	<?php	

	foreach($out as $each) {
		$stockName = stockInfo($each['stockID']);
		echo "<tr>";
		echo "<td style=\"text-align:left;\">" . $each['pubDate'] . '</td>';
		echo "<td style=\"text-align:left;\">" . $stockName['name'] . '</td>';
		echo '<td style="text-align:left;">';
		if($each['new'] == '1')
		echo '<img src="img/unread.png" width="12px" alt="Oläst"/><b>';
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

