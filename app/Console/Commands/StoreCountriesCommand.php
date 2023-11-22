<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class StoreCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:store-countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Storing  countries to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {


        $response = Http::get('http://api.geonames.org/countryInfoJSON', [
            'country' => 'NG',
            'username' => config('services.geonames.username'),
        ]);

        $data = $response->json();
        $countries = $data['geonames'];

        foreach ($countries as $country) {
            $countryData = [
                'geoname_id' => $country['geonameId'],
                'code' => $country['countryCode'],
                'name' => $country['countryName'],
            ];
            Country::updateOrCreate(['geoname_id' => $countryData['geoname_id']], $countryData);
        }

        $this->info('Countries fetched and stored successfully!');
    }
}
