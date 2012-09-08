<?php


class sys {
  function flush_page() {
    ob_end_flush();
    flush();
    ob_start(); 
  }

  ### Returns date in more readable way.
  function humanDate($date) { # $date = date in format '2000-01-01'
    switch ($date) {
      case date("Y-m-d"):
        $date = "Idag";
        break;
      case date("Y-m-d", strtotime("-1 day")):
        $date = "Igår";
        break;
    }
    return $date;
  }
  
  function number_readable($a, $b, $c, $d, $negativeRed = false) {
   $output = '';
	if($negativeRed && 0 > $a) 
	  $output = '<span style="color:red;">';
	
    $output  .= number_format($a, $b, $c, $d);
    
	if($negativeRed && 0 > $a) 
      $output .= '</span>';
	
	return $output;
  }
  
}
?>