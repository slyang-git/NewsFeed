<?php
/**
*	Description����������ࣻ
*	����������ĳ����࣬���������еĳ��󷽷�
*	Created Date��2014-08-09 13:15
* 	Modified Date�� 
*	Author����˫�� slyang@aliyun.com
* 	
**/

abstract class crawler {

	abstract public function start($url);
	abstract public function extract_links($html);
	abstract public function extract_content($html);
	abstract public function insert($article);
	
}

?>