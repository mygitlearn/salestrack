<?php
return array(
    'TMPL_PARSE_STRING'  =>  array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__JS__'     =>  __ROOT__.'/Public/Admin/js',
        '__CSS__'     => __ROOT__. '/Public/Admin/css',
        '__IMG__'    => __ROOT__.'/Public/Admin/images'
    ),
    'DEFAULT_CONTROLLER'    =>  'Advantage', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称

    /* 后台错误页面模板 */
    'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/Public/success.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/Public/exception.html',// 异常页面的模板文件
    'UPLOADED_PATH'=>'./Public/Uploads/Advantage',

);