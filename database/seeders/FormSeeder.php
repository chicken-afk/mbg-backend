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
                'name' => 'category',
                'label' => 'Kategori',
                'type' => 'select',
                'options' => ['Makanan', 'Transportasi', 'Kesehatan', 'Pendidikan'],
                'required' => true,
            ]
        ];
        if (\App\Models\FormField::count() > 0) {
            return; // Default form fields already exist, no need to create again
        }
        foreach ($defaultFields as $field) {
            \App\Models\FormField::create(
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
