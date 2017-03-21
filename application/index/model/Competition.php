<?php
namespace app\index\model;

use think\Model;

class Competition extends Model
{
    public function types()
    {
        return $this->hasMany('Type');
    }
    public function titles()
    {
        return $this->hasMany('Title');
    }
    public function signs()
    {
        return $this->hasMany('Sign');
    }
}