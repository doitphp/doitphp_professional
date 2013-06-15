<?php
/**
 * WSDL文件生成类
 *
 * 注:本类代码来自YII framework 1.1.5, 略有改动
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @link http://www.doitphp.com
 * @license New BSD License.{@link http://www.opensource.org/licenses/bsd-license.php}
 * @version $Id: Wsdl.class.php 2.0 2012-12-23 10:08:01Z tommy $
 * @package library
 * @since 1.0
 */

if (!defined('IN_DOIT')) {
    exit();
}

class Wsdl {

    /**
     * @var string encoding of the Web service. Defaults to 'UTF-8'.
     */
    protected $_encoding='UTF-8';

    /**
     * @var string the namespace to be used in the generated WSDL.
     * If not set, it defaults to the name of the class that WSDL is generated upon.
     */
    protected $_namespace = null;

    /**
     * @var string the name of the generated WSDL.
     * If not set, it defaults to "urn:{$className}wsdl".
     */
    protected $_serviceName = null;

    private $_operations = null;
    private $_types = null;
    private $_messages = null;

    private $_providerClass = null;
    private $_serviceUrl = null;


    /**
     * 构造方法
     *
     * @access public
     *
     * @param string $providerClass 所要解析的PHP文件类名称
     * @param string $serviceUrl 所要解析的PHP文件的访问网址
     *
     * @return boolean
     */
    public function __construct($providerClass, $serviceUrl) {

        $this->_providerClass = $providerClass;
        $this->_serviceUrl    = $serviceUrl;

        return true;
    }

    /**
     * Generates the WSDL for the given class.
     *
     * @access public
     *
     * @param string $className class name
     * @param string $serviceUrl Web service URL
     * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
     *
     * @return string the generated WSDL
     */
    public function createWsdl($className, $serviceUrl, $encoding='UTF-8')
    {
        $this->_operations=array();
        $this->_types=array();
        $this->_messages=array();
        if($this->_serviceName===null)
            $this->_serviceName=$className;
        if($this->_namespace===null)
            $this->_namespace="urn:{$className}wsdl";

        $reflection=new ReflectionClass($className);
        foreach($reflection->getMethods() as $method)
        {
            if($method->isPublic())
                $this->_processMethod($method);
        }

        return $this->_buildDOM($serviceUrl,$encoding)->saveXML();
    }

    /*
     * @param ReflectionMethod $method method
     */
    private function _processMethod($method)
    {
        $comment=$method->getDocComment();
        if(strpos($comment,'@soap')===false)
            return;
        $methodName=$method->getName();
        $comment=preg_replace('/^\s*\**(\s*?$|\s*)/m','',$comment);
        $params=$method->getParameters();
        $message=array();
        $n=preg_match_all('/^@param\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches);
        if($n>count($params))
            $n=count($params);
        for($i=0;$i<$n;++$i)
            $message[$params[$i]->getName()]=array($this->_processType($matches[1][$i]), trim($matches[3][$i])); // name => type, doc

        $this->_messages[$methodName.'Request']=$message;

        if(preg_match('/^@return\s+([\w\.]+(\[\s*\])?)\s*?(.*)$/im',$comment,$matches))
            $return=array($this->_processType($matches[1]),trim($matches[2])); // type, doc
        else
            $return=null;
        $this->_messages[$methodName.'Response']=array('return'=>$return);

        if(preg_match('/^\/\*+\s*([^@]*?)\n@/s',$comment,$matches))
            $doc=trim($matches[1]);
        else
            $doc='';
        $this->_operations[$methodName]=$doc;
    }

    /*
     * @param string $type PHP variable type
     */
    private function _processType($type)
    {
        static $typeMap=array(
            'string'  =>'xsd:string',
            'str'     =>'xsd:string',
            'int'     =>'xsd:int',
            'integer' =>'xsd:integer',
            'float'   =>'xsd:float',
            'double'  =>'xsd:float',
            'bool'    =>'xsd:boolean',
            'boolean' =>'xsd:boolean',
            'date'    =>'xsd:date',
            'time'    =>'xsd:time',
            'datetime'=>'xsd:dateTime',
            'array'   =>'soap-enc:Array',
            'object'  =>'xsd:struct',
            'mixed'   =>'xsd:anyType',
        );
        if(isset($typeMap[$type]))
            return $typeMap[$type];
        else if(isset($this->_types[$type]))
            return is_array($this->_types[$type]) ? 'tns:'.$type : $this->_types[$type];
        else if(($pos=strpos($type,'[]'))!==false) // if it is an array
        {
            $type=substr($type,0,$pos);
            if(isset($typeMap[$type]))
                $this->_types[$type.'[]']='xsd:'.$type.'Array';
            else
            {
                $this->_types[$type.'[]']='tns:'.$type.'Array';
                $this->_processType($type);
            }
            return $this->_types[$type.'[]'];
        }
    }

    /*
     * @param string $serviceUrl Web service URL
     * @param string $encoding encoding of the WSDL. Defaults to 'UTF-8'.
     */
    private function _buildDOM($serviceUrl,$encoding)
    {
        $xml="<?xml version=\"1.0\" encoding=\"$encoding\"?>
<definitions name=\"{$this->_serviceName}\" targetNamespace=\"{$this->_namespace}\"
     xmlns=\"http://schemas.xmlsoap.org/wsdl/\"
     xmlns:tns=\"{$this->_namespace}\"
     xmlns:soap=\"http://schemas.xmlsoap.org/wsdl/soap/\"
     xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"
     xmlns:wsdl=\"http://schemas.xmlsoap.org/wsdl/\"
     xmlns:soap-enc=\"http://schemas.xmlsoap.org/soap/encoding/\"></definitions>";

        $dom=new DOMDocument();
        $dom->loadXml($xml);
        $this->_addTypes($dom);

        $this->_addMessages($dom);
        $this->_addPortTypes($dom);
        $this->_addBindings($dom);
        $this->_addService($dom,$serviceUrl);

        return $dom;
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function _addTypes($dom)
    {
        if($this->_types===array())
            return;
        $types=$dom->createElement('wsdl:types');
        $schema=$dom->createElement('xsd:schema');
        $schema->setAttribute('targetNamespace',$this->_namespace);
        foreach($this->_types as $phpType=>$xmlType)
        {
            if(is_string($xmlType) && strrpos($xmlType,'Array')!==strlen($xmlType)-5)
                continue;  // simple type
            $complexType=$dom->createElement('xsd:complexType');
            if(is_string($xmlType))
            {
                if(($pos=strpos($xmlType,'tns:'))!==false)
                    $complexType->setAttribute('name',substr($xmlType,4));
                else
                    $complexType->setAttribute('name',$xmlType);
                $complexContent=$dom->createElement('xsd:complexContent');
                $restriction=$dom->createElement('xsd:restriction');
                $restriction->setAttribute('base','soap-enc:Array');
                $attribute=$dom->createElement('xsd:attribute');
                $attribute->setAttribute('ref','soap-enc:arrayType');
                $attribute->setAttribute('wsdl:arrayType',substr($xmlType,0,strlen($xmlType)-5).'[]');
                $restriction->appendChild($attribute);
                $complexContent->appendChild($restriction);
                $complexType->appendChild($complexContent);
            }
            else if(is_array($xmlType))
            {
                $complexType->setAttribute('name',$phpType);
                $all=$dom->createElement('xsd:all');
                foreach($xmlType as $name=>$type)
                {
                    $element=$dom->createElement('xsd:element');
                    $element->setAttribute('name',$name);
                    $element->setAttribute('type',$type[0]);
                    $all->appendChild($element);
                }
                $complexType->appendChild($all);
            }
            $schema->appendChild($complexType);
            $types->appendChild($schema);
        }

        $dom->documentElement->appendChild($types);
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function _addMessages($dom)
    {
        foreach($this->_messages as $name=>$message)
        {
            $element=$dom->createElement('wsdl:message');
            $element->setAttribute('name',$name);
            foreach($this->_messages[$name] as $partName=>$part)
            {
                if(is_array($part))
                {
                    $partElement=$dom->createElement('wsdl:part');
                    $partElement->setAttribute('name',$partName);
                    $partElement->setAttribute('type',$part[0]);
                    $element->appendChild($partElement);
                }
            }
            $dom->documentElement->appendChild($element);
        }
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function _addPortTypes($dom)
    {
        $portType=$dom->createElement('wsdl:portType');
        $portType->setAttribute('name',$this->_serviceName.'PortType');
        $dom->documentElement->appendChild($portType);
        foreach($this->_operations as $name=>$doc)
            $portType->appendChild($this->_createPortElement($dom,$name,$doc));
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $name method name
     * @param string $doc doc
     */
    private function _createPortElement($dom,$name,$doc)
    {
        $operation=$dom->createElement('wsdl:operation');
        $operation->setAttribute('name',$name);

        $input = $dom->createElement('wsdl:input');
        $input->setAttribute('message', 'tns:'.$name.'Request');
        $output = $dom->createElement('wsdl:output');
        $output->setAttribute('message', 'tns:'.$name.'Response');

        $operation->appendChild($dom->createElement('wsdl:documentation',$doc));
        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     */
    private function _addBindings($dom)
    {
        $binding=$dom->createElement('wsdl:binding');
        $binding->setAttribute('name',$this->_serviceName.'Binding');
        $binding->setAttribute('type','tns:'.$this->_serviceName.'PortType');

        $soapBinding=$dom->createElement('soap:binding');
        $soapBinding->setAttribute('style','rpc');
        $soapBinding->setAttribute('transport','http://schemas.xmlsoap.org/soap/http');
        $binding->appendChild($soapBinding);

        $dom->documentElement->appendChild($binding);

        foreach($this->_operations as $name=>$doc)
            $binding->appendChild($this->_createOperationElement($dom,$name));
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $name method name
     */
    private function _createOperationElement($dom,$name)
    {
        $operation=$dom->createElement('wsdl:operation');
        $operation->setAttribute('name', $name);
        $soapOperation = $dom->createElement('soap:operation');
        $soapOperation->setAttribute('soapAction', $this->_namespace.'#'.$name);
        $soapOperation->setAttribute('style','rpc');

        $input = $dom->createElement('wsdl:input');
        $output = $dom->createElement('wsdl:output');

        $soapBody = $dom->createElement('soap:body');
        $soapBody->setAttribute('use', 'encoded');
        $soapBody->setAttribute('namespace', $this->_namespace);
        $soapBody->setAttribute('encodingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');
        $input->appendChild($soapBody);
        $output->appendChild(clone $soapBody);

        $operation->appendChild($soapOperation);
        $operation->appendChild($input);
        $operation->appendChild($output);

        return $operation;
    }

    /*
     * @param DOMDocument $dom Represents an entire HTML or XML document; serves as the root of the document tree
     * @param string $serviceUrl Web service URL
     */
    private function _addService($dom,$serviceUrl)
    {
        $service=$dom->createElement('wsdl:service');
        $service->setAttribute('name', $this->_serviceName.'Service');

        $port=$dom->createElement('wsdl:port');
        $port->setAttribute('name', $this->_serviceName.'Port');
        $port->setAttribute('binding', 'tns:'.$this->_serviceName.'Binding');

        $soapAddress=$dom->createElement('soap:address');
        $soapAddress->setAttribute('location',$serviceUrl);
        $port->appendChild($soapAddress);
        $service->appendChild($port);
        $dom->documentElement->appendChild($service);
    }

    /**
     * 显示wsdl文件
     *
     * @access public
     * @return void
     */
    public function renderWsdl() {

        $wsdlContent = $this->createWsdl($this->_providerClass, $this->_serviceUrl, $this->_encoding);

        header('Content-Type: text/xml;charset='.$this->_encoding);
        header('Content-Length: '.(function_exists('mb_strlen') ? mb_strlen($wsdlContent,'8bit') : strlen($wsdlContent)));

        echo $wsdlContent;
    }
}