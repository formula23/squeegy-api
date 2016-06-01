<?php

namespace App\Console\Commands;

use App\Partner;
use App\PartnerDay;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdatePartnerDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'squeegy:update_partner_dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the next date for partner days';

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
        $partners = Partner::where('is_active', 1)->with('days')->get();

        foreach($partners as $partner) {

            $this->info($partner->name);

            foreach($partner->days as $day) {

                if( ! $day->next_date->isToday()) continue;

                if(Carbon::parse($day->time_end)->isPast()) {

                    switch ($day->frequency) {
                        case "weekly":
                            $day->next_date = $day->next_date->addWeek(1);
                            break;
                        case "bi-weekly":
                            $day->next_date = $day->next_date->addWeek(2);
                            break;
                        case "monthly":
                            $day->next_date = $day->next_date->addWeek(4);
                            break;
                    }
                    
                    $day->save();
                }
            }
        }
    }
}
