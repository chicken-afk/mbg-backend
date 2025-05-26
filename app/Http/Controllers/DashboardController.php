<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');

        $totalIncomeQuery = Transaction::where('type', 'pemasukan');
        $totalExpenseQuery = Transaction::where('type', 'pengeluaran');
        $totalSpentQuery = Transaction::where('type', 'pengeluaran')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('transactions.user_id, users.name, SUM(transactions.amount) as total_spent')
            ->groupBy('transactions.user_id', 'users.name')
            ->orderBy('total_spent', 'asc');
        $totalIncomeByUserQuery = Transaction::where('type', 'pemasukan')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('transactions.user_id, users.name, SUM(transactions.amount) as total_income')
            ->groupBy('transactions.user_id', 'users.name')
            ->orderBy('total_income', 'desc');
        $recentTransactionsQuery = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5);

        if ($warehouseId !== null) {
            $totalIncomeQuery->where('warehouse_id', $warehouseId);
            $totalExpenseQuery->where('warehouse_id', $warehouseId);
            $totalSpentQuery->where('transactions.warehouse_id', $warehouseId);
            $totalIncomeByUserQuery->where('transactions.warehouse_id', $warehouseId);
            $recentTransactionsQuery->where('warehouse_id', $warehouseId);
        }

        $totalIncome = $totalIncomeQuery->sum('amount');
        $totalExpense = $totalExpenseQuery->sum('amount');
        $saldo = $totalIncome + $totalExpense;
        $totalSpent = $totalSpentQuery->get();
        $totalIncomeByUser = $totalIncomeByUserQuery->get();
        $recentTransactions = $recentTransactionsQuery->get();

        $dashboardData = [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'saldo' => $saldo,
            'total_spent_by_user' => $totalSpent,
            'total_income_by_user' => $totalIncomeByUser,
            'recent_transactions' => $recentTransactions,
        ];
        $output = [
            'status' => 'success',
            'message' => 'Dashboard data retrieved successfully',
            'data' => $dashboardData,
        ];
        return response()->json($output, 200);
    }
}
