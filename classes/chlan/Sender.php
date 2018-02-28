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

class Sender extends SmsVendor {
	public function getId() {
		return 'chlan';
	}

	public function getName() {
		return '创蓝';
	}

	public function send($template, $phone) {
		$cfg     = $this->getConfig();
		$account = $cfg['account'];
		$pswd    = $cfg['passwd'];
		$url     = $cfg['api'];
		$content = $template->getContent();
		if ($account && $pswd && $url) {
			if ($phone && $content) {
				$postData = ['account' => $account, 'pswd' => $pswd, 'msg' => $content, 'mobile' => $phone];
				$result   = $this->sendSms($url, http_build_query($postData));
				if ($result) {
					$this->error = $result;
					$rst         = explode(',', $result);
					if (isset ($rst [1]) && $rst [1] == '0') {
						return true;
					}
				}
			} else {
				$this->error = '手机号或发送内容为空';
			}
		} else {
			$this->error = '请配置创蓝通道';
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
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			$result      = curl_exec($ch);
			$this->error = curl_error($ch);
			curl_close($ch);

			return $result;
		} else {
			$this->error = '无法初始化curl';
		}

		return false;
	}
}