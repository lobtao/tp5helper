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
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'yssoft'))//需要在apicloud config.xml里配置<preference name="userAgent" value="yssoft" />
            return sprintf("func_openWin('%s','%s')", url($router, '', true, true), config('title'));
        else
            return sprintf("window.location.href='%s'", url($router, '', true, true));

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
            $replace = array_merge($replace,[
                config('template.layout_item') => \think\View::instance(config('template'))->fetch($template, $vars, $replace)
            ]);
            return \think\Response::create('./' . config('template.layout_name'), 'view', $code)->replace($replace);
        } else {
            return \think\Response::create($template, 'view', $code);
        }
    }
}