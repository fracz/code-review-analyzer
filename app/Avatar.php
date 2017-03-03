<?php namespace App;

class Avatar extends Model {

    protected $table = 'avatars';

    protected $fillable = ['url', 'height'];
    
    public function person()
    {
        return $this->belongsTo('Person', 'person_id');
    }
}