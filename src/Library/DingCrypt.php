<?php
namespace UUPT\Corp\Library;

use Exception;

class DingCrypt
{
    public static $OK = 0;

    public static $IllegalAesKey = 900004;
    public static $ValidateSignatureError = 900005;
    public static $ComputeSignatureError = 900006;
    public static $EncryptAESError = 900007;
    public static $DecryptAESError = 900008;
    public static $ValidateSuiteKeyError = 900010;

    private $key;
    private $encodingAesKey;
    private $token;
    private $corpId;

    function __construct($token, $encodingAesKey, $corpId)
    {
        $this->key = base64_decode($encodingAesKey . "=");
        $this->encodingAesKey = $encodingAesKey;
        $this->token = $token;
        $this->corpId = $corpId;
    }


    function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

    public function encrypt($text, $corpid)
    {
        try {
            $random = $this->getRandomStr();
            $text = $random . pack("N", strlen($text)) . $text . $corpid;
            $iv = substr($this->key, 0, 16);
            $pkc_encoder = new PKCS7Encoder;
            $text = $pkc_encoder->encode($text);
            $encrypted = openssl_encrypt($text, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
            return array(0, base64_encode($encrypted));
        } catch (Exception $e) {
            return array(90007, $e->getMessage());
        }
    }


//解密
    public function decrypt($encrypted, $corpid)
    {
        try {
            $ciphertext_dec = base64_decode($encrypted);
            $iv = substr($this->key, 0, 16);
            $decrypted = openssl_decrypt($ciphertext_dec, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        } catch (\Throwable $e) {
            throw new  Exception($e->getMessage(), self::$DecryptAESError);
        }
        try {
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_corpid = substr($content, $xml_len + 4);
        } catch (\Throwable $e) {
            throw new  Exception("AES ERROR", self::$DecryptAESError);
        }
        if ($from_corpid != $corpid){
            throw new Exception('Corp iD ERROR', self::$ValidateSuiteKeyError);
        }
        return $xml_content;

    }


    /**
     * 解密回调信息
     */
    public function decryptMsg($signature, $timeStamp = null, $nonce = null, $encrypt= null)
    {
        if (strlen($this->encodingAesKey) != 43) {
            throw new \Exception('IllegalAesKey' . $this->encodingAesKey, self::$IllegalAesKey);
        }
        if ($timeStamp == null) {
            $timeStamp = time();
        }

        $array = $this->getSHA1($this->token, $timeStamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            throw new Exception('ComputeSignatureError', self::$ComputeSignatureError);
        }
        $verifySignature = $array[1];
        if ($verifySignature != $signature) {
            throw new Exception('ValidateSignatureError', self::$ValidateSignatureError);
        }
        $result = $this->decrypt($encrypt, $this->corpId);
        return json_decode($result,true);
    }

    public function EncryptMsg($msg, $suiteKey)
    {
        $timeStamp = time();
        $array = $this->encrypt($msg, $suiteKey);
        $nonce = $this->getRandomStr();
        $encrypt = $array[1];
        $array = $this->getSHA1($this->token, $timeStamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];
        return (array(
            "msg_signature" => $signature,
            "encrypt" => $encrypt,
            "timeStamp" => $timeStamp,
            "nonce" => $nonce
        ));

    }

    public function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return array(0, sha1($str));
        } catch (\Exception $e) {
            print $e . "\n";
            return array(900006, null);
        }
    }
}
