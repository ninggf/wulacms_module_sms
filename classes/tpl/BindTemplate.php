<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\classes\tpl;

use sms\classes\SMSTemplate;

class BindTemplate extends SMSTemplate {
	private $code = null;

	public function getTemplate() {
		return '绑定手机的验证码是：{code},请不要把验证码透漏给其他人。';
	}

	public function getArgsDesc() {
		return ['code' => '验证码'];
	}

	/*
	 * (non-PHPdoc) @see \sms\classes\SMSTemplate::getArgs()
	 */
	public function getArgs() {
		if (!$this->code) {
			if ($this->testMode) {
				$this->code = '123456';
			} else {
				$this->code = rand_str(6, '0-9');
			}
		}

		return ['code' => $this->code];
	}

	public function onSuccess() {
		$_SESSION ['reg_bind_code']   = $this->code;
		$_SESSION ['reg_bind_expire'] = time() + $this->getTimeout();
	}

	public function getName() {
		return '绑定手机';
	}

	public static function validate($code) {
		$code1 = sess_get('reg_bind_code');
		$time1 = sess_get('reg_bind_expire', 0);
		if ($time1 > time()) {
			if ($code && strtolower($code1) == strtolower($code)) {
				sess_del('reg_bind_expire');
				sess_del('reg_bind_code');

				return true;
			}
		} else {
			sess_del('reg_bind_expire');
			sess_del('reg_bind_code');

		}

		return false;
	}
}