<?php
/**
 * 作者:  chenbool
 * 邮箱:   30024167@qq.com
 * 版本:  1.0.0
 *
 * https://github.com/chenbool
 * 一个轻量级的网络操作类，实现GET、POST、UPLOAD、DOWNLOAD常用操作，支持链式写法。
 * 兼容PHP 5.6 - PHP 8.x
 */

namespace chenbool;

// 引入异常类
if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    // PHP7及以上版本使用内置的Throwable
} else {
    // PHP5使用Exception
}

/**
 * Curl 网络请求类
 * 支持链式调用，提供GET、POST、文件上传、文件下载等功能
 */
class Curl {
    // POST数据
    private $post;
    // 重试次数
    private $retry = 0;
    // 自定义选项
    private $custom = array();
    // 默认选项配置
    private $option = array(
        'CURLOPT_HEADER'         => 0,         // 不输出头部
        'CURLOPT_TIMEOUT'        => 30,        // 请求超时时间
        'CURLOPT_ENCODING'       => '',        // 编码
        'CURLOPT_IPRESOLVE'      => 1,         // 使用IPv4
        'CURLOPT_RETURNTRANSFER' => true,      // 返回字符串而非输出
        'CURLOPT_SSL_VERIFYPEER' => false,     // 禁用SSL证书验证
        'CURLOPT_CONNECTTIMEOUT' => 10,        // 连接超时时间
    );

    // curl_getinfo() 返回的信息
    private $info;
    // 响应数据
    private $data;
    // 错误码
    private $error;
    // 错误信息
    private $message;

    // 单例实例
    private static $instance;
        
    /**
     * 获取单例实例
     * @return self 返回当前类实例
     */
    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 获取请求信息
     * @return array 返回curl_getinfo()的信息
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * 获取响应数据
     * @return string 返回服务器响应的内容
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * 获取错误码
     * @return int 返回curl错误码，0表示无错误
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * 获取错误信息
     * @return string 返回curl错误描述信息
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * 设置POST数据
     * @param array|string  $data  POST数据，数组或字符串
     * @param null|string   $value 当$data为字符串时的键名
     * @return self 返回当前实例以支持链式调用
     */
    public function post($data, $value = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $this->post[$key] = $val;
            }
        } else {
            if ($value === null) {
                $this->post = $data;
            } else {
                $this->post[$data] = $value;
            }
        }
        return $this;
    }

    /**
     * 设置上传文件
     * @param string $field 表单字段名
     * @param string $path 文件路径
     * @param string $type 文件MIME类型
     * @param string $name 文件名
     * @return self 返回当前实例以支持链式调用
     */
    public function file($field, $path, $type, $name)
    {
        $name = basename($name);
        if (class_exists('CURLFile')) {
            // PHP 5.5+ 使用CURLFile类
            $this->set('CURLOPT_SAFE_UPLOAD', true);
            $file = curl_file_create($path, $type, $name);
        } else {
            // PHP 5.4及以下使用@前缀
            $file = "@{$path};type={$type};filename={$name}";
        }
        return $this->post($field, $file);
    }

    /**
     * 保存响应内容到文件
     * @param string $path 保存路径
     * @return self 返回当前实例以支持链式调用
     * @throws Exception 保存失败时抛出异常
     */
    public function save($path)
    {
        if ($this->error) {
            throw new Exception($this->message, $this->error);
        }
        $fp = @fopen($path, 'w');
        if ($fp === false) {
            throw new Exception('Failed to save the content', 500);
        }
        fwrite($fp, $this->data);
        fclose($fp);
        return $this;
    }

    /**
     * 设置请求URL并执行请求
     * @param string $url 目标URL地址
     * @return self 返回当前实例以支持链式调用
     * @throws Exception URL无效时抛出异常
     */
    public function url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->set('CURLOPT_URL', $url)->process();
        }
        throw new Exception('Target URL is required.', 500);
    }

    /**
     * 设置cURL选项
     * @param array|string  $item 选项名称或选项数组
     * @param null|string   $value 选项值
     * @return self 返回当前实例以支持链式调用
     */
    public function set($item, $value = null)
    {
        if (is_array($item)) {
            foreach($item as $key => $val){
                $this->custom[$key] = $val;
            }
        } else {
            $this->custom[$item] = $value;
        }
        return $this;
    }

    /**
     * 设置请求失败时的重试次数
     * @param int $times 重试次数
     * @return self 返回当前实例以支持链式调用
     */
    public function retry($times = 0)
    {
        $this->retry = $times;
        return $this;
    }

    /**
     * 执行cURL请求
     * @param int $retry 当前重试次数计数
     * @return self 返回当前实例以支持链式调用
     */
    private function process($retry = 0)
    {
        $ch = curl_init();

        // 合并默认选项和自定义选项
        $option = array_merge($this->option, $this->custom);
        foreach($option as $key => $val) {
            // 如果选项名是字符串，转换为常量
            if (is_string($key)) {
                $key = constant(strtoupper($key));
            }
            curl_setopt($ch, $key, $val);
        }

        // 如果有POST数据，设置POST请求
        if ($this->post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->convert($this->post));
        }

        // 执行请求
        $this->data = (string) curl_exec($ch);
        // 获取请求信息
        $this->info = curl_getinfo($ch);
        // 获取错误码
        $this->error = curl_errno($ch);
        // 获取错误信息
        $this->message = $this->error ? curl_error($ch) : '';

        // 关闭cURL资源
        curl_close($ch);

        // 如果有错误且未超过重试次数，则重试
        if ($this->error && $retry < $this->retry) {
            $this->process($retry + 1);
        }

        // 重置状态
        $this->post     = null;
        $this->retry    = 0;

        return $this;
    }

    /**
     * 转换数组格式
     * 将嵌套数组转换为PHP cURL兼容的格式
     * @param array  $input 输入数组
     * @param string $pre 键前缀
     * @return array 转换后的数组
     */
    private function convert($input, $pre = null){
        if (is_array($input)) {
            $output = array();
            foreach ($input as $key => $value) {
                // 构建索引名
                $index = is_null($pre) ? $key : "{$pre}[{$key}]";
                if (is_array($value)) {
                    // 递归处理嵌套数组
                    $output = array_merge($output, $this->convert($value, $index));
                } else {
                    $output[$index] = $value;
                }
            }
            return $output;
        }
        return $input;
    }

    /**
     * 重置实例状态
     * 用于在连续请求时清理之前的数据
     * @return void
     */
    public function reset()
    {
        $this->post = null;
        $this->custom = array();
    }
}
