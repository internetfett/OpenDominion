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
        <br>
    </div>

    @if (!isset($selectedDominion))
    <i class="fa fa-file-text-o"></i> <a href="{{ route('legal.privacypolicy') }}">Privacy Policy</a> | <a href="{{ route('legal.termsandconditions') }}">Terms and Conditions</a>
    @endif

    <a href="https://www.youtube.com/channel/UCGR9htOHUFzIfiPUsZapHhw" target="_blank" style="border:none; text-decoration:none;">
    <img src="https://68ef2f69c7787d4078ac-7864ae55ba174c40683f10ab811d9167.ssl.cf1.rackcdn.com/youtube-icon_24x24.png" width="24" height="24" style="filter: gray; -webkit-filter: grayscale(1); filter: grayscale(1);" />
    </a>

    <a href="https://www.facebook.com/odarenagame/" target="_blank" style="border:none; text-decoration:none;">
    <img src="https://68ef2f69c7787d4078ac-7864ae55ba174c40683f10ab811d9167.ssl.cf1.rackcdn.com/facebook-icon_24x24.png" width="24" height="24" style="filter: gray; -webkit-filter: grayscale(1); filter: grayscale(1);" />
    </a>

    <a href="https://twitter.com/OD_Arena" target="_blank" style="border:none; text-decoration:none;">
    <img src="https://68ef2f69c7787d4078ac-7864ae55ba174c40683f10ab811d9167.ssl.cf1.rackcdn.com/twitter-icon_24x24.png" width="24" height="24" style="filter: gray; -webkit-filter: grayscale(1); filter: grayscale(1);" />
    </a>

    <a href="https://instagram.com/OD_Arena" target="_blank" style="border:none; text-decoration:none;">
    <img src="https://68ef2f69c7787d4078ac-7864ae55ba174c40683f10ab811d9167.ssl.cf1.rackcdn.com/instagram-icon_24x24.png" width="24" height="24" style="filter: gray; -webkit-filter: grayscale(1); filter: grayscale(1);" />
    </a>

</footer>
