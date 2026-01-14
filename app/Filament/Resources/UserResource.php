<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Select;



class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $pluralModelLabel = 'Usuários';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'users.id',
                'users.name',
                'users.email',
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Usuário')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->disabled(),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Funções')
                    ->schema([
                        Select::make('roles')
                            ->label('Funções do usuário')
                            ->relationship('roles', 'name') // 🔴 LINHA CRÍTICA
                            ->multiple()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Funções')
                    ->badge()
                    ->separator(','),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Gerenciar funções'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
