<?php
/**
 * Created by PhpStorm.
 * User: JackWong
 * Date: 2018/11/28
 * Time: 9:47
 */

namespace JackWong\Fadada;


class FadadaApi {
    
    public $appsecret;
    public $appid;
    public $version;

    public function __construct()
    {
        $this->appsecret = config('fadada.app_secret');
        $this->appid = config('fadada.fadada.app_id');
        $this->version = config('fadada.version');
    }
    /**
     * 调用申请证书接口
     */
    public function syncPersonAuto(array $params)
    {
        $idCard = $params['idCard'];
        $mobile = $params['mobile'];
        $timestamp = date("YmdHis");
        $id_mobile = $this->encrypt($idCard . "|" . $mobile, $this->appsecret); // 对身份证、手机号进行3des加密
        $msg_digest = base64_encode(strtoupper(sha1($this->appid . strtoupper(md5($timestamp)) . strtoupper(sha1($this->appsecret))))); // 消息摘要
        return $this->post("syncPerson_auto", [
            "app_id"        => $this->appid,
            "timestamp"     => $timestamp,
            "v"             => $this->appid,
            "customer_name" => $params['customerName'],
            "email"         => $params['email'],
            "id_mobile"     => $id_mobile,
            "msg_digest"    => $msg_digest
        ]);
    }



    /**
     * 合同生成接口
     */
    public function generateContract($params)
    {
        $timestamp = date("YmdHis");
        $msg_digest = base64_encode(
            strtoupper(
                sha1(
                    $this->appid
                    . strtoupper(md5($timestamp))
                    . strtoupper(
                        sha1($this->appsecret
                            . $params['template_id']
                            . $params['contract_id']
                        )
                    )
                    . $params['parameter_map']
                )
            )
        ); // 消息摘要
        return $this->post("generate_contract", [
            "app_id"         => $this->appid,
            "timestamp"      => $timestamp,
            "v"              => $this->appid,
            "doc_title"      => $params['doc_title'],         //合同标题 如“xx 投资合同”
            "template_id"   => $params['template_id'],       //模板编号
            "contract_id"   => $params['contract_id'],       //合同编号 只允许长度<=32 的英文或数字字符
            "font_size"      => $params['font_size'],       //字体大小 参考 word 字体设置，例如：10,12,12.5,14；不传则为默认值 9
            "font_type"      => $params['font_type'],       //字体类型 0-宋体；1-仿宋；2-黑体；3-楷体；4-微软雅黑
            "parameter_map"  => $params['parameter_map'],       //填充内容 JsonObject 字符串  key 为文本域，value 为要填充的值  示例： {"platformName":"TheEarth","borrower":"Boss Horse"}
            'dynamic_tables' => $params['dynamic_tables'],//动态表格
            "msg_digest"     => $msg_digest
        ]);
    }

    /**
     * 文档签署接口（自动签）
     */
    public function extsignAuto($params)
    {
        $timestamp = date("YmdHis");
        $msg_digest = base64_encode(strtoupper(sha1($this->appid . strtoupper(md5($params['transaction_id'].$timestamp)) . strtoupper(sha1($this->appsecret.$params['customer_id']))))); // 消息摘要

        return $this->post("extsign_auto", [
            "app_id"          => $this->appid,
            "timestamp"       => $timestamp,
            "v"               => $this->appid,
            "transaction_id" => $params['transaction_id'],         //每次请求视为一个交易。 只允许长度<=32 的英文或数字字符。
            "contract_id"    => $params['contract_id'],       //合同编号 根据合同编号指定在哪份文档上进行签署
            "customer_id"    => $params['customer_id'],       //客户编号 CA 注册时获取。
            "client_role"    => $params['client_role'],       //客户角色 1-接入平台 2-担保公司3-投资人 4-借款人
            "doc_title"      => $params['doc_title'],       //文档标题 如“xx 投资合同” 。
            "sign_keyword"   => $params['sign_keyword'],       //定位关键字 法大大按此关键字进行签章位置的定位，将电子章盖在这个关键字上面
            "notify_url"    => $params['notify_url'],       //签署结果异步通知 URL
            "msg_digest"      => $msg_digest
        ]);
    }


    /**
     * 合同归档接口
     */
    public function contractFiling($params)
    {
        $timestamp = date("YmdHis");
        $msg_digest = base64_encode(strtoupper(sha1($this->appid . strtoupper(md5($timestamp)) . strtoupper(sha1($this->appsecret.$params['contract_id']))))); // 消息摘要

        return $this->post("contractFiling", [
            "app_id"          => $this->appid,
            "timestamp"       => $timestamp,
            "v"               => $this->appid,
            "contract_id"    => $params['contract_id'],       //合同编号 根据合同编号指定在哪份文档上进行签署
            "msg_digest"      => $msg_digest
        ]);
    }
    /**
     * CURL POST 请求
     * @param  [type] $url  请求接口 url
     * @param  [type] $data 请求数据
     * @return [type]       返回处理结果
     */
    public function post($url, $data)
    {
        $fadada_url = config('param.fadada.url') . $url . '.api';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $fadada_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        return $result;
    }
    /**
     *
     * PHP版3DES加解密类
     *
     * 可与java的3DES(DESede)加密方式兼容
     *
     */
    /**
     * 使用pkcs7进行填充
     * @param unknown $input
     * @return string
     */
    private function PaddingPKCS7($input)
    {
        $srcdata = $input;
        $block_size = mcrypt_get_block_size('tripledes', 'ecb');
        $padding_char = $block_size - (strlen($input) % $block_size);
        $srcdata .= str_repeat(chr($padding_char), $padding_char);
        return $srcdata;
    }

    /**
     * 3des加密
     * @param  $string 待加密的字符串
     * @param  $key 加密用的密钥
     * @return string
     */
    private function encrypt($string, $key)
    {
        $string = self::PaddingPKCS7($string);

        // 加密方法
        $cipher_alg = MCRYPT_TRIPLEDES;
        // 初始化向量来增加安全性
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg, MCRYPT_MODE_ECB), MCRYPT_RAND);

        $encrypted_string = mcrypt_encrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
        $des3 = bin2hex($encrypted_string); // 转化成16进制

        //echo $des3 . "</br>";
        return $des3;
    }

    // 开始64位编码
    // $base64=base64_encode($spid."$".$des3);
    // echo "base64:".$base64."<br>";
}