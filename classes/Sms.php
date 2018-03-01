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
use wulaphp\app\App;

/**
 * 短信工具类.
 *
 * @author Leo Ning.
 *
 */
class Sms {
	/**
	 * 发送短信.
	 * 注：在发送之前请开启SESSION。
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
		$table   = new SmsVendorTable();
		$vendors = $table->getAvailableVendors();
		if (empty ($vendors)) {
			log_error('未配置短信提供商', 'sms');
			$args['errorMsg'] = '未配置短信提供商';

			return false;
		}

		$templates = self::templates();
		if (!isset ($templates [ $tid ])) {
			log_error('模板' . $tid . '不存在', 'sms');
			$args['errorMsg'] = '模板' . $tid . '不存在';

			return false;
		}
		$args['phone'] = $phone;
		$tpl           = $templates [ $tid ];
		$tplTble       = new SmsTplTable();
		$rst           = false;
		$testMode      = App::bcfg('testMode@sms', true);
		foreach ($vendors as $vid => $vendor) {
			try {
				$rst = self::sendMsg($tid, $vendor, $tpl, $tplTble, $args, $testMode);
				if ($rst) {
					//发送成功跳出
					break;
				} else {
					//将vendor设为禁用，并重试下一个通道.
					$table->updateStatus(0, [$vid], false);
				}
			} catch (ToofastException $te) {
				//发送太快
				$rst              = false;
				$args['errorMsg'] = $te->getMessage();
				break;
			} catch (\Exception $e) {
				$rst              = false;
				$args['errorMsg'] = $e->getMessage();
			}
		}

		return $rst;
	}

	/**
	 * 发送短信.
	 *
	 * @param string                   $tid
	 * @param \sms\classes\SmsVendor   $vendor
	 * @param \sms\classes\SMSTemplate $tpl
	 * @param SmsTplTable              $tplTble
	 * @param array                    $args
	 * @param bool                     $testMode
	 *
	 * @return bool
	 * @throws
	 */
	private static function sendMsg($tid, $vendor, $tpl, $tplTble, &$args, $testMode) {
		$v   = $vendor;
		$cfg = $tplTble->getTemplate($v->getId(), $tid);
		if (empty($cfg['cnt'])) {
			$cfg['cnt'] = $tpl->getTemplate();
		}
		$phone       = $args['phone'];
		$args['exp'] = $cfg['exp'];
		if ($cfg['exp']) {
			$last_sent = sess_get('sms_' . $tid . '_sent', 0);
			if (($last_sent + $cfg['exp']) > time()) {
				log_error('模板' . $tid . '发送太快', 'sms');
				throw new ToofastException('发送太快');
			}
		}
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
			throw_exception('模板' . $tid . '内容为空');
		}
		if ($testMode) {
			$rst = true;
		} else {
			$rst = $v->send($tpl, $phone);
		}
		if ($rst) {
			$data ['status'] = 1;
			$tpl->onSuccess();
			if ($cfg['exp']) {
				$_SESSION[ 'sms_' . $tid . '_sent' ] = time();
			}
		} else {
			$data ['status'] = 0;
			$data ['note']   = $v->getError();
			$tpl->onFailure();
			$args['error'] = $data['note'];
		}
		$tplTble->db()->insert($data)->into('{sms_log}')->exec();

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