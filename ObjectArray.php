<?php

class ObjectArray
{
   private $key;
   
   // set Method for Table
   public function setColName ($key, $value)
   {
      $this->$key = $value;
   }
   
   // get Method for Table
   public function getColName ()
   {
      return $this->$key;
   }

}
