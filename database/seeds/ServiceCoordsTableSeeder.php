<?php

use App\ServiceCoord;
use Illuminate\Database\Seeder;


class ServiceCoordsTableSeeder extends Seeder
{
    public function run()
    {
        ServiceCoord::truncate();

        $coords = [
            "34.035768"=>"-118.535957",
            "34.049423"=>"-118.529348",
            "34.041529"=>"-118.518190",
            "34.044587"=>"-118.516645",
            "34.046507"=>"-118.513298",
            "34.051059"=>"-118.510809",
            "34.050134"=>"-118.509178",
            "34.047645"=>"-118.510036",
            "34.046508"=>"-118.508148",
            "34.052410"=>"-118.503427",
            "34.056606"=>"-118.495874",
            "34.061228"=>"-118.494672",
            "34.056748"=>"-118.490123",
            "34.058810"=>"-118.478407",
            "34.065423"=>"-118.470296",
            "34.071289"=>"-118.469052",
            "34.074257"=>"-118.464267",
            "34.073244"=>"-118.461863",
            "34.077172"=>"-118.453881", //
            "34.093309"=>"-118.439519",
//            "34.073878"=>"-118.446939",
//            "34.077035"=>"-118.444055",
//            "34.082740"=>"-118.434806", //sunset&beverly
            "34.108129"=>"-118.405431", //coldwater canyon

            "34.114088"=>"-118.337002", //hollywood bowl north
            "34.105916"=>"-118.325200",
            "34.105419"=>"-118.284774", //hollywood & hoover
            "34.076204"=>"-118.284431", //hoover & beverly

            ///*** Downtown pilot ****/
            "34.042956"=>"-118.264125", //flower & 11st
            "34.041543"=>"-118.261990", //11th & grand
            "34.038947"=>"-118.264342", //grand & pico
            "34.040090"=>"-118.266887", //pico & flower
            "34.042957"=>"-118.264125", //flower & 11st

            ////*** *END ** ////

            "34.076200"=>"-118.284431", //hoover & beverly
            "34.076087"=>"-118.327304", //beverly & rossmore

            "34.061691"=>"-118.327341",
            "34.061913"=>"-118.335345",
            "34.046615"=>"-118.341439",
            "34.042988"=>"-118.363068",
            "34.039330"=>"-118.369205",

            "33.988248"=>"-118.367488",
            "33.960279"=>"-118.368861", //la cienega & manchester

            "33.959880"=>"-118.394777", //machetser & sepulveda
            "33.953735"=>"-118.394987",
            "33.947705"=>"-118.447482",
            "33.931261"=>"-118.437402", //southbay - imperial-105 & ocean
            "33.930905"=>"-118.368995", //105/405
            "33.902307"=>"-118.370348", //rosecrans - 405
            "33.899112"=>"-118.370348",
            "33.896316"=>"-118.368808",
            "33.884574"=>"-118.352449",
            "33.859851"=>"-118.352619",
            "33.858270"=>"-118.353563",
            "33.837455"=>"-118.353606", //hawthorne blvd & torrance blvd
            "33.824782"=>"-118.351159",
            "33.804101"=>"-118.351159",
            "33.818935"=>"-118.385492",
            "33.819599"=>"-118.391328",
            "33.837954"=>"-118.390985",
            "33.837955"=>"-118.390985",
            "33.843679"=>"-118.400426",
            "33.850046"=>"-118.402062",
            "33.852131"=>"-118.399832",
            "33.901983"=>"-118.422376",
            "33.952365"=>"-118.449928",
        ];

        foreach($coords as $lat=>$lng) {
            ServiceCoord::create([
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }

    }
}
