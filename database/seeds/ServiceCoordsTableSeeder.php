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
            "34.077172"=>"-118.453881",
            "34.073878"=>"-118.446939",
            "34.077035"=>"-118.444055",
            "34.082740"=>"-118.434806",
            "34.078794"=>"-118.417104",
            "34.086809"=>"-118.405838",
            "34.090772"=>"-118.392277",
            "34.091234"=>"-118.383093",
            "34.094433"=>"-118.376270",

            "34.102322"=>"-118.365584",
            "34.101754"=>"-118.345113",
            "34.098236"=>"-118.343955",
            "34.098237"=>"-118.338376",
            "34.098138"=>"-118.326478",
            "34.061691"=>"-118.327341",
            "34.061913"=>"-118.335345",
            "34.046615"=>"-118.341439",
            "34.042988"=>"-118.363068",
            "34.034987"=>"-118.377788",

            "34.034813"=>"-118.377815",
            "34.032501"=>"-118.374295",
            "34.021404"=>"-118.377385",
            "34.016756"=>"-118.380547",
            "34.000091"=>"-118.380638",
            "33.989058"=>"-118.380233",
            "33.988524"=>"-118.393365",
            "33.959412"=>"-118.395640",
            "33.954255"=>"-118.396327",
            "33.947705"=>"-118.447482",
            "33.952364"=>"-118.449928",
            "33.901982"=>"-118.422376", //southbay - rosecrans/ocean
            "33.902307"=>"-118.370348", //rosecrans - 405
            "33.899112"=>"-118.370348",
            "33.896316"=>"-118.368808",
            "33.884574"=>"-118.352449",
            "33.859851"=>"-118.352619",
            "33.858270"=>"-118.353563",
            "33.837455"=>"-118.353606", //hawthroner blvd & torrance blvd
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
