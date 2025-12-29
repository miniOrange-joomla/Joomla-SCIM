<?php

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

class moScimRequest
{
    private $requestType = NULL;
    private $requestEndpoint = moScimConstants::NOT_SUPPORTED_ENDPOINT;
    private $queryParameter = NULL;
    private $resourceId = NULL;
    private $post = NULL;

    public function __construct(Uri $uri)
    {
        try {
            $app = Factory::getApplication();

            $this->queryParameter = $uri->getQuery(TRUE);
            $this->requestType = $app->input->server->get("REQUEST_METHOD");
            if ($this->requestType !== moScimConstants::GET_REQUEST) {
                $this->post = file_get_contents('php://input');
            }
            $remaining = explode(moScimConstants::SCIM_EXTENSION, $uri)[1];
            $paths = explode("/", explode("?", $remaining)[0]);
            if (count($paths) >= 2 && in_array($paths[1], moScimConstants::SUPPORTED_ENDPOINTS)) {
                foreach (moScimConstants::SUPPORTED_ENDPOINTS as $value) {
                    if (strcasecmp($paths[1], $value) == 0) {
                        $this->requestEndpoint = ucfirst($value);
                    }
                }
                if (count($paths) == 3) {
                    $this->resourceId = $paths[2];
                }
            }
        } catch (Exception $e) {
        }


    }

    /**
     * @return mixed|null
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @return string
     */
    public function getRequestEndpoint()
    {
        return $this->requestEndpoint;
    }

    /**
     * @return array|string|null
     */
    public function getQueryParameter()
    {
        return $this->queryParameter;
    }

    /**
     * @return |null
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @return false|string|null
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param false|string|null $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }
}
