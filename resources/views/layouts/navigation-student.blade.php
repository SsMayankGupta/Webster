<nav class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
    <div class="max-w-md mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ route('student.dashboard') }}" class="font-semibold text-emerald-600">ZeroWasteMess</a>
        <form method="POST" action="{{ route('student.logout') }}">
            @csrf
            <button class="text-sm text-slate-500">Logout</button>
        </form>
    </div>
    <div class="max-w-md mx-auto px-4 pb-2 flex gap-2 overflow-x-auto text-xs">
        <a href="{{ route('student.dashboard') }}" class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">Home</a>
        <a href="{{ route('student.menus') }}" class="px-3 py-1 rounded-full bg-slate-100">Menus</a>
        <a href="{{ route('student.qr') }}" class="px-3 py-1 rounded-full bg-slate-100">QR</a>
        <a href="{{ route('student.leave.index') }}" class="px-3 py-1 rounded-full bg-slate-100">Leave</a>
        <a href="{{ route('student.tickets.index') }}" class="px-3 py-1 rounded-full bg-slate-100">Tickets</a>
        <a href="{{ route('student.health') }}" class="px-3 py-1 rounded-full bg-slate-100">Health</a>
    </div>
</nav>
