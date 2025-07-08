<?php

namespace App\Http\Requests\Attachment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAttachmentRequest extends FormRequest
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
    public function rules(): array
    {
        return [
                'attachment'   => ['required','file','mimes:jpg,jpeg,png,pdf','mimetypes:image/jpg,image/jpeg,image/png,application/pdf','max:5120'],
        ];
    }


    /**
     *  Get the error messages for the defined validation rules.
     *
     *  @return array<string, string>
     */
    public function messages():array
    {
        return[
            'attachment.required' => 'The attachment is required please.',
            'attachment.file'     => 'The attachment must be an file',
            'attachment.mimes'    => 'The attachment must be a file of type: jpg,jpeg,png,pdf',
            'attachment.max'      => 'The attachment size must not exceed 5 MB',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     *
     * @return void
     */
    protected function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json
            ([
                'success' => false,
                'message' => 'Data validation error',
                'errors'  => $validator->errors()
            ] , 422));
    }

}
