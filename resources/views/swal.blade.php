@if (session('success'))
<script>
    Swal.fire(
            'Berhasil!',
            '{{session('success')}}',
            'success'
        )
</script>
@endif
@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '{{session('error')}}',
    })
</script>
@endif
@if ($errors->any())
@php
    $message='';
    foreach ($errors->all() as $error){
        $message .= $error;
    }
@endphp
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '{{$message}}',
    })
</script>
@endif
