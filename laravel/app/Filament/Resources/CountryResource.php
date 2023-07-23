<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Models\Country;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                ->schema([
                    Forms\Components\TextInput::make('countries_name')->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('countries_iso_code_2')->unique(ignoreRecord: true)->required()->rules('size:2'),
                    Forms\Components\TextInput::make('countries_iso_code_3')->unique(ignoreRecord: true)->required()->rules('size:3'),
                    Forms\Components\Checkbox::make('status')->default(fn ($record) => boolval($record->status)),
                    Forms\Components\Select::make('address_format_id')->required()
                        ->relationship('addressFormat', 'address_format_id')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('countries_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('countries_iso_code_3')->searchable()->sortable()->label('ISO Code'),
                Tables\Columns\IconColumn::make('status')->label('Status')
                    ->boolean()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',

                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
