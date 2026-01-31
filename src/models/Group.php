<?php

class Group
{
    private $id;
    private $name;
    private $description;
    private $owner;

    public function __construct($id, $name, $description, $owner)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->owner = $owner;
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getOwner() { return $this->owner; }
}