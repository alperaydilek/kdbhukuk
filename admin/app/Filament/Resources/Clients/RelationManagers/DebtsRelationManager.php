<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DebtsRelationManager extends RelationManager
{
    protected static string $relationship = 'debts';

    protected static ?string $title = 'Borçlandırmalar';

    protected static ?string $modelLabel = 'Borçlandırma';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('type')
                    ->label('Tür')
                    ->options([
                        'ucret' => 'Ücret (tahsilat bekleyen alacak)',
                        'masraf' => 'Masraf (şimdi ödenen, kasadan düşer)',
                    ])
                    ->default('ucret')
                    ->required()
                    ->helperText('"Masraf" seçilirse tutar anında ana kasadan gider olarak işlenir.'),
                Select::make('status')
                    ->label('Durum')
                    ->options([
                        'bekliyor' => 'Bekliyor',
                        'odendi' => 'Ödendi',
                        'iptal' => 'İptal',
                    ])
                    ->default('bekliyor')
                    ->required(),
                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->label('Tutar (₺)')
                    ->numeric()
                    ->required(),
                DatePicker::make('due_date')
                    ->label('Vade Tarihi')
                    ->native(false)
                    ->displayFormat('d.m.Y'),
                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Başlık')->searchable(),
                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'masraf' ? 'Masraf' : 'Ücret')
                    ->color(fn (string $state) => $state === 'masraf' ? 'danger' : 'gray'),
                TextColumn::make('amount')->label('Tutar')->money('TRY')->sortable(),
                TextColumn::make('due_date')->label('Vade')->date('d.m.Y')->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'odendi' => 'Ödendi',
                        'iptal' => 'İptal',
                        default => 'Bekliyor',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'odendi' => 'success',
                        'iptal' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'bekliyor' => 'Bekliyor',
                    'odendi' => 'Ödendi',
                    'iptal' => 'İptal',
                ]),
            ])
            ->headerActions([
                CreateAction::make()->label('Borçlandırma Ekle'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
