<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
use think\Db;
// 'blog/:id'   => ['Blog/update',['method' => 'post','ext'=>'html|shtml']],

// 首页重定向
/*Route::any('/',function (){
    return redirect('wiki');
});*/

// http://localhost/framework/gitTest/hy-admin2.0/public/index.php/user/user/ajax?q=list


$routeRules = Db::name("frame_rule")
                ->where(['status'=>1,'pid'=>['neq',0]])
                ->field("source,action,distance,method,domain,ext,https,cache,ajax")
                ->select();
// dump($routeRules);
$rules = $params = [];


foreach ($routeRules as $k => $v) {
   foreach ($v as $k1 => $v1) {
        
            if(in_array($k1,['method','source','distance','action'])||is_null($v1)) continue;
            
            if(in_array($k1,['https','cache','ajax'])){
                if($v1 == 0) continue;
                else{
                     $params[$k1] = true;
                     continue;
                }  
            }
        
            $params[$k1] = $v1;    
        
   }
   
   $source  = (is_null($v['action'])||empty($v['action']))?$v['source']:$v['source'].'/'.$v['action'];

   switch ($v['method']) {
        case 1:
           $rules[0][$source] = empty($params)?$v['distance']:[$v['distance'],$params];
           break;
        case 2:
           $rules[1][$source] = empty($params)?$v['distance']:[$v['distance'],$params];
           break;
        case 3:
           $rules[2][$source] = empty($params)?$v['distance']:[$v['distance'],$params];
           break;
        case 4:
           $rules[3][$source] = empty($params)?$v['distance']:[$v['distance'],$params];
           break;

   }
}

// print_r($rules);

Route::get($rules[0]);
Route::post($rules[1]);
Route::put($rules[2]);
Route::delete($rules[3]);
unset($params);
unset($rules);
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
