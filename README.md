# PHP Curl 网络请求库

一个轻量级的 PHP 网络操作类，基于 Curl 封装，实现了 GET、POST、UPLOAD、DOWNLOAD 等常用方法，支持方法链式调用。

## 特性

- 轻量级，简单易用
- 支持 GET、POST 请求
- 支持文件上传
- 支持文件下载
- 支持方法链式调用
- 支持自动重试
- 兼容 PHP 5.3+

## 项目结构

```
curl/
├── src/                    # 源代码
│   └── Curl.php           # Curl 操作类
├── composer.json            # 依赖配置
├── composer.lock
└── README.md
```

## 安装

```bash
composer require chenbool/curl
```

## 快速开始

### 初始化

```php
use chenbool\Curl;

// 方式一：new 创建
$curl = new Curl;

// 方式二：静态初始化
$curl = Curl::init();
```

## 请求方法

### GET 请求

```php
$curl->url('目标网址');
```

### POST 请求

```php
// 单一参数
$curl->post('变量名', '变量值')->url('目标网址');

// 多个参数（多维数组）
$curl->post(['key1' => 'value1', 'key2' => 'value2'])->url('目标网址');
```

### 文件上传

```php
$curl->post(['field' => 'value'])
     ->file('file', '本地路径', '文件类型', '原始名称')
     ->url('目标网址');
```

### 文件下载

```php
$curl->url('文件地址')->save('保存路径');
```

## 高级配置

### 自定义 Curl 选项

参考：[PHP curl_setopt](http://php.net/manual/en/function.curl-setopt.php)

```php
$curl->set('CURLOPT_选项', '值')
     ->post(['key' => 'value'])
     ->url('目标网址');
```

### 自动重试

```php
// 出错自动重试 N 次（默认 0 次）
$curl->retry(3)->post(['key' => 'value'])->url('目标网址');
```

## 结果处理

```php
// 判断是否有错误
if ($curl->error()) {
    echo $curl->message(); // 错误信息
} else {
    // 获取任务进程信息
    $info = $curl->info();
    
    // 获取任务结果内容
    $content = $curl->data();
}
```

## API 说明

| 方法 | 说明 |
|------|------|
| `url($url)` | 设置请求 URL |
| `post($key, $value)` | 设置 POST 参数 |
| `file($field, $path, $type, $name)` | 设置上传文件 |
| `save($path)` | 保存下载文件 |
| `set($option, $value)` | 设置 Curl 选项 |
| `retry($times)` | 设置重试次数 |
| `error()` | 判断是否有错误 |
| `message()` | 获取错误信息 |
| `info()` | 获取请求信息 |
| `data()` | 获取响应数据 |

## 环境要求

- PHP >= 5.3.0
- PHP cURL 扩展

## 相关链接

- [PHP cURL 官方文档](http://php.net/manual/en/book.curl.php)
- [cURL 项目官网](https://curl.haxx.se/)
