@extends('layouts.master')

@section('page-header', "Register to Round {$round->number} of OD Arena")

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Round #{{ $round->number }} &mdash; <strong>{{ $round->name }}</strong></h3>
        </div>
        <form action="{{ route('round.register', $round) }}" method="post" class="form-horizontal" role="form">
            @csrf

            <div class="box-body">

                <!-- Dominion Name -->
                <div class="form-group">
                    <label for="dominion_name" class="col-sm-3 control-label">Dominion Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="dominion_name" id="dominion_name" class="form-control" placeholder="Dominion Name" value="{{ old('dominion_name') }}" required autofocus>
                        <p class="help-block">Your dominion name is shown when viewing and interacting with other players.</p>
                    </div>
                </div>

                <!-- Ruler Name -->
                <div class="form-group">
                    <label for="ruler_name" class="col-sm-3 control-label">Ruler Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="ruler_name" id="ruler_name" class="form-control" placeholder="{{ Auth::user()->display_name }}" value="{{ old('ruler_name') }}">
                        <p class="help-block">This is your personal alias in the round which will be shown to your realmies. Defaults to your display name '{{ Auth::user()->display_name }}' if omitted.</p>
                    </div>
                </div>

                <!-- Race -->
                <div class="form-group">
                    <label for="race" class="col-sm-3 control-label">Faction</label>
                    <div class="col-sm-9">
                        <div class="row">

                            <div class="col-xs-12">
                                <div class="text-center">
                                    <h2>The Commonwealth</h2>
                                </div>
                                <div class="row">
                                    @foreach ($races->filter(function ($race) { return $race->playable && $race->alignment === 'good'; }) as $race)
                                        <div class="col-xs-12">
                                            <label class="btn btn-block" style="border: 1px solid #d2d6de; margin: 5px 0px; white-space: normal;">
                                                <div class="row text-left">
                                                    <div class="col-lg-4">
                                                        <p>
                                                            <input type="radio" name="race" value="{{ $race->id }}" autocomplete="off" {{ (old('race') == $race->id) ? 'checked' : null }} required>
                                                            <strong>{{ $race->name }}</strong>
                                                            &nbsp;&mdash;&nbsp;
                                                        <a href="{{ route('scribes.race', $race->name) }}">Scribes</a>
                                                      </p>
                                                    </div>

                                                    <div class="col-sm-4">
                                                      <ul>
                                                        <li>Attacking: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->attacking)))) }}</li>
                                                        <li>Converting: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->converting)))) }}</li>
                                                        <li>Exploring: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->exploring)))) }}</li>
                                                      </ul>
                                                    </div>

                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-xs-12">
                                <div class="text-center">
                                    <h2>The Empire</h2>
                                </div>
                                <div class="row">
                                    @foreach ($races->filter(function ($race) { return $race->playable && $race->alignment === 'evil'; }) as $race)
                                    <div class="col-xs-12">
                                        <label class="btn btn-block" style="border: 1px solid #d2d6de; margin: 5px 0px; white-space: normal;">
                                            <div class="row text-left">
                                                <div class="col-lg-4">
                                                    <p>
                                                        <input type="radio" name="race" value="{{ $race->id }}" autocomplete="off" {{ (old('race') == $race->id) ? 'checked' : null }} required>
                                                        <strong>{{ $race->name }}</strong>
                                                        &nbsp;&mdash;&nbsp;
                                                    <a href="{{ route('scribes.race', $race->name) }}">Scribes</a>
                                                  </p>
                                                </div>

                                                <div class="col-sm-4">
                                                  <ul>
                                                    <li>Attacking: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->attacking)))) }}</li>
                                                    <li>Converting: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->converting)))) }}</li>
                                                    <li>Exploring: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->exploring)))) }}</li>
                                                  </ul>
                                                </div>

                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            @if(stristr(Auth::user()->email, '@lykanthropos.com'))
                            <div class="col-xs-12">
                                <div class="text-center">
                                    <h2>NPC</h2>
                                </div>
                                <div class="row">
                                    @foreach ($races->filter(function ($race) { return $race->alignment === 'npc'; }) as $race)
                                    <div class="col-xs-12">
                                        <label class="btn btn-block" style="border: 1px solid #d2d6de; margin: 5px 0px; white-space: normal;">
                                            <div class="row text-left">
                                                <div class="col-lg-4">
                                                    <p>
                                                        <input type="radio" name="race" value="{{ $race->id }}" autocomplete="off" {{ (old('race') == $race->id) ? 'checked' : null }} required>
                                                        <strong>{{ $race->name }}</strong>
                                                        &nbsp;&mdash;&nbsp;
                                                    <a href="{{ route('scribes.race', $race->name) }}">Scribes</a>
                                                  </p>
                                                </div>

                                                <div class="col-sm-4">
                                                  <ul>
                                                    <li>Attacking: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->attacking)))) }}</li>
                                                    <li>Converting: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->converting)))) }}</li>
                                                    <li>Exploring: {{ str_replace('0','Unplayable',str_replace(1,'Difficult',str_replace(2,'Challenging', str_replace(3,'Apt',$race->exploring)))) }}</li>
                                                  </ul>
                                                </div>

                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif




                        </div>
                    </div>
                </div>

                <!-- Realm -->
                <div class="form-group" style="display: none;">
                    <label for="realm" class="col-sm-3 control-label">Realm</label>
                    <div class="col-sm-9">
                        <select name="realm_type" id="realm_type" class="form-control" required>
                            <option value="random" {{ (old('realm_type') === 'random') ? 'selected' : null }}>Put me in a random realm</option>
                            <option value="join_pack" {{ (old('realm_type') === 'join_pack') ? 'selected' : null }}>Join an existing pack</option>
                            <option value="create_pack" {{ (old('realm_type') === 'create_pack') ? 'selected' : null }}>Create a new pack</option>
                        </select>
                    </div>
                </div>

                <!-- Pack Name -->
                <div class="form-group create-pack-only join-pack-only" style="display: none;">
                    <label for="pack_name" class="col-sm-3 control-label">Pack Name</label>
                    <div class="col-sm-9">
                        <input type="text" name="pack_name" id="pack_name" class="form-control" placeholder="Pack Name" value="{{ old('pack_name') }}">
                        <p class="help-block create-pack-only">This is the name of your pack. This will be recorded and will eventually be shown in Valhalla.</p>
                        <p class="help-block join-pack-only">You need the pack name and password from the player whose pack you want to join.</p>
                    </div>
                </div>

                <!-- Pack Password -->
                <div class="form-group create-pack-only join-pack-only" style="display: none;">
                    <label for="pack_password" class="col-sm-3 control-label">Pack Password</label>
                    <div class="col-sm-9">
                        <input type="text" name="pack_password" id="pack_password" class="form-control" placeholder="Pack Password" value="{{ old('pack_password') }}">
                        <p class="help-block create-pack-only">Your packies need both your pack name and pack password in order to join.</p>
                    </div>
                </div>

                <!-- Pack Size (create only) -->
                <div class="form-group create-pack-only" style="display: none;">
                    <label for="pack_size" class="col-sm-3 control-label">Pack Size</label>
                    <div class="col-sm-9">
                        <select name="pack_size" id="pack_size" class="form-control">
                            @for ($i = 2; $i <= $round->pack_size; $i++)
                                <option value="{{ $i }}" {{ (old('pack_size') == $i) ? 'selected' : null }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <p class="help-block">The amount of players that will be in your pack (including yourself).</p>
                    </div>
                </div>

                {{-- Terms and Conditions --}}
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="terms" required> I agree to the <a href="{{ route('legal.termsandconditions') }}">Terms and Conditions</a>
                            </label>
                        </div>
                    </div>
                </div>


                {{-- Notice --}}
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">

                      @if($round->hasStarted())
                      <p>This round has already started and ends {{ $round->end_date->format('l, jS \o\f F Y \a\t H:i') }}.</p>
                      <p>Protection lasts seven hours and starts immediately when you click the Register button.</p>
                      <p>To help you get going, you will get 0.5% extra starting resources for every hour since the round started.</p>

                        @if ($discordInviteLink = config('app.discord_invite_link'))
                        <p>If you need any help or just want to chat, come join us on <a href="{{ $discordInviteLink }}" target="_blank">Discord</a>.</p>
                        @endif

                      @else
                      <p>The round starts on {{ $round->start_date->format('l, jS \o\f F Y \a\t H:i') }}.</p>
                      <p>Protection lasts seven hours.</p>
                      <p>If 00:00 to 07:00 UTC are not convenient hours for you, consider registering a little later.</p>

                        @if ($discordInviteLink = config('app.discord_invite_link'))
                        <p>In the meantime, come join us on <a href="{{ $discordInviteLink }}" target="_blank">Discord</a>.</p>
                        @endif

                      @endif
                      </p>
                    </div>
                </div>

            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>

        </form>
    </div>
@endsection

@push('inline-scripts')
    <script type="text/javascript">
        (function ($) {
            var realmTypeEl = $('#realm_type');
            var createPackOnlyEls = $('.create-pack-only');
            var joinPackOnlyEls = $('.join-pack-only');

            function updatePackInputs() {
                var realmTypeOption = realmTypeEl.find(':selected');

                if (realmTypeOption.val() === 'join_pack') {
                    createPackOnlyEls.hide();
                    joinPackOnlyEls.show();

                } else if (realmTypeOption.val() === 'create_pack') {
                    joinPackOnlyEls.hide();
                    createPackOnlyEls.show();

                } else {
                    createPackOnlyEls.hide();
                    joinPackOnlyEls.hide();
                }
            }

            realmTypeEl.on('change', updatePackInputs);

            updatePackInputs();
        })(jQuery);
    </script>
@endpush
