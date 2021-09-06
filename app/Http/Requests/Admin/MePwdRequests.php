<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class MePwdRequests extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => [
                'required',
            ],
            'password' => [
                'required',
                'confirmed',
            ],
            'password_confirmation' => [
                'required',
                'same:password'
            ]
        ];
    }
}
