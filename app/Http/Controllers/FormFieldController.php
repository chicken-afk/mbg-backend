<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Models\FormField;

class FormFieldController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all form fields
        $formFields = FormField::active();
        if ($request->has('show_in_table')) {
            $formFields = $formFields->where('show_in_table', $request->input('show_in_table'));
        }
        if ($request->has('type')) {
            $formFields = $formFields->where('type', $request->input('type'));
        }
        if ($request->has('name')) {
            $formFields = $formFields->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->has('label')) {
            $formFields = $formFields->where('label', 'like', '%' . $request->input('label') . '%');
        }
        if ($request->has('required')) {
            $formFields = $formFields->where('required', $request->input('required'));
        }
        if ($request->has('status')) {
            $formFields = $formFields->where('status', $request->input('status'));
        }

        $clientId = $request->input('warehouse_id');

        if ($clientId) {
            $formFields = $formFields->where('warehouse_id', $clientId);
        }

        $formFields = $formFields->get();

        return response()->json($formFields);
    }

    public function store(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'options' => 'nullable|array',
            'required' => 'boolean',
            'show_in_table' => 'boolean',
            "warehouse_id" => 'required|exists:warehouses,id,deleted_at,NULL',
            "label" => 'required|string|max:255',
        ]);

        $clientId = $validatedData['warehouse_id'];

        // Create a new form field
        $validatedData['options'] = $request->has('options') ? json_encode($validatedData['options']) : null;
        $validatedData['status'] = $validatedData['status'] ?? true; // Default to true if not provided
        $validatedData['show_in_table'] = $validatedData['show_in_table'] ?? false; // Default to false if not provided
        $validatedData['required'] = $validatedData['required'] ?? false; // Dult label to name if not provided
        $validatedData['name'] = strtolower(str_replace(' ', '_', $validatedData['name'])); // Convert name to lowercase and replace spaces with underscores
        $validatedData['warehouse_id'] = $clientId; // Set the warehouse_id based on the user's role

        if ($request->has("id") && $request->input("id") != null) {
            $formField = FormField::findOrFail($request->input("id"));
            $formField->update($validatedData);
        } else {
            $formField = FormField::create($validatedData);
        }

        return response()->json(null, 201);
    }

    public function update(Request $request, $id)
    {
        // Find the form field
        $formField = FormField::findOrFail($id);

        // Validate the request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'options' => 'nullable|array',
            'required' => 'boolean',
            'show_in_table' => 'boolean',
        ]);

        // Update the form field
        $validatedData['options'] = $request->has('options') ? json_encode($validatedData['options']) : null;
        $validatedData['show_in_table'] = $validatedData['show_in_table'] ?? false; // Default to false if not provided
        $validatedData['required'] = $validatedData['required'] ?? false; // Default to false if not provided
        $validatedData['label'] = $validatedData['label'] ?? $validatedData['name']; // Default label to name if not provided
        $validatedData['name'] = strtolower(str_replace(' ', '_', $validatedData['name'])); // Convert name to lowercase and replace spaces with underscores
        $validatedData['status'] = $validatedData['status'] ?? $formField->status; // Keep the current status if not provided
        $validatedData['show_in_table'] = $validatedData['show_in_table'] ?? $formField->show_in_table; // Keep the current show_in_table if not provided
        $validatedData['required'] = $validatedData['required'] ?? $formField->required; // Keep the current required if not provided
        $validatedData['options'] = $validatedData['options'] ?? $formField->options; // Keep the current options if not provided
        $formField->update($validatedData);

        return response()->json($formField);
    }

    public function destroy($id)
    {
        // Find the form field
        $formField = FormField::findOrFail($id);

        // Soft delete the form field
        $formField->delete();

        return response()->json(['message' => 'Form field deleted successfully']);
    }
}
