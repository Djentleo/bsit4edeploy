@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Incident Reports</h1>
        <div class="flex space-x-2">
            <input type="text" placeholder="Search incidents..." class="border rounded px-4 py-2" wire:model="search">
            <button class="bg-gray-200 px-4 py-2 rounded">Filter/Sort</button>
            <button class="bg-blue-500 text-white px-4 py-2 rounded">Export</button>
        </div>
    </div>

    <table class="table-auto w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-4 py-2">#</th>
                <th class="border border-gray-300 px-4 py-2">Incident Type</th>
                <th class="border border-gray-300 px-4 py-2">Location</th>
                <th class="border border-gray-300 px-4 py-2">Time</th>
                <th class="border border-gray-300 px-4 py-2">Source</th>
                <th class="border border-gray-300 px-4 py-2">Status</th>
                <th class="border border-gray-300 px-4 py-2">Severity</th>
                <th class="border border-gray-300 px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $incident)
            <tr>
                <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $incident['type'] }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $incident['location'] }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $incident['timestamp'] }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $incident['source'] }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $incident['status'] }}</td>
                <td class="border border-gray-300 px-4 py-2">{{ $incident['severity'] }}</td>
                <td class="border border-gray-300 px-4 py-2"><a href="#" class="text-blue-500">Details</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
