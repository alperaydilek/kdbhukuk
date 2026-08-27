<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Support\AccessArea;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Ekip ve Yetkiler';

    protected static ?string $navigationLabel = 'Kullanıcılar';

    protected static ?string $modelLabel = 'Kullanıcı';

    protected static ?string $pluralModelLabel = 'Kullanıcılar';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return auth()->user()?->can(AccessArea::TEAM) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hesap Bilgileri')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Ad Soyad')
                            ->required(),
                        TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Şifre')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Düzenlerken boş bırakırsanız mevcut şifre korunur.')
                            ->columnSpanFull(),
                        Select::make('roles')
                            ->label('Roller')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Kullanıcının erişebileceği alanlar seçilen rollere göre belirlenir.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ad Soyad')->searchable(),
                TextColumn::make('email')->label('E-posta')->searchable(),
                TextColumn::make('roles.name')->label('Roller')->badge(),
                TextColumn::make('created_at')->label('Kayıt Tarihi')->date('d.m.Y'),
            ])
            ->recordActions([
                EditAction::make(),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
