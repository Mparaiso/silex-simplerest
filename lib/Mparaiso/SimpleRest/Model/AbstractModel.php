<?php
namespace Mparaiso\SimpleRest\Model;
/**
 * Class IModel
 * @package Model
 */
abstract class AbstractModel implements IModel
{
    function __construct(array $values = array())
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

    }

    /**
     * FR : converti le model en tableau <br/>
     * @return array
     */
    function toArray()
    {
        $data = get_object_vars($this);
        foreach ($data as $key => $value) {
            if ($value == NULL) {
                unset($data[$key]);
            }
        }
        return $data;
    }



    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    abstract function __toString();
}