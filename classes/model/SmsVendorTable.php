<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\classes\model;

use sms\classes\Sms;
use wulaphp\db\Table;

class SmsVendorTable extends Table {
	protected $autoIncrement = false;

	/**
	 * 供应商列表.
	 *
	 * @return array
	 */
	public function vendors() {
		$apps = Sms::vendors();
		$data = [];
		if ($apps) {
			$ids  = array_keys($apps);
			$sql  = $this->find(['id IN' => $ids]);
			$list = $sql->toArray(null, 'id');
			foreach ($apps as $id => $app) {
				$list[ $id ]['id']      = $id;
				$list[ $id ]['name']    = $app->getName();
				$list[ $id ]['desc']    = $app->getDesc();
				$list[ $id ]['hasForm'] = $app->getForm() ? true : false;
				$data[]                 = $list[ $id ];
			}
		}

		return $data;
	}

	public function newVendor($vendor) {
		try {
			return $this->insert($vendor);
		} catch (\Exception $e) {
			return false;
		}
	}

	public function updateVendor($vendor) {
		return $this->update($vendor, ['id' => $vendor['id']]);
	}

	/**
	 * 更新状态.
	 *
	 * @param string|int   $status
	 * @param array|string $ids
	 * @param bool         $check
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function updateStatus($status, $ids, $check = true) {
		$vendors = Sms::vendors();
		$status  = intval($status);
		foreach ((array)$ids as $id) {
			if (!isset($vendors[ $id ])) {
				continue;
			}
			$v = $vendors[ $id ];
			if ($check && !$v->canEnable()) {
				throw_exception($v->getError());
			}
			if ($this->exist(['id' => $id])) {
				$this->update(['status' => $status], $id);
			} else {
				$this->insert(['id' => $id, 'status' => $status]);
			}
		}

		return true;
	}

	public function updatePriority($id, $priority) {
		$vendors  = Sms::vendors();
		$priority = intval($priority);

		if (!isset($vendors[ $id ])) {
			return false;
		}

		if ($this->exist(['id' => $id])) {
			$this->update(['priority' => $priority], $id);
		} else {
			$this->insert(['id' => $id, 'priority' => $priority]);
		}

		return true;
	}

	/**
	 * 随机获取一个可用的短信通道.
	 *
	 * @return \sms\classes\SmsVendor[]
	 */
	public function getAvailableVendors() {
		$vendors = [];
		$ids     = $this->findAll(['status' => 1], 'id')->desc('priority')->toArray('id');
		if ($ids) {
			$apps = Sms::vendors();
			foreach ($ids as $id) {
				if (isset($apps[ $id ])) {
					$vendors[ $id ] = $apps[ $id ];
				}
			}
		}

		return $vendors;
	}
}