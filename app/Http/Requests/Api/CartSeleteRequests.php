<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class CartSeleteRequests extends BaseRequest
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
            'goods_id' => ['required','integer'],
            'count' => ['required','numeric'],
        ];
    }

    public function messages()
    {
        return [
            'goods_id.required'    => 'The :goods_id and :required',
            'goods_id.integer'    => 'The :goods_id and :integer',
            'count.required'    => 'The :count and :required',
            'count.numeric'    => 'The :count and :numeric',
        ];
    }
}
