<?php
namespace lobtao\tp5helper;

use think\Log;
use think\Response;

class RpcController {

    private $func;
    private $args;
    private $callback;
    private $namespace;

    /**
     * 主方法
     * @param $namespace
     * @param null $filter
     */
    public function handle($namespace, $filter = null) {

        $this->namespace = $namespace;

        $request = request();
        //if ($request->isGet()) return 'API服务接口';

        //异常拦截
//        error_reporting(E_ERROR);
//        set_exception_handler([$this, "exception_handler"]);
        try {
            $this->func = $request->param('f');
            $this->args = $request->param('p', []);

            if (gettype($this->args) == 'string') {//微信小程序特别设置；浏览器提交过来自动转换
                $this->args = json_decode($this->args, true);
            }
            $this->callback = $request->param('callback');

            //过滤处理
            if ($filter) {
                call_user_func_array($filter, [$this->func, $this->args]);
            }

            $result   = $this->callFunc($this->func, $this->args);
            $response = $this->ajaxReturn(
                [
                    'data'  => $result,//返回数据
                    'retid' => 1,//调用成功标识
                ],
                $this->callback//jsonp调用时的回调函数
            );
            return $response;
        } catch (\Exception $exception) {
            if ($exception instanceof RpcException) {
                $errMsg = $exception->getMessage();
            } else {
                $errMsg = '系统异常';
            }
            $response = $this->ajaxReturn([
                'retid'  => 0,
                'retmsg' => $errMsg,
            ], $this->callback);

            $msg = sprintf("Trace:%s\nClass: %s\nFile: %s\nLine: %s\n异常描述: %s", $exception->getTraceAsString(), get_class($exception), $exception->getFile(), $exception->getLine(), $exception->getMessage());
            if (class_exists('\think\facade\Log')) {
                \think\facade\Log::error($msg);
            } else {
                \think\Log::error($msg);
            }
            return $response;
        }
    }

    /**
     * 以‘-’来分割ajax传递过来的类名和方法名，调用该方法，并返回值
     * @param $func
     * @param $args
     * @return mixed
     */
    private function callFunc($func, $args) {

        $params = explode('_', $func, 2);
        if (count($params) != 2) throw new RpcException('请求参数错误');

        $svname    = ucfirst($params[0]);
        $classname = $this->namespace . $svname . 'Service';
        $funcname  = $params[1];
        if (!class_exists($classname)) throw new RpcException('类' . $svname . '不存在！！！');
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
    private function ajaxReturn($result, $callback) {

        return $callback ? jsonp($result) : json($result);
    }

}