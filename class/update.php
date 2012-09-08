<?php


class update {
  #####NORDNET#####
  function filter($var){ 
    return(preg_match("/^20/", $var));
  }
  function nordnet($fetch) {
    $output = '';
    foreach($fetch as $address) {
      $file = file($address['link']);
      foreach(array_filter($file, "update::filter") as $line){
        $reg = "/(2012-[0-9]{2}-[0-9]{2})[\s]([0-9]+,[0-9]+)/";
        preg_match ($reg, $line, $matches);
        if(!empty($matches)) {
          $matches['2'] = preg_replace("/,/", ".", $matches['2']);
          $query = "INSERT IGNORE stockPrice (date, price, stockID)
                    VALUES ('$matches[1]', '$matches[2]', '$address[stockID]')";
          $output .= $query . "\n";
          $result=mysql_query($query) or die(mysql_error());; 
        }  
      }
    }
    return $output;
  }  

  #####MORNINGSTAR#####
  function morningstar($fetch) {
    $output = '';
    foreach($fetch as $address) {
      $file = file($address['link']);
      foreach($file as $line){
        $reg = "/<td>Senaste NAV<\/td><td>  ([0-9]+,[0-9]+) SEK<\/td><td>([0-9]{4}-[0-9]{2}-[0-9]{2})<\/td>/";
        preg_match ($reg, $line, $matches);
        if(!empty($matches)) {
          $matches['1'] = preg_replace("/,/", ".", $matches['1']);
          $query = "INSERT IGNORE stockPrice (date, price, stockID)
                    VALUES ('$matches[2]', '$matches[1]', '$address[stockID]')";
          $output .= $query . "\n";
          $result=mysql_query($query) or die(mysql_error());; 
        }  
      }
    }
    return $output;
  }

  #####AVANZA#####
  function avanza($fetch) {
    ### Tidigaste klockslag vi kan ta hem rätt uppgifter
    $minTid = '18';
    ### Senaste klockslag vi kan ta hem rätt uppgifter
	$maxTid = '09';
    $output = '';
    $hourNow = date('H');
    $unixt = time();
    if($maxTid > $hourNow) {
      $offset = ($hourNow + 1) * 60 * 60;
      $date = date('Y-m-d', $unixt - $offset);
    } else if($minTid <= $hourNow) {
      $date = date('Y-m-d');
    } else{
      return("tiden är utanför tillåten tid (AVANZA)");
    }
    foreach($fetch as $address) {
      $file = file_get_contents($address['link']);
      $reg = '/>[A-Za-z .]*<\/td><td nowrap class="(winner|looser|neutral)">[\-\+]*[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">[\-\+]*[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">([0-9]+,[0-9]+)<\/td>/';
      preg_match ($reg, $file, $matches);

      if(!empty($matches)) {
	    # Ersätt komma med punkt.
        $matches['6'] = preg_replace("/,/", ".", $matches['6']);
        $query = "INSERT IGNORE stockPrice (date, price, stockID)
                  VALUES ('$date', '$matches[6]', '$address[stockID]')";
        $output .= $query . "\n";
        $result=mysql_query($query) or die(mysql_error());;
      }  
    }
    return $output;
  }

  ###NASDAQ INDEX PARSER### 
  function nasdaqParse($rawData) {
    require_once('classHtmldom.php');
    $html = str_get_html($rawData);

    $i = 0;
    $output = array();
    foreach($html->find('tr') as $e) {
      $temp_row = array();
      foreach($e->find('td') as $f) {
        $temp_row[] = $f->innertext;
      }

      if(!empty($temp_row['0'])) {
        $i++;
        $output[$i]['date'] = $temp_row['0'];
        $temp_row['3'] = preg_replace("/,/", ".", $temp_row['3']);
        $output[$i]['price'] = preg_replace("/ /", "", $temp_row['3']);
      } 

    }
    return $output;
  }

  ###NASDAQ RETRIEVER###
  function nasdaqGet($instrument, $toDate, $fromDate = '2012-05-01') {

    $requestData =
    '<post>
    <param name="SubSystem" value="History"/>
    <param name="Action" value="GetDataSeries"/>
    <param name="AppendIntraDay" value="no"/>
    <param name="Instrument" value="'.$instrument.'"/>
    <param name="FromDate" value="'.$fromDate.'"/>
    <param name="ToDate" value="'.$toDate.'"/>
    <param name="hi__a" value="0,1,2,4,21,8,10,11,12,9"/>
    <param name="ext_xslt" value="/nordicV3/hi_table.xsl"/>
    <param name="ext_xslt_lang" value="sv"/>
    <param name="ext_contenttype" value="application/vnd.ms-excel"/>
    <param name="ext_contenttypefilename" value="_SE0004384915.xls"/>
    <param name="ext_xslt_hiddenattrs" value=",ip,iv,"/>
    <param name="ext_xslt_tableId" value="historicalTable"/>
    <param name="app" value="/index/historiska_kurser/"/>
    </post>';

    $postdata = http_build_query(
        array(
          'xmlquery' => $requestData,
        ) 
    );

    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        ) 
    );

    $context  = stream_context_create($opts);
    $result = file_get_contents('http://www.nasdaqomxnordic.com/webproxy/DataFeedProxy.aspx', false, $context);
    return $result;
  }

  ###INDEX UPDATER###
  function index($values, $isin){
    $output = '';
    foreach($values as $key) {
      $query = "INSERT IGNORE indexPrice (ISIN, date, price)
                VALUES ('$isin', '$key[date]', '$key[price]')";
      $result=mysql_query($query) or die(mysql_error());;
    }
  }

  function nasdaq($toDate) {
    $indexList = index::getList();
    ### Makes php seg fault if the document is to big, use with caution..
	foreach($indexList as $index) {
      $ng = update::nasdaqGet($index['ISIN'], $toDate);
      $output = update::nasdaqParse($ng);
      update::index($output, $index['ISIN']);
    } 
	
    return true;
	return false;
  }
}


?>