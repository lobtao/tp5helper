<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-7-26
 * Time: 19:07
 */

namespace lobtao\tp5helper;

use think\Log;
use Workerman\Connection\TcpConnection;

class RpcWorker extends BaseRpc
{

    /**
     * 主方法
     * @param $namespace
     * @param null $filter
     * @return String|\think\response\Json|\think\response\Jsonp
     */
    public function handle( $namespace, $filter = null) {
        $this->namespace = $namespace;
        //if ($request->isGet()) return 'API服务接口';

        //异常捕获
        try {
            $this->func = isset($_REQUEST['f']) ? $_REQUEST['f'] : '';
            $this->args = isset($_REQUEST['p']) ? $_REQUEST['p'] : [];

            if (gettype($this->args) == 'string') {//微信小程序特别设置；浏览器提交过来自动转换
                $this->args = html_entity_decode($this->args);
                $this->args = json_decode($this->args, true);
            }
            $this->callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '';

            //过滤处理
            if (isset($filter)) {
                call_user_func_array($filter, [$this->func, $this->args]);
            }
            $result = $this->callFunc($this->func, $this->args);

            $data = $this->ajaxReturn(
                [
                    'data'  => $result,//返回数据
                    'retid' => 1,//调用成功标识
                ],
                $this->callback//jsonp调用时的回调函数
            );
            return $data;
        }catch(\Exception $ex){
            return $this->exception_handler($ex);
        }
    }

}