<?php

use App\Addon;
use App\Service;
use Illuminate\Database\Seeder;

// composer require laracasts/testdummy
use Laracasts\TestDummy\Factory as TestDummy;

class AddonsTableSeeder extends Seeder
{
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        Addon::truncate();

        Addon::create([
            'name'=>'Carbauba Hand Wax',
            'description'=>'Carbauba Was makes your car shine and protects your paint.',
            'price'=>'2500',
        ]);

        Addon::create([
            'name'=>'Leather Conditioning',
            'description'=>'Restore and protect your leather. Give your interior a new car smell.',
            'price'=>'1500',
        ]);

        Addon::create([
            'name'=>'Exterior Plastic Dressing',
            'description'=>'Protect, nourish and keep a fine touch to all exterior plastic. Prevent UV damage.',
            'price'=>'1500',
        ]);

        Addon::create([
            'name'=>'Tree Sap Removal',
            'description'=>'We will remove tree sap from your vehicle\'s body and windows',
            'price'=>'2000',
        ]);

        Addon::create([
            'name'=>'Scent - New Car',
            'description'=>'',
            'price'=>'400',
        ]);

        Addon::create([
            'name'=>'Scent - Vanilla',
            'description'=>'',
            'price'=>'400',
        ]);

        Addon::create([
            'name'=>'Scent - Cherry',
            'description'=>'',
            'price'=>'400',
        ]);

        Addon::create([
            'name'=>'Scent - Black Ice',
            'description'=>'',
            'price'=>'400',
        ]);

        Addon::create([
            'name'=>'Pet Hair Removal',
            'description'=>'Remove all pet hair from vehicle.',
            'price'=>'2000',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // disable foreign key constraints

        //Express

        $express = Service::findOrFail(1);

        $express->addons()->sync([
            1=>[
                'sequence'=>1,
                'is_active'=>1,
            ],
            2=>[
                'sequence'=>2,
                'is_active'=>1,
            ],
            3=>[
                'sequence'=>3,
                'is_active'=>1,
            ],
            4=>[
                'sequence'=>4,
                'is_active'=>1,
            ],
            5=>[
                'sequence'=>5,
                'is_active'=>1,
            ],
            6=>[
                'sequence'=>6,
                'is_active'=>1,
            ],
            7=>[
                'sequence'=>7,
                'is_active'=>1,
            ],
            8=>[
                'sequence'=>8,
                'is_active'=>1,
            ],
            9=>[
                'sequence'=>9,
                'is_active'=>1,
            ],
        ]);

        $classic = Service::findOrFail(2);

        $classic->addons()->sync([
            1=>[
                'sequence'=>1,
                'is_active'=>1,
            ],
            2=>[
                'sequence'=>2,
                'is_active'=>1,
            ],
            3=>[
                'sequence'=>3,
                'is_active'=>1,
            ],
            4=>[
                'sequence'=>4,
                'is_active'=>1,
            ],
            5=>[
                'sequence'=>5,
                'is_active'=>1,
            ],
            6=>[
                'sequence'=>6,
                'is_active'=>1,
            ],
            7=>[
                'sequence'=>7,
                'is_active'=>1,
            ],
            8=>[
                'sequence'=>8,
                'is_active'=>1,
            ],
            9=>[
                'sequence'=>9,
                'is_active'=>1,
            ],
        ]);

        $exec = Service::findOrFail(4);

        $exec->addons()->sync([
            2=>[
                'sequence'=>2,
                'is_active'=>1,
            ],
            4=>[
                'sequence'=>4,
                'is_active'=>1,
            ],
            5=>[
                'sequence'=>5,
                'is_active'=>1,
            ],
            6=>[
                'sequence'=>6,
                'is_active'=>1,
            ],
            7=>[
                'sequence'=>7,
                'is_active'=>1,
            ],
            8=>[
                'sequence'=>8,
                'is_active'=>1,
            ],
            9=>[
                'sequence'=>9,
                'is_active'=>1,
            ],
        ]);

    }
}
