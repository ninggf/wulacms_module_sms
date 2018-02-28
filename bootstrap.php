<?php

namespace sms;

use wula\cms\CmfModule;
use wulaphp\app\App;

/**
 * Class SmsModule
 * @package sms
 * @group   工具
 */
class SmsModule extends CmfModule {
	public function getName() {
		return '短信通道';
	}

	public function getDescription() {
		return '统一提供短信发送功能.';
	}

	public function getHomePageURL() {
		return 'https://www.wulacms.com/modules/sms';
	}

	public function getAuthor() {
		return 'wulacms team';
	}

	public function getVersionList() {
		$v['1.0.0'] = '初始化版本';

		return $v;
	}
}

App::register(new SmsModule());