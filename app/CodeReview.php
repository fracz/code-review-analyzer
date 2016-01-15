<?php namespace App;

class CodeReview extends Model {

    protected $table = 'codereviews';
    
    public function reviewer() {
        return $this->belongsTo('App\Person', 'reviewer_id');
    }
}
