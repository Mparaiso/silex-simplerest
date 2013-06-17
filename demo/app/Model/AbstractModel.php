<?php
namespace Model;
/**
 * Class IModel
 * @package Model
 */
abstract class AbstractModel
{

    /**
     * FR : converti le model en tableau <br/>
     * @return array
     */
    function toArray(){
        $data =  get_object_vars($this);
        foreach($data as $key=>$value){
            if($value==null){
                unset($data[$key]);
            }
        }
        return $data;
    }
}