<?php

namespace App\Livewire;

use App\Events\MenuUpdated;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.contractor')]
#[Title('Update Menu')]
class MenuUpdate extends Component
{
    public array $menus = [];

    public ?string $statusMessage = null;

    #[Locked]
    public int $previousWeeksLoaded = 0;

    #[Locked]
    public bool $hasPreviousMenus = false;

    public function mount(): void
    {
        $this->loadMenus();
    }

    #[On('contractor-logged-in')]
    public function onContractorLoggedIn(): void
    {
        $this->previousWeeksLoaded = 0;
        $this->loadMenus();
    }

    public function loadMenus(): void
    {
        $today = now()->startOfDay();
        $startDate = $today->copy();

        if ($this->previousWeeksLoaded > 0) {
            $startDate = $today->copy()->startOfWeek()->subWeeks($this->previousWeeksLoaded);
        }

        $latestUpcomingMenuDate = Menu::query()
            ->where('serve_at', '>=', $today)
            ->max('serve_at');
        $endDate = $latestUpcomingMenuDate === null
            ? $today->copy()->endOfDay()
            : Carbon::parse($latestUpcomingMenuDate)->endOfDay();

        $menusByDate = Menu::query()
            ->where('serve_at', '>=', $startDate)
            ->where('serve_at', '<=', $endDate)
            ->orderBy('serve_at')
            ->get()
            ->groupBy(fn ($menu) => $menu->serve_at->format('Y-m-d'));

        $this->menus = [];
        foreach ($menusByDate as $date => $dayMenus) {
            $this->menus[$date] = [
                'breakfast' => null,
                'lunch' => null,
                'snacks' => null,
                'dinner' => null,
            ];

            foreach ($dayMenus as $menu) {
                $this->menus[$date][$menu->meal_type] = [
                    'id' => $menu->id,
                    'items' => $menu->menuItems(),
                    'protein' => $menu->protein,
                    'carbs' => $menu->carbs,
                    'calories' => $menu->calories,
                    'status' => $menu->status,
                ];
            }
        }

        $this->hasPreviousMenus = Menu::query()
            ->where('serve_at', '<', $startDate)
            ->exists();
    }

    public function loadPreviousData(): void
    {
        if (! $this->hasPreviousMenus) {
            return;
        }

        $this->previousWeeksLoaded++;
        $this->loadMenus();
    }

    public function updateMenu(int $menuId, string $date, string $mealType): void
    {
        $contractor = Auth::user();

        if ($contractor === null || $contractor->role !== 'contractor') {
            $this->dispatch('portal-toast', message: 'Unauthorized action.', type: 'error');

            return;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            || ! in_array($mealType, ['breakfast', 'lunch', 'snacks', 'dinner'], true)) {
            $this->dispatch('portal-toast', message: 'Invalid meal selection.', type: 'error');

            return;
        }

        $entry = $this->menus[$date][$mealType] ?? null;
        if (! is_array($entry) || (int) ($entry['id'] ?? 0) !== $menuId) {
            $this->dispatch('portal-toast', message: 'This meal is no longer available to update. Reload the page and try again.', type: 'error');

            return;
        }

        $path = "menus.{$date}.{$mealType}";
        $validated = $this->validate([
            "{$path}.items" => ['required'],
            "{$path}.protein" => ['required', 'numeric', 'min:0'],
            "{$path}.carbs" => ['required', 'numeric', 'min:0'],
            "{$path}.calories" => ['required', 'numeric', 'min:0'],
        ]);
        $data = data_get($validated, $path);
        $menuItems = $data['items'];

        if (is_string($menuItems)) {
            $menuItems = explode(',', $menuItems);
        }

        if (! is_array($menuItems)) {
            throw ValidationException::withMessages([
                "{$path}.items" => 'Enter menu items separated by commas.',
            ]);
        }

        $menuItems = array_values(array_filter(
            array_map(fn (mixed $item): string => trim((string) $item), $menuItems),
            fn (string $item): bool => $item !== '',
        ));

        if ($menuItems === []) {
            throw ValidationException::withMessages([
                "{$path}.items" => 'Enter at least one menu item.',
            ]);
        }

        $menu = Menu::query()->findOrFail($menuId);
        if ($menu->serve_at->toDateString() !== $date || $menu->meal_type !== $mealType) {
            $this->dispatch('portal-toast', message: 'This meal is no longer available to update. Reload the page and try again.', type: 'error');

            return;
        }

        $menu->update([
            'recipe_text' => implode(', ', $menuItems),
            'items' => $menuItems,
            'protein' => $data['protein'],
            'carbs' => $data['carbs'],
            'calories' => $data['calories'],
        ]);

        broadcast(new MenuUpdated($menu));
        $this->loadMenus();
        $this->statusMessage = 'Meal updated successfully.';

        $this->dispatch('portal-toast', message: 'Menu updated successfully!', type: 'success');
    }

    public function render()
    {
        return view('livewire.menu-update');
    }
}
