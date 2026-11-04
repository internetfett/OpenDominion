@php($rulesTooltip = $heroHelper->getLoadoutOptionRulesTooltip($option))
@if ($rulesTooltip !== null)
    <i class="fa fa-info-circle text-secondary" title="{{ $rulesTooltip }}" data-bs-toggle="tooltip" tabindex="0" aria-label="Rules"></i>
@endif
