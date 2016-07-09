<?php
// 获取QQ账户信息
require_once('function.php');

$qq_account = '1508992696';

// 获取加密信息
$httpret = curlResponse('http://check.ptlogin2.qq.com/check?pt_tea=1&uin='.$qq_account.'&appid=11000101');
$verify_info = explode("','", $httpret['content']);
$verify_code = $verify_info[1];
$verify_session = $verify_info[3];
$encryption = exec("\"C:\\Program Files\\nodejs\\node.exe\" C:\\workspace\\common\\QQ.js xml315xiao ".$verify_code.' '.$verify_info[2]);

// 提交登录
$postdata = 'u='.$qq_account.'&verifycode='.$verify_code.'&pt_vcode_v1=0&pt_verifysession_v1='.$verify_session.'&p='.$encryption.'&pt_randsalt=0&u1=http%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html&ptredirect=1&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=2-18-'.time().'&js_ver=10116&js_type=1&login_sig=&pt_uistyle=20&aid=11000101';
$httpret = curlResponse('http://ptlogin2.qq.com/login?'.$postdata);
$skey = cutstr($httpret['header'], 'skey=', '; ');
$httpret = curlResponse('https://www.tenpay.com/cgi-bin/v1.0/communitylogin.cgi?p_uin='.$qq_account.'&skey='.$skey.'&u1=https%3A%2F%2Fwww.tenpay.com%2Fcgi-bin%2Fv1.0%2Fcheck_balance.cgi');

// 获取账号信息
$httpret = curlResponse('https://www.tenpay.com/app/v1.0/account_info.cgi', 0, null, getCookies($httpret['header']), 0, 'https://www.tenpay.com/app/v1.0/cftaccount.cgi');
$account_info = $httpret['content'];
die($account_info);