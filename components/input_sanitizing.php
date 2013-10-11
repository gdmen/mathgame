<?php
function get_request_var($var){
	$val = $_REQUEST[$var];
	if (get_magic_quotes_gpc())
		$val = stripslashes($val);
	return $val;
}
?>