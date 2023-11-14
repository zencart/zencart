<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeveloperResource\Pages;
use App\Filament\Resources\DeveloperResource\RelationManagers;
use App\Models\Developer;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeveloperResource extends Resource
{
    protected static ?string $model = Developer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Developer Name'),
                Tables\Columns\TextColumn::make('user.email')->label('Developer Email'),
                Tables\Columns\IconColumn::make('is_activated')->label('Active')
                    ->options([
                        'heroicon-o-x-circle',
                        'heroicon-o-check-circle' => fn ($state): bool => $state === 1,
                    ]),
                Tables\Columns\IconColumn::make('user.email_verified_at')->label('Verified')
                    ->options([
                        'heroicon-o-x-circle',
                        'heroicon-o-check-circle' => fn ($state): bool => isset($state),
                    ])
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevelopers::route('/'),
            'create' => Pages\CreateDeveloper::route('/create'),
            'edit' => Pages\EditDeveloper::route('/{record}/edit'),
        ];
    }
    public static function canSee($request): bool
    {
        return false;
    }
}
