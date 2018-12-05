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

use sms\classes\chlan\JSONSender;
use sms\classes\chlan\Sender;
use sms\classes\model\SmsTplTable;
use sms\classes\model\SmsVendorTable;
use sms\classes\tpl\BindTemplate;
use sms\classes\tpl\LoginTemplate;
use sms\classes\tpl\RegCodeTemplate;
use sms\classes\tpl\ResetPasswdTemplate;
use wulaphp\app\App;
use wulaphp\util\RedisClient;

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
     * @param string|array $phone 手机号码.
     * @param string       $tid   模板编号.
     * @param array        $args  参数数组.
     *
     * @return bool 发送成功返回true,反之返回false.
     */
    public static function send($phone, $tid, &$args = null) {
        if (empty ($phone) || empty ($tid)) {
            $args['errorMsg'] = '手机号:' . $phone . ', 模板:' . $tid . ', 有一个为空';

            return false;
        }
        $day      = date('Ymd');
        $hour     = 'h' . date('H');
        $hm       = 'm' . date('Hi');
        $limitKey = 'pl@' . $phone;
        $limit    = null;
        if (is_array($phone)) {
            // 群发短信时不校验手机号格式
        } else {
            if (!preg_match('#^1[3456789]\d{9}$#', $phone)) {
                $args['errorMsg'] = '手机号:' . $phone . '非法';

                return false;
            }
            try {
                $redis = RedisClient::getRedis();
                $limit = $redis->get($limitKey);
                if ($limit) {
                    $limit = json_decode($limit, true);
                }
                $pday = date('Ymd', strtotime('-1 day'));
                if (empty($limit)) {
                    $limit = [
                        $day => [
                            'day' => 0
                        ]
                    ];
                } else {
                    unset($limit[ $pday ]);
                    if (!isset($limit[ $day ])) {
                        $limit = [
                            $day => [
                                'day' => 0
                            ]
                        ];
                    }
                }

                //第天10条
                if ($limit[ $day ]['day'] > 10) {
                    $args['errorMsg'] = '超载啦';

                    return false;
                }
                //每小时5条
                if (isset($limit[ $day ][ $hour ]) && $limit[ $day ][ $hour ] > 5) {
                    $args['errorMsg'] = '超载啦';

                    return false;
                }
                //每分钟两条
                if (isset($limit[ $day ][ $hm ]) && $limit[ $day ][ $hm ] > 2) {
                    $args['errorMsg'] = '超载啦';

                    return false;
                }
            } catch (\Exception $e) {
                $redis = null;
            }
        }
        $table   = new SmsVendorTable();
        $vendors = $table->getAvailableVendors();
        if (empty ($vendors)) {
            $args['errorMsg'] = '未配置短信提供商';

            return false;
        }

        $templates = self::templates();
        if (!isset ($templates [ $tid ])) {
            $args['errorMsg'] = '模板' . $tid . '不存在';

            return false;
        }
        $args['phone'] = $phone;
        $tpl           = $templates [ $tid ];
        $rtn           = $tpl->beforeSend($tid, $args);
        if ($rtn !== true) {
            $args['errorMsg'] = $rtn;

            return false;
        }
        $tplTble  = new SmsTplTable();
        $rst      = false;
        $testMode = App::bcfg('testMode@sms', true);
        foreach ($vendors as $vid => $vendor) {
            try {
                $rst = self::sendMsg($tid, $vendor, $tpl, $tplTble, $args, $testMode);
                if ($rst) {
                    //发送成功跳出
                    break;
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
        if ($rst && isset($redis)) {
            if (isset($limit[ $day ]['day'])) {
                $limit[ $day ]['day'] = (int)$limit[ $day ]['day'] + 1;
            } else {
                $limit[ $day ]['day'] = 1;
            }
            if (isset($limit[ $day ][ $hour ])) {
                $limit[ $day ][ $hour ] = (int)$limit[ $day ][ $hour ] + 1;
            } else {
                $limit[ $day ][ $hour ] = 1;
            }
            if ($limit[ $day ][ $hm ]) {
                $limit[ $day ][ $hm ] = (int)$limit[ $day ][ $hm ] + 1;
            } else {
                $limit[ $day ][ $hm ] = 1;
            }
            try {
                $redis->set($limitKey, json_encode($limit));
            } catch (\Exception $e) {
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
        if (is_array($data['phone'])) {
            $data['note']  = '群发短信，共发送到' . count($data['phone']) . '个手机号。';
            $data['phone'] = $data['phone'][0];
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
                'dayu'      => new \sms\classes\dayu\Sender(),
                'chlan'     => new Sender(),
                'chlanjson' => new JSONSender()
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
                'register_code' => new RegCodeTemplate(),
                'login_code'    => new LoginTemplate(),
                'reset_pwd'     => new ResetPasswdTemplate(),
                'bind_phone'    => new BindTemplate()
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