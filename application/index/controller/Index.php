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
            return $this->suces('当前暂无竞赛','logout');
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
        $this->assign('competition',$competition);
        return $this->fetch('index/home');
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
            return $this->suces('当前暂无竞赛','logout');
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
        return $this->fetch('index/sign');
    }

    public function signforcompetition($id) {
        Session::start();
        $user_id = Session::get('user_id');
        if($user_id == null) {
            return $this->redirect('index/index/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无竞赛','logout');
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
                return $this->redirect('index/index/competitionresult');
            }
        }
        $code = input('post.code');
        $name = input('post.name');
        $collage = input('post.collage');
        $type = input('post.type');
        $grade = input('post.grade');
        $phone = input('post.phone');
        $email = input('post.email');
        $title_id = input('post.title');
        $sign_title = input('post.sign_title');
        $sign_teacher = input('post.sign_teacher');
        $sign_name = input('post.sign_name');
        if($code == null) {
            return $this->suces('学号不能为空');
        }
        if($name == null) {
            return $this->suces('姓名不能为空');
        }
        if($collage == null) {
            return $this->suces('学院不能为空');
        }
        if($type == null) {
            return $this->suces('类别不能为空');
        }
        if($phone == null) {
            return $this->suces('联系方式不能为空');
        }
        if($email == null) {
            return $this->suces('邮件不能为空');
        }
        if($title_id == null) {
            return $this->suces('请选择竞赛组别');
        }
        $sign = new SignModel();
        $sign->code = $code;
        $sign->name = $name;
        $sign->competition_id = $competition->id;
        $sign->user_id = $id;
        $sign->collage = $collage;
        $sign->type = $type;
        $sign->grade = $grade;
        $sign->phone = $phone;
        $sign->email = $email;
        $sign->title_id = $title_id;
        $sign->sign_title = $sign_title;
        $sign->sign_teacher = $sign_teacher;
        $sign->sign_name = $sign_name;
        $sign->save();
        for($i = 1; $i < $competition->number;$i++) {
            $mid = $i;
            $name = 'post.sign_friend'.$mid;
            $info = input($name);
            if($info != null) {
                $member = new MemberModel();
                $member->sign_id = $sign->id;
                $member->number = $mid;
                $member->info = $info;
                $member->save();
            }
        }
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
            return $this->suces('当前暂无竞赛','logout');
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
            return $this->suces('当前暂无竞赛','logout');
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
            return $this->suces('当前暂无竞赛','logout');
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
            if($current_time > $commit_time) {
                return $this->suces('上传作品截止日期已过，无法再上传','home');
            }
        }
        $file = $request->file('file');
        if($file == null) {
            return $this->suces('文件未选择或超过限制大小');
        }
        $style = $file->validate(['ext' => 'rar,zip'])->move(ROOT_PATH . 'public' . DS . 'uploads',$user_sign->id.$file->getInfo()['name']);
        if($style == null) {
            return $this->suces('只支持.zip, .rar文件');
        }
        if($user_sign->file != null) {
            $past_file = ROOT_PATH . 'public' . DS . 'uploads\\'.$user_sign->file;
            unlink($past_file);
        }
        $user_sign->file = $style->getSaveName();
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
            return $this->suces('当前暂无竞赛','logout');
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
            if($current_time > $end_time) {
                return $this->suces('报名已经截止，无法再修改报名信息','home');
            }
        }
        $this->assign('sign',$user_sign);
        $this->assign('size',sizeof($user_sign->members));
        return $this->fetch('index/updatesign');
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
            return $this->suces('当前暂无竞赛','logout');
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
            if($current_time > $end_time) {
                return $this->suces('报名已经截止，无法再修改报名信息','home');
            }
        }
        $sign = SignModel::get($id);
        $code = input('post.code');
        $name = input('post.name');
        $collage = input('post.collage');
        $type = input('post.type');
        $grade = input('post.grade');
        $phone = input('post.phone');
        $email = input('post.email');
        $title_id = input('post.title');
        $sign_title = input('post.sign_title');
        $sign_teacher = input('post.sign_teacher');
        $sign_name = input('post.sign_name');
        if($code == null) {
            return $this->suces('学号不能为空');
        }
        if($name == null) {
            return $this->suces('姓名不能为空');
        }
        if($collage == null) {
            return $this->suces('学院不能为空');
        }
        if($type == null) {
            return $this->suces('类别不能为空');
        }
        if($phone == null) {
            return $this->suces('联系方式不能为空');
        }
        if($email == null) {
            return $this->suces('邮件不能为空');
        }
        if($title_id == null) {
            return $this->suces('请选择竞赛组别');
        }
        $sign->code = $code;
        $sign->name = $name;
        $sign->competition_id = $competition->id;
        $sign->user_id = $id;
        $sign->collage = $collage;
        $sign->type = $type;
        $sign->grade = $grade;
        $sign->phone = $phone;
        $sign->email = $email;
        $sign->title_id = $title_id;
        $sign->sign_title = $sign_title;
        $sign->sign_teacher = $sign_teacher;
        $sign->sign_name = $sign_name;
        $sign->save();
        for($i = 1; $i < $competition->number;$i++) {
            $mid = $i;
            $name = 'post.sign_friend'.$mid;
            $info = input($name);
            if($info != null) {
                $member = MemberModel::where('sign_id',$sign->id)->where('number',$mid)->find();
                if($member == null) {
                    $member = new MemberModel();
                    $member->sign_id = $sign->id;
                    $member->number = $mid;
                    $member->info = $info;
                    $member->save();
                }
                $member->number = $mid;
                $member->info = $info;
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

    public function getexcel() {
        $competition = CompetitionModel::where('status',1)->find();
        $array = SignModel::where('competition_id',$competition->id)->where('point','>=',0)->order('point','desc')->paginate();
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('竞赛评分汇总');

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('评分信息');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '竞赛大类');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '竞赛组别');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '参赛队伍');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '参赛题目');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '指导老师');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '队长姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '队长学院');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '队长年级');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '队员信息');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '评分');

        for($i=0,$cnt = sizeof($array);$i<$cnt;$i++) {
            $item = $array[$i];
            $sum = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->title->type->title);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->title->title);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,  $item->sign_name);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum,  $item->sign_title);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$sum,  $item->sign_teacher);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$sum,  $item->name);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$sum,  $item->collage);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$sum,  $item->grade);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $members = $item->members;
            $String = "";
            for($j=0,$cnt_members = sizeof($members);$j < $cnt_members; $j++) {
                $String += $members[$j]->info."\n";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$sum,  $String);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$sum,  $item->point);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$competition->title.'评分汇总.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
}
