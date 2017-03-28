<?php

use think\Route;

Route::pattern([ 'id'=>'\d+',
]);

Route::rule('index/signforcompetition/:id','index/Index/signforcompetition');
Route::rule('index/update/:id','index/Index/update');
Route::rule('backstage/settype/:id','index/Backstage/settype');
Route::rule('backstage/settitle/:id','index/Backstage/settitle');
Route::rule('backstage/title/:id','index/Backstage/title');
Route::rule('backstage/setonline/:id','index/Backstage/setonline');
Route::rule('correct/setpoint/:id','index/Correct/setpoint');
Route::rule('correct/title_pingyu/:id','index/Correct/title_pingyu');
Route::rule('correct/getfile/:id','index/Correct/getfile');

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
    'sign'=>'index/index/sign',
    'commit'=>'index/index/commit',
    'upload'=>'index/index/upload',
    'updatesign'=>'index/index/updatesign',
    'competitionresult'=>'index/index/competitionresult',
    'backstage'=>'index/backstage/index',
    'backstage/login'=>'index/backstage/login',
    'backstage/logout'=>'index/backstage/logout',
    'backstage/home'=>'index/backstage/home',
    'backstage/addcompetition'=>'index/backstage/addcompetition',
    'correct'=>'index/correct/index',
    'correct/login'=>'index/correct/login',
    'correct/logout'=>'index/correct/logout',
    'correct/home'=>'index/correct/home',
    'correct/correct'=>'index/correct/correct',
    'correct/createxvel'=>'index/correct/createxvel',
    'correct/signexvel'=>'index/correct/signexvel',
];