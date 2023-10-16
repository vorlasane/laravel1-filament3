<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use Filament\Actions;
use App\Models\Employee;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array 
    {
        return [
            'All' => Tab::make(),
            // this week tab filter from date_of_hire field using subWeek()
            'This Week' => Tab::make()->query(function (QueryBuilder | EloquentBuilder $query) {
                $query->where('date_of_hire', '>=', now()->subWeek());
            })->badge(Employee::query()->where('date_of_hire', '>=', now()->subWeek())->count()),
            // this month tab filter from date_of_hire field using subMonth()
            'This Month' => Tab::make()->query(function (QueryBuilder | EloquentBuilder $query) {
                $query->where('date_of_hire', '>=', now()->subMonth());
            })->badge(Employee::query()->where('date_of_hire', '>=', now()->subMonth())->count()),
            // this year tab filter from date_of_hire field using subYear()
            'This Year' => Tab::make()->query(function (QueryBuilder | EloquentBuilder $query) {
                $query->where('date_of_hire', '>=', now()->subYear());
            })->badge(Employee::query()->where('date_of_hire', '>=', now()->subYear())->count()),
            // this year tab filter from date_of_hire field using subYear()
            'Last Year' => Tab::make()->query(function (QueryBuilder | EloquentBuilder $query) {
                $query->where('date_of_hire', '>=', now()->lastOfYear());
            })->badge(Employee::query()->where('date_of_hire', '>=', now()->lastOfYear())->count()),
            
        ];
    }
}
