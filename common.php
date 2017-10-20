<?php

use think\Db;

/**
 * 获取分类所有子分类
 * @param int $cid 分类ID
 * @return array|bool
 */
function get_category_children($cid)
{
    if (empty($cid)) {
        return false;
    }

    $children = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->select();

    return array2tree($children);
}

/**
 * 根据分类ID获取文章列表（包括子分类）
 * @param int   $cid   分类ID
 * @param int   $limit 显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|false|PDOStatement|string|\think\Collection
 */
function get_articles_by_cid($cid, $limit = 10, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->limit($limit)->select();

    return $article_list;
}

/**
 * 根据分类ID获取文章列表，带分页（包括子分类）
 * @param int   $cid       分类ID
 * @param int   $page_size 每页显示条数
 * @param array $where     查询条件
 * @param array $order     排序
 * @param array $filed     查询字段
 * @return bool|\think\paginator\Collection
 */
function get_articles_by_cid_paged($cid, $page_size = 15, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->paginate($page_size);

    return $article_list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level;
            $list[]     = $v;
            array2level($array, $v['id'], $level + 1);
        }
    }

    return $list;
}

/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name]))
                $item[$child_key_name] = [];
            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function is_mobile()
{
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}
/**
 * 获取当前时间上一周的开始时间和结束时间；
 * 可以通过配置一周的开始时间，默认为星期一；
 * 使用方法，执行  extract(get_lastweek());
 * 之后，可以直接使用 $beginLastweek $endLastweek 两个变量；
 * 如果需要其他变量名称，可以修改参数 $begin 和 $end ，自行指定名称
 *
 * @param int       $week_start_num 一周的开始时间，默认为周一
 * @param int       $now_time       当前时间时间戳，这里做一个当前时间方便测试该方法的正确性
 * @param string    $begin          上一周开始时间的变量名称；
 * @param string    $end            上一周结束时间的变量名称；
 *
 * @author leeyi <leeyisoft@qq.com>
 * @return array()
 */
function get_lastweek($week_start_num = 1, $now_time = 0, $begin = 'beginLastweek', $end = 'endLastweek') {
    $now_time       = $now_time>0 ? $now_time : time();
    $now_weekday    = date('w', $now_time); // 获取当前是星期前 0-6 星期日-星期六
    $week_start_num = in_array($week_start_num, ['0','1','2','3','4','5','6']) ? $week_start_num : 1; // 默认一周开始时间为周一
    $now_weekday    = $now_weekday<$week_start_num ? $now_weekday+7 : $now_weekday;
    //php获取上周起始时间戳和结束时间戳
    $beginLastweek  = $now_time-($now_weekday+7-$week_start_num)*86400;
    $endLastweek    = $beginLastweek+(6*86400);

    return array(
//        $begin => strtotime(date('Y-m-d 00:00:00', $beginLastweek)),
//        $end   => strtotime(date('Y-m-d 23:59:59', $endLastweek))
        $begin => date('Y-m-d', $beginLastweek),
        $end   => date('Y-m-d', $endLastweek)
    );
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
function getJsonStr($code=511,$msg='error',$data=array(),$url=''){
    /* {
        code:xxx,
        msg:xxx,
        data:{}
    } */
    return json(array('code'=>$code,'msg'=>$msg,'data'=>$data,'url'=>$url));
}
/**
 * 成功JSON
 * @param $msg
 * @param array $data
 * @param $url 跳转地址
 * Author sakura 2016年7月6日下午5:19:16
 */
function getJsonStrSuc($msg='',$data=array(),$url=''){
    return getJsonStr(200,$msg,$data,$url);
}
/**
 * 成功JSON，木有消息
 * @param mixed $data
 */
function getJsonStrSucNoMsg ($data=[],$url='') {
    return getJsonStrSuc('',$data,$url) ;
}
/**
 * "page":1,"count":"1","pcount":1,"data":
 * @param unknown $page
 * Author sakura 2016年7月29日上午10:43:08
 */
function getJsonPage($page=null,$data=[],$currentPage=10,$setTotalpage=''){
    $re = [];
    if($page){
        $re['data'] = $page->items()->all();//内容
        $re['page'] = $page->currentPage();//当前页数
        $re['pcount'] = $page->lastPage();//最后一页
        if($setTotalpage!=''){
            $re['pcount'] = $setTotalpage;//最后一页
        }
        $re['count'] = $page->total();//总条数//$page->listRows();
    }else{
        $re['data'] = [];//内容
        $re['page'] = $currentPage;//当前页数
        $re['pcount'] = $currentPage;//最后一页
        $re['count'] = 0;//总条数//$page->listRows();
    }
    if($data){
        foreach ($data as $k=>$v){
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
function getJsonStrError($msg='error',$code=511,$url='',$data=[]){
    return getJsonStr($code,$msg,$data,$url);
}

/**
 * 获取上月日期
 * @param   $date  Y-m
 * @return array
 */
function getlastMonthDays($date){
    $timestamp=strtotime($date);
    $lastMonth=date('Y-m',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
    return $lastMonth;
}

/**
 * PHP获取某个月第一天/最后一天
 */

function getthemonth($date)
{
    $firstday = date('Y-m-01', strtotime($date));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return array($firstday, $lastday);
}