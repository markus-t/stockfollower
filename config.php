<?php

header("Content-type: text/html; charset=utf-8");
session_start();

error_reporting(E_ALL ^ E_STRICT);
/*error_reporting(E_ALL);*/
/*ini_set('display_errors', '1');*/
ini_set('display_errors', '1');
$con = mysql_connect("localhost","username","password");
if(!$con)
  die('Could not connect: ' . mysql_error());
  
mysql_select_db("stock", $con) or die(mysql_error());;

mysql_set_charset('utf8');
$query = ( 'SET @@global.max_allowed_packet = ' . 100 * 1024 * 1024 );
$result=mysql_query($query) or die(mysql_error());; 


$TODAY = date("Y-m-d");

set_time_limit(200);

#Both GET TO and GET FROM
if(!empty($_GET['to']) && !empty($_GET['from'])){  
  $_SESSION['TO']    = date('Y-m-d', strtotime($_GET['to']));
  $_SESSION['FROM']  = date('Y-m-d', strtotime($_GET['from']));
  $TO                = $_SESSION['TO'];
  $FROM              = $_SESSION['FROM'];
#Only GET TO
} else if(!empty($_GET['to']) && empty($_GET['from'])){
  $_SESSION['TO']    = date('Y-m-d', strtotime($_GET['to']));
  $_SESSION['FROM']  = "2012-01-01";
  $TO                = $_SESSION['TO'];
  $FROM              = $_SESSION['FROM'];
#Only GET FROM
} else if(empty($_GET['to']) && !empty($_GET['from'])){
  $_SESSION['TO']    = $TODAY;
  $_SESSION['FROM']  = date('Y-m-d', strtotime($_GET['from']));
  $TO                = $_SESSION['TO'];
  $FROM              = $_SESSION['FROM'];
#None, both SESSION TO & SESSION FROM
} else if(!empty($_SESSION['TO']) && !empty($_SESSION['FROM'])) {
  $TO                = $_SESSION['TO'];
  $FROM              = $_SESSION['FROM'];
#Standard dates.
} else {
  $TO   = $TODAY;
  $FROM = "2012-01-01";
}

if(strtotime($TO)   > strtotime($TODAY)) 
  $TO = $TODAY;
if(strtotime($FROM) > strtotime($TO)) 
  $FROM = $TO;
if(strtotime($FROM) < strtotime('2011-05-01'))
  $FROM = '2011-05-01';


if(!empty($_GET['indexID'])) {
  $_SESSION['indexID']  = $_GET['indexID'];
  $indexISIN = $_SESSION['indexID'];
} else if(!empty($_SESSION['indexID'])) {
  $indexISIN = $_SESSION['indexID'];
} else {
  $indexISIN = '-';
}

if($indexISIN != '-')
  $compareToIndex = true;
else
  $compareToIndex = false;


if(!empty($_GET['stock'])) {
  $_stock  = $_GET['stock'];
  $_dField = 'readonly="readonly"';
  $_dClass = ' dateInputGrey';
} else {
  $_stock  = '';
  $_dClass = '';
  $_dField = '';
}


if(isset($_GET['stock'])) {
  $stockID = $_GET['stock'];
}

if(isset($_POST['stockList'])) {
  $stockList = $_POST['stockList'];
  $_SESSION['stockList'] = $stockList;
} else if (isset($_SESSION['stockList'])) {
  $stockList = $_SESSION['stockList'];
} 

#Dividend 1: Dividend at day based on quantity at day
#Dividend 2: Dividend every day based on last know interest on year basis. (Bank year).

$stockProperties = 
  array(
    array('type'     => 1,
	      'name'     => 'Aktie',
		  'dividend' => 1),
		  
    array('type'     => 2,
	      'name'     => 'Fond',
		  'dividend' => 1),
		  
    array('type'     => 3,
	      'name'     => 'Räntebärande konto',
		  'dividend' => 2));
		  
$daysInBankYear = 360;

$fetch_nordnet = 
  array(
    array('stockID' => 1,
	      'link'    => 'https://www.nordnet.se/mux/laddaner/historikLaddaner.ctl?isin=SE0000112724&country=Sverige'),
    array('stockID' => 11,
	      'link'    => 'https://www.nordnet.se/mux/laddaner/historikLaddaner.ctl?isin=SE0003208792&country=Sverige')
  ); 

$fetch_morningstar = 
  array( 
    array('stockID' => 5,
	      'link'    => 'http://morningstar.se/Funds/Quicktake/Overview.aspx?perfid=0P00005U1J&programid=0000000000'),
    array('stockID' => 7,
	      'link'    => 'http://morningstar.se/Funds/Quicktake/Overview.aspx?perfid=0P00008WHN&programid=0000000000'),
    array('stockID' => 9,
	      'link'    => 'http://morningstar.se/Funds/Quicktake/Overview.aspx?perfid=0P00000LGM&programid=0000000000')
  ); 

$fetch_avanza = 
  array( 
    array('stockID' => 6,
	      'link'    => 'https://www.avanza.se/aza/aktieroptioner/kurslistor/aktie.jsp?orderbookId=298607'),
    array('stockID' => 13,
              'link'    => 'https://www.avanza.se/aza/aktieroptioner/kurslistor/aktie.jsp?orderbookId=362016')
    ); 

?>
