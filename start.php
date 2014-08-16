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

//记录开始运行时间
$start_time= microtime(true);


//=====纽约时报===============
$crawler = new NYtimesCrawler();
$filename = 'urlseed/nytimes.txt';
$fp = fopen($filename,'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //防止空行
	echo '开始爬取入口URL：' . $link;
    $crawler->start($link);
}

//=====福布斯中文网===============
$crawler = new ForbesChinaCrawler();
$url_seed_filename = 'urlseed/forbeschina.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //防止空行
	echo '开始爬取入口URL：' . $link . PHP_EOL;
    $crawler->start($link);
}

//=====英国金融时报===============
$crawler = new FTChineseCrawler();
$url_seed_filename = 'urlseed/ftchinese.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //防止空行
	echo '开始爬取入口URL：' . $link . PHP_EOL;
    $crawler->start($link);
}

/*
//=====BBC中文网===============
$crawler = new BBCCrawler();
$url_seed_filename = 'urlseed/bbc.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(strlen($link) < 0) continue; //防止空行
	echo '开始爬取入口URL：' . $link . PHP_EOL;
    $crawler->start($link);
}
*/

/*

*/
//记录结束运行时间
$finish_time = microtime(true);

echo '程序执行完成，共耗时：' . ($finish_time - $start_time) . '秒！';
//$crawler->start('http://cn.nytimes.com/entrepreneurs/');





?>