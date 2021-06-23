<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

use App\Repositories\UserRepository;

class UserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getOrSaveUser(string $nickname): array
    {
        try {
            $arrayReturn = array();
            $sanitizedNickname = trim(preg_replace( '/[\W]/', '', $nickname));

            if ($sanitizedNickname === '') {
                $arrayReturn['error'] = 'Please inform a valid nickname, alphanumeric characters only';
            } else {
                $userId = $this->userRepository->getOrSaveUser($sanitizedNickname);
                $arrayReturn['user_id'] = $userId;
                $arrayReturn['sanitized_nickname'] = $sanitizedNickname;

                if ($userId == 0) {
                    $arrayReturn['error'] = 'Error creating or retrieving the user - probably there is a problem with your DB connection';
                }
            }

            return $arrayReturn;
        } catch (Exception $exception) {
            Log::error($exception);
            return [];
        }
    }
}
