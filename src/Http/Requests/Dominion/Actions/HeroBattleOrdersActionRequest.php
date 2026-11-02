<?php

namespace OpenDominion\Http\Requests\Dominion\Actions;

use OpenDominion\Http\Requests\Dominion\AbstractDominionRequest;

class HeroBattleOrdersActionRequest extends AbstractDominionRequest
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'battle' => 'required|integer',
            'orders' => 'required|array',
            'orders.*.action' => 'required|string',
            'orders.*.target' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'orders.required' => 'Choose an action for each of your heroes.',
            'orders.*.action.required' => 'Choose an action for each of your heroes.',
        ];
    }
}
