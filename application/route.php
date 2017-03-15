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
    'backstage'=>'index/backstage/index',
];