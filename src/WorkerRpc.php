<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-7-26
 * Time: 19:07
 */

namespace lobtao\tp5helper;

use Workerman\Connection\TcpConnection;

class WorkerRpc
{
    private $func;
    private $args;
    private $callback;
    private $namespace;
    /**
     * @var TcpConnection
     */
    private $con;

    /**
     * 主方法
     * @return string|\think\response\Json|\think\response\Jsonp
     * @throws RpcException
     */
    public function handle(TcpConnection $con, $namespace, $filter = null) {
        $this->con = $con;
        $this->namespace = $namespace;
        //if ($request->isGet()) return 'API服务接口';

        //异常拦截
//        error_reporting(E_ERROR);
//        set_RpcException_handler([$this, "RpcException_handler"]);

        $this->func = isset($_GET['f']) ? $_GET['f'] : '';
        $this->args = isset($_GET['p']) ? $_GET['p'] : [];

        if (gettype($this->args) == 'string') {//微信小程序特别设置；浏览器提交过来自动转换
            $this->args = json_decode($this->args, true);
        }
        $this->callback = isset($_GET['callback']) ? $_GET['callback'] : '';

        //过滤处理
        if ($filter) {
            call_user_func_array($filter, [$this->func, $this->args]);
        }
        $result = $this->callFunc($this->func, $this->args);
        $response = $this->ajaxReturn(
            [
                'data'  => $result,//返回数据
                'retid' => 1,//调用成功标识
            ],
            $this->callback//jsonp调用时的回调函数
        );
        $this->con->send($response);
    }

    /**
     * 异常拦截回复
     * @param RpcException $RpcException
     * @return String
     */
    function RpcException_handler($RpcException) {
        $errMsg = $RpcException->getMessage();
        $response = $this->ajaxReturn([
            'retid'  => 0,
            'retmsg' => $errMsg,
        ], $this->callback);
        $this->con->send($response);
    }

    /**
     * 以‘-’来分割ajax传递过来的类名和方法名，调用该方法，并返回值
     * @param $func
     * @param $args
     * @return mixed
     * @throws RpcException
     */
    private function callFunc($func, $args) {
        $params = explode('_', $func, 2);
        if (count($params) != 2) throw new RpcException('请求参数错误');

        $svname = ucfirst($params[0]);
        $classname = $this->namespace . $svname . 'Service';
        $funcname = $params[1];
        if (!class_exists($classname)) throw new RpcException('类' . $svname . '不存在！');

//        global $objects;
//        $object = $objects[$classname];
//        if(!$objects[$classname]) {
//            $object = new $classname();
//            $objects[$classname] = $object;
//        }
        $object = new $classname();

        if (!method_exists($object, $funcname)) throw new RpcException($svname . '中不存在' . $funcname . '方法');

        //if(!$this->is_assoc($args)) throw new RpcException('参数格式错误！');//必须为序号数组嵌入一个对象[{"name":"xiao"}]
//        $data = $object->$funcname($args[0]);

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
        $data = json_encode($result);
        return $callback ? sprintf('%s(%s)',$callback,$data) : $data;
    }

    /**
     * 判断是否为序号数组
     * @param $arr
     * @return bool
     */
    function is_assoc($arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}