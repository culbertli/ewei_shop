<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//QQ571891417
if (!defined('IN_IA')) {
	exit('Access Denied');
}
$allow_origin = array('http://www.huiyichina.net','http://wq.huiyichina.net','http://api.hiuyichina.net','http://localhost:8080');
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allow_origin)) {
	header('Access-Control-Allow-Headers:Origin,X-Requested-With,Content-Type,Accept,Authorization,cancelload');
	header('Access-Control-Allow-Credentials:true');
	header('Access-Control-Allow-Method:POST,GET,OPTIONS');
	header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
}
class Mall_EweiShopV2Page extends MobilePage
{
	public $attach_path;
        public function __construct(){
            $this->attach_path='attachment/';
        }
        public function main(){
            var_dump(26);
            die;
        }
        //获取首页信息
        public function getIndexInfo(){
            global $_W;
            global $_GPC;
            $uniacid = $_W['uniacid'];
            $appsql = '';
            if ($this->iswxapp) {
                    $appsql = ' and iswxapp = 1';
            }
            $postData= json_decode(htmlspecialchars_decode($_POST));
            return show_json(1,array('message'=>'数据请求成功！','other'=>$_POST));
            if($postData['se']):
                $data=array();
                $advs = pdo_fetchall('select id,advname,link,thumb from ' . tablename('ewei_shop_adv') . ' where uniacid=:uniacid and iswxapp=0 and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
                $data['advs']=$this->setMobileUrl($advs);
                $navs = pdo_fetchall('select id,navname,url,icon from ' . tablename('ewei_shop_nav') . ' where uniacid=:uniacid' . $appsql . ' and status=1 order by displayorder desc', array(':uniacid' => $uniacid));
                $data['navs']=$this->setMobileUrl($navs,'icon','url');
                //获取快装单品信息
                $data['goods1']=$this->getGoodsByCates($postData['goods']['goods1']);
                return show_json(1,array('data'=>$data,'message'=>'数据请求成功！','other'=>$postData));
            endif;
            return show_json(0,array('data'=>null,'message'=>'参数非法！'));
        }
        /*
            *@params:$cates:Array  $lim:int
         *  *@return:array()
         *          */
        public function getGoodsByCates($cates=null,$lim=6){
            global $_W;
            $uniacid = $_W['uniacid'];
            $goods=array('data'=>[],'cates'=>[]);
            foreach($cates as $ck => $cate){
                $query = 'select id,title,thumb,minprice,total from ' . tablename('ewei_shop_goods') . ' where uniacid = ' . $uniacid . ' and deleted = 0 '.($cate?'and FIND_IN_SET('.$cate.', cates)>0':'').' limit '.$lim;
                $goods['data'][] = $this->setMobileUrl(pdo_fetchall($query));
                $goods['cates'][] = pdo_get('ewei_shop_category',array('id'=>$cate,'uniacid'=>$_W['uniacid']),array('id','name'));
            }
            
            return $goods;
        }
        /*
            *@params:*
         *  *@return:array()
         *          */
        //http://wq.com/app/index.php?i=1&c=entry&m=ewei_shopv2&do=mobile&r=culbert.mall.getGoodsByCate
        public function getGoodsByCate(){
            global $_W,$_GPC;
            $cate=$_GPC['cate']?$_GPC['cate']:1185;
            $page=$_GPC['page']?$_GPC['page']-1:0;
            $pageNum=15;
            $uniacid = $_W['uniacid'];
            $goods=array('data'=>[],'cate'=>[],'page'=>$_GPC['page']);
            $query = 'select id,title,thumb,minprice,total from ' . tablename('ewei_shop_goods') . ' where uniacid = ' . $uniacid . ' and deleted = 0 '.($cate?'and FIND_IN_SET('.$cate.', cates)>0':'').' limit '.$page*$pageNum.','.$pageNum ;
            $goods['data'] = $this->setMobileUrl(pdo_fetchall($query));
            $goods['cate'] = pdo_get('ewei_shop_category',array('id'=>$cate,'uniacid'=>$_W['uniacid']),array('id','name'));
            return show_json(1,$goods);
        }
        
        public function setMobileUrl($data=[],$imgField='thumb',$linkField='link'){
            global $_W;
            if($data):
                foreach($data as $k => $v){
                    if($v[$linkField]){
                        $data[$k][$linkField]=str_replace('./index',$_W['siteroot'].'app/index',$v[$linkField]);
                    }
                    if($v[$imgField]):
                        $data[$k][$imgField]=$_W['siteroot'].$this->attach_path.$v[$imgField];
                    endif;
                }
                return $data;
            endif;
        }

}

?>
