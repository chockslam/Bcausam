<<?php
function getConnection() 
{
    try {
        //Line for connecting to the DB
        $connection = new PDO("mysql:host=localhost;dbname=dbs1853699", "679480077", "My2b4bies!");

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $connection;

    //Catch for connection error
    }catch (Exception $e) {
        throw new Exception("Connection error ". $e->getMessage(), 0, $e);
    }
}
?>
