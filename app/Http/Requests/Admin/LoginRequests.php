<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class LoginRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account' => ['required'],
            'password' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'account.required'    => 'The :attribute and :required',
            'password.required'    => 'The :attribute must be required.',

        ];
    }
}
