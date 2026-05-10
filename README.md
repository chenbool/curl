# PHP Curl 网络请求库

<p align="center">
  <a href="https://github.com/chenbool/curl">
    <img src="https://img.shields.io/badge/version-1.0.0-blue.svg" alt="version">
  </a>
  <a href="https://github.com/chenbool/curl">
    <img src="https://img.shields.io/badge/php-5.3%2B-8892BF.svg" alt="php">
  </a>
  <a href="https://github.com/chenbool/curl">
    <img src="https://img.shields.io/badge/license-MIT-green.svg" alt="license">
  </a>
</p>

> 一个轻量级的 PHP 网络操作类，基于 Curl 封装，支持 GET、POST、UPLOAD、DOWNLOAD 等常用操作，采用方法链式调用设计。

---

## 📋 目录

- [特性](#-特性)
- [环境要求](#-环境要求)
- [安装](#-安装)
- [快速开始](#-快速开始)
- [使用示例](#-使用示例)
- [API 文档](#-api-文档)
- [项目结构](#-项目结构)

---

## ✨ 特性

| 特性 | 描述 |
|------|------|
| 🚀 轻量级 | 单文件实现，无额外依赖 |
| 🔗 链式调用 | 支持流畅的方法链式操作 |
| 📤 文件上传 | 支持单/多文件上传 |
| 📥 文件下载 | 支持文件下载并保存 |
| 🔄 自动重试 | 请求失败自动重试机制 |
| 🔒 SSL支持 | 支持 HTTPS 请求 |
| ⚡ 高性能 | 基于 cURL 扩展，性能优异 |

---

## 🔧 环境要求

| 项目 | 版本 |
|------|------|
| PHP | >= 5.3.0 |
| cURL扩展 | 已安装 |

---

## 📦 安装

### 方式一：Composer 安装（推荐）

```bash
composer require chenbool/curl
```

### 方式二：手动引入

```php
require_once 'src/Curl.php';
```

---

## 🚀 快速开始

```php
<?php
use chenbool\Curl;

// GET 请求
$response = Curl::init()->url('https://api.example.com/data')->data();

// POST 请求
$response = Curl::init()
    ->post(['name' => 'test', 'value' => 123])
    ->url('https://api.example.com/submit')
    ->data();

echo $response;
```

---

## 📖 使用示例

### 🔍 GET 请求

```php
$curl = Curl::init();

// 简单 GET
$curl->url('https://api.example.com/users');

// 带查询参数
$curl->url('https://api.example.com/users?page=1&limit=10');

// 获取响应
if (!$curl->error()) {
    echo $curl->data();
} else {
    echo 'Error: ' . $curl->message();
}
```

### 📝 POST 请求

```php
$curl = Curl::init();

// 方式一：数组传参
$curl->post([
    'username' => 'admin',
    'password' => '123456'
])->url('https://api.example.com/login');

// 方式二：逐个设置
$curl->post('username', 'admin')
     ->post('password', '123456')
     ->url('https://api.example.com/login');
```

### 📤 文件上传

```php
$curl = Curl::init();

// 单文件上传
$curl->post(['description' => '头像'])
     ->file('avatar', '/path/to/photo.jpg', 'image/jpeg', 'photo.jpg')
     ->url('https://api.example.com/upload');

// 多文件上传
$curl->post(['description' => '相册'])
     ->file('images[]', '/path/to/1.jpg', 'image/jpeg', '1.jpg')
     ->file('images[]', '/path/to/2.jpg', 'image/jpeg', '2.jpg')
     ->url('https://api.example.com/upload');
```

### 📥 文件下载

```php
$curl = Curl::init();

// 下载并保存
$curl->url('https://example.com/file.zip')
     ->save('/path/to/save/file.zip');

if ($curl->error()) {
    echo 'Download failed: ' . $curl->message();
}
```

### ⚙️ 高级配置

```php
$curl = Curl::init();

// 自定义超时
$curl->set('CURLOPT_TIMEOUT', 60)
     ->set('CURLOPT_CONNECTTIMEOUT', 10);

// 设置请求头
$curl->set('CURLOPT_HTTPHEADER', [
    'Authorization: Bearer token123',
    'Content-Type: application/json'
]);

// 启用自动重试
$curl->retry(3)
     ->url('https://api.example.com/data');
```

### 🔄 完整请求流程

```php
$curl = Curl::init();

// 链式调用示例
$result = $curl
    ->set('CURLOPT_TIMEOUT', 30)
    ->set('CURLOPT_SSL_VERIFYPEER', false)
    ->post([
        'name' => 'test',
        'data' => json_encode(['key' => 'value'])
    ])
    ->retry(3)
    ->url('https://api.example.com/submit');

// 结果处理
if ($result->error()) {
    echo "请求失败: " . $result->message();
    print_r($result->info());  // 查看详细信息
} else {
    echo "响应内容: " . $result->data();
}
```

---

## 📚 API 文档

### 核心方法

| 方法 | 参数 | 返回值 | 说明 |
|------|------|--------|------|
| `init()` | - | `Curl` | 静态初始化，获取单例实例 |
| `url($url)` | `string $url` | `Curl` | 设置请求 URL |
| `post($data, $value)` | `array/string $data`, `string $value` | `Curl` | 设置 POST 数据 |
| `file($field, $path, $type, $name)` | `string...` | `Curl` | 设置上传文件 |
| `save($path)` | `string $path` | `Curl` | 保存响应到文件 |
| `set($option, $value)` | `string $option`, `mixed $value` | `Curl` | 设置 cURL 选项 |
| `retry($times)` | `int $times` | `Curl` | 设置失败重试次数 |

### 结果方法

| 方法 | 返回值 | 说明 |
|------|--------|------|
| `error()` | `int` | 获取错误码，0 表示无错误 |
| `message()` | `string` | 获取错误信息 |
| `data()` | `string` | 获取响应内容 |
| `info()` | `array` | 获取请求信息（HTTP 状态码等） |

### cURL 常用选项

| 选项 | 默认值 | 说明 |
|------|--------|------|
| `CURLOPT_TIMEOUT` | 30 | 请求超时时间（秒） |
| `CURLOPT_CONNECTTIMEOUT` | 10 | 连接超时时间（秒） |
| `CURLOPT_SSL_VERIFYPEER` | false | 是否验证 SSL 证书 |
| `CURLOPT_RETURNTRANSFER` | true | 返回字符串而非直接输出 |
| `CURLOPT_HEADER` | 0 | 是否在输出中包含 header |
| `CURLOPT_FOLLOWLOCATION` | - | 是否跟随重定向 |
| `CURLOPT_USERAGENT` | - | 设置 User-Agent |
| `CURLOPT_REFERER` | - | 设置 Referer |

> 更多选项请参考 [PHP cURL 官方文档](http://php.net/manual/en/function.curl-setopt.php)

---

## 📂 项目结构

```
curl/
├── src/
│   └── Curl.php          # 核心类文件
├── composer.json          # Composer 配置
├── composer.lock          # 依赖锁定
└── README.md             # 本文档
```

---

## 🔗 相关链接

- [PHP cURL 官方文档](http://php.net/manual/en/book.curl.php)
- [cURL 项目官网](https://curl.haxx.se/)
- [GitHub 项目地址](https://github.com/chenbool/curl)

---

## 📄 许可证

MIT License

---

## 👤 作者

- **chenbool**
- Email: 30024167@qq.com
- GitHub: [@chenbool](https://github.com/chenbool)

---

<p align="center">如果这个项目对您有帮助，请给个 ⭐ Star 支持一下！</p>
