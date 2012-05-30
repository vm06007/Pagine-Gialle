
<?php


function parsePageSignedRequest() 
{
	if (isset($_REQUEST['signed_request'])) 
	{
		$encoded_sig = null;
		$payload = null;
		list($encoded_sig, $payload) = explode('.', $_POST['signed_request'], 2);
		$sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
		$data = json_decode(base64_decode(strtr($payload, '-_', '+/'), true));
		return $data;
    }
    return false;
}			if($signed_request = parsePageSignedRequest()) 
				{
					if($signed_request->page->liked)
					{	//$page = file_get_contents ('page2.html');
						include_once("builder.php");
                                                
                    }
					else
					{
						//$page = file_get_contents ('page1.html');
						echo '<div style="width:510px; overflow:hidden;"><img src="explorer/files/cover-layer/default.png"></div>';
					}
				}
?>