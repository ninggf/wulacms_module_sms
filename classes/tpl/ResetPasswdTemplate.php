<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\classes\tpl;

use sms\classes\SMSTemplate;

class ResetPasswdTemplate extends SMSTemplate {
    private $code = null;

    public function getTemplate() {
        return '重置密码的验证码是：{code},请不要把验证码透漏给其他人。';
    }

    public function getArgsDesc() {
        return ['code' => '验证码'];
    }

    /*
     * (non-PHPdoc) @see \sms\classes\SMSTemplate::getArgs()
     */
    public function getArgs() {
        if (!$this->code) {
            if ($this->testMode) {
                $this->code = '123456';
            } else {
                $this->code = rand_str(6, '0-9');
            }
        }

        return ['code' => $this->code];
    }

    public function onSuccess() {
        $_SESSION ['reg_reset_code']   = $this->code;
        $_SESSION ['reg_reset_expire'] = time() + $this->getTimeout();
        $_SESSION ['reg_reset_phone']  = $this->phone;
    }

    public function getName() {
        return '重设密码';
    }

    public static function validate($code, $phone = null) {
        $code1 = sess_get('reg_reset_code');
        $time1 = sess_get('reg_reset_expire', 0);
        if ($time1 > time()) {
            if (!$phone) {
                $phone = $phonex = sess_get('reg_reset_phone');
            } else {
                $phonex = sess_get('reg_reset_phone');
            }
            if ($code && strtolower($code1) == strtolower($code) && $phone == $phonex) {
                sess_del('reg_reset_expire');
                sess_del('reg_reset_code');
                sess_del('reg_reset_phone');

                return true;
            }
        } else {
            sess_del('reg_reset_expire');
            sess_del('reg_reset_code');
            sess_del('reg_reset_phone');
        }

        return false;
    }
}