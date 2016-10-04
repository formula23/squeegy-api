<?php

namespace App\Console\Commands;

use App\Discount;
use App\DiscountCode;
use Illuminate\Console\Command;

class ManhattanTowerCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squeegy:tower-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $discount = Discount::create([
            'name'=>'Manhattan Tower Free Express Launch event',
            'discount_type'=>'pct',
            'amount'=>'100',
            'is_active'=>'1',
        ]);

        $this->info('Discount Added: '.$discount->name);

        $discount->services()->attach(1);

        for($i=1;$i<=50;$i++) {
            $code = DiscountCode::generateReferralCode('EX');
            $discount->discount_code()->create([
                'code'=>$code,
                'is_active'=>'1',
            ]);
            $this->info($i.' added: '.$code);
        }

        $this->info("\n\n");

        $discount = Discount::create([
            'name'=>'Manhattan Tower 50% Off Classic Launch event',
            'discount_type'=>'pct',
            'amount'=>'50',
            'is_active'=>'1',
        ]);

        $this->info('Discount Added: '.$discount->name);

        $discount->services()->attach(2);

        for($i=1;$i<=50;$i++) {
            $code = DiscountCode::generateReferralCode('CL');
            $discount->discount_code()->create([
                'code'=>$code,
                'is_active'=>'1',
            ]);
            $this->info($i.' added: '.$code);
        }

    }
}
