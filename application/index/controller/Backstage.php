<?php
namespace app\index\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Session;
use think\Route;
use think\Image;
use think\Request;
use app\index\model\User as UserModel;

class Backstage extends Controller
{
    private function judge()
    {
        $id = Session::get('user_id');
        if($id == null) {
            return $this->redirect('index/backstage/index');
        }
        return UserModel::where('id',Session::get('user_id'))->find();
    }

    public function index()
    {
        Session::start();
        if(Session::get('user_id') != null) {
            $user = UserModel::where('id',Session::get('user_id'))->find();
            if($user->admin == 1) {
                return $this->redirect('index/backstage/home');
            }
        }
        return $this->fetch('backstage/index');
    }

    public function login()
    {
        Session::start();
        $user_name = input('post.user_name');
        $password = md5(input('post.password'));
        $user = UserModel::where('user_name', $user_name)->where('password', $password)->find();
        if($user == null) {
            return $this->suces('用户名或密码错误');
        }
        if($user->admin == 0) {
            return $this->suces('对不起，你没有权限');
        }
        Session::set('user_id', $user->id);
        Session::set('user_name', $user->user_name);
        return $this->redirect('index/backstage/home');
    }

    public function home()
    {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        return "backstage/home";
    }

    public function logout() {
        Session::start();
        Session::destroy();
        return $this->redirect('index/backstage/index');
    }
}