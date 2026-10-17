@extends('layouts.app')
@section('content')
@include('db.karyawan.form', ['employee' => new \App\Models\db\Karyawan, 'editing' => false])
@endsection
