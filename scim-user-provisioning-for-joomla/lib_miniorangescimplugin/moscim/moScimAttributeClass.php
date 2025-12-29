<?php


class moScimAttributeClass
{
  private $value;
  private $schema;

  public function __construct($value,$schema)
  {
      $this->value =$value;
      $this->schema=$schema;

  }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

}