<div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Menu Management Engine</h3>

        <div class="flex gap-2 mb-6">
            <button
                wire:click="$set('mode', 'clone')"
                class="px-4 py-2 rounded text-sm {{ $mode === 'clone' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
            >
                Clone Past Week
            </button>
            <button
                wire:click="$set('mode', 'macros')"
                class="px-4 py-2 rounded text-sm {{ $mode === 'macros' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
            >
                Generate from Macros
            </button>
            <button
                wire:click="$set('mode', 'manual')"
                class="px-4 py-2 rounded text-sm {{ $mode === 'manual' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}"
            >
                Manual Entry
            </button>
        </div>

        @if($mode === 'clone')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Week to Clone</label>
                    <select
                        wire:model.live="selectedWeek"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">-- Select a week --</option>
                        @foreach($pastWeeks as $index => $week)
                            <option value="{{ $index }}">{{ $week['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    wire:click="clonePastWeek"
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition disabled:opacity-50"
                >
                    <span wire:loading>Cloning...</span>
                    <span wire:loading.remove>Clone Week Schedule</span>
                </button>
            </div>
        @elseif($mode === 'macros')
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Protein (g)</label>
                        <input
                            type="number"
                            wire:model.live="targetMacros.protein"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Carbs (g)</label>
                        <input
                            type="number"
                            wire:model.live="targetMacros.carbs"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Calories</label>
                        <input
                            type="number"
                            wire:model.live="targetMacros.calories"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>
                </div>

                <button
                    wire:click="generateFromMacros"
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition disabled:opacity-50"
                >
                    <span wire:loading>Generating...</span>
                    <span wire:loading.remove>Generate Schedule</span>
                </button>
            </div>
        @elseif($mode === 'manual')
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Breakfast</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lunch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Snacks</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dinner</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $day }}</td>
                                @foreach(['BREAKFAST', 'LUNCH', 'SNACKS', 'DINNER'] as $mealType)
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            wire:model.live="schedule.{{ $day }}.{{ $mealType }}.items"
                                            placeholder="Menu items"
                                            class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-1"
                                        >
                                        <div class="grid grid-cols-3 gap-1 text-xs">
                                            <input
                                                type="number"
                                                wire:model.live="schedule.{{ $day }}.{{ $mealType }}.protein"
                                                placeholder="P (g)"
                                                class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                            <input
                                                type="number"
                                                wire:model.live="schedule.{{ $day }}.{{ $mealType }}.carbs"
                                                placeholder="C (g)"
                                                class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                            <input
                                                type="number"
                                                wire:model.live="schedule.{{ $day }}.{{ $mealType }}.calories"
                                                placeholder="Kcal"
                                                class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button
                wire:click="saveSchedule"
                wire:loading.attr="disabled"
                class="mt-4 w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition disabled:opacity-50"
            >
                <span wire:loading>Saving...</span>
                <span wire:loading.remove>Save Schedule</span>
            </button>
        @endif
    </div>
</div>
