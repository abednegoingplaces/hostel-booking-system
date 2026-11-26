<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="brand" href="{{ route('home') }}">Hostel Booking</a>
        <nav>
            <a href="{{ route('hostels.index') }}" @class(['active' => request()->routeIs('hostels.*')])>Hostels</a>
            <a href="{{ route('rooms.index') }}" @class(['active' => request()->routeIs('rooms.*')])>Rooms</a>
            @auth
                @if (auth()->user()->isStudent())
                    <a href="{{ route('bookings.index') }}" @class(['active' => request()->routeIs('bookings.*')])>My bookings</a>
                @else
                    <a href="{{ route('warden.bookings.index') }}" @class(['active' => request()->routeIs('warden.bookings.*')])>Bookings</a>
                    <a href="{{ route('warden.hostels.index') }}" @class(['active' => request()->routeIs('warden.hostels.*')])>Manage hostels</a>
                    <a href="{{ route('warden.rooms.index') }}" @class(['active' => request()->routeIs('warden.rooms.*')])>Manage rooms</a>
                @endif
            @endauth
        </nav>
        <div class="account">
            @auth
                <span class="muted">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="link">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}" class="button small">Register</a>
            @endauth
        </div>
    </div>
</header>
