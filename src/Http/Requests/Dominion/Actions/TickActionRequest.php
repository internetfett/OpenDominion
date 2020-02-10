<?php

namespace OpenDominion\Http\Requests\Dominion\Actions;

use OpenDominion\Http\Requests\Dominion\AbstractDominionRequest;

class TickActionService extends AbstractDominionRequest
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'dominion' => 'required'
        ];
    }
}
