@foreach (['success', 'error', 'info'] as $type)
    @if (session($type))
        <p class="flash {{ $type }}">{{ session($type) }}</p>
    @endif
@endforeach

{{-- Booking errors come back to the rooms page, which has no form fields to show them next to. --}}
@foreach (['room_id', 'semester'] as $field)
    @error($field)
        <p class="flash error">{{ $message }}</p>
    @enderror
@endforeach
