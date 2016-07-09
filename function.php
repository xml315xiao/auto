<?php

/* *
 * 使用CURL模拟提交HTTP、HTTPS请求
 * @return array('code', 'header', 'content', 'encoding'); 返回状态码、响应头信息、内容
 */
function curlResponse($url, $method = false, $postdata = NULL, $cookie = '', $redirect = false, $referer = '', $useragent = '', $request_header = array(), $timeout = 30){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, max($timeout, 30));
	curl_setopt($ch, CURLOPT_POST, $method ?  true : false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $redirect ? true : false);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	if (isset($postdata) && !empty($postdata)) curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	if (isset($cookie) && strlen($cookie) > 0) curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	if (isset($referer) && strlen($referer) > 0) curl_setopt($ch, CURLOPT_REFERER, $referer);
	if (isset($useragent) && strlen($useragent) > 0) curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	if (isset($request_header) && !empty($request_header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $request_header);
	$output_stream = curl_exec($ch);
	$info = curl_getinfo($ch);
	$http_code = $info['http_code'];
	$header_size = $info['header_size'];
	$header = substr($output_stream, 0, $header_size);
	$content = substr($output_stream, $header_size);
	$encoding = mb_detect_encoding($content,array('UTF-8','ASCII','EUC-CN','CP936','BIG-5','GB2312','GBK'));
	if ($encoding && strcasecmp($encoding, 'UTF-8') !== 0) $content = @mb_convert_encoding($content,'UTF-8',array('ASCII','EUC-CN','CP936','BIG-5','GB2312','GBK'));
	curl_close($ch);
	
	return array('code' => $http_code, 'header' => $header, 'content' => $content, 'encoding' => $encoding);
}

/* * 
 * 获取Set-Cookie串
 * @return array | string 默认返回字符串
 */
function getCookies($response_header, $return_type = false){
	preg_match_all('/Set-Cookie: (.+)path=\//i', $response_header, $matches);
	if ($return_type){
		$cookies = array();
		foreach($matches[1] as $match){
			$name = strstr($match, '=', true);
			$value = ltrim(strstr($match, '='), '=');
			$cookies["$name"] = $value;
		}
		return $cookies;
	}
	return implode($matches[1]);
}

function getLocation($header){
	preg_match('/Location: (.+)/', $header, $match);
	return trim($match[1]);
}

/* *
 * 截取字符串
 */
function cutstr($haystack, $path, $format){
	switch($format){
		case 'xml' : 
			$obj = simplexml_load_string($haystack);
			return eval('return $obj->' . $path. ';');	
		case 'json' :
			$obj = json_decode($haystack);
			return eval('return $obj->' . $path . ';');
		default :
			if(strpos($haystack, $path) === FALSE) return '';
			return strstr(substr($haystack, strpos($haystack, $path) + strlen($path)), $format, true);	
	}
}

/* *
 * 获取表单内容
 */
function getFormData($content, $return_type = false, $out_charset = 'utf-8', $exclude_names = '', $exclude_types = "submit"){
	// if (strcasecmp($out_charset, 'utf-8') !== 0) $content = mb_convert_encoding($content, $out_charset, 'UTF-8');
	if (!preg_match_all('/<input.+>/iU', $content, $matches) || count($matches) === 0) return false;
    $exclude_types = explode('|', $exclude_types);
    $exclude_names = explode('|', $exclude_names);		
	$inputs = $matches[0];
	$params = array();
	foreach($inputs as $input){
		$input = str_replace('\"', '"', $input); // 广东移动特殊化
        if (!preg_match('/type *= *["\']{0,1}(?<m>[^"\' ]*)["\']{0,1}/i', $input, $matches)) continue;
        $type = $matches['m'];
        if (in_array($type, $exclude_types)) continue;
        if (!preg_match('/name *= *["\']{0,1}(?<m>[^"\' ]*)["\']{0,1}/i', $input, $matches)) continue;
        $name = $matches['m'];
		if (in_array($name, $exclude_names)) continue;
        $value = "";
        if (preg_match('/value *= *["\']{0,1}(?<m>[^"\']*)["\']{0,1}/i', $input, $matches)) {
			$value = html_entity_decode($matches['m']);
			if (strcasecmp($out_charset, 'utf-8') !== 0) $value = mb_convert_encoding($value, $out_charset, 'UTF-8');
		}
        $params["$name"] = $value;
	}
	if($return_type) return $params;
	return http_build_query($params);
}

/* *
 * 输出xml格式结果 (仅一维数组)
 */
function outputXML($datas){
	header('Content-type:application/xml;charset=utf-8');
	if(!is_array($datas)) return '<?xml version="1.0" encoding="utf-8"?><datas>'.htmlspecialchars($datas).'</datas>';
	$xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><datas></datas>');
	foreach($datas as $key => $value){
		$value = htmlspecialchars($value);
		$xml->addChild($key, $value);
	}
	return $xml->asXML();
}

/* *
 * 悠悠云验证码识别
 * 根据图片路径上传, 返回验证码在服务器的ID, 根据验证码ID获取识别结果
 * 常用1004, 1005, 1900,取值查看：http://www.uuwise.com/price.html
 */
function autoRecognition($imgage_url, $code_type = '1004', $cookie = '', $referer = '', $header = null){
	$image_result = curlResponse($imgage_url, false, null, $cookie, false, $referer, '', $header);
	$cookie.= getCookies($image_result['header']);
	$header = array(
		'SID: 90054', // $softID
		'HASH: 926ee2922dd3e92fae46eea0d2d5aef8', // $uhash = md5($softId.strtoupper($softKEY));
		'UUVersion: 1.1.1.3',
		'UID: 5737', // $_SESSION['uid']
		'User-Agent: 3e45ae3c17259ee850e5405d0cd4594d', // md5(strtoupper($softKEY.$userName)).$macAddress;
	);
	$userKey = "5737_JIEYITONG_C3-1D-53-B7-AF-D1-3C-93-A0-FE-B1-2A-37-B7-73-7D_6C-64-0F-C2-FF-97-AE-10-4D-15-F9-8D-14-91-72-18-57-68-DF-2F"; // $_SESSION['userKey']
	$postdata = array(
		"filename=\"C:/temp.png\"" => $image_result['content'], // 'img'=>'@'.realpath($saveImgPath),
		// 'img' => '@'.realpath("C:\\Users\\Yang\\Desktop\\test.jpg"),
		'key' => $userKey,
		'sid' => '90054', // $softID
		'skey' => 'a6f8caf5613ab9286e8e0c76097a2cff', // $softContentKEY=md5(strtolower($userKey.$softID.$softKEY));
		'Type' => $code_type,
		'GUID' => 'dd53c34ab76002d04abfe763482ac927',
		'Version' => 100,
	);
	
	$vcode = curlResponse('http://upload.uuwise.net:9000/Upload/Processing.aspx', 1, $postdata, null, 0, null, '', $header);
	$vcodeID = $vcode['content'];
	if (strpos($vcodeID, '|') !== false){
		$vcode_info = explode('|', $vcodeID);
		return array('vcode' => $vcode_info[1], 'cookie' => $cookie);
	}
	
	do{
		$result = curlResponse('http://upload.uuwise.net:9000/Upload/GetResult.aspx?KEY='.$userKey.'&ID='.$vcodeID.'&Random='.mktime(time()));
		usleep(100000);
	}while($result['content'] == '-3' || strlen($result['content']) === 0);	
	
	return array('vcode' => $result['content'], 'cookie' => $cookie);
}

/* *
 * 获取命令行参数
 */
function getArgument($argument){
	$temp = explode('&', $temp);
	foreach($temp as $val){
		$val = explode('=',$val);
		$name = $val[0];
		$value = $val[1];
		$_GET["$name"] = $value;
	}
}
?>