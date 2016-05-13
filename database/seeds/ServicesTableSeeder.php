<?php

use App\Service;
use App\ServiceAttrib;
use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder {

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

//        Service::truncate();
        ServiceAttrib::truncate();

        $types = ['Car','Truck','SUV','Van','Minivan'];
        $sizes = ['Compact','Midsize','Large'];
        $etc_surcharge = [
            'Car' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'20',
                        'surcharge'=>'0',
                    ],
                    '2' => [
                        'etc'=>'30',
                        'surcharge'=>'0',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'30',
                        'surcharge'=>'0',
                    ],
                    '2' => [
                        'etc'=>'45',
                        'surcharge'=>'0',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'0',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'0',
                    ],
                ],
            ],
            'Truck' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'30',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'20',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'30',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'20',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'20',
                    ],
                ],
            ],
            'SUV' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'30',
                        'surcharge'=>'5',
                    ],
                    '2' => [
                        'etc'=>'40',
                        'surcharge'=>'10',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'5',
                    ],
                    '2' => [
                        'etc'=>'60',
                        'surcharge'=>'10',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'90',
                        'surcharge'=>'20',
                    ],
                ],
            ],
            'Van' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'20',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'40',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'45',
                        'surcharge'=>'20',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'40',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'60',
                        'surcharge'=>'20',
                    ],
                    '2' => [
                        'etc'=>'90',
                        'surcharge'=>'40',
                    ],
                ],
            ],
            'Minivan' => [
                'Compact' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'20',
                    ],
                ],
                'Midsize' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'20',
                    ],
                ],
                'Large' => [
                    '1' => [
                        'etc'=>'40',
                        'surcharge'=>'10',
                    ],
                    '2' => [
                        'etc'=>'75',
                        'surcharge'=>'20',
                    ],
                ],
            ],
        ];

        ////// ***** EXPRESS ***** ///////

//        $express = Service::create([
//            'name' => 'Express',
//            'price' => '2500',
//            'details' => '["- Exterior Wash & Wax", "- Clean Exterior Windows", "- Light Wheel Wipe Down", "- Clean & Dress Tires"]',
//            'time' => '30',
//            'time_label' => '30 - 45 Minutes (Depending on Vehicle)',
//            'sequence' => 2,
//            'is_active' => 1,
//        ]);


        ////// ***** CLASSIC ***** ///////

//        $classic = Service::create([
//            'name' => 'Classic',
//            'price' => '3900',
//            'details' => '["- Express Wash +", "- Floor & Seat Vacuum", "- Dash & Panel Wipe Down", "- Clean Interior Windows"]',
//            'time' => '60',
//            'time_label' => '45 - 75 Minutes (Depending on Vehicle)',
//            'sequence' => 3,
//            'is_active' => 1,
//        ]);



        foreach([1,2] as $service_id) {

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

        ////// ***** SQUEEGY ***** ///////

//        $squeegy = Service::create([
//            'name' => 'Squeegy',
//            'price' => '1500',
//            'details' => '["- Exterior Wash"]',
//            'time' => '20',
//            'time_label' => '20 - 30 Minutes (Depending on Vehicle)',
//            'sequence' => 1,
//            'is_active' => 1,
//        ]);
//
//        $squeegy->attribs()->create([
//            'vehicle_type' => 'Car',
//            'vehicle_size' => 'Midsize',
//            'etc' => 20,
//            'surcharge' => 0,
//        ]);
//
//        $squeegy->attribs()->create([
//            'vehicle_type' => 'Non-Car',
//            'vehicle_size' => 'Midsize',
//            'etc' => 30,
//            'surcharge' => 200,
//        ]);
//
//        $squeegy->attribs()->create([
//            'vehicle_type' => 'Non-Car',
//            'vehicle_size' => 'Large',
//            'etc' => 40,
//            'surcharge' => 200,
//        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }

}