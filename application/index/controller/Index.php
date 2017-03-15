<?php
namespace app\index\controller;

use app\index\model\Activity;
use think\Model;
use think\Page;
use think\Paginator;
use think\Controller;
use think\Cookie;
use think\Db;
use think\paginator\Collection;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\Route;
use think\Image;
use think\Request;

/**
 * Class Index 前端类
 * @package app\index\controller
 */
class Index extends Controller
{
    /**
     * 登录页面
     *
     * @return mixed 主页面渲染
     */
    public function index()
    {
        return "success";
    }
}
