<?php

class DbConnect {

    private $host = "localhost";
    private $dbname = "jwtapi";
    private $username = "root";
    private $password = "";

    public function connectdb()
    {
        try{
            $conn = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->username,$this->password);
            return $conn;
        }
        catch(Exception $e)
        {
            die("Database error: ".$e->getMessage());
        }

    }
}