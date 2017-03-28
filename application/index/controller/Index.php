<?php
namespace app\index\controller;

use app\index\model\Activity;
use app\index\model\User;
use think\File;
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
use app\index\model\Competition as CompetitionModel;
use app\index\model\Type as TypeModel;
use app\index\model\Title as TitleModel;
use app\index\model\Sign as SignModel;
use app\index\model\Member as MemberModel;

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
            Session::pause();
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
        Session::pause();
        return $this->redirect('index/index/home');
    }

    public function register()
    {
        Session::start();
        if(Session::get('user_id') != null) {
            Session::pause();
            return $this->redirect('index/index/home');
        }
        $user_name = input('post.user_name');
        $password1 = input('post.password1');
        $password2 = input('post.password2');
        if($user_name == null) {
            return $this->suces('用户名不能为空');
        }
        if(strlen($user_name) < 6) {
            return $this->suces('用户名最少6位');
        }
        if(strlen($user_name) > 12) {
            return $this->suces('用户名最多12位');
        }
        if(UserModel::where('user_name',$user_name)->find() != null) {
            return $this->suces('用户名已被占用');
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
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            }
        } else {
            if($current_time < $correct_time) {
                Session::pause();
                return $this->redirect('index/index/commit');
            } else {
                Session::pause();
                return $this->redirect('index/index/competitionresult');
            }
        }
        //$this->assign('competition',$competition);
        Session::pause();
        return $this->redirect('index/index/sign');
    }

    public function sign() {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            }
        } else {
            if($current_time < $correct_time) {
                Session::pause();
                return $this->redirect('index/index/commit');
            } else {
                Session::pause();
                return $this->redirect('index/index/competitionresult');
            }
        }
        $user = UserModel::get($user_id);
        $this->assign('user',$user);
        $this->assign('competition',$competition);
        return $this->fetch('index/new_sign');
    }

    public function signforcompetition($id) {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            }
        } else {
            if($current_time < $correct_time) {
                Session::pause();
                return $this->redirect('index/index/commit');
            } else {
                Session::pause();
                return $this->redirect('index/index/competitionresult');
            }
        }
        $name = input('post.name');
        $code = input('post.code');
        $class = input('post.class');
        $grade = input('post.grade');
        $collage = input('post.collage');
        $phone = input('post.phone');
        $email = input('post.email');
        $type_id = input('post.type');
        $sign_title = input('post.sign_title');
        $sign_teacher_renke = input('post.sign_teacher_renke');
        $sign_teacher = input('post.sign_teacher');
        if($name == null) {
            return $this->suces('姓名不能为空');
        }
        if($code == null) {
            return $this->suces('学号不能为空');
        }
        if($class == null) {
            return $this->suces('班级不能为空');
        }
        if($grade == null) {
            return $this->suces('年级不能为空');
        }
        if($collage == null) {
            return $this->suces('学院不能为空');
        }
        if($phone == null) {
            return $this->suces('联系方式不能为空');
        }
        if($email == null) {
            return $this->suces('邮件不能为空');
        }
        if($type_id == null) {
            return $this->suces('请选择社会实践方向');
        }
        if($sign_title == null) {
            return $this->suces('社会实践题目不能为空');
        }
        if($sign_teacher_renke == null) {
            return $this->suces('任课老师不能为空');
        }
        if($sign_teacher == null) {
            return $this->suces('指导老师不能为空');
        }
        $sign = new SignModel();
        $sign->name = $name;
        $sign->code = $code;
        $sign->class = $class;
        $sign->competition_id = $competition->id;
        $sign->user_id = $id;
        $sign->grade = $grade;
        $sign->collage = $collage;
        $sign->phone = $phone;
        $sign->email = $email;
        $sign->type_id = $type_id;
        $sign->sign_title = $sign_title;
        $sign->sign_teacher_renke = $sign_teacher_renke;
        $sign->sign_teacher = $sign_teacher;
        $sign->save();
        for($i = 1; $i < $competition->number;$i++) {
            $mid = $i;
            $name = 'post.sign_friend_name'.$mid;
            $code = 'post.sign_friend_code'.$mid;
            $phone = 'post.sign_friend_phone'.$mid;
            $friend_name = input($name);
            $friend_code = input($code);
            $frienf_phone = input($phone);
            if($friend_code != null || $friend_name != null || $frienf_phone != null) {
                $member = new MemberModel();
                $member->sign_id = $sign->id;
                $member->number = $mid;
                $member->name = $friend_name;
                $member->code = $friend_code;
                $member->phone = $frienf_phone;
                $member->save();
            }
        }
        $competition->sign = $competition->sign + 1;
        $competition->save();
        return $this->suces('报名成功','home');
    }

    public function commit() {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            } else {
                Session::pause();
                return $this->redirect('index/index/home');
            }
        } else {
            if($current_time < $end_time) {
                $this->assign('update',1);
                $this->assign('commit',0);
                $this->assign('correct',0);
            } else if($current_time < $commit_time) {
                $this->assign('update',0);
                $this->assign('commit',1);
                $this->assign('correct',0);
            } else if($current_time < $correct_time) {
                $this->assign('update',0);
                $this->assign('commit',0);
                $this->assign('correct',1);
            } else{
                Session::pause();
                return $this->redirect('index/index/competitionresult');
            }
        }
        $user = UserModel::get($user_id);
        $this->assign('user',$user);
        $this->assign('sign',$user_sign);
        $this->assign('competition',$competition);
        return $this->fetch('index/commit');
    }

    public function competitionresult() {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            } else {
                Session::pause();
                return $this->redirect('index/index/home');
            }
        } else {
            if($current_time < $correct_time) {
                Session::pause();
                return $this->redirect('index/index/commit');
            }
        }
        $this->assign('sign',$user_sign);
        return $this->fetch('index/result');
    }

    public function upload(Request $request) {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            } else {
                Session::pause();
                return $this->redirect('index/index/home');
            }
        } else {
            if($current_time > $commit_time) {
                return $this->suces('上传作品截止日期已过，无法再上传','home');
            }
        }
        $file = $request->file('file');
        if($file == null) {
            return $this->suces('文件未选择或超过限制大小');
        }

        $style = $file->validate(['ext' => 'rar,zip'])->move(ROOT_PATH . 'public' . DS . 'uploads',$user_sign->id.$request->time());
        if($style == null) {
            return $this->suces('只支持.zip, .rar文件');
        }
        if($user_sign->file != null) {
            $past_file = ROOT_PATH . 'public' . DS . 'uploads\\'.$user_sign->file;
            unlink($past_file);
        }
        $true_file_name = $file->getInfo()['name'];
        $user_sign->file = $style->getSaveName();
        $user_sign->true_file_name = $true_file_name;
        $user_sign->save();
        return $this->suces('上传成功');
    }

    public function updatesign()
    {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            } else {
                Session::pause();
                return $this->redirect('index/index/home');
            }
        } else {
            if($current_time > $end_time) {
                return $this->suces('报名已经截止，无法再修改报名信息','home');
            }
        }
        $this->assign('sign',$user_sign);
        $this->assign('size',sizeof($user_sign->members));
        return $this->fetch('index/new_updatesign');
    }

    public function update($id) {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            Session::pause();
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无社会实践','logout');
        }
        $start_time = $competition->start_time;
        $end_time = $competition->end_time;
        $commit_time = $competition->commit_time;
        $correct_time = $competition->correct_time;
        $current_time = date('Y-m-d H:i:s');
        $user_sign = SignModel::where('competition_id',$competition->id)->where('user_id',$user_id)->find();
        if($user_sign == null) {
            if($current_time < $start_time) {
                return $this->suces('报名还未开始，请在'.$start_time.'后再登录系统','logout');
            } else if($current_time > $end_time) {
                return $this->suces('报名已经截止，您之前并没有报名，无法进入系统','logout');
            } else {
                Session::pause();
                return $this->redirect('index/index/home');
            }
        } else {
            if($current_time > $end_time) {
                return $this->suces('报名已经截止，无法再修改报名信息','home');
            }
        }
        $sign = SignModel::get($id);
        $name = input('post.name');
        $code = input('post.code');
        $class = input('post.class');
        $grade = input('post.grade');
        $collage = input('post.collage');
        $phone = input('post.phone');
        $email = input('post.email');
        $type_id = input('post.type');
        $sign_title = input('post.sign_title');
        $sign_teacher_renke = input('post.sign_teacher_renke');
        $sign_teacher = input('post.sign_teacher');
        if($name == null) {
            return $this->suces('姓名不能为空');
        }
        if($code == null) {
            return $this->suces('学号不能为空');
        }
        if($class == null) {
            return $this->suces('班级不能为空');
        }
        if($grade == null) {
            return $this->suces('年级不能为空');
        }
        if($collage == null) {
            return $this->suces('学院不能为空');
        }
        if($phone == null) {
            return $this->suces('联系方式不能为空');
        }
        if($email == null) {
            return $this->suces('邮件不能为空');
        }
        if($type_id == null) {
            return $this->suces('请选择社会实践方向');
        }
        if($sign_title == null) {
            return $this->suces('社会实践题目不能为空');
        }
        if($sign_teacher_renke == null) {
            return $this->suces('任课老师不能为空');
        }
        if($sign_teacher == null) {
            return $this->suces('指导老师不能为空');
        }
        $sign->name = $name;
        $sign->code = $code;
        $sign->class = $class;
        $sign->competition_id = $competition->id;
        $sign->grade = $grade;
        $sign->collage = $collage;
        $sign->phone = $phone;
        $sign->email = $email;
        $sign->type_id = $type_id;
        $sign->sign_title = $sign_title;
        $sign->sign_teacher_renke = $sign_teacher_renke;
        $sign->sign_teacher = $sign_teacher;
        $sign->save();
        for($i = 1; $i < $competition->number;$i++) {
            $mid = $i;
            $name = 'post.sign_friend_name'.$mid;
            $code = 'post.sign_friend_code'.$mid;
            $phone = 'post.sign_friend_phone'.$mid;
            $friend_name = input($name);
            $friend_code = input($code);
            $frienf_phone = input($phone);
            if($friend_code != null || $friend_name != null || $frienf_phone != null) {
                $member = MemberModel::where('sign_id',$sign->id)->where('number',$mid)->find();
                if($member == null) {
                    $member = new MemberModel();
                    $member->sign_id = $sign->id;
                    $member->number = $mid;
                    $member->name = $friend_name;
                    $member->code = $friend_code;
                    $member->phone = $frienf_phone;
                    $member->save();
                }
                $member->number = $mid;
                $member->name = $friend_name;
                $member->code = $friend_code;
                $member->phone = $frienf_phone;
                $member->save();
            } else {
                $member = MemberModel::where('sign_id',$sign->id)->where('number',$mid)->find();
                if($member != null) {
                    $member->delete();
                }
            }
        }
        return $this->suces('修改成功','home');
    }

    public function logout() {
        Session::start();
        Session::destroy();
        return $this->redirect('index/index/index');
    }
}
