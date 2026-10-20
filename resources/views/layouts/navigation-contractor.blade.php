<aside class="w-64 bg-slate-900 text-slate-100 min-h-screen p-4 space-y-2">
    <div class="text-lg font-semibold text-emerald-400 mb-4">Contractor</div>
    <a href="{{ route('contractor.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
    <a href="{{ route('contractor.menu-builder') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Menu Builder</a>
    <a href="{{ route('contractor.headcount') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Headcount</a>
    <a href="{{ route('contractor.scanner') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Scanner</a>
    <a href="{{ route('contractor.tickets.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Tickets</a>
    <a href="{{ route('contractor.waste.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Waste</a>
    <a href="{{ route('contractor.leaves.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800">Leaves</a>
    <form method="POST" action="{{ route('contractor.logout') }}" class="mt-6">
        @csrf
        <button class="text-sm text-slate-400">Logout</button>
    </form>
</aside>
