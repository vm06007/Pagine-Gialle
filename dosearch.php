<?php

ini_set('display_errors',1);
include('igniter.php'); 
error_reporting(E_ALL);

$a = @$_POST["qs"];
$b = @$_POST["dv"];

//$a = 'Pizza';
//$b = 'Milano';

if(!$a && !$b) { 
?>
  <img src="explorer/files/search-layer/default.png">
<?php exit; } else { ?>



<style>

.results { 
  
  margin-top:5px; 
  width:520px; 
  font-family:Arial,Sans-serif; 
  font-size:13px; 
  color: #444;

}


.com {background-image:url('content/com.jpg'); width:35px; height:25px; text-align:center; padding-top:2px; background-repeat:no-repeat; float:right; margin-right:5px; }
.stars {margin-right:10px; float:right;}
.results img {padding:3px;}

.h1 { font-size:26px; }
.h2 { font-size:18px; } 
.right { text-align:right; }

</style>


<!--[if lte IE 8]>

<style>
.com {background-image:url('http://facems.77agency.com/content/com.jpg'); background-position: left top;   }
</style>

<![endif]-->


<table border="0" cellpadding="6" class="results">


<?php  


$c = 1;

  $doc = new DOMDocument();
  $doc->load('http://pgd.paginegialle.it/facebook/search?qs='.$a.'&dv='.$b.'&mr=3&rank=11');
  
  $totalka = $doc->getElementsByTagName("xx");
  $totalka = $totalka->item(0)->getAttribute('nr');
  
?>

<tr>
  <td class="h2">
  <?php echo($totalka); ?> risultati trovati
  </td>
  <td class="h2 right">
  
  </td>

</tr>

<?php  
  
  $results = $doc->getElementsByTagName("co");
  foreach( $results as $result )
  { 

    echo("<tr>");
    
  
        echo("<td style='width:280px;'>");
  
    $names = $result->getElementsByTagName("na");
    $name = $names->item(0)->nodeValue;

    $ums = $result->getElementsByTagName("ums");
    $um = $ums->item(0)->nodeValue;

          echo("<div style='margin:4px; margin-left:0px; font-size:16px;'><a target='_blank' href='http://".$um."?sorgente=fb_appwelcome' style='color:#444'><u style='font-size:16px;'>".ucfirst(strtoupper($name))."</u></a><br></div>");


    $los = $result->getElementsByTagName("lo");
    $lo = $los->item(0)->nodeValue;
    
    $prs = $result->getElementsByTagName("pr");
    $pr = $prs->item(0)->nodeValue;

    $ads = $result->getElementsByTagName("ad");
    $ad = $ads->item(0)->nodeValue;
    
    $cps = $result->getElementsByTagName("cp");
    $cp = $cps->item(0)->nodeValue;

  
    $star = $result->getElementsByTagName("vote");
    $avg = $star->item(0)->getAttribute('avg');
    $tot = $star->item(0)->getAttribute('tot');


    echo("<font style='font-size:12px; '>".$cp." ".$lo." (".$pr.")</font><br> ");
      echo("<font style='font-size:12px; '>".$ad."</font>");
       echo("</td>");
        echo("<td style='text-align:right'>");  
    
    echo("<div class='stars'><img src='content/stars/".round($avg).".png'></div>");
    echo("<div class='com'>".$tot."</div>");
    echo("<a href='http://".$um."/commenti?sorgente=fb_appwelcome#cmntForm' target='_blank' ><img border='0' src='content/scrivi.png'>");

    $c++;

        echo("</td>");    
    echo("</tr>");
    echo("<tr>");
    
    if($c<4) {
    echo("<td colspan='3'><hr>"); 
    echo("</td>");
    }
    
    else {
    echo("<td colspan='3'><div style='position:absolute; top:450px; left:160px;'><a href='http://www.paginegialle.it/pgol/4-".$a."/3-".$b."?sorgente=fb_appwelcome&rk=11' target='_blank' style='color:#444'><br><u><font style='font-size:23px; text-align:center'>Vedi tutti i risultati</font></u>"); 
    echo("</div><br><br><br></td>");
    }
    echo("</tr>");
    
  }
  ?>
  
</table>
<?php if($c<4) { ?>
<img src="explorer/files/search-layer/alternative.png">
<?php } ?>
<?php } ?>

