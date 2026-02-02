<?php

namespace App\Http\Controllers;

use App\Exports\AgingAnalysisExport;
use Maatwebsite\Excel\Facades\Excel;
class AgingAnalysisController extends Controller
{
public function export()
{
    return Excel::download(new AgingAnalysisExport, 'aging_analysis.xlsx');
}
}