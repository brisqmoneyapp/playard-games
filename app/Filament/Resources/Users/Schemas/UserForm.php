<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User details')
                    ->description('Create super admins, managers and staff members for Playard Games.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('role')
                            ->label('Role')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin / Manager',
                                'staff' => 'Staff',
                            ])
                            ->default('staff')
                            ->required(),

                        TextInput::make('staff_pin')
                            ->label('Staff PIN')
                            ->placeholder('1234')
                            ->helperText('Optional for now. Later this can be used for fast staff login.')
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(6),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive users cannot access the system.')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
