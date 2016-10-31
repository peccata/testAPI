<?php

class Comment extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;
    protected $table = 'comment';

    public function user() {
        return $this->belongsTo('User', 'from_user');
    }

    public function post() {
        return $this->belongsTo('Post');
    }
}
