<?php
namespace app\common\model;
use app\common\model\HyBase;
use think\Model;
use think\Request;
use think\Db;
use think\Loader;
use app\common\model\HyFile;
use think\Validate;

class HyList extends HyBase
{   
    // 默认主键
    protected $pk;

    protected $fieldsAliasMap = [];

    protected $sqlType = [];
    // sql条件
    protected $sqlOptions = [
        'alias'     => '',
        'associate' =>  [],
        'where'     =>  [],
        'field'     =>  [],
        'whereOr'   =>  [],
        'whereNull' =>  [],
        'whereNotNull'  =>  [],
        'whereIn'   =>  [],
        'whereNotIn'    =>  [],
        'whereBetween'  =>  [],
        'whereNotBetween'   =>  [],
        'whereLike' =>  [],
        'whereNotLike'  =>  [],
        'whereExists'   =>  [],
        'whereNotExists'    =>  [],
        'whereExp'  =>  [],
    ];
    //标题和副标题
    protected $infoOptions = [
        'title' => '',
        'subtitle'  =>  '',
        'pagetitle' => ''
    ];


    // 页面配置
    protected $pageOptions = [];

    //  字段配置
    protected $fieldsOptions = [];

    //设置一对一关联
    protected $relativeTable = [];

    public function initialize(){
        parent::initialize();

        $this->pk = $this->initPk();
        // var_dump($this);
        $this->pageOptions = [
            // 表格类型
            'type'      =>  'ajax',
            // 响应式表格
            'dtResponsive'=>true,
            // 删除 | 禁用：forbid <=> forbid|status|9
            'deleteType'=>  'delete',
            // 分页限制
            'limit'     =>  10,
            // 开启字段排序
            'sort'      =>  true,
            // 默认排序字段
            'order'     =>  $this->getPk().' desc',
            // 打印
            'print'     =>  true,
            // 导出 - xls(csv)、pdf
            'export'    =>  'xls',
            // 序号
            'number'    =>  false,
            // 显示全部
            'all'       =>  false,
            // 复选框
            'checkbox'  =>  true,
            // 操作成功刷新页面
            'okRefresh' =>  false,
            // 操作组
            'actions'   =>  [
                    'edit'  =>  [
                            'title'     =>  '编辑',
                            'max'       =>  1
                    ],
                    'delete'=>  [
                            'title'     =>  '删除',
                            // 是否需要确认
                            'confirm'   =>  true
                    ]
            ],
            // 按钮组
            'buttons'   =>  [
                    'add'   =>  [
                            'title' =>  '新增',
                            'icon'  =>  'fa-plus'
                    ]
            ],
            'formSize'      =>  'default',
            // 多表数据写入
            'tablesWrite'   =>  false,
            // 主键检查
            'checkPk'       =>  [
                    'edit'      =>  false,
                    'delete'    =>  false,
                    'detail'    =>  false
            ],
            // 详情模板
            // 'detailTpl'      =>  'Common@HyFrame/detail',
            // 编辑数据时重新发送pageOptions
            'editFields'    =>  false
        ];
    }
    /**
     * [_after_initialize 初始化后置方法]
     * @return [type] [description]
     */
    protected function _after_initialize(){
        $this->setInfoOptions($this->initInfoOptions());
        $this->setSqlType($this->initSqlType());
        $this->setSqlOptions($this->initSqlOptions());
        $this->setPageOptions($this->initPageOptions());
        $this->setFieldsOptions($this->initFieldsOptions());
        $this->setFieldsAliasMap();

        // if($this->pageOptions['tablesWrite']===true)
        //  $this->setRelativeTable();
    }

    /**
     * [setSqlType 设置sql查询类型]
     * getDtData为默认类型
     * custom为自定义类型
     * @param [type] $type [description]
     */
    public function setSqlType($type){
        $this->sqlType = $type;
    }
    /**
     * [setInfoOptions 初始化infoOptions]
     * @param [type] $option [description]
     */
    public function setInfoOptions($option){
        if(is_array($option)){
            $this->infoOptions = array_merge($this->infoOptions,$option);
        }
        
    }
    /**
     * [setInfoOptions 初始化pageOptions]
     * @param [type] $option [description]
     */
    public function setPageOptions($option){
        if(is_array($option)){  
            $this->pageOptions = array_merge($this->pageOptions,$option);
        }
    }

    /**
     * [setInfoOptions 初始化fieldsOptions]
     * @param [type] $option [description]
     */
    public function setFieldsOptions($option){
        if(is_array($option)){
            $this->fieldsOptions = array_merge($this->fieldsOptions,$option);
        }
    }

    /**
     * [setInfoOptions 初始化sqlOptions]
     * @param [type] $option [description]
     */
    public function setSqlOptions($option){
        if(is_array($option)){
            $this->sqlOptions = array_merge($this->sqlOptions,$option);
        }
    }

    // public function setRelativeTable(){

    //  foreach ($this->sqlOptions['associate'] as $k => $v) {
    //      $this->relativeTable[$v['table']] = $this->hasOne($v['table']);
    //  }
        
    // }

    /**
     * [getTitle 获取标题信息]
     * @return [type] [description]
     */
    public function getTitle(){
        return $this->infoOptions;
    }

    /**
     * [getBreadcrumb 获取面包屑]
     * @return [type] [description]
     */
    public function getBreadcrumb(){
        return '';
    }

    /**
     * [ajax 模型的ajax入口]
     * @param  [type] $type  [description]
     * @param  [type] &$json [description]
     * @return [type]        [description]
     */
    public function ajax($type,&$json,$req = null,$id = null){
        if(is_null($req)&&!is_null($id)){
            $args = [&$json,$id];
        }
        elseif(is_object($req)){
            $args = [&$json,$req,$id];
        }
        else{
            $args = [&$json,$req,$id];
        }
        call_user_func_array([&$this,'ajax_'.$type],$args);
    }
    /**
     * [ajax_list 列表获取数据入口]
     * @param  [type] &$json [description]
     * @return [type]        [description]
     */
    public function ajax_list(&$json,$id=null){
        $type = strtolower($this->sqlType);
        switch ($type) {
            case 'custom':
                $json = $this->getCustom($id);
                break;
            case 'getdtdata':
            default:
                $json = $this->getDtData($id);
                break;
        }
        
    }

    /**
     * [callbackHandler 回调控制]
     * @param  [type] $callback [回调函数的名字]
     * @param  [type] $value    [回调函数的参数]
     * @param  [type] $data     [前台提交的数组]
     * @return [type]           [description]
     */
    protected function callbackHandler($callback,$value,$data){
        // dump(func_get_args());
        $cb = $callback;
        
        if(is_string($cb)){
            if($cb = method_exists($this,'callback_'.$cb)?array(&$this, 'callback_'.$cb) : false){
                return call_user_func_array($cb, array($value,$data));
            }
        }
    }


    /**
     * [searchMap 搜索处理数组]
     * @param  [type] $search [搜索的字段]
     * @return [NULL]         
     */
    protected function searchMap($search){
        $searchArr = [];

        foreach($search as $k => $v){

            if(!isset($this->fieldsOptions[$k]['list']['search'])) continue;
            $searchOption = $this->fieldsOptions[$k]['list']['search'];
            $type = $searchOption['type']?:'like';
            $table = DTP.$this->fieldsOptions[$k]['table']?:'';
            
            /*如果使用了callbackHander就默认使用字符串查询*/
            if($callback = isset($searchOption['callback'])?$searchOption['callback']:false){
                $cb = $callback[0];
                $string = $this->callbackHandler($cb,$v);
                $this->where($string);
                contnue;
            }

            $k = isset($this->fieldsAliasMap[$k])?$this->fieldsAliasMap[$k]:$k;

            switch($type){  
                case 'daterange':
                    if($this->autoWriteTimestamp === true){
                        $v[0] = strtotime($v[0]);
                        $v[1] = strtotime($v[1]);
                    }

                    $searchArr[$table.'.'.$k] = ['between',[$v[0],$v[1]]];
                    break;
                case 'like':
                    $searchArr[$table.'.'.$k] = ['like','%'.$v.'%']; break;
                case 'select':
                case 'equal':
                default:
                    $searchArr[$table.'.'.$k] = ['eq',$v]; break;

            }
        }
        // dump($stringSearch);
        $this->where($searchArr);   
    }



    /**
     * [getDtData 根据配置的model的条件返回对应的数据]
     * @return [type] [description]
     */
    
    protected function getDtData($id = null){
        /*$result = Db::table('hy_user')
                ->where(
                    array(
                        'id'=>array(['lt',3],['eq',10],'OR'),
                        'qq'=>array(['eq',123],['neq',3434],'OR'),
                        'name|ssr'=>array(['like','thinkphp'],['like','wewe'],'or'),
                        'update_time'=>array(['in',[1,2,3,4,5]],['like',32],'AND')
                    )
                )
                ->whereOr(
                    array(
                        'phone'=>array(['lt',0],['eq',10],'AND'),
                        'create_time'=>array('eq',2)                
                    )
                )
                ->fetchSql(true)
                ->select();
        var_dump($result);*/
        //'SELECT * FROM `hy_user` WHERE  ( `id` < 3 OR `id` = 10 )  AND ( `qq` = 123 OR `qq` <> 3434 )  AND ( `name` LIKE 'thinkphp' or `name` LIKE 'wewe' )  AND ( `update_time` IN (1,2,3,4,5) AND `update_time` LIKE 32 ) OR ( `phone` < '0' AND `phone` = '10' )  OR `create_time` = 2' 
        /*$result = $this
                    ->where(function($query){
                        return $query->where('a','卡通')->where('b','欧美');
                    })
                    ->whereOr(function($query){
                        return $query->where('c','功夫')->whereOr('d','亚洲');
                    })
                    ->whereNull('id','AND')
                    ->whereBetween('cd',[1,2],'OR')
                    ->whereIn('aa',[1,2,3,4,6,5],'OR')
                    ->whereLike('name','tt','AND')
                    ->whereExp('password','> 3 AND \'password\' <= 4')
                    ->fetchSql(true)
                    ->count('id');
        dump($result);*/
        // dump(strtotime(date('Y-m-d', time())));
        $get = Request::instance()->get();

        //获取alias
        if($alias = $this->sqlOptions['alias']?:'') $this->alias($alias);

        // 先判断是否存在连表
        if($associate = $this->sqlOptions['associate']?:null&&is_array($associate)){
            $join = array();

            foreach ($associate as $k => $v) {
                // preg_match('/([\w\.\-\(\)]+)\s*(AS|as)*\s*([\w\-]*)/i', $v['table'],$m);

                array_push($join,array(DTP.$v['table'].' '.$v['alias'],trim($alias).'.'.trim($v['key']).' = '.trim(isset($v['alias'])?$v['alias']:'').'.'.trim($v['foreignKey']).(isset($v['limit'])?' AND '.$v['limit']:''),isset($v['type'])?strtoupper($v['type']):'INNER'));
            }
            $this->join($join);
            // dump($join);
        }

        
        $field = '';
        //获取field
        if($field = $this->sqlOptions['field']?:''){
            if(is_array($field)){
                $field = implode(',',$field);
            }
        }

        // dump(self::$fieldAliasMap);
        $limit = $this->pageOptions['limit']?:null;
        
        //判断分页
        $pageSize = isset($get['pageSize'])?$get['pageSize']:$limit;
        $page = isset($get['page'])?$get['page']:1;

        // dump($pageSize);
        if($page&&$pageSize){
            $this->page($page); 
            $limit = $pageSize;
        }

        $field .= ','.$alias.'.'.$this->getPk();

        $this->field($field);
        //获取where
        
        if($where = $this->sqlOptions['where']?:null&&is_array($where)){
            //扩展搜索框时要在这里加查询条件
            
            $search = array();

            //如果只是查询一条信息
            if(!is_null($id))
            {
                $where[$alias.".".$this->getPk()] = $id;
                $limit = 1;  
            }
            else
            {
                //判断是否为搜素功能
                if(method_exists($this,'searchMap')){
                    foreach ($get as $k => $v) {
                        if($k!='q'&&$k!='page'&&$k!='pageSize'){
                            $search[$k] = $v;
                        }
                    }
                    if(!empty($search))
                        $this->searchMap($search);
                } 
            }
            
            
            $this->where($where);
            // dump($where);
        }

        
        if($whereOr = $this->sqlOptions['whereOr']?:null){
            $this->whereOr($whereOr);
        }
        // dump($field);
        // dump($join);
        // dump($where);

        $obj = $this
                ->order((is_null($alias)?'':$alias).'.'.$this->pageOptions['order'])
                ->limit($limit)
                ->select();

         // dump($obj);

        if(empty($obj)){
            return array(
                'data'=>null,
                'total'=>0,
                'info' => '没有符合要求的信息！',
                'code'  => 404,
                'status'=>false
            );
        }

        $data = [];
        foreach ($obj as $k => $v) {
            $data[$k] = $v->toArray();
            // dump($v[$this->getPk()]);
            $data[$k]['_pk'] = act_encrypt($v[$this->getPk()]);

        }

        if(isset($alias)) $this->alias($alias);
        if(isset($where)) $this->where($where);
        if(isset($whereOr)) $this->whereOr($whereOr);
        if(isset($join)) $this->join($join);

        $count = $this->count((isset($alias)?$alias:'').'.'.$this->getPk())?:0;

        // dump($count);
        $json = array(
            'data'=>$data,
            'total'=>$count,
            'info' => '获取数据成功',
            'status'=>true,
            'code' => 200
        );

        return $json;
    }

    /**
     * [setFieldsAliasMap 设置字段别名映射]
     */
    protected function setFieldsAliasMap(){
        $fieldArr = explode(',',implode(',',$this->sqlOptions['field']));

        foreach ($fieldArr as $k => $v) {
            preg_match('/([\w\.\-\(\)]+)\s*(AS|as)*\s*([\w\-]*)/i', $v,$m);
            if(empty($m[2])) continue;
            $f = explode('.',$m[1])[1];
            $this->fieldsAliasMap[$m[3]] = $f;  
        }

    }

    /**
     * [ajax_insert 新增操作]
     * @param  [type] &$json [description]
     * @return [type]        [description]
     */
    protected function ajax_insert(&$json,$req){
        $res = $this->write('add',$req);
        $json['status'] = $res;
        $json['info'] = $res ?'数据新增成功！': "新增数据失败";
    }

    /**
     * [ajax_update 更新操作]
     * @param  [type] &$json [description]
     * @return [type]        [description]
     */
    protected function ajax_update(&$json,$req,$pk){
        // $pk = token_validator(Request::instance()->post('_token'));
        
        if(false === $pk){
            $json['info'] = '请勿非法操作，拉黑后果自负！';
            $json['status'] = false;
            $json['code'] = 500;
            return;
        }
        // dump($req);
        $res = $this->write('edit',$req,$pk);
       
        $json['status'] = $res;
        $json['info'] = $res ?'数据修改成功！':'修改数据失败';
        $json['code'] = $res ?200:500;

        return;
    }


    /**
     * [ajax_edit 获取要修改数据的操作]
     * @param  [type] &$json [description]
     * @return [type]        [description]
     */
    protected function ajax_edit(&$json,$id){
        // $data = Request::instance()->post();
        //这个段代码做示例要用
        $tokenValue = act_encrypt($id);
        
        // $data['pk'] = act_encrypt($data['pk']);

        $pk = $id;

            
        $error = '无权访问要请求的数据！';
        $field = $this->getPk();
        if(!$field || !$pk){
            $json['info'] = $error;
            $json['status'] = false;
            return; 
        } 
        // 数据访问检查
        $value=$this->where(array($field=>array('eq', $pk)))->find();
        // dump($value);
        if(!$value){
            $json['info'] = '数据不存在！';;
            $json['status'] = false;
            $json['code'] = 404;
            return;
        } 

        $json['info'] = '获取数据成功！';
        $json['status'] = true;
        $json['val'] = $value;
        $json['token'] = tokenBuilder($tokenValue);
        $json['total'] = 1;

        // 主键检查
        /*if(false===$this->checkPk($pk, 'edit')) return $this->setError($error);
        $log=array('msg'=>'数据描述：'.implode('|', $value), 'style'=>'text-info');
        Hook::listen('hy_log', $log);*/
    }


    /**
     * [ajax_delete 删除操作]
     * @param  [type] &$json [description]
     * @return [type]        [description]
     */
    protected function ajax_delete(&$json,$id){
        // $arr = Request::instance()->post();
        //之后将会通过post获取一个经过加密的id
        /*if(!is_array($arr=explode(',', (I('pk'))))){
            $json['info']='数据非法！';
            return false;
        }*/
        if(!is_array($id) && !is_numeric($id)){
            $json['info'] = 'id格式错误！';
            $json['status'] = false; 
            $json['code']   = 500;
            return;
        } 
        if(is_array($id)){
            foreach ($id as &$v){
                $v=act_decrypt($v);
            }  
        }

        // if(is_numeric($id)){}

        $this->hy_delete($json,$id);
        
       
    }

    /**
     * [del 支持逻辑删除]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    protected function hy_delete(&$json,$id){
        // dump($id);
        $error = '无权访问要请求的数据！';
        $pk = $this->getPk();
        if(!$pk) return false;
        // 数据访问检查
        // $value=$this->where(array($pk=>array('in', $id)))->find();

        /*
        // 主键检查
        if(false === $this->checkPk($arr,'delete')) return $this->setError($error);
        //记录日志
        $log=array('msg'=>'数据描述：'.implode('|', $value), 'style'=>'text-info');
        Hook::listen('hy_log', $log);*/
        // dump($this);
        $model  =  get_class($this);
        $model = new $model();
        
        // dump($this->pageOptions['deleteType'])
        if('delete'==$this->pageOptions['deleteType']){

            $res = $model::destroy($id);
            // dump($res);
            // $this->where(array($pk=>array('IN', $id)))->delete();
            $json['info'] = '删除成功！';
            $json['status'] = true; 
            $json['code']   = 200;
            return;
        }

        $del=explode('|', $this->pageOptions['deleteType']);

        if(2 !== count($del)){
            $json['info'] = 'deleteType配置错误！';
            $json['status'] = false;
            $json['code']   = 500;
            return;
        } 

        $model->where([$pk=>['IN', $id], $del[0]=>['lt', $del[1]]])
              ->update([$del[0]=>$del[1]]);

        $json['info'] = '删除成功！';
        $json['status'] = true;
        $json['code']   = 200;
        return; 
    }

    /**
     * [write 对数据库进行的写操作，涉及多表写入]
     * @param  [string] $type [写入类型insert/update]
     * @param  [int] $pk   [主键]
     * @return [boolean]       [是否写入成功]
     */
    protected function write($type,$req,$pk=null){
        // $file = request()->file();
        // dump($file);
        // dump($_FILES);
        
        $class = CONTROLLER_NAME;
        $model = Loader::model($class);
        // dump($theModel);
        $data = $req;
        // var_dump($data);
        // $data = [
        //     'username'=>'SZA',
        //     'user_gender'=>'114',
        //     'email'=>'347756896@qq.com',
        //     'article_title'=>'love galore'
        // ];

        $model->fieldsTypeDealing();

        $valide = $model->fieldsValidate($data);
        if($valide['status']===false) return $valide;

        
        $model->initCallback($data);
        $model->autoFill($type,$data);

        $dataList = [];
        $current = $model->table;
        
        if(isset($model->pageOptions['tablesWrite'])&&$model->pageOptions['tablesWrite'] === true){

            foreach ($model->fieldsOptions as $k => $v) {
                if(empty($v['form']) || (empty($v['form'][$type]) && false === $v['form'][$type])) continue;
                if(!$v['table']) $v['table'] = $current;
                
                if(isset($data[$k])&&$data[$k]!==false){
                    $t = $k;
                    if(isset($model->fieldsAliasMap[$k])) $k = $model->fieldsAliasMap[$k];
                    $dataList[DTP.$v['table']][$k] = $data[$t];
                }
            }

            // $dataList[$current][$this->getPk()]=$pk;
            $associate = $model->sqlOptions['associate'];
            $res = true;
            $keys = array_keys($dataList);
            $pkArr = [];
            // dump($dataList);

            if($type == 'add'){
                //插入并获取返回的id
                $model->isUpdate(false);

                foreach ($dataList[$current] as $k => $v) {
                    $model->$k = $v;
                }
                $model->allowField(true)->save();

                $pkName = $model->getPk();
                $id = $model->$pkName;
                // dump($id);
                $res = $id?true:false;

                foreach ($associate as $k => $v) {
                    $table = DTP.$v['table'];
                    if(in_array($table,$keys)){
                        $dataList[$table][$v['foreignKey']] = $id;
                        //返回的主键先暂存起来
                        $pkArr[$table] = Db::table($table)->insertGetId($dataList[$table]);
                        $res = $res && $pkArr[$table];
                    }           
                
                }

                //如果任意一张表新增不成功全部回滚
                if(!$res){
                    foreach ($pkArr as $k=>$v){
                        if($v>1) Db::table($k)->delete($v);
                    }
                    $model->delete($id);
                }
            }elseif($type == 'edit'){

                // dump($dataList[$current]);
                $model->isUpdate(true)->allowField(true)->save($dataList[$current],[$this->getPk()=>$pk]);
                // dump(array($dataList[$current],[$this->getPk()=>$pk]));
                // dump($keys);
                foreach ($associate as $k => $v) {
                    $table = DTP.$v['table'];
                    
                    if(in_array($table,$keys)){
                        //返回的主键先暂存起来
                        $pRes = Db::table($table)
                                        ->where($v['foreignKey'],$pk)
                                        ->update($dataList[$table]);
                        $pkArr[$table] = ($pRes == 0)?false:true;
                        $res = $res && $pkArr[$table];
                    }           
                }
                
            }

            return $res;
        }else{
            $dataList = [];
            $t = null;
            foreach($data as $k => $v) {
                $t = $k;
                if(isset($model->fieldsAliasMap[$k])){
                    $k = $model->fieldsAliasMap[$k];
                } 

                $dataList[$k] = $data[$t];          
            }
            
            if($type == 'add'){

                // $this_pk = $model->getPk();
                $res = $model->data($dataList)->allowField(true)->save();
                return !!$res;

            }elseif($type == 'edit'){
                $res = $model->isUpdate(true)->allowField(true)->save($dataList,[$this->getPk()=>$pk]);
                return !!$res;
            }
        }   
    }

    /**
     * [fieldsValidate 使用TP5自带验证器进行验证]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    protected function fieldsValidate($data){


        $rules = $msgs = [];
        // dump($data);
        $data_key = array_keys($data);
        
        foreach ($this->fieldsOptions as $k => $v) {

            if(!in_array($k,$data_key)||empty($v['form'])||empty($v['form']['validate'])) continue;

            if($v['form']['type']=='text')
                $data[$k] = htmlspecialchars($data[$k],ENT_QUOTES);
            elseif($v['form']['type']=='textarea')
                $data[$k] = remove_xss($data[$k]);

            $fieldRule = $v['form']['validate'];
            if(is_string($fieldRule)){
                 $rules[$k] = $fieldRule;
            }
            if(is_array($fieldRule)){
                if(isset($fieldRule['rule'])){
                    $rules[$k] = $fieldRule['rule'];
                }else{
                    $rules[$k] = $fieldRule;
                }

                if(isset($fieldRule['msg'])){
                    foreach ($fieldRule['msg'] as $k1 => $v1) {
                        $msgs[$k.'.'.$k1] = $v1;
                    }       
                }
                
            }
        }
        
        $validate = new Validate($rules,$msgs);
        $result = $validate->check($data);

        $info = [];
        $info['status'] = $result;
        $info['info'] = ($result === false)?$validate->getError():'验证通过！';
        
        return $info;
    }


    /**
     * [fieldsTypeDealing 数据类型预处理]
     * @param  [type] &$field [description]
     * @return [type]         [description]
     */
    protected function fieldsTypeDealing(){
        foreach($this->fieldsOptions as $k=>&$v){
            if(isset($v['form'])&&is_array($v['form'])){
                $v['form']['edit'] = $v['form']['add'] = true;
                
                if(isset($v['form']['type'])){
                    switch(strtolower($v['form']['type'])){
                        case 'daterange':
                            break;
                        case 'select':
                            break;
                        /*case 'file':
                            $v['form']['callback'] = array('uploadFile');
                            break;*/
                    }
                }else{
                    $v['form']['type'] = 'text';
                }           
            }

            if(isset($v['form']['fill'])){
                $fill = $v['form']['fill'];
                foreach($fill as $k1=>$v1){
                    // if($k1 === 'both') $v['form']['edit'] = $v['form']['add'] = true;
                    if($k1 == "add") 
                        $v['form']["edit"] = false;
                    elseif($k1 == "edit")
                        $v['form']['add'] = false;
                }
            }   
        }
    }
    
    protected function initCallback(&$data){

        foreach ($this->fieldsOptions as $k => &$v) {
            if($v['form']['type'] === 'file'){
                /*if(!isset($v['form']['file'])){
                    $v['form']['file'] = array(
                        'ext'   =>  'jpg,png,gif',
                        'size'=>521000,
                        'mimeType'=>'image/jpeg,image/png,image.gif'
                    )
                }*/
                // $data[$k] = $this->uploadFile($k,$v['form']['file']);
                continue;
            }

            $cbArr = isset($v['form']['callback'])?$v['form']['callback']:null;
            if(is_null($cbArr)) continue;

            
            $cb = $cbArr[0];
            // var_dump($data[$k]);
            $data[$k] = $this->callbackHandler($cb,$data[$k],$data);
        }
        // dump($data);
    }

    /**
     * [autoFill 自动写入功能]
     * @param  [type] $type  [description]
     * @param  [type] &$data [description]
     * @return [type]        [description]
     */
    private function autoFill($type,&$data){
        $fieldsOptions = $this->fieldsOptions;
        foreach ($fieldsOptions as $k => $v) {
            if(isset($v['form']['fill'])){
                $fill = $v['form']['fill'];
                if(!is_array($fill)||(!isset($fill[$type])&&!isset($fill['both']))) continue;
                
                if($cbArr = $fill[$type]?:$fill['both']){
                    $cb = array_shift($cbArr);
                    $data[$k] = $this->callbackHandler($cb,$cbArr,$data);
                    if($data[$k]===false) unset($data[$k]);
                }
            }
            
        }
        

    }


    protected function callback_value($value,$data){
        // dump($value);
        return $value[0];
    }

    protected function uploadFile($filename,$options){
        $obj = HyFile::upload($filename,$options);
        
    }

    /**
     * [remove_xss 过滤xss]
     * @param  [type] $val [description]
     * @return [type]      [description]
     */
    public function remove_xss($val) {
       // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
       // this prevents some character re-spacing such as <java\0script>
       // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
       $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
       // straight replacements, the user should never need these since they're normal characters
       // this prevents like <IMG SRC=@avascript:alert('XSS')>
       $search = 'abcdefghijklmnopqrstuvwxyz';
       $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $search .= '1234567890!@#$%^&*()';
       $search .= '~`";:?+/={}[]-_|\'\\';
       for ($i = 0; $i < strlen($search); $i++) {
          // ;? matches the ;, which is optional
          // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
          // @ @ search for the hex values
          $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
          // @ @ 0{0,7} matches '0' zero to seven times
          $val = preg_replace('/( {0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
       }
       // now the only remaining whitespace attacks are \t, \n, and \r
       $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
       
       $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
       $ra = array_merge($ra1, $ra2);
       $found = true; // keep replacing as long as the previous round replaced something
       while ($found == true) {
          $val_before = $val;
          for ($i = 0; $i < sizeof($ra); $i++) {
             $pattern = '/';
             for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                   $pattern .= '(';
                   $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                   $pattern .= '|';
                   $pattern .= '|( {0,8}([9|10|13]);)';
                   $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
             }
             $pattern .= '/i';
             $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
             $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
             if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
             }
          }
       }
       return $val;
    }



}