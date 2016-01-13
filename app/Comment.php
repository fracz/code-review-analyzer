<?php namespace App;

class Comment extends Model {

    protected $table = 'comments';

    protected $fillable = ['comment_id', 'line', 'start_line', 'start_character', 'end_line', 'end_character', 'updated', 'message'];
    
    public function commit()
    {
        return $this->belongsTo('Commit', 'commit_id');
    }
    
    public function author()
    {
        return $this->hasOne('Person', 'author_id');
    }
}
