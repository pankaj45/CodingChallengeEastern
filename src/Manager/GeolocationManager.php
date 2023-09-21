<?php

namespace App\Manager;

use App\Service\PositionStack;

class GeolocationManager
{
    public const SAMPLE_ADDRESSES =[
        'The White House -1600 Pennsylvania Avenue, Washington, D.C., USA',
        'Sherlock Holmes -221B Baker St., London, United Kingdom',
        'Adchieve Rotterdam -Weena 505, 3013 AL Rotterdam, The Netherlands',
        'The Empire State Building -350 Fifth Avenue, New York City, NY 10118',
        'Neverland -5225 Figueroa Mountain Road, Los Olivos, Calif. 93441, USA',
        'The Pope -Saint Martha House, 00120 Citta del Vaticano, Vatican City',
        'Eastern Enterprise -46/1 Office no 1 Ground Floor , Dada House , Inside dada silk mills compound, Udhana Main Rd, near Chhaydo Hospital, Surat, 394210, India',
        'Eastern Enterprise B.V. -Deldenerstraat 70, 7551AH Hengelo, The Netherlands'
    ];

    //We are saving Adchieve HQ longitude/latitude in constants as we know we always have to calculate distance from it.
    public const ADCHIEVE_LONGITUDE = 5.298532;
    public const ADCHIEVE_LATITUDE = 51.6882;

    public function __construct(
        private PositionStack $positionStack,
    )
    {

    }

    public function calculateDistanceToAdchieve(): array
    {
        $addresses = [];
        foreach (self::SAMPLE_ADDRESSES as $address)
        {
            $addressWithName = explode("-", $address);
            $apiResponse = $this->positionStack->fetchAddress($addressWithName[1]);

            if($apiResponse) {
                $geolocation = ($apiResponse->getData())[0];
                $distance = $this->calculateDistanceTwoPointsInMiles(
                    $geolocation->getLatitude(),
                    $geolocation->getLongitude(),
                    self::ADCHIEVE_LATITUDE,
                    self::ADCHIEVE_LONGITUDE
                );

                $distanceInKms = round($distance * 1.609344, 2);
                $addresses[] = ['name' => $addressWithName[0], 'distance' => $distanceInKms, 'address' => $addressWithName[1]];
            }
        }

        return $addresses;
    }

    public function calculateDistanceTwoPointsInMiles(
        float $latitudeFrom,
        float $longitudeFrom,
        float $latitudeTo,
        float $longitudeTo
    ): float
    {
        $long1 = deg2rad($longitudeFrom);
        $long2 = deg2rad($longitudeTo);
        $lat1 = deg2rad($latitudeFrom);
        $lat2 = deg2rad($latitudeTo);

        $dlong = $long2 - $long1;
        $dlati = $lat2 - $lat1;

        $val = pow(sin($dlati/2),2)+cos($lat1)*cos($lat2)*pow(sin($dlong/2),2);

        $res = 2 * asin(sqrt($val));

        $radius = 3958.756;

        return ($res*$radius);
    }
}