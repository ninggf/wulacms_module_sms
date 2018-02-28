<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\classes;

use sms\classes\model\SmsTplTable;
use sms\classes\model\SmsVendorTable;
use sms\classes\tpl\RegCodeTemplate;

/**
 * 短信工具类.
 *
 * @author Leo Ning.
 *
 */
class Sms {
	/**
	 * 发送短信.
	 *
	 * @param string $phone 手机号码.
	 * @param string $tid   模板编号.
	 * @param array  $args  参数数组.
	 *
	 * @return bool 发送成功返回true,反之返回false.
	 */
	public static function send($phone, $tid, &$args = null) {
		if (empty ($phone) || empty ($tid)) {
			log_error('手机号:' . $phone . ', 模板:' . $tid . ', 有一个为空', 'sms');
			$args['errorMsg'] = '手机号:' . $phone . ', 模板:' . $tid . ', 有一个为空';

			return false;
		}
		if (!preg_match('#^1[345789]\d{9}$#', $phone)) {
			log_error('手机号:' . $phone . '非法', 'sms');
			$args['errorMsg'] = '手机号:' . $phone . '非法';

			return false;
		}
		$table  = new SmsVendorTable();
		$vendor = $table->getAvailableVendor();
		if (empty ($vendor)) {
			log_error('未配置短信提供商', 'sms');
			$args['errorMsg'] = '未配置短信提供商';

			return false;
		}

		$v         = $vendor;
		$templates = self::templates();
		if (!isset ($templates [ $tid ])) {
			log_error('模板' . $tid . '不存在', 'sms');
			$args['errorMsg'] = '模板' . $tid . '不存在';

			return false;
		}
		$tpl     = $templates [ $tid ];
		$tplTble = new SmsTplTable();
		$cfg     = $tplTble->getTemplate($v->getId(), $tid);
		if (empty($cfg['cnt'])) {
			$cfg['cnt'] = $tpl->getTemplate();
		}
		$args['exp'] = $cfg['exp'];

		$last_sent = sess_get('sms_' . $tid . '_sent', 0);
		if (($last_sent + $cfg['exp']) > time()) {
			log_error('模板' . $tid . '发送太快', 'sms');
			$args['errorMsg'] = '发送太快';

			return false;
		}

		$args['phone'] = $phone;
		$testMode      = DEBUG == DEBUG_DEBUG;
		$tpl->setTestMode($testMode);
		$tpl->setParams($args);
		$tpl->setOptions($cfg);
		$data ['create_time'] = time();
		$data ['phone']       = $phone;
		$data ['tid']         = $tid;
		$data ['vendor']      = $v->getId();
		$tpl->setContent($cfg ['cnt']);
		$data ['content'] = $tpl->getContent();
		if ($data['content'] === false) {
			$args['errorMsg'] = '模板' . $tid . '内容为空';

			return false;
		}
		if ($testMode) {
			$rst = true;
		} else {
			$rst = $v->send($tpl, $phone);
		}
		if ($rst) {
			$data ['status'] = 1;
			$tpl->onSuccess();
			$_SESSION[ 'sms_' . $tid . '_sent' ] = time();
		} else {
			$data ['status'] = 0;
			$data ['note']   = $v->getError();
			$tpl->onFailure();
			$args['errorMsg'] = $data['note'];
		}
		$table->db()->insert($data)->into('{sms_log}')->exec();

		return $rst;
	}

	/**
	 * 短信提供商列表.
	 *
	 * @return \sms\classes\SmsVendor[] 短信提供商列表.
	 */
	public static function vendors() {
		static $vendors = false;
		if ($vendors === false) {
			$vendors = apply_filter('sms\vendors', [
				'dayu'  => new \sms\classes\dayu\Sender(),
				'chlan' => new \sms\classes\chlan\Sender()
			]);
		}

		return $vendors;
	}

	public static function vendorsName() {
		$names = [];
		foreach (self::vendors() as $key => $v) {
			$names[ $key ] = $v->getName();
		}

		return $names;
	}

	/**
	 * 系统业务模板.
	 *
	 * @return \sms\classes\SMSTemplate[] 系统业务模板.
	 */
	public static function templates() {
		static $templates = false;
		if ($templates === false) {

			$templates = apply_filter('sms\templates', [
				'register_code' => new RegCodeTemplate()
			]);
		}

		return $templates;
	}

	/**
	 * @return array
	 */
	public static function templatesName() {
		$names = [];
		foreach (self::templates() as $key => $v) {
			$names[ $key ] = $v->getName();
		}

		return $names;
	}
}