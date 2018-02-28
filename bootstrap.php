<?php

namespace sms;

use wula\cms\CmfModule;
use wulaphp\app\App;
use wulaphp\auth\AclResourceManager;

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

	/**
	 * @param \wulaphp\auth\AclResourceManager $manager
	 *
	 * @bind rbac\initAdminManager
	 */
	public static function aclres(AclResourceManager $manager) {
		$manager->getResource('system/sms', '短信通道', 'm');
	}

	/**
	 * @param \backend\classes\DashboardUI $ui
	 *
	 * @bind dashboard\initUI
	 */
	public static function initUI($ui) {
		$passport = whoami('admin');
		if ($passport->cando('m:system/sms') && $passport->cando('m:system')) {
			$menu          = $ui->getMenu('system');
			$navi          = $menu->getMenu('sms', '短信通道');
			$navi->icon    = '&#xe62c;';
			$navi->pos     = 899;

			$ch              = $navi->getMenu('ch', '通道管理', 1);
			$ch->data['url'] = App::url('sms');
			$ch->icon        = '&#xe668;';

			$log              = $navi->getMenu('log', '发送日志', 3);
			$log->data['url'] = App::url('sms/log');
			$log->icon        = '&#xe64a;';
		}
	}
}

App::register(new SmsModule());