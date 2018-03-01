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

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use sms\classes\SmsVendor;

class Sender extends SmsVendor {
	private static $acsClient = null;

	public function getId() {
		return 'dayu';
	}

	public function getName() {
		return '阿里大于';
	}

	/**
	 * @param \sms\classes\SMSTemplate $template
	 * @param string                   $phone
	 *
	 * @return bool
	 */
	public function send($template, $phone) {
		$this->getConfig();
		$request = new SendSmsRequest();
		$request->setPhoneNumbers($phone);
		$request->setSignName($this->options['name']);
		$request->setTemplateCode($template->getContent());
		$args = $template->getArgs();
		if ($args) {
			$request->setTemplateParam(json_encode($args));
		}
		$acsResponse = static::getAcsClient($this->options)->getAcsResponse($request);

		if ($acsResponse && $acsResponse->Code == 'OK') {
			return true;
		} else if ($acsResponse) {
			$this->error = $acsResponse->Message;
		} else {
			$this->error = '好像无法连上阿里大于服务器呢';
		}

		return false;
	}

	public function getForm() {
		return new DayuForm(true);
	}

	public function canEnable() {
		if (!class_exists('\Aliyun\Api\Sms\Request\V20170525\SendSmsRequest')) {
			$this->error = '请安装阿里大鱼SDK';

			return false;
		}
		$options = $this->getConfig();
		if (!$options['appkey'] || !$options['appsecret'] || !$options['name']) {
			$this->error = '请完成通道配置';

			return false;
		}

		return true;
	}

	/**
	 * @param $options
	 *
	 * @return \Aliyun\Core\DefaultAcsClient
	 */
	public static function getAcsClient($options) {
		//产品名称:云通信流量服务API产品,开发者无需替换
		$product = "Dysmsapi";
		//产品域名,开发者无需替换
		$domain          = "dysmsapi.aliyuncs.com";
		$accessKeyId     = $options['appkey']; // AccessKeyId
		$accessKeySecret = $options['appsecret']; // AccessKeySecret
		// 暂时不支持多Region
		$region = "cn-hangzhou";
		// 服务结点
		$endPointName = "cn-hangzhou";
		if (static::$acsClient == null) {
			Config::load();
			//初始化acsClient,暂不支持region化
			$profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
			// 增加服务结点
			DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
			// 初始化AcsClient用于发起请求
			static::$acsClient = new DefaultAcsClient($profile);
		}

		return static::$acsClient;
	}
}