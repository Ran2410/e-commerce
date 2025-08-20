<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('New Orders', Order::query()->where('status', 'new')->count()),
            Stat::make('Processing Orders', Order::query()->where('status', 'processing')->count()),
            Stat::make('Order Shipped', Order::query()->where('status', 'Shipped')->count()),
            Stat::make('Average Price', Number::currency(Order::query()->avg('grand_total'), 'IDR' )
            )
        ];
    }
}
