<?php
// 163邮箱注册机
require_once('function.php');
require_once('database_fun.php');

set_time_limit(0);

// 注册页面初始化
$httpret = curlResponse('http://reg.email.163.com/unireg/call.do?cmd=register.entrance&from=163mail_right');
$jsessionid = cutstr($httpret['header'], 'JSESSIONID=', '; Path=');
$envalue = cutstr($httpret['content'], 'envalue : "', '",');
$cookies = getCookies($httpret['header']);

// 保存会话
$url = cutstr($httpret['content'], '<iframe id="submitFrame" name="submitFrame" style="display:none;" src="', '"></iframe>');
$httpret = curlResponse($url, 0, null, $cookies);

// 校验用户名
do{
	$init_account = getAccount();
	$username = $init_account['username'];
	$password = $init_account['password'];
	$httpret = curlResponse('http://reg.email.163.com/unireg/call.do?cmd=urs.checkName', 1, 'name='.$username, $cookies);
	$checkinfo = json_decode($httpret['content'], true);
}while(!isset($checkinfo['result']['163.com']) || strcmp($checkinfo['result']['163.com'], 1) !== 0);

// 获取验证码
exec("\"C:\\Program Files\\nodejs\\node.exe\" C:\\workspace\\common\\10086\\getEnv.js ".$envalue, $result);
$url = 'http://reg.email.163.com/unireg/call.do?cmd=register.verifyCode&v=common/verifycode/vc_en&env='.$result[0].'&t='.$result[1];
$vcodeinfo = autoRecognition($url, 1106, $cookies);

// 提交注册
$postdata = 'name='.$username.'&flow=main&uid='.$username.'%40163.com&password='.$password.'&confirmPassword='.$password.'&mobile=&vcode='.$vcodeinfo['vcode'].'&from=163mail_right';
$httpret = curlResponse('https://ssl.mail.163.com/regall/unireg/call.do;jsessionid='.$jsessionid.'?cmd=register.start', 1, $postdata, $cookies);

// 再次校验
$envalue = cutstr($httpret['content'], '"envalue" : "', '",');
$suspendId = cutstr($httpret['content'], '"suspendId" : "', '",');
if(strlen($envalue) === 0) die("Error".$httpret['content']);

sleep(2);
$index = 0;
do{
	exec("\"C:\\Program Files\\nodejs\\node.exe\" C:\\workspace\\common\\10086\\getEnv.js ".$envalue, $result);
	$resume_info = autoRecognition('http://reg.email.163.com/unireg/call.do?cmd=register.verifyCode&v=common/verifycode/vc_zh&vt=grey&env='.$result[0].'&t='.$result[1], 2006, $cookies);
	$httpret = curlResponse('http://reg.email.163.com/unireg/call.do?cmd=register.resume', 1, 'vcode='.$resume_info['vcode'].'&uid='.$username.'%40163.com&suspendId='.$suspendId, $cookies);
}while(++$index < 3 && strcmp($httpret['content'], '{"code":200}') !== 0);

if(strcmp($httpret['content'], '{"code":200}') !== 0){
	@file_put_contents('../Accounts/163_error.txt', "[$username][$password][$envalue][$suspendId][$cookies]\r\n", FILE_APPEND);
	die('Resume Check Fail');
}

// 保存账号与密码至文本
@mkdir('../Accounts/');
@file_put_contents('../Accounts/163mails.txt', "'{$username}' => '{$password}',\r\n", FILE_APPEND);

// 保存账号密码在Redis
/*$redis = redis_conn();
if (!$redis['status']) die('连接Redis失败');
$redis = $redis['con'];
$redis->select(1);

$redis->hmset('H:163mails', array('username' => $username, 'password' => $password));
$redis->lpush('L:163mails', $username);
*/
die('OK');

// 随机生成用户名
function getAccount(){
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*';
	$username = '';
	$password = '';
	for($i = 0;$i < 15; $i++){
		$index = $i < 10 ? rand(0, 25) : rand(51, 61);
		$username.= $chars["$index"];
		if ($i < 8) {
			$index = rand(0, 69);
			$password.= $chars["$index"];
		}
	}
	return array('username' => $username, 'password' => $password);
}





