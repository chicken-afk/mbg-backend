<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFields = [
            [
                'id' => 1,
                'name' => 'date',
                'label' => 'Tanggal',
                'type' => 'date',
                'required' => true,
            ],
            [
                'id' => 2,
                'name' => 'description',
                'label' => 'Deskripsi',
                'type' => 'text',
                'required' => true,
            ],
            [
                'id' => 3,
                'name' => 'Jumlah',
                'label' => 'Jumlah (Rp.)',
                'type' => 'number',
                'required' => true,
            ],
            [
                'id' => 4,
                'name' => 'category',
                'label' => 'Kategori',
                'type' => 'select',
                'options' => ['Pemasukan', 'Pengeluaran'],
                'required' => true,
            ],
            [
                'id' => 5,
                'name' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => ['Selesai'],
                'required' => true,
            ],
            [
                'id' => 6,
                'name' => 'paymentMethod',
                'label' => 'Metode Pembayaran',
                'type' => 'select',
                'options' => ['Cash', 'Transfer Bank'],
                'required' => true,
            ],
        ];
        if (\App\Models\FormField::count() > 0) {
            return; // Default form fields already exist, no need to create again
        }
        foreach ($defaultFields as $field) {
            \App\Models\FormField::updateOrCreate(
                ['id' => $field['id']],
                [
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'required' => $field['required'],
                    'options' => isset($field['options']) ? json_encode($field['options']) : null,
                ]
            );
        }
        // Create default form fields if they don't exist
    }
}
