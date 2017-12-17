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

return [
    // 生成应用公共文件
    '__file__' => ['common.php', 'config.php', 'database.php'],

    // 定义demo模块的自动生成 （按照实际定义的文件名生成）
    'common'     => [
        '__file__'   => ['common.php'],
        '__dir__'    => ['behavior', 'controller', 'model', 'view'],
        'controller' => ['HyAll','HyBase'],
        'model'      => [
                            'HyAll',
                            'HyBase',
                            'HyCrypt',
                            'HyAjaxHandler',
                            'HyFile',
                            'HyAuth',
                            'HyAccount'
                        ],
        'view'       => ['HyBase/base'],
    ],
    'admin'      => [
        '__file__'   => ['common.php'],
        '__dir__'    => ['behavior', 'controller', 'model', 'view'],
        'controller' => ['HyStart','Index'],
        'model'      => [],
        'view'       => ['index/index','public/base'],
    ]
    // 其他更多的模块定义
];
