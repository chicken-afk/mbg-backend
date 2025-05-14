<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with('user');
        if ($search = $request->input('search')) {
            $search = strtolower($search);
            $transactions->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(amount) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    });
            });
        }
        if ($type = $request->input('type')) {
            $transactions->where('type', $type);
        }
        if ($status = $request->input('status')) {
            $transactions->where('status', $status);
        }
        if ($payment_method = $request->input('payment_method')) {
            $transactions->where('payment_method', $payment_method);
        }
        if ($date = $request->input('transaction_at')) {
            $transactions->whereDate('transaction_at', $date);
        }
        $transactions = $transactions->orderBy('created_at', 'desc')->paginate(10);
        // $resData = $transactions->get();
        $output = [
            'status' => 'success',
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions,
        ];
        return response()->json($output, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer',
            'category' => 'required|in:Pemasukan,Pengeluaran',
            'description' => 'nullable|string',
            'paymentMethod' => 'required|string',
        ]);
        $additionalData = null;
        if ($request->has("customFields")) {
            $additionalData = json_encode($request->input("customFields"));
        }
        $transaction = Transaction::create([
            'uuid' => \Str::uuid(),
            'user_id' => auth()->id(),
            'status' => 'selesai',
            'amount' => $validated['amount'],
            'type' => strtolower($validated['category']),
            'description' => $validated['description'] ?? null,
            'payment_method' => $validated['paymentMethod'] ?? null,
            'transaction_at' => $request->input('transaction_at') ?? now(),
            'additional_data' => $additionalData,
        ])->load('user');
        return response()->json($transaction, 201);
    }

    public function show(String $uuid)
    {
        $transaction = Transaction::with('user')
            ->where('uuid', $uuid)->first();
        $output = [
            'status' => 'success',
            'message' => 'Transaction retrieved successfully',
            'data' => $transaction,
        ];
        return response()->json($output, 200);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'amount' => 'integer',
            'type' => 'in:pemasukan,pengeluaran',
            'description' => 'nullable|string',
            'status' => 'string',
            'payment_method' => 'nullable|string',
        ]);

        $transaction->update($validated);
        return response()->json($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }

    public function exportPdf(Request $request)
    {
        $query = Transaction::with('user');
        if ($request->has('start_date') && $request->start_date !== null && $request->start_date !== 'null') {
            $query->whereDate('transaction_at', '>=', $request->input('start_date'));
        }
        // dd($request->all());
        if ($request->has('end_date') && $request->end_date !== null && $request->end_date !== 'null') {
            $query->whereDate('transaction_at', '<=', $request->input('end_date'));
        }
        if ($request->has('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }
        $transactions = $query->orderBy('transactions.id', "desc")->get();
        $start_date = ($request->has('start_date') && $request->start_date !== null && $request->start_date !== 'null') ? date('d-m-Y', strtotime($request->input('start_date'))) : null;
        $end_date = ($request->has('end_date') && $request->end_date !== null && $request->end_date !== 'null') ? date('d-m-Y', strtotime($request->input('end_date'))) : null;
        $pdf = \PDF::loadView('transactions.export', compact(
            'transactions',
            'start_date',
            'end_date',
        ));
        return $pdf->download('transactions.pdf');
    }
}
