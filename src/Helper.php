<?php
/**
 * Created by lobtao.
 * User: lobtao
 * Date: 2017-5-24
 * Time: 19:24
 */


if (!function_exists('ROOT')) {
    /**
     * 当前应用URL路径
     * @return string
     */
    function ROOT() {
        // 基础替换字符串
        $request = \think\Request::instance();
        $base = $request->root();
        $root = strpos($base, '.') ? ltrim(dirname($base), DS) : $base;
        if ('' != $root) {
            $root = '/' . ltrim($root, '/');
        }
        return $root;
    }
}


if (!function_exists('V')) {
    /**
     * 快捷校验方法
     * @param \think\Validate $validate
     * @param String $scenario
     * @param Array $params
     * @param bool|true $showException
     * @return string
     * @throws Exception
     */
    function V($validate, $scenario, $params, $showException = true) {
        //校验输入值
        $msg = '';
        if (!$validate->scene($scenario)->check($params)) {
            $msg = $validate->getError();

            if ($showException) throw new \lobtao\tp5helper\RpcException($msg);
        }
        return $msg;
    }
}

if (!function_exists('getValue')) {
    /**
     * 获取表单formData里字段值
     * @param $array
     * @param $key
     * @param int $type
     * @return int|string
     */
    function getValue($array, $key, $type = 0) {
        switch ($type) {
            case 0://字符串
                return array_key_exists($key, $array) ? $array[$key] : '';
                break;
            case 1://整数、浮点数
                return array_key_exists($key, $array) ? $array[$key] : 0;
                break;
        }
    }
}

if (!function_exists('createUrl')) {
    /**
     * 生成url访问地址
     * @param $router
     * @return string
     */
    function createUrl($router) {
        $webview_type = request()->param('WEBVIEW_TYPE');
        $url = url($router, ['WEBVIEW_TYPE' => $webview_type], true, true);
        //1、小程序
        if ($webview_type == 'miniprogram')
            //公众号和小程序webview里都有MicroMessenger 安卓里有miniprogram iphone里没有miniprogram，所以增加参数WEBVIEW_TYPE区分是小程序里webview
            return "wx.miniProgram.navigateTo({url: '/pages/webview/webview?url={$url}'})";
        //2、APP
        else if (strpos($_SERVER['HTTP_USER_AGENT'], 'yssoft'))//需要在apicloud config.xml里配置<preference name="userAgent" value="yssoft" />
            return sprintf("func_openWin('%s','%s')", $url, config('title'));
        //3、wap和公众号
        else
            return sprintf("window.location.href='%s'", $url);
    }
}

if (!function_exists('layout')) {
    /**
     * 布局母板页输出
     * @param string $template
     * @param array $vars
     * @param array $replace
     * @param int $code
     * @return $this|\think\Response|\think\response\Json|\think\response\Jsonp|\think\response\Redirect|\think\response\View|\think\response\Xml
     */
    function layout($template = '', $vars = [], $replace = [], $code = 200) {
        if (config('template.layout_on')) {
            $replace = array_merge($replace, [
                config('template.layout_item') => \think\View::instance(config('template'))->fetch($template, $vars, $replace)
            ]);
            return \think\Response::create('./' . config('template.layout_name'), 'view', $code)->replace($replace);
        } else {
            return \think\Response::create($template, 'view', $code);
        }
    }
}
if (!function_exists('client_ip')) {
    function client_ip() {
        $unknown = 'unknown';
        $ip = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        /*
        处理多层代理的情况
        或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
        */
        if (false !== strpos($ip, ','))
            $ip = reset(explode(',', $ip));
        return $ip;
    }
}