<?php 
define('IMAGE_SAVE_PATH', 'images/');
define('IMAGE_EXT', '.jpg');

require('lib/crawler/nytimes.class.php');
require('lib/crawler/forbes.class.php');
require('lib/crawler/tfchinese.class.php');
require('lib/crawler/bbc.class.php');



$crawler = '';
$url_seed_filename = '';
$fp = '';

//��¼��ʼ����ʱ��
$start_time= microtime(true);


//=====ŦԼʱ��===============
$crawler = new NYtimesCrawler();
$filename = 'urlseed/nytimes.txt';
$fp = fopen($filename,'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //��ֹ����
	echo '��ʼ��ȡ���URL��' . $link;
    $crawler->start($link);
}

//=====����˹������===============
$crawler = new ForbesChinaCrawler();
$url_seed_filename = 'urlseed/forbeschina.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //��ֹ����
	echo '��ʼ��ȡ���URL��' . $link . PHP_EOL;
    $crawler->start($link);
}

//=====Ӣ������ʱ��===============
$crawler = new FTChineseCrawler();
$url_seed_filename = 'urlseed/ftchinese.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //��ֹ����
	echo '��ʼ��ȡ���URL��' . $link . PHP_EOL;
    $crawler->start($link);
}

/*
//=====BBC������===============
$crawler = new BBCCrawler();
$url_seed_filename = 'urlseed/bbc.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(strlen($link) < 0) continue; //��ֹ����
	echo '��ʼ��ȡ���URL��' . $link . PHP_EOL;
    $crawler->start($link);
}
*/

/*

*/
//��¼��������ʱ��
$finish_time = microtime(true);

echo '����ִ����ɣ�����ʱ��' . ($finish_time - $start_time) . '�룡';
//$crawler->start('http://cn.nytimes.com/entrepreneurs/');





?>