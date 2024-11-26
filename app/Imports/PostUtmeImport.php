<?php

namespace App\Imports;

use App\Models\PostUtmeUpload;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class PostUtmeImport implements ToModel, WithHeadingRow, WithBatchInserts, WithUpserts
{
    /**
     * Define the model for each row.
     *
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::info('Row data:', $row); //
        return new PostUtmeUpload([
            'jamb_no' => $row['rg_num'],
            'name'    => $row['rg_candname'],
        ]);
    }

    /**
     * Specify the columns used for upserts.
     *
     * @return string[]
     */
    public function uniqueBy()
    {
        return ['jamb_no']; // Define the unique column for upserts
    }

    /**
     * Enable batch inserts for better performance.
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 1000; // Adjust batch size as needed
    }
}
