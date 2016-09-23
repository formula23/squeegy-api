<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Instruction extends Model
{
    protected $fillable = ['key','label','hint','type','input_type','prepopulate','required','min_length','max_length','validation'];
    
    public function partners()
    {
        return $this->belongsToMany('App\Partner');
    }
    
}
