Curl.php 使用示例
-----

```php
use lobtao\thinkphp5\curl;
$curl = new curl\Curl();

//get http://example.com/
$response = $curl->get('http://example.com/');

if ($curl->errorCode === null) {
   echo $response;
} else {
     // List of curl error codes here https://curl.haxx.se/libcurl/c/libcurl-errors.html
    switch ($curl->errorCode) {
    
        case 6:
            //host unknown example
            break;
    }
} 
```

```php
// GET request with GET params
// http://example.com/?key=value&scondKey=secondValue
$curl = new curl\Curl();
$response = $curl->setGetParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->get('http://example.com/');
```


```php
// POST URL form-urlencoded 
$curl = new curl\Curl();
$response = $curl->setPostParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->post('http://example.com/');
```

```php
// POST with special headers
$curl = new curl\Curl();
$response = $curl->setPostParams([
        'key' => 'value',
        'secondKey' => 'secondValue'
     ])
     ->setHeaders([
        'Custom-Header' => 'user-b'
     ])
     ->post('http://example.com/');
```


```php
// POST JSON with body string & special headers
$curl = new curl\Curl();

$params = [
    'key' => 'value',
    'secondKey' => 'secondValue'
];

$response = $curl->setRequestBody(json_encode($params))
     ->setHeaders([
        'Content-Type' => 'application/json',
        'Content-Length' => strlen(json_encode($params))
     ])
     ->post('http://example.com/');
```

```php
// Avanced POST request with curl options & error handling
$curl = new curl\Curl();

$params = [
    'key' => 'value',
    'secondKey' => 'secondValue'
];

$response = $curl->setRequestBody(json_encode($params))
     ->setOption(CURLOPT_ENCODING, 'gzip')
     ->post('http://example.com/');
     
// List of status codes here http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
switch ($curl->responseCode) {

    case 'timeout':
        //timeout error logic here
        break;
        
    case 200:
        //success logic here
        break;

    case 404:
        //404 Error logic here
        break;
}

//list response headers
var_dump($curl->responseHeaders);
```

RpcController.php 远程调用示例
-----
ServiceController.php 服务控制器类

```php
namespace app\index\controller;

use lobtao\tp5helper\RpcController;
use lobtao\tp5helper\RpcException;
use think\Session;

class ServiceController extends RpcController {

    function index() {
        $this->handle('app\\service\\', function ($func, $params) {
            if (in_array(strtolower($func), ['user_login', 'user_logout'])) //登录方法不判断
                return;

            if(!Session::get('user')){
                throw new RpcException('尚未登录，不能访问');
            }
        });
    }
}
```

UserService.php 服务类

```php
namespace app\service;


use think\Session;

class UserService {
    function login($params){
        Session::set('user', ['name'=>'远思']);
    }

    function logout($params){
        Session::delete('user');
        Session::destroy();
    }

    function test(){
        return '恭喜，你可以正常访问此方法';
    }
}
```


web端js调用示例
-----
js端调用server.js库 依赖jquery.js
```javascript
function client(baseUrl){
    var client = {
        ajax: function (func, args, dataType) {
            var _this = this;
            var def = $.Deferred();
            $.ajax({
                type: "POST",
                url: baseUrl,
                data: {f: func, p: JSON.stringify(args)},
                success: function (ret) {
                    if (ret.retid == 0) {
                        if (_this.onerror) {
                            _this.onerror(ret.retmsg)
                        }
                        def.reject(ret.retmsg);
                    } else {

                        def.resolve(ret.data);
                    }
                },
                dataType: dataType
            });
            return def;
        },
        onerror: null,
        invoke: function (func, args, callback) {
            var promise = this.ajax(func, args, 'json');
            if (callback) {
                promise.then(callback);
            }
            return promise;
        },
        invokep: function (func, args, callback) {
            var promise = this.ajax(func, args, 'jsonp');
            if (callback) {
                promise.then(callback);
            }
            return promise;
        }
    };
    //全局异常处理
    client.onerror = function (err) {
        alert(err);
    };

    return client;
}
```

js调用后端PHP服务类示例，先引入server.js
```javascript

var client = client("http://localhost/testpro/index.php/index/service/index");//服务控制类地址

client.invoke('test_hello',[]).then(function(ret){
    console.log(ret)
});

client.invoke('user_login',[{
    name:'用户名',
    password:'密码',
}]).then(function(ret){
  console.log(ret);
});

client.invoke('user_test',[]).then(function(ret){
  console.log(ret);
});

client.invoke('user_logout',[{
     name:'用户名',
 }]).then(function(ret){
  console.log(ret);
});

```

小程序调用示例
-----
小程序调用 client.js 库
```javascript
var serviceUrl = 'http://localhost/testpro/index.php/index/service/index';//服务控制类地址

function invoke(func, args, callback) {
    wx.request({
        url: serviceUrl,
        data: {
            f: func,
            p: JSON.stringify(args)
        },
        header: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        dataType: 'json',
        method: 'POST',
        success: function (ret) {
            if (ret.data.retid == 1) {
                callback(ret.data.data);
            } else {
                wx.showModal({
                    showCancel: false,
                    confirmColor: '#ea644a',
                    content: typeof ret.data == 'string' ? ret.data : ret.data.retmsg,
                });
            }
        },
        complete:function(){
        },
    })
}

module.exports = {
    invoke: invoke
}
```

调用示例
```javascript

var client = require('client.js');

client.invoke('user_login', [{
    name:'用户名',
    password:'密码',
}], function (ret) {
    console.log(ret);
});

client.invoke('user_test', [], function (ret) {
    console.log(ret);
});

client.invoke('user_logout', [{
    name:'用户名',
}], function (ret) {
    console.log(ret);
});
```

Workerman 提供Rpc服务
-----
ServiceController.php 服务控制器类

```php
/**
 * Created by lobtao.
 * User: Administrator
 * Date: 2017-7-26
 * Time: 16:51
 * workerman的性能是apache的239倍
 */

namespace app\admin\command;

use lobtao\tp5helper\WorkerRpc;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\Worker;

class Api extends Command
{
    protected function configure() {
        $this->setName('api')
            ->addArgument('args')
            ->addArgument('daemon')
            ->setDescription('api接口调用');
    }

    protected function execute(Input $input, Output $output) {
        global $argv;
        array_shift($argv);//弹出第一个参数
        if ($argv[1] == 'startd') {
            $argv[1] = 'start';
            $argv[2] = '-d';
        }

        $worker->onMessage = function (TcpConnection $con, $data) {
            if($data['server']['REQUEST_URI'] == '/favicon.ico') return;//忽略favicon.ico请求
            Http::header('Access-Control-Allow-Origin:*');//允许前端跨域请求
            $rpc = new WorkerRpc();
            $rpc->handle($con, 'app\\service\\');
        };
        Worker::runAll();
    }
}

./think api start
./think api startd
```

