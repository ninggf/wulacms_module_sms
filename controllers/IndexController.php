<?php

namespace sms\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use sms\classes\model\SmsVendorTable;
use sms\classes\Sms;
use sms\classes\SmsSetting;
use wulaphp\app\App;
use wulaphp\io\Ajax;
use wulaphp\io\Response;
use wulaphp\util\ArrayCompare;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * 默认控制器.
 * @acl m:system/sms
 */
class IndexController extends IFramePageController {
	use JQueryValidatorController;

	public function index() {
		$data['cfg'] = App::cfg('@sms');

		return $this->render($data);
	}

	public function data($sort) {
		$app          = new SmsVendorTable();
		$data['rows'] = $app->vendors();
		usort($data['rows'], ArrayCompare::compare('priority', 'd'));
		usort($data['rows'], ArrayCompare::compare($sort['name'], $sort['dir']));

		return view($data);
	}

	public function setStatus($status, $ids = '') {
		$ids = explode(',', $ids);
		if (empty($ids)) {
			return Ajax::warn('请选择要停用的通道');
		}
		$app = new SmsVendorTable();
		try {
			$app->updateStatus($status, $ids);
		} catch (\Exception $e) {
			return Ajax::error($e->getMessage(), 'alert');
		}

		return Ajax::reload('#table', $status ? '启用成功' : '禁用成功');
	}

	public function changePriority($id, $value) {
		$app = new SmsVendorTable();
		try {
			$app->updatePriority($id, $value);
		} catch (\Exception $e) {
			return Ajax::error($e->getMessage(), 'alert');
		}

		return Ajax::reload('#table', '通道优先级调整成功');
	}

	public function setting($id, $value) {
		$setting = new SmsSetting();
		$setting->save([$id => $value], 'sms');

		return Ajax::success('');
	}

	public function cfg($id) {
		$apps = Sms::vendors();
		if (!isset($apps[ $id ])) {
			Response::error('通道' . $id . '不存在');
		}
		$table = new SmsVendorTable();
		$cfg   = $table->get($id)->get();
		if (!$cfg) {
			$cfg['id']     = $id;
			$cfg['status'] = 0;
			$table->newVendor($cfg);
		}
		$data    = ['id' => $id];
		$app     = $apps[ $id ];
		$aform   = $app->getForm();
		$options = $cfg['options'] ? @json_decode($cfg['options'], true) : false;
		if ($aform) {
			if ($options) {
				$aform->inflateByData($options);
			}
			$data['form'] = BootstrapFormRender::v($aform);
			if (method_exists($aform, 'encodeValidatorRule')) {
				$data['rules'] = $aform->encodeValidatorRule($this);
			}
		}

		return view($data);
	}

	public function save($id) {
		$apps = Sms::vendors();
		if (!isset($apps[ $id ])) {
			Response::error('通道' . $id . '不存在');
		}
		$table = new SmsVendorTable();
		$cfg   = $table->exist(['id' => $id]);
		if (!$cfg) {
			Response::error('通道' . $id . '不可用');
		}
		try {
			$appx           = $apps[ $id ];
			$aform          = $appx->getForm();
			$app['id']      = $id;
			$app['options'] = '';
			if ($aform) {
				$options = $aform->inflate();
				if ($options) {
					$app['options'] = json_encode($options);
				}
				$aform->validate();
			}
			$rst = $table->updateVendor($app);
			if ($rst) {
				return Ajax::reload('#table', '配置完成');
			} else {
				return Ajax::error('配置失败');
			}
		} catch (ValidateException $e) {
			return Ajax::validate('EditForm', $e->getErrors());
		} catch (\Exception $e) {
			return Ajax::error('配置失败:' . $e->getMessage());
		}
	}
}