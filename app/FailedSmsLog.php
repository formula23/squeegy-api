<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FailedSmsLog extends Model
{
    protected $fillable = ['to','from','message'];
}
