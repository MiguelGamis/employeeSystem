<?php

class MechanicalTA_ID
{
    public $id;
    function __construct($id){
        $this->id = $id;
    }
    function checkType($type){
        if(get_class($this) != "$type")
            throw new Exception("Expected type $type, but was a ".get_class($this));
    }

    function __toString()
    {
        return "$this->id";
    }
};

class UserID extends MechanicalTA_ID
{}

class GroupID extends MechanicalTA_ID
{}

class RequestID extends MechanicalTA_ID
{}

class EmployeeID extends MechanicalTA_ID
{}

class AssignmentID extends MechanicalTA_ID
{}

