<?php namespace App;

class Commit extends Model {

    protected $table = 'commits';

    protected $fillable = ['commit_id', 'project', 'branch', 'change_id', 'subject', 'status', 'created',
        'updated', 'submittable', 'insertions', 'deletions', '_number'];

    public function owner()
    {
        return $this->belongsTo('App\Person', 'owner_id');
    }

    public function revisions() {
        return $this->hasMany('App\Revision', 'commit_id');
    }
    
    public function codeReviews(){
        return $this->hasMany('App\CodeReview', 'commit_id');
    }
    
    public function verified(){
        return $this->hasMany('App\Verified', 'commit_id');
    }
}
