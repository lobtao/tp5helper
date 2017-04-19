<?php
namespace lobtao\tp5helper;

use think\Controller;
use think\Response;

class RpcController extends Controller {

    private $func;
    private $args;
    private $callback;
    private $namespace;

    /**
     * 主方法
     * @return string|\think\response\Json|\think\response\Jsonp
     * @throws \Exception
     */
    public function handle($namespace) {
        $this->namespace = $namespace;

        $request = $this->request;
        if ($request->isGet()) return 'API服务接口';

        error_reporting(E_ERROR);
        set_exception_handler([$this, "exception_handler"]);

        $this->func = $request->param('f');
        $this->args = $request->param('p', []);
        if (gettype($this->args) == 'string') {//微信小程序特别设置；浏览器提交过来自动转换
            $this->args = json_decode($this->args, true);
        }
        $this->callback = $request->param('callback');

        $result = $this->callFunc($this->func, $this->args);
        return $this->ajaxReturn(
            [
                'data'  => $result,//返回数据
                'retid' => 1,//调用成功标识
            ],
            $this->callback//jsonp调用时的回调函数
        );
    }

    /**
     * 异常拦截回复
     * @param \Exception $exception
     * @return String
     */
    function exception_handler($exception) {
        $errMsg = $exception->getMessage();
        $response = $this->ajaxReturn([
            'retid'  => 0,
            'retmsg' => $errMsg,
        ], $this->callback);
        $response->send();
    }

    /**
     * 以‘-’来分割ajax传递过来的类名和方法名，调用该方法，并返回值
     * @param $func
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function callFunc($func, $args) {
        $params = explode('_', $func, 2);
        if (count($params) != 2) throw new \Exception('请求参数错误');

        $svname = ucfirst($params[0]);
        $classname = $this->namespace . $svname . 'Service';
        $funcname = $params[1];
        if (!class_exists($classname)) throw new \Exception('类' . $svname . '不存在！！！');
        $object = new $classname();
        if (!method_exists($object, $funcname)) throw new \Exception($svname . '中不存在' . $funcname . '方法');
        $data = call_user_func_array([$object, $funcname], $args);
        return $data;
    }

    /**
     * ajax返回
     * @param $result
     * @param $callback
     * @return \think\response\Json|\think\response\Jsonp
     */
    public function ajaxReturn($result, $callback) {
        return $callback ? jsonp($result) : json($result);
    }

}