<?php

class rss {
  function update($array) {
    foreach($array as $var) { #$var[stockID], $var[link]
      $rss = new rss_php;
      $rss->load($var['link']);
      $items = $rss->getItems();
      foreach($items as $each) {
        $time = date('Y-m-d H:m', strtotime($each['pubDate']));
        $query="SELECT * FROM rss WHERE stockID = '$var[stockID]' AND pubDate = '$time'";
        $result = mysql_query($query);
        if(0 == mysql_num_rows($result)) {
          $pdf = rss::getRelease($each['link']);
          $query = "INSERT INTO rss (stockID, new,              pubDate,  title,          link, pdf)
                    VALUES          ('$var[stockID]', TRUE,      '$time',  '$each[title]', '$each[link]', '" . mysql_real_escape_string($pdf['data']) ."')";
          $result=mysql_query($query) or die(mysql_error());;
        }
      }
    }
  return 1;
  }

  function readAll() {
    $query="SELECT * FROM rss
            ORDER BY pubDate 
            DESC";
    $result=mysql_query($query) or die(mysql_error());;
	while($row = mysql_fetch_assoc($result)) {
      $output[] = $row;
    }
    return $output;
  }

  function readStockID($ID) {
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

  function setRead($id) {
    $query = "UPDATE rss SET new = '0' WHERE ID = $id"; 
    mysql_query($query) or die(mysql_error());;
  }

  function getRelease($link) {
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
  }
  function isUnread() {
    $query = "SELECT * FROM rss WHERE new = '1'";
    $result = mysql_query($query) or die(mysql_error());;
    return '0' < mysql_num_rows($result) ? TRUE : FALSE;
  }
  function getList() {
    $query="SELECT * FROM stockName";
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
}

?>
<?php
/*
	RSS_PHP - the PHP DOM based RSS Parser
	Author: <rssphp.net>
	Published: 200801 :: blacknet :: via rssphp.net
	
	RSS_PHP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY.

	Usage:
		See the documentation at http://rssphp.net/documentation
	Examples:
		Can be found online at http://rssphp.net/examples
*/

class rss_php {
	
	public $document;
	public $channel;
	public $items;

/****************************
	public load methods
***/
	# load RSS by URL
		public function load($url=false, $unblock=true) {
			if($url) {
				if($unblock) {
					$this->loadParser(file_get_contents($url, false, $this->randomContext()));
				} else {
					$this->loadParser(file_get_contents($url));
				}
			}
		}
	# load raw RSS data
		public function loadRSS($rawxml=false) {
			if($rawxml) {
				$this->loadParser($rawxml);
			}
		}
		
/****************************
	public load methods
		@param $includeAttributes BOOLEAN
		return array;
***/
	# return full rss array
		public function getRSS($includeAttributes=false) {
			if($includeAttributes) {
				return $this->document;
			}
			return $this->valueReturner();
		}
	# return channel data
		public function getChannel($includeAttributes=false) {
			if($includeAttributes) {
				return $this->channel;
			}
			return $this->valueReturner($this->channel);
		}
	# return rss items
		public function getItems($includeAttributes=false) {
			if($includeAttributes) {
				return $this->items;
			}
			return $this->valueReturner($this->items);
		}

/****************************
	internal methods
***/
	private function loadParser($rss=false) {
		if($rss) {
			$this->document = array();
			$this->channel = array();
			$this->items = array();
			$DOMDocument = new DOMDocument;
			$DOMDocument->strictErrorChecking = false;
			$DOMDocument->loadXML($rss);
			$this->document = $this->extractDOM($DOMDocument->childNodes);
		}
	}
	
	private function valueReturner($valueBlock=false) {
		if(!$valueBlock) {
			$valueBlock = $this->document;
		}
		foreach($valueBlock as $valueName => $values) {
				if(isset($values['value'])) {
					$values = $values['value'];
				}
				if(is_array($values)) {
					$valueBlock[$valueName] = $this->valueReturner($values);
				} else {
					$valueBlock[$valueName] = $values;
				}
		}
		return $valueBlock;
	}
	
	private function extractDOM($nodeList,$parentNodeName=false) {
		$itemCounter = 0;
		foreach($nodeList as $values) {
			if(substr($values->nodeName,0,1) != '#') {
				if($values->nodeName == 'item') {
					$nodeName = $values->nodeName.':'.$itemCounter;
					$itemCounter++;
				} else {
					$nodeName = $values->nodeName;
				}
				$tempNode[$nodeName] = array();				
				if($values->attributes) {
					for($i=0;$values->attributes->item($i);$i++) {
						$tempNode[$nodeName]['properties'][$values->attributes->item($i)->nodeName] = $values->attributes->item($i)->nodeValue;
					}
				}
				if(!$values->firstChild) {
					$tempNode[$nodeName]['value'] = $values->textContent;
				} else {
					$tempNode[$nodeName]['value']  = $this->extractDOM($values->childNodes, $values->nodeName);
				}
				if(in_array($parentNodeName, array('channel','rdf:RDF'))) {
					if($values->nodeName == 'item') {
						$this->items[] = $tempNode[$nodeName]['value'];
					} elseif(!in_array($values->nodeName, array('rss','channel'))) {
						$this->channel[$values->nodeName] = $tempNode[$nodeName];
					}
				}
			} elseif(substr($values->nodeName,1) == 'text') {
				$tempValue = trim(preg_replace('/\s\s+/',' ',str_replace("\n",' ', $values->textContent)));
				if($tempValue) {
					$tempNode = $tempValue;
				}
			} elseif(substr($values->nodeName,1) == 'cdata-section'){
				$tempNode = $values->textContent;
			}
		}
		return $tempNode;
	}
	
	private function randomContext() {
		$headerstrings = array();
		$headerstrings['User-Agent'] = 'Mozilla/5.0 (Windows; U; Windows NT 5.'.rand(0,2).'; en-US; rv:1.'.rand(2,9).'.'.rand(0,4).'.'.rand(1,9).') Gecko/2007'.rand(10,12).rand(10,30).' Firefox/2.0.'.rand(0,1).'.'.rand(1,9);
		$headerstrings['Accept-Charset'] = rand(0,1) ? 'en-gb,en;q=0.'.rand(3,8) : 'en-us,en;q=0.'.rand(3,8);
		$headerstrings['Accept-Language'] = 'en-us,en;q=0.'.rand(4,6);
		$setHeaders = 	'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'."\r\n".
						'Accept-Charset: '.$headerstrings['Accept-Charset']."\r\n".
						'Accept-Language: '.$headerstrings['Accept-Language']."\r\n".
						'User-Agent: '.$headerstrings['User-Agent']."\r\n";
		$contextOptions = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>$setHeaders
			)
		);
		return stream_context_create($contextOptions);
	}
	
}

?>
