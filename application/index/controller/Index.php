<?php
namespace app\index\controller;

use app\index\model\Activity;
use app\index\model\User;
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
use app\index\model\User as UserModel;

/**
 * Class Index 前端类
 * @package app\index\controller
 */
class Index extends Controller
{
    public function index()
    {
        Session::start();
        if(Session::get('user_id') != null) {
            return $this->redirect('index/index/home');
        }
        return $this->fetch('index/index');
    }

    public function login() {
        Session::start();
        $user_name = input('post.user_name');
        $password = md5(input('post.password'));
        $user = UserModel::where('user_name', $user_name)->where('password', $password)->find();
        if($user == null) {
            return $this->suces('用户名或密码错误');
        }
        Session::set('user_id', $user->id);
        Session::set('user_name', $user->user_name);
        return $this->redirect('index/index/home');
    }

    public function register()
    {
        Session::start();
        $user_name = input('post.user_name');
        $password1 = input('post.password1');
        $password2 = input('post.password2');
        if($user_name == null) {
            return $this->suces('用户名不能为空');
        }
        if(UserModel::where('user_name',$user_name)->find() != null) {
            return $this->suces('用户名已被占用');
        }
        if(strlen($user_name) < 6) {
            return $this->suces('用户名最少六位');
        }
        if($password1 == null) {
            return $this->suces('密码不能为空');
        }
        if($password1 != $password2) {
            return $this->suces('两次密码不一致');
        }
        $user = new UserModel();
        $user->user_name = $user_name;
        $user->password = md5($password1);
        $user->save();
        Session::set('user_id', $user->id);
        Session::set('user_name', $user->user_name);
        return $this->suces('注册成功!');
    }

    public function home() {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            return $this->redirect('index/index/index');
        }
        return "home";
    }

    public function logout() {
        Session::start();
        Session::destroy();
        return $this->redirect('index/index/index');
    }
}
