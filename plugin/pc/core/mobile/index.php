<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class IndexController extends PluginMobilePage
{
	/**
     * @var PcModel
     */
	public $model;

	/**
     * 渲染模板界面
     * @return string|void
     * @throws \Twig\Error\SyntaxError
     * @author: Vencenty
     * @time: 2019/5/27 21:02
     */
	public function main()
	{
		global $_W;
		global $_GPC;
		$data = $this->model->getData('home');
		$data['layout'] = $this->model->getTemplateSetting();
		$info = m('common')->getSysset('shop');
		$data['title'] = empty($info['name']) ? '小酷商城' : $info['name'];
		$data['seckill'] = plugin_run('seckill::getTaskSeckillInfo');
                $recommends = pdo_fetchall('select id,advname,link,thumb from ' . tablename('ewei_shop_pc_slide') . ' where uniacid=:uniacid and enabled=1 AND type=1 order by displayorder desc', array(':uniacid' => $_W['uniacid']));
		if (isset($_GET['debug'])) {
			print_r($this->model->getTemplateGlobalVariables());
			exit();
		}
		return $this->view('index', $data);
	}
        public function goods_list($args = array()) 
	{
		global $_GPC;
		global $_W;
		$_default = array('pagesize' => 10, 'page' => 1, 'isnew' => '', 'ishot' => '', 'isrecommand' => '', 'isdiscount' => '', 'istime' => '', 'keywords' => '', 'cate' => '', 'order' => 'id', 'by' => 'desc');
		$args = array_merge($_default, $args);
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		if ($merch_plugin && $merch_data['is_openmerch']) 
		{
			$args['merchid'] = intval($_GPC['merchid']);
		}
		if (isset($_GPC['nocommission'])) 
		{
			$args['nocommission'] = intval($_GPC['nocommission']);
		}
		$goods = m('goods')->getList($args);
		return $goods;
	}

	public function debug()
	{
		$r = $this->model->getData('home');
	}

	public function seckill()
	{
		$seckill_list = $this->model->invoke('seckill.index::get_list', false);
		$currentSecKillActivity = $seckill_list['times'][$seckill_list['timeindex']];
	}

	/**
     * 全局变量
     * @author: Vencenty
     * @time: 2019/5/27 19:09
     */
	public function globalVariables()
	{
		$r = $this->model->getTemplateGlobalVariables();
		print_r($r);
		exit();
	}

	/**
     * 获取二维码
     */
	public function getCode()
	{
		global $_W;
		global $_GPC;
		$id = $_GPC['id'];
		$url = mobileUrl($_GPC['url'], array('id' => $id), true);
		$qrcode = m('qrcode')->createQrcode($url);
		return json_encode(array('status' => 1, 'img' => $qrcode));
	}
}

?>
