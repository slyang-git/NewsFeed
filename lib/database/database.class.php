<?php
/**
*	Description：数据库连接与操作类（单例模式）；
*	Created Date：2014-08-8 
* 	Modified Date： 2014-08-10
*	Author：杨双龙 slyang@aliyun.com
* 	
**/

class database {
    private $_host;
    private $_username;
    private $_password;
    private $_database;
	private $_mysqli;
    private static $_instance;

	/**
	 *	构造函数：初始化数据库连接参数
	 */
    private function __construct() {
        $this->_host = "localhost:3306";
        $this->_username = 'root';
        $this->_password = 'yslong';
        $this->_database = 'news';
		$this->_mysqli = new mysqli($this->_host, $this->_username, $this->_password, $this->_database);
		if ($this->_mysqli->connect_error) {
			die ('database connection error: ' . $this->_mysqli->connect_error);
		}
        if(!$this->_mysqli->set_charset('utf8')) echo 'database charset select error' . PHP_EOL;
    }

	/**
	 *	保证单例模式不被克隆破坏
	 */
    private function __clone() {}

	/**
	 *	获得数据库连接实例对象
	 */
    public static function get_instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

	/**
	 *	返回连接
	 */
	public function getConnection() {
		return $this->_mysqli;
	}
	/**
	 *	向数据库中插入记录
	 */
    public function insert($sql) {
        if ( $this->_mysqli->query($sql) ) {
            return true;
        } else {
            echo $this->_mysqli->error;
            return false;
         }
    }
	
	/**
	 * 查询数据库中是否已经存在对应记录
	 */
	public function query($sql) {
		$result = $this->_mysqli->query($sql);
		//if (!$result) print $this->_mysqli_error; 
		if ( $result->num_rows > 0) {
            return true;
        } else {
            echo $this->_mysqli->error;
            return false;
         }
	}

}

?>