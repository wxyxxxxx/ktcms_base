KTCMS 1.0
===============

[KTCMS开发手册](https://www.kancloud.cn/wxyxxxxx/ktcms1_0/346642)
应用部署目录


~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─admin              后台模块
│  │  ├─controller      控制器目录
│  │  │  ├─Admin.php    用户自定义控制器
│  │  │  ├─Common.php   公用控制器
│  │  │  ├─Develop.php  开发控制器
│  │  │  └─Login.php    登录控制器
│  │  │  
│  │  ├─view            模版文件目录
│  │  ├─common.php      模块函数文件
│  │  └─config.php      模块配置文件
│  ├─index              前台模块
│  │  ├─controller      控制器目录
│  │  ├─common.php      模块函数文件
│  │  └─config.php      模块配置文件
│  ├─install            安装模块
│  │  ├─controller      控制器目录
│  │  └─config.php      模块配置文件
│  │
│  ├─common.php         公共函数文件
│  ├─config.php         公共配置文件
│  ├─data.php           公共数据函数
│  ├─ktcms.sql           数据库安装文件
│  ├─route.php          路由配置文件
│  └─database.php       数据库配置文件
│
├─extend                扩展类库目录
│  ├─OAuth              第三方登录
│  │  ├─qq              QQ登录
│  │  └─Wechat.php      微信登录
│  │  
│  ├─Pay                第三方支付
│  │  ├─alipay          支付包支付
│  │  └─Wechat.php      微信支付
│  │  
│  ├─phpanalysis        分词类
│  ├─Water              水印
│  ├─weixin             微信类
│  │
├─public                WEB目录（对外访问目录）
│  ├─404                404页面目录
│  ├─data               备份目录
│  ├─install            install静态文件
│  ├─static             静态资源目录
│  ├─templets           前段模版目录
│  │  └─default         默认模版
│  │       ├─pc         电脑模版
│  │       └─mobile     手机模版
│  │  
│  ├─uploads            上传文件目录
│  │
│  ├─.htaccess          用于apache的重写
│  ├─404.html           404页面
│  ├─favicon.ico        网站小图标
│  └─index.php          入口文件


更多参考   [KTCMS开发手册](https://www.kancloud.cn/wxyxxxxx/ktcms1_0/346642)
