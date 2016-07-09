<?php

require_once('function.php');

$cookies = 'JSESSIONID=BAE6BAD84803EDB011FD351DCB11F10B; memkey=2609E5A3E22DB6FD6F183D5E08449A26';
$date = Date('Y-m-d', mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
$filename = 'fundlist_'. Date('Ymd', mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))) .'.txt';
$current_page = $_GET['page'];
if ($current_page == 1) @unlink($filename);
$file = @fopen($filename, 'a+b');
$fundlist = array();

$postdata = 'Sys_MemCached_Key=2609E5A3E22DB6FD6F183D5E08449A26&begindate='.$date.'&enddate='.$date.'&iDisplayStart='.bcmul(($current_page - 1), 20).'&iDisplayLength=20&nowPage=0&isPage=true&count=';
$httpret = curlResponse('http://bj.lianlian.com/acct/serverSearch.do', 1, $postdata, $cookies);
if ($current_page == 1) {
	$total_count = cutstr($httpret['content'], '<input type="hidden" id="count" name="count" value="', '"/>');
	fwrite($file, "-----------------------------------------------------------------------------------------------------\r\n");
	fwrite($file, "交易总笔数：$total_count								交易日期：$date\r\n");
	fwrite($file, "-----------------------------------------------------------------------------------------------------\r\n");
	fwrite($file, "交易流水号	  创建时间		业务类型	    钱包	交易额	交易前余额 交易后余额\r\n");
	fwrite($file, "-----------------------------------------------------------------------------------------------------\r\n");
}
$tbody = cutstr($httpret['content'], '<tbody id="tbody">', '</tbody>');
preg_match_all('/<tr>.*?<\/tr>/is', $tbody, $matches);

if(empty($matches[0])) die('Error');
foreach($matches[0] as $match){
	preg_match_all('/(<td>|<li>)(?<m>[^<]*)/i', $match, $list);	
	$fundlist[] = implode(',  ', $list['m']);
}

$content = implode("\r\n", $fundlist)."\r\n";
fwrite($file, $content);

fclose($file);
die('OK');