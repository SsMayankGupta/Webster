<div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Request Guest Pass</h3>

        @if($errorMessage)
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Meal</label>
                <select
                    wire:model.live="menuId"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="0">-- Select a meal --</option>
                    @foreach($menus as $menu)
                        <option value="{{ $menu->id }}">
                            {{ $menu->meal_type }} - {{ $menu->serve_at->format('M j, g:i A') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Guests</label>
                <input
                    type="number"
                    wire:model.live="guestCount"
                    min="1"
                    max="5"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            @if($estimatedFee > 0)
                <div class="p-3 bg-blue-50 border border-blue-200 rounded">
                    <p class="text-sm text-blue-700">
                        <strong>Estimated Fee:</strong> ₹{{ number_format($estimatedFee / 100, 2) }}
                    </p>
                </div>
            @endif

            <button
                wire:click="submit"
                wire:loading.attr="disabled"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition disabled:opacity-50"
            >
                <span wire:loading>Submitting...</span>
                <span wire:loading.remove>Submit Request</span>
            </button>
        </div>
    </div>
</div>
