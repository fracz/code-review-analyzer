<?php namespace App;

class CodeReview extends Model {

    protected $table = 'codereviews';
    
    protected $fillable = ['review_value', '_revision_number'];
    
    public function reviewer() {
        return $this->belongsTo('App\Person', 'reviewer_id');
    }
}
