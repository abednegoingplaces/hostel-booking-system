{{-- Semester dropdown. Pass $semester (selected) and optionally $allowAll. --}}
<div>
    <label for="semester">Semester</label>
    <select id="semester" name="semester">
        @if (! empty($allowAll))
            <option value="">All semesters</option>
        @endif
        @foreach (config('hostel.semesters') as $s)
            <option value="{{ $s }}" @selected($s === $semester)>{{ $s }}</option>
        @endforeach
    </select>
</div>
