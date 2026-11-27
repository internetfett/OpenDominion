@php($relicHelper = app(\OpenDominion\Helpers\RelicHelper::class))
<table class="table table-sm">
    <colgroup>
        <col width="45%">
        <col width="15%">
        <col width="25%">
        <col width="15%">
    </colgroup>
    <thead>
        <tr>
            <th>{{ $relicHelper->getName(true) }}</th>
            <th>Rarity</th>
            <th>State</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($relics as $relic)
            <tr>
                <td>
                    <span class="{{ $relicHelper->getRarityClass($relic['rarity']) }}">{{ $relic['name'] }}</span>
                    <small class="text-muted">{{ $relicHelper->getCategoryLabel($relic['category']) }}</small>
                </td>
                <td>{{ $relicHelper->getRarityLabel($relic['rarity']) }}</td>
                <td>
                    {{ collect([
                        $relic['equipped'] ? 'Equipped' : null,
                        $relic['bound'] ? 'Bound' : null,
                        $relic['listed'] ? 'Listed' : null,
                    ])->filter()->implode(', ') ?: 'Held' }}
                </td>
                <td>{{ $relic['value'] !== null ? number_format($relic['value']) . 'p' : '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">None</td>
            </tr>
        @endforelse
    </tbody>
</table>
