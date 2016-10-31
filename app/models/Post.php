<?php

class Post extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;
    protected $table = 'post';

    public function category() {
        return $this->belongsTo('Category');
    }

    public function user() {
        return $this->belongsTo('User');
    }
}
