<?php
namespace lobtao\tp5helper;

use think\Log;
use think\Response;

class RpcController extends BaseRpc{

    /**
     * 主方法
     * @param $namespace
     * @param null $filter
     * @return string
     */
    public function handle($namespace, $filter = null) {

        $this->namespace = $namespace;

        $request = request();
        //if ($request->isGet()) return 'API服务接口';

        //异常拦截
        try {
            $this->func = $request->param('f');
            $this->args = $request->param('p', []);

            if (gettype($this->args) == 'string') {//微信小程序特别设置；浏览器提交过来自动转换
                //$this->args = html_entity_decode($this->args);//chrome端加上这个会报错
                $this->args = json_decode($this->args, true);
            }
            $this->callback = $request->param('callback');

            //过滤处理
            if (isset($filter)) {
                call_user_func_array($filter, [$this->func, $this->args]);
            }

            $result   = $this->callFunc($this->func, $this->args);
            $result=$this->formatResult($result,'',1);
            $response = $this->ajaxReturn($result,
                $this->callback//jsonp调用时的回调函数
            );
            return $response;
        } catch (\Exception $ex) {
            return $this->exception_handler($ex);
        }
    }

}