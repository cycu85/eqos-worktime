<x-app-layout>
    <div>
        @foreach($paginator as $row)
            <div>
                {{ $row['user_name'] }}
                {{ \Carbon\Carbon::parse($row['work_date'])->format('d.m.Y') }}
            </div>
        @endforeach
    </div>
</x-app-layout>
