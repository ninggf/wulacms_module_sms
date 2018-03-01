# 短信通道

为wulacms提供短信发送功能。默认支持[创蓝](https://www.253.com)与[阿里大于](https://www.aliyun.com/product/sms)。

## 发送短信

```php
    Sms::send('18049920019','短信模板编号',['arg'=>'模板需要的参数']);
```

## 使用阿里大于

使用阿里大于短信通道需要安装`wula/aliyun-sms`依赖:

`#composer install wula/aliyun-sms`