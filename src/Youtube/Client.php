<?php

namespace Youtube;

final class Client
{

    /**
     * Google client
     * @var \Google_Client
     */
    private $client;

    /**
     * Youtube service
     * @var \Google_Service_YouTube
     */
    private $service;

    /**
     * Token repository
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * Client constructor
     * @param \Youtube\TokenRepositoryInterface $tokenRepository
     * @param string $configFile
     * @param string $redirectUrl
     */
    public function __construct(
            TokenRepositoryInterface $tokenRepository, 
            string $configFile, 
            string $redirectUrl
        )
    {
        // Set token repository
        $this->tokenRepository = $tokenRepository;

        // Create Google client
        $this->client = $this->getClient($configFile, $redirectUrl);

        // Create Youtube service
        $this->service = $this->getService();
    }

    /**
     * Creates Youtube service
     * @return \Google_Service_YouTube
     */
    private function getService()
    {
        // Create Youtube service
        return new \Google_Service_YouTube($this->client);
    }

    /**
     * Creates Google client
     * @param string $configFile
     * @param string $redirectUrl
     * @return \Google_Client
     */
    private function getClient(string $configFile, string $redirectUrl)
    {
        // Create Google client
        $client = new \Google_Client();

        // Setup location of client secret JSON file
        $client->setAuthConfigFile($configFile);

        // Setup redirect URI
        $client->setRedirectUri($redirectUrl);

        // Setup client params
        $client->addScope(\Google_Service_YouTube::YOUTUBE_FORCE_SSL);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        
        return $client;
    }
    
    /**
     * Makes authentication
     * @param \Closure $getAuthCodeClosure
     * @param \Closure $redicretClosure
     * @return type
     */
    public function authenticate(\Closure $getAuthCodeClosure, \Closure $redicretClosure)
    {
        // Load previously authorized credentials.
        $accessToken = $this->tokenRepository->getToken();
        
        // Check if token exists
        if (empty($accessToken)) {
            // Get authorization code
            $authCode = $getAuthCodeClosure();
            if (!empty($authCode)) {
                // Exchange authorization code for an access token
                $accessToken = $this->client->authenticate($authCode);
                
                // Save access token
                $this->tokenRepository->setToken($accessToken);
            } else {
                // Request authorization from the user.
                $authUrl = $this->client->createAuthUrl();
                $redicretClosure($authUrl);
                return;
            }
        }
        
        // Set access token
        $this->client->setAccessToken($accessToken);
        
        // Check if token expired
        if ($this->client->isAccessTokenExpired()) {
            // Refresh token
            $this->refreshAccessToken();
        }
    }
    
    /**
     * Refreshes expired access token
     */
    private function refreshAccessToken()
    {
        // Refresh token
        $refreshTokenSaved = $this->client->refreshToken(
                $this->client->getRefreshToken()
            );

        // Update access token
        $this->client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

        // Pass access token to temp variable
        $accessTokenUpdated = $this->client->getAccessToken();

        // Append refresh token
        $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

        // Save updated token
        $this->tokenRepository->setToken($accessTokenUpdated);
    }

    public function cannelsListByUsername($userName)
    {
        return $this->service->channels->listChannels(
                'snippet,contentDetails,statistics',
                ['forUsername' => $userName]
            );
    }
    
    /**
     * Returns video rating info
     * @param type $videoId
     * @param type $params
     * @return type
     */
    public function videosGetRating($videoId, $params = [])
    {
        return $this->service->videos->getRating($videoId, $params);
    }

    /**
     * Returns channel subscription info
     * @param type $id
     * @return type
     */
    public function subscriptionsListForChannelId($id)
    {
        return $this->service->subscriptions
                        ->listSubscriptions('snippet,contentDetails', [
                            'forChannelId' => $id,
                            'mine' => true
        ]);
    }

}
