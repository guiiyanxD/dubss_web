<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubirDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'requisito_id' => 'required|exists:requisitos,id',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ];
    }

    public function messages()
    {
        return [
            'requisito_id.required' => 'Debes especificar el requisito',
            'requisito_id.exists' => 'El requisito no existe',
            'archivo.required' => 'Debes subir un archivo',
            'archivo.mimes' => 'El archivo debe ser PDF, JPG, JPEG o PNG',
            'archivo.max' => 'El archivo no puede superar 10MB',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
