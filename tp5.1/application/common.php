<?php

use PHPMailer\PHPMailer\PHPMailer;
use service\DataService;
use service\FileService;
use service\NodeService;
use think\facade\Env;
use think\Db;
use Wechat\Loader;

/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function p($data, $replace = false, $pathname = null)
{
    is_null($pathname) && $pathname = Env::get('runtime_path') . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . "\n";
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 获取mongoDB连接
 * @param string $col 数据库集合
 * @param bool $force 是否强制连接
 * @return \think\db\Query|\think\mongo\Query
 */
function mongo($col, $force = false)
{
    return Db::connect(config('mongo'), $force)->name($col);
}

/**
 * 获取微信操作对象
 * @param string $type
 * @return Wechat
 * @throws Exception
 */
function & load_wechat($type = '')
{
    static $wechat = [];
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        $config = [
            'token' => sysconf('wechat_token'),
            'appid' => sysconf('wechat_appid'),
            'appsecret' => sysconf('wechat_appsecret'),
            'encodingaeskey' => sysconf('wechat_encodingaeskey'),
            'mch_id' => sysconf('wechat_mch_id'),
            'partnerkey' => sysconf('wechat_partnerkey'),
            'ssl_cer' => sysconf('wechat_cert_cert'),
            'ssl_key' => sysconf('wechat_cert_key'),
            'cachepath' => Env::get('runtime_path') . 'cache/' . 'wxpay' . DIRECTORY_SEPARATOR,
        ];
        $wechat[$index] = Loader::get($type, $config);
    }
    return $wechat[$index];
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string)
{
    list($chars, $length) = ['', strlen($string = iconv('utf-8', 'gbk', $string))];
    for ($i = 0; $i < $length; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return $chars;
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string)
{
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf-8', $chars);
}

/**
 * 网络图片本地化
 * @param string $url
 * @return string
 */
function local_image($url)
{
    if (is_array(($result = FileService::download($url)))) return $result['url'];
    return $url;
}

/**
 * 密码重加密
 * @param string $password 密码
 * @param string $string 字符串
 * @return string
 */
function password($password, $string = 'weilmanky.cn')
{
    return md5($password . md5($string));
}

/**
 * 验证手机号是否正确
 * @param string $phone 手机号码
 * @return bool
 */
function isPhone($phone)
{
    return preg_match('/^0?1[3|4|5|6|7|8|9|9][0-9]\d{8}$/', $phone) ? true : false;
}

/**
 * 日期格式化
 * @param string $timestamp 标准时间戳
 * @param string $format 输出格式
 * @return string
 */
function dateTime($timestamp, $format = 'Y-m-d H:i:s')
{
    return ($timestamp > 0 || !empty($timestamp)) ? date($format, $timestamp) : '';
}

/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是null为获取值，否则为更新
 * @return string|bool
 */
function sysconf($name, $value = null)
{
    static $config = [];
    if ($value !== null) {
        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name,value');
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node)
{
    return NodeService::checkAuthNode($node);
}

/**
 * 发送http请求方法
 * @param string $url 请求URL
 * @param array $params 请求参数
 * @param string $method
 * @param array $header
 * @param bool $multi
 * @return mixed $data 响应数据
 */
function http_get_post($url, $params = [], $method = 'GET', $header = [], $multi = false)
{
    $opts = array(
        CURLOPT_TIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => $header
    );
    //根据请求类型设置特定参数
    switch (strtoupper($method)) {
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    //初始化并执行curl请求
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) throw new Exception('请求发生错误：' . $error);
    return $data;
}

/**
 * 清除空格
 * @param string $string 字符串
 * @return string $string 字符串
 */
function myTrim($string)
{
    $search = ["　", "\n", "\r", "\t"];
    $replace = ["", "", "", ""];
    return str_replace($search, $replace, $string);
}

/**
 * 超出长度省略号代替
 * @param string $string 字符串
 * @param int $length 长度
 * @return string 字符串
 */
function omit($string, $length = 7)
{
    if (mb_strlen($string, 'UTF-8') <= $length) return $string;
    return mb_substr($string, 0, $length, 'UTF-8') . '...';
}

/**
 * 隐藏手机号码
 * @param int $phone 手机号
 * @return int 手机号
 */
function hidePhone($phone)
{
    return substr_replace($phone, '****', 3, 4);
}

/**
 * 发送邮件
 * @param array $data 发送信息
 * @param int $port 邮箱端口
 * @param string $encoding 编码方式
 * @param string $charSet 字符集
 * @param bool $isHTML 支持html格式内容
 * @return string 手机号
 */
function sendEmail($data, $port = 465, $encoding = 'base64', $charSet = 'UTF-8', $isHTML = true)
{
    $pattern = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";//邮箱正则
    if (!isset($data['email']) || !preg_match($pattern, $data['email'])) return json(['code' => 5, 'msg' => '收件邮箱错误！']);
    if (!isset($data['title']) || empty($data['title'])) return json(['code' => 5, 'msg' => '收件标题错误！']);
    if (!isset($data['content']) || empty($data['content'])) return json(['code' => 5, 'msg' => '收件内容错误！']);
    $mail = new PHPMailer();//实例化
    try {
        $mail->IsSMTP();//启用SMTP
        $mail->Host = config('API.EmailHost');//SMTP服务器 以qq邮箱为例子
        $mail->Port = $port;//邮件发送端口
        $mail->SMTPAuth = true;//启用SMTP认证
        $mail->SMTPSecure = 'ssl';//设置安全验证方式为ssl
        $mail->CharSet = $charSet;//字符集
        $mail->Encoding = $encoding;//编码方式
        $mail->Username = config('API.EmailAddress');//发件邮箱
        $mail->Password = config('API.EmailAuthCode');//发件邮箱授权码
        $mail->Subject = $data['title'];//邮件标题
        $mail->From = config('API.EmailAddress');//发件邮箱
        $mail->FromName = config('API.EmailUsername');//发件人名称
        $mail->AddAddress($data['email'], config('API.EmailUsername'));//收件邮箱,发件人姓名
        $mail->IsHTML($isHTML);//支持html格式内容
        $mail->Body = $data['content'];//邮件主体内容
        $result = $mail->Send() ? 1 : 0;
        return ($result == 0) ? json(['code' => 5, 'msg' => '发送失败！']) : json(['code' => 1, 'msg' => '发送成功！']);
    } catch (Exception $e) {
        return json(['code' => 5, 'msg' => 'Mailer Error: ' . $mail->ErrorInfo]);
    }
}

/**
 * 发送短信
 * @param $smsId int 短信模板ID
 * @param $phone array|int 需要发送的手机号
 * @param $options array 短信模板需要替换的变量
 * @param $remarks string 短信备注
 * @return string
 */
function sendSms($smsId, $phone, $options = [], $remarks = '')
{
    $accessKeyId = config('API.aliyunSmsAccessKeyId');//阿里云accessKeyId
    $accessKeySecret = config('API.aliyunSmsAccessKeySecret');//阿里云accessKeySecret
    $sign = config('API.aliyunSmsSign');//阿里云短信签名
    $AliyunSms = new AliyunSms($accessKeyId, $accessKeySecret);
    $options['code'] = rand(10000, 99999);
    $result = $AliyunSms->sendSms($sign, $smsId, $phone, $options);//签名,模板ID,手机号码,阿里云accessKeySecret
    $result = json_decode(json_encode($result), true);
    return $result['Code'] == 'OK' ? json(['code' => 1, 'msg' => '发送成功！']) : json(['code' => 5, 'msg' => json_encode($result, JSON_UNESCAPED_UNICODE), 'wait' => 5]);
}

/**
 * array_column 函数兼容
 */
if (!function_exists("array_column")) {
    function array_column(array &$rows, $column_key, $index_key = null)
    {
        $data = [];
        foreach ($rows as $row) {
            if (empty($index_key)) {
                $data[] = $row[$column_key];
            } else {
                $data[$row[$index_key]] = $row[$column_key];
            }
        }
        return $data;
    }
}













