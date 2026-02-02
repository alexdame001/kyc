<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgingAnalysisExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return DB::table('kyc_forms')
            ->select(
                'id',
                'account_id',
                'name',
                'status',
                DB::raw("DATEDIFF(DAY, created_at, GETDATE()) as aging_days")
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Account ID',
            'Name',
            'Status',
            'Aging Days'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $agingDays = $sheet->getCell('E' . $row)->getValue();

            if ($agingDays > 10) {
                $sheet->getStyle("A{$row}:E{$row}")
                    ->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FF0000'], // Red text
                            'bold' => true
                        ]
                    ]);
            }
        }

        return [];
    }
}
