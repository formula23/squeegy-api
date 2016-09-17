<?php

namespace App\Console\Commands;

use App\Partner;
use App\PartnerDay;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AdvancePartnerDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squeegy:advance_partner_dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance the partner day to the next available date';

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
        $partner_days = PartnerDay::whereDate('open', '<=', Carbon::now()->toDateString())->get();

        foreach($partner_days as $day) {
            if( ($day->cutoff && $day->cutoff->isPast()) || $day->close->isPast()) {

                foreach(['open','close','cutoff'] as $col) {
                    if(!$day->{$col}) continue;

                    switch ($day->frequency) {
                        case "weekly":
                            $day->{$col} = $day->{$col}->addWeek(1);
                            break;
                        case "bi-weekly":
                            $day->{$col} = $day->{$col}->addWeek(2);
                            break;
                        case "monthly":
                            $day->{$col} = $day->{$col}->addWeek(4);
                            break;
                    }
                }
                $day->save();
            }
        }
    }
}
