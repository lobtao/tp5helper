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