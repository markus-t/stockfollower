
<!--  STOCK CHOOSER  -->
<div id="stockChooser" style="display:none;">
<input type="submit" value="X" id="stockActivate" class="menuObjectRight" />
<hr>
<form name="stock" action="" method="post">
<?php

foreach($stockListAll as $stockID) {
  if(in_array($stockID, $stockList)){
    $checked = 'checked="checked"';
  } else {
    $checked = ''; 
  }
  $e = "";
  $f = "";
  if (!in_array($stockID, $stockAvailable)) {
    $f = "<font color=gray>";
	$e = "</font>";
  }
  echo '<input type="checkbox" name="stockList[]" value="'.$stockID.'" '.$checked.' />' ;
  $res = stockInfo($stockID);
  echo $f;
  print_r($res['shortName']);
  echo $e;
  echo "<br />\n";
}
?>
  <input type="submit" value="OK" id="close" />
</form>
</div>
<!--  END STOCK CHOOSER  -->   

</div>

<!--  PORT ACTIVITY ADD  -->
<div id="stockActivityAddW" style="display:none;">
<input type="submit" value="X" class="menuObjectRight popup" />
<hr>
<div id="m_content">
<!-- HÄR HAMNAR INNEHÅLLET-->
</div>
</div>
<!--  END PORT ACTIVITY ADD --> 	
	<hr>
<span style="font-size:0.8em; font-style:italic;">StockFollower 2</span>
</div>

</body>
</html>

