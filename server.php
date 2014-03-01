#!/usr/bin/php5
<?php
# PHP SERVER 

include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/sys.php';
include 'functions/rss.php';
include 'functions/update.php';
include 'setVar.php';

set_time_limit (0);

function pD() {
  return date('Y-m-d H:i:s');
}

function u1() {
	global $TODAY;
	global $mysqli;
	$TODAY = date("Y-m-d");
	
	$nordnet = stockGetUpdateList('nordnet');
	updateNordnet($nordnet);

	$morningstar = stockGetUpdateList('morningstar');
	updateMorningstar($morningstar);

	$avanza = stockGetUpdateList('avanza');
	updateAvanza($avanza);
}

function u2() {
	global $TODAY;
	$TODAY = date("Y-m-d");
	$nordnetCurrent = stockGetUpdateList('nordnetcurrent');
	updateNordnetCurrent($nordnetCurrent);
}

$rssTimer      = 1800;
$rssLastUpdate = time() - $rssTimer - 1;

# Server loop
while(TRUE) {
  if((time() - $rssLastUpdate) > $rssTimer) {
    echo pD() . " Doing RSS feed update\n";
	$rss = rssGetList();
	rssUpdate($rss);
    $rssLastUpdate = time();
  }

	if(date('i') == 00) {
		updateBitstamp();
		if(date('N') <= 5 && date('H') > 08 && date('H') < 19) {
			echo pD() . " Doing update NordnetCurrent\n";
			u2();
		}

		if(date('N') <= 5 && (date('H') == 21 || date('H') == 18)) {
			echo pD() . " Doing update Nordnet, Morningstar and Avanza\n";
			u1();
		}
		echo pD() . " Doing dividends\n";
		portCacheDividendSum();

		if(date('H') == 03) {
			echo pD() . " Doing night rebuild of Holdingsum\n";
			portCacheHoldingSum('2000-01-01');
		} else  {
			echo pD() . " Doing rebuild of holdingsum\n";
			portCacheHoldingSum('2013-01-01');
		}
		
		echo pD() . " Commits\n";
		$mysqli->commit();
	}
  
	if(date('i') == 30) {
		updateBitstamp();
		if(date('N') <= 5 && date('H') > 08 && date('H') < 18) {
			echo pD() . " Doing update NordnetCurrent\n";
			u2();
		}		
		echo pD() . " Doing dividends\n";
		portCacheDividendSum();
		echo pD() . " Doing rebuild of holdingsum\n";
		portCacheHoldingSum('2013-01-01');
		echo pD() . " Commits\n";
		$mysqli->commit();
	}
  
  
  echo pD() . " Sleeping\n";
  sleep('45');
}

?>