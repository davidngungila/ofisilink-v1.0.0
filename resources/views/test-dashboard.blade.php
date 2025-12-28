@extends('layouts.app')

@section('title', 'Test Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h1>Test Dashboard</h1>
                    <p>This is a minimal test dashboard to check if the infinite loading issue is resolved.</p>
                    <p>Current time: {{ now() }}</p>
                    <p>User: {{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
console.log('Test dashboard script loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('Test dashboard DOM loaded');
    console.log('Test dashboard initialization complete');
});
</script>
@endpush
