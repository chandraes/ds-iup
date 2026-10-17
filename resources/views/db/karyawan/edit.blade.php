@extends('layouts.app')
@section('content')
@include('db.karyawan.form', ['employee' => $data, 'editing' => true])
@endsection
