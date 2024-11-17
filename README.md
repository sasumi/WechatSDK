# 微信API SDK库

## 1. 微信公众号开发环境配置

### 1.1 配置基本域名信息

进入【设置与开发】 > 【账号设置】 > 【功能设置】，配置业务相关域名信息，包括：

- `业务域名`（必须）
- `JS接口安全域名`（前端JS接口场景必须）
- `网页授权域名`（后端回调必须）。

### 1.2 配置技术相关参数

进入 mp.weixin.qq.com ，访问【设置与开发】 > 【开发接口管理】 菜单。

- 获取开发者ID（AppID）信息
- 获取令牌（Token）
- 设置开发者密码（AppSecret）（必须）
- 添加后台接口访问IP白名单（必须）
- 配置并启用【服务器配置】中的
    - `服务器地址（URL）`（必须）
    - `消息加解密密钥（EncodingAESKey）`（必须）
    - 选择 `消息加密方式`（推荐【安全模式】，当前库大部分函数也是基于这个模式）。

## 1.3 验证服务器地址

假设服务器地址（URL）配置为：`https://www.site.com/callback.php`，则在 `callback.php` 代码中实现服务器校验代码示例为：

```php
use function LFPhp\WechatSdk\Util\wechat_echo_validation;
//微信接口校验模式
$token = ''; //请填入 1.2 中获取的【令牌（Token）】
if($_GET['echostr']){
    wechat_echo_validation($token);
    die;
}

//否则为微信常规回调模式
//正常微信回调业务代码···
```

## 2. 接收处理微信回调

### 2.1 获取回调数据

在上述处理响应代码中，继续追加正常微信回调业务代码：

```php
//接【2】中微信接口校验代码···

$app_id = ''; //请填入 1.2 中获取的【开发者ID（AppID）】
$token = ''; //请填入 1.2 中获取的【令牌（Token）】
$encoding_aes_key = ''; //请填入 1.2 中设置的 【消息加解密密钥（EncodingAESKey）】

//解析并获取微信回调数据 
$data = LFPhp\WechatSdk\Util\wechat_decode_callback($app_id, $token, $encoding_aes_key);

//数据调试
var_dump($data); 
```

上述代码中 `$data`
即为微信回调数组，详细格式请参考 [微信开发文档](https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html)

### 2.2 转换为事件对象
以下示例仅以普通文本消息为例
```php
//$data 为上一步骤中解析出来的数组
$receive_msg = MessageBase::getMessageInstance($data);
if($receive_msg instanceof EventSubscribe ||
    $receive_msg instanceof EventScan){
    $success_msg = new MessageText;
    $success_msg->ToUserName = $receive_msg->FromUserName;
    $success_msg->FromUserName = $receive_msg->ToUserName;
    $success_msg->CreateTime = time();
    if($receive_msg->Ticket){
        //公众号扫码成功
    } else {
        //公众号扫码订阅成功
    }
}
```

## 3. 获取 `access_token` `js_ticket` `page_access_token`
> 公众号后端接口调用基本都依赖 `access_token`，应用需要获取 `access_token` 保存到本地，并定期刷新 `access_token` （一般有效期为 7200 s） 
> 应用H5页面js调用接口需要 js_ticket 信息（一般有效期为7200s），应用需要获取并刷新该 js_ticket
> 微信环境H5 oAuth方式登录依赖页面page_access_token(不是后端接口的access_token)

### 3.1 获取 `access_token`

```php
use LFPhp\WechatSdk\Service\Auth;
$app_id = ''; //请填入 1.2 中获取的【开发者ID（AppID）】
$app_secrect = ''; //请填入 1.2 中获取的【开发者密码（AppSecret）】
list($access_token, $expires) = Auth::getAccessToken($app_id, $app_secrect);

//todo 应用需要保存 $access_token 到本地
```

### 3.2 获取 `js_ticket`

```php
use LFPhp\WechatSdk\Service\JSAuth;
$app_id = ''; //请填入 1.2 中获取的【开发者ID（AppID）】
$app_secrect = ''; //请填入 1.2 中获取的【开发者密码（AppSecret）】
list($ticket, $expires) = JSAuth::getJsTicket($app_id, $app_secrect);

//todo 应用需要保存 $ticket 到本地
```

### 3.3 获取 page_access_token
需要微信oAuth登录流程获取微信返回code，才能获取并使用 page_access_token，
具体请见 [步骤5](#s5) 

```php
$app_id = ''; //请填入 1.2 中获取的【开发者ID（AppID）】
$app_secrect = ''; //请填入 1.2 中获取的【开发者密码（AppSecret）】
$code = $_GET['code'];  //微信登录回调code
$page_access_token = PageAuth::getAccessToken($app_id, $app_secret, $code);
```


## 4. 后端请求公众号接口

以获取公众号分享二维码为例，其他功能请参考 `Service` 下个类库方法

```php
use LFPhp\WechatSdk\Base\AuthorizedService;
use LFPhp\WechatSdk\Service\QRCode;

$access_token = ''; //读取 4.1 中本地保存的 access_token

//初始化需要鉴权的服务
//需要鉴权类的服务调用前都必须初始化access_token信息
AuthorizedService::setAccessToken($access_token);

$info = QRCode::createTemporaryQRCode($scene);

//打印二维码图像地址
var_dump($info['qrcode_url']);

//输出到html中：
echo '<img src="'.$info['qrcode_url'].'"/>';
```

## 5. 页面oAuth鉴权流程 <a name="s5"></a>
### 5.1 微信环境内自动登录
//todo

## 6. 前端JS调用
前端JS调用微信前端能力，需要通过步骤3.2 获取到可用 `js_ticket`

### 6.1 前端微信JSSDK初始化
前端初始化代码如下：
```php
<?php
//获取当前页面URL，如有需要可以自行组装
use function LFPhp\Func\http_get_current_page_url;

$app_id = ''; //请填入 1.2 中获取的【开发者ID（AppID）】

//读取 步骤3.2 存储的有效 js_ticket
$js_ticket = '';

//快速计算前端初始化相关信息
$config_info = JSAuth::generateJsSignatureSimple($js_ticket, http_get_current_page_url());
?>
<!-- 引入微信JSSDK -->
<script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js">
<script>
	const WECHAT_JS_CONFIG = {
		debug: false,
		appId: '$app_id',
		timestamp: <?=$config_info['timestamp'];?>,
		nonceStr: '<?=$config_info['noncestr'];?>',
		signature: '<?=$config_info['signature'];?>',
		jsApiList: [ //需要的js api接口列表，可自行增减
			'updateAppMessageShareData', //分享微信好友
			'updateTimelineShareData' //分享朋友圈
		]
	};
	//执行微信JSSDK初始化
	wx.config(WECHAT_JS_CONFIG);
</script>
```
### 6.2 前端微信接口调用
在上一步前端初始化代码后，可通过 `wx.ready()` 方法判断是否初始化完成，完成后可以调用相应微信前端接口
以下仅以微信分享来举例：
```javascript
//这部分代码必须在步骤6.1之后执行
wx.ready(()=>{
  wx.updateAppMessageShareData({
    title: '', //填入页面标题
    desc: '', //填入页面描述
    link: '', //分享页面地址，一般为当前页面
    imgUrl: '', //分享页面缩略图,
    success: function(){
		//成功回调
        console.log('微信分享好友信息设置成功');
    }
  });
});
```

## 7. 其他场景应用
1. [公众号扫码订阅方式登录](./mp_subscribe_login.md)
//todo
