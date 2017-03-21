<?php
namespace app\index\model;

use think\Model;

class Member extends Model
{
    public function sign()
    {
        return $this->belongsTo('Sign');
    }
}