<?php

#Error reporting
error_reporting(E_ALL ^ E_STRICT);

#Display errors?
ini_set('display_errors', '1');

#Mysql host, username and password.
$dbSettings = array( 
	'host' 		=> "localhost",
	'username'	=> "stock",
	'password'	=> "zzSLJwBE9eJP3vX6",
	'database'	=> "stock",
	'charset'	=> "utf8");
	
#Timezone
date_default_timezone_set('Europe/Stockholm');

#Allow to store big blobs
$query = ( 'SET @@global.max_allowed_packet = ' . 100 * 1024 * 1024 );
$result=mysql_query($query); 

#Todays date
$TODAY = date("Y-m-d");

#Yesterday
$YESTERDAY = date('Y-m-d',strtotime ( '-1 day' , strtotime ($TODAY) ) );

#Future
$ENDDATE = date('Y-m-d',strtotime ( '+7 day' , strtotime ($TODAY) ) );

#Active from
$STARTDATE = '2000-01-01';

#Default to start the stock on the first day of the year
$DEFAULTSTART = date('Y-01-01');

#Allow script to run a long time to finish updates
set_time_limit(400);

#Dividend 1: Dividend at day based on quantity at day
#Dividend 2: Dividend every day based on last know interest on year basis. (Bank year).
#Dividend 3: Binary dividend based on owning or not.
$stockProperties = 
  array(
    array('type'     => 1,
	      'name'     => 'Aktie',
		  'dividend' => 1),
		  
    array('type'     => 2,
	      'name'     => 'Fond',
		  'dividend' => 1),
		  
    array('type'     => 3,
	      'name'     => 'Räntebärande konto eller kredit',
		  'dividend' => 2));
		  

#How many days to calculate intereset on.
$daysInBankYear = 365;

$con = mysql_connect($dbSettings['host'],"stock","zzSLJwBE9eJP3vX6");  
  
#check connection to Mysql
if(!$con)
  die('Could not connect: ' . mysql_error());

#database to use
mysql_select_db("stock", $con) or die(mysql_error());;

#charset to use
mysql_set_charset('utf8');

header("Content-type: text/html; charset=utf-8");
session_name("stockFollower");
session_start();

#mysqli Prepared STMNT
$mysqli = new mysqli($dbSettings['host'], "stock", "zzSLJwBE9eJP3vX6", "stock");
$mysqli->set_charset("utf8");
$mysqli->autocommit(FALSE);

if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}


?>
