<?php

class API extends REST {
    
    private $conn;
    
    public function __construct(){
        parent::__construct();

        $db = new DbConnect;
        $this->conn = $db->connectdb();
    }

    public function generateToken(){
        $email = $this->validateParameter("email",$this->param['email'],STRING);
        $pass = $this->validateParameter("pass",$this->param['pass'],STRING);

        $stmt = $this->conn->prepare("select * from users where email=:email and password=:pass");
        $stmt->bindParam(":email",$email);
        $stmt->bindParam(":pass",$pass);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(empty($user))
        {
            $this->returnResponse(200,["error"=>"The user email or password is not valid. Please try again."]);
        }

        if($user["status"]==0)
        {
            $this->returnResponse(200,["error"=>"The user is not valid. Please connect with Administrator."]);
        }

        $payload = [
            'userid'=>$user["id"],
        ];

        $token = JWT::encode($payload,"sharad123","HS256");
       
        $this->returnResponse(200,[
            "token"=>$token
        ]);

    }

    public function getCustomers()
    {
        $token = $this->getBearerToken();
        try{
            $validToken = JWT::decode($token,"sharad123",['HS256']);
            print_r($validToken); exit;
        } catch(Execption $e){
            $this->returnResponse(200,["error"=>$e->getMessage()]);
        }


        $stmt = $this->conn->prepare("select * from customers where status = 1");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($customers){
            $this->returnResponse(200,["data"=>$customers]);
        }
        else{
            $this->returnResponse(200,["error"=>"No customer found."]);
        }
    }
}