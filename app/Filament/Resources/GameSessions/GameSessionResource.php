<?php

namespace App\Filament\Resources\GameSessions;

use App\Filament\Resources\GameSessions\Pages\CreateGameSession;
use App\Filament\Resources\GameSessions\Pages\EditGameSession;
use App\Filament\Resources\GameSessions\Pages\ListGameSessions;
use App\Filament\Resources\GameSessions\Schemas\GameSessionForm;
use App\Filament\Resources\GameSessions\Tables\GameSessionsTable;
use App\Models\GameSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GameSessionResource extends Resource
{
    protected static ?string $model = GameSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return GameSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GameSessionsTable::configure($table);
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
            'index' => ListGameSessions::route('/'),
            'create' => CreateGameSession::route('/create'),
            'edit' => EditGameSession::route('/{record}/edit'),
        ];
    }
}
