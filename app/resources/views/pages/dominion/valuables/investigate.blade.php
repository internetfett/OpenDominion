@extends('layouts.master')

@section('page-header', 'Investigate Valuable')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-scout"></i> Start Investigation</h3>
                </div>
                <form action="{{ route('dominion.espionage.valuables.investigate', $valuable) }}" method="post" role="form">
                    @csrf
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Valuable</label>
                                    <p class="form-control-static">
                                        <strong>{{ $valuablesHelper->getDiscoveryDisplay($valuable) }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Target Dominion</label>
                                    <p class="form-control-static">
                                        <strong>{{ $valuable->targetDominion->name }}</strong> (#{{ $valuable->targetDominion->realm->number }})
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-4 col-lg-4">
                                <label for="spies_assigned" id="spiesLabel">Spies to Assign</label>
                                <input type="number"
                                        name="spies_assigned"
                                        id="spies_assigned"
                                        class="form-control text-center"
                                        value="{{ old('spies_assigned', $minSpies) }}"
                                        placeholder="{{ $minSpies }}"
                                        min="{{ $minSpies }}"
                                        max="{{ min($maxSpies, $availableSpies) }}"
                                        {{ $selectedDominion->isLocked() ? 'disabled' : null }}>
                            </div>
                            <div class="form-group col-sm-8 col-lg-8">
                                <label for="spiesSlider">Number of Spies</label>
                                <input type="number"
                                        id="spiesSlider"
                                        class="form-control slider"
                                        data-slider-value="{{ $minSpies }}"
                                        data-slider-min="{{ $minSpies }}"
                                        data-slider-max="{{ min($maxSpies, $availableSpies) }}"
                                        data-slider-step="1"
                                        data-slider-tooltip="show"
                                        data-slider-handle="triangle"
                                        data-slider-id="yellow"
                                        {{ $selectedDominion->isLocked() ? 'disabled' : null }}>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" {{ $selectedDominion->isLocked() ? 'disabled' : null }}>
                            <i class="ra ra-scout"></i> Start Investigation
                        </button>
                        <a href="{{ route('dominion.espionage') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>Assign spies to investigate this valuable. The more spies you assign, the faster your investigation will progress.</p>
                    <p><strong>Note:</strong> Once you start an investigation, you cannot change the number of spies assigned.</p>
                    <p>This {{ $valuable->rarity }} valuable requires <strong>{{ number_format($requiredSpyHours) }}</strong> spy-hours to complete.</p>
                    <p>You must assign between <strong>{{ number_format($minSpies) }}</strong> and <strong>{{ number_format($maxSpies) }}</strong> {{ str_plural('spy', $maxSpies) }}.</p>
                    <p>You have <strong>{{ number_format($availableSpies) }}</strong> {{ str_plural('spy', $availableSpies) }} available for assignment.</p>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('page-styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/admin-lte/plugins/bootstrap-slider/slider.css') }}">
@endpush

@push('page-scripts')
    <script type="text/javascript" src="{{ asset('assets/vendor/admin-lte/plugins/bootstrap-slider/bootstrap-slider.js') }}"></script>
@endpush

@push('inline-scripts')
    <script type="text/javascript">
        (function ($) {
            var spiesElement = $('#spies_assigned'),
                spiesSliderElement = $('#spiesSlider');

            spiesElement.on('change', function() {
                var spiesValue = Math.min(parseInt(spiesElement.val() || 0), {{ $availableSpies }});
                if (spiesValue == 0) {
                    spiesValue = '';
                }
                spiesElement.val(spiesValue);
                spiesSliderElement.slider('setValue', spiesValue || 0);
            });

            spiesSliderElement.slider({
                formatter: function (value) {
                    return value.toLocaleString();
                }
            }).on('change', function (slideEvent) {
                spiesElement.val(slideEvent.value.newValue).change();
            });
        })(jQuery);
    </script>
@endpush
