<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;


class KycAgingExport implements FromCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }
}
