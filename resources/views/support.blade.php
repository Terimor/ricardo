@extends('layouts.app')

@section('title', $page_title)

@section('js_deps')

    <script type="text/javascript">
        js_deps.show([]);
    </script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            const tabElements = document.querySelectorAll('[data-toggle="tab"]');
            tabElements.forEach(function (item, index) {
                item.addEventListener('click', function (e) {
                    tabElements.forEach(function (item) {
                        item.classList.remove('active');
                        document.querySelector(`#${item.getAttribute('data-child')}`).classList.remove('show', 'active')
                    });
                    this.classList.add('active');
                    document.querySelector(`#${this.getAttribute('data-child')}`).classList.add('show', 'active')
                });
            });
            const form = document.querySelectorAll('form');
            form.forEach(function (item) {
                item.addEventListener('submit',sendRequest)
            });
        });
        function sendRequest(event) {
            event.preventDefault();
            fetch('{{ route("support-request") }}',{
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({'search':this.querySelector('[name="search"]').value})
            }).then( response => {
                if(response.status >= 200 && response.status < 300) {
                    response.text().then(data=>{
                        document.querySelector('#response_data').innerHTML = data;
                    })
                }

            } );
        }

    </script>

@endsection

@section('content')
    <div class="contacts bg-white py-5">
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
                        <form action="" method="get" >
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
                        <form action="" method="get" >
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
        <div class="container" id="response_data"></div>
    </div>
@endsection
