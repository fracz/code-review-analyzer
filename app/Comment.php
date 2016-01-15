<?php namespace App;

class Comment extends Model {

    protected $table = 'comments';

    protected $fillable = ['comment_id', 'line', 'start_line', 'start_character', 
        'end_line', 'end_character', 'updated', 'message', 'in_reply_to', 'filename'];  
    
    public function author()
    {
        return $this->belongsTo('App\Person', 'author_id');
    }
}
