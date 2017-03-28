<?php
namespace app\index\controller;

use think\cache\driver\File;
use think\Controller;
use think\Cookie;
use think\Db;
use think\helper\Str;
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

    public function home() {
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
        if($current_time > $commit_time) {
            $this->assign('correct',1);
        } else {
            $this->assign('correct',0);
        }
        $list = SignModel::where('competition_id',$competition->id)->paginate();
        $this->assign('competition',$competition);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch('correct/home');
    }

    public function title_pingyu($id) {
        Session::start();
        $user = $this->judge();
        if($user->teacher == 0) {
            return $this->redirect('index/correct/index');
        }
        $competition = CompetitionModel::where('status',1)->find();
        if($competition == null) {
            return $this->suces('当前暂无竞赛','logout');
        }
        $pingyu = input('post.pingyu');
        $sign = SignModel::get($id);
        $sign->title_pingyu = $pingyu;
        $sign->save();
        return $this->suces('评语成功');
    }

    public function correct()
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
        if($current_time > $commit_time) {
        } else {
            return $this->suces('未到评分时间');
        }
        $list = SignModel::where('file','>','')->where('competition_id',$competition->id)->paginate(50);
        $size = SignModel::where('file','>','')->where('competition_id',$competition->id)->where('point',null)->count();
        $this->assign('competition',$competition);
        $this->assign('size',$size);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch('correct/correct');
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
            return $this->suces('未到评分时间');
        } else if ($current_time > $correct_time){
            return $this->suces('评分时间已过');
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
        $array = SignModel::where('competition_id',$competition->id)->paginate();
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('报名信息汇总');

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('报名信息');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '社会实践方向');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '社会实践题目');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '任课老师');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '指导老师');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '队长姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '队长学号');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '队长班级');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '队长年级');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '队长学院');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '队长联系方式');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '队长邮箱');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', '队员信息');

        for($i=0,$cnt = sizeof($array);$i<$cnt;$i++) {
            $item = $array[$i];
            $sum = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->type->title);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->sign_title);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,  $item->sign_teacher_renke);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum,  $item->sign_teacher);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$sum,  $item->name);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$sum,  $item->code);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$sum,  $item->class);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$sum,  $item->grade);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$sum,  $item->collage);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$sum,  $item->phone);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$sum,  $item->email);
            $objPHPExcel->getActiveSheet()->getStyle('K'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $members = $item->members;
            $String = '';
            for($j=0,$cnt_members = sizeof($members);$j < $cnt_members; $j++) {
                if($j != $cnt_members-1) {
                    $String = $String."姓名：".$members[$j]->name." 学号：".$members[$j]->code." 联系方式：".$members[$j]->phone."\n";
                } else {
                    $String = $String."姓名：".$members[$j]->name." 学号：".$members[$j]->code." 联系方式：".$members[$j]->phone;
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$sum,  $String);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$sum)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('L'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(60);

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$competition->title.'社会实践报名汇总.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    public function getfile($id)
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
        $sign = SignModel::get($id);
        if($sign == null) {
            return $this->suces('作品不存在');
        }
        $file = ROOT_PATH . 'public' . DS . 'uploads\\'.$sign->file;
        $name = $sign->true_file_name;
        $fileName = $name ? $name : pathinfo($file,PATHINFO_FILENAME);
        $filePath = realpath($file);

        $fp = fopen($filePath,'rb');

        if(!$filePath || !$fp){
            header('HTTP/1.1 404 Not Found');
            echo "Error: 404 Not Found.(server file path error)<!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding -->";
            exit;
        }

        $encoded_filename = urlencode($fileName);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);

        header('HTTP/1.1 200 OK');
        header( "Pragma: public" );
        header( "Expires: 0" );
        header("Content-type: application/octet-stream");
        header("Content-Length: ".filesize($filePath));
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($filePath));

        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
        }
        // ob_end_clean();
        // 输出文件内容
        fpassthru($fp);
        exit;
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
        $array = SignModel::where('competition_id',$competition->id)->order('point','desc')->paginate();
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('评分汇总');

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('评分信息');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '社会实践方向');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '社会实践题目');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '任课老师');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '指导老师');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '队长姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '队长学号');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '队长班级');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', '队长学院');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', '队长年级');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', '队员信息');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', '评分');

        for($i=0,$cnt = sizeof($array);$i<$cnt;$i++) {
            $item = $array[$i];
            $sum = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->type->title);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->sign_title);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,  $item->sign_teacher_renke);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum,  $item->sign_teacher);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$sum,  $item->name);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$sum,  $item->code);
            $objPHPExcel->getActiveSheet()->getStyle('F'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$sum,  $item->class);
            $objPHPExcel->getActiveSheet()->getStyle('G'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$sum,  $item->collage);
            $objPHPExcel->getActiveSheet()->getStyle('H'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$sum,  $item->grade);
            $objPHPExcel->getActiveSheet()->getStyle('I'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $members = $item->members;
            $String = "";
            for($j=0,$cnt_members = sizeof($members);$j < $cnt_members; $j++) {
                if($j != $cnt_members-1) {
                    $String = $String."姓名：".$members[$j]->name." 学号：".$members[$j]->code." 联系方式：".$members[$j]->phone."\n";
                } else {
                    $String = $String."姓名：".$members[$j]->name." 学号：".$members[$j]->code." 联系方式：".$members[$j]->phone;
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$sum,  $String);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$sum)->getAlignment()->setWrapText(true);
            $objPHPExcel->getActiveSheet()->getStyle('J'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if($item->file == null) {
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$sum,  '未提交作品');
            } else if($item->point == null) {
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$sum,  '未评分');
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('K'.$sum,  $item->point);
            }
            $objPHPExcel->getActiveSheet()->getStyle('K'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$competition->title.'社会实践评分汇总.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
}