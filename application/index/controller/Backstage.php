<?php
namespace app\index\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Session;
use think\Route;
use think\Image;
use think\Request;

/**
 * 后台操作类
 * Class Backstage
 * @package app\index\controller
 */
class Backstage extends Controller
{

    /**
     * 首页
     *
     * 跳转登录界面，渲染home界面
     */
    public function index()
    {
        return "backstage";
    }
}