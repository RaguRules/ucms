<?php

function sendSms($to, $message){
	$verify_code=rand(1000,9999);

	$user = "94769669804";
	$password = "3100";
	$text = urlencode($message);
		
	$baseurl ="http://www.textit.biz/sendmsg";
	$url = "$baseurl/?id=$user&pw=$password&to=$to&text=$text";
	$ret = file($url);
		
	$res= explode(":",$ret[0]);

	if (trim($res[0]) === "OK") {
        // Success
        return ['status' => true, 'msg_id' => $res[1]];
    } else {
        // Failed
        return ['status' => false, 'message' => "Error: " . $res[1]];
    }
}

?>



