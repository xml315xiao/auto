<?php
require_once('function.php');

$date = Date('Y-m-d', mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
$cookies = 'JSESSIONID=BAE6BAD84803EDB011FD351DCB11F10B; memkey=2609E5A3E22DB6FD6F183D5E08449A26';
$postdata = 'Sys_MemCached_Key=2609E5A3E22DB6FD6F183D5E08449A26&begindate='.$date.'&enddate='.$date.'&iDisplayStart=0&iDisplayLength=20&nowPage=0&isPage=true&count=';
$httpret = curlResponse('http://bj.lianlian.com/acct/serverSearch.do', 1, $postdata, $cookies);
$total_count = cutstr($httpret['content'], '<input type="hidden" id="count" name="count" value="', '"/>');
$total_pages = ceil(bcdiv($total_count, 20, 2));
die($total_pages);

