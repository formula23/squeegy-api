<?php

use App\Service;
use App\ServiceAttrib;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints
        
        ServiceAttrib::truncate();

        $types = ['Car','Truck','SUV','Van','Minivan'];
        $sizes = ['Compact','Midsize','Large'];
        $etc_surcharge = [
            'Car' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'30',
                        'surcharge'=>'0',
                    ],
                    '2' => [
                        'etc'=>'50',
                        'surcharge'=>'0',
                    ],
                    '4' => [
                        'etc'=>'70',
                        'surcharge'=>'0',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'0',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'0',
                    ],
                    '4' => [
                        'etc'=>'80',
                        'surcharge'=>'0',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'50',
                        'surcharge'=>'0',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'0',
                    ],
                    '4' => [
                        'etc'=>'90',
                        'surcharge'=>'0',
                    ],
                ],
            ],
            'Truck' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'80',
                        'surcharge'=>'800',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'70',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'800',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'90',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'800',
                    ],
                ],
            ],
            'SUV' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'500',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'500',
                    ],
                    '4' => [
                        'etc'=>'80',
                        'surcharge'=>'500',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'50',
                        'surcharge'=>'500',
                    ],
                    '2' => [
                        'etc'=>'70',
                        'surcharge'=>'500',
                    ],
                    '4' => [
                        'etc'=>'90',
                        'surcharge'=>'500',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'60',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'90',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'800',
                    ],
                ],
            ],
            'Van' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'2000',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'4000',
                    ],
                    '4' => [
                        'etc'=>'95',
                        'surcharge'=>'4000',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'2000',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'4000',
                    ],
                    '4' => [
                        'etc'=>'95',
                        'surcharge'=>'4000',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'60',
                        'surcharge'=>'2000',
                    ],
                    '2' => [
                        'etc'=>'90',
                        'surcharge'=>'4000',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'4000',
                    ],
                ],
            ],
            'Minivan' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'80',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'800',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'80',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'800',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'800',
                    ],
                    '2' => [
                        'etc'=>'80',
                        'surcharge'=>'800',
                    ],
                    '4' => [
                        'etc'=>'110',
                        'surcharge'=>'800',
                    ],
                ],
            ],
        ];


        foreach([1,2,4] as $service_id) {

            $service = Service::find($service_id);

            foreach($types as $type) {
                foreach($sizes as $size) {
                    $service->attribs()->create([
                        'vehicle_type' => $type,
                        'vehicle_size' => $size,
                        'etc' => $etc_surcharge[$type][$size][$service_id]['etc'],
                        'surcharge' => $etc_surcharge[$type][$size][$service_id]['surcharge'],
                    ]);
                }
            }

        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}