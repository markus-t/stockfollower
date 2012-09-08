<?php

$site="rss";

include 'class/rss.php';
include 'class/port.php';
include 'class/stock.php';
include 'class/index.php';
include 'class/sys.php';
include 'config.php';

# Download PDF set.
if(isset($_GET['pdf'])) {
  $query = "SELECT pdf FROM rss WHERE ID = '$_GET[pdf]'";
  $result= mysql_query($query) or die(mysql_error());;
  $pdf   = mysql_fetch_assoc($result);
  header("content-type: application/pdf");
  echo $pdf['pdf'];
  exit;
}


include 'pageTop.php';

if(!isset($_GET['load'])) {
  $out = rss::readAll();

  echo '<table width="100%">';
  foreach($out as $each) {
    $stockName = stock::resName($each['stockID']);
    echo "<tr>";
    echo "<td style=\"text-align:left;\">" . $each['pubDate'] . '</td>';
    echo "<td style=\"text-align:left;\">" . $stockName['name'] . '</a></td>';

    echo '<td style="text-align:left;">';
    if($each['new'] == '1')
      echo '<img src="img/unread.png" width="12px"><b>';
    echo '<a href="rss.php?load=' . $each['ID'] . '">' . $each['title'] . '</a>';
    if($each['new'] == '1')
      echo '</b>';
    echo '</td>';
    echo '<td style="text-align:left;"> <a href="' . $each['link'] . '">LÄNK</a>';
    echo '</td>';

    echo '</tr>';
  }

  echo "</table>";

} else {
  rss::setRead($_GET['load']);
  echo '
<object data="rss.php?pdf='. $_GET['load'] .'" type="application/pdf" height="700px" width="100%" id="pdf" >
</object>
';
}
include 'pageBottom.php';


?> 

