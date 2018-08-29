<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sms\api\v1;

use backend\classes\CaptchaCode;
use rest\api\v1\SessionApi;
use rest\classes\API;
use sms\classes\Sms;
use wulaphp\app\App;

/**
 * Class SendApi
 * @package sms\api\v1
 * @name 短信
 */
class SendApi extends API {
    /**
     * 发送短信时是否需要提供图片验证码.
     *
     * @apiName 图片验证
     * @session
     *
     * @param string $type 图片类型(gif,png,jpg)
     * @param string $size 图片尺寸，格式为:宽x高
     * @param int    $font 字体大小
     *
     * @paramo  bool enabled 是否需要验证码
     * @paramo  string captcha 验证码URL,通过此URL加载验证码图片
     * @paramo  string session 自动开启的会话ID，如果调用此接口时已经开启会话，则不会出现此字段
     *
     * @return array {
     *  "enabled":true,
     *  "captcha":"/rest/captcha/afasdfasdfasd/90x30.15.gif"
     * }
     */
    public function captcha($type = 'gif', $size = '90x30', $font = 15) {
        $enabled = App::bcfg('captcha@sms', false);
        $captcha = '';
        if (empty($this->sessionId)) {
            $session = new SessionApi($this->appKey, '1');
            $rtn     = $session->start();
            if ($rtn && $rtn['session']) {
                $this->sessionId = $rtn['session'];
            }
        }
        if ($enabled && $this->sessionId) {
            $captcha = App::url('rest/captcha') . '/' . $this->sessionId . '/' . $size . '.' . $font . '.' . $type;
        } else {
            $enabled = false;
        }
        if (isset($rtn)) {
            return ['enabled' => $enabled, 'captcha' => $captcha, 'session' => $this->sessionId];
        } else {
            return ['enabled' => $enabled, 'captcha' => $captcha];
        }
    }

    /**
     * 发送短信
     * @apiName 发送短信
     * @session
     *
     * @param string $phone   (required) 手机号
     * @param string $tid     (required,sample=register_code) 模板编号,参见具体业务接口。
     * @param string $captcha 验证码
     * @param object $param   (sample={"code":"string"}) 短信模板需要的参数
     *
     * @error   403=>未开始会话
     * @error   405=>验证码不正确
     * @error   406=>请输入手机号
     * @error   407=>手机格式错误
     * @error   408=>模板编号为空
     * @error   500=>短信通道错误
     *
     * @paramo  int timeout 多久后可以重新发送
     *
     * @return array {
     *  "timeout":120
     * }
     * @throws
     */
    public function send($phone, $tid, $captcha = '', $param = null) {
        if (!$this->sessionId) {
            $this->error(403, '请开启会话');
        }
        $content = $tid;

        if (App::bcfg('captcha@sms')) {
            $code = new CaptchaCode();
            if (!$captcha || !$code->validate($captcha, false, true)) {
                $this->error(405, '验证码不正确');
            }
        }
        if (!$phone) {
            $this->error(406, '请输入手机号');
        }
        // 正则手机
        if (preg_match('/^1[34578]\d{9}$/', $phone) == 0) {
            $this->error(407, '手机格式错误');
        }
        if (!$content) {
            $this->error(408, '模板编号为空');
        }
        $rtn = [];
        $rst = Sms::send($phone, $content, $param);
        if ($rst) {
            $rtn['timeout'] = $param['exp'];
        } else {
            $this->error(500, $param['errorMsg']);
        }

        return $rtn;
    }
}