<?php

namespace App\Console\Commands;

use App\Models\Lga;
use App\Models\State;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class StoreLgasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:store-lgas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch LGA of each state and store it in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $states = State::all();

        foreach ($states as $state) {
            $response = Http::get('http://api.geonames.org/childrenJSON', [
                'geonameId' => $state['geoname_id'],
                'username' => config('services.geonames.username'),
            ]);

            $data = $response->json();
            $lgas = $data['geonames'];

            foreach ($lgas as $lga) {
                $attributes = [
                    'name' => $lga['name'],
                    'state_id' => $state['id'],
                ];

                Lga::updateOrCreate(['geoname_id' => $lga['geonameId']], $attributes);
            }
        }

        $this->info('LGAs fetched and stored successfully!');
    }
}
