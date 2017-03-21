<?php
namespace app\index\model;

use think\Model;

class Title extends Model
{
    public function signs()
    {
        return $this->hasMany('Sign');
    }
    public function type()
    {
        return $this->belongsTo('Type');
    }
}