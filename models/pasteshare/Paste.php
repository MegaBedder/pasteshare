<?php

namespace pasteshare;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="Pastes")
 */
class Paste implements \JsonSerializable
{
    /** @ODM\Id */
    private $id;
    
    /** @ODM\Field(type="string") */
    private $uniqid;
    
    /** @ODM\Field(type="boolean") */
    private $encrypted = false;
    
    /** @ODM\Field(type="string") */
    private $iv;
    
    /** @ODM\Field(type="string") */
    private $language;
    
    /** @ODM\Field(type="string") */
    private $contents;
    
    /** @ODM\Field(type="date") */
    private $created;
    
    /** @ODM\Field(type="boolean") */
    private $expires = false;
    
    /** @ODM\Field(type="date") */
    private $expiration;
    
    /**
     *
     */
    public function __construct()
    {
        $this->uniqid = uniqid();
    }
    
    /**
     * Magic set function
     *
     * @param string $name The name of the variable to set
     * @param mixed $value The value to store in the variable
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
    
    /**
     * Magic get function
     *
     * @param string $name The name of the variable to get
     * @return mixed The value stored in the variable
     */
    public function __get($name)
    {
        return $this->$name;
    }
    
    /**
     * Magic Json Serialization function
     * This gets fired off whenever the object is serialized
     * to a json object.
     *
     * @return array The array of values contained within this class
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    
    /**
     * Return an array of the contents of this paste
     *
     * @return array The contents of this paste
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "uniqid" => $this->uniqid,
            "encrypted" => $this->encrypted,
            "iv" => $this->iv,
            "language" => $this->language,
            "contents" => $this->contents,
            "created" => $this->created,
            "expires" => $this->expires,
            "expiration" => $this->expiration,
        ];
    }
}
