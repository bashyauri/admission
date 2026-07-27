<?php

namespace App\Imports;

use App\Models\PostUtmeUpload;
use App\Services\AcademicSessionService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
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

     return new PostUtmeUpload([
    'jamb_no'      => Arr::get($row, 'rg_num'),
    'name'         => Arr::get($row, 'rg_candname'),
'course'       => Arr::get($row, 'co_name'),
    'jamb_score'   => Arr::get($row, 'rg_aggregate'),
    'acad_session' => app(AcademicSessionService::class)
        ->getAcademicSession(Auth::user()),
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
