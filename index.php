<html>
<head>
<title>���Ŷ���</title>
<meta charset="GBK" />
<style type="text/css">
	#subcribe {
		margin:0 auto;
		text-color:#f00;
		/*border:1px solid;*/
		text-align:center;
		padding-top:200px;
	}
</style>
</head>

<?php
	require('lib/sendmail.class.php');
	date_default_timezone_set('Asia/Shanghai');
	
	//���û�������Ϣ�����ݿ�
	function save_to_db($sql,$email) {
		$db = database::get_instance();
		$mysqli = $db->getConnection();
		$mysqli->query($sql);
	}
	
	function query($email) {
		//$email = addslashes($email);
		$sql = "SELECT user_email FROM subscriber WHERE user_email='".$email . "'";
		$db = database::get_instance();
		if ($db->query($sql)) {
			return true;
		}
		return false;
	}
	
	if (isset($_POST['email'])) {
		if(empty($_POST['email'])) {
			echo ('<script type="text/javascript">alert("�����-^- ���䲻��Ϊ��:-(");</script>');
			return;
		}
		$email = $_POST['email'];
		if (query($email)) {
			echo '<p style="color:#f00;">������ò���Ѿ����Ĺ�Ŷ��:-(</p>'. PHP_EOL;
		} else {
			$mail = new sendmail();
			//$mail->mailbody();
			$ret = $mail->send($email);
			if ($ret) {
				$time = date("Y-m-d H:m:s");
				$sql = "INSERT INTO subscriber (user_email,user_sub_time) VALUES('$email','$time')";
				save_to_db($sql,$email);
				echo '<div style="text-align:center;color:green;"><h1>��ϲ�������ĳɹ���������ʼ���</h1></div>';
			} else {
				echo '<div style="text-align:center;color:#f00;"><h1>�ò�ƶ���ʧ�ܣ��ȴ��ҵĸĽ��ɣ�</h1></div>';
			}
			
		}
	}
		
	
?>

	
<body>
<div id="subcribe">
	<p style='color:blue;'>360������û���Ҫ�ڵ�ַ������ĩβ���ͼ�꣬ѡ�񡰼���ģʽ��������������ļ������⣩</p>
	<form action="index.php" method="POST">
		�������������ַ<input type="text" name='email'> <button>����</button>
	</form>
</div>

<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']); 
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://182.92.185.250/analytics/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="http://www.chetongji.com/analytics/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
</body>
</html>

