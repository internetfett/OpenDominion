@php $borrowed = $view->isBorrowed($combatant); @endphp
<form action="{{ route('dominion.heroes.battles') }}" method="post" role="form" class="mb-3">
    @csrf
    <input type="hidden" name="combatant" value="{{ $combatant->id }}">
    @if (count($view->commandable()) > 1)
        <h6 class="fw-bold">{{ $combatant->name }} @if ($borrowed)<small class="text-muted">(borrowed)</small>@endif</h6>
    @endif
    <div class="row mb-3">
        <div class="col-sm-12">
            <label class="form-label">
                Strategy <small>(for turns taken while offline)</small>
            </label>
            <select name="strategy" class="form-select">
                @foreach ($view->strategies() as $key => $strategyName)
                    <option value="{{ $key }}" {{ $combatant->ai == $key ? 'selected' : null }}>{{ $strategyName }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-9">
            <div class="form-check">
                <input type="checkbox" id="automated-{{ $combatant->id }}" name="automated" class="form-check-input" {{ $combatant->automated ? 'checked' : null }}>
                <label for="automated-{{ $combatant->id }}" class="form-check-label">
                    Automate all of {{ $borrowed ? $combatant->name . "'s" : 'my' }} turns
                </label>
            </div>
        </div>
        <div class="col-sm-3">
            <button class="btn btn-primary">Update</button>
        </div>
    </div>
</form>
