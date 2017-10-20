<?php
/*
 * common.php
 * 
 * Copyright Sichuan Great Wall Software Technology Co.,LTD. All Rights Reserved.
 * Author sakura 2016年7月5日下午3:51:31
 */
//////////////////////////////////////////////////////

/**
 * 域名路由设置
 */
use think\Route;
use think\Session;
use think\Config;

// if(Config::get('app_debug')){
// 	////////////////////////////////////////////////
// 	//调试模式下用--
// 	//（要配置httpd-vhosts.conf）  将此设置为第一个，手机端输入IP可直接访问wap
// 	//正式删除
// 	Route::bind('wap');
// 	////////////////////////////////////////////////
// }

// Session::init([
//     'prefix'         => 'module',
//     'type'           => '',
//     'auto_start'     => true,
// ]);

/**
 * 设置默认值
 * @param string $def
 * Author sakura 2016年7月5日下午3:44:42
 */
function iset($s, $def = '')
{
    return isset($s) ? $s : $def;
}

function isett($s, $def = '')
{
    return (isset($s) && null != $s && 'null' != $s && '' != $s && !empty($s)) ? $s : $def;
}

function isetn($s, $def = 0)
{
    return (isset($s) && null != $s && 'null' != $s && '' != $s) ? $s : $def;
}

function iseta($a, $s, $def = '')
{
    return isset($a[$s]) ? $a[$s] : $def;
}

function isetna($a, $s, $def = 0)
{
    return (isset($a[$s]) && null != $a[$s] && 'null' != $a[$s] && '' != $a[$s]) ? $a[$s] : $def;
}

function isetnz($a, $s)
{
    return (isset($a[$s]) && ('0' != $a[$s]));
}

/**
 * 获取数组默认值
 * @param array $data 数组
 * @param string $key
 * @param string $defaultVal
 * @return Ambigous <string, unknown>|string
 */
function output($data, $key, $defaultVal = '')
{
    if (isset($data)) {
        return isset($data[$key]) ? $data[$key] : $defaultVal;
    }
    return $defaultVal;
}

/**
 * 获取DDIC数组默认值
 * @param unknown $data
 * @param string $key
 * @param string $key1
 * @param string $key2
 * @return string|Ambigous
 * Author sakura 2016年7月8日下午5:30:11
 */
function outputDDIC($data, $key = '', $key1 = '', $key2 = '')
{
    $val = '';
    if ($key) {
        $val = output($data, $key);
        if ($key1) {
            $val = output($val, $key1);
            if ($key2) {
                $val = output($val, $key2);
            }
        }
    }
    return $val;
}

/**
 * @param unknown $str
 * @return string
 * Author sakura 2016年7月30日下午9:39:13
 */
function strnohtml($str = '')
{
    if (!$str) return '';
    return strip_tags($str);
}

/**
 * 剪切字符串 适合前台显示 -----先过滤所有html标签
 * 函数作用：将字符串剪切成为 $len 个汉字的长度
 * @param unknown $str字符串 (utf-8编码)
 * @param unknown $len长度（汉字长度）
 * @return string
 * Author sakura 2016年7月22日下午3:30:07
 */
function cutstrnohtml($str, $len, $dot = '...')
{
    $str = strnohtml($str);
    $str = trim_($str);
    return str_cut($str, $len * 2, $dot);
}

/**替换空格
 * @param $str
 * @return mixed|string
 */
function _cutstrnohtml($str, $len, $dot = '...')
{
    $str = cutstrnohtml($str, $len, $dot);
    $str = preg_replace('/&(nbsp);/i', '', $str);
    $str = preg_replace('/^\s+|\s+$/i', '', $str);
    return $str;
}

/**
 * 去除html空格与换行
 * @param $content
 * @return mixe
 */
function _nobrspinhtml($content)
{
    return preg_replace("/([\r\n])+|(<br\/>)+/", "", $content);
}

/**
 * 剪切字符串 适合前台显示
 * 函数作用：将字符串剪切成为 $len 个汉字的长度o
 * @param unknown $str字符串 (utf-8编码)
 * @param unknown $len长度（汉字长度）
 * @return string
 * Author sakura 2016年7月22日下午3:30:07
 */
function cutstr($str, $len, $dot = '...')
{
    $str = trim_($str);
    return str_cut($str, $len * 2, $dot);
}

/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($string, $length, $dot = '...')
{
    $strlen = strlen($string);
    if ($strlen <= $length) return $string;
    $string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strcut = '';
    if (strtolower('utf-8') == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < $strlen) {
            if (!isset($string[$n])) break;
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t < 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $length) break;
        }
        if ($noc > $length) $n -= $tn;
        $strcut = substr($string, 0, $n);
    } else {
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen - 1;
        for ($i = 0; $i < $maxi; $i++) {
            if (isset($string[$i]) && ord($string[$i]) > 127) {
                $strcut .= $string[$i] . $string[++$i];
            } else {
                $strcut .= $string[$i];
            }
        }
    }
    $strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
    //判断长度再加
    $newstrlen = strlen($strcut);
    if ($strlen <= $newstrlen) {
        return $strcut;
    }
    return $strcut . $dot;
}

/**
 * 检查权限--可以控制是否显示
 * @param unknown $url
 * @return boolean
 * Author sakura 2016年7月13日下午1:25:55
 */
function checkPopedom($url)
{
    //return true;
    $url = strtolower_($url);
    $menus = session(SESSION_ADMIN_USER_MENUS);
    if (!$menus) {
        return false;
    }
    foreach ($menus as $k => $v) {
        if (!iseta($v, 'rightMenus')) {
            return false;
        }
        foreach ($v['rightMenus'] as $k1 => $v1) {
            if (
                (iset($v1['menuUrl']) && strpos_($url, strtolower_($v1['menuUrl'])))
                ||
                (iset($v1['submitUrl']) && strpos_($url, strtolower_($v1['submitUrl'])))

            ) {
                return true;
            }
        }
    }
    return false;
}

/**
 * 取得随机纯数字 可指定几位
 * @param unknown $len
 * @param number $type
 * Author sakura 2016年7月5日下午3:40:19
 */
function generateCode($len, $type = 1)
{
    $code = '';
    if ($type == 1) $charset = '0123456789';
    elseif ($type == 2) $charset = 'ABCDEFGHKLMNPRSTUVWYZ3456789';
    elseif ($type == 3) $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';
    for ($i = 1, $cslen = strlen($charset); $i <= $len; ++$i) {
        $code .= strtoupper($charset{rand(0, $cslen - 1)});
    }
    $tmpCode = $code . 'x';
    if (strpos($tmpCode, '0') === 0) {
        return generateCode($len, $type);
    }
    return $code;
}

/**
 * 获取随机盐值
 * 明码
 * @param number $len
 * @param string $prefix
 * @return string
 * Author sakura 2016年7月6日上午11:31:49
 */
function getRundomSaltFigure($len = 10, $prefix = '')
{
    return $prefix . generateCode($len, 3);
}

/**
 * 获取【加密后的】随机盐值
 * @param number $len
 * @param string $prefix
 * @return string
 * Author sakura 2016年7月6日上午11:33:33
 */
function getRundomSaltFigureMd5($len = 10, $prefix = '')
{
    return md5($prefix . generateCode($len, 3));
}

/**
 * 用户密码加密
 * 在业务端先做密码非空判断
 * @param unknown $password
 * @param string $saltfigure
 * @return string
 * Author sakura 2016年7月5日下午3:43:52
 */
function getPasswordMd5($password, $saltfigure = '')
{
    if (!iset($password)) {
        return '';
    }
    $password = md5($saltfigure . $password);
    /* import('Encoder');
    $Encoder = new Encoder();
    $password = $Encoder->getEncoder($saltfigure.$password); */
    return $password;
}

/**
 * 获取json字符串
 * 以tp5中json为基础
 * @param number $code
 * @param string $msg
 * @param unknown $data
 * @param $url 跳转地址
 * Author sakura 2016年7月6日上午11:15:00
 */
function getJsonStr($code = 511, $msg = 'error', $data = array(), $url = '')
{
    /* {
        code:xxx,
        msg:xxx,
        data:{}
    } */
    return json(array('code' => $code, 'msg' => $msg, 'data' => $data, 'url' => $url));
}

/**
 * 成功JSON
 * @param $msg
 * @param array $data
 * @param $url 跳转地址
 * Author sakura 2016年7月6日下午5:19:16
 */
function getJsonStrSuc($msg = '', $data = array(), $url = '')
{
    return getJsonStr(200, $msg, $data, $url);
}

/**
 * 成功JSON，木有消息
 * @param mixed $data
 */
function getJsonStrSucNoMsg($data = [], $url = '')
{
    return getJsonStrSuc('', $data, $url);
}

/**
 * "page":1,"count":"1","pcount":1,"data":
 * @param unknown $page
 * Author sakura 2016年7月29日上午10:43:08
 */
function getJsonPage($page = null, $data = [], $currentPage = 10, $setTotalpage = '')
{
    $re = [];
    if ($page) {
        $re['data'] = $page->items()->all();//内容
        $re['page'] = $page->currentPage();//当前页数
        $re['pcount'] = $page->lastPage();//最后一页
        if ($setTotalpage != '') {
            $re['pcount'] = $setTotalpage;//最后一页
        }
        $re['count'] = $page->total();//总条数//$page->listRows();
    } else {
        $re['data'] = [];//内容
        $re['page'] = $currentPage;//当前页数
        $re['pcount'] = $currentPage;//最后一页
        $re['count'] = 0;//总条数//$page->listRows();
    }
    if ($data) {
        foreach ($data as $k => $v) {
            $re[$k] = $v;
        }
    }
    return getJsonStrSucNoMsg($re);
}

/**
 * 操作失败JSON
 * @param string $msg
 * @param number $code
 * Author sakura 2016年7月8日下午3:48:26
 */
function getJsonStrError($msg = 'error', $code = 511, $url = '', $data = [])
{
    return getJsonStr($code, $msg, $data, $url);
}

function getJsonStrErrorUrl($msg = "error", $code = 511, $url = '', $data = [])
{
    return getJsonStr($code, $msg, $data, $url);
}

/**
 * 获取指定时间
 * @param unknown $timestamp
 * Author sakura 2016年7月8日下午5:52:35
 */
function getDate_($timestamp = null)
{
    return getDateByFormat('Y-m-d H:i:s', $timestamp);
}

/**
 * 获取指定样式的指定时间
 * @param string $format
 * @param string $timestamp
 * @return string
 * Author sakura 2016年7月8日下午5:52:38
 */
function getDateByFormat($format = 'Y-m-d H:i:s', $timestamp = null)
{
    if (!$timestamp) $timestamp = time();
    return date($format, $timestamp);
}

function getDateStr($dateStr, $format = 'Y-m-d H:i:s')
{
    if (!$dateStr) return '';
    return getDateByFormat($format, strtotime($dateStr));
}

function getDateStrYmd($dateStr)
{
    return getDateStr($dateStr, 'Y-m-d');
}

function getDateStrYmdHi($dateStr)
{
    return getDateStr($dateStr, 'Y-m-d H:i');
}

function formatResetTime($et = '2013-11-04 11:42:43', $f = 'dhm', $nt = '')
{
    $date1 = date_create($et);
    if (!$date1) {
        return '未知';
    }
    $e = floatval(date_format($date1, 'U'));
    $n = floatval(date_format(date_create($nt), 'U'));
    $i_i = 60;
    $i_h = 3600;
    $i_d = $i_h * 24;
    if ($f == 'dhm') {
        if (($t = $e - $n) <= 0) return '1分';
        $s = '';
        $d = intval($t / $i_d);
        if ($d > 0) {
            $s .= $d . '天';
        }
        $t = $t % $i_d;
        $h = intval($t / $i_h);
        if ($h > 0) {
            $s .= $h . '小时';
        }
        $t = $t % ($i_h);
        $h = intval($t / $i_i);
        if ($h > 0) {
            $s .= $h . '分';
        }
        return $s;
    } elseif ($f == 'd/his') {
        if (($t = $e - $n) <= 0) return '1秒';
        $t = abs($e - $n);
        $s = '';
        $d = intval($t / $i_d);
        if ($d > 0) {
            $s .= $d . '天';
            return $s;
        }
        $t = $t % $i_d;
        $h = intval($t / $i_h);
        if ($h > 0) {
            $s .= $h . '时';
        }
        $t = $t % ($i_h);
        $h = intval($t / $i_i);
        if ($h > 0) {
            $s .= $h . '分';
        }
        $t = $t % ($i_i);
        if ($t > 0) {
            $s .= $t . '秒';
        }
        return $s;
    } elseif ($f == 'd') {
        if (($t = $e - $n) <= 0) return '0';
        $t = abs($e - $n);
        $s = '0';
        $d = intval($t / $i_d);
        if ($d > 0) {
            $s = $d;
            return $s;
        }
        return $s;
    } elseif ($f == 'y') {
        $t = $e - $n;
        return intval($t / (360 * $i_d));
    } elseif ($f == 'h') {
        if (($t = $e - $n) <= 0) return '0';
        $t = abs($e - $n);
        $s = '0';
        $d = intval($t / $i_h);
        if ($d > 0) {
            $s = $d;
            return $s;
        }
        return $s;
    }
}

/**
 * 获取IP
 * Author sakura 2016年7月13日下午2:36:50
 */
function getIp()
{
    return $_SERVER["REMOTE_ADDR"];
}

/**
 * 判断字符串是否包含
 * @param unknown $haystack 原字符串
 * @param string $needle 要查找的字符串
 * @param unknown $offset
 * Author sakura 2016年7月13日下午2:37:01
 */
function strpos_($haystack, $needle = ',', $offset = null)
{
    if (!iset($haystack)) {
        return -1;
    }
    return strpos('_' . $haystack, $needle, $offset);
}

/**
 * 转换成小写
 * @param unknown $str
 * Author sakura 2016年8月9日上午11:21:54
 */
function strtolower_($str)
{
    if (!iset($str)) {
        return '';
    }
    return strtolower($str);
}

/**
 *
 * @param unknown $str
 * @return string
 * Author sakura 2016年8月18日下午4:44:24
 */
function trim_($str)
{
    if (!iset($str)) {
        return '';
    }
    return trim($str);
}

function mb_strlen_($str, $charset = 'utf-8', $toreplace = false)
{
    if ($str && $toreplace) {
        $str = str_replace("\r\n", "", $str);
        $str = str_replace("&nbsp;", "", $str);
        $str = strnohtml($str);
        $str = trim_($str);
    }
    $len = mb_strlen($str, $charset);
    return $len;
}


function fileUp($file)
{
    if ($file['size'] > 2097152) {
        echo '图片大小不能超过2M';
        exit;
    }
    //取文件扩展名,判断扩展名
    $type = strtolower(substr($file["name"], strrpos($file["name"], '.')));
    if (!in_array($type, array('.gif', '.jpg', '.jpeg', '.png'))) {
        echo '图片格式不对！';
        exit;
    }
    //判断mime文件类型
//	$uptypes = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif',);
//	if (!in_array($file["type"], $uptypes)) {
//		echo "文件类型不符!" . $file["type"];
//		exit;
//	}

    $rand = mt_rand(100, 999);
    $pics_0 = date("YmdHis") . $rand;
    //原图名
    $pics = $pics_0 . $type;
    //缩略图名
    //上传路径
    $p = config('img_path');
    //图片上级目录
    if (!is_dir($p)) {
        mkdir($p);
    }
    //图片目录
    $date = date("Ymd");
    if (!is_dir($p . '/' . $date)) {
        mkdir($p . '/' . $date);
    }
    //图片路径,缩略图路径
    $savepath = $date . '/' . $pics;
    //上传图片
    move_uploaded_file($file['tmp_name'], $p . '/' . $savepath);
    //获取图片大小
    $picsize = getimagesize($p . '/' . $savepath);
    $arr = array(
        'picname' => $pics,
        'picpath' => $savepath,
        'picsize' => $picsize,
    );
    return $savepath;
}

//多图片上传
function fileUps($files)
{
    $pic = array();
    //循环多个上传文件,上传
    foreach ($files as $file) {
        $pic[] = fileUp($file);
    }
    $pic = implode(',', $pic);
    return $pic;
}

function fileDel($path)
{
    $p = config('img_path');
    $a = $p . '/' . $path;
    if (file_exists($a)) {
        if (unlink($a)) {
            return true;
        } else {
            return false;
        }
    }
    return false;
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl()
{
    if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}


/**
 * 判断是否为SAE
 */
function sp_is_sae()
{
    if (defined('APP_MODE') && APP_MODE == 'sae') {
        return true;
    } else {
        return false;
    }
}

function sp_file_write($file, $content)
{

    if (sp_is_sae()) {
        $s = new SaeStorage();
        $arr = explode('/', ltrim($file, './'));
        $domain = array_shift($arr);
        $save_path = implode('/', $arr);
        return $s->write($domain, $save_path, $content);
    } else {
        try {
            $fp2 = @fopen($file, "w");
            fwrite($fp2, $content);
            fclose($fp2);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

function checkStringLength($str = '', $maxLen, $name, $notempty = true)
{
    $strLen = mb_strlen($str, 'UTF-8');
    if ($strLen > $maxLen) {
        return "{$name}不能超过{$maxLen}字";
    }
    if ($notempty && 0 == $strLen) {
        return "{$name}不能为空";
    }
}

function checkChain4StringLength($str = '', $maxLen, $name, $chain = '', $notempty = true)
{
    if ($chain && mb_strlen($chain) > 1) {
        return $chain;
    }
    return checkStringLength($str, $maxLen, $name, $notempty);
}

//手机座机验证
function funcphone($str)//电话号码正则表达试
{
    return ((preg_match("/^0\d{2,3}-?\d{7,8}$/", $str) || preg_match("/^(\+86)?1[3|4|5|7|8]\d{9}$/", $str))) ? true : false;
}

function tlog($data, $title = false)
{
    $path = LOG_PATH;
    if (is_array($data)) {
        $tmp = "";
        foreach ($data as $k => $d) {
            $tmp .= "[{$k}:{$d}]";
        }
        $data = $tmp;
    } else {
        $data = "[{$data}]";
    }
    $now = date('Y-m-d H:i:s');
    if ($title) {
        $data = "[{$title}] {$data}";
    }
    $log = "[{$now}] $data";
    $log = str_replace("\r\n", "", $log) . "\r\n";
    $file = $path . date("Y-m-d") . "-pay-log.txt";
    file_put_contents($file, $log, FILE_APPEND);
}

function wx_tlog($data, $title = false)
{
    $path = LOG_PATH;
    if (is_array($data)) {
        $data = json_encode($data);
    } else {
        $data = "[{$data}]";
    }
    $now = date('Y-m-d H:i:s');
    if ($title) {
        $data = "[{$title}] {$data}";
    }
    $log = "[{$now}] $data";
    $log = str_replace("\r\n", "", $log) . "\r\n";
    $file = $path . date("Y-m-d") . "-wxmsg-log.txt";
    file_put_contents($file, $log, FILE_APPEND);
}

/**
 * 微信端的错误码转中文解释
 * @param array $return
 * @param string $more_tips
 * @return string
 */
function error_msg($return, $more_tips = '')
{
    $msg = array(
        '-1' => '系统繁忙，此时请开发者稍候再试',
        '0' => '请求成功',
        '40001' => '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口',
        '40002' => '不合法的凭证类型',
        '40003' => '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID',
        '40004' => '不合法的媒体文件类型',
        '40005' => '不合法的文件类型',
        '40006' => '不合法的文件大小',
        '40007' => '不合法的媒体文件id',
        '40008' => '不合法的消息类型',
        '40009' => '不合法的图片文件大小',
        '40010' => '不合法的语音文件大小',
        '40011' => '不合法的视频文件大小',
        '40012' => '不合法的缩略图文件大小',
        '40013' => '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写',
        '40014' => '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口',
        '40015' => '不合法的菜单类型',
        '40016' => '不合法的按钮个数',
        '40017' => '不合法的按钮个数',
        '40018' => '不合法的按钮名字长度',
        '40019' => '不合法的按钮KEY长度',
        '40020' => '不合法的按钮URL长度',
        '40021' => '不合法的菜单版本号',
        '40022' => '不合法的子菜单级数',
        '40023' => '不合法的子菜单按钮个数',
        '40024' => '不合法的子菜单按钮类型',
        '40025' => '不合法的子菜单按钮名字长度',
        '40026' => '不合法的子菜单按钮KEY长度',
        '40027' => '不合法的子菜单按钮URL长度',
        '40028' => '不合法的自定义菜单使用用户',
        '40029' => '不合法的oauth_code',
        '40030' => '不合法的refresh_token',
        '40031' => '不合法的openid列表',
        '40032' => '不合法的openid列表长度',
        '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
        '40035' => '不合法的参数',
        '40038' => '不合法的请求格式',
        '40039' => '不合法的URL长度',
        '40050' => '不合法的分组id',
        '40051' => '分组名字不合法',
        '40117' => '分组名字不合法',
        '40118' => 'media_id大小不合法',
        '40119' => 'button类型错误',
        '40120' => 'button类型错误',
        '40121' => '不合法的media_id类型',
        '40132' => '微信号不合法',
        '40137' => '不支持的图片格式',
        '41001' => '缺少access_token参数',
        '41002' => '缺少appid参数',
        '41003' => '缺少refresh_token参数',
        '41004' => '缺少secret参数',
        '41005' => '缺少多媒体文件数据',
        '41006' => '缺少media_id参数',
        '41007' => '缺少子菜单数据',
        '41008' => '缺少oauth code',
        '41009' => '缺少openid',
        '42001' => 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明',
        '42002' => 'refresh_token超时',
        '42003' => 'oauth_code超时',
        '43001' => '需要GET请求',
        '43002' => '需要POST请求',
        '43003' => '需要HTTPS请求',
        '43004' => '需要接收者关注',
        '43005' => '需要好友关系',
        '44001' => '多媒体文件为空',
        '44002' => 'POST的数据包为空',
        '44003' => '图文消息内容为空',
        '44004' => '文本消息内容为空',
        '45001' => '多媒体文件大小超过限制',
        '45002' => '消息内容超过限制',
        '45003' => '标题字段超过限制',
        '45004' => '描述字段超过限制',
        '45005' => '链接字段超过限制',
        '45006' => '图片链接字段超过限制',
        '45007' => '语音播放时间超过限制',
        '45008' => '图文消息超过限制',
        '45009' => '接口调用超过限制',
        '45010' => '创建菜单个数超过限制',
        '45015' => '回复时间超过限制',
        '45016' => '系统分组，不允许修改',
        '45017' => '分组名字过长',
        '45018' => '分组数量超过上限',
        '46001' => '不存在媒体数据',
        '46002' => '不存在的菜单版本',
        '46003' => '不存在的菜单数据',
        '46004' => '不存在的用户',
        '47001' => '解析JSON/XML内容错误',
        '48001' => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限',
        '50001' => '用户未授权该api',
        '50002' => '用户受限，可能是违规后接口被封禁',
        '61451' => '参数错误(invalid parameter)',
        '61452' => '无效客服账号(invalid kf_account)',
        '61453' => '客服帐号已存在(kf_account exsited)',
        '61454' => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)',
        '61455' => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)',
        '61456' => '客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)',
        '61457' => '无效头像文件类型(invalid file type)',
        '61450' => '系统错误(system error)',
        '61500' => '日期格式错误',
        '61501' => '日期范围错误',
        '9001001' => 'POST数据参数不合法',
        '9001002' => '远端服务不可用',
        '9001003' => 'Ticket不合法',
        '9001004' => '获取摇周边用户信息失败',
        '9001005' => '获取商户信息失败',
        '9001006' => '获取OpenID失败',
        '9001007' => '上传文件缺失',
        '9001008' => '上传素材的文件类型不合法',
        '9001009' => '上传素材的文件尺寸不合法',
        '9001010' => '上传失败',
        '9001020' => '帐号不合法',
        '9001021' => '已有设备激活率低于50%，不能新增设备',
        '9001022' => '设备申请数不合法，必须为大于0的数字',
        '9001023' => '已存在审核中的设备ID申请',
        '9001024' => '一次查询设备ID数量不能超过50',
        '9001025' => '设备ID不合法',
        '9001026' => '页面ID不合法',
        '9001027' => '页面参数不合法',
        '9001028' => '一次删除页面ID数量不能超过10',
        '9001029' => '页面已应用在设备中，请先解除应用关系再删除',
        '9001030' => '一次查询页面ID数量不能超过50',
        '9001031' => '时间区间不合法',
        '9001032' => '保存设备与页面的绑定关系参数错误',
        '9001033' => '门店ID不合法',
        '9001034' => '设备备注信息过长',
        '9001035' => '设备申请参数不合法',
        '9001036' => '查询起始值begin不合法'
    );

    if ($more_tips) {
        $res = $more_tips . ': ';
    } else {
        $res = '';
    }
    if (isset ($msg [$return ['errcode']])) {
        $res .= $msg [$return ['errcode']];
    } else {
        $res .= $return ['errmsg'];
    }

    $res .= ', 返回码：' . $return ['errcode'];

    return $res;
}

/**
 * 取一个二维数组中的每个数组的固定的键知道的值来形成一个新的一维数组
 *
 * @param $pArray 一个二维数组
 * @param $pKey 数组的键的名称
 * @return 返回新的一维数组
 */
function getSubByKey($pArray, $pKey = "", $pCondition = "")
{
    $result = array();
    if (is_array($pArray)) {
        foreach ($pArray as $temp_array) {
            if (is_object($temp_array)) {
                $temp_array = ( array )$temp_array;
            }
            if (("" != $pCondition && $temp_array [$pCondition [0]] == $pCondition [1]) || "" == $pCondition) {
                $result [] = ("" == $pKey) ? $temp_array : isset ($temp_array [$pKey]) ? $temp_array [$pKey] : "";
            }
        }
        return $result;
    } else {
        return false;
    }
}

function resizeimage($srcfile, $mySize)
{
    $size = getimagesize($srcfile);
    switch ($size[2]) {
        case 1:
            return false;
            $img = imagecreatefromgif($srcfile);
            break;
        case 2:
            $img = imagecreatefromjpeg($srcfile);
            break;
        case 3:
            $img = imagecreatefrompng($srcfile);
            break;
    }
    //源图片的宽度和高度
    $oldImg['w'] = imagesx($img);
    $oldImg['h'] = imagesy($img);
    //
    if (function_exists("imagecreatetruecolor")) {
        $dim = imagecreatetruecolor($mySize['w'], $mySize['h']); // 创建目标图gd2
    } else {
        $dim = imagecreate($mySize['w'], $mySize['h']); // 创建目标图gd1
    }
    imagecopyresampled($dim, $img, 0, 0, 0, 0, $mySize['w'], $mySize['h'], $oldImg['w'], $oldImg['h']);
    switch ($size[2]) {
        case 1:
            //$img=imagecreatefromgif($srcfile);
            header("Content-type: image/jpeg");
            imagejpeg($dim, $srcfile);
            break;
        case 2:
            header("Content-type: image/jpeg");
            imagejpeg($dim, $srcfile);
            break;
        case 3:
            header("Content-type: image/png");
            imagepng($dim, $srcfile);
            break;
    }
    //imagedestroy($dim);
    return true;
}

/**
 * 获取功德卡图片
 * @param unknown $tid
 * @param unknown $wid
 * @return string|boolean
 * Author sakura 2017年3月2日下午6:37:57
 */
function getWorshipCard($tid, $wid, $del = false)
{
    $baseurl = config('worshipcard_path');
    $type = config('worshipcard_type');
    if ($wid) {
        $baseurl .= $tid . '/';
    } else {
        $wid = $tid;
    }
    $dir = config('img_path') . $baseurl;
    $filename = $wid . '.' . $type;
    if ($del && file_exists($dir . $filename)) {
        unlink($dir . $filename);
    }
    if (file_exists($dir . $filename)) {
        return config('img_url') . $baseurl . $filename;
    }
    return false;
}

/**
 * 创建多级目录
 * @param string $dir
 * @return boolean
 */
function mkdirs($dir)
{
    if (!is_dir($dir)) {
        if (!mkdirs(dirname($dir))) {
            return false;
        }
        if (!mkdir($dir, 0777)) {
            return false;
        }
    }
    return true;
}

/**
 * 防超时的file_get_contents改造函数
 */
function wp_file_get_contents($url)
{
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 30
        )
    )); // 超时时间，单位为秒

    return file_get_contents($url, 0, $context);
}

/**
 * 获取文档封面图片
 *
 * @param int $cover_id
 * @param string $field
 * @return 完整的数据 或者 指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = null)
{
    if (empty ($cover_id))
        return false;

    $key = 'Picture_' . $cover_id;
    $picture = cache($key);

    if (!$picture) {
        $map ['state'] = 1;
        $map ['id'] = $cover_id;
        $picture = db('Picture')->where($map)->find();
        cache($key, $picture, 86400);
    }

    if (empty ($picture))
        return '';

    return empty ($field) ? $picture : $picture [$field];
}

function get_cover_url($cover_id, $width = '', $height = '')
{
    $info = get_cover($cover_id);
    $thumb = '';
    if ($width > 0 && $height > 0) {
        $thumb = "?imageMogr2/thumbnail/{$width}x{$height}";
    } elseif ($width > 0) {
        $thumb = "?imageMogr2/thumbnail/{$width}x";
    } elseif ($height > 0) {
        $thumb = "?imageMogr2/thumbnail/x{$height}";
    }
    if ($info ['url'])
        return $info ['url'] . $thumb;

    $url = $info ['path'];
    if (empty ($url))
        return '';
    return $url . $thumb;
}

/**
 * 生成带时间戳的静态文件地址
 * @param string $src /public/static/下的文件路径
 * @param string $tsType 时间戳类型，可选的有：time、md5，rand
 * @param string $tsKey url中时间戳参数key
 * @return string
 */
function getResource($src, $tsType = 'time', $tsKey = 'ts')
{
    $path = ROOT_PATH . '/public/static/';
    $file = $path . $src;
    if (file_exists($file)) {
        $ts = '?ts=';
        if ('time' === $tsType) {
            $ts .= filemtime($file);
        }
        if ('md5' === $tsType) {
            $ts .= md5_file($file);
        }
        if ('rand' == $tsType) {
            $ts .= mt_rand();
        }
        $result = "/static/" . $src . $ts;
        return $result;
    }
    return '';
}

function isWxBrowser()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    return !!(strpos($user_agent, 'MicroMessenger'));
// 	if (strpos($user_agent, 'MicroMessenger') === false) {
// 		return false;
// 	}else{
// 		return true;
// 	}
}

//对象转数组,使用get_object_vars返回对象属性组成的数组
function objectToArray($obj)
{
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if (is_array($arr)) {
        return array_map(__FUNCTION__, $arr);
    } else {
        return $arr;
    }
}

//数组转对象
function arrayToObject($arr)
{
    if (is_array($arr)) {
        return (object)array_map(__FUNCTION__, $arr);
    } else {
        return $arr;
    }
}

/**
 * 导出excel
 * @param string $data
 * @param string $filename
 * @param string $sheet
 */

function export_excl($data = '', $filename = '', $sheet = false)
{
    // Create new PHPExcel object
    import('excel.PHPExcel', EXTEND_PATH, '.php');

    $objPHPExcel = new \PHPExcel();
    // Set document properties
    $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
    $filename = empty ($filename) ? date('YmdHis') : $filename;
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename=' . $filename . '.xls');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0
    $Line = array(
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ'
    );
    if (!$sheet) {
        foreach ($data as $k => $v) {
            $u = $k + 1;
            $s = count($v);
            for ($i = 0; $i < $s; $i++) {
                $n = $Line[$i] . $u;
                $va = array_values($v);
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($n, $va[$i]);
            }
        }

        /*// Miscellaneous glyphs, UTF-8
         $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A4', 'Miscellaneous glyphs')
        ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
        */
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
    } else {
        $f = 0;
        foreach ($data as $t => $u) {
            foreach ($u as $k => $v) {
                $u = $k + 1;
                $s = count($v);
                for ($i = 0; $i < $s; $i++) {
                    $n = $Line[$i] . $u;
                    $va = array_values($v);
                    $objPHPExcel->setActiveSheetIndex($f)
                        ->setCellValue($n, $va[$i]);
                    if ($data[$t][$k][1] != $data[$t][$k - 1][1] && $k != 0) {
                        $objPHPExcel->getActiveSheet()->getStyle($n)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle($n)->getFill()->getStartColor()->setARGB('FFFF00');
                    }
                }
            }


            /*// Miscellaneous glyphs, UTF-8
             $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A4', 'Miscellaneous glyphs')
            ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');
            */
            // Rename worksheet
            $objPHPExcel->createSheet();
            $objPHPExcel->getSheet($f)->setTitle($t);
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex($f);
            $f++;
        }
        $f = 0;
    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');

}

function getDateStrBySelect($fastDaySelect, $start = '', $end = '')
{
    if (in_array($fastDaySelect, [0, 1, 2, 3, 4, 5])) {
        switch ($fastDaySelect) {
            // all
            case 0 :
                $start = '2017-01-26';
                $end = date('Y-m-d');
                break;
            // today
            case 1 :
                $start = $end = date('Y-m-d');
                break;
            // yesterday
            case 2 :
                $start = $end = date('Y-m-d', strtotime('-1 days', time()));
                break;
            // this month
            case 3 :
                $start = date('Y-m') . '-01';
                $end = date('Y-m-d');
                break;
            // last month
            case 4 :
                $start = date('Y-m-d', strtotime('-1 months', mktime(0, 0, 0, date('m'), 1)));
                $end = date('Y-m-d', strtotime('-1 days', mktime(0, 0, 0, date('m'), 1)));
                break;
            // this year
            case 5 :
                $start = date('Y') . '-01-01';
                $end = date('Y-m-d');
                break;
        }
        $start .= ' 00:00:00';
        $end .= ' 23:59:59';
    }
    $data = [
        'start' => $start,
        'end' => $end,
    ];
    return $data;
}

/**
 * getGender性别转换
 * @param $sex
 * @return string
 */
function getGender($sex)
{
    switch ($sex) {
        case 'Male':
            return '男';
        case 'Female':
            return '女';
        default:
            return '';
    }
    return '';
}

function datediff($beginDate, $endDate)
{
    $begin_time = strtotime($beginDate);
    $end_time = strtotime($endDate);
    return timediff($begin_time, $end_time);
}

function timediff($begin_time, $end_time)
{
    /* if ( $begin_time < $end_time ) {
        $starttime = $begin_time;
        $endtime = $end_time;
    } else {
        $starttime = $end_time;
        $endtime = $begin_time;
    } */
    $starttime = $begin_time;
    $endtime = $end_time;

    $timediff = $endtime - $starttime;
    $days = intval($timediff / 86400);
    $remain = $timediff % 86400;
    $hours = intval($remain / 3600);
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    $secs = $remain % 60;
    $res = array("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs);
    return $res;
}


/**
 *  日志函数
 * @param string $postion
 * @param string $info
 * @param array $log_config
 */
function tw_log($postion = '连接', $info = '', $log_config = [], $file = 'log_weixin/')
{
    if (!$file) {
        $file = 'log_weixin/';
    }

    $log = [
        'file_size' => 2097152,
        'type' => 'File',
        'path' => RUNTIME_PATH . $file,
    ];//TP5 log;
    $log = array_merge($log, $log_config);
    $path = $log['path'];
    $destination = $path . date('y_m_d') . '.log';
    !is_dir($path) && mkdir($path, 0755, true);

    //检测日志文件大小，超过配置大小则备份日志文件重新生成
    if (is_file($destination) && floor($log['file_size']) <= filesize($destination)) {
        rename($destination, dirname($destination) . DS . time() . '-' . basename($destination));
    }

    // 获取基本信息
    if (isset($_SERVER['HTTP_HOST'])) {
        $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    } else {
        $current_uri = "cmd:" . implode(' ', $_SERVER['argv']);
    }
    $runtime = microtime(true) - START_TIME;
    $reqs = number_format(1 / number_format($runtime, 8), 2);
    $runtime = number_format($runtime, 6);
    $time_str = " [运行时间：{$runtime}s] [吞吐率：{$reqs}req/s]";
    $memory_use = number_format((memory_get_usage() - START_MEM) / 1024, 2);
    $memory_str = " [内存消耗：{$memory_use}kb]";
    $file_load = " [文件加载：" . count(get_included_files()) . "]";

    $info1 = '[ log ] ' . $current_uri . $time_str . $memory_str . $file_load . "\r\n";

    if (is_array($info)) {
        $info = json_encode($info);
    }

    error_log(date('Y-m-d H:i:s') . "\t" . $postion . "\r\n" . $info1 . $info . "\r\n\r\n", 3, $destination);
}


/**
 * 将字符串参数变为数组
 * @param $query
 * @return array array
 */
function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

/**
 * 将参数变为字符串
 * @param $array_query
 * @return string string 'm=content&c=index&a=l'
 */
function getUrlQuery($array_query)
{
    $tmp = array();
    foreach ($array_query as $k => $param) {
        $tmp[] = $k . '=' . $param;
    }
    $params = implode('&', $tmp);
    return $params;
}

function trimall($str)
{
    $qian = array(" ", "　", "\t", "\n", "\r");
    return str_replace($qian, '', $str);
}