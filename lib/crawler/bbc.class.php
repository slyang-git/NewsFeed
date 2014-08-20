<?php 
/**
*	Description：BBC中文网http://www.bbc.co.uk/zhongwen/simp/爬虫程序；
*	对每一个入口URL，首先解析出其中的所有文章链接地址，再将这些文章地址逐个下载，
*	解析出其中的重要字段，如新闻标题，发布时间，新闻正文等
*	Created Date：2014-08-13 23:06
* 	Modified Date： 2014-08-17 21:40 2014-08-19 21:00
*	Author：杨双龙 slyang@aliyun.com
* 	
**/

require_once('lib/simple_html_dom.php');
require_once('lib/url/url.class.php');
require_once('lib/database/database.class.php');
require_once('lib/crawler/crawler.class.php');
require_once('lib/downloader.class.php');

defined('IMAGE_SAVE_PATH') or define('IMAGE_SAVE_PATH', 'images/');
defined('IMAGE_EXT') or define('IMAGE_EXT', '.jpg');

class BBCCrawler extends crawler {
	private $_downloader = null;
	private $_url = null;
	private $_url_history;
	
	/**
	*	构造函数：加载历史url数据（已爬取url文件）
	*/
	public function __construct() {
		//获得网页下载器实例
		$this->_downloader = downloader::get_instance();
		$this->_url = new url();
		$this->_url_history = 'urllog/bbc_url.txt';
		$this->_url->load_url_history($this->_url_history);
	}

	 /**
	 * 爬虫启动运行，url为入口地址
	 */
	public function start($url) {
        $html = $this->_downloader->download($url);
		//$links = array();
        if (!($links = $this->extract_links($html))) {
			print 'No URLs Found in this Entrance' . PHP_EOL;
			return;
		}
		
		//print '开始下载新闻 ' . PHP_EOL;
        foreach ($links as $link) {
            if ($this->_url->is_fetched($link)) {
                print 'Ignored a repetive URL ' . PHP_EOL;
                continue;
            }
			
            $html = $this->_downloader->download($link);
            if (!$html) continue;
            $article = array();
            if(!empty($html)) {
                $article = $this->extract_content($html);
				if (!$article) continue;
			}
            //var_dump($article);
            $this->insert($article);
			$this->_url->push($link);
        }
		$this->_url->save_url_history($this->_url_history);
		
	}

    /**
	 * 抽取页面中的链接地址
     * @param $html
     * @return array
     */
    public function extract_links($html) {
        $links = array();
		$pattern = '{/zhongwen/simp/\\w+/\\d+/\\d+/\\d+_\\w+\\.shtml}';
		$matches = array();
		preg_match_all($pattern, $html, $matches);
		echo 'Extracted URLs: ' . count($matches[0]) . PHP_EOL;
		
		foreach($matches[0] as $link) {
			if ( $this->_url->is_fetched($link) ) {
                echo 'Ignored an URL' . PHP_EOL;
                continue;
            }
			
			$link = 'http://www.bbc.co.uk' . $link;
			//print $link . PHP_EOL;
            array_push($links, $link);
		}
		return $links;
	}

	/**
	*	解析网页内容
	*/
	public function extract_content($html) {
		$doc = str_get_html($html);
		$element = $doc->find('div.module');
		if (!isset($element)) {
			print 'Not a news page.' . PHP_EOL;
			return false;
		}
		
        $article = array();
		
		//提取新闻图片地址
		$image_url = '';
		foreach ($doc->find('div.module') as $element) {
			if ($element->find('img')) {
				foreach($element->find('img') as $ele) {
					$image_url = $ele->src;
					$article['image_url'] = $image_url;
				}
			}
		}
		
		//如果文章中附图，则下载图片
		if (!empty($image_url)) {
			$image_data = $this->_downloader->download($image_url);
			if (strlen($image_data) != 0) {
				$image_name = md5($image_data) . IMAGE_EXT;
				$image_local_path = IMAGE_SAVE_PATH . $image_name;
				file_put_contents(IMAGE_SAVE_PATH . $image_name, $image_data);
				$article['image_local_path'] = $image_local_path;
			}
		}
		
        //提取新闻内容
		$content = '';
        foreach ( $doc->find('div.bodytext') as $element ) {
            foreach($element->find('p') as $ele) {
                $content = $content . $ele->plaintext . '<br /><br />';
            }
        }
        $article['content'] = $content;

		//新闻撰写作者
		$author = '';
		foreach($doc->find('p.name') as $element) {
			$author = $element->plaintext;
		}
        $article['author'] = trim($author);
		
        //提取新闻标题
        $title = '';
		foreach($doc->find('h1') as $element) {
			$title = $element->plaintext;
		}
        $article['title'] = $title;

		$category = '';
        foreach($doc->find('title') as $element) {
			$category = $element->plaintext;
			$pos = strrpos($category, '-');
			$category = substr($category, $pos + 1);
		}
        $article['category'] = trim($category);

        //新闻来源
        $article['source'] = 'BBC中文网';

        //新闻URL
		$news_url = '';
        foreach($doc->find('meta[property=og:url]') as $element) {
			$news_url = $element->content;
		}
        $article['news_url'] = $news_url;
		
		//提取新闻时间
		$date = '';
        foreach($doc->find('meta[name=dcterms.modified]') as $element) {
			$date = $element->content;
		}
		print $date . PHP_EOL;
		//$date = $this->format_time($date);
        $article['date'] = $date;
		
        return $article;
	}
	
	/**
	*	将文章插入数据库
	*/
    public function insert($article) {
		//print '插入数据库开始： ' . PHP_EOL;
        if (isset($article['title']) && strlen($article['title']) != 0) 
			$title = addslashes($article['title']);
		else { 
			print 'News title should not be NULL' . PHP_EOL;
			return;
		}
        if (isset($article['date']))
			$date = addslashes($article['date']);
        if (isset($article['source'])) 
			$source = addslashes($article['source']);
        if (isset($article['content'])) 
			$content = addslashes($article['content']);
        if (isset($article['category'])) 
			$category = addslashes($article['category']);
        if (isset($article['author'])) 
			$author = addslashes($article['author']);
        if (isset($article['news_url'])) 
			$news_url = addslashes($article['news_url']);
		if (isset($article['image_url'])) 
			$news_iamge_url = addslashes($article['image_url']);
		else
			$news_image_url = '';
		if (isset($article['image_local_path'])) 
			$news_image_local = addslashes($article['image_local_path']);
		else
			$news_image_local = '';
		
		$sql = "SELECT news_id FROM global_news WHERE news_title = '" .$title. "'";
        $mysqli = database::get_instance();
        if ($mysqli->query($sql)) {
			echo 'News already in DataBase, Ignored!' . PHP_EOL;
			return;
		}
		
		$sql = "INSERT INTO global_news 
		(news_title,news_date,news_source,news_content,news_author,news_category,news_url,news_image_url,news_image_local)
        VALUES('$title','$date','$source','$content','$author','$category','$news_url','$news_iamge_url','$news_image_local')";
        $result = $mysqli->insert($sql);
        if ($result) 
			echo 'Insert Success!' . PHP_EOL;
        else 
			echo "Insert Failed!" . PHP_EOL;
    }
	
	
	/**
     *   析构函数，
     */
    public function __deconstruct() {
        unset($this->_downloader);
    }
	
}


?>