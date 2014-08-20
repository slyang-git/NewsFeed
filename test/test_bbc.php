<?php

require('lib\crawler\bbc.class.php');

//=====BBC中文网===============
$crawler = new BBCCrawler();
$url_seed_filename = 'urlseed/bbc.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	if(empty($link)) continue; //防止空行
	echo '开始爬取入口URL：' . $link . PHP_EOL;
    $crawler->start($link);
}


?>