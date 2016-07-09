<?php
// 百度注册
require_once('../MAGIC/function.php');
// header('Content-type: text/plain;');

// 获取token与codeString
$httpret = curlResponse('https://passport.baidu.com/v2/?reg');
$cookies = getCookies($httpret['header']);
$httpret = curlResponse('https://passport.baidu.com/v2/api/?getapi&tpl=&apiver=v3&tt=&class=reg&app=&callback=bd__cbs__ef90nx', 0, null, $cookies);
$codestring = cutstr($httpret['content'], '"codeString" : "', '", "token" : "');
$token = cutstr($httpret['content'], '"token" : "', '", "cookie"');

// 获取验证码
$httpret = curlResponse('https://passport.baidu.com/v2/?reggetcodestr&token='.$token.'&tpl=&apiver=v3&tt=&app=&callback=bd__cbs__u01d58 ');
$verifystr = cutstr($httpret['content'], '"verifyStr" : "', '", "verifySign"');
$verify = autoRecognition('https://passport.baidu.com/cgi-bin/genimage?'.$verifystr, '1004', $cookies);
$vcode = $verify['vcode'];

echo $vcode;
echo "\n\n";

$username = 'xlwoszfemu15373';
$password = 'm4NH6atH';
$postdata = 'staticpage=https%3A%2F%2Fpassport.baidu.com%2Fstatic%2Fpasspc-account%2Fhtml%2Fv3Jump.html&charset=UTF-8&registerType=1&verifypass='.$password.'&token='.$token
			.'&tpl=&subpro=&apiver=v3&tt=1432533454577&retu=&u=&quick_user=0&regmerge=true&suggestIndex=&suggestType=&codestring='.$codestring
			.'&app=&pass_reg_suggestuserradio_0=&islowpwdcheck=undefined&logRegType=pc_regBasic&isexchangeable=1&exchange=0&sloc=loaded%23%23%23247%23469%231432532576117%23%231432533454574%40email%23%23%23%23%23%23%23%40userName%23%23%23%23%23%23%23%40phone%23%23%23%23%23%23%23%40smscode%23%23%23%23%23%23%23%40verifyCode%23156%2316%2323.5%2317%231432533424203%231432533429634%231432533432486%40password%23328%2316%2376.5%2321%231432533414592%231432533422881%231432533417451%40submit%23350%2350%23135.5%2332%231432533454396%23%23%40'
			.'&account='.$username.'%40163.com&loginpass=m4NH6atH&verifycode='.$vcode.'&isagree=on&ppui_regtime=878656&email='.$username.'%40163.com&callback=parent.bd__pcbs__pu8bb1';

$httpret = curlResponse('https://passport.baidu.com/v2/api/?reg', 1, $postdata, $cookies);
print_r($httpret['content']);