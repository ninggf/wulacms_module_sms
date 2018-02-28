<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\controllers;

use backend\classes\IFramePageController;
use sms\classes\model\SmsLog;
use sms\classes\Sms;

/**
 * Class LogController
 * @package sms\controllers
 * @acl     m:system/sms
 */
class LogController extends IFramePageController {
	public function index($id = '') {
		$data['id']     = $id;
		$data['groups'] = Sms::vendorsName();

		return $this->render($data);
	}

	public function data($q, $type, $time, $time1, $count) {
		$table = new SmsLog();
		$sql   = $table->select('*');
		$sql->sort()->page();
		$where = [];
		if ($q) {
			$where['phone'] = $q;
		}
		if ($type) {
			$where['vendor'] = $type;
		}
		if ($time) {
			$s_time                  = strtotime($time . ' 00:00:00');
			$where['create_time >='] = $s_time;
		}
		if ($time1) {
			$e_time                  = strtotime($time1 . ' 23:59:59');
			$where['create_time <='] = $e_time;
		}
		$sql->where($where);
		if ($count) {
			$data['total'] = $sql->total('id');
		}
		$data['rows']    = $sql->toArray();
		$data['vendors'] = Sms::vendorsName();
		$data['tpls']    = Sms::templatesName();

		return view($data);
	}
}