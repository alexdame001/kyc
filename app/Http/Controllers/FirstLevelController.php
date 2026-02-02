<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FirstLevelController extends Controller
{
    /**
     * Dashboard overview with breakdown cards
     */
    public function index()
    {
        // Get counts for RKAM & BM
        $rkamCount = DB::selectOne("
            DECLARE @TotalCount INT;
            EXEC sp_get_firstlevel_dashboard @ValidatorType = 'RKAM', @PageNumber = 1, @PageSize = 1, @TotalCount = @TotalCount OUTPUT;
            SELECT @TotalCount AS TotalCount;
        ")->TotalCount;

        $bmCount = DB::selectOne("
            DECLARE @TotalCount INT;
            EXEC sp_get_firstlevel_dashboard @ValidatorType = 'BM', @PageNumber = 1, @PageSize = 1, @TotalCount = @TotalCount OUTPUT;
            SELECT @TotalCount AS TotalCount;
        ")->TotalCount;

        return view('firstlevel.dashboard', compact('rkamCount', 'bmCount'));
    }

    /**
     * Detailed list view for RKAM or BM
     */
 public function list(Request $request, $validatorType)
{
    $page = $request->get('page', 1);
    $pageSize = 20;
    $search = $request->get('search', null);

    $results = DB::select("
        DECLARE @TotalCount INT;
        EXEC sp_get_firstlevel_dashboard 
            @ValidatorType = ?, 
            @PageNumber = ?, 
            @PageSize = ?, 
            @SearchTerm = ?, 
            @TotalCount = @TotalCount OUTPUT;
        SELECT @TotalCount AS TotalCount;
    ", [$validatorType, $page, $pageSize, $search]);

    $totalCount = $results[count($results) - 1]->TotalCount ?? 0;
    $records = array_slice($results, 0, -1);

    return view('firstlevel.list', [
        'records' => $records,
        'validatorType' => strtoupper($validatorType),
        'totalCount' => $totalCount,
        'page' => $page,
        'pageSize' => $pageSize,
        'search' => $search,
    ]);
}

}
