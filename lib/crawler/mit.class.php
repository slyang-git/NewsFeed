<?php 
/**
*	Description：MIT科技评论网http://www.techreviewchina.com/爬虫程序；
*	对每一个入口URL，首先解析出其中的所有文章链接地址，再将这些文章地址逐个下载，
*	解析出其中的重要字段，如新闻标题，发布时间，新闻正文等
*	Created Date：2014-08-27 15:55
* 	Modified Date： 
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

class MITCrawler extends crawler {
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
		$this->_url_history = 'urllog/mit_url.txt';
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
    
            if(!empty($html)) {
                $article = $this->extract_content($html);
				$article['news_url'] = $link;
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
		$pattern = '{/home/article/detail/id/\\d+.html}';
		preg_match_all($pattern, $html, $matches);
		//print_r($matches);
		echo 'Extracted URLs: ' . count($matches[0]) . PHP_EOL;
		foreach($matches[0] as $link) {
			if ( $this->_url->is_fetched($link) ) {
                echo 'Ignored an URL' . PHP_EOL;
                continue;
            }
			$link = 'http://www.techreviewchina.com' . $link;
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
		
        $article = array();
		
		//提取新闻图片地址
		$image_url = 'www.techreviewchina.com';
		$element = $doc->find('div.content', 0)->find('img',0);
		if (isset($element)) {
			$image_url .= $element->src;
			$article['image_url'] = $image_url;
		} else 
			$article['image_url'] = '';
		//print $image_url . PHP_EOL;
		
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
        foreach ( $doc->find('div.detail') as $element ) {
            foreach($element->find('p') as $ele) {
				if (!empty($ele->plaintext)) 
					$content .= trim($ele->plaintext) . '<br /><br />';
            }
        }
        $article['content'] = trim($content);
		//print $content;
		
		//新闻撰写作者
		$element = $doc->find('p.p1',0);
		if (!empty($element)) {
			$author = $element->plaintext;
		} else {
			$author = '佚名';
		}
        $article['author'] = trim($author);
		
        //提取新闻标题
        $title = '';
		foreach($doc->find('title') as $element) {
			$title = $element->plaintext;
		}
        $article['title'] = trim($title);

		//新闻类别
		$category = $doc->find('h2', 0)->plaintext;
        $article['category'] = $category;

        //新闻来源
        $article['source'] = 'MIT科技评论';
		
		//提取新闻时间
		$element = $doc->find('div.text', 0)->find('em', 0);
		$date = $element->plaintext;
		//print $date . PHP_EOL;
		$date = $this->format_time($date);
        $article['date'] = $date;
		//print $date . PHP_EOL;
		
        return $article;
	}
	
	public function format_time($date) {
		$date = str_replace('发表于 ', '', $date);
		return trim($date);
		//print_r($matches);
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