<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Konten Website';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Testimoni';

    protected static ?string $pluralModelLabel = 'Testimoni';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->label('Keterangan (misal: Pendaki Gunung)')
                    ->maxLength(255),
                Forms\Components\Select::make('rating')
                    ->label('Rating')
                    ->options([
                        5 => '5 - Sangat puas',
                        4 => '4 - Puas',
                        3 => '3 - Cukup',
                        2 => '2 - Kurang',
                        1 => '1 - Buruk',
                    ])
                    ->default(5)
                    ->required(),
                Forms\Components\FileUpload::make('photo')
                    ->label('Foto (opsional)')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->disk('public')
                    ->directory('testimonials')
                    ->visibility('public')
                    ->maxSize(1024),
                Forms\Components\Textarea::make('content')
                    ->label('Isi Testimoni')
                    ->required()
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->label('Tampilkan di website')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))
                    ->color('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('Isi')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Tampil')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTestimonials::route('/'),
        ];
    }
}