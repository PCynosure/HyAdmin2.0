<?php
namespace app\admin\model;
use app\common\model\HyList;

class User extends HyList
{   
    public $table = DTP.'user';
    // protected $autoWriteTimestamp = true;
    //若数据库的时间格式是datetime就改成 protected $autoWriteTimestamp = 'datetime'

    public function initialize(){
        parent::initialize();
        // var_dump($this->where(array("status"=>1))->column('id,name'));
    }
    
    /**
     * [initPk 初始化主键]
     * @return [string] [主键字段]
     */
    protected function initPk(){
        return 'id';
    }

    /**
     * [initInfoOptions 初始化标题]
     * @return [array] [description]
     */
    protected function initInfoOptions() {
        return array (
            'title' => '用户管理',
            'subtitle' => '管理所有用户信息' 
        );
    }

    protected function initSqlType(){
        return 'getDtData';
    }
    /**
     * [initSqlOptions 初始化sql语句]cust
     * @return [type] [description]
     */
    protected function initSqlOptions(){
        return array(
            'alias' =>  'u',
            'associate' => array(
                array(
                    'alias' => 'a',
                    'table' =>  'article',//关联表名
                    'foreignKey'    =>  'user_id',//当前表连接的关联表的外键 a.user_id
                    'key'   =>   'id',//当前表的主键 u.id
                    'limit'=>'a.status = 1',
                    'searchType'=>'eq',
                    'type'  => 'inner'
                ),
                array(
                    'alias' => 'fr',
                    'table' =>  'frame_role',
                    'foreignKey'    =>  'id',//fr.id
                    'key'   =>   'role_id',//u.role_id
                    'searchType'=>'in'
                    // 'type'  =>  false
                )
            ),
            /*传参形式支持ThinkPHP5的传参形式*/
            'where' =>  array(
                'u.status'   =>   array('eq',1),
                // 'a.user_id'=>array('lt',3)
            ),
            // 'whereOr'=>array(),
            'field' =>  array(
                'a.title AS article_title',
                'fr.name AS frame_role_name',
                'u.username','u.password','u.gender AS user_gender,u.create_time,u.email'
            )
        );
    }

    // protected function getCustom(){
       
    //     return $json;
    // }

    protected function initPageOptions(){
        return array (
            'checkbox'  => true,
            // 'deleteType'=> 'status|9',
            /*
            'actions'   => array (
                'Pass' => array(
                    'title' => '通过',
                    'max' => 1
                )
            ),
            'buttons'   => array(
                'add'       =>  array(
                    'title'     =>  '报名',
                    'icon'      =>  'fa-plus'
                ),
            ),
            'initJS' => array(
                'OrganizeTrain',
            ),
            'formSize'=>'full',*/
            'tablesWrite'  => true
        );
    }


    protected function initFieldsOptions() {
        return array(
            'username' => array (
                'table' => 'user',
                'title' => '名字',
                'list' => array (
                    'search' => array (
                        'type' => 'like',
                        
                    )
                ),
                'form'=>array(
                    'type' => 'text',
                    'validate'=>[
                        'rule'=>'require|max:25',
                        'msg'=>[
                            'require' => '用户名必须',
                            'max'   => '名字不能超过25个字符'
                        ]
                    ]
                )
            ),

            'user_gender' => array (
                'table' => 'user',
                'title' => '性别',
                'list' => array (
                    'hidden' => true,
                    'search' => array(
                        'type' => 'select',
                    )
                ),

                'form' => array (
                    'title' => '课程名称',
                    'edit' => false,
                    'type' => 'select',
                    'select' => array (
                            'multiple' => true
                    ),
                    'validate' => ['require','between'=>'1,20']
                )
            ),
            'email'=> array(
                'table' => 'user',
                'title' => '性别',
                'list' => array (
                    'hidden' => true,
                    'search' => array(
                        'type' => 'select',
                        'validate'=>'email'
                    )
                ),

                'form' => array (
                    'title' => '课程名称',
                    'edit' => false,
                    'type' => 'select',
                    'select' => array (
                        'multiple' => true
                    ),
                    // 'callback' => array('callback1','arg1','arg2'),
                    // 'fill'  =>  array(
                    //     'edit'  =>  array('value','arg2')
                    // ),
                    'validate' => 'email'
                )
            ),
            'article_title' => array (
                'table' => 'article',
                'title' => '标题',
                'list' => array (
                    // 'callback' =>  array('callback2','arg1','arg2'),
                    'search' => [
                        'type' => 'select',
                        'callback'=>['toUpper']
                        
                    ]
                ),
                'form'=>array(
                    'title' => '文章标题',
                    'validate'  =>  ['require']
                )
            ),
            'create_time'   => array(
                'table' => 'article',
                'title' => '创建时间',
                'list'  =>  array(
                    'search' => array(
                        'type' =>  'daterange',
                    )
                ),
                'form'  =>  array(
                    'fill'  =>   array(
                        'add'   =>  array('value',time())
                    )
                )
            ),

            'avatar_id'    =>  array(
                'table' => 'user',
                'title' => '头像',
                'form'  =>  array(
                    'type'  =>  'file',
                    'file'  =>  array(
                        'ext'   =>  'jpg,png',
                        'size'=>512000,
                        'mimeType'=>'image/jpeg,image/png'
                    )
                    
                 )
            )
        );
    }

    public function detail($pk){}

    /**
     * [getGenderAttr 支持ThinkPHP5的获取器,支持字段定义别名的写法，但要转成驼峰法]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function getUserGenderAttr($value,$data){
        // dump($value);
        switch($value){
            case 1: $gender = '男';break;
            case 2: $gender = '女';break;
            default: $gender = '未定义';break;
        }
        return $gender;
    }

    protected function callback_setUserName($value){
        return strtoupper($value);
    }   

    /*protected function getCreateTimeAttr($value){
        return strtotime($value);
    }*/

    protected function callback_toUpper($value){
        // var_dump($value);
        return 'title like '.'\'%'.strtolower($value).'%\'';
    }

}
