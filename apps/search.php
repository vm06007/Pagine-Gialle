
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script> 
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js" ></script> 


<style>
input {color:#444; height:28px; padding-top:1px;}

</style>

<!--[if lte IE 8]>

<style>

input { 
    padding-top:10px;
    line-height: 12px;
    color:#444;
    height:19px;
}

</style>

<![endif]-->
<link rel="stylesheet" type="text/css" media="all" href="content/1319.css">


<div style="width:520px; height:130px; overflow:hidden; background-image:url(content/box.png);">
<div style="text-align:center; font-family:verdana; font-weight:bold; font-size:16px; margin-top:10px; width:520px;">

<div style="position:absolute;top:80px; left:160px;">
<input id="qs" type="text" class="txtClass" style="width:115px; border:0px;  font-weight:bold; font-size: 15px; color:#444;" defaultVal="Cosa">


<div id="errbox" style="display:none" class="serror-box f-cosa"><div class="serror-box-inner"><div class="serror-box-top"><span class="serror-triangle"> </span></div><div class="serror-box-txt"><a id="errclose" href="javascript:;" title="chiudi" class="serror-close">chiudi</a><div class="serror-field"><span class="serror-sost-img"> </span><p id="errmsg" style="font-size:11px; margin-left:-15px;">È necessario compilare il campo inserendo il nome di una azienda o l'attività</p><div class="clear"> </div></div></div></div></div>


<input id="dv" type="text" class="txtClass" style="width:115px; margin-left:25px; border:0px;  font-weight:bold; font-size: 15px; color:#444;" defaultVal="Dove" value="Dove">
</div>


<div style="position:absolute; top:75px; left:435px;"> 
<img src="content/trova.png" class="search" style="cursor:pointer;">
</div>

<script>

	$(function() 
	{

  $('#errclose').click(function() {
       $('#errbox').fadeOut('slow', function() {
        // Animation complete
      });
   });


  $('.search').click(function() {
  $('#errbox').fadeOut('slow', function() {
        // Animation complete
      });
    
    var qs = $('#qs').attr('value');
    var dv = $('#dv').attr('value');
  
    if(dv == "Dove") { dv=""; }
    if(qs == "" || qs=="Cosa") {
    
      $('#errbox').fadeIn('slow', function() {
        // Animation complete
      });
    
    }
    else {
  
      $.ajax({
      type: "POST",
      url: "dosearch.php",
      data: "qs="+qs+"&dv="+dv,
      
      success: function(data)
      {
        //alert(data); 
        //$('.flash').fadeIn('slow');
        $('#flash').html(data)
      }
      });
     } 
   });     
  });

</script>

</div>
</div>

<script>
$('body').ready(function(){
  $('.txtClass').each( function () {
    $(this).val($(this).attr('defaultVal'));
    $(this).css({color:'grey'});
      });
  
  $('.txtClass').focus(function(){
    if ( $(this).val() == $(this).attr('defaultVal') ){
      $(this).val('');
      $(this).css({color:'black'});
    }
    });
  $('.txtClass').blur(function(){
    if ( $(this).val() == '' ){
      $(this).val($(this).attr('defaultVal'));
      $(this).css({color:'grey'});
    }
    });
});

</script>

<div id="flash"></div>
<?php include_once('dosearch.php'); ?>

