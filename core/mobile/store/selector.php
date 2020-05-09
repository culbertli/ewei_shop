<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Selector_EweiShopV2Page extends MobilePage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		include $this->template();
	}

	public function get_list()
	{
		global $_W;
		global $_GPC;
		$psize = 20;
		$pindex = max(1, intval($_GPC['page']));
		$ids = trim($_GPC['ids']);
		$type = intval($_GPC['type']);
		$merchid = intval($_GPC['merchid']);
		$neer = intval($_GPC['isneer']);
		$lat = $_GPC['lat'];
		$lng = $_GPC['lng'];
		$keyword = $_GPC['keyword'];
		$condition = '';
		$params = array();

		if (!empty($ids)) {
			$condition = ' and id in(' . $ids . ')';
		}

		if ($type == 1) {
			$condition .= ' and type in(1,3) ';
		}
		else {
			if ($type == 2) {
				$condition .= ' and type in(2,3) ';
			}
		}

		if (!empty($keyword)) {
			$condition .= ' and `storename` like :keyword ';
			$params[':keyword'] = '%' . $keyword . '%';
		}

		if (!$neer) {
			$isneer = 0;

			if (0 < $merchid) {
				$list = pdo_fetchall('select * from ' . tablename('ewei_shop_merch_store') . ' where  uniacid=:uniacid and merchid=:merchid and status=1 ' . $condition . ' order by displayorder desc,id desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array_merge(array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid), $params));
				$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_merch_store') . ' where  uniacid=:uniacid and merchid=:merchid and status=1 ' . $condition, array_merge(array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid), $params));
			}
			else {
				$list = pdo_fetchall('select * from ' . tablename('ewei_shop_store') . ' where  uniacid=:uniacid and status=1 ' . $condition . ' order by displayorder desc,id desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array_merge(array(':uniacid' => $_W['uniacid']), $params));
				$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_store') . ' where  uniacid=:uniacid and status=1 ' . $condition, array_merge(array(':uniacid' => $_W['uniacid']), $params));
			}
		}
		else {
			$isneer = 1;

			if (0 < $merchid) {
				$list = pdo_fetchall('select *, ROUND(6378.138 * 2 * ASIN( SQRT(POW(SIN((:lat * PI() / 180 - lat * PI() / 180) / 2),2) + COS(:lat * PI() / 180) * COS(lat * PI() / 180) * POW(SIN((:lng * PI() / 180 - lng * PI() / 180) / 2),2))) * 1000) AS juli
                    from ' . tablename('ewei_shop_merch_store') . ' where  uniacid=:uniacid and merchid=:merchid and status=1 ' . $condition . ' order by juli asc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array_merge(array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid, ':lat' => $lat, ':lng' => $lng), $params));
				$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_merch_store') . ' where  uniacid=:uniacid and merchid=:merchid and status=1 ' . $condition, array_merge(array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid), $params));
			}
			else {
				$list = pdo_fetchall('select *,ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((:lat * PI() / 180 - lat * PI() / 180) / 2),2) + COS(:lat * PI() / 180) * COS(lat * PI() / 180) * POW(SIN((:lng * PI() / 180 - lng * PI() / 180) / 2),2))) * 1000) AS juli
                    from ' . tablename('ewei_shop_store') . ' where  uniacid=:uniacid and status=1 ' . $condition . ' order by juli asc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array_merge(array(':uniacid' => $_W['uniacid'], ':lat' => $lat, ':lng' => $lng), $params));
				$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_store') . ' where  uniacid=:uniacid and status=1 ' . $condition, array_merge(array(':uniacid' => $_W['uniacid']), $params));
			}
		}

		show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize));
	}
}

?>
