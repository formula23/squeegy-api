<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InstructionOption extends Model
{
    protected $fillable = ['instruction_id','option','value','sequence'];
    
    public function instruction()
    {
        return $this->belongsTo('App\Instruction');
    }

}
