#!/usr/bin/php5
<?php
# PHP SERVER 

include 'config.php';
include 'class.php';
include 'class_rss.php';

function pD() {
  return date('Y-m-d h:i:s');
}

function u1() {
  global $fetch_nordnet, $fetch_morningstar, $fetch_avanza;
  update::nordnet($fetch_nordnet);
  update::morningstar($fetch_morningstar);
  update::avanza($fetch_avanza);
  pop::cDividendSum();
  pop::cHoldingSum();
}

function u2() {
  global $fetch_nordnet, $fetch_morningstar;
  update::nordnet($fetch_nordnet);
  update::morningstar($fetch_morningstar);
  update::avanza($fetch_avanza);
  pop::cDividendSum();
  pop::cHoldingSum();
}

$rssTimer      = 120;
$rssLastUpdate = time() - $rssTimer - 1;

$run1800  = FALSE;
$run2100  = FALSE;
$firstRun = TRUE;


# Server loop
while(TRUE) {
  #fetch day, before 09:00 is yesterday and after 09:00 is today.
  $hourNow = date('H');
  if('09' > $hourNow) {
    $offset = ($hourNow + 1) * 60 * 60;
    $date = date('Y-m-d', time() - $offset);
  } else {
    $date = date('Y-m-d');
  } 

  if((time() - $rssLastUpdate) > $rssTimer) {
    echo pD() . " Doing RSS feed update\n";
    $array = rss::getList();
    rss::update($array);
    $rssLastUpdate = time();
  }

  if(!$run2100 && date('H') == 21) {
    echo pD() . " Doing 21:00 price update\n";
    u2();
    $run2100 = TRUE;
    $run1800 = TRUE;
  }

  if(!$run1800 && date('H') == 18) {
    echo pD() . " Doing 18:00 price update\n";
    u1();
    $run1800 = TRUE;
  }

  if($firstRun && !$run2100 && !$run1800) {
    echo pD() . " Doing firstrun price update\n";
    u2();
    $firstRun = FALSE;
  }

  echo pD() . " Sleeping\n";
  sleep('20');
}

?>