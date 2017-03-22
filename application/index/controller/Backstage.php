<?php
namespace app\index\controller;

use app\index\model\Type;
use think\Controller;
use think\Cookie;
use think\Db;
use think\Session;
use think\Route;
use think\Image;
use think\Request;
use app\index\model\User as UserModel;
use app\index\model\Competition as CompetitionModel;
use app\index\model\Type as TypeModel;
use app\index\model\Title as TitleModel;
use app\index\model\Sign as SignModel;
use app\index\model\Member as MemberModel;

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
                Session::set('user_name',$user->user_name);
                Session::pause();
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

    public function addcompetition()
    {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        $title = input('post.title');
        $body = input('post.body');
        $warning = input('post.warning');
        $number = input('number');
        $number_type = input('post.number_type');
        $number_title = input('post.number_title');
        $start_time = date("Y-m-d H:i:s",strtotime(input('post.start_time')));
        $end_time = date("Y-m-d H:i:s",strtotime(input('post.end_time')));
        $commit_time = date("Y-m-d H:i:s",strtotime(input('post.commit_time')));
        $correct_time = date("Y-m-d H:i:s",strtotime(input('post.correct_time')));
        if($title == null) {
            return $this->suces('标题不能为空');
        }
        if($body == null) {
            return $this->suces('描述不能为空');
        }
        if($warning == null) {
            return $this->suces('注意事项不能为空');
        }
        if($number == null) {
            return $this->suces('每组最多报名人数不能为空');
        }
        if(!is_numeric($number))
        {
            return $this->suces('每组最多报名人数必须为数字');
        }
        if($number < 1) {
            return $this->suces('每组最多报名人数应为大于等于1的整数');
        }
        if($number_type == null) {
            return $this->suces('大类数目不能为空');
        }
        if(!is_numeric($number_type))
        {
            return $this->suces('大类数目必须为数字');
        }
        if($number_title == null) {
            return $this->suces('题目总数不能为空');
        }
        if(!is_numeric($number_title))
        {
            return $this->suces('题目总数必须为数字');
        }
        if($start_time == null) {
            return $this->suces('报名开始时间不能为空');
        }
        if($end_time == null) {
            return $this->suces('报名截止时间不能为空');
        }
        if($commit_time == null) {
            return $this->suces('提交截止时间不能为空');
        }
        if($correct_time == null) {
            return $this->suces('批改截止时间不能为空');
        }
        if($correct_time > $commit_time && $commit_time > $end_time && $end_time > $start_time) {
        } else {
            return $this->suces('时间顺序错误');
        }
        $competition = new CompetitionModel();
        $competition->title = $title;
        $competition->body = $body;
        $competition->warning = $warning;
        $competition->number = $number;
        $competition->number_type = $number_type;
        $competition->number_title = $number_title;
        $competition->start_time = $start_time;
        $competition->end_time = $end_time;
        $competition->commit_time = $commit_time;
        $competition->correct_time = $correct_time;
        $competition->save();
        return $this->suces('添加成功');
    }

    public function settype($id)
    {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        $competition = CompetitionModel::get($id);
        $sum = $competition->number_type;
        for($i = 0; $i < $sum; $i++) {
            $mid = $i + 1;
            $name = 'type'.$mid;
            $input = input('post.'.$name);
            if($input == null) {
                return $this->suces('大类不允许为空');
            }
        }
        for($i = 0; $i < $sum; $i++) {
            $mid = $i + 1;
            $name = 'type'.$mid;
            $input = input('post.'.$name);
            $type = new TypeModel();
            $type->competition_id = $competition->id;
            $type->title = $input;
            $type->save();
        }
        $competition->type_status = 1;
        $competition->save();
        return $this->suces('设置成功');
    }

    public function settitle($id) {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        $type = input('post.type');
        $title = input('post.title');
        $competition = CompetitionModel::get($id);
        if($type == null) {
            return $this->suces('所属大类为必填');
        }
        if($title == null) {
            return $this->suces('题目为必填');
        }
        $new_title = new TitleModel();
        $new_title->competition_id = $id;
        $new_title->type_id = $type;
        $new_title->title = $title;
        $new_title->save();
        $competition->title_i = $competition->title_i + 1;
        if($competition->title_i == $competition->number_title) {
            $competition->title_status = 1;
            $competition->save();
            return $this->suces('添加完成,返回主页','backstage/home');
        } else {
            $competition->save();
        }
        return $this->suces('添加成功,继续添加下一题');
    }

    public function home()
    {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        $list = CompetitionModel::where('id','>',0)->order('id','desc')->paginate(10);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch('backstage/home');
    }

    public function title($id) {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        $competition = CompetitionModel::get($id);
        $this->assign('list',$competition);
        $this->assign('number_title',$competition->number_title);
        $this->assign('type',$competition->types);
        $this->assign('i',$competition->title_i);
        $this->assign('number_type',$competition->number_type);
        return $this->fetch('backstage/title');
    }

    public function setonline($id) {
        Session::start();
        $user = $this->judge();
        if($user->admin == 0) {
            return $this->redirect('index/backstage/index');
        }
        $competition = CompetitionModel::get($id);
        $online = CompetitionModel::where('status',1)->find();
        if($online != null) {
            $online->status = 0;
            $online->save();
        }
        $competition->status = 1;
        $competition->save();
        return $this->suces('上线成功');
    }

    public function logout() {
        Session::start();
        Session::destroy();
        return $this->redirect('index/backstage/index');
    }
}