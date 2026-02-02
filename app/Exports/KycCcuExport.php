<?php

namespace App\Exports;

use App\Models\KycForm; // or use query builder
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KycCcuExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        // Reuse same logic as dashboard (you can extract to trait/service later)
        // For brevity, returning simplified version
        return \DB::table('kyc_forms')->select(
            'account_id', 'fullname', 'phone', 'state', 'current_stage', 'submitted_at'
        )->get();
    }

    public function headings(): array
    {
        return ['Account ID', 'Name', 'Phone', 'State', 'Current Stage', 'Submitted At'];
    }
}