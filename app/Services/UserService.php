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
            $arrayReturn['sanitized_nickname'] = trim(preg_replace( '/[\W]/', '', $nickname));

            if ($arrayReturn['sanitized_nickname'] === '') {
                $arrayReturn['error'] = 'Please inform a valid nickname, alphanumeric characters only';
            } else {
                $objSaveUser = $this->userRepository->getOrSaveUser($arrayReturn['sanitized_nickname']);
                $arrayReturn['user_id'] = $objSaveUser->id ?? null;

                if (!isset($arrayReturn['user_id'])) {
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
