<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    private $userRepository;

    public function __construct (UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getOrSaveUser(string $nickname): int
    {
        try {
            return $this->userRepository->getOrSaveUser($nickname);
        } catch (\Exception $exception) {
            Log::error($exception);
            return 0;
        }
    }
}
