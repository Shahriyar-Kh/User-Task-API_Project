<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;


class ImportFileRequest extends FormRequest
{
public function authorize(): bool
{
return true;
}


public function rules(): array
{
return [
'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:30720', // 30 MB
];
}
}