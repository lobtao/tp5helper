<?php
/**
 * Created by lobtao.
 * Date: 2018/5/11
 * Time: 上午11:26
 */

namespace lobtao\tp5helper;


use PHPSocketIO\SocketIO;
use Workerman\Worker;

abstract class PHPSocketIO {
    public $io;
    protected $opts = [];
    protected $port = '9982';

    /**
     * 架构函数
     * @access public
     */
    public function __construct() {

        // 实例化 SocketIO 服务
        $this->io = new SocketIO($this->port, $this->opts);
        // 初始化
        $this->init();
        // Run worker
        Worker::runAll();
    }

    protected function init() {
    }
}