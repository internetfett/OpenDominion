<footer class="main-footer">

    <div class="pull-right">
        @if (isset($selectedDominion) && ($selectedDominion->round->isActive()))
            @php
            $diff = $selectedDominion->round->start_date->subDays(1)->diff(now());
            $roundDay = $selectedDominion->round->start_date->subDays(1)->diffInDays(now());
            $roundDurationInDays = $selectedDominion->round->durationInDays();
            $currentHour = ($diff->h + 1);
            $currentTick = 1+floor(intval(Date('i')) / 15);

            echo "Day <strong>{$roundDay}</strong>/{$roundDurationInDays}, hour <strong>{$currentHour}</strong>, tick <strong>{$currentTick}</strong>.";

            @endphp
        @endif
        <span class="hidden-xs">Version: </span>{!! $version !!}
    </div>

    <i class="fa fa-github"></i> <span class="hidden-xs">View this project on </span><a href="https://github.com/Dr-Eki/OpenDominion" target="_blank">GitHub <i class="fa fa-external-link"></i></a>
     |
    <i class="fa fa-file-text-o"></i> <span class="hidden-xs">View this project on </span><a href="{{ route('legal.privacypolicy') }}" target="_blank">Privacy Policy</a>
     |
    <i class="fa fa-file-text-o"></i> <span class="hidden-xs">View this project on </span><a href="{{ route('legal.termsandconditions') }}" target="_blank">Terms and Conditions</a>


</footer>
