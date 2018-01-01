<?php

namespace Youtube;

class SessionTokenRepository implements TokenRepositoryInterface
{

    public function getToken()
    {
        return $_SESSION['token'] ?? null;
    }

    public function setToken(array $value)
    {
        $_SESSION['token'] = $value;
    }

}
