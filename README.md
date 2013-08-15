SimpleRest Service Provider
===========================

Simple restfull controllers with json and xml serialization
in Silex !

author: Mparaiso <mparaiso@online.fr>

status: WORK IN PROGRESS  , come back soon !


CHANGELOG:

0.0.5 :
+ add xml format support for request datas
+ formats are now expressed as an array like : <code> array("xml","json") </code>
+ Base Service Class uses interface for models (..\Model\IModel) rather than an abstract clas