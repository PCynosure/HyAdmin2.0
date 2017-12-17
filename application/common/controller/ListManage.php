<?php
namespace app\common\controller;
use app\common\controller\Base;
use think\View;
use think\Loader;
use think\Request;

abstract class ListManage extends Base
{   

    public function _initialize(){

        parent::_initialize();
        
        // $url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
        // dump($url);

        if($this->model === null){
            // dump(CONTROLLER_NAME);
            $this->model = Loader::model(CONTROLLER_NAME);
        }
        // dump($this->model);
        
    }

    abstract protected function index();

    abstract protected function lists();
    
    abstract protected function insert();

    abstract protected function read($id);

    abstract protected function edit($id);

    abstract protected function update($id);

    abstract protected function delete($id);


}