<?php
//QQ571891417
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ku_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
	}
        /*1
         * getMembersFromFans  从系统分析数据库中的人员信息放入会员表中
         *
         * 1 */
        public function getMembersFromFans(){
            die;
//            global $_W;
//            global $_GPC;
            //获取粉丝信息
            $fans = pdo_fetchall("SELECT * FROM ".tablename('mc_fans_tag'), array());
            foreach($fans as $fk => $fv){
                //检查在shop_member表中是否存在信息，不存在则插入
                $isExistMember=pdo_get('ewei_shop_member', array('uniacid'=>1,'openid'=>$fv['openid']),'id');
                if($fv['subscribe']==1&&$fv['headimgurl']&&!$isExistMember){//将已关注且未录入shop会员表的进行插入
                    //查询获得mc_members的id
                    $memberId=pdo_get('mc_members', array('uniacid'=>1,'avatar'=>$fv['headimgurl']),'uid');
                    $inser_z=pdo_insert('ewei_shop_member', array(
                        'uniacid' => 1,
                        'uid'  => $memberId["uid"],
                        'agentid' => 0,
                        'openid' => $fv['openid'],
                        'createtime' => time(),
                        'agenttime' => 0,
                        'clickcount' => 0,
                        'nickname' => $fv['nickname'],
                        'gender' => $fv['sex'],
                        'avatar' => $fv['headimgurl'],
                        'avatar_wechat' => $fv['headimgurl'],
                        'nickname_wechat' => $fv['nickname'],
                        '456wd_id' => 0,
                        'jpush' => ''
                    ), false);
                    var_dump($inser_z);
                }
            }
        }
        /*2
         *  addOptionToGoodsByCates 通过商品类别，给商品添加规格
         * 2*/
//        public function addOptionToGoodsByCates($cates=[],$otpInfo=[]){
//            global $_W;
//            $uniacid = $_W['uniacid'];
//            $cates = $cates?$cates:[1176];
//            $requireFields=['title','thumb'];
//            $otpInfo = $otpInfo['title']?$otpInfo['title']:'';
//        }
        
        
        
}       

?>
