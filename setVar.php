<?php


#Set userID
if(!empty($_GET['userID'])) {
  if($_GET['userID'] == 1)
    $_SESSION['admin'] = true;
  else
    $_SESSION['admin'] = false;
  $_SESSION['userID'] = $_GET['userID'];
  $userID = $_SESSION['userID'];
} else if(!empty($_SESSION['userID'])) {
  $userID = $_SESSION['userID'];
} else {
  $userID = false;
  $_SESSION['admin'] = false;
}


if(!empty($_GET['indexID'])) {
  $_SESSION['indexID']  = $_GET['indexID'];
  $indexISIN = $_SESSION['indexID'];
} else if(!empty($_SESSION['indexID'])) {
  $indexISIN = $_SESSION['indexID'];
} else {
  $indexISIN = '-';
}

if(!empty($_GET['loggaut'])) {
  session_destroy();
  header( "Location: /stock/" ) ;
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

#Check if we want new stockList from Post
if(isset($_POST['stockList'])) {
  $stockList = $_POST['stockList'];
  $_SESSION['stockList'] = $stockList;
#Else from session
} else if (isset($_SESSION['stockList'])) {
  $stockList = $_SESSION['stockList'];
#Else we take standard list.
} else if (empty($stockList)) {
  $stockList = portGetStock('2010-01-01', $ENDDATE, $userID);
}

############# Check values
  
# Users
$USERS = array("1", "2", "3");

#Both GET TO and GET FROM
if(!empty($_GET['to']) && !empty($_GET['from'])){  
  $_SESSION['TO']    = date('Y-m-d', strtotime($_GET['to']));
  $_SESSION['FROM']  = date('Y-m-d', strtotime($_GET['from']));
  $TO                = $_SESSION['TO'];
  $FROM              = $_SESSION['FROM'];
#Only GET TO
} else if(!empty($_GET['to']) && empty($_GET['from'])){
  $_SESSION['TO']    = date('Y-m-d', strtotime($_GET['to']));
  $_SESSION['FROM']  = $DEFAULTSTART;
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
  $FROM = $DEFAULTSTART;
}

if(strtotime($TO)   > strtotime($TODAY)) 
  $TO = $TODAY;
if(strtotime($FROM) > strtotime($TO)) 
  $FROM = $TO;
if(strtotime($FROM) < strtotime($STARTDATE))
  $FROM = $STARTDATE;
  
if(isset($_REQUEST['stockID']) && preg_match('/^[0-9]*$/', $_REQUEST['stockID'])) {
  $stockID = $_REQUEST['stockID'];
} else {
  $stockID = false;
}

@$addDate        = $_REQUEST['date'];
@$addStockID     = $_REQUEST['stockID'];
@$addPrice       = str_replace(',', '.', $_REQUEST['value']);
@$courtage       = $_REQUEST['courtage'];
@$addQuantity    = str_replace(',', '.', $_REQUEST['antal']);
@$addUserID      = $userID;


#No more sessionwriting.
session_write_close();

#Hole list.
$stockListAll = portGetStock($STARTDATE, $ENDDATE, $userID);

#Intersect arrays and remove papers that are not owned under that specific period.
$stockAvailable = portGetStock($FROM, $TO, $userID);
$stockPapers = array_intersect($stockList, $stockAvailable);

?>