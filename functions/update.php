<?php


#####CURL GET URL#####
function get_url($url) {
  $ch = curl_init();
  $timeout = 5;

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');
  $data = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
  curl_close($ch);
  
  if ($httpCode == 200) {
    return $data;
  }
  return false;
}

#####NORDNET#####
function updateFilter($var){ 
	return(preg_match("/^20/", $var));
}
function updateNordnet($fetch) {
	foreach($fetch as $stockID => $link) {
		$file = file($link);
		foreach(array_filter($file, "updateFilter") as $line){
		###BUG
			$reg = "/(2013-[0-9]{2}-[0-9]{2})[\s]([0-9]+,[0-9]+)/";
			preg_match ($reg, $line, $matches);
			if(!empty($matches)) {
				$matches['2'] = preg_replace("/,/", ".", $matches['2']);
				stockUpdatePrice($stockID, $matches['1'], $matches['2']);
			}  
		}
	}
	return true;
}  

function updateNordnetCurrent($nordnetCurrent) {
	global $TODAY;
	$data = get_url("https://www.nordnet.se/mux/web/marknaden/kurslista/aktier.html?marknad=Sverige&lista=1_1&large=on&mid=on&small=on&sektor=0");
	$data = utf8_encode($data);

	foreach($nordnetCurrent as $stockID => $id) {
		$reg  = '`';
		$reg .= '\<td class\="text"\>\<div class\="truncate"\>\<a href\="/mux/web/marknaden/aktiehemsidan/index\.html\?identifier\='.$id.'&marketplace\=[0-9]*" class\="underline"\>[a-zA-Z0-9 \.]*\</a\>\</div\>\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>([0-9]*,[0-9]*)\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>\<span class\="kurs(Minus|Plus|Neutral)"\>[\-]*[0-9]*,[0-9]*\</span\>\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>\<span class\="kurs(Minus|Plus|Neutral)"\>[\-]*[0-9]*,[0-9]*%\</span\>\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>[0-9]*,[0-9]*\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>[0-9]*,[0-9]*\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>[0-9]*,[0-9]*\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>[0-9]*,[0-9]*\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>[0-9 ]*\</td\>[\s\t\r\n]*';
		$reg .= '\<td  \>SEK\</td\>[\s\t\r\n]*';
		$reg .= '\<td class\="last" \>([0-9]*\:[0-9]*) \<img class\="delayicon" title\="[^"]*" alt\="([^"]*)" src\="[^"]*" /\>\</td\>';
		$reg .= '';
		$reg .= '`';
		preg_match($reg, $data, $matches);
		if($matches['5'] == 'StÃ¤ngningskurs') 
			$close = true;
		else
			$close = false;
			
		$matches['1'] = preg_replace("/,/", ".", $matches['1']);
		if($matches['1'] > 0) {
			stockUpdatePrice($stockID, $TODAY, $matches['1'], $close, $matches['4']);
		}
	}
}

#####MORNINGSTAR#####
function updateMorningstar($fetch) {
	foreach($fetch as $stockID => $link) {
		$file = get_url($link);
		$reg = "/<td>Senaste NAV<\/td><td> ([0-9 ]*[0-9]+,[0-9]+) SEK<\/td><td>([0-9]{4}-[0-9]{2}-[0-9]{2})<\/td>/";
		preg_match ($reg, $file, $matches);
		if(!empty($matches)) {
			$matches['1'] = preg_replace("/,/", ".", $matches['1']);
			$matches['1'] = str_replace(" ", "", $matches['1']);
			stockUpdatePrice($stockID, $matches['2'], $matches['1']);
		}  
	}
	return true;
}

#####NORDEA SIX SOLUTIONS#####
function updateNordeaSix($fetch = 0) {
	$data = get_url("http://nordea.solutions.six.se/nordea.public/include/iframe.page?t=nordea_fund_retnav&x5=commonGroupX.intervalPeriod/1*year&x1=main.tickercode/200583&x13=main.priceType/Ret&x100=mainValueBase.tickercode/200583");
    if ($data) {
		$reg = "/\"parent.sV\('([0-9]{4}-[0-9]{2}-[0-9]{2})','([0-9]+.[0-9]+)'\);\"/";
		preg_match_all ($reg, $data, $matches);
		$output = "";
		foreach ($matches['1'] as $key => $line) {
		    $value = $matches['2'][$key];
			$query = "REPLACE INTO stockprice (date, price, stockID)  VALUES ('$line', '$value', '5');";
		    $result=mysql_query($query) or die(mysql_error());;
			$output .= $query . "\n";
		}
		echo $output;
	} else { 
	  echo "not status 200"; 
	}
	  
}

#####AVANZA AKTIER#####
function updateAvanza($fetch) {
	### Tidigaste klockslag vi kan ta hem rätt uppgifter
	$minTid = '18';
	### Senaste klockslag vi kan ta hem rätt uppgifter
	$maxTid = '09';
	$hourNow = date('H');
	if($maxTid > $hourNow) {
		$offset = ($hourNow + 1) * 60 * 60;
		$date = date('Y-m-d', time() - $offset);
	} else if($minTid <= $hourNow) {
		$date = date('Y-m-d');
	} else {
		return("tiden är utanför tillåten tid (AVANZA)");
	}
	foreach($fetch as $stockID => $link) {
	    $check = "SELECT * FROM stockprice WHERE date = '$date' AND stockID = $stockID";
		$result = mysql_query($check);
		$num_rows = mysql_num_rows($result);
		if ($num_rows == 0) {

			$file = file_get_contents($link);
			$reg = '/>[A-Za-z0-9 .]*<\/td><td nowrap class="(winner|looser|neutral)">[\-\+]*[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">[\-\+]*[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">[0-9]+,[0-9]+<\/td><td nowrap class="(winner|looser|neutral)">([0-9]+,[0-9]+)<\/td>/';
			preg_match ($reg, $file, $matches);

			if(!empty($matches)) {
				$matches['6'] = preg_replace("/,/", ".", $matches['6']);
				stockUpdatePrice($stockID, $date, $matches['6']);
			} 
		}
	}
	return true;
}

###NASDAQ INDEX PARSER### 
function updateNasdaqParse($rawData) {
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
function updateNasdaqGet($instrument, $toDate, $fromDate = '2013-02-01') {
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
function updateIndex($values, $isin){
	global $mysqli;
	$stmt = $mysqli->prepare("REPLACE INTO indexprice (ISIN, date, price)
							VALUES (?, ?, ?)");
	$output = '';
	foreach($values as $key) {
		$stmt->bind_param('sss', $isin, $key['date'], $key['price'] );
		$stmt->execute();
	}
}

function updateNasdaq($toDate, $fromDate) {
	$indexList = indexGetList();
	### Makes php seg fault if the document is to big, use with caution..
	foreach($indexList as $index) {
		$ng = updateNasdaqGet($index['ISIN'], $toDate, $fromDate);
		$output = updateNasdaqParse($ng);
		updateIndex($output, $index['ISIN']);
	} 
	return true;
}


?>