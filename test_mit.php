<?php

require('lib/crawler/mit.class.php');

//=====MIT科技评论===============
$crawler = new MITCrawler();
$url_seed_filename = 'urlseed/mit.txt';
$fp = fopen($url_seed_filename, 'r');

while (!feof($fp)) {
    $link = fgets($fp);
	//防止空行
	if(empty($link)) continue; 
	echo 'Starting URL: ' . $link . PHP_EOL;
    $crawler->start($link);
}


?>