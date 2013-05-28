<?php


$site="port";


include 'config.php';
include 'functions/port.php';
include 'functions/stock.php';
include 'functions/index.php';
include 'functions/rss.php';
include 'functions/sys.php';
include 'setVar.php';
include 'pageTop.php';

echo '<div class="contentbox">';
echo '  <h2>Välj portfölj</h2>';
echo '  <hr>';
echo '  <a href="index.php?userID=1" class="loginname">User 1</a><br><br>';
echo '  <a href="index.php?userID=2" class="loginname">User 2</a><br><br>';
echo '  <a href="index.php?userID=3" class="loginname">User 3</a>';
echo '</div>';

include 'pageBottom.php';


?>