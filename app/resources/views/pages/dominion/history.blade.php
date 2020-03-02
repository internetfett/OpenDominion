@extends('layouts.master')

@section('page-header', 'History')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-book"></i> History</h3>
                </div>
                <div class="box-body no-padding">
                    <div class="row">

                      <div class="box-body table-responsive no-padding">
                          <table class="table">
                              <colgroup>
                                  <col width="150">
                                  <col width="150">
                                  <col>
                              </colgroup>
                              <thead>
                                  <tr>
                                      <th>Date</th>
                                      <th>Action</th>
                                      <th>Details</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  @foreach ($history as $event)
                                      <tr>
                                          <td>
                                              {{ $event->created_at }}
                                          </td>
                                          <td>
                                                <i class="{{ $historyHelper->getEventIcon($event->event) }}"></i> {{ $historyHelper->getEventName($event->event) }}</a>
                                          </td>
                                          <td>
                                              {{ $event->delta }}

                                              <ul>
                                              @foreach(json_decode($event->delta, TRUE) as $data => $delta)
                                                <li>{{ $data }}: {{ $delta }}</li>
                                              @endforeach
                                              </ul>
                                          </td>
                                      </tr>
                                  @endforeach
                              </tbody>
                          </table>
                      </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>This is your history.</p>
                </div>
            </div>
        </div>

    </div>
@endsection
