<?php

require_once "constants.php";

class REST{
    protected $request;
    protected $param;
    protected $serviceName;

    public function __construct()
    {
        if($_SERVER["REQUEST_METHOD"]!=='POST')
        {
            $this->throwError(REQUEST_METHOD_ERR,"The request method should be POST method.");
        }

        $handler = fopen("php://input","r");
        $this->request = stream_get_contents($handler);
        $this->validateRequest();
    }

    public function validateRequest()
    {
        if($_SERVER["CONTENT_TYPE"] !== 'application/json'){
            $this->throwError(CONTENT_TYPE_NOT_VALID,"Content type is not valid. It should be application/json.");
        }
        $data = json_decode($this->request,true);

        if(!isset($data["name"]) || $data["name"]=='')
        {
            $this->throwError(API_NAME_REQUIRED,"API name is required");
        }
        $this->serviceName = $data["name"];

        if(!isset($data["param"]) || !is_array($data["param"]))
        {
            $this->throwError(API_PARAM_REQUIRED,"API param is required");
        }
        $this->param = $data["param"];

        //print_r($data);
    }

    public function validateParameter($fieldname,$value,$dataType,$required=true){
        if($required == true && empty($value) == true)
        {
            $this->throwError(VALIDATE_PARAM_REQUIRE,$fieldname." is required.");
        }

        switch ($dataType){
            case BOOLEAN:
                if(!is_bool($value)){
                    $this->throwError(VALIDATE_PARAM_VALUE,$fieldname." is not valid. It should be boolean.");
                }
                break;
            case STRING:
                if(!is_string($value)){
                    $this->throwError(VALIDATE_PARAM_VALUE,$fieldname." is not valid. It should be STRING.");
                }
                break;
            case INTEGER:
                if(!is_numeric($value)){
                    $this->throwError(VALIDATE_PARAM_VALUE,$fieldname." is not valid. It should be INTEGER.");
                }
                break;
            default:
                break;
        }

        return $value;

    }

    public function processApi(){
        $api = new API();

        $rMethod = new reflectionMethod('API',$this->serviceName);
        if(!method_exists($api,$this->serviceName))
        {
            $this->throwError(API_DOST_EXIST,"API does not exist.");
        }
        $rMethod->invoke($api);
    }

    public function throwError($code,$message){
        $error = [
            "error"=>[
                "status"=>$code,
                "message"=>$message
            ]
        ];
        header("content-type: application/json");
        echo json_encode($error,true);
        exit;
    }
    
    public function returnResponse($code,$message){
        $result = [
            "success"=>[
                "status"=>$code,
                "result"=>$message
            ]
        ];
        header("content-type: application/json");
        echo json_encode($result,true);
        exit;
    }


    public function getBearerToken() {
        $headers = null;
    
        // Get all headers from the request
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            // Fallback for servers where apache_request_headers is not available
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
    
        // Check if the Authorization header exists
        if (isset($headers['Authorization'])) {
            $authorization = $headers['Authorization'];
            // Check if it starts with "Bearer"
            if (preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
                return $matches[1]; // Return the token value
            }
        }
    
        return null; // Return null if no token is found
    }
}

?>