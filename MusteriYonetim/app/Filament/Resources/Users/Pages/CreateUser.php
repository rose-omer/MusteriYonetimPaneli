<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private bool $sendInvite = true;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->sendInvite = (bool) ($this->data['davet_gonder'] ?? true);

        // Davet akışında kullanıcı parolayı kendisi belirler; rastgele parola kimseye gösterilmez.
        if ($this->sendInvite || blank($data['password'] ?? null)) {
            $data['password'] = Str::password(32);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->sendInvite) {
            return;
        }

        /** @var User $user */
        $user = $this->getRecord();
        $token = Password::broker(Filament::getAuthPasswordBroker())->createToken($user);

        $notification = new ResetPassword($token);
        $notification->url = Filament::getResetPasswordUrl($token, $user);
        $user->notify($notification);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->sendInvite ? 'Kullanıcı eklendi, davet e-postası gönderildi' : 'Kullanıcı eklendi';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
