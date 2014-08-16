<?php
/**
*	Description：URL类；
*	Created Date：2014-08-08
* 	Modified Date： 2014-08-10 15:24
*	Author：杨双龙 slyang@aliyun.com
* 	
**/

class url {

	//爬取的URL队列
    private $_fetched_list = array();
	
	/**
	 *	构造函数
	 */
	public function __construct() {
	}

    /**
	 * 判断url是否已经在已爬取队列中
     * @param $url
     */
    public function is_fetched( $url ) {
        return array_key_exists(md5(trim($url)), $this->_fetched_list);
    }

    /**
	 * 将已爬取的URL存入队列
     * @param $url
     */
    public function push( $url ) {
        $this->_fetched_list[md5(trim($url))] = $url;
    }

	/**
	 *	将磁盘文件反序列化
	 */
    public function load_url_history($filepath) {
        if (file_exists($filepath)) {
            $fp = fopen($filepath, 'r');
            $file_content = fread($fp, filesize($filepath));
            fclose($fp);
            $this->_fetched_list = unserialize($file_content);
        }
    }

	/**
	 * 将已爬取队列序列化存储磁盘文件
	 */
    public function save_url_history($filepath) {
        $url_data = serialize($this->_fetched_list);
        $fp = fopen($filepath, 'w');
        fwrite($fp, $url_data);
        fclose($fp);
    }
	
	/**
	 *	返回url队列的大小
	 */
	public function size() {
		return count($this->_fetched_list) . PHP_EOL;
	}
}
?>