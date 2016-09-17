<?php

namespace App\Console\Commands;

use App\PartnerDay;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdatePartnerDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squeegy:update_partner_days';

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

        $partner_days = PartnerDay::whereNull('open')->get();

        foreach($partner_days as $partner_day) {

            $partner_day->open = Carbon::parse($partner_day->next_date->format('Y-m-d')." ".$partner_day->time_start);
            $partner_day->close = Carbon::parse($partner_day->next_date->format('Y-m-d')." ".$partner_day->time_end);

            $partner_day->save();
        }
    }
}
