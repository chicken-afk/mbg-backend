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

        $clientId = $request->input('warehouse_id', null);
        if ($clientId) {
            $transactions->where('warehouse_id', $clientId);
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
            'date' => 'required|date',
            'amount' => 'required|integer',
            'category' => 'required|in:Pemasukan,Pengeluaran',
            'description' => 'nullable|string',
            'paymentMethod' => 'required|string',
            'warehouse_id' => 'required|exists:warehouses,id,deleted_at,NULL',
        ], [
            'amount.required' => 'Jumlah wajib diisi.',
            'amount.integer' => 'Jumlah harus berupa angka.',
            'category.required' => 'Kategori wajib diisi.',
            'category.in' => 'Kategori harus Pemasukan atau Pengeluaran.',
            'paymentMethod.required' => 'Metode pembayaran wajib diisi.',
            'paymentMethod.string' => 'Metode pembayaran harus berupa teks.',
        ]);
        $validated['amount'] = $validated['amount'] < 0 ? $validated['amount'] * -1 : $validated['amount'];
        $additionalData = null;
        if ($request->has("customFields")) {
            $additionalData = json_encode($request->input("customFields"));
        }
        $clientId = $request->input('warehouse_id');
        $base64File = $request->has('invoiceFileBase64') ? $request->invoiceFileBase64  : null;
        $validated['invoice_file_path'] = null;
        if ($base64File) {
            // Extract MIME type and actual base64 content
            [$typeInfo, $base64Content] = explode(';', $base64File);
            [$meta, $mimeType] = explode(':', $typeInfo);
            $extension = explode('/', $mimeType)[1];

            // Decode the base64 content
            $base64Content = explode(',', $base64File)[1];
            $decodedFile = base64_decode($base64Content);

            // Optionally validate base64 decode
            if ($decodedFile === false) {
                throw new \Exception("Invalid base64 file");
            }

            // Save the file
            $fileName = $clientId . "/" . time() . '.' . $extension;
            \Storage::disk('public')->put($fileName, $decodedFile);

            // Set validated fields
            $validated['invoice_file'] = $fileName;
            $validated['invoice_file_path'] = env("APP_URL") . \Storage::url($fileName);
        }

        if ($request->has("id") && $request->input("id") != null) {
            $transaction = Transaction::where("uuid", $request->input("id"))->first();
            $transaction->update([
                'user_id' => auth()->id(),
                'status' => 'selesai',
                'amount' => $validated['amount'],
                'type' => strtolower($validated['category']),
                'description' => $validated['description'] ?? null,
                'payment_method' => $validated['paymentMethod'] ?? null,
                'transaction_at' => $request->input('date') ?? now(),
                'additional_data' => $additionalData,
                "invoice_file" => $validated['invoice_file_path'] ?? $transaction->invoice_file,
            ]);
        } else {
            $transaction = Transaction::create([
                'uuid' => \Str::uuid(),
                'user_id' => auth()->id(),
                'status' => 'selesai',
                'amount' => $validated['amount'],
                'type' => strtolower($validated['category']),
                'description' => $validated['description'] ?? null,
                'payment_method' => $validated['paymentMethod'] ?? null,
                'transaction_at' => $request->input('date') ?? now(),
                'additional_data' => $additionalData,
                'warehouse_id' => $clientId,
                "invoice_file" => $validated['invoice_file_path'],
            ])->load('user');
        }

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
            "warehouse_id" => 'required|exists:warehouses,id,deleted_at,NULL',
        ]);
        $validated['amount'] = $validated['amount'] < 0 ? $validated['amount'] * -1 : $validated['amount'];
        $transaction->update($validated);
        return response()->json($transaction);
    }

    public function destroy(int $id)
    {
        $transaction = Transaction::where('id', $id)->first();
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 422);
        }
        $transaction->deleted_at = now();
        $transaction->deleted_by = auth()->user()->id;
        $transaction->deleted_reason = request()->input('reason') ?? null;
        $transaction->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successfully',
            'data' => $transaction,
        ])->setStatusCode(200, 'Transaction deleted successfully');
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

        // Get Total Expence By User Group By User
        $totalSpent = Transaction::where('type', 'pengeluaran')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->when($request->has('start_date') && $request->start_date !== null && $request->start_date !== 'null', function ($query) use ($request) {
                $query->whereDate('transaction_at', '>=', $request->input('start_date'));
            })
            ->when($request->has('end_date') && $request->end_date !== null && $request->end_date !== 'null', function ($query) use ($request) {
                $query->whereDate('transaction_at', '<=', $request->input('end_date'));
            })
            ->selectRaw('transactions.user_id, users.name, SUM(transactions.amount) as total_spent')
            ->groupBy('transactions.user_id', 'users.name')
            ->orderBy('total_spent', 'asc')
            ->get();

        $totalIncomeByUser = Transaction::where('type', 'pemasukan')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->when($request->has('start_date') && $request->start_date !== null && $request->start_date !== 'null', function ($query) use ($request) {
                $query->whereDate('transaction_at', '>=', $request->input('start_date'));
            })
            ->when($request->has('end_date') && $request->end_date !== null && $request->end_date !== 'null', function ($query) use ($request) {
                $query->whereDate('transaction_at', '<=', $request->input('end_date'));
            })
            ->selectRaw('transactions.user_id, users.name, SUM(transactions.amount) as total_income')
            ->groupBy('transactions.user_id', 'users.name')
            ->orderBy('total_income', 'desc')
            ->get();

        $pdf = \PDF::loadView('transactions.export', compact(
            'transactions',
            'start_date',
            'end_date',
            'totalSpent',
            'totalIncomeByUser'
        ));
        return $pdf->download('transactions.pdf');
    }
}
