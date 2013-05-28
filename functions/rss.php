<?php

include './classes/rss_php.php';


  function rssUpdate($array) {
    foreach($array as $var) { #$var[stockID], $var[link]
      $rss = new rss_php;
      $rss->load($var['link']);
      $items = $rss->getItems();
      foreach($items as $each) {
        $time = date('Y-m-d H:m', strtotime($each['pubDate']));
        $query="SELECT * FROM rss WHERE stockID = '$var[stockID]' AND pubDate = '$time'";
        $result = mysql_query($query);
        if(0 == mysql_num_rows($result)) {
          /* $pdf = rssGetRelease($each['link']); */
          $query = "INSERT INTO rss (stockID, new,              pubDate,  title,          link)
                    VALUES          ('$var[stockID]', TRUE,      '$time',  '$each[title]', '$each[link]')";
          $result=mysql_query($query) or die(mysql_error());;
        }
      }
    }
  return 1;
  }

  function rssReadAll() {
    $query="SELECT * FROM rss
            ORDER BY pubDate 
            DESC";
    $result=mysql_query($query) or die(mysql_error());;
	while($row = mysql_fetch_assoc($result)) {
      $output[] = $row;
    }
    return $output;
  }

  function rssReadStockID($ID) {
    $query="SELECT * FROM rss
            WHERE stockID = '$ID'
            ORDER BY pubDate 
            DESC";
    $result=mysql_query($query) or die(mysql_error());;
	while($row = mysql_fetch_assoc($result)) {
      $output[] = $row;
    }
    return !empty($output) ? $output : '';
  }

  function rssSetRead($id) {
    $query = "UPDATE rss SET new = '0' WHERE ID = $id"; 
    mysql_query($query) or die(mysql_error());;
  }

  /*
  function rssGetRelease($link) {
     $handle = fopen($link, "r");
     $contents = stream_get_contents($handle);
     $reg = '/href="(http:\/\/[a-zA-Z0-9.;&=\/?]+\.pdf)"/';
     $reg = '/<li class="release-external-link"><a title="([a-öA-Ö0-9,. ;\:&=\-\/?\)\(]+)" href="(https?\:\/\/[a-öA-Ö0-9. ;&=\-\/?]+)"/';
     preg_match ($reg, $contents, $matches);
	 
     if(!empty($matches['1'])) {
       $filename['1'] = rand('10000', '99999');
       $pdfHandle = fopen($matches['2'], "r");
       $pdf = stream_get_contents($pdfHandle);
       $output = array(
                  'name' =>  $filename['1'],
                  'link' =>  $matches['2'],
                  'data' =>  $pdf                 
                 );
     } else {
       $output = '1';
     }

     return $output;
  }*/
  function rssIsUnread() {
    $query = "SELECT * FROM rss WHERE new = '1'";
    $result = mysql_query($query) or die(mysql_error());;
    return '0' < mysql_num_rows($result) ? TRUE : FALSE;
  }
  function rssGetList() {
    $query="SELECT * FROM stockname";
    $result=mysql_query($query) or die(mysql_error());;
    while($row = mysql_fetch_assoc($result)) {
      if(!empty($row['rss'])) {
        $array[] =  array( 'stockID' => $row['ID'], 
                           'link'    => $row['rss']
                   );
      }
    }
    return $array;
  }


?>

