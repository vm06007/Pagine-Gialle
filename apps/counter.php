<style>

#counter {width:auto;visibility:hidden;top:40px;left:0;background-color:#fff;position:absolute;border-radius:1px;overflow:hidden;}
#counter .shadow {position:absolute;height:13px;width:100%;background:url(content/shadow.png) repeat-x top left;}
#counter .tile {float:left;width:35px;height:48px;background:url(content/tile_wide.png) no-repeat top left;}
#counter .tile_narrow {float:left;width:20px;height:48px;background:url(content/tile_narrow.png) no-repeat top left;background-size:100% 100%;}
.num0, .num1, .num2, .num3, .num4, .num5, .num6, .num7, .num8, .num9, .comma {width:35px;height:48px;background:url(content/numbers_purple.png) no-repeat top left; line-height:48px;text-align:center;}

	.num1 {/*default value*/}
	.num2 {background-position:-35px 0;position:absolute;}
	.num3 {background-position:-70px 0;}
	.num4 {background-position:-105px 0;}
	.num5 {background-position:-140px 0;}
	.num6 {background-position:-175px 0;}
	.num7 {background-position:-210px 0;}
	.num8 {background-position:-245px 0;}
	.num9 {background-position:-280px 0;}
	.num0 {background-position:-315px 0;}
	.comma {width:20px;background-position:-353px 0;}

</style>
<div style="width:520px; height:104px; overflow:hidden; background-image:url(content/yellowgradient.jpg);">
<div style="text-align:center; font-family:verdana; font-weight:bold; font-size:16px; margin-top:10px; width:520px;">Seguici anche tu, siamo gia in:</div>
</div>


<div id="counter" style="visibility: visible; opacity: 1; left:160px; ">
			<div class="shadow"></div>
			
		<?php

    function numberFormat($num) { 
     return preg_replace("/(?<=\d)(?=(\d{3})+(?!\d))/",",",$num); 
    } 

    $sum = 0;
    $pages = array("paginegialle","tuttocitta"); 
    foreach ($pages as $page) 
    {
      
      $page_id = $page;
      $url = 'http://graph.facebook.com/'. $page_id .'/';
      $data = file_get_contents($url);
      if ($data) {
       
        $data = json_decode($data);
        $likes = $data->likes;
        $sum = $sum + $likes;  
        
      }
    }  
    
    $digits = str_split(number_format($sum),1);
    foreach ($digits as $digit) {
          
      if($digit == ',') {echo('<div class="tile_narrow"><div class="comma">&nbsp;</div></div>');}
      else {echo('<div class="tile"><div class="num'.$digit.'">&nbsp;</div></div>');}
          
     }
  
    
?>				        
																
							<br clear="all">
		</div>

