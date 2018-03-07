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

class SmsTplTable extends Table {
	protected $autoIncrement = false;
	protected $primaryKeys   = ['id', 'tpl'];

	public function getTemplate($id, $tpl) {
		$rst = $this->get(['id' => $id, 'tpl' => $tpl], '*')->get();

		if ($rst) {
			$rst['exp'] = $rst['interval'];
			$rst['cnt'] = $rst['content'];
		} else {

			$rst['exp'] = 120;
			$rst['cnt'] = '';
		}

		return $rst;
	}

	/**
	 * @param string $id 供应商ID
	 *
	 * @return array
	 */
	public function templates($id) {
		$apps = Sms::templates();
		$data = [];
		if ($apps) {
			$sql  = $this->find(['id' => $id]);
			$list = $sql->toArray(null, 'tpl');
			foreach ($apps as $tpl => $app) {
				$list[ $tpl ]['tpl']      = $tpl;
				$list[ $tpl ]['name']     = $app->getName();
				$list[ $tpl ]['template'] = $app->getTemplate();
				$list[ $tpl ]['args']     = $app->getArgsDesc();
				$data[]                   = $list[ $tpl ];
			}
		}

		return $data;
	}

	public function updateTpl($id, $tpl, $content, $interval) {
		try {
			$where = ['id' => $id, 'tpl' => $tpl];
			if ($this->exist($where)) {
				return $this->update(['content' => $content, 'interval' => intval($interval)], $where);
			} else {
				return $this->insert([
					'content'  => $content,
					'interval' => intval($interval),
					'id'       => $id,
					'tpl'      => $tpl
				]);
			}
		} catch (\Exception $e) {
			return false;
		}
	}
}