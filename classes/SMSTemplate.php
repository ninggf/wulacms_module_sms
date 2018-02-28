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
/**
 * 短信模板.
 *
 * @package sms\classes
 */
abstract class SMSTemplate {
	protected $params   = [];//参数
	protected $options  = [];//选项
	protected $content  = null;//内容
	protected $testMode = false;//测试模式

	/**
	 * 模板名称.
	 *
	 * @return string 模板名称.
	 */
	public abstract function getName();

	/**
	 * 短信模板,模板中变量使用{}包起来.
	 *
	 * @return SMSTemplate 获取短信模板.
	 */
	public abstract function getTemplate();

	/**
	 * 发送成功时触发.
	 */
	public function onSuccess() {
	}

	/**
	 * 发送失败.
	 */
	public function onFailure() {
	}

	/**
	 * @param boolean $testMode
	 */
	public function setTestMode($testMode) {
		$this->testMode = $testMode;
	}

	/**
	 * 获取此模板的参数.
	 *
	 * @return array 模板参数.
	 */
	public function getArgs() {
		return [];
	}

	/**
	 * 获取参数描述.
	 *
	 * @return array key=>value.
	 */
	public function getArgsDesc() {
		return [];
	}

	/**
	 * 设置业务参数.
	 *
	 * @param array $params 参数.
	 */
	public function setParams($params) {
		$this->params = $params;
	}

	/**
	 * 设置选项.
	 *
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * 获取配置选项.
	 *
	 * @return array 配置选项.
	 */
	public function getOptions() {
		return $this->options;
	}

	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * 获取要发送的内容.
	 *
	 * @return string 内容.
	 */
	public final function getContent() {
		$tpl  = $this->content ? $this->content : $this->getTemplate();
		$s    = [];
		$r    = [];
		$args = $this->getArgs();
		if ($args === false) {
			return false;
		}
		foreach ($args as $k => $v) {
			$s [] = '{' . $k . '}';
			$r [] = $v;
		}
		$content = str_replace($s, $r, $tpl);

		return $content;
	}

	protected function getTimeout() {
		return $this->options['exp'];
	}
}