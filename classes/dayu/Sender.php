<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\classes\dayu;

use sms\classes\SmsVendor;

class Sender extends SmsVendor {
	public function getId() {
		return 'dayu';
	}

	public function getName() {
		return '大鱼';
	}

	public function send($template, $phone) {
		return true;
	}

	public function getForm() {
		return new DayuForm(true);
	}

	public function canEnable() {
		if (!class_exists('\TopClient')) {
			$this->error = '请安装阿里大鱼SDK';

			return false;
		}

		return true;
	}
}