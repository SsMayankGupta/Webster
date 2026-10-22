<div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Guest Pass Queue</h3>

        @if(empty($pendingRequests))
            <div class="text-center py-8 text-gray-500">
                <p>No pending guest pass requests.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($pendingRequests as $request)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium">{{ $request['student_name'] }}</h4>
                                <p class="text-sm text-gray-600">ID: {{ $request['student_id'] }}</p>
                            </div>
                            <span class="text-sm text-gray-500">{{ $request['created_at'] }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                            <div>
                                <span class="text-gray-600">Meal:</span>
                                <span class="font-medium">{{ $request['meal_type'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Time:</span>
                                <span class="font-medium">{{ $request['serve_at'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Guests:</span>
                                <span class="font-medium">{{ $request['guest_count'] }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                wire:click="approve({{ $request['id'] }})"
                                wire:loading.attr="disabled"
                                class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition disabled:opacity-50 text-sm"
                            >
                                <span wire:loading>Processing...</span>
                                <span wire:loading.remove>Approve</span>
                            </button>
                            <button
                                wire:click="reject({{ $request['id'] }})"
                                wire:loading.attr="disabled"
                                class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition disabled:opacity-50 text-sm"
                            >
                                <span wire:loading>Processing...</span>
                                <span wire:loading.remove>Reject</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
