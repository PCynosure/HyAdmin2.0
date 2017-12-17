<?php
namespace app\admin\controller\Student;

use app\common\controller\ListManage;
use think\Request;

class Student extends ListManage{
    protected $resJson = [
                'data'=>'',
                'info'=>'',
                'status'=>false,
                'code'  => 200,
                'total' => ''
              ];
    public function index(){
        //初始化视图类
        // $view = new View();
        // 基础访问认证
        // $this->baseAccessAuth();
        //记录日志
        // $log = array('msg'=>'管理列表页');
        // Hook::listen('hy_log', $log);
        
        //设置页面标题
        // $view->title = $this->model->getTitle();
        //设置面包屑
        // $view->breadCrumb
        
        
        /*$listInfo = array(
            'title'=>$this->model->getTitle(),
            'breadCrumb'=>$this->model->getBreadcrumb(),
        )
        
        return json($listInfo);*/
        //输出json数据（需要json_encode）
        
        //加载额外补充js（可能不需要这个功能）
        // dump(324);
        dump(123); 
        return 123;
        // return $this->fetch('admin@User/User/all');
    }



    /**
     * 显示资源列表 获取所有数据
     *
     * @return \think\Response
     */
    public function list()
    {

        $json = $this->resJson;
        $this->model->ajax('list',$json);

        return json($json);
    }

    /**
     * 保存新建的资源  新增数据
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function insert()
    {
        $request = Request::instance()->param;
        var_dump($request);

        $json = $this->resJson;
        // $this->model->ajax("insert",$json,$request);

        return json($json);
    }

    /**
     * 显示指定的资源 获取某个数据的值
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $request = Request::instance()->param;
        // var_dump($request);

        // $json = $this->resJson;
        // $this->model->ajax("list",$json,$request);

        return json($json);
    }

    /**
     * 显示编辑资源表单页. 获取某个数据的值进行编辑
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $json = $this->resJson;
        $this->model->ajax("edit",$json,null,$id);

        return json($json);
    }

    /**
     * 保存更新的资源 更新某个数据的值
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update()
    {
        $json = $this->resJson;
        $this->model->ajax("update",$json,$request,$id);

        return json($json);
    }

    /**
     * 删除指定资源 删除某个数据
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        $json = $this->resJson;
        $this->model->ajax("delete",$json,null,$id);

        return json($json);
    }
}
