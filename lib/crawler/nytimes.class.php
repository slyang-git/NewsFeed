<?php 
/**
*	Description��ŦԼʱ��������http://cn.nytimes.com/�������
*	��ÿһ�����URL�����Ƚ��������е������������ӵ�ַ���ٽ���Щ���µ�ַ������أ�
*	���������е���Ҫ�ֶΣ������ű��⣬����ʱ�䣬�������ĵ�
*	Created Date��2014.8.6 ��
* 	Modified Date�� 2014.8.7,2014.8.8
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

class NYtimesCrawler extends crawler{

	private $_downloader = null;
	private $_url = null;
	private $_url_history_file_path;
	/**
	*	���캯����������ʷurl���ݣ�����ȡurl�ļ���
	*/
	public function __construct() {
		//�����ҳ������ʵ��
		$this->_downloader = downloader::get_instance();
		$this->_url = new url();
		$this->_url_history = 'urllog/nytimes_url.txt';
        $this->_url->load_url_history($this->_url_history);
	}

	 /**
	 * �����������У�urlΪ��ڵ�ַ
	 */
	public function start($url) {
        $html = $this->_downloader->download($url);
        if (!($links = $this->extract_links($html))) {
			print '������У�δ��ȡ��URL' . PHP_EOL;
			return;
		}
		
        foreach ($links as $link) {
            if ($this->_url->is_fetched($link)) {
                echo '����һ���ظ�URL' . PHP_EOL;
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
		$doc = str_get_html($html);
        if (!$doc) return false;
        $links = array();
		foreach($doc->find('a') as $element) {
			if (isset($element->title) 
			&& ($element->parent()->class == 'referListHeadline'
			|| $element->parent()->class == 'regularSummaryHeadline'
			|| $element->parent()->tag == 'li')) {
				if (substr($element->href,0,4) != 'http') {
					$url = 'http://cn.nytimes.com' . $element->href;
				} else {
					$url = $element->href;
				}
                if ( $this->_url->is_fetched($url)) {
                    echo '����һ������ȡURL' . PHP_EOL;
                    continue;
                }
                array_push($links, $url);
			}
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
		foreach ( $doc->find('meta[property=og:image]') as $element ) {
            $image_url = $element->content;
        }
        $article['image_url'] = $image_url;
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
        foreach ( $doc->find('div.content') as $element ) {
            foreach($element->find('p.paragraph') as $ele) {
                $content = $content . $ele->plaintext . '<br /><br />';
            }
        }
        $article['content'] = $content;

        //��ȡ����ʱ��
        $date = '';
        foreach ($doc->find('meta[property=og:article:published_time]') as $element) {
            $date = $element->content;
        }
        $article['date'] = $date;

        //��ȡ���ű���
        $title = '';
        foreach ($doc->find('h3.articleHeadline') as $element) {
            $title = $element->plaintext;
        }
        $article['title'] = $title;

        //�����������
        $category = '';
        foreach ($doc->find('meta[property=og:article:section]') as $element) {
            $category = $element->content;
        }
        $article['category'] = $category;

        //����׫д����
        $author = '';
        foreach($doc->find('meta[property=og:article:author]') as $element ) {
            $author = $element->content;
        }
        $article['author'] = $author;

        //������Դ
        $article['source'] = iconv('GBK','UTF-8','ŦԼʱ��������');

        //����URL
        $news_url = '';
        foreach($doc->find('meta[name=twitter:url]') as $element ) {
            $news_url = $element->content;
        }
        $article['news_url'] = $news_url;
        return $article;
	}


	/**
	*	�����²������ݿ�
	*/
    public function insert($article) {
        if (isset($article['title']) && strlen($article['title']) != 0) 
			$title = addslashes($article['title']);
		else 
			return;
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
		
		$sql = "SELECT news_id FROM global_news WHERE news_title = '".$title."' AND news_date = '". $date."'";
        $mysqli = database::get_instance();
        if ($mysqli->query($sql)) {
			echo '�������ݿ��Ѵ��ڸ����ţ������ѱ����ԣ�' . PHP_EOL;
			return;
		}
		
		$sql = "INSERT INTO global_news 
		(news_title,news_date,news_source,news_content,news_author,news_category,news_url,news_image_url,news_image_local)
        VALUES('$title','$date','$source','$content','$author','$category','$news_url','$news_iamge_url','$news_image_local')";
        $result = $mysqli->insert($sql);
        if ($result) echo '���ݲ���ɹ���' . PHP_EOL;
            else echo "���ݲ���ʧ��!" . PHP_EOL;
    }
	
	/**
     *   ����������
     */
    public function __deconstruct() {
        unset($this->_downloader);
    }
}


?>