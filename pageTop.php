<?php
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n";
echo '	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">'."\n";
echo ' <head>'."\n";
echo '  <title>STOCK</title>'."\n";
echo '  <meta http-equiv="X-UA-Compatible" content="IE=10"/>'."\n";
echo '  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'."\n";
echo '  <link rel="icon" href="http://85.24.243.69/stock/favicon.ico" type="image/vnd.microsoft.icon" />'."\n";
echo '  <link rel="stylesheet" href="css/design.css" type="text/css" />'."\n";
echo '  <link rel="stylesheet" href="css/jqueryui.css" type="text/css" />'."\n";
echo '  <script type="text/javascript" src="js/jsapi"></script>'."\n";
echo '  <script type="text/javascript" src="js/sorttable.js"></script>'."\n";
echo '  <script type="text/javascript" src="js/jquery.js"></script>'."\n";
echo '  <script type="text/javascript" src="js/jquery-ui.js"></script>'."\n";
echo '  <script type="text/javascript" src="js/script.js"></script>'."\n";
echo '  <script type="text/javascript">'."\n";
echo "    google.load('visualization', '1', {packages: ['corechart']});"."\n";
echo "	  $(document).ready(initiPop);"."\n";
echo "	  $(document).ready(iniBut);"."\n";
echo "  </script>"."\n";
echo ' </head>'."\n";
echo ' <body>'."\n";
echo '  <div id="backgroundBlank" style="display:none;">'."\n";
echo '  </div>'."\n";
echo '  <div id="container">'."\n";
echo '   <!-- START TOP NAVIGATION  --> '."\n";
echo '   <div id="navigation">'."\n";
echo '    <ul>'."\n";

$background = ($site == "port") ? ' style="background: #C9C;"' : '';
echo '     <li><a href="index.php" accesskey="1"'.$background.'><img src="./img/money.png" height="32" width="32" border="0" alt="Portfölj"/></a></li>'."\n";

if($_SESSION['admin']){
	$background = ($site == "edit") ? ' style="background: #CC9"' : '';
	echo '     <li><a href="edit.php" accesskey="3" '.$background.'><img src="./img/edit.png" height="32" width="32" border="0" alt="Lägg till/ändra aktier"/></a></li>'."\n";
}
if(isset($_SESSION['userID']) && $_SESSION['userID'] == true) {
	echo '     <li><a href="index.php?loggaut=true"><img src="./img/signout.png" height="32" width="32" border="0" alt="Logga ut"/></a></li>'."\n";
}
echo '    </ul><div class="navitem"></div>'."\n";
echo '    <div id="menu_right">';
if(isset($_SESSION['userID']) && $_SESSION['userID'] == true) {


	### From years start
	$defaultstartPort = portGetStockSummary($DEFAULTSTART, $TODAY, $stockList, $userID);
	$sum = array(
		"mvalue" => "0",
		"avalue" => "0",
		"tprice" => "0",
		"utvkr" => "0",
		"diravkkr" => "0",
		"diravk" => "0",
		"rea" => "0"
	);
	  
	foreach ($defaultstartPort as $fromyear) {
	      $sum['mvalue'] += $fromyear['mvalue'];
	      $sum['avalue'] += $fromyear['aprice'] * $fromyear['q'];
	      $sum['utvkr']  += $fromyear['utvkr'] ;
	      $sum['tprice']  += $fromyear['tprice'] ;
	      $sum['diravkkr']  += $fromyear['diravkkr'] ;
	      $sum['rea']  += $fromyear['rea'] ;
	}
	$defaultstartPercent = ((($sum['mvalue'] + $sum['rea'] + $sum['diravkkr']) / $sum['tprice']) - 1) * 100;
	
	if($defaultstartPercent > 0) 
		$defaultstartImg = 'arrowup.png';
	else
		$defaultstartImg = 'arrowdown.png';
	
	### From yesterday
	$yesterdayPort = portGetStockSummary($YESTERDAY, $TODAY, $stockPapers, $userID);
	$sum = array(
		"mvalue" => "0",
		"avalue" => "0",
		"tprice" => "0",
		"utvkr" => "0",
		"diravkkr" => "0",
		"diravk" => "0",
		"rea" => "0"
	);
	  
	foreach ($yesterdayPort as $fromyear) {
	      $sum['mvalue'] += $fromyear['mvalue'];
	      $sum['avalue'] += $fromyear['aprice'] * $fromyear['q'];
	      $sum['utvkr']  += $fromyear['utvkr'] ;
	      $sum['tprice']  += $fromyear['tprice'] ;
	      $sum['diravkkr']  += $fromyear['diravkkr'] ;
	      $sum['rea']  += $fromyear['rea'] ;
	}
	$yesterdayPercent = ((($sum['mvalue'] + $sum['rea'] + $sum['diravkkr']) / $sum['tprice']) - 1) * 100;
	
	if($yesterdayPercent > 0) 
		$yesterdayImg = 'arrowup.png';
	else
		$yesterdayImg = 'arrowdown.png';
		

	echo '<div style="font-size: 0.7em; padding:0px; margin:0; display:inline-block"><a href="?from='.$DEFAULTSTART.'&to='.$TODAY.'">I ÅR:<br><img src="img/'.$defaultstartImg.'"> '.number_format($defaultstartPercent, 2, ',', ' ').'%</a></div>'."\n";
	echo '<div style="font-size: 0.7em; padding:0px; margin:0; display:inline-block"><a href="?from='.$YESTERDAY.'&to='.$TODAY.'">I DAG:<br><img src="img/'.$yesterdayImg.'"> '.number_format($yesterdayPercent, 2, ',', ' ').'%</a></div>'."\n";
}
### Check for new messages
if(rssIsUnread()) 
	echo '     <a href="rss.php" class="menuObjectRight"><img src="img/unread.png" style="padding-right: 10px; margin: 0px;" width="22px" alt="D"/></a>'."\n";

### User only section
if(isset($_SESSION['userID']) && $_SESSION['userID'] == true) {
	echo '     <form name="range" action="" method="get" class="menuObjectRight">'."\n";
	echo '      <select name="indexID" style="padding: 0; margin: 5;" class="menuObjectRight" accesskey="i">'."\n";

	echo '       <option value="-">-</option>'."\n";
	$indexList = indexGetList();
	foreach($indexList as $index){
		$selected = ($indexISIN == $index['ISIN']) ? 'selected="selected"' : '';
		echo '       <option value="'.$index['ISIN'].'" '.$selected.'>'.$index['name'].'</option>'."\n";
	}

	echo '      </select>'."\n"; 
	echo '      <input type="hidden" name="stockID" value="'.$stockID.'"/>'."\n";
	echo '      <input accesskey="f" type="text" name="from" id="dateinputex2" class="dateInput" value="'.$FROM.'" '.$_dField.' size="10" onclick="javascript:this.form.from.focus();this.form.from.select();" /> '."\n";
	echo '      <img src="img/arrow.png" height="16px" style="margin-top:5px;" alt="->" /> '."\n";
	echo '      <input accesskey="t" type="text" name="to" id="dateinputex1" class="dateInput" value="'.$TO.'" '.$_dField.' size="10" onclick="javascript:this.form.to.focus();this.form.to.select();"  />'."\n";
	echo '      <input type="submit" value="Ok" />'."\n";
	echo '     </form>'."\n";
	echo '     <input type="submit" value="Filtrera" id="stockActivate" class="menuObjectRight" />'."\n";

}

echo '          </div>'."\n";

echo '          <!-- STOP TOP NAVIGATION  --> '."\n";
echo '        </div>'."\n";

echo '        <div id="content">'."\n";

sysFlush_page();
?>