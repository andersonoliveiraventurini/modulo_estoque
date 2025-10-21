@if (session('success'))
<div class="mb-4 rounded-lg bg-green-100 text-green-800 p-4 shadow">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="mb-4 rounded-lg bg-red-100 text-red-800 p-4 shadow">
    <ul class="list-disc pl-4">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


@if (session('warning'))
<div class="mb-4 rounded-lg bg-yellow-100 text-yellow-800 p-4 shadow">
    {{ session('warning') }}
</div>
@endif