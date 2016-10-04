<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model {

    protected $fillable = ['code','frequency_rate','is_active'];

    public function discount()
    {
        return $this->belongsTo('App\Discount', 'discount_id');
    }

    public static function generateReferralCode($prefix='', $postfix='')
    {
        while(true) {
            $code = $prefix.strtoupper(substr( md5(rand()), 0, 3)).$postfix;
            $code_rec = self::where('code', $code)->get();
            if( ! $code_rec->count()) break;
        }
        return $code;
    }

}
