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
use app\index\model\Competition as CompetitionModel;
use app\index\model\Type as TypeModel;
use app\index\model\Title as TitleModel;
use app\index\model\Sign as SignModel;
use app\index\model\Member as MemberModel;

class Correct extends Controller
{
    private function judge()
    {
        $id = Session::get('user_id');
        if($id == null) {
            return $this->redirect('index/correct/index');
        }
        return UserModel::where('id',Session::get('user_id'))->find();
    }

    public function index()
    {
        Session::start();
        if(Session::get('user_id') != null) {
            $user = UserModel::where('id',Session::get('user_id'))->find();
            if($user->teacher == 1) {
                return $this->redirect('index/correct/home');
            }
        }
        return $this->fetch('correct/index');
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
        if($user->teacher == 0) {
            return $this->suces('对不起，你没有权限');
        }
        Session::set('user_id', $user->id);
        Session::set('user_name', $user->user_name);
        return $this->redirect('index/correct/home');
    }

    public function home()
    {
        Session::start();
        $user = $this->judge();
        if($user->teacher == 0) {
            return $this->redirect('index/correct/index');
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
        if($current_time < $commit_time) {
            return $this->suces('作品上传还未截止','logout');
        } else if($current_time > $correct_time) {
            return $this->suces('作品审阅已经完成','logout');
        }
        $list = SignModel::where('file','>','')->where('competition_id',$competition->id)->where('point',null)->paginate(10);
        $size = SignModel::where('file','>','')->where('competition_id',$competition->id)->where('point',null)->count();
        $this->assign('size',$size);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch('correct/home');
    }

    public function setpoint($id) {
        Session::start();
        $user = $this->judge();
        if($user->teacher == 0) {
            return $this->redirect('index/correct/index');
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
        if($current_time < $commit_time) {
            return $this->suces('作品上传还未截止','logout');
        } else if($current_time > $correct_time) {
            return $this->suces('作品审阅已经完成','logout');
        }
        $sign = SignModel::get($id);
        $point = input('post.point');
        $pingyu = input('post.pingyu');
        if($point == null) {
            return $this->suces('评分不能为空');
        }
        if(!is_numeric($point)) {
            return $this->suces('评分必须为数字');
        }
        $sign->point = $point;
        $sign->pingyu = $pingyu;
        $sign->save();
        return $this->suces('评分成功');
    }

    public function logout() {
        Session::start();
        Session::destroy();
        return $this->redirect('index/correct/index');
    }

    public function signexvel() {
        Session::start();
        $user = $this->judge();
        if($user->teacher == 0) {
            return $this->redirect('index/correct/index');
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
        if($current_time < $commit_time) {
            return $this->suces('作品上传还未截止','logout');
        } else if($current_time > $correct_time) {
            return $this->suces('作品审阅已经完成','logout');
        }
        $array = SignModel::where('competition_id',$competition->id)->paginate();
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
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '队长学号');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '队长联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '队长邮箱');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '队员信息');

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
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$sum,  $item->code);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$sum,  $item->phone);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$sum,  $item->email);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $members = $item->members;
            $String = "";
            for($j=0,$cnt_members = sizeof($members);$j < $cnt_members; $j++) {
                $String += $members[$j]->info."\n";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$sum,  $String);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(40);

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$competition->title.'报名汇总.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    public function createxvel() {
        Session::start();
        $user = $this->judge();
        if($user->teacher == 0) {
            return $this->redirect('index/correct/index');
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
        if($current_time < $commit_time) {
            return $this->suces('作品上传还未截止','logout');
        } else if($current_time > $correct_time) {
            return $this->suces('作品审阅已经完成','logout');
        }
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