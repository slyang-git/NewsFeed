<?php 
/**
*	Description������˹������http://www.forbeschina.com/�������
*	��ÿһ�����URL�����Ƚ��������е������������ӵ�ַ���ٽ���Щ���µ�ַ������أ�
*	���������е���Ҫ�ֶΣ������ű��⣬����ʱ�䣬�������ĵ�
*	Created Date��2014-08-09 13:39
* 	Modified Date�� 2014-08-10 15:15
*	Author����˫�� slyang@aliyun.com
* 	
**/

require_once('lib/simple_html_dom.php');
require_once('lib/url/url.class.php');
require_once('lib/database/database.class.php');
require_once('lib/crawler/crawler.class.php');
require_once('lib/downloader.class.php');

defined('IMAGE_SAVE_PATH') or define('IMAGE_SAVE_PATH', 'images/');
defined('IMAGE_EXT') or define('IMAGE_EXT', '.jpg');

class ForbesChinaCrawler extends crawler {
	private $_downloader = null;
	private $_url = null;
	private $_url_history;
	
	/**
	*	���캯����������ʷurl���ݣ�����ȡurl�ļ���
	*/
	public function __construct() {
		//�����ҳ������ʵ��
		$this->_downloader = downloader::get_instance();
		$this->_url = new url();
		$this->_url_history = 'urllog/forbes_url.txt';
		$this->_url->load_url_history($this->_url_history);
	}

	 /**
	 * �����������У�urlΪ��ڵ�ַ
	 */
	public function start($url) {
        $html = $this->_downloader->download($url);
		//$links = array();
        if (!($links = $this->extract_links($html))) {
			print '������У�δ��ȡ��URL' . PHP_EOL;
			return;
		}
		
		//print '��ʼ�������� ' . PHP_EOL;
        foreach ($links as $link) {
			
            if ($this->_url->is_fetched($link)) {
                print '����һ���ظ�URL ' . PHP_EOL;
                continue;
            }
            $html = $this->_downloader->download($link);
            if (!$html) continue;
            $article = array();
            if(strlen($html) != 0)
                $article = $this->extract_content($html);
            //var_dump($article);
            $this->insert($article);
			$this->_url->push($link);
        }
		
        $this->_url->save_url_history($this->_url_history);
		
	}

    /**
	 * ��ȡҳ���е����ӵ�ַ
     * @param $html
     * @return array
     */
    public function extract_links($html) {
        $links = array();
		$pattern = '{http://www\.forbeschina\.com/review/\\d{6,}/\\d*\.shtml}';
		$matches = array();
		preg_match_all($pattern, $html, $matches);
		echo '��ȡ��������������' . count($matches[0]) . PHP_EOL;
		
		foreach($matches[0] as $link) {
			if ( $this->_url->is_fetched($link) ) {
                echo '����һ������ȡURL' . PHP_EOL;
                continue;
            }
            array_push($links, $link);
		}
		return $links;
	}

	/**
	*	������ҳ����
	*/
	public function extract_content($html) {
		$doc = str_get_html($html);
        $article = array();
		
		//��ȡ����ͼƬ��ַ
		$image_url = '';
		foreach ($doc->find('div.p_detail') as $element) {
			foreach ( $element->find('img[alt=""]') as $ele ) {
				$image_url = 'http://www.forbeschina.com' . $ele->src;
			}
			$article['image_url'] = $image_url;
		}
		
		//echo $image_url . PHP_EOL;
		//��������и�ͼ��������ͼƬ
		if (strlen($image_url) != 0) {
			$image_data = $this->_downloader->download($image_url);
			if (strlen($image_data) != 0) {
				$image_name = md5($image_data) . IMAGE_EXT;
				$image_local_path = IMAGE_SAVE_PATH . $image_name;
				file_put_contents(IMAGE_SAVE_PATH . $image_name, $image_data);
				$article['image_local_path'] = $image_local_path;
			}
		}

        //��ȡ��������
		$content = '';
        foreach ( $doc->find('div.p_detail') as $element ) {
            foreach($element->find('p') as $ele) {
                $content = $content . $ele->plaintext . '<br /><br />';
            }
        }
        $article['content'] = $content;

        //��ȡ����ʱ��
        $date = '';
        foreach ($doc->find('h6.p_message') as $element) {
            $date = $element->plaintext;
        }
		$date = $this->format_time($date);
		//echo $date . PHP_EOL;
        $article['date'] = $date;

		//����׫д����
        $author = '';
        foreach($doc->find('div.message') as $element ) {
			$author = $element->find('p.p_message',0)->plaintext;
        }
        $article['author'] = $author;
		
        //��ȡ���ű���
        $title = '';
        foreach ($doc->find('h1#article_title') as $element) {
            $title = $element->plaintext;
        }
        $article['title'] = $title;

        //�����������
        $category = '';
        foreach ($doc->find('input#ga_url0') as $element) {
            $category = $element->value;
        }
        $article['category'] = $category;

        //������Դ
        $article['source'] = iconv('GBK','UTF-8','����˹������');

        //����URL
        $news_url = '';
        foreach($doc->find('link[rel=canonical]') as $element ) {
            $news_url = $element->href;
        }
        $article['news_url'] = $news_url;
		
        return $article;
	}

	public function format_time($time) {
		$year = iconv('GBK','UTF-8','��');
		$month = iconv('GBK','UTF-8','��');
		$day = iconv('GBK','UTF-8','��');
		$delim = array($year,$month);
		$time = str_replace($day,'',str_replace($delim,'-',$time));
		//$time = str_replace($year,'-',$time);
		//$time = str_replace($month,'-',$time);
		return $time;
	}
	
	/**
	*	�����²������ݿ�
	*/
    public function insert($article) {
		//print '�������ݿ⿪ʼ�� ' . PHP_EOL;
        if (isset($article['title']) && strlen($article['title']) != 0) 
			$title = addslashes($article['title']);
		else { 
			print '���ű��ⲻ��Ϊ�գ�' . PHP_EOL;
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
		
		$sql = "SELECT news_id FROM global_news WHERE news_title = '" .$title. "' AND news_date = '". $date ."'";
        $mysqli = database::get_instance();
        if ($mysqli->query($sql)) {
			echo '�������ݿ��Ѵ��ڸ����ţ������ѱ����ԣ�' . PHP_EOL;
			return;
		}
		
		$sql = "INSERT INTO global_news 
		(news_title,news_date,news_source,news_content,news_author,news_category,news_url,news_image_url,news_image_local)
        VALUES('$title','$date','$source','$content','$author','$category','$news_url','$news_iamge_url','$news_image_local')";
        $result = $mysqli->insert($sql);
        if ($result) 
			echo '���ݲ���ɹ���' . PHP_EOL;
        else 
			echo "���ݲ���ʧ��!" . PHP_EOL;
    }
	
	
	/**
     *   ����������
     */
    public function __deconstruct() {
        unset($this->_downloader);
    }
	
}


?>