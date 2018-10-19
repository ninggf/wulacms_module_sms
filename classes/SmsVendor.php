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

use sms\classes\model\SmsVendorTable;

/**
 * 短信提供商。
 *
 * @package sms\classes
 */
abstract class SmsVendor {
    protected $error;
    protected $options = false;

    /**
     * 出错信息.
     *
     * @return string 当发送失败时平台返回的错误信息.
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 是否有平台模板.
     *
     * @return bool 是否平台上定义模板.
     */
    public function usePlatformTemplate() {
        return false;
    }

    public function canEnable() {
        return true;
    }

    /**
     * 配置表单.
     * @return null|\wulaphp\form\FormTable
     */
    public function getForm() {
        return null;
    }

    /**
     * 说明
     * @return string
     */
    public function getDesc() {
        return '';
    }

    /**
     * 获取配置.
     *
     * @return array|bool
     */
    protected function getConfig() {
        if ($this->options === false) {
            $table         = new SmsVendorTable();
            $this->options = $table->json_decode($this->getId(), 'options');
        }

        return $this->options;
    }

    /**
     * @return string
     */
    public abstract function getId();

    /**
     * 短信提供商名称.
     *
     * @return string 短信提供商名称.
     */
    public abstract function getName();

    /**
     * 发送短信.
     *
     * @param SMSTemplate  $template 短信模板.
     * @param string|array $phone    手机号.
     *
     * @return bool 发送是否成功.
     */
    public abstract function send($template, $phone);

}