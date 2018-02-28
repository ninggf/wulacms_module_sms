<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\api\v1;

use backend\classes\CaptchaCode;
use rest\classes\API;
use sms\classes\Sms;
use wulaphp\app\App;

/**
 * Class SendApi
 * @package sms\api\v1
 * @name 短信
 */
class SendApi extends API {
	/**
	 * 验证码是否启用.
	 *
	 * @apiName 验证码
	 * @session
	 *
	 * @return array {
	 *  "enabled":"bool|是否需要验证码",
	 *  "captcha":"string|验证码URL"
	 * }
	 */
	public function captcha() {
		$enabled = App::bcfg('captcha_enabled@sms');

		return ['enabled' => $enabled];
	}

	/**
	 * 发送短信
	 * @apiName 发送
	 * @session
	 *
	 * @param string $phone (required) 手机号
	 * @param string $tid   (required) 模板编号
	 * @param object $param (sample={"code":"string"}) 短信模板需要的参数
	 *
	 * @error   403=>未开始会话
	 * @error   405=>验证码不正确
	 * @error   406=>请输入手机号
	 * @error   407=>手机格式错误
	 * @error   408=>模板编号为空
	 * @error   409=>短信通道错误
	 *
	 * @return array {
	 *  "timeout":"多久后可以重新发送"
	 * }
	 * @throws
	 */
	public function send($phone, $tid, $param = null) {
		if (!defined('REST_SESSION_ID')) {
			$this->error(403, '请开启会话');
		}
		$content = $tid;

		if (App::bcfg('captcha_enabled@sms')) {
			$captcha = aryget('captcha', $param);
			$code    = new CaptchaCode();
			if (!$captcha || !$code->validate($captcha, false, true)) {
				$this->error(405, '验证码不正确');
			}
		}
		if (!$phone) {
			$this->error(406, '请输入手机号');
		}
		// 正则手机
		if (preg_match('/^1[34578]\d{9}$/', $phone) == 0) {
			$this->error(407, '手机格式错误');
		}
		if (!$content) {
			$this->error(408, '模板编号为空');
		}
		$rtn = [];
		$rst = Sms::send($phone, $content, $param);
		if ($rst) {
			$rtn['timeout'] = $param['exp'];
		} else {
			$this->error(409, $param['errorMsg']);
		}

		return $rtn;
	}
}