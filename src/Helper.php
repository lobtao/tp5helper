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