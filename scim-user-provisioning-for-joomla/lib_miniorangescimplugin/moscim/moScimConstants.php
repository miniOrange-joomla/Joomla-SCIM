<?php


class moScimConstants
{

    public CONST GET_REQUEST  = "GET";
    public CONST POST_REQUEST = "POST";
    public CONST PUT_REQUEST  = "PUT";
    public CONST PATCH_REQUEST = "PATCH";
    public CONST DELETE_REQUEST = "DELETE";
    public CONST USERS_ENPOINT = "Users";
    public CONST GROUP_ENDPOINT = "Groups";

    public CONST SUPPORTED_ENDPOINTS = array(self::USERS_ENPOINT);

    public CONST NOT_SUPPORTED_ENDPOINT = "NOT_SUPPORTED";
    public CONST SCIM_EXTENSION = "miniorangescim";

}