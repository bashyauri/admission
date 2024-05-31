@if ($message = Session::get('success'))

{{-- <x-adminlte-alert theme="success" title="Success" dismissable>
    <strong>{{ $message }}</strong>!
</x-adminlte-alert> --}}
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '{{ session('success') }}',
});
</script>



@endif

@if ($message = Session::get('error'))

{{-- <x-adminlte-alert theme="danger" title="Error" dismissable>
    <strong>{{ $message }}</strong>!
</x-adminlte-alert> --}}
<script>
    Swal.fire({
    icon: 'error',
    title: 'Oops...',
    text: '{{ session('error') }}',
    footer: '<a href="#">Why do I have this issue?</a>'
});

</script>


@endif



@if ($message = Session::get('warning'))

{{-- <x-adminlte-alert theme="warning" title="Warning" dismissable>
    <strong>{{ $message }}</strong>!
</x-adminlte-alert> --}}

@endif



@if ($message = Session::get('info'))
{{-- <x-adminlte-alert theme="info" title="Success" dismissable>
    <strong>{{ $message }}</strong>
</x-adminlte-alert> --}}

@endif
