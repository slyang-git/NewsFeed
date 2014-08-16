<?php
/**
*	Description：爬虫抽象类；
*	定义爬虫类的抽象类，并申明其中的抽象方法
*	Created Date：2014-08-09 13:15
* 	Modified Date： 
*	Author：杨双龙 slyang@aliyun.com
* 	
**/

abstract class crawler {

	abstract public function start($url);
	abstract public function extract_links($html);
	abstract public function extract_content($html);
	abstract public function insert($article);
	
}

?>