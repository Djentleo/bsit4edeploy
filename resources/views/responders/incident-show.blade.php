@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h2 class="font-semibold text-2xl dark:text-white text-gray-900 tracking-tight mb-4">Incident Details</h2>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="mb-2"><strong>ID:</strong> {{ $incident->id }}</div>
        <div class="mb-2"><strong>Type:</strong> {{ $incident->type }}</div>
        <div class="mb-2"><strong>Location:</strong> {{ $incident->location }}</div>
        <div class="mb-2"><strong>Status:</strong> {{ $incident->status }}</div>
        <div class="mb-2"><strong>Reported By:</strong> {{ $incident->reporter_name }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $incident->incident_description }}</div>
        <div class="mb-2"><strong>Priority:</strong> {{ $incident->priority }}</div>
        <div class="mb-2"><strong>Severity:</strong> {{ $incident->severity }}</div>
        <div class="mb-2"><strong>Created At:</strong> {{ $incident->created_at }}</div>
    </div>
</div>
@endsection
