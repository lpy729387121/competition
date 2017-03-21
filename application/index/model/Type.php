<?php
namespace app\index\model;

use think\Model;

class Type extends Model
{
    public function titles()
    {
        return $this->hasMany('Title');
    }
}