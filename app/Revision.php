<?php namespace App;

class Revision extends Model {

    protected $table = 'revisions';

    protected $fillable = ['revision_id', '_number', 'created', 'ref'];
    
    public function uploader()
    {
        return $this->hasOne('Person', 'uploader_id');
    }
    
    public function comments() {
        return $this->hasMany('App\Comment', 'revision_id');
    }
}
