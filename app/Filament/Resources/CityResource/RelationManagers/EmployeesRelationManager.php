<?php

namespace App\Filament\Resources\CityResource\RelationManagers;

use Filament\Forms;
use App\Models\City;
use Filament\Tables;
use App\Models\State;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->description('Enter the employee\'s personal information.')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship(name: 'country', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                            ->required(),
                        Forms\Components\Select::make('state_id')
                            ->options(fn (Get $get): Collection => State::query()
                                ->where('country_id', $get('country_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('city_id', null);
                            })
                            ->required(),
                        Forms\Components\Select::make('city_id')
                            ->options(fn (Get $get): Collection => City::query()
                                ->where('state_id', $get('state_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('department_id')
                            ->relationship(name: 'department', titleAttribute: 'name')
                            ->searchable()
                            // ->multiple()
                            ->preload()
                            ->required(),
                    ])->columns(2),


                Forms\Components\Section::make('Personal Information')
                    ->description('Enter the employee\'s personal information.')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('last_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')->required()->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Personal Address')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('zip_code')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\Datepicker::make('date_of_birth')
                            ->native(false)
                            ->displayformat('d/m/Y')
                            ->maxDate(now()->toDateString())
                            ->required(),
                        Forms\Components\Datepicker::make('date_of_hire')
                            ->native(false)
                            ->displayformat('d/m/Y')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                Tables\Columns\TextColumn::make('country_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('state_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('department_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('middle_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_hire')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    // ->multiple()
                    ->label('Department'),
                Filter::make('Created At')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['created_from'] ?? null,
                            fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date)
                        )->when(
                            $data['created_until'] ?? null,
                            fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date)
                        );
                    })
                    ->indicateUsing(function (array $data):array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['createed_from'] = 'Created from: ' . Carbon::parse($data['created_from'])->format('d/m/Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['createed_until'] = 'Created until: ' . Carbon::parse($data['created_until'])->format('d/m/Y');
                        }
                        return $indicators;
                    })
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
