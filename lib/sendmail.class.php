<?php
/**
*	Description：发送邮件类
*	Created Date：2014-08-09
* 	Modified Date：2014-08-10 22:50
*	Author：杨双龙 slyang@aliyun.com
* 	
**/

require 'lib/phpmailer/PHPMailerAutoload.php';
require 'lib/database/database.class.php';


class sendmail {
	
	private $_body;
	private $_receivers;
	private $_mail;
	
	public function __construct() {
		$this->_receivers = array();
		$this->_mail = new PHPMailer();
		$this->_mail->CharSet = 'UTF-8';
		$this->_mail->IsSMTP();	//设定使用SMTP服务
		$this->_mail->SMTPAuth = true; //启用SMTP验证功能
		//========QQ邮箱===================//
		$this->_mail->Host = 'smtp.qq.com';
		$this->_mail->Username = '1025832379@qq.com';
		$this->_mail->Password = 'jiayouysl517';
		$this->_mail->setFrom('1025832379@qq.com', '杨双龙');
		
		//========Gmail邮箱===================//
		/*
		$this->_mail->Host = 'smtp.gmail.com';
		$this->_mail->Username = 'ysl1989517@gmail.com';
		$this->_mail->Password = 'jyyslmmbb1989517';
		$this->_mail->setFrom('ysl1989517@gmail.com', '杨双龙');
		*/
		//========163邮箱===================//
		/*
		$this->_mail->Host = 'smtp.163.com';
		$this->_mail->Username = 'ysl1989517@163.com';
		$this->_mail->Password = 'jyyslmmbb1989517';
		$this->_mail->setFrom('ysl1989517@163.com', '杨双龙');
		*/
		$this->_mail->Subject = '第3期--每日新闻-身在墙内，尽知墙外事！';
	
	}
	/**
	 *	发送邮件，用户在网页的输入框中输入邮箱后，会调用此函数，单独发送邮件
	 */
	public function send($email) {
		if(!$this->_mailbody()) {
			print iconv('utf-8','GBK','<div>稍等哦，还未收集到今日最新新闻呢:-(' . '</div>');
			return;
		}
		$this->_mail->addAddress($email);
		$this->_mail->msgHTML($this->_body);
        
		if (!$this->_mail->send()) {
			echo "Mailer Error: " . $this->_mail->ErrorInfo;
			return false;
		} else {
			//echo "Message sent!";
			return true;
		}
	}
	
	/**
	 *	生成邮件正文部分内容，从数据库中读取当日的最新新闻
	 */
	private function _mailbody() {
		$db = database::get_instance();
        $mysqli = $db->getConnection();
		//今日时间
		$today = date('Y-m-d');
		//昨日时间
		$yesterday = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
		$sql = "SELECT news_title, news_date, news_content, news_source, news_category
				FROM global_news 
				WHERE DATE(news_date)='" . $today . "'";
		$news_list = '<p>感谢您对我的信任，以下是截止目前为止，程序自动收集的新闻列表,您可以选择查看。如果有更好的建议或意见请直接回复本邮件:-)</p><ul >'; //邮件中开头的列表
		if ( $result = $mysqli->query($sql) ) {
			if ($result->num_rows <= 0) return false;
			while ( $obj = $result->fetch_object()) {
				$news_list .= '<li style="color:blue;margin-left:0px;">' . $obj->news_title . '<span style="color:#858C97;margin-left:10px;">(' . $obj->news_source . ')</span></li>';
				$this->_body .= '<h2 style="color:#007EE5">' . $obj->news_title . '</h2><span style="color:#858C97;">发布时间：' . $obj->news_date . '&nbsp;&nbsp;&nbsp;&nbsp;新闻来源：' .$obj->news_source.'&nbsp;&nbsp;&nbsp;&nbsp;类别：'. $obj->news_category .'</span><br/><br/>' . $obj->news_content;
			}
			$news_list .= '</ul>';
			$this->_body = $news_list . '<div style="width:75%;">' . $this->_body . '</div>';
			$result->close();
		} else {
			echo '生成邮件正文失败！' . PHP_EOL;
		}
		return true; //生成正文成功，则返回true
	}
	
	/**
	 *	从数据库订阅新闻用户列表中，获得用户列表，群发邮件
	 */
	private function _receivers () {
		$db = database::get_instance();
		$mysqli = $db->getConnection();
		$sql = "SELECT user_email FROM subscriber";
		if ( $result = $mysqli->query($sql) ) {
			while ( $obj = $result->fetch_object()) {
				array_push($this->_receivers, $obj->user_email);
			}
			$result->close();
		} else {
			die('读取订阅用户表出错！！' . PHP_EOL);
		}
	}
	
	/**
	 *	群发邮件
	 */
	/*public function group_mail() {
		$this->_mailbody();
		$this->_mail->msgHTML($this->_body);
		$this->_receivers();
		foreach ($this->_receivers as $recipient) {
			$this->_mail->addAddress($recipient);
		}
		
		if (!$this->_mail->send()) {
			echo "Mailer Error: " . $this->_mail->ErrorInfo;
			return false;
		} else {
			echo "Message sent!";
			return true;
		}
	}
	*/
	
	
	public function group_mail() {
		if(!$this->_mailbody()) {
			print '稍等哦，还未收集到今日最新新闻呢:-(' . '<br/>';
			return;
		}
		$this->_mail->msgHTML($this->_body);
		$this->_receivers();
		foreach ($this->_receivers as $recipient) {
			$this->_mail->addAddress($recipient);
		}
		
		if (!$this->_mail->send()) {
			echo "Mailer Error: " . $this->_mail->ErrorInfo;
			return false;
		} else {
			echo "Message sent!";
		}
		
	}
	
	
	
}


?>

