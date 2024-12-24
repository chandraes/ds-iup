@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PENGATURAN HOLDING</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-4">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pengaturan')}}"><img src="{{asset('images/pengaturan.svg')}}" alt="dokumen"
                                width="30"> Pengaturan</a></td>
                </tr>
            </table>
        </div>
    </div>
    <form action="{{route('pengaturan.holding.store')}}" method="post">
        @csrf
        <div class="row mt-3">
            <div class="col-md-2 mb-3">
                <label class="form-label" for="flexSwitchCheckDefault">Holding:</label> <br>
                <input name="status" class="form-control" type="checkbox" @if ($data && $data->status == 1)
                checked
                @endif data-toggle="toggle"
                    data-onlabel="Aktif" data-offlabel="Tidak" data-onstyle="success" data-offstyle="danger"
                    onchange="checkState()">
            </div>
            <div class="col-md-3 mb-3">
                <label for="holding_url" class="form-label">URL Aplikasi Holding</label>
                <input type="text" class="form-control" name="holding_url" id="holding_url" aria-describedby="helpId"
                    placeholder="" @if (!$data)
                    disabled @else value="{{$data->holding_url}}"
                    @endif />
            </div>
            <div class="col-md-4 mb-3">
                <label for="token" class="form-label">Token Holding</label>
                <input type="text" class="form-control" name="token" id="token" aria-describedby="helpId"
                    placeholder="token" @if (!$data)
                    disabled @else value="{{$data->token}}"
                    @endif/>
            </div>
            <div class="col-md-3 mb-3" @if (!$data) hidden @endif >
                {{-- fetch status connect to holding url --}}
                <label for="status" class="form-label">Status Koneksi</label>
                <div class="d-flex align-items-center">
                    <div id="status-indicator" class="indicator"></div>
                    <input type="text" class="form-control ms-2" name="status_indicator" id="status_indicator" aria-describedby="helpId" placeholder="status" disabled/>
                </div>
            </div>
        </div>
        <div class="row mt-2 justify-content-center p-3">
            <div class="col-md-6">
                <div class="row">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('css')
<style>
    .indicator {
        width: 20px;
        height: 20px;
        border-radius: 100%;
        background-color: grey; /* Default color */
    }
    .indicator.connected {
        background-color: green;
    }
    .indicator.disconnected {
        background-color: red;
    }
</style>
@endpush
@push('js')
<script>
    confirmAndSubmit('#editForm', "Apakah anda yakin?");

    function checkState()
    {
        let status = document.querySelector('input[name="status"]').checked;
        let holding_url = document.querySelector('input[name="holding_url"]');
        let token = document.querySelector('input[name="token"]');
        let statusConnect = document.querySelector('input[name="status"]');

        if(status){
            holding_url.removeAttribute('disabled');
            token.removeAttribute('disabled');
            statusConnect.removeAttribute('disabled');
        }else{
            holding_url.setAttribute('disabled', 'disabled');
            token.setAttribute('disabled', 'disabled');
            statusConnect.setAttribute('disabled', 'disabled');
        }
    }

    $(document).ready(function() {
        // Function to fetch status
        function fetchStatus() {
            $.ajax({
                url: '{{route('holding.check_connection')}}', // Ganti dengan endpoint yang sesuai
                method: 'GET',
                success: function(response) {
                    // Update status text
                    console.log(response);
                    $('#status_indicator').val(response.message);

                    // Update indicator color
                    if (response.status === 'success') {
                        $('#status-indicator').removeClass('disconnected').addClass('connected');
                    } else {
                        $('#status-indicator').removeClass('connected').addClass('disconnected');
                    }
                },
                error: function() {
                    $('#status_indicator').val('Error fetching status');
                    $('#status-indicator').removeClass('connected').addClass('disconnected');
                }
            });
        }

        // Fetch status on page load
        fetchStatus();
    });
</script>
@endpush
