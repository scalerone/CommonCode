<?php
/*前台用户 登录用户的设置和得到*/
function setuser($user){
	$_SESSION['user'] = $user;
}

/* footer邮件列表 */
function getlinks(){
	$Dbb=new Base();
	$linarr= $Dbb->Db->fetch_all("select Vc_name,Vc_link from es_link where status=1");
	$str="";
	$str.="<ul>";
	foreach($linarr as $v){
		$str.= '<li><a href="'.$v['Vc_link'].'" target="_blank">'.$v['Vc_name'].'</a></li>';
	}
	$str.="</ul>";
	return $str;	
}

function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}  

function log4r ($category , $rn=null,$data = null) {
	$trace = debug_backtrace() ;
	$trace = formatLog($trace) ;
	$logger = Logger::getLogger('return.'.$category) ;
	if ($rn && 0===isetna($rn,0)) {
		$logger->error($trace) ;
	} else {
		$logger->info($trace) ;
	}
	if($data){
		$logger->info($data) ;
	}else{
		if (isset($_POST)) {
			$logger->info(formatLog($_POST)) ;
		}else{
			$logger->info(formatLog($_GET)) ;
		}
	}
	
}

function log4n ($category , $rn=null,$data = null) {
	$trace = debug_backtrace() ;
	$trace = formatLog($trace) ;
	$logger = Logger::getLogger('notify.'.$category) ;
	if ($rn && 0===isetna($rn,0)) {
		$logger->error($trace) ;
	} else {
		$logger->info($trace) ;
	}
	if($data){
		$logger->info($data) ;
	}else{
		if (isset($_POST)) {
			$logger->info(formatLog($_POST)) ;
		}else{
			$logger->info(formatLog($_GET)) ;
		}
	}
}

function formatLog ($data,$str='') {
	if (is_array($data)) {
		$str.='[';
		foreach ($data as $key => $val) {
			$str .= $key . '=' ;
			if (is_array($val)) {
				$str .= formatLog($val,$str) ;
			} elseif (is_numeric($val) || is_string($val)) {
				$str .= $val ;
			} 
			$str .= ';' ;
		}
		$str.=']';
	} elseif (is_numeric($data) || is_string($data)) {
		$str = $data ;
	}
	return $str;
}

/**
 * 写日志,主要针对于直接取得返回结果的处理
 * @param  string $category 操作类型
 * @param  array  $rn       0=>0/1, 1=>结果
 * @param  array  $data     需要记录的结果
 * @author zhaowf
 */
function log4rNoPost($category, $rn, $data) {
	$trace = debug_backtrace();
	$logger = Logger::getLogger('return.'.$category);
	if (0===$rn[0]) {
		$logger->error($trace);
	} else {
		$logger->info($trace);
	}
	$logger->info($data);
}

/**
 * 记录发送邮件及短信
 * @param  string $category 操作类型
 * @param  string $content  需要记录的内容
 * @param  string $file     文件名
 * @author zhaowf
 */
function w_email_msg_log($category, $content='', $file='email_msg_log.txt') {
	$fp=fopen(WEBROOT.'log'.L.$file,"a+");
	flock($fp, LOCK_EX) ;
	fwrite($fp, my_date('Y-m-d H:i:s')."\t".$category."\t".$content."\r\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

function getuser($isall=1){
	
	$lg = isset($_SESSION['user']) ? $_SESSION['user'] : false;
	if(!$lg){
		$cookie_users = isset($_COOKIE['loginstaus']) ? $_COOKIE['loginstaus'] : false;
		if(!$cookie_users) return false;
		$cuo = explode(',', ase_decode($cookie_users));
		if(count($cuo)!=2)return false;
		$u = new User();
		$r = $u->login($cuo[0], $cuo[1], 0);
		if($r['flag']<1){
			setcookie('loginstaus','',-1,'/');
			return false;
		}
		$usero = $r['user'];
		setuser($u->usero($usero));
		$uid = $usero['ID'];
	}else{
		$u = new User();
		
		/*zysadd
		 * 增加cookie过期验证，有待优化
		 */ 
		if(isset($_SESSION['logintime'])){
			$etime=$u->Cfg->expirationTime/2;
			$otime=(int)$_SESSION['logintime'];
			$ntime=time();
			$ctime=$ntime-$otime;
			if($ctime>=0){
				if($ctime>$u->Cfg->expirationTime){
					unset($_SESSION['user']);
					unset($_SESSION['logintime']);
				}elseif($ctime>$etime){
					$_SESSION['logintime']=$ntime;
				}
			}
			else{
				unset($_SESSION['user']);
				//w_log("");
				//setcookie('loginstaus','',-1,'/');
				setcookie('logintime','',-1,'/');
				return false;
			}	
		}else{	
			unset($_SESSION['user']);			 
			return false;
		}
		
		$uid = $lg['uid'];
		$usero = $u->getInfo($uid,'*');
		if(!$usero){unsetuser();return false;}
	}
	$lg = $u->usero($usero, $isall);
	if($isall){
		$o = jsonstr_to_array($lg['T_json']);
		$lg['json'] = $o;
	}
	$Message = new Message();
	$lg['messagenum']=$Message->getCount($uid,1);
	unset($u, $o, $Message);
	return $lg;
}
function unsetuser(){
	setcookie('loginstaus','',-1,'/');
	setcookie('logintime','',-1,'/');
	if (isset($_SESSION['user'])) unset($_SESSION['user']);
}
function loginouttime(){
	setcookie('lglocation',dropPostURl(),-1,'/');
	header('Location: /index.php?act=user&m=public&w=login');
	exit;
}
/**
 * post请求重新赋值 add by sakura 20141204
 * @return string|unknown
 */
function dropPostURl(){
	$act=isset($_GET['act'])?$_GET['act']:'';
	$m=isset($_GET['m'])?$_GET['m']:'';
	$w=isset($_GET['w'])?$_GET['w']:'';
	$uri=$act.$m.$w;
	switch ($uri){
		case 'userfundpayto':
			return '/index.php?act=user&m=fund&w=pay';
		case 'userfundpaytocompany':
			return '/index.php?act=user&m=fund&w=paycompany';
		default:
			return $_SERVER['REQUEST_URI'];
	}
}
function toerrorpage(){
	header('Location: /404.php');
	exit;
}

//页面级设置用户扩展信息
function setuserextend(&$lg,$da=array()){
	if(!$lg || empty($da))return false;
	$o = jsonstr_to_array($lg['T_json']);
	foreach($da as $k=>$v){$o[$k] = $v;}
	$u = new User();
	$u->setUserExtend($lg['uid'],$o);
	$lg['T_json'] = json_encode($o);
	$lg['json'] = $o;
}

/*
php ASE 加密与解密
*/
function ase_encode($d,$key = 'kign@zj'){
	return ase_en_de_code($d,0,$key);
}
function ase_decode($d,$key = 'kign@zj'){
	return ase_en_de_code($d,1,$key);
}
function ase_en_de_code($data,$k=0,$key){
	$iv = md5(md5($key));
	if($k==0){
		return trim(safe_b64encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv)));
	}else{
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, safe_b64decode($data), MCRYPT_MODE_CBC, $iv));
	}
}
function safe_b64encode($string) {
	return str_replace(array('+','/','='),array('-','_',''),base64_encode($string));
}
function safe_b64decode($string) {
	$data = str_replace(array('-','_'),array('+','/'),$string);
	$mod4 = strlen($data) % 4;
	if ($mod4) { $data .= substr('====', $mod4); }
	return base64_decode($data);
}



/*return json tip
url:CLOSE, 跳转连接
*/
/*成功不提示直接跳转*/
function showSucUrl($url='',$obj='parent'){return showTip('',$url,$obj,'tourl');}
/*成功提示跳转*/
function showSuc($str,$url='',$obj='parent'){return showTip($str,$url,$obj,'succ');}
/*失败提示关闭*/
function showErr($str,$url='CLOSE'){return showTip($str,$url,'self','error');}
function showTip($str,$url='CLOSE',$obj='self',$type='error',$format='htmls'){
	if($obj=='')$obj='parent';
	$jsons = json_encode(array('str'=>$str,'url'=>$url,'w'=>$obj,'ty'=>$type));
	if($format=='json'){ return $jsons; }
	return $str.'<br/><script>parent.showTip('.$jsons.');</script>';
}

/**
 * 记录SQL操作日志
 */
function w_sql_log($str, $filename='sql.log') {
	file_put_contents(WEBROOT.'log/'.$filename, $str."\r\n", FILE_APPEND);
}

/*test log return ''*/
function w_log($str, $filename='log.txt'){
	$fp = fopen(WEBROOT.'log/'.$filename, 'a+');
	fwrite($fp, "\r\n".my_date('Y-m-d H:i:s')."\t".$str);
	fclose($fp);
}
function w_log_regulatory($str) {
    $filename = 'regulatory-'.date('Y-m-d').'.log' ;
    w_log($str, $filename) ;
}

/*
 * 手机操作日志
 */
function w_log_phone($str, $filename='log_phone'){
	if(!strpos($filename,'.txt')){
		$filename .= '_'.date('Y-m').'.txt';
	}
	$fp = fopen(WEBROOT.'log'.L.$filename, 'a+');
	fwrite($fp, "\r\n".date('Y-m-d H:i:s')."\t".$str);
	fclose($fp);
}
function w_log_phone_notify($str, $filename='log_phone'){
	if(!strpos($filename,'.txt')){
		$filename .= '_'.date('Y-m').'.txt';
	}
	$fp = fopen(WEBROOT.'log'.L.$filename, 'a+');
	fwrite($fp, "\r\n".date('Y-m-d H:i:s')."\t".'notify_'.$str);
	fclose($fp);
}
/*test log return ''*/
function dump($o){
	echo '<hr><pre>';
	var_dump($o);
	echo '</pre><hr>';
}



//对象转数组,使用get_object_vars返回对象属性组成的数组
function objectToArray($obj){
	$arr = is_object($obj) ? get_object_vars($obj) : $obj;
	if(is_array($arr)){
		return array_map(__FUNCTION__, $arr);
	}else{
		return $arr;
	}
}

//数组转对象
function arrayToObject($arr){
	if(is_array($arr)){
		return (object) array_map(__FUNCTION__, $arr);
	}else{
		return $arr;
	}
}


/*param noset return ''*/
function iset(&$s,$def=''){return isset($s)?$s:$def;}
function isetn(&$s,$def=0){return isset($s)?$s:$def;}
function iseta($a,$s,$def=''){return isset($a[$s])?$a[$s]:$def;}
function isetna($a,$s,$def=0){return iseta($a,$s,$def);}

function I($s,$def=''){return iset($_REQUEST[$s],$def);}

/*json   area*/
function returnjson($array){echo json_encode($array);exit;}
/*jsonstr to array*/
function jsonstr_to_array($str){
	$obj = json_decode($str);
	return json_to_array($obj);
}
/*json to array*/
function json_to_array($obj){
	$arr = array();
	if(is_null($obj)) return $arr;
	foreach($obj as $k=>$v){
		if(is_object($v) || is_array($v)){
			$arr[$k] = json_to_array($v);
		}else{
			$arr[$k] = $v;
		}
	}
	return $arr;
}
/*array to json中文不转义 */
 function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
      
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
      
    function array2json($array) {
        arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
/*取得随机纯数字 可指定几位*/
function generateCode($len,$T=1){
    $code = '';
	if($T==1) $charset = '0123456789';
	else $charset = 'ABCDEFGHKLMNPRSTUVWYZ3456789';
    for($i = 1, $cslen = strlen($charset); $i <= $len; ++$i) {
      $code .= strtoupper( $charset{rand(0, $cslen - 1)} );
    }
    $tmpCode = $code . 'x' ;
    if (strpos($tmpCode, '0') === 0) {
    	return generateCode($len,$T) ;
    }
    return $code;
}
/*radio checkbox  checked*/
function fncheck($str, $find){
	return strpos(','.$str.',', ','.$find.',')===false ? '':'checked="checked"';
}
/*select_option  selected*/
function fnselect($str, $find){
	return $str==$find ? 'selected="selected"':'';
}
/*组合字符串，$name:参数名,$value:值,$param:[[0,*],[1,*]]*/
function fn_radio($name,$value,$param){
	$str = '';
	foreach($param as $k=>$v){
		$str .= '<input type="radio" name="'.$name.'" value="'.$k.'" '.fncheck($value,$k).'>'.$v;
	}
	return $str;
}
function fn_check($name,$value,$param){
	$str = '';
	foreach($param as $k=>$v){
		$str .= '<input type="checkbox" name="'.$name.'[]" value="'.$k.'" '.fncheck($value,$k).'>'.$v;
	}
	return $str;
}
function fn_select($name,$value,$param){
	$str = '<select name="'.$name.'" class="sel_put2 chzn-select-no-single">';
	foreach($param as $k=>$v){
		$str .= '<option value="'.$k.'" '.(fnselect($value,$k)).'>'.$v.'</option>';
	}
	$str .='</select>';
	return $str;
}
/**过滤用户姓名**/
function suname($v,$isgl=1){
	if($isgl==1)return $v;
	return mb_substr($v,0,1,'UTF-8').'**';
}

//临时用的
/******************************************/

function getIndex ($str , $mblen) {
    $idx = 0 ;
    $i = 0 ;
    while ($i < strlen($str)) {
        if(ord(substr($str, $i, 1)) > 127){ 
            $i+=3;  
        } else {
            $i+=1;
        }
        $idx ++ ;
        if ($idx == $mblen) {
            return $i ;
        }
    }
}

function msubstr($str, $start, $len) {  
    $tmpstr = "";  
    $strlen = $start + $len;  
    for($i = $start; $i < $strlen; $i++){  
        if(ord(substr($str, $i, 1)) > 127){  
            $tmpstr.=substr($str, $i, 3);  
            $i+=2;  
        }else  {
            $tmpstr.= substr($str, $i, 1);  
        }
    }  
    return $tmpstr;  
}  
/**过滤名字**/

function suname1 ($name) {
    $nameLen = mb_strlen($name,'utf-8') ;
    if ($nameLen == 1) {
        return $name . '**' ;
    }
    if ($nameLen == 2) {
        return mb_substr($name, 0,1,'utf-8') . '**' .mb_substr($name, 1,2,'utf-8') ;
    }
    $fhead = msubstr($name,0,2).'**' ;
    $fend = msubstr($name,getIndex($name,$nameLen-2),strlen($name))  ;
    if (strlen($fend)>2) {
        $fend = mb_substr($fend, 1,2,'utf-8') ;
    }
    $fname = $fhead.$fend;
	return $fname ;
}

/*****************************************/

/**过滤手机号 将中间四位隐藏**/
function sphone($phone){
	if($phone=='') return '';
	return substr($phone,0,3) . '****' . substr($phone,-4,4);
}
/**过滤身份证号 将中间生日隐藏**/
function ssfzh($phone){
	if($phone=='') return '';
	return substr($phone,0,2) . '************' . substr($phone,14);
}
/**过滤银行证号 4位分割 前面几组加密**/
function sbankno($s, $d=1){
	if($s=='') return '';
	$s = preg_replace("/(\d{4})(?=\d)/","$1 ", $s);
	if($d==0) return $s;
	$sa = explode(' ', $s);
	foreach($sa as $k=>$v){
		$nsa[] = $k==count($sa)-1 ? $v:'****';
	}
	return join(' ',$nsa);
}
/*text 转 html 显示*/
function txt2html($s){
	return str_replace(array("\r\n","\r","\n"),array('<br/>'),$s);
}
/**写参数文件 data目录**/
function writeincdata($str, $fname=''){
	if(!$fp = fopen($fname,'w')){ return array(-2,"不能打开文件 {$fname} ");}
	if(flock($fp, LOCK_EX)){// 进行排它型锁定
		if(fwrite($fp,$str)===false){ return array(-3,"不能写入到文件 {$fname} ");}
		flock($fp, LOCK_UN);// 释放锁定
	} else {
		 return array(-4,"不能锁定文件 {$fname} ");
	}
	fclose($fp);
	return array(1);
}
/**坏词过滤**/
function badwordfilter($str,$replyto='**'){
	$badfile = WEBROOTDATA.'badword.cache.inc.php';
	if(file_exists($badfile)){require($badfile);}
	if(!isset($da_badword)){return $str;}
	return str_replace($da_badword,$replyto,$str);
}
/**保留名检查过滤**/
function protectnamecheck($str){
	$files = WEBROOTDATA.'protectname.cache.inc.php';
	if(file_exists($files)){require($files);}
	if(!isset($da_protectname)){return $str;}
	$nstr = str_replace($da_protectname,'',$str);
	return $nstr==$str;
}
/**自定义认证获取全部或者单个名称 **/
function getCertificate($id=0){
	$files = WEBROOTDATA.'certificate.cache.inc.php';
	if(file_exists($files)){require($files);}
	if(!isset($da_certificate)){return $id=0?array():'';}
	if($id==0){
		return $da_certificate;
	}else{
		return iset($da_certificate[$id]);
	}
}

//分页列表简单函数
function getPageStrFun($pcount, $page, $urlinfo=''){
	$str = '';
	$str .= $page>=1?'<span>上一页</span>':'<a href="?page='.($page-1).$urlinfo.'">上一页</a>';
	for($i=1; $i<=$pcount; $i++){
		$str .= ' ';
		$str .= $page==$i?'<span>'.$i.'</span>':'<a href="?page='.$i.$urlinfo.'">'.$i.'</a>';
	}
	$str .= ' '; 
	$str .= $page>=$pcount?'<span>下一页</span>':'<a href="?page='.($page+1).$urlinfo.'">下一页</a>';
	return $str;
}
//分页列表 居中函数
function getPageStrFunSd($pcount, $page, $urlinfo=''){
	if($pcount<1){return '';}
	$s = '';$m=10;
	$s .= $page<=1?'<span>上一页</span>':'<a href="?page='.($page-1).$urlinfo.'">上一页</a>';
	if($pcount<$m){$b=1;$e=$pcount;}
	else{
		$b=$page-3;$e=$page+2;
		$b=$b<1?1:$b;$e=$e>$pcount?$pcount:$e;
	}
	$s .= $b>1?'<a href="?page=1'.$urlinfo.'">1</a>...':'';
	for($i=$b; $i<=$e; $i++){
		$s .= $page==$i?'<a class="now">'.$i.'</a>':'<a href="?page='.$i.$urlinfo.'">'.$i.'</a>';
	}
	$s .= $e<$pcount?'...':'';

	$s .= ($page+3==$pcount&&$e<$pcount) ? '<a href="?page='.$pcount.$urlinfo.'">'.$pcount.'</a>':'';
	$s .= $page>=$pcount?'<span>下一页</span>':'<a href="?page='.($page+1).$urlinfo.'">下一页</a>';
	$s .= ' &nbsp; 共'.$pcount.'页';
	//跳转 功能需要特点js支持
	if($pcount>9)$s .= '，到第<input type="text" name="gotopage" class="gotopage" data="'.$urlinfo.'" value="'.$page.'">页 <a class="sure b_gotopage" href="javascript:;">确定</a>';
	return $s;
}

function getPageStrFunSd_JS($pcount, $page, $urlinfo='',$gotopage='gotopage'){
	if($pcount<1){return '';}
	$s = '';$m=10;
	$s .= $page<=1?'<span>上一页</span>':'<a href="javascript:getPageStrFunSd_JS(\'?page='.($page-1).$urlinfo.'\')">上一页</a>';
	if($pcount<$m){$b=1;$e=$pcount;}
	else{
		$b=$page-3;$e=$page+2;
		$b=$b<1?1:$b;$e=$e>$pcount?$pcount:$e;
	}
	$s .= $b>1?'<a href="?page=1'.$urlinfo.'">1</a>...':'';
	for($i=$b; $i<=$e; $i++){
		$s .= $page==$i?'<a class="now">'.$i.'</a>':'<a href="javascript:getPageStrFunSd_JS(\'?page='.$i.$urlinfo.'\')">'.$i.'</a>';
	}
	$s .= $e<$pcount?'...':'';
	
	$s .= ($page+3==$pcount&&$e<$pcount) ? '<a href="javascript:getPageStrFunSd_JS(\'?page='.$pcount.$urlinfo.'\')">'.$pcount.'</a>':'';
	$s .= $page>=$pcount?'<span>下一页</span>':'<a href="javascript:getPageStrFunSd_JS(\'?page='.($page+1).$urlinfo.'\')">下一页</a>';
	$s .= ' &nbsp; 共'.$pcount.'页';
	//跳转 功能需要特点js支持
	if($pcount>9)$s .= '，到第<input type="text" name="'.$gotopage.'" class="gotopage" data="'.$urlinfo.'" value="'.$page.'">页 <a class="sure b_gotopage" href="javascript:;">确定</a>';
	return $s;
}

function getPageStrFunN($pcount, $page, $count,$urlinfo=''){
	if($pcount<1){return '';}
	$s = "<span>共{$count}条，{$pcount}页</span><a href=\"?page=1{$urlinfo}\">&lt;&lt;首页</a>&lt;";
	$m=10;
	$s .= $page<=1?'<span>上一页</span>':'<a href="?page='.($page-1).$urlinfo.'">上一页</a>';
	if($pcount<$m){$b=1;$e=$pcount;}
	else{
		$b=$page-3;$e=$page+2;
		$b=$b<1?1:$b;$e=$e>$pcount?$pcount:$e;
	}
	$s .= $b>1?'<a href="?page=1'.$urlinfo.'">1</a>...':'';
	for($i=$b; $i<=$e; $i++){
		$s .= $page==$i?'<a class="now">'.$i.'</a>':'<a href="?page='.$i.$urlinfo.'">'.$i.'</a>';
	}
	$s .= $e<$pcount?'...':'';

	$s .= $page>=$pcount?'<span>下一页</span>':'<a href="?page='.($page+1).$urlinfo.'">下一页</a>';
	$s .= '<a href="?page='.$pcount.$urlinfo.'">尾页&gt;&gt;</a>';
	
	return $s;
}

/*
* 剩余时间格式化 参数:截止时间
 返回: 剩余时间，格式X天X小时X分，24小时制
       时间已过，显示X月X日 XX时XX分 已满
*/
function formatResetTime($et='2013-11-04 11:42:43', $f='',$nt=''){
	$date1 = date_create($et);
	if(!$date1){return '未知';}
	$e = floatval(date_format($date1,'U'));
	$n = floatval(date_format(date_create($nt),'U'));
	$i_i=60;$i_h=3600;$i_d=$i_h*24;
	if($f==''){
		if(($t=$e-$n)<=0) return '已过期';
		$s='VIP期限 ';
		$d=intval($t/$i_d);
		if($d>0){$s.=$d.'天';}
		$t=$t%$i_d;
		$h=intval($t/$i_h);
		if($h>0){$s.=$h.'小时';}
		return $s;
	}elseif($f=='dhm'){
		if(($t=$e-$n)<=0) return '0';
		$s='';
		$d=intval($t/$i_d);
		if($d>0){$s.=$d.'天';}
		$t=$t%$i_d;
		$h=intval($t/$i_h);
		if($h>0){$s.=$h.'小时';}
		$t=$t%($i_h);
		$h=intval($t/$i_i);
		if($h>0){$s.=$h.'分';}
		return $s;
	}elseif($f=='d/his'){
		if(($t=$e-$n)<=0) return '0秒';
		$t=abs($e-$n);
		$s='';
		$d=intval($t/$i_d);
		if($d>0){$s.=$d.'天';return $s;}
		$t=$t%$i_d;
		$h=intval($t/$i_h);
		if($h>0){$s.=$h.'时';}
		$t=$t%($i_h);
		$h=intval($t/$i_i);
		if($h>0){$s.=$h.'分';}
		$t=$t%($i_i);
		if($t>0){$s.=$t.'秒';}
		return $s;
	}elseif($f=='y'){
		$t=$e-$n;
		return intval($t/(360*$i_d));
	}
}

/*
*格式化 金额
*/
//超过百万，资金按万取整，显示XXX万
function formatAmount($v,$abs=false){
	if ($abs) {
		$v = abs($v) ;
	}
	return formatAmountSimply($v);
	//if($v<1000000) return formatAmountSimply($v);
	//return floor($v/10000).'万';
}
function formatAmountSimply($v,$t=1){
	//if($v>=1000000 && $t==1) return formatAmount($v);
	return number_format($v,2,'.',',');
}
function formatRenNum($n){
	$s = '';
	if($n>10000){ $s = floor($n/10000).'万';}
	else{ $s = $n.'人';}
	return $s;
}
/**
* 剪切字符串 $str:字符串(utf-8编码) $len:长度（汉字长度）
* 函数作用：将字符串剪切成为 $len 个汉字的长度
**/
function cutstr($str,$len){
	global $FL;
	return $FL->cutstr($str,$len);
}

/**
  * 系统邮件发送函数
  * @param string $to    接收邮件者邮箱
  * @param string $name  接收邮件者名称
  * @param string $subject 邮件主题 
  * @param string $body    邮件内容
  * @param string $attachment 附件列表  
  * @param string $from_name 发件人名称 addbysakura
  * @return boolean 
  */
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null,$from_name=''){
	global $g_conf;
	//测试环境不发送邮件和手机消息---TODO
	if(iset($g_conf['cfg_sys_re'],0)==1){
		w_log("测试环境发送判断begin".$to,'log_test.txt');
		if(file_exists(WEBROOTDATA.'test.email.cache.inc.php')){
			require(WEBROOTDATA.'test.email.cache.inc.php');
			w_log("判断是否符合发送条件",'log_test.txt');
			if(!in_array($to, $da_test)){
				w_log("不发送end",'log_test.txt');
				return '测试环境，不发送邮件';
			}
		}else{
			w_log("无缓存文件，不发送end",'log_test.txt');
			return '测试环境，无缓存文件，不发送邮件';
		}
		w_log("进入发送end",'log_test.txt');
	}
		
	//邮件配置
	$c = array(
		'SMTP_HOST'   => $g_conf['cfg_email_server'], //SMTP服务器 'smtp.vip.163.com'
		'SMTP_PORT'   => '25', //SMTP服务器端口
		'SMTP_USER'   => $g_conf['cfg_email'], //SMTP服务器用户名
		'SMTP_PASS'   => $g_conf['cfg_email_pwd'], //SMTP服务器密码
		'FROM_EMAIL'  => $g_conf['cfg_email'], //发件人EMAIL
		'FROM_NAME'   => iset($from_name,$g_conf['cfg_email_name']), //发件人名称
		'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
		'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
	);

	include_once(WEBROOT.'include'.L.'phpmailer'.L.'class.phpmailer.php');
	$mail             = new PHPMailer();  // PHPMailer对象
	$mail->CharSet    = 'UTF-8';          // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
	$mail->IsSMTP();                      // 设定使用SMTP服务
	$mail->SMTPDebug  = 0;                // 关闭SMTP调试功能
	// 1 = errors and messages
	// 2 = messages only
	$mail->SMTPAuth   = true;             // 启用 SMTP 验证功能
	//$mail->SMTPSecure = 'ssl';          // 使用安全协议
	$mail->Host       = $c['SMTP_HOST'];  // SMTP 服务器
	$mail->Port       = $c['SMTP_PORT'];  // SMTP服务器的端口号
	$mail->Username   = $c['SMTP_USER'];  // SMTP服务器用户名
	$mail->Password   = $c['SMTP_PASS'];  // SMTP服务器密码
	$mail->SetFrom($c['FROM_EMAIL'], $c['FROM_NAME']);
	$replyEmail       = $c['REPLY_EMAIL']?$c['REPLY_EMAIL']:$c['FROM_EMAIL'];
	$replyName        = $c['REPLY_NAME']?$c['REPLY_NAME']:$c['FROM_NAME'];
	$mail->AddReplyTo($replyEmail, $replyName);
	$mail->Subject    = $subject;
	$mail->MsgHTML($body);
	$mail->AddAddress($to, $name);
	if(is_array($attachment)){ // 添加附件
		foreach ($attachment as $file){
			is_file($file) && $mail->AddAttachment($file);
		}
	}
	return $mail->Send() ? 'ok' : $mail->ErrorInfo;
}

/*
* 适用于 php >5.3 时间超出范围
*关于strtotime 的另外版本 对应2038问题解决
*/
function my_strtotime($str=''){
	$date = date_create($str);
	if(!$date){return false;}
	return floatval(date_format($date,'U'));
}
function my_date($gs='Y-m-d H:i',$timestamp=0){
	$date = date_create();
	if($timestamp!=0 && $timestamp){
		$date = date_create("@$timestamp");
		$date->setTimeZone(new DateTimeZone('PRC'));
	}
	return date_format($date, $gs);
}
function formatTime($time='now',$gs='Y-m-d H:i'){
	if($time==''){return '';}
	$date = date_create($time);
	if(!$date){return '';}
	return date_format($date, $gs);
}

/*
*用户状态检查
*/
function checkUserStatus($lg_n=false){
	if($lg_n){$lg=$lg_n;}
	else{ global $lg; }
	if(!$lg){return array('flag'=>-404,'msg'=>'需要登录','url'=>'/index.php?act=user&m=public&w=login');}
	$da = getUserStatus($lg);
	
	// if($da['authEmail']!=1){return array('flag'=>-11,'msg'=>'邮箱未认证','url'=>'/index.php?act=user&m=account&w=safe&ty=email#email');}
	if($da['authMobile']!=1){return array('flag'=>-12,'msg'=>'手机未认证','url'=>'/index.php?act=user&m=account&w=safe&ty=mobile#mobile');}
	//if($da['isopen']!=1){return array('flag'=>-13,'msg'=>'未开通易宝','url'=>'/index.php?act=user&m=fund&w=yiji'.($da['icy']==1?'&ty=company':''));}
	//if($da['isopen']!=1){return array('flag'=>-13,'msg'=>'未绑定银行卡','url'=>'/index.php?act=user&m=wizard');}
	
	if($da['icy']==0){//个人
		if($da['isopen']!=1){
			return array('flag'=>-13,'msg'=>'未绑定银行卡','url'=>'/index.php?act=user&m=wizard');
		}
		if(!$da['isbindbank']){return array('flag'=>-13,'msg'=>'未绑定银行卡','url'=>'/index.php?act=user&m=wizard');}
	}else{
		if($da['icyisopen']==0){return array('flag'=>-19,'msg'=>'企业会员未审核','url'=>'/index.php?act=user');}
	}
	
	//if($da['tradepasswd']!=1){return array('flag'=>-14,'msg'=>'未设置交易密码');}
	if($da['vipexpire']==-1){return array('flag'=>-15,'msg'=>'会员VIP未激活','url'=>'/index.php?act=user');}
	if($da['vipexpire']==0){return array('flag'=>-16,'msg'=>'会员VIP已过期','url'=>'/index.php?act=user');}
	
	//if($da['authTrueName']!=1){return array('flag'=>-17,'msg'=>'未实名认证','url'=>'');}
	//if($da['allocation_type']!=1){return array('flag'=>-18,'msg'=>'未授权二次分配','url'=>'/index.php?act=user&m=fund&w=mmmauto');}
	
	
	return array('flag'=>1, 'msg'=>'正常');
}

function getUserStatus($lg_n=false){
	global $Db;
	if($lg_n){$lg=$lg_n;}
	else{ global $lg; }
	if(!$lg){return array();}
	$da['authEmail']=$lg['I_Emailauthenticate']==2 ?1:0;//邮箱
	$da['authMobile']=$lg['I_mobileauthenticate']==2 ?1:0;//手机
	$da['isopen']=$lg['Vc_openid'].'a'!='a' ?1:0;//开通易宝
	$da['icy']=$lg['I_company']>0?1:0;//是否企业用户
	//$da['icyisopen']=$da['icy']==0?$da['isopen']:($da['isopen']==1&&$lg['I_audit']==1?1:0);//是否审核
	$da['icyisopen']=$da['icy']==0?$da['isopen']:(/* $da['isopen']==1&& */$lg['I_audit']==1?1:0);//是否审核
	
	//$da['allocation_type']=$lg['allocation_type']==3 ?1:0;//授权二次分配  
	$da['authTrueName']=iset($lg['json']['authTrueName'],0)==1 ?1:0;//实名认证
	//$da['tradepasswd']=iset($lg['json']['tradepasswd'],'')!='' ?1:0;//设置交易密码
	$ndate = formatTime('now','Y-m-d H:i:s');
	$end = formatTime($lg['Dt_expire'],'Y-m-d H:i:s');
	$da['vipexpire'] = !is_null($lg['Dt_expire']) ? ($end>$ndate ? 1:0):-1;//VIP
	
	//是否开启自动投标
	// $db=new Base();
	// $da['isOpenAutobid'] = $db->Db->fetch_one("select * from p2p_autobid where I_userID = {$lg['ID']} and Status = 1 and I_deal = 1") ;
	if(!$lg['uid']){
		$lg['uid'] = isetna($lg,'ID');
	}
	//已绑定银行卡数量
	if($lg['I_company']>0){
		$da['isbindbank'] = true;
	}else{
		$da['isbindbank'] = $Db->fetch_val("select count(*) from p2p_user_bankcard where LENGTH(Vc_orderNo)>10 and Status=2 and I_userID=".$lg['uid']);
	}
	return $da;
}
/*企业用户已注册但未审核通过提示函数*/
function companyStat($company=0,$audit=0){
	if($company==0) return ;
	if($audit==1)return ;
	$act=isset($_GET['act'])?$_GET['act']:'';
	$m=isset($_GET['m'])?$_GET['m']:'';
	$w=isset($_GET['w'])?$_GET['w']:'';
	$uri=$act.'.'.$m.'.'.$w;
	if(in_array($uri,array('loan.step3.','loan.saveloan.','loan.savereplay.',//借款
			'invest.invest.','invest.savebid.',//投资
			'user.fund.payto','user.fund.paytocompany','',//充值--只加了网银充值
			'user.fund.mmmwithdrawto','user.fund.withdrawto',//提现
			'user.account.mmmbank','user.account.bankEdit','user.account.bankSave',//绑定银行卡
			
			))){
		$msg = '如有问题请联系平台客服，客服电话：40002';
		if($audit==0) $msg = '企业身份信息正在审核中，请耐心等待。'.$msg;
		if($audit==2) $msg = '企业身份信息未通过平台审核。'.$msg;
		//errorPage($msg,'确认','/index.php?act=user&m=fund&w=yiji&ty=company');
		errorPage($msg,'确认','/index.php?act=user&m=wizard');
		
	}
}
/*页面错误转向*/
function errorPage($msg,$bmsg='',$u='',$back=''){
  //$GLOBALS['FLib']->selfUrl("index.php?act=msg&msg={$msg}&bmsg={$bmsg}&u=".urlencode($u)."&back={$back}");
  $GLOBALS['FLib']->selfUrl("/index.php?act=msg&msg=".urlencode($msg)."&bmsg=".urlencode($bmsg)."&u=".urlencode($u)."&back=".urlencode($back)."");
  exit;
}
//根据邮箱地址获取邮箱服务器地址
function getEmailServer($email){
	$url = 'javascript:;';
	$domain = strstr($email, '@');
	if($domain) $url = 'http://mail.'.substr($domain,1);
	return $url;
}
function getVoucherClassStr($data,$I_classIDs=''){
	if(!iset($I_classIDs)){
		return '';
	}
	$files = WEBROOTDATA.'appclass.cache.inc.php';
	if(file_exists($files)){require_once ($files);}else{errorPage('借款分类未生成');}
	
	$class_name = '';
	if(iset($I_classIDs)){
		$I_classID = explode(',', $I_classIDs);
		foreach ($I_classID as $k=>$v){
			$i_classid=intval($v,0);
			if($i_classid>0){
				$class_name .= $da_appclass[$i_classid]['Vc_name'].'投资专用';
			}
		}
	}else{
		$class_name = '无限制';
			
	}
	$data[$key]['class_name'] = $class_name;
	return $class_name;
}

function loanlistFilter($da){
  global $DDIC;
  if(!is_array($da))return $da;
  foreach($da as $k=>$row){
    $da[$k]['repayment']=$DDIC['p2p_application.I_repayment'][$row['I_repayment']];
    if ($da[$k]['I_repayment'] != 3) {
	    if ($da[$k]['I_cycle_num'] == 1) {
	    	$da[$k]['repayment'] .= '（每月还款一次）';
	    } else {
	    	$da[$k]['repayment'] .= '（每'.$da[$k]['I_cycle_num'].'个月还款一次）';
	    }
	}
	$da[$k]['repayment'] = "一次性还本付息" ;
	if ($da[$k]['I_repayment'] == 1) {
		if ($da[$k]['I_cycle_num'] == 1) {
	    	$da[$k]['repayment'] = '月等额本息';
	    } else {
	    	$da[$k]['repayment'] = '每'.$da[$k]['I_cycle_num'].'月等额本息';
	    }
	}
	if ($da[$k]['I_repayment'] == 2) {
		if ($da[$k]['I_cycle_num'] == 1) {
	    	$da[$k]['repayment'] = '按月付息，到期还本';
	    } else {
	    	$da[$k]['repayment'] = '每'.$da[$k]['I_cycle_num'].'月付息，到期还本';
	    }
	}
	$row['N_yearannualrate'] = $row['I_show_y']==1?$row['N_show_yearannualrate']:$row['N_yearannualrate'];
	
	$da[$k]['annualrate']=floatval($row['N_yearannualrate']);
	
	if($row['I_status']==1){
	  $da[$k]['Status']='审核中';
	  $da[$k]['scss']='finish';
	}elseif($row['I_status']==10){
	  $da[$k]['Status']='未通过审核';
	  $da[$k]['scss']='finish';
	}elseif($row['I_status']==15){
	  $da[$k]['Status']='立即预约';
	  $da[$k]['scss']='finish';
	}elseif($row['I_status']==20){
	  $da[$k]['Status']='立即投标';
	  $da[$k]['scss']='';
	}elseif($row['I_status']==40){
	  $da[$k]['Status']='已满标';
	  $da[$k]['scss']='max';
	}elseif($row['I_status']==50){
	  $da[$k]['Status']='还款中';
	  $da[$k]['scss']='return';
	}elseif($row['I_status']==60){
	  $da[$k]['Status']='已还款';
	  $da[$k]['scss']='finish';
	}elseif($row['I_status']==30){
	  $da[$k]['Status']='流标';
	  $da[$k]['scss']='finish';
	}
	if(!$row['Vc_photo']){
		$da[$k]['Vc_photo']='/tpl/image/pic_un.png';
	}
	//20150121 add
	$photo = '/tpl/image/pic_un.png' ;
	if ($row['Vc_title_image']) {
		$imgs = json_decode($row['Vc_title_image']) ;
		if ($imgs && isset($imgs[0])) {
			$photo = $imgs[0] ;
		}
	} 
	$da[$k]['photo'] = $photo ;
	// $da[$k]['photo'] = isset($p['info']['photo'])?$p['info']['photo']:'/tpl/image/pic_un.png';
	// $p['info']['photo'] = isset($p['info']['photo'])?$p['info']['photo']:'/tpl/image/pic_un.png';
	$da[$k]['amount']=sprintf("%.2f",$row['N_amount']/10000);
	$da[$k]['bookingTime'] = date('Y-m-d H:i:s', $row['bookingTime']);
  }
  return $da;
}
/*计算利息和总额
*@param $app 贷款信息
*/
function getReMoey($app){
	if(!is_array($app))return 0;
	$a = calcMoey($app['I_repayment'],$app['N_yearannualrate'],$app['N_amount'],intval($app['Vc_life']));
	return $a['total'];
}

/*计算利息和总额
*@param $repayment : 还款方式 $yearannualrate : 年利率% $amount :　借款金额 $life : 时间长度月
*@return 还款计划各个参数
*/
function calcMoey($repayment,$yearannualrate,$amount,$life){
	$a=array('monthrate'=>floatval($yearannualrate)/1200,'month'=>0,'total'=>0,'list'=>array());
	$life=intval($life);
	if(!$amount || !$life)return $a;
	switch($repayment){
		case 1:
			$a['month']=$amount*$a['monthrate']*pow((1+$a['monthrate']),$life)/(pow((1+$a['monthrate']),$life)-1);
			$a['total']=$a['month']*$life;
			$a['list']=array();
			$money=$amount;
			for($i=1;$i<=$life;$i++){
				$arr=array('inte'=>$money*$a['monthrate']);
				$arr['prin']=$a['month']-$arr['inte'];
				$arr['sur']=$a['total']-$i*$a['month'];
				$a['list'][]=$arr;
			}
			break;
		case 2:
			$a['month']=$amount*$a['monthrate'];
			$a['total']=$amount + $a['month']*$life;
			break;
		case 3:
			$a['month']=$amount+$amount*$a['monthrate']*$life;
			$a['total']=$a['month'];
			break;
	}
	return $a;
}
//计算可投金额
function castMoney($ada,$uam){
    $ada['aamount']=$ada['N_amount']-$ada['bamount'];
	if($ada['aamount']>$ada['I_max']){
		$ada['aamount']=$ada['I_max'];
	}
	if($uam<$ada['I_min']){
		$ada['aamount']=0;
	}elseif($uam<$ada['aamount']){
	  $ada['aamount']=$uam;
	}
	return $ada['aamount'];
}

//文件大小单位转换
//@param $k byte
function sizeto($t){
	$m=1024;
	$t1=round($t/$m,1);
	if($t1<1){ return $t.'Byte'; }
	$t=$t1;
	$t1=round($t/$m,1);
	if($t1<1){ return $t.'Kb'; }
	$t=$t1;
	$t1=round($t/$m,1);
	if($t1<1){ return $t.'Mb'; }
	$t=$t1;
	$t1=round($t/$m,1);
	return $t1.'Gb';
}

//生成随机字符串
function generateNo($len){
	$code = '';
	//$charset = '0123456789abcdefghijklmnopqrstuvwxyz';
	$charset = '0123456789';
	for($i = 1, $cslen = strlen($charset); $i <= $len; ++$i) {
		$code .= strtoupper( $charset{rand(0, $cslen - 1)} );
	}
	return $code;
}

/**
 * 进一计算
 * @param  float  $num 要计算的值
 * @param  integer $len 保留位数
 * @return float       
 */
function floatCeil($num,$len=2) {
	$len = pow(10, $len) ;
	$num = ceil($num*$len) / $len;
	return $num ;
}

function getWebRootToShell(){
	global $Cfg,$g_conf;
	$r = parse_url($Cfg->WebRoot);
	
	//测试环境下此代码才有用
	if(iset($g_conf['cfg_sys_re'],0)==1){
		$r_port =$test_port = isset($r['port']) ? intval($r['port']):80;
	
		$test_r = parse_url($g_conf['cfg_root_url']);
		$test_port = isset($test_r['port']) ? intval($test_r['port']):80;
			
		if($r_port != $test_port){
			$r = parse_url($g_conf['cfg_root_url']);
		}
	}
	//w_log(serialize($r),'getWebRootToShell.txt');
	return $r;
}

function createAsynchronous ($file,$params) {
	global $g_conf;
	$Cfg=$GLOBALS['Cfg'];
	
	$r = parse_url($Cfg->WebRoot);
		
	//测试环境下此代码才有用
	if(iset($g_conf['cfg_sys_re'],0)==1){
		$r_port =$test_port = isset($r['port']) ? intval($r['port']):80;
	
		$test_r = parse_url($g_conf['cfg_root_url']);
		$test_port = isset($test_r['port']) ? intval($test_r['port']):80;
			
		if($r_port != $test_port){
			$r = parse_url($g_conf['cfg_root_url']);
		}
	}
	$paramStr = serializeParam($params) ;
	
	w_log($file.':'.serialize($r).'--'.$paramStr,'createAsynchronous.txt');
	//end-----------
	
	$url = $r;//parse_url($Cfg->WebRoot);
	$url['port'] = isset($url['port']) ? intval($url['port']):80;
	$fp=fsockopen($url['host'],$url['port'],$errno,$errstr,5);
	$out = "GET /shell/${file}.php?{$paramStr} HTTP/1.1\r\n";
	$out .= "Host: ".$url['host'].($url['port']==80?'':':'.$url['port'])."\r\n";
	$out .= "Connection: Close\r\n\r\n";
	fwrite($fp, $out);
	fclose($fp);
}

function validateAsynchronous ($params , $token) {
	if (IS_DEBUG) {return true ;}
	$_token = _getToken($params , $token['timestamp']) ;
	return $_token['token'] == $token['token'] ;
}

function _getToken ($params  , $timestamp='') {
	//编码规则  md5(kiss + md5(参数值+timestamp) + ass)
	//参数值按照字母排序
	ksort($params) ;
	if (''==$timestamp) {
		$timestamp = time() . rand(1000000,9000000) ;
	}
	$valueStr =  implode(array_values($params));
	$result = array(
		'token'     => md5('kiss' . md5($valueStr . $timestamp) . 'ass') ,
		'timestamp' => $timestamp , 
	) ;
	return $result ;
}

function serializeParam ($param , $equal='=' , $separator='&') {
	$result = array(); 
	$token = _getToken ($param) ;
	$param = array_merge($param , $token) ;
	if (count($param) > 0) {
		foreach ($param as $key => $value) {
			$result[] = $key . $equal . $value ;
		}
	}
	return implode($separator, $result) ;
}


/** 
 * 人民币小写转大写 
 * 
 * @param string $number 数值 
 * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆" 
 * @param bool $is_round 是否对小数进行四舍五入 
 * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30， 
 *             有的系统要求输出"壹仟玖佰陆拾元零叁角"，实际上"壹仟玖佰陆拾元叁角"也是对的 
 * @return string 
 */ 
function num2rmb($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE) {
    // 将数字切分成两段
    $parts = explode('.', $number, 2); 
    $int = isset($parts[0]) ? strval($parts[0]) : '0';
    $dec = isset($parts[1]) ? strval($parts[1]) : '';
 
    // 如果小数点后多于2位，不四舍五入就直接截，否则就处理 
    $dec_len = strlen($dec); 
    if (isset($parts[1]) && $dec_len > 2) { 
        $dec = $is_round 
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1) 
                : substr($parts[1], 0, 2); 
    } 
 
    // 当number为0.001时，小数点后的金额为0元 
    if(empty($int) && empty($dec)) { 
        return '零'; 
    } 
 
    // 定义 
    $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖'); 
    $uni = array('','拾','佰','仟'); 
    $dec_uni = array('角', '分'); 
    $exp = array('', '万'); 
    $res = ''; 
 
    // 整数部分从右向左找 
    for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++) { 
        $str = ''; 
        // 按照中文读写习惯，每4个字为一段进行转化，i一直在减 
        for($j = 0; $j < 4 && $i >= 0; $j++, $i--) { 
            $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位 
            $str = $chs[$int{$i}] . $u . $str; 
        } 
        //echo $str."|".($k - 2)."<br>"; 
        $str = rtrim($str, '0');// 去掉末尾的0 
        $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0 
        if(!isset($exp[$k])) { 
            $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位 
        } 
        $u2 = $str != '' ? $exp[$k] : ''; 
        $res = $str . $u2 . $res; 
    } 
 
    // 如果小数部分处理完之后是00，需要处理下 
    $dec = rtrim($dec, '0'); 
 
    // 小数部分从左向右找 
    if(!empty($dec)) { 
        $res .= $int_unit; 
 
        // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求 
        if ($is_extra_zero) { 
            if (substr($int, -1) === '0') { 
                $res.= '零'; 
            } 
        } 
 
        for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++) { 
            $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位 
            $res .= $chs[$dec{$i}] . $u; 
        } 
        $res = rtrim($res, '0');// 去掉末尾的0 
        $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0 
    } else { 
        $res .= $int_unit . '整'; 
    } 
    return $res; 
}











