<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Ekip ve Yetkiler';

    protected static ?string $navigationLabel = 'Roller ve Yetkiler';

    protected static ?string $modelLabel = 'Rol';

    protected static ?string $pluralModelLabel = 'Roller';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TEAM) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Rol Adı')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
                CheckboxList::make('permissions')
                    ->label('Erişebileceği Alanlar')
                    ->relationship('permissions', 'name')
                    ->options(AccessArea::labels())
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Rol Adı')->searchable(),
                TextColumn::make('permissions.name')
                    ->label('Erişim Alanları')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AccessArea::labels()[$state] ?? $state),
                TextColumn::make('users_count')->label('Kullanıcı Sayısı')->counts('users')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->visible(fn (Role $record) => $record->name !== 'Süper Admin'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
