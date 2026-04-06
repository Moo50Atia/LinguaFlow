<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function updateBaseProfile(User $user, array $data): User
    {
        if (isset($data['avatar'])) {
            if ($user->avatar) {
                $this->fileUploadService->delete($user->avatar);
            }
            $data['avatar'] = $this->fileUploadService->store($data['avatar'], 'avatars');
        }

        $user->update($data);

        return $user;
    }
}
