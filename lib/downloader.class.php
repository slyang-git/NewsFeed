<?php
/**
*	Description������������������ģʽ��
*	����������Ӧ�õ���ģʽ���ڶ��̻߳����в�����Ч�Ͱ�ȫ��
*	����Ŀǰ�����Ƕ��̣߳�ֻ���ǣ�����Ķ����ԣ�ģ�黯��
*	Created Date��2014-08-10 14:18
* 	Modified Date��
*	Author����˫�� slyang@aliyun.com
* 	
**/

class downloader {
	private $_headers = array();
	private $_user_agent = '';
	private $_proxy = '';
	private $_curl = null;
	public static $_instance;
	private function __construct() {
		$this->_headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36';
		$this->_headers['Referer'] = 'www.google.com.hk';
		$this->_headers['Accept-Language'] = 'zh-CN,zh;q=0.8,en-US;q=0.6,en;q=0.4';
		$this->_proxy = 'http://localhost:8087';
		$this->_init_curl();
	}
	
	public static function get_instance() {
		if(!isset(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
     * ��ʼ��curl
     */
    private function  _init_curl() {
        $this->_curl = curl_init();
        curl_setopt( $this->_curl, CURLOPT_PROXY, $this->_proxy );
        curl_setopt( $this->_curl, CURLOPT_HTTPHEADER, $this->_headers );
        curl_setopt( $this->_curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $this->_curl, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $this->_curl, CURLOPT_TIMEOUT, 120 );
    }
	
	/**
	*	��ҳ���غ������������غ��HTMLԴ���ַ���
	*/
    public function download($url) {
        curl_setopt( $this->_curl, CURLOPT_URL, $url );
        $html = curl_exec( $this->_curl );
        $curl_errno = curl_errno($this->_curl);
        $curl_error = curl_error($this->_curl);
        if ($curl_errno > 0) {
            echo "CURL Error($curl_errno): $curl_error" . PHP_EOL;
            return false;
        }
        return $html;
    }
	
	/**
	*	�����������ͷ�curl��Դ
	*/
	public function __deconstruct() {
        curl_close( $this->curl );
    }
	
}


?>