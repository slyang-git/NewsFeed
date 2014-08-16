<?php
	require('lib/sendmail.class.php');
	
	$mail = new sendmail();
	$mail->group_mail();
	
?>