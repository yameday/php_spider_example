<?php

##計算耗時FUNCTION
function caclutime()
{ 
	$time = explode( " ", microtime()); 
	$usec = (double)$time[0]; 
	$sec = (double)$time[1]; 
	return $sec + $usec; 
}

##utf8_字串切割
function utf8_str_split($str, $split_len = 1)
{
	if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
		return FALSE;

	$len = mb_strlen($str, 'UTF-8');
	if ($len <= $split_len)
		return array($str);

	preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);

	return $ar[0];
}

function curl_requset($url, $custom_header, $referer_url, $post_data, $cookie_content, $http_sockopen_timeout, $http_curlopt_timeout, $_debug) 
{  
	if ($http_sockopen_timeout == "") $http_sockopen_timeout=30; 
	if ($http_curlopt_timeout == "") $http_curlopt_timeout=30; 
	if (!is_array($url)) {
		$url_info = parse_url($url);
	}
	else {
		$url_info = parse_url($url[0]);      
	}

	if (!$url_info["host"]) { $url_info["host"] = $_SERVER["HTTP_HOST"]; }
	if (!$url_info["scheme"]) { $url_info["scheme"] = "http"; }
	if (!$url_info["path"]) { return -1; }
	if ($url_info["scheme"] == "https") { $ssl_str="ssl://"; $port=443; }
	$HTTP_USER_AGENT="Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36";

	#SET HEADER
	if (!$custom_header) {
		$header[] = "Cache-Control: no-cache";
		$header[] = "Pragma: no-cache";
	} else {
		$header = $custom_header;

	}	


	$curl_rs=curl_init();	
	if (!$curl_rs) { return "connect failed\n"; }
	curl_setopt($curl_rs, CURLOPT_URL, $url);
	curl_setopt($curl_rs, CURLOPT_USERAGENT, $HTTP_USER_AGENT);	
	curl_setopt($curl_rs, CURLOPT_RETURNTRANSFER, true);	
	curl_setopt($curl_rs, CURLOPT_HTTPHEADER, $header);	 

	if ($referer_url != "") {
		curl_setopt($curl_rs, CURLOPT_REFERER , $referer_url );
	}	

	if ($post_data) {		
		curl_setopt($curl_rs, CURLOPT_POST, 1);
		curl_setopt($curl_rs, CURLOPT_POSTFIELDS, $post_data);	      
		//curl_setopt($curl_rs, CURLOPT_COOKIEJAR, 'cookies.txt');		
		//curl_setopt($curl_rs, CURLOPT_COOKIEFILE, 'cookies.txt');
	}
	
	if ($cookie_content) {			
		curl_setopt($curl_rs, CURLOPT_COOKIEJAR, 'cookies.txt');		
		curl_setopt($curl_rs, CURLOPT_COOKIEFILE, 'cookies.txt');
		//curl_setopt($curl_rs, CURLOPT_COOKIE, $cookie_content);
	}
	curl_setopt($curl_rs, CURLOPT_TIMEOUT, $http_curlopt_timeout);
	

	$curl_result=curl_exec($curl_rs);

	if (curl_errno($curl_rs)) { 
		echo  "Error: " . curl_error($curl_rs)."\n"; 
		//curl_close($curl_rs);
		return $out;
	} else { 
		// Show me the result 
		if ($_debug) {
			$information=curl_getinfo($curl_rs);
			$out["header"]=$information;
		}		

		curl_close($curl_rs); 
		$out["body"]=$curl_result;
	}

	return $out;
}

function get_pchome_search($keyword) 
{ 
	global $include_path, $company_data;

	$t=time();
	$keyword = urlencode($keyword);
	$url_pchome = $company_data[1]["url"]; //config.php
	$get_str="q=".$keyword."&page=1&sort=rnk/dc";
	$referer="http://ecshweb.pchome.com.tw/search/v3.3/?q=".$keyword;

	$http_url = $url_pchome.$get_str;
	$cookies='ECC=9805f4ea2c4b3532ef33e2eb1e09154de911e125.1508915548; U=2cf01c2872c38d047a5bddec05bc4856cd970305; uuid=983463b2-6938-4689-a5c1-0bb2595fdb11; HistoryEC=%7B%22P%22%3A%5B%7B%22Id%22%3A%22DGBJ88-A9006P390%22%2C%20%22M%22%3A1509076711%7D%2C%20%7B%22Id%22%3A%22DGBJ88-A9008ECJJ%22%2C%20%22M%22%3A1509067128%7D%5D%2C%20%22T%22%3A1%7D; ECWEBSESS=60e516e330.de879134777dc3f7dab3157dbbb42b6eba097a1a.$t';
	

	$header =array(		
		
		'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
		/*'Accept-Encoding:gzip, deflate',	*/
		'Host: ecshweb.pchome.com.tw',
		'Cache-Control: no-cache',		
		'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
		'Connection: keep-alive',
		'Pragma:no-cache',
		'Upgrade-Insecure-Requests:1',
		'X-Requested-With: XMLHttpRequest'
	);		
	
	$ret_pchome = curl_requset($http_url, $header, $referer, "", $cookies, "", "", 0);//pchome

	
	$pchome_arr=array();
	$curl_result_json=json_decode($ret_pchome["body"],1);   
	if ($curl_result_json["totalPage"] < 1) {

	} else {
		$count=0;
		foreach ( $curl_result_json["prods"] as $k => $v ) {
			$pchome_arr[$k]["id"]=$v["Id"];
			$pchome_arr[$k]["pic"]=$v["picS"];
			$pchome_arr[$k]["name"]=$v["name"];
			$pchome_arr[$k]["price"]=$v["price"];
			$pchome_arr[$k]["describe"]=$v["describe"];
			$count++;				
		}
	}     

	$out=$pchome_arr;

	return $out;
}

function get_momo_search($keyword) 
{ 
	global $include_path, $company_data;

	$t=time();
	$keyword_urlencode = urlencode($keyword);
	$url_momo = $company_data[2]["url"]; //config.php
	$get_str = "?n=6002&t=".$t;	
	$referer="https://www.momoshop.com.tw/search/searchShop.jsp?keyword=".$keyword_urlencode."&searchType=1&curPage=1&_isFuzzy=0";

	$http_url = $url_momo.$get_str;

	$header =array(
		/*'Content-Encoding: gzip',*/
		'Accept: application/json, text/javascript, */*',
		/*'Accept-Encoding:gzip, deflate, br',	*/
		'Host: http://www.momoshop.com.tw',
		'Origin: https://www.momoshop.com.tw',		
		'Content-Type: application/x-www-form-urlencoded', 		
		'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
		'Connection:keep-alive',
		'X-Requested-With: XMLHttpRequest'
	);	


	//$keyword="米森 蔓越莓麥片";	 //TEST用
	$str_arr = utf8_str_split($keyword);//字串切割

	$uri_str='';
	//轉換為&# XXXXX 再做URLENCODE
	foreach ($str_arr as $v) {		
		//echo strlen($v)."\n"; //TEST用
		$unicode_html1=$v;
		if (strlen($v)==3) {
			$unicode_html1 = '&#' . base_convert(bin2hex(iconv("UTF-8", "UCS-4", $v)), 16, 10) . ';'; // &#25105; 補上 &#xxxxx;	
		}	
		$uri_str.= $unicode_html1;
	}		

	/*  //測試用
	data:{"flag":2018,"data":{"searchValue":"&#31859;&#26862; &#34067;&#36234;&#33683;&#40613;&#29255;BTREW 456","cateCode":"","cateLevel":"-1","cp":"N","NAM":"N","normal":"N","first":"N","superstore":"N","curPage":"1","priceS":"0","priceE":"9999999","searchType":"1","reduceKeyword":"","specialGoodsType":"","rtnCateDatainfo":{"cateCode":"","cateLv":"-1","curPage":"1","historyDoPush":false,"timestamp":1509073433702}}}		
	*/	

	$json_data='{"flag":2018,"data":{"searchValue":"'.$uri_str.'","cateCode":"","cateLevel":"-1","cp":"N","NAM":"N","normal":"N","first":"N","superstore":"N","curPage":"1","priceS":"0","priceE":"9999999","searchType":"1","reduceKeyword":"","specialGoodsType":"","rtnCateDatainfo":{"cateCode":"","cateLv":"-1","curPage":"1","historyDoPush":false,"timestamp":'.$t.'}}}';


	$data = "data=".urlencode($json_data)."\n";		
	/*  //測試用
	$data="data=%7B%22flag%22%3A2018%2C%22data%22%3A%7B%22searchValue%22%3A%22%26%2331859%3B%26%2326862%3B+%26%2334067%3B%26%2336234%3B%26%2333683%3B%26%2340613%3B%26%2329255%3BBTREW+456%22%2C%22cateCode%22%3A%22%22%2C%22cateLevel%22%3A%22-1%22%2C%22cp%22%3A%22N%22%2C%22NAM%22%3A%22N%22%2C%22normal%22%3A%22N%22%2C%22first%22%3A%22N%22%2C%22superstore%22%3A%22N%22%2C%22curPage%22%3A%221%22%2C%22priceS%22%3A%220%22%2C%22priceE%22%3A%229999999%22%2C%22searchType%22%3A%221%22%2C%22reduceKeyword%22%3A%22%22%2C%22specialGoodsType%22%3A%22%22%2C%22rtnCateDatainfo%22%3A%7B%22cateCode%22%3A%22%22%2C%22cateLv%22%3A%22-1%22%2C%22curPage%22%3A%221%22%2C%22historyDoPush%22%3Afalse%2C%22timestamp%22%3A1509074120%7D%7D%7D";	
	*/
	$ret_momo = curl_requset($http_url, $header, $referer, $data, 1, "", "", 0);//MOMO

	$ret_arr=json_decode($ret_momo["body"],1);

	$momo_arr=array();
	foreach ( $ret_arr["rtnData"]["searchResult"]["rtnSearchData"]["goodsInfoList"] as $k => $v ) {

		$momo_arr[$k]["id"]=$v["goodsCode"];
		$momo_arr[$k]["pic"]=$v["imgUrl"];
		$momo_arr[$k]["name"]=$v["goodsName"];
		$momo_arr[$k]["price"]=$v["SALE_PRICE"];
		$momo_arr[$k]["describe"]=$v["goodsSubName"];
	}

	$out = $momo_arr;
	return $out;
}

function get_books_search($keyword) 
{ 
	global $include_path, $company_data;

	$keyword = urlencode($keyword);
	$url_books = $company_data[3]["url"]; //config.php
	$get_str = $keyword."/cat/all";
	

	$header = array(
		'Connection: keep-alive',
		'Upgrade-Insecure-Requests: 1',
		'Cache-Control: max-age=0',	
		'Upgrade-Insecure-Requests: 1',
		/*'Accept-Encoding: gzip, deflate',		*/
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 		
		'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',		
		'X-Requested-With: XMLHttpRequest'
	);
	$referer = "http://www.books.com.tw/";
   
	$ret_books = curl_requset($url_books.$get_str, $header, $referer, 0, "", "", "", 0);//BOOKS	

	#解析HTML
	include_once $include_path."/simple_html_dom.php";
	$books_arr=array();
	$html = str_get_html($ret_books["body"]);

	#名稱&連結
	$c=0;
	foreach ( $html->find('.item h3 a') as $element4) {

		$books_arr[$c]["name"]=trim($element4->text());
		$books_arr[$c]["link"]=$element4->href;

		$c++;
	}
	#價錢
	$c=0;
	foreach ( $html->find('.price strong') as $element1) {

		if (strpos ($element1->text(), "元")) {        
			$books_arr[$c]["price"]=$element1->text();
			$c++;
		}    
	}
	$out = $books_arr;

	return $out;
}

function get_yahoo_search($keyword) 
{ 
	global $include_path, $company_data;

	$keyword = urlencode($keyword);
	$url_yahoo = $company_data[4]["url"]; //config.php
	$get_str="p=".$keyword."&qt=product&cid=0&clv=0&cid_path=";
	

	$header = array(
		'Connection: keep-alive',
		'Upgrade-Insecure-Requests: 1',
		'Cache-Control: max-age=0',	
		'Upgrade-Insecure-Requests: 1',
		'Host: tw.search.buy.yahoo.com',
		/*'Accept-Encoding:gzip, deflate, br',		*/
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8', 
		'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',		
		'X-Requested-With: XMLHttpRequest'
	);
	$referer = "https://tw.buy.yahoo.com/";


	$ret_yahoo = curl_requset($url_yahoo.$get_str, $header, $referer, 0, "", "", "", 1);//BOOKS

	#解析HTML
	include_once $include_path."/simple_html_dom.php";
	//yahoo 
	$yahoo_arr=array();
	$html123 = str_get_html($ret_yahoo["body"]);

	#價錢
	$c=0;
	foreach ( $html123->find('.srp-pdprice') as $e_pdprice) {

		$yahoo_arr[$c]["price"]=trim($e_pdprice->plaintext);    
		$c++;
	}
	#名稱
	$c=0;
	foreach ( $html123->find('.srp-pdtitle a') as $e_pdtitle) {

		$yahoo_arr[$c]["name"]=trim($e_pdtitle->text());
		$yahoo_arr[$c]["link"]=trim($e_pdtitle->href);

		$c++;
	}
	$out = $yahoo_arr;
	return $out;
}



?>