<?php

namespace Youtube;

interface TokenRepositoryInterface
{

    /**
     * Return token
     */
    public function getToken();

    /**
     * Sets token
     * @param array $value
     */
    public function setToken(array $value);
}
