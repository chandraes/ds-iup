@if(session('success') || session('error') || $errors->any())
<script>document.addEventListener('DOMContentLoaded',function(){Swal.fire({icon:{{ Illuminate\Support\Js::from(session('success')?'success':'error') }},title:{{ Illuminate\Support\Js::from(session('success')?'Berhasil':'Periksa kembali') }},text:{{ Illuminate\Support\Js::from(session('success') ?? session('error') ?? implode("\n",$errors->all())) }}});});</script>
@endif
