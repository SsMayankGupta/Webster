<div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Student Feedback</h3>
            <div class="flex gap-2">
                <button
                    wire:click="$set('filter', 'DAILY')"
                    class="px-3 py-1 rounded text-sm {{ $filter === 'DAILY' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    Daily
                </button>
                <button
                    wire:click="$set('filter', 'PERIODIC_15_DAY')"
                    class="px-3 py-1 rounded text-sm {{ $filter === 'PERIODIC_15_DAY' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                >
                    15-Day Periodic
                </button>
            </div>
        </div>

        @if(empty($feedbacks))
            <div class="text-center py-8 text-gray-500">
                <p>No {{ strtolower($filter) }} feedback found.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($feedbacks as $feedback)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium">{{ $feedback['student_name'] }}</h4>
                                <p class="text-sm text-gray-600">ID: {{ $feedback['student_id'] }}</p>
                            </div>
                            <span class="text-sm text-gray-500">{{ $feedback['created_at'] }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                            <div>
                                <span class="text-gray-600">Meal:</span>
                                <span class="font-medium">{{ $feedback['meal_type'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Time:</span>
                                <span class="font-medium">{{ $feedback['serve_at'] }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Rating:</span>
                                <span class="font-medium">{{ $feedback['rating'] }}/5</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Hygiene:</span>
                                <span class="font-medium">{{ $feedback['hygiene_rating'] }}/5</span>
                            </div>
                        </div>

                        @if($feedback['comment'])
                            <p class="text-sm text-gray-700 mb-3">"{{ $feedback['comment'] }}"</p>
                        @endif

                        @if($feedback['contractor_action'] === 'PENDING')
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <button
                                        wire:click="respond({{ $feedback['id'] }}, 'ACKNOWLEDGED')"
                                        class="flex-1 bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700 transition text-sm"
                                    >
                                        Acknowledge
                                    </button>
                                    <button
                                        wire:click="respond({{ $feedback['id'] }}, 'ACTION_TAKEN')"
                                        class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition text-sm"
                                    >
                                        Action Taken
                                    </button>
                                </div>
                                <input
                                    type="text"
                                    wire:model.debounce.500ms="responses.{{ $feedback['id'] }}"
                                    placeholder="Add a note (optional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        @else
                            <div class="p-2 bg-{{ $feedback['contractor_action'] === 'ACTION_TAKEN' ? 'green' : 'yellow' }}-50 border border-{{ $feedback['contractor_action'] === 'ACTION_TAKEN' ? 'green' : 'yellow' }}-200 rounded text-sm">
                                <span class="font-medium">{{ $feedback['contractor_action'] }}</span>
                                @if($feedback['contractor_note'])
                                    <span class="text-gray-600">: {{ $feedback['contractor_note'] }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
