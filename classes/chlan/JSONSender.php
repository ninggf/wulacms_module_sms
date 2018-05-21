<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\classes\chlan;

use sms\classes\SmsVendor;

class JSONSender extends SmsVendor {
	public function getId() {
		return 'chlanjson';
	}

	public function getName() {
		return '创蓝(JSON)';
	}

	/**
	 * @param \sms\classes\SMSTemplate $template
	 * @param string                   $phone
	 *
	 * @return bool
	 */
	public function send($template, $phone) {
		$cfg     = $this->getConfig();
		$account = $cfg['account'];
		$pswd    = $cfg['passwd'];
		$url     = $cfg['api'];
		$content = $template->getContent();
		if ($account && $pswd && $url) {
			if ($phone && $content) {
				$postData = ['account' => $account, 'password' => $pswd, 'msg' => $content, 'phone' => $phone];
				$result   = $this->sendSms($url, json_encode($postData));
				if ($result) {
					$result = json_decode($result, true);
					if ($result && isset($result['code']) && $result['code'] == '0') {
						return true;
					} else if ($result) {
						$this->error = $result['errorMsg'];
					}
				}
			} else {
				$this->error = '手机号或发送内容为空';
			}
		} else {
			$this->error = '请配置创蓝(JSON)通道';
		}

		return false;
	}

	public function getForm() {
		return new ChLanForm(true);
	}

	public function canEnable() {
		if (!function_exists('curl_init')) {
			$this->error = '请先安装curl扩展';

			return false;
		}
		$cfg = $this->getConfig();
		if (empty($cfg['account']) || empty($cfg['passwd']) || empty($cfg['api'])) {
			$this->error = '请先配置通道后再启用';

			return false;
		}

		return true;
	}

	private function sendSms($url, $post_data) {
		$ch = @curl_init();
		if ($ch) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json; charset=utf-8'
			]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$result      = curl_exec($ch);
			$this->error = curl_error($ch);
			$rsp         = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 != $rsp) {
				$this->error = "请求状态 " . $rsp . " " . curl_error($ch);

				return false;
			}
			curl_close($ch);

			return $result;
		} else {
			$this->error = '无法初始化curl';
		}

		return false;
	}
}