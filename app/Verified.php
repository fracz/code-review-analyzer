<?php namespace App;

class Verified extends Model {

    protected $table = 'verified';
    
    protected $fillable = ['verified_date, verified_value', '_revision_number'];
    
    public function reviewer() {
        return $this->belongsTo('App\Person', 'verifier_id');
    }
}
