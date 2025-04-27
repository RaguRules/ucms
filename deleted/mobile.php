<?php
//send verification code
	$verify_code=rand(100000,999999);

	$user = "94769669804";
	$password = "3100";
	$text = urlencode("Department, Hi here, Your verification code is ".$verify_code);
	$to = "94777958841";
		
	$baseurl ="http://www.textit.biz/sendmsg";
	$url = "$baseurl/?id=$user&pw=$password&to=$to&text=$text";
	$ret = file($url);
		
	$res= explode(":",$ret[0]);

	if (trim($res[0])=="OK"){
		echo "Message Sent - ID : ".$res[1];
	}
	else{
		echo "Sent Failed - Error : ".$res[1];
	}

?>



