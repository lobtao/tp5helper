<?php
/**
 * Created by lobtao.
 * Date: 2018/4/27
 * Time: 上午10:36
 */

namespace lobtao\tp5helper;


abstract class BaseRpc {
    protected $func;
    protected $args;
    protected $callback;
    protected $namespace;

    public abstract function handle($namespace, $filter = null);

    /**
     * 异常拦截回复
     * @param RpcException $exception
     * @return String
     */
    protected function exception_handler($exception) {

        if ($exception instanceof RpcException) {
            $errMsg = $exception->getMessage();
        } else {
            if(config('showerror')){
                $errMsg = $exception->getMessage();
            }else{
                $errMsg = '系统异常';
            }
        }
        $data = $this->ajaxReturn([
            'retid'  => 0,
            'retmsg' => $errMsg,
        ], $this->callback);


        $msg = sprintf("Trace:%s\nClass: %s\nFile: %s\nLine: %s\n异常描述: %s", $exception->getTraceAsString(), get_class($exception), $exception->getFile(), $exception->getLine(), $exception->getMessage());
        if (class_exists('\think\facade\Log')) {
            \think\facade\Log::error($msg);
        } else {
            \think\Log::error($msg);
        }

        return $data;
    }

    /**
     * 以‘-’来分割ajax传递过来的类名和方法名，调用该方法，并返回值
     * @param $func
     * @param $args
     * @return mixed
     * @throws RpcException
     */
    protected function callFunc($func, $args) {

        $params = explode('_', $func, 2);
        if (count($params) != 2) throw new RpcException('请求参数错误');

        $svname    = ucfirst($params[0]);
        $classname = $this->namespace . $svname . 'Service';
        $funcname  = $params[1];
        if (!class_exists($classname)) throw new RpcException('类' . $classname . '不存在！');

        $object = new $classname();
        if (!method_exists($object, $funcname)) throw new RpcException($svname . '中不存在' . $funcname . '方法');
        $data = call_user_func_array([$object, $funcname], $args);

        return $data;
    }

    /**
     * ajax返回
     * @param $result
     * @param $callback
     * @return \think\response\Json|\think\response\Jsonp
     */
    protected function ajaxReturn($result, $callback) {

        $data = json_encode($result, JSON_UNESCAPED_UNICODE);
        return $callback ? sprintf('%s(%s)', $callback, $data) : $data;
    }

    /**
     * 判断是否为序号数组
     * @param $arr
     * @return bool
     */
    protected function is_assoc($arr) {

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}