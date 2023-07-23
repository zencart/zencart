<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CurrencyResource\Pages;
use App\Filament\Resources\CurrencyResource\RelationManagers;
use App\Models\Currency;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(32),
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(3),
            Forms\Components\TextInput::make('symbol_left')
                ->maxLength(32),
            Forms\Components\TextInput::make('symbol_right')
                ->maxLength(32),
            Forms\Components\TextInput::make('decimal_point')
                ->maxLength(1),
            Forms\Components\TextInput::make('thousands_point')
                ->maxLength(1),
            Forms\Components\TextInput::make('decimal_places')
                ->maxLength(1),
            Forms\Components\TextInput::make('value'),
            Forms\Components\Checkbox::make('is_default')->when(fn ($record) => (!$record) || (defined('DEFAULT_CURRENCY') && DEFAULT_CURRENCY != $record->code)),
        ];

        return $form
            ->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Title')
                    ->getStateUsing(function(Model $record) {
                        $title  = $record->title;
                        if ($record->code == \DEFAULT_CURRENCY) {
                            $title .= '(default)';
                        }
                        return $title;
                    }),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('symbol_left'),
                Tables\Columns\TextColumn::make('symbol_right'),
                Tables\Columns\TextColumn::make('decimal_point'),
                Tables\Columns\TextColumn::make('thousands_point'),
                Tables\Columns\TextColumn::make('decimal_places'),
                Tables\Columns\TextColumn::make('value'),
                //Tables\Columns\TextColumn::make('last_updated')
                //    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCurrencies::route('/'),
            'create' => Pages\CreateCurrency::route('/create'),
            'edit' => Pages\EditCurrency::route('/{record}/edit'),
        ];
    }
}
