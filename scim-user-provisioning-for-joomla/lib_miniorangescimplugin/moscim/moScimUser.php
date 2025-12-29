<?php

jimport("miniorangescimplugin.moscim.moScimConstants");
jimport("miniorangescimplugin.moscim.moScimRequest");
jimport('miniorangescimplugin.utility.MoScimUtilitiesClient');
jimport("miniorangescimplugin.moscim.moScimAttributeClass");
class moScimUser
{

    public $alreadyExists=FALSE;
    private $userName;
    private $name;
    private $displayName;
    private $nickName;
    private $profileUrl;
    private $title;
    private $userType;
    private $preferredLanguage;
    private $locale;
    private $timezone;
    private $active;
    private $password;
    private $emails;
    private $addresses;
    private $phoneNumbers;
    private $ims;
    private $photos;
    private $address;
    private $groups;
    private $entitlements;
    private $roles;
    private $x509Certificates;

    private $userSchema;
    private $postData;

    public function __construct(moScimRequest $moScimRequest)
    {

        $this->postData = json_decode($moScimRequest->getPost());
        $this->userSchema = json_decode(MoScimUtilitiesClient::getUserJson());

        foreach ($this as $key=>$value){
            if(is_null($value)){
                $attributeSchema = $this->getAttributeSchema($key);
                $value           = $this->getAttributeValue($key);
                $this->$key = new moScimAttributeClass($value,$attributeSchema);
            }
        }
        $this->alreadyExists = $this->checkIfUserIsUnique();

    }


    public function getAttributeSchema($key){
        foreach ($this->userSchema->attributes as $key1=>$value1){
            if($value1->name==$key){
                return $value1;
            }
        }
    }
    public function getAttributeValue($key){
        return isset($this->postData->$key)?$this->postData->$key:null;
    }



    function checkIfUserIsUnique(){
        $result = MoScimUtilitiesClient::miniScimFetchDb('#__users',array('username'=>$this->userName->getValue()),'loadResult','id');
        return !is_null($result);
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return bool
     */
    public function isAlreadyExists(): bool
    {
        return $this->alreadyExists;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return mixed
     */
    public function getNickName()
    {
        return $this->nickName;
    }

    /**
     * @return mixed
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @return mixed
     */
    public function getPreferredLanguage()
    {
        return $this->preferredLanguage;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumbers()
    {
        return $this->phoneNumbers;
    }

    /**
     * @return mixed
     */
    public function getIms()
    {
        return $this->ims;
    }

    /**
     * @return mixed
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return mixed
     */
    public function getEntitlements()
    {
        return $this->entitlements;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return mixed
     */
    public function getX509Certificates()
    {
        return $this->x509Certificates;
    }

    /**
     * @return mixed
     */
    public function getUserSchema()
    {
        return $this->userSchema;
    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @return mixed
     */
    public function getAddresses()
    {
        return $this->addresses;
    }




    public function getPrimaryEmail(){
        $primaryEmail="";
        foreach ($this->emails->getValue() as $key=>$value){
            if(isset($value->primary) || is_null($primaryEmail)){
                $primaryEmail = $value->value;
            }
        }
        return $primaryEmail;

    }


    public static function getUsersByFilter(moScimRequest $moScimRequest){
        $usersArray = array();
        $query = $moScimRequest->getQueryParameter();
        $fieldName  = NULL;
        $operator   = NULL;
        $queryValue = NULL;

        if(!is_null($query) && isset($query['filter']))
        {
            $filter = $query['filter'];
            $filterParts = explode(" ", $filter);
            foreach ($filterParts as $key=>$value){
                if(empty($value))
                   continue;
                else if(is_null($fieldName) && strpos(strtolower($value),"username")!==FALSE){
                    $fieldName = "username";
                }
                else if(is_null($fieldName) && strpos(strtolower($value),"email")!==FALSE){
                    $fieldName  = "email";
                }
                else if(is_null($operator) && strcasecmp("eq",$value )==0){
                    $operator = '=';
                }
                elseif( is_null($queryValue) && $value[0]=="\""){
                    $queryValue = substr($value,1,strlen($value)-2);

                }
            }
        }
        if(is_null($operator) || is_null($queryValue) || is_null($fieldName)){
            return $usersArray;
        }
        else{
            $userIds = MoScimUtilitiesClient::miniScimFetchDb('#__users',array($fieldName=>$queryValue),"loadAssocList",array("id","username"));
            return $userIds;
        }



    }


    function getAttributeMap($attributeMap){
        $attributeMap = json_decode($attributeMap)->profile;
        $attributeMapResult = new stdClass();
        $attributeMapResult->userTable = new stdClass();
        $attributeMapResult->userProfile = new stdClass();
        $attributeMapResult->userFields  = new stdClass();
        foreach ($attributeMap as $scimAttribute=>$JoomlaAttribute){
           $parts = explode(".",$scimAttribute);
            list($typeOfAttribute,$JoomlaAttribute) = self::getTypeOfAttribute($JoomlaAttribute);

            foreach ($parts as $key=>$part){
                if(strpos($part,"]")!==FALSE){
                    list($root,$matcherAttribute,$matcherValue)=$this->getDifferentParts($part);

                    if(is_null($this->$root)){
                        $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = NULL;
                        break;
                    }
                    else
                        $multiValues = $this->$root->getValue();
                    if(!is_object($multiValues) && !is_array($multiValues)){
                        $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = NULL;
                        break;
                    }
                    $found = FALSE;
                    foreach ($multiValues as  $val){
                        if(isset($val->$matcherAttribute) && $val->$matcherAttribute===$matcherValue){
                            $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = $val;
                            $found=TRUE;
                        }
                    }
                    if(!$found){
                        $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = NULL;
                        break;
                    }
                }
                else
                {
                    if($key==0){
                        if(is_null($this->$part)){
                            $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = NULL;
                            break;
                        }
                        $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = $this->$part->getValue();
                    }
                    else {
                        if(!is_null($attributeMapResult->$typeOfAttribute->$JoomlaAttribute) && isset($attributeMapResult->$typeOfAttribute->$JoomlaAttribute->$part))
                            $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = $attributeMapResult->$typeOfAttribute->$JoomlaAttribute->$part;
                        else{
                            $attributeMapResult->$typeOfAttribute->$JoomlaAttribute = NULL;
                        }
                    }

                }



            }
        }
        return $attributeMapResult;
    }
    
    function getDifferentParts($attribute){
        $root = "";
        $matcher = "";
        $section = $root;
        for($i=0;$i<strlen($attribute);$i++){
            if($attribute[$i]==='['){
                $root = $section;
                $section="";
            }
            elseif($attribute[$i]===']'){
                $matcher = $section;
                $section="";
                $i++;

            }
            else
                $section=$section.$attribute[$i];
        }
        $matchers = explode("eq",$matcher);
        return array($root,trim($matchers[0]),trim($matchers[1]," \""));
    }

    static function getTypeOfAttribute($attribute){
        if(substr($attribute,0,3)=="up_"){
            return array("userProfile",substr($attribute,3));
        }
        elseif (substr($attribute,0,2)=="u_"){
            return array("userTable",substr($attribute,2));
        }
        else{
            return array("userFields",$attribute);
        }
    }




}