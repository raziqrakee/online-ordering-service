<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalesReportController extends Controller
{
    public function index()
    {
        try {
            $salesReports = Order::with('items')
                                 ->selectRaw('DATE(created_at) as date, GROUP_CONCAT(id) as orderIds, SUM(total_amount) as totalSale')
                                 ->groupBy('date')
                                 ->get();

            // Format the orderIds as an array and ensure totalSale is a float
            $salesReports = $salesReports->map(function ($report) {
                return [
                    'date' => $report->date,
                    'orderIds' => explode(',', $report->orderIds),
                    'totalSale' => (float) $report->totalSale,
                ];
            });

            return response()->json(['status' => 200, 'salesReports' => $salesReports], 200);
        } catch (\Exception $e) {
            Log::error('Error generating sales reports: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Error generating sales reports'], 500);
        }
    }
}
