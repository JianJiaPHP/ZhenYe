<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class OrderCreateRequests extends BaseRequest
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
            'goods_list' => ['required'],
            'address_id' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'type.required'    => 'The :type and :required',
            'goods_list.required'    => 'The :goods_list and :required',
            'address_id.required'    => 'The :address_id and :required',
        ];
    }
}
