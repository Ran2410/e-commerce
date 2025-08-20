<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStats::class
        ];
    }

    public function getTabs(): array 
    {
        return [
            null => Tab::make('All'),
            'new' => Tab::make()->query(fn ($query) => $query->where('status', 'new')),
            'processing' => Tab::make()->query(fn ($query) => $query->where('status', 'processing')),
            'shipped' => Tab::make()->query(fn ($query) => $query->where('status', 'Shipped')),
            'delivered' => Tab::make()->query(fn ($query) => $query->where('status', 'Delivered')),
            'cancelled' => Tab::make()->query(fn ($query) => $query->where('status', 'Cancelled')),
        ];
    }
}
