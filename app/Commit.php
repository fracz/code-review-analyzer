<?php namespace App;

class Commit extends Model {

    protected $table = 'commits';

    protected $fillable = ['commit_id', 'project', 'branch', 'change_id', 'subject', 'status', 'created',
        'updated', 'submittable', 'insertions', 'deletions', '_number'];

    public function owner()
    {
        return $this->hasOne('Person', 'owner_id');
    }
    
    public function comments() {
        return Comment::where('commit_id', $this->id);
    }
}
