<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class StoreStatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:store-states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch States and store it in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $country = Country::where(['geoname_id' => '2328926'])->first(); // getting for nigeria only

        $response = Http::get('http://api.geonames.org/childrenJSON', [
            'geonameId' => config('services.geonames.country_id'),
            'code' => 'NG',
            'username' => config('services.geonames.username'),
        ]);

        $data = $response->json();

        $states = $data['geonames'];

        foreach ($states as $state) {
            $attributes = [
                'name' => $state['name'],
                'code' => $state['adminCodes1']['ISO3166_2'],

            ];

            State::updateOrCreate(['geoname_id' => $state['geonameId']], $attributes);
        }

        $this->info('States fetched and stored successfully!');
    }
}
