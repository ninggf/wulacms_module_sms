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

use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class DayuForm extends FormTable {
	use JQueryValidator;
	/**
	 * App Key
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 1,col-xs-6
	 */
	public $appkey;
	/**
	 * App Secret
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 1,col-xs-6
	 */
	public $appsecret;
	/**
	 * 短信签名
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 */
	public $name;
	/**
	 * 启用SSL
	 * @var \backend\form\CheckboxField
	 * @type bool
	 */
	public $ssl;
}