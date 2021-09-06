<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class UserAddressRequests extends BaseRequest
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
            'id'=>[],
            'address_name' => ['required'],
            'consignee' => ['required'],
            'phone' => ['required'],
            'status' => ['required'],//1设置默认 2设置不默认
            'address' => ['required'],//1设置默认 2设置不默认
        ];
    }

    public function messages()
    {
        return [
            'address_name.required'    => 'The :address_name and :required',
            'consignee.required'    => 'The :consignee must be required.',
            'phone.required'    => 'The :phone must be required.',
            'status.required'    => 'The :status must be required.',
            'address.required'    => 'The :address must be required.',
        ];
    }
}
