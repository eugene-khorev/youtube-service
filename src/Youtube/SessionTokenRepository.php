<?php

namespace Youtube;

class SessionTokenRepository implements TokenRepositoryInterface
{

    /**
     * @inheritdoc
     */
    public function getToken()
    {
        return $_SESSION['token'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function setToken(array $value)
    {
        $_SESSION['token'] = $value;
    }

}
