@extends('layouts.app')

@section('title', $page_title)

@section('js_deps')

    <script type="text/javascript">
        js_deps.show([]);
    </script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            let tabElements = document.querySelectorAll('[data-toggle="tab"]');
            tabElements.forEach(function (item, index) {
                item.addEventListener('click', function (e) {
                    tabElements.forEach(function (item) {
                        item.classList.remove('active');
                        document.querySelector(`#${item.getAttribute('data-child')}`).classList.remove('show', 'active')
                    });
                    this.classList.add('active');
                    document.querySelector(`#${this.getAttribute('data-child')}`).classList.add('show', 'active')
                });
            })
        })

    </script>

@endsection

@section('content')
    <div class="contacts bg-white py-5">
        <div class="container">
            @if(is_array($info))
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tracking number</th>
                        <th scope="col">Tracking website</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($info as $i)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $i['number'] }}</td>
                            <td><a target="_blank" href="{{ $i['link'] }}">{{ $i['link'] }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            @elseif(is_string($info))
                <div class="alert alert-warning" role="alert">
                    {{ $info }}
                </div>
            @endif
        </div>
        <div class="container py-5">
            <div class="contacts__wrapper">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link " data-toggle="tab" data-child="parcel" href="javascript:void(0)">
                            Where is my parcel?
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" data-child="number" href="javascript:void(0)">
                            Where is my tracking number?
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" data-child="other" href="javascript:void(0)">
                            Others
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade" id="parcel">
                        <form action="" method="get">
                            <div class="input-group mt-5 mb-3">
                                <input type="text" name="search" class="form-control"
                                       placeholder="write tracking number or mail"
                                       aria-label="tracking number or mail" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="number">
                        <form action="" method="get">
                            <div class="input-group mt-5 mb-3">
                                <input type="text" name="search" class="form-control"
                                       placeholder="where is my tracking number"
                                       aria-label="number" aria-describedby="basic-addon2">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="other">
                        <a href="{{ route('contact-us') }}">Contact us </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
