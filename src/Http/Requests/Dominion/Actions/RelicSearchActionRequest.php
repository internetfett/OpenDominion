<?php

namespace OpenDominion\Http\Requests\Dominion\Actions;

use OpenDominion\Http\Requests\Dominion\AbstractDominionRequest;

class RelicSearchActionRequest extends AbstractDominionRequest
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $intensities = implode(',', [0, ...config('relics.search.intensities')]);

        return [
            'intensity' => "required|integer|in:{$intensities}",
            'stop_on_find' => 'nullable|boolean',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function messages()
    {
        return [
            'intensity.in' => 'Choose a valid Search intensity.',
        ];
    }
}
