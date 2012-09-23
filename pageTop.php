<?php

header("Content-type: text/html; charset=utf-8");
session_start();

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






?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
<title>STOCK</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" href="http://192.168.0.101/stock/favicon.ico" type="image/vnd.microsoft.icon" />
    <script type="text/javascript" src="js/jsapi"></script>
    <script type="text/javascript" src="js/sorttable.js"></script>
    <link rel="stylesheet" href="css/design.css" type="text/css" />
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
	<link rel="stylesheet" type="text/css" media="all" href="css/datechooser.css" />
	<script type="text/javascript" src="js/datechooser.js"></script>
	<script type="text/javascript">
	<!-- //

		events.add(window, 'load', WindowLoad);

		function WindowLoad()
		{
			/*
				Example 1 Description:
				The DateChooser will close 200 milliseconds after mouseout.
				It will show 10 pixels to the right of, and 10 pixels above the click.
				It will call the FunctionEx1() function (below) when updated.
				Instead of using setLink() or setIcon(), so it attaches an event handler to 'datelinkex1' in the markup.
			*/

			var ndExample1 = document.getElementById('datechooserex1');
			ndExample1.DateChooser = new DateChooser();

			var ndExample2 = document.getElementById('datechooserex2');
			ndExample2.DateChooser = new DateChooser();

			// Check if the browser has fully loaded the DateChooser object, and supports it.
			if (!ndExample1.DateChooser.display)
			{
				return false;
			}

			if (!ndExample2.DateChooser.display)
			{
				return false;
			}

			ndExample1.DateChooser.setCloseTime(2000);
			ndExample1.DateChooser.setXOffset(-110);
			ndExample1.DateChooser.setYOffset(30);
			ndExample1.DateChooser.setUpdateFunction(FunctionEx1);
			document.getElementById('datelinkex1').onclick = ndExample1.DateChooser.display;

			ndExample2.DateChooser.setCloseTime(2000);
			ndExample2.DateChooser.setXOffset(-110);
			ndExample2.DateChooser.setYOffset(30);
			ndExample2.DateChooser.setUpdateFunction(FunctionEx2);
			document.getElementById('datelinkex2').onclick = ndExample2.DateChooser.display;

			return true;
		}

		function FunctionEx1(objDate)
		{
			// objDate is a plain old Date object, with the getPHPDate() property added on.
			document.getElementById('dateinputex1').value = objDate.getPHPDate('Y-m-d');
			return true;
		}

		function FunctionEx2(objDate)
		{
			// objDate is a plain old Date object, with the getPHPDate() property added on.
			document.getElementById('dateinputex2').value = objDate.getPHPDate('Y-m-d');
			return true;
		}

		function FunctionEx6(objDate)
		{
			var ndExample5 = document.getElementById('datechooserex5');
			ndExample5.DateChooser.setEarliestDate(objDate);
			ndExample5.DateChooser.updateFields();

			return true;
		}

	// -->
	</script>
    <script type="text/javascript">
      $(document).ready(function(){


        $('a#close').click(function(){
   		  $('#stockChooser').hide('fast');
		})   

        var closed=true;
   		$('input#stockActivate').click(function(){
          if(closed) {
            closed=false;
            $('#stockChooser').show();
          } else { 
            closed=true;
            $('#stockChooser').hide();
          }
        });

 	  });
    </script>

  </head>
  <body>

<?php
if(empty($stockList)) {
  $stockList = portGetStock('2010-01-01', '2015-01-01', "1");
}
?>



  <?php



?>
  <div id="container">
	<div id="navigation">
		<ul>
			<li><a href="index.php" accesskey="1" <?php if($site == "port") echo "style=\"background: #C9C;\""; ?> ><img src="./img/money.png" height="32" width="32" border="0" alt="Portfölj"/></a></li>
			<li><a href="rss.php" accesskey="2" <?php if($site == "rss") echo "style=\"background: #9CC;\""; ?>><img src="./img/news.png" height="32" width="32" border="0" alt="Nyheter"/></a></li>
            <!--<li><a href="stockIndex.php" accesskey="4" <?php if($site == "index") echo "style=\"background: #99C;\""; ?>>Index</a></li>-->
            <li><a href="update.php" accesskey="3" <?php if($site == "update") echo "style=\"background: #CC9;\""; ?>><img src="./img/update.png" height="32" width="32" border="0" alt="Updatera"/></a></li>
		</ul> 
<div id="menu_right">



<div id="loading" class="menuObjectRight"><img src="img/loading.gif" alt="LOADING" /> </div>


<?php
if(rssIsUnread()) {
  echo '<a href="rss.php" class="menuObjectRight"><img src="img/unread.png" style="padding-right: 10px; margin: 0px;" width="22px" alt="D"/></a>';
}
?>
  <form name="range" action="" method="get" class="menuObjectRight">
<select name="indexID" style="padding: 0; margin: 5;" class="menuObjectRight" accesskey="i">
<?php 

$indexList = indexGetList();
echo '<option value="-">-</option>';
foreach($indexList as $index){
  if($indexISIN == $index['ISIN'])
    $selected = 'selected="selected"';
  else 
    $selected = '';
  echo '<option value="'.$index['ISIN'].'" '.$selected.'>'.$index['name'].'</option>';
}

?>
</select> 



  <input type="hidden" name="stock" value="<?php echo $_stock; ?>"/>
  <span id="datechooserex2">
    <a id="datelinkex2" href="#" ><img src="img/datechooser.png" style="margin: 3px 0 0 0; border:0px;" alt="Välj från datum"/></a>
    <input accesskey="f" type="text" name="from" id="dateinputex2" class="dateInput<?php echo $_dClass ?>" value="<?php echo $FROM ?>" <?php echo $_dField ?> size="8" onclick="javascript:this.form.from.focus();this.form.from.select();" /> 
  </span>

  <span id="datechooserex1">
    <a id="datelinkex1" href="#" ><img src="img/datechooser.png" style="margin: 3px 0 0 0; border:0px;" alt="Välj till datum"/></a>
    <input accesskey="t" type="text" name="to" id="dateinputex1" class="dateInput<?php echo $_dClass ?>" value="<?php echo $TO ?>" <?php echo $_dField ?> size="8" onclick="javascript:this.form.to.focus();this.form.to.select();"  />
  </span>
  <input type="submit" value="OK" />
<!--  <input type="checkbox" style="padding: 0; margin: 5; float: right;" value="sirius" label="gross"/>
  <input type="checkbox" style="padding: 0; margin: 5; float: right;" value="sirius" label="gross"/>
  <input type="checkbox" style="padding: 0; margin: 5; float: right;" value="sirius" label="gross"/>-->
</form>

<input type="submit" value="SORTERA" id="stockActivate" class="menuObjectRight" />


</div>

	</div>
	<div id="content">
	

<?php
sysFlush_page();
?>