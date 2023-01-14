<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeSuggestRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
        'destination' => 'string|required',
        'events' => 'array|required',
        'events.*.type' => 'string|required',
        'events.*.message.type' => 'string|required_if:events.*.type,message',
        'events.*.message.id' => 'string|required_if:events.*.type,message',
        'events.*.message.text' => 'string|required_if:events.*.type,message',
        'events.*.timestamp' => 'integer|required',
        'events.*.source.type' => 'string|required',
        'events.*.source.userId' => 'string|required',
        'events.*.replyToken' => 'string|required_unless:events.*.type,unfollow',
        'events.*.mode' => 'string|required',
        'events.*.webhookEventId' => 'string|required',
        'events.*.deliveryContext.isRedelivery' => 'boolean|required',
        ];
    }
}
