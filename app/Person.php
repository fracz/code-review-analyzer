<?php namespace App;

class Person extends Model {

    protected $table = 'persons';

    protected $fillable = ['_account_id', 'name', 'email', 'username'];
    
    public function avatars()
    {
        return Avatar::where('person_id', $this->id);
    }
}