<?php

use App\Service;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class ServiceToAddonTableSeeder extends Seeder
{
    public function run()
    {
        //Express

        $express = Service::findOrFail(1);

        $express->addons()->sync([
            1=>[
                'sequence'=>1,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            2=>[
                'sequence'=>2,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            3=>[
                'sequence'=>3,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            4=>[
                'sequence'=>4,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            5=>[
                'sequence'=>5,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            6=>[
                'sequence'=>6,
                'is_active'=>1,
                'is_corp'=>1,
            ],
        ]);

        $express->addons()->attach([
                1=>[
                    'sequence'=>1,
                    'is_active'=>1,
                    'is_corp'=>0,
                ]]
        );

        $classic = Service::findOrFail(2);

        $classic->addons()->sync([
            1=>[
                'sequence'=>1,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            2=>[
                'sequence'=>2,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            3=>[
                'sequence'=>3,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            4=>[
                'sequence'=>4,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            5=>[
                'sequence'=>5,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            6=>[
                'sequence'=>6,
                'is_active'=>1,
                'is_corp'=>1,
            ],
        ]);

        $classic->addons()->attach([
                1=>[
                    'sequence'=>1,
                    'is_active'=>1,
                    'is_corp'=>0,
                ]]
        );

        $exec = Service::findOrFail(4);

        $exec->addons()->sync([
            2=>[
                'sequence'=>2,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            4=>[
                'sequence'=>4,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            5=>[
                'sequence'=>5,
                'is_active'=>1,
                'is_corp'=>1,
            ],
            6=>[
                'sequence'=>6,
                'is_active'=>1,
                'is_corp'=>1,
            ],
        ]);
    }
}
