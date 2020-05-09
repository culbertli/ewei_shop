<?php
class Culbert_EweiShopV2Model
{
        public $shop_need_id;//购物需要完善会员资料标识:true为需要，false为不需要
        public $show_member_price;//是否显示列表商品会员价,true为显示，false为不显示
        public $member_discount;//会员折扣值，可填0-10,0表示使用会员本事的折扣
        public function __construct() {
            //$this->shop_need_id=true;
            $this->show_member_price=true;
            $this->member_discount=6;
        }
    
    /*1
        *object_to_array：递归将对象全部转化为数组
    1*/
    public function object_to_array($obj){
        $_arr=is_object($obj)?get_object_vars($obj):$obj;
        $arr = null;
        foreach($_arr as $key=>$val){
            $val=(is_array($val))||is_object($val)?$this->object_to_array($val):$val;
            $arr[$key]=$val;
        }
        return $arr;
    }
        /*2
        *dealRichTextInfo：处理详情页模板中的富文本信息
                    *@diypage_id（必）:详情页模板中的id
                *return null / string
    2*/
        public function dealRichTextInfo($diypage_id=0){
            $diypageInfo_result=null;
            //获取详情页模板信息
            $diypageInfo=pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_diypage') . ' WHERE id = '.$diypage_id);
            if($diypageInfo){
                //对详情页模板中的数据进行解码
                $diypageEncode=m('culbert')->object_to_array(json_decode(base64_decode($diypageInfo['data'])));
                //对解码后的详情页数据进行循环，判断是否有富文本
                foreach($diypageEncode['items'] as $k => $v){
                    if(in_array('richtext',$v)){//找到详情中的富文本内容
                         $diypageInfo_result=base64_decode($v['params']['content']);
                         break;//结束循环
                    }
                }
            }
            return m('common')->html_images($diypageInfo_result);
        }
        

    /*3
        *getDiyPageByGood：通过商品获取对应的详情页模板的信息
                    *@good_id（必）:商品id,0为新增，大于0为编辑
                    *@diypage_id（必）:详情页模板的id，一般通过商品数据中的diypage
                    *@origindetailContent （必）: 商品的详情信息。
                * return array;
        *注：本函数的处理在于：假如商品自身有详情、模板中也有富文本详情，以自身的详情为准
        *两个参数的查询要分开，以免有些用户要自定义
    3*/
    public function getDiyPageByGood($good_id=0,$diypage_id=0,$origindetailContent=''){
            global $_GPC;
            $_GPC['li_all_goods_id']=1185;//自定义【全部商品】分类id，本框架的id：1185
            $diypageInfo_result=null;
            $isUseDiyPage=0;
            if($good_id){//根据商品id获取详情页模板id
                $good_info=pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_goods') . ' WHERE id = '.$good_id);
                $origindiypage_id=$good_info['diypage'];//获得原始的diypage
            }else{//假如是新增，就直接设定为0
                $origindiypage_id=0;
            }
            if($origindetailContent&&($origindiypage_id==$diypage_id||$good_id===0)){//1.自身有富文本详情，且编辑前后的详情页模板相同（新增商品无需比较），以自身的为准
                $diypageInfo_result=$origindetailContent;
                if($diypage_id){//应用自身详情前，比较一下自身内容与模板内容是否相同，相同的话，做好应用标识
                    $compareInfo=$this->dealRichTextInfo($diypage_id);
                    $isUseDiyPage=$compareInfo==$diypageInfo_result?1:0;
                }
            }else{//2.商品本身没有详情
                if(!$diypageInfo_result&&$diypage_id){
                    //获取详情页模板信息
                    $diypageInfo_result=$this->dealRichTextInfo($diypage_id);
                    $isUseDiyPage=$diypageInfo_result?1:0;//详情页模板有内容，做好应用标识

                }
                
            }
        
       return array(0=>$diypageInfo_result,1=>$isUseDiyPage);
    }
        /*4
        *associateDiyPage：修改详情页模板时，自动修改关联的商品详情信息
                    *@diypage_id（必）:详情页模板中的id
                *return ~
    4*/
        public function associateDiyPage($diypage_id){
            //获取详情页模板信息
            $diypageInfo_result=$this->dealRichTextInfo($diypage_id);
            if($diypageInfo_result){
                //查询出应用了该模板的商品id
                $goods = pdo_fetchall("SELECT id FROM ".tablename('ewei_shop_goods')." where diypage=:diypage and usediypage=:usediypage", array(':diypage'=>$diypage_id,':usediypage'=>1), 'id');
                foreach($goods as $goods_key){//循环将更新的详情页内容插入到商品详情中
                    pdo_update('ewei_shop_goods', array('content'=>$diypageInfo_result), array('id' => $goods_key));
                }
            }
        }
        /*5
        *selectGoodsListByUrl：通过url的参数选择商品列表
                    *@openStatus:boolen 是否开启
                *return ~
    5*/
        public function selectGoodsListByUrl($openStaus=true){
            global $_GPC,$_W;
            if($openStaus&&$_GPC["li_usecate"]&&$_GPC["li_cateid"]){//判断开启状态/是否使用/分类id
                //查询获取商品分类信息,作为分享用
                $cateInfo = pdo_fetchall("SELECT name,description,thumb,advimg FROM ".tablename('ewei_shop_category')." where id=:id", array(':id'=>$_GPC["li_cateid"]));
                $thumb=$cateInfo[0]['thumb']?$cateInfo[0]['thumb']:$cateInfo[0]['advimg'];
                if($cateInfo){//有信息，将写入全局GPC
                    $_GPC['cate']=$_GPC["li_cateid"];//用以获取商品列表
                    $_GPC['li_page']=array('title'=>$cateInfo[0]['name'],'desc'=>$cateInfo[0]['description']);//页面tdk信息
                    $_GPC['li_shrea']=array(//用以微信分享
                        'title'=>$cateInfo[0]['name'],
                        'imgUrl'=>$thumb?$_W["siteroot"].'attachment/'.$thumb:null,
                        'desc'=>$cateInfo[0]['description'],
                        'link'=>$_W['siteurl']
                    );
                }

            }
        }
        /*6
        *shopNeedIdentification：购物是否需要完善会员资料
                    *@member 会员信息 ：一般外部的获取方式为：$member = m('member')->getMember($_W['openid']);
                    *@_that（必）:obj，用以执行message函数
                *return ~
        6*/
        public function shopNeedIdentification($member=null,$_that){
            global $_W;
            if($this->shop_need_id){//如果配置购物需要完善资料
                $member=$member?$member:m('member')->getMember($_W['openid']);
                if (empty($member['realname'])&&empty($member['mobile'])) {
                    $_that->message('需要您完善资料才能继续操作!', mobileUrl('member/info', array('returnurl' => $returnurl)), 'info');
                }elseif(empty($member['realname'])){
                    $_that->message('需要您填写姓名才能继续操作!', mobileUrl('member/info', array('returnurl' => $returnurl)), 'info');
                }elseif(empty($member['mobile'])){
                    $_that->message('需要您绑定手机才能继续操作!', mobileUrl('member/info', array('returnurl' => $returnurl)), 'info');
                }
            }
        }
        /*7
        *getCurrentMemberDiscount：获取当前会员的折扣
                *return int
        7*/
        public function getCurrentMemberDiscount(){
            global $_W;
            $li_sql="select b.discount from ".tablename('ewei_shop_member_level').' as b left join '.tablename('ewei_shop_member').' as a on a.level=b.id where a.id='.$_W['ewei_shopv2_member']['id'];
            $li_discount=pdo_fetch($li_sql);
            return $li_discount["discount"];
        }
        /*8
        *getPageInfoOfMerchRegister：获取多商户申请的页面信息（tdk、分享信息等）
                *$id , 对应的表单id，可以通过mysql追踪器在后台设置申请入驻时查询表：ims_cover_reply获得
        8*/
        public function getPageInfoOfMerchRegister($id=13){
            global $_W,$_GPC;
            $page_info=pdo_fetch('SELECT * FROM ' . tablename('cover_reply') . ' WHERE id = '.$id);
            if($page_info){//有信息，将写入全局GPC
                $_GPC['li_page']=array('title'=>$page_info['title'],'desc'=>$page_info['description']);//页面tdk信息
                $_GPC['li_shrea']=array(//用以微信分享
                    'title'=>$page_info['title'],
                    'imgUrl'=>$page_info['thumb']?$_W["siteroot"].'attachment/'.$page_info['thumb']:null,
                    'desc'=>$page_info['description'],
                    'link'=>$_W["siteurl"]
                );
            }
        }
        
        
}

if (!defined('IN_IA')) {
    exit('Access Denied');
        
}

?>
