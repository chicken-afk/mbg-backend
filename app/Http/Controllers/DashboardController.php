<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalIncome = Transaction::where('type', 'pemasukan')->sum('amount');
        $totalExpense = Transaction::where('type', 'pengeluaran')->sum('amount');
        $saldo = $totalIncome + $totalExpense;

        // Get Total Expence By User Group By User
        $totalSpent = Transaction::where('type', 'pengeluaran')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('transactions.user_id, users.name, SUM(transactions.amount) as total_spent')
            ->groupBy('transactions.user_id', 'users.name')
            ->orderBy('total_spent', 'asc')
            ->get();
        // Prepare the dashboard data
        $dashboardData = [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'saldo' => $saldo,
            'total_spent_by_user' => $totalSpent,
            'recent_transactions' => Transaction::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];
        $output = [
            'status' => 'success',
            'message' => 'Dashboard data retrieved successfully',
            'data' => $dashboardData,
        ];
        // Return the dashboard data
        return response()->json($output, 200);
    }
}
