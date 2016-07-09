<?php
// 逐个登录百度账号
require_once ("../../common/function.php");

$type = $_REQUEST['type'];

$accounts = array(
	'teswuvgikq18651@163.com' => 'chengdu9',
	'qlowejigoj2527@163.com' => 'dunuo054',
	'esluqtggqc60718@163.com' => 'guaifen4',
	'abyililmfn65073@163.com' => 'kuanghui',
	'fcbysldbdo30722@163.com' => 'miqin109',
	'umxaaehocv22204@163.com' => 'mizhui02',
	'psvzlyzadb5054@163.com' => 'zhangyon',
	'raflngqikk3002@163.com' => 'fenque10',
	'ztscxeuanl1189@163.com' => 'xiwei929',
	'eydldbwotc0814@163.com' => 'boou8837',
	'bbyqnrrgsl5363@163.com' => 'laoqiao3',
	'ckzxafgulw02938@163.com' => 'rangjing',
	'razmeqgrlt56348@163.com' => 'duxing36',
	'zesdepatxy34026@163.com' => 'yedi1425',
	'jkbgmdwgmh4450@163.com' => 'shalin24',
	'ladoxymmse50154@163.com' => 'zhifang3',
	'zpnekkeeic5157@163.com' => 'yiyi3150',
	'efayikzjtl31076@163.com' => 'zhanyou0',
	'cqxxvoyiou6850@163.com' => 'liangshi',
	'wsnpibhxyw4816@163.com' => 'menqiao2',
	'caxyczfbyp88538@163.com' => 'gengyan2',
	'acsiksjwgw62090@163.com' => 'zimen622',
	'xqbcrdycbr58577@163.com' => 'bulan387',
	'amtbrdppqj8324@163.com' => 'jianyao6',
	'aiftcniihd8870@163.com' => 'chuizhao',
	'oovjvrpdmx03883@163.com' => 'qinju030',
	'queucfmqbo4235@163.com' => 'jiangque',
	'oikaazznjx4021@163.com' => 'xianyong',
	'hxvqhgxzfi71196@163.com' => 'pakeng41',
	'ttsklwzyop66226@163.com' => 'mouji649',
	'bfoeocshlz37251@163.com' => 'kedao172',
	'dhaokgcmva1160@163.com' => 'mujing92',
	'oxxztevdgb23766@163.com' => 'xiafu836',
	'rjpvpcmqye1392@163.com' => 'anqian93',
	'zrvjzjlrtj95906@163.com' => 'jiaze750',
	'sukpnfqbbj60497@163.com' => 'bengfang',
	'iogsjjthtk6401@163.com' => 'jiaan441',
	'bwdphvqhid7610@163.com' => 'paoba372',
	'vfurfmjyzv79633@163.com' => 'tangmeng',
	'trekwxjxdm5586@163.com' => 'taolang3',
	'layoxikvlo56271@163.com' => 'shitao46',	
	'nhscnlksym99899@163.com' => 'qinshi70',
	'mrtkiifqqv2555@163.com' => 'hezi2568',
	'erolhsvare42773@163.com' => 'guangai2',
	'rnzhfylqnq39303@163.com' => 'bengju19',
	'davqlurckf3653@163.com' => 'gongnai1',
	'seqmsrkpxn82466@163.com' => 'yecan625',
	'csuiwnzbnl68658@163.com' => 'hegong68',
	'ulgbbojrtw97213@163.com' => 'tuiyong3',
	'aiirgbbcjm2864@163.com' => 'qiaokuan',
	'cjmwaviskn05618@163.com' => 'shuidou8',
	'eyzfhionvs6604@163.com' => 'xianlan4',
	'mqybbfnvlk6926@163.com' => 'yuhan203',
	'gifbzjxxkh64331@163.com' => 'wobai454',
	'xuloqrwuid62940@163.com' => 'ciyi4217',
);

// 连接redis服务器
$result = redis_conn();
if (!$result['status']) die(ret_output(1, $result['msg']));
$redis = $result['kv'];

$username = $redis->lpop('L:BAIDU_REFRESH_ACCOUNTS');
if(strlen($username) === 0) die('当前已经是最后一个了');

$login_data = array(
	"_client_id" => "wappc_1386816224047_167",
	"_client_type" => '1',
	"_client_version" => "6.0.1",
	"_phone_imei" => "a6ca20a897260bb1a1529d1276ee8176",
	"cuid" => "JC6737884997EF9AFE247470B6E960E4|165384710041368|com.baidu.tieba6.0.1",
	"model" => "M1",
	"un" => $username,
	"passwd" => base64_encode($accounts["$username"]),
	"isphone" => "0",
	'stErrorNums' => '0',
	'stMethod' => '1',
	'stMode' => '3',
	'stSize' => '91',
	'stTime' => '356',
	'stTimesNum' => '0',
	'from' => 'baidu_appstore',
	"timestamp" => strtotime('now') . rand(0, 9),
);

ksort($login_data);
$to_sign = "";
foreach ($login_data as $key => $value) {
	$to_sign .= "$key=$value";
}
$sign = strtoupper(md5($to_sign . "tiebaclient!!!"));
$login_data['sign'] = $sign;
$postdata = http_build_query($login_data);
$httpret = curl_httprequest("http://c.tieba.baidu.com/c/s/login", 1, $postdata);	
if (strpos($httpret[0], '"error_code":"0"') === false) {
	$redis->rpush('L:BAIDU_REFRESH_ACCOUNTS', $username);
	die;
}

$cookie = cut_str($httpret[0],'BDUSS":"','','|');

// 0:仅更新登录COOKIE	1:全部重置
if($type) {
	$res = $redis->hmset('H:BAIDU:'.$username, array('cookie' => $cookie, 'balance' => 1000));
	$redis->lPush('L:BAIDU_ACCOUNTS', $username);
	echo '['.$username.']<br/>['.$redis->hget('H:BAIDU:'.$username, 'cookie').']<br/>['.$redis->hget('H:BAIDU:'.$username, 'balance').']<br/>';
} else {
	$redis->hset('H:BAIDU:'.$username, 'cookie', $cookie);
	echo '['.$username.']<br/>['.$redis->hget('H:BAIDU:'.$username, 'cookie').']<br/>';
}

function redis_conn() {
    // $redis_host = "127.0.0.1";
    //$redis_pass = "user:password";
	$redis_host = "40ac1fd4e27511e4.m.cnhza.kvstore.aliyuncs.com";	
	$redis_pass = "40ac1fd4e27511e4:12345qwertQWERT";
    $redis = new Redis();
    if ($redis->connect($redis_host, 6379) == false) return array('status' => false, 'msg' => $redis->getLastError());
    if ($redis->auth($redis_pass) == false) return array('status' => false, 'msg' => $redis->getLastError());
    return array('status' => true, 'kv' => $redis);
}