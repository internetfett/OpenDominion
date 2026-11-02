<?php

namespace OpenDominion\Http\Requests\Dominion\Actions;

use OpenDominion\Http\Requests\Dominion\AbstractDominionRequest;

class HeroLoadoutActionRequest extends AbstractDominionRequest
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            'technique' => 'nullable|string',
            'ability' => 'nullable|string',
            'gear' => 'nullable|array',
            'gear.*' => 'string',
        ];
    }
}
