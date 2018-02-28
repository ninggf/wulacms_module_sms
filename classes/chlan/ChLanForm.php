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

use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class ChLanForm extends FormTable {
	use JQueryValidator;
	/**
	 * 创蓝帐号
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 1,col-xs-6
	 */
	public $account;
	/**
	 * 密码
	 * @var \backend\form\PasswordField
	 * @type string
	 * @required
	 * @layout 1,col-xs-6
	 */
	public $passwd;
	/**
	 * 接口地址
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @url
	 */
	public $api;
}