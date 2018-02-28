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
use sms\classes\model\SmsTplTable;
use sms\classes\Sms;
use wulaphp\io\Ajax;
use wulaphp\io\Response;

/**
 * Class TplController
 * @package sms\controllers
 * @acl     m:system/sms
 */
class TplController extends IFramePageController {
	public function index($id) {
		$apps = Sms::vendors();
		if (!isset($apps[ $id ])) {
			Response::respond(404, '通道' . $id . '不存在');
		}
		$app = $apps[ $id ];

		$table                = new SmsTplTable();
		$tpls                 = $table->templates($id);
		$data                 = ['id' => $id, 'tpls' => $tpls];
		$data['hasVendorTpl'] = $app->usePlatformTemplate();

		return $this->render($data);
	}

	public function saveTpl($id, $tpl, $content, $interval) {
		$apps = Sms::vendors();
		if (!isset($apps[ $id ])) {
			Response::error('通道' . $id . '不存在');
		}
		$table = new SmsTplTable();
		if ($table->updateTpl($id, $tpl, $content, $interval)) {
			return Ajax::success('模板已保存');
		} else {
			Response::error('模板保存失败');

			return null;
		}
	}
}