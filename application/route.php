<?php

use think\Route;

Route::pattern([ 'id'=>'\d+',
]);

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'index'=>'index/index/index',
    'register'=>'index/index/register',
    'home'=>'index/index/home',
    'logout'=>'index/index/logout',
    'login'=>'index/index/login',
    'backstage'=>'index/backstage/index',
    'backstage/login'=>'index/backstage/login',
    'backstage/logout'=>'index/backstage/logout',
    'backstage/home'=>'index/backstage/home',
    'correct'=>'index/correct/index',
    'correct/login'=>'index/correct/login',
    'correct/logout'=>'index/correct/logout',
    'correct/home'=>'index/correct/home',
];