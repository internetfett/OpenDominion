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

    <div class="pull-right">
      <a href="https://www.youtube.com/channel/UCGR9htOHUFzIfiPUsZapHhw" target="_blank" style="border:none; text-decoration:none;">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAIklEQVRIiWP4T2PAMGrBqAWjFoxaMGrBqAWjFoxaMDQsAACAo/d5It9lkgAAAABJRU5ErkJggg==" width="24" height="24" />
      </a>

      <a href="https://www.facebook.com/odarenagame/" target="_blank" style="border:none; text-decoration:none;">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAIklEQVRIiWP4T2PAMGrBqAWjFoxaMGrBqAWjFoxaMDQsAACAo/d5It9lkgAAAABJRU5ErkJggg==" width="24" height="24" />
      </a>

      <a href="https://instagram.com/OD_Arena" target="_blank" style="border:none; text-decoration:none;">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAIklEQVRIiWP4T2PAMGrBqAWjFoxaMGrBqAWjFoxaMDQsAACAo/d5It9lkgAAAABJRU5ErkJggg==" width="24" height="24" />
      </a>

      <a href="https://twitter.com/OD_Arena" target="_blank" style="border:none; text-decoration:none;">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAIklEQVRIiWP4T2PAMGrBqAWjFoxaMGrBqAWjFoxaMDQsAACAo/d5It9lkgAAAABJRU5ErkJggg==" width="24" height="24" />
      </a>

    </div>

    @if (!isset($selectedDominion))
    <i class="fa fa-file-text-o"></i> <a href="{{ route('legal.privacypolicy') }}">Privacy Policy</a> | <a href="{{ route('legal.termsandconditions') }}">Terms and Conditions</a>
    @endif



</footer>
