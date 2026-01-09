@extends('layouts.master')

@section('page-header', 'Valuables History')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-scroll-unfurled"></i> Valuables History</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    @if ($valuablesHistory->isEmpty())
                        <div class="box-body">
                            <p class="text-center text-muted">
                                No completed valuables yet. Successfully steal valuables from your targets to see them here.
                            </p>
                        </div>
                    @else
                        <table class="table table-striped">
                            <colgroup>
                                <col width="150">
                                <col width="200">
                                <col>
                                <col width="150">
                                <col width="100">
                                <col width="150">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Completed</th>
                                    <th>Valuable</th>
                                    <th>Target</th>
                                    <th>Result</th>
                                    <th>Sale Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($valuablesHistory as $valuable)
                                    @php
                                        $wasSuccessful = $valuable->success;
                                        $wasSold = $valuable->sold_at !== null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span title="{{ $valuable->completed_at }}">
                                                {{ $valuable->completed_at->format('M j, g:ia') }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $valuable->completed_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            <strong>{{ $valuable->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ ucfirst($valuable->rarity) }} {{ ucfirst($valuable->type) }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ $valuable->targetDominion->name }}
                                            <br>
                                            <small class="text-muted">
                                                (#{{ $valuable->targetDominion->realm->number }})
                                            </small>
                                        </td>
                                        <td>
                                            @if ($wasSuccessful)
                                                <span class="text-green">
                                                    <i class="fa fa-check"></i> <strong>Success</strong>
                                                </span>
                                            @else
                                                <span class="text-red">
                                                    <i class="fa fa-times"></i> <strong>Failed</strong>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($wasSold)
                                                <span class="text-green">
                                                    <strong>{{ number_format($valuable->platinum_received) }}</strong>
                                                </span>
                                                <br>
                                                <small class="text-muted">platinum</small>
                                            @elseif ($wasSuccessful)
                                                <span class="text-muted">Not sold</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($wasSold)
                                                <span class="label label-success">Sold</span>
                                            @elseif ($wasSuccessful)
                                                <span class="label label-warning">Expired</span>
                                            @else
                                                <span class="label label-danger">Theft Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="box-footer">
                    <a href="{{ route('dominion.espionage') }}" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to Espionage
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Information</h3>
                </div>
                <div class="box-body">
                    <p>This page shows all completed valuable heists from this round.</p>

                    <hr>

                    <h4>Summary</h4>
                    @php
                        $totalAttempts = $valuablesHistory->count();
                        $successfulThefts = $valuablesHistory->where('success', true)->count();
                        $failedThefts = $totalAttempts - $successfulThefts;
                        $soldValuables = $valuablesHistory->whereNotNull('sold_at')->count();
                        $expiredValuables = $valuablesHistory->where('success', true)->whereNull('sold_at')->count();
                        $totalPlatinum = $valuablesHistory->whereNotNull('platinum_received')->sum('platinum_received');
                    @endphp

                    <dl>
                        <dt>Total Attempts</dt>
                        <dd>{{ number_format($totalAttempts) }}</dd>

                        <dt>Successful Thefts</dt>
                        <dd class="text-green">{{ number_format($successfulThefts) }}</dd>

                        <dt>Failed Thefts</dt>
                        <dd class="text-red">{{ number_format($failedThefts) }}</dd>

                        <dt>Valuables Sold</dt>
                        <dd class="text-green">{{ number_format($soldValuables) }}</dd>

                        <dt>Valuables Expired</dt>
                        <dd class="text-warning">{{ number_format($expiredValuables) }}</dd>

                        <dt>Total Platinum Earned</dt>
                        <dd class="text-green"><strong>{{ number_format($totalPlatinum) }}</strong></dd>
                    </dl>

                    @if ($successfulThefts > 0)
                        <hr>
                        <p class="text-muted small">
                            <i class="fa fa-lightbulb-o"></i>
                            Success rate: <strong>{{ number_format(($successfulThefts / $totalAttempts) * 100, 1) }}%</strong>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
