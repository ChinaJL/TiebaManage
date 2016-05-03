<?php
header("Content-type: text/html; charset=utf-8");
$bduss = '';//吧务的BDUSS
$kw = '';//目标贴吧
$keywords = '淘宝';//关键词 格式 关键词1|关键词2|关键词3|......(使用正则表达式)
$block='1'; //1代表禁封id     0代表不禁封
/*
 * 作者：Giuem
 * 博客地址：http://giuem.qiniudn.com/
 * 转载请保留版权！
 */
 /***********************/
 $forum = get_forum();
$fid = $forum['forum']['id'];
foreach($forum['thread_list'] as $thread){
	if(check_ad($thread['title'])){ 
		if($block==1) blockid($thread['author']['name_show'],$fid);
		del_thread($kw,$fid,$thread['tid']);
		continue ; 
	}elseif($thread['abstract']){
		if(check_ad($thread['abstract'][0]['text'])){
			if($block==1) blockid($thread['author']['name_show'],$fid);
			del_thread($kw,$fid,$thread['tid']);
		}
	}
}
function get_forum(){
	global $kw;
    $data=array(
        '_client_id=wappc_1396611108603_817',
        '_client_type=2',
        '_client_version=5.7.0',
        '_phone_imei=642b43b58d21b7a5814e1fd41b08e2a6',
        'from=tieba',
        "kw={$kw}",
        'pn=1',
        'q_type=2',
        'rn=30',
        'with_group=1');
    $data=implode('&', $data).'&sign='.md5(implode('', $data).'tiebaclient!!!');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://c.tieba.baidu.com/c/f/frs/page');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $re = json_decode(curl_exec($ch),true); 
    curl_close($ch);
    return $re;
}
function get_tbs(){
	global $bduss;
	$re=json_decode(fetch('http://tieba.baidu.com/dc/common/tbs','BDUSS='.$bduss),true);
	return $re['tbs'];
}
function fetch($url,$cookie=null,$postdata=null){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	if (!is_null($postdata)) curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
	if (!is_null($cookie)) curl_setopt($ch, CURLOPT_COOKIE,$cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	$re = curl_exec($ch);
	curl_close($ch);
	return $re;
}
function check_ad($content){
	global $keywords;
	$preg = '/'.$keywords.'/i';
	$res = preg_match($preg,$content);
	return $res;
}
function del_thread($kw,$fid,$tid){
	global $bduss;
	$data = 'commit_fr=pb&ie=utf-8&tbs='.get_tbs()."&kw={$kw}&fid={$fid}&tid={$tid}";
	$re = json_decode(fetch('http://tieba.baidu.com/f/commit/thread/delete','BDUSS='.$bduss,$data),true);
	echo '删除帖子:'.$tid,$re['no']==0?'成功':'失败','<br />';
}
function blockid($id,$fid){
	global $bduss;
	$data='day=1&fid='.$fid.'&tbs='.get_tbs().'&ie=gbk&user_name[]='.$id.'&reason=';
	$re = json_decode(fetch('http://tieba.baidu.com/pmc/blockid','BDUSS='.$bduss,$data),true);
	echo '禁封'.$id,$re['errno']==0?'成功':'失败',' ';
}
?>