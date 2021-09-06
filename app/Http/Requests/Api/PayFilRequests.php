<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class PayFilRequests extends BaseRequest
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
            'order_number' => ['required'],
            'price' => ['required'],
            'real_price' => ['required'],
            'type_bi' => ['required'],
            'real_pay' => ['required'],
            'password' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'order_number.required'    => 'The :order_number and :required',
            'price.required'    => 'The :price and :required',
            'real_price.required'    => 'The :real_price and :required',
            'type_bi.required'    => 'The :type_bi and :required',
            'real_pay.required'    => 'The :real_pay and :required',
            'password.required'    => 'The :password and :required',
        ];
    }
}
