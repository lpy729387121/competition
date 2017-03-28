<?php
namespace app\index\model;

use think\Model;

class Sign extends Model
{
    public function members()
    {
        return $this->hasMany('Member');
    }
    public function user()
    {
        return $this->belongsTo('User');
    }
    public function competition()
    {
        return $this->belongsTo('Competition');
    }
    public function type()
    {
        return $this->belongsTo('Type');
    }
}