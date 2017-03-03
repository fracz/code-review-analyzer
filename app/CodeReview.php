<?php namespace App;

class CodeReview extends Model {

    protected $table = 'codereviews';
    
    protected $fillable = ['review_value', '_revision_number', 'review_date'];
    
    public function reviewer() {
        return $this->belongsTo('App\Person', 'reviewer_id');
    }
}
