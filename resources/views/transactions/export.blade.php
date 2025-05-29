<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi</title>
</head>

<body>
    <h3 style="text-align: center;">Data Transaksi</h3>
    <p style="margin-bottom: 3px;margin-top:0px;">Periode: {{ $start_date }} s/d {{ $end_date }}</p>
    <p style="margin-bottom: 3px;margin-top:0px;">Jumlah Transaksi: {{ $transactions->count() }}</p>
    <p style="margin-bottom: 3px;margin-top:0px;">Saldo: Rp.
        {{ number_format($transactions->where('type', 'pemasukan')->sum('amount') - $transactions->where('type', 'pengeluaran')->sum('amount'), 0) }}
    </p>
    <p style="margin-bottom: 3px;margin-top:0px;">Jumlah Transaksi Masuk: Rp.
        {{ number_format($transactions->where('type', 'pemasukan')->sum('amount'), 0) }}</p>
    <p style="margin-bottom: 3px;margin-top:0px;">Jumlah Transaksi Keluar: Rp.
        {{ number_format($transactions->where('type', 'pengeluaran')->sum('amount'), 0) }}</p>

    {{--  --}}

    <h4 style="text-align: center">Pengeluaran Berdasarkan Pengguna</h4>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($totalSpent as $userTransactions)
                <tr>
                    <td>{{ $userTransactions->name }}</td>
                    <td>Rp. {{ number_format($userTransactions->total_spent, 0) }}</td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <br>

    <h4 style="text-align: center">Pemasukan Berdasarkan Pengguna</h4>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($totalIncomeByUser as $userTransactions)
                <tr>
                    <td>{{ $userTransactions->name }}</td>
                    <td>Rp. {{ number_format($userTransactions->total_income, 0) }}</td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <br>

    <h4 style="text-align: center">List Transaksi</h4>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                {{-- <th>Transaksion ID</th> --}}
                <th>Tanggal Input</th>
                <th>Nama</th>
                <th>Jumlah</th>
                <th>Kategori</th>
                <th>Tanggal Transaksi</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    {{-- <td>{{ $transaction->uuid }}</td> --}}
                    <td>
                        {{ $transaction->created_at !== null ? $transaction->created_at->format('H:i d-M-Y') : '-' }}
                    </td>
                    <td>{{ $transaction->user->name }}</td>
                    <td>Rp. {{ number_format($transaction->amount, 0) }}</td>
                    <td>{{ $transaction->type }}</td>
                    <td>
                        {{ $transaction->transaction_at !== null ? $transaction->transaction_at->format('d-M-Y') : '-' }}
                    </td>
                    <td>
                        {{ $transaction->description !== null ? $transaction->description : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
