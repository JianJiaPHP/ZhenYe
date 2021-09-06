<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class HycMinerOrderCreateRequests extends BaseRequest
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
            'type' => ['required'],
            'miner_id' => ['required'],
            'number' => ['required'],
            'password' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'type.required'    => 'The :type and :required',
            'miner_id.required'    => 'The :miner_id and :required',
            'number.required'    => 'The :number and :required',
            'password.required'    => 'The :password and :required',
        ];
    }
}
