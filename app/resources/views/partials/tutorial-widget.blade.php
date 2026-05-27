@php
    $tutorialUser = auth()->user();
    $tutorialProgress = $tutorialUser?->tutorial_progress;
@endphp
@if ($tutorialUser && empty($tutorialProgress['done']) && isset($selectedDominion))
    @php
        $tutorial = app(\OpenDominion\Services\User\TutorialService::class)
            ->evaluateAndSync($tutorialUser, $selectedDominion);
    @endphp
    @if ($tutorial['current'] !== null)
        @php($step = $tutorial['current'])
        <div class="alert alert-warning small border-0 rounded-0 mb-0">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start">
                            <i class="ra ra-scroll-unfurled fa-lg me-2 mt-1"></i>
                            <div>
                                <div class="fw-bold">{{ $step['title'] }}</div>
                                <div class="text-muted">{{ $step['description'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        @if ($step['id'] === 'choose_branch')
                            <form method="post" action="{{ route('dominion.tutorial.branch', 'explorer') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="ra ra-compass"></i> Explorer (Recommended)
                                </button>
                            </form>
                            <form method="post" action="{{ route('dominion.tutorial.branch', 'attacker') }}" class="d-inline ms-1">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="ra ra-crossed-swords"></i> Attacker
                                </button>
                            </form>
                        @else
                            @if (!empty($step['action_route']))
                                <a href="{{ route($step['action_route']) }}" class="btn btn-sm btn-primary">
                                    Go &raquo;
                                </a>
                            @endif
                            @if (!empty($step['manual_complete']))
                                <form method="post" action="{{ route('dominion.tutorial.complete', $step['id']) }}" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fa fa-check"></i> Got it
                                    </button>
                                </form>
                            @endif
                            @if (!empty($step['skippable']))
                                <form method="post" action="{{ route('dominion.tutorial.skip', $step['id']) }}" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-muted">
                                        Skip
                                    </button>
                                </form>
                            @endif
                        @endif
                        <div class="small text-muted mt-1">
                            <a href="#tutorial-widget-details" data-bs-toggle="collapse" role="button"
                               aria-expanded="false" aria-controls="tutorial-widget-details">
                                Tutorial progress: <b>{{ $tutorial['progress']['completed'] }} of {{ $tutorial['progress']['total'] }}</b>
                            </a>
                        </div>
                    </div>
                </div>
                <div id="tutorial-widget-details" class="collapse mt-2">
                    <div class="row">
                        @if (!empty($tutorial['completed']))
                            <div class="col-md-4">
                                <div class="small fw-bold text-success">Completed</div>
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($tutorial['completed'] as $done)
                                        <li><i class="fa fa-check text-success"></i> {{ $done['title'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (!empty($tutorial['upcoming']))
                            <div class="col-md-4">
                                <div class="small fw-bold text-muted">Upcoming</div>
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($tutorial['upcoming'] as $up)
                                        <li class="text-muted"><i class="fa fa-circle-o"></i> {{ $up['title'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (!empty($tutorial['skipped']) || !empty($tutorial['not_applicable']))
                            <div class="col-md-4">
                                <div class="small fw-bold text-muted">Skipped / N/A</div>
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($tutorial['skipped'] as $sk)
                                        <li class="text-muted"><i class="fa fa-times"></i> {{ $sk['title'] }}</li>
                                    @endforeach
                                    @foreach ($tutorial['not_applicable'] as $na)
                                        <li class="text-muted">
                                            <i class="fa fa-ban"></i> {{ $na['title'] }}
                                            @if (!empty($na['not_applicable_reason']))
                                                <span class="d-block ms-3 fst-italic">{{ $na['not_applicable_reason'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
