<?php
include_once 'SqlBank.php';
class Database
{

    private $conn = null;
    private $bTrans = false;

    private function connect()
    {
        try{
            //Open the connection
            //$this->conn =  new PDO('mysql:host=localhost:3306;dbname=iFlow','root','RetePuja@2018');
            $this->conn =  new PDO('mysql:host=localhost:3306;dbname=iFlow','anjali','admin');
            //$this->conn = new PDO("sqlsrv:Server=AJUS\AJUSSQLXPRESS;Database=iFlow", "sa", "sa@2018");
            if ($this->conn)
            {
                //raise exception on error
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                //Fetch results as objects - not as array.
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ); //Fetch results as objects -not as arrays.
                //get the sql statements library ready.
                //$this->sqlBank = new SqlBank();

            }
            else
            {
                throw new Exception('Failed to connect to the database. System halted.');
            }
        }
        finally{
        }
    }
    public function __destruct()
    {
        try
        {
            if ($this->bTrans)
            {
                $this->bTrans = false;
                $this->conn->rollBack();
            }
        }
        catch(Exception $eX)
        {
            //Ignore
        }
        finally
        {
            //When destroying, close the connection and SQL bank
            $this->conn = null;
        }
    }

    public function dbTrans($sCode)
    {
        $retValue = array();
        $retValue['status'] = -1;
        $retValue['count'] = 1;
        $retValue['data'] = ['Unknown Error'];

        try
        {
            if ($sCode == 'B')
            {
                if ($this->bTrans == true)
                {
                    $retValue['status'] = 2;
                    $retValue['count'] = 1;
                    $retValue['data'] = ['There is another trnsaction in progress. New one not created.'];
                }
                else
                {
                    $this->bTrans = $this->conn->beginTransaction();
                    if ($this->bTrans == true)
                    {
                        $retValue['status'] = 1;
                        $retValue['count'] = 1;
                        $retValue['data'] = ['DB Transaction started.'];
                    }
                    else
                    {
                        $retValue['status'] = -1;
                        $retValue['count'] = 1;
                        $retValue['data'] = ['Failed to start database transaction'];
                    }
                }
            }
            else if ($sCode == 'C')
            {
                if ($this->bTrans == true)
                {
                    if ($this->conn->commit() == true)
                    {
                        $this->bTrans = false;
                        $retValue['status'] = 1;
                        $retValue['count'] = 1;
                        $retValue['data'] = ['DB Transaction Committed.'];
                    }
                    else
                    {
                        $this->conn->rollBack();
                        $this->bTrans = false;
                        $this->conn = null;
                        $retValue['status'] = -1;
                        $retValue['count'] = 1;
                        $retValue['data'] = ['Failed to commit transaction. Connection closed.'];
                    }
                }
                else
                {
                    $retValue['status'] = 0;
                    $retValue['count'] = 1;
                    $retValue['data'] = ['No transactions pending. nothing committed.'];
                }
            }
            else if ($sCode == 'R')
            {
                if ($this->bTrans == true)
                {
                    if ($this->conn->rollBack() == true)
                    {
                        $this->bTrans = false;
                        $retValue['status'] = 1;
                        $retValue['count'] = 1;
                        $retValue['data'] = ['DB Transaction Rolled back.'];
                    }
                    else
                    {
                        $this->bTrans = false;
                        $this->conn = null;
                        $retValue['status'] = -1;
                        $retValue['count'] = 1;
                        $retValue['data'] = ['Failed to roll back transaction. Connection closed.'];
                    }
                }
                else
                {
                    $retValue['status'] = 0;
                    $retValue['count'] = 1;
                    $retValue['data'] = ['No transactions pending. nothing rolled back.'];
                }
            }
            return $retValue;
        }
        catch(Exception $eX)
        {
            $this->bTrans = false;
            $this->conn = null;
            $retValue['status'] = -1;
            $retValue['count'] = 1;
            $retValue['data'] = ['Unexpected error occured. Database connection closed. Error : '. $eX->getMessage()];
            return $retValue;
        }
        finally
        {

        }
    }


    public function getData($sqlID, $param)
    {
        $retValue = null;
        try{
            //In this session, if the connection is not opened, open it. It is possible to re-ue it for subsequent calls of getData
            if (! $this->conn)
            {
                $this->connect();
            }
            //prepare and execute the SQL
            $stmt = $this->conn->prepare(sqlBank::getSql($sqlID));
            if ($param!=null)
                $stmt->execute($param);
            else
                $stmt->execute();
            if ($stmt)
            {
                //catch the count of results and the result into an array to return
                $retValue = array();
                $retValue['status'] = 1;
                $retValue['count'] = $stmt->rowCount();
                $retValue['data'] = $stmt->fetchAll();
            }
            else
            {
                $retValue = array();
                $retValue['status'] = 0;
                $retValue['count'] = 1;
                $retValue['data'] = ['SQL failed. Data not read.'];
            }
            return ($retValue);
        }
        finally{
            $stmt = null;
        }
    }

    //Description: Fetch on (or a block of) new unique ID from the config table.
    //Parameters: $numID = the number of IDs required.
    //returns: the first ID in the alloted block
    public function getNewID($numID)
    {
        $retValue = -1;
        try{
            //In this session, if the connection is not opened, open it. It is possible to re-ue it for subsequent calls of getData
            if (! $this->conn)
            {
                $this->connect();
            }
            //read the current value of next id and increment it by one
            $stmt = $this->conn->prepare('SELECT NEXT_ID FROM SYS_CONFIG; UPDATE SYS_CONFIG SET NEXT_ID=NEXT_ID+'. strval($numID));
            $stmt->execute();
            if ($stmt)
            {
                $nextID = $stmt->fetch();
                $retValue=$nextID->NEXT_ID;
            }
            return ($retValue);
        }
        finally{
            $stmt = null;
        }
    }

    public function putData($sqlID, $param)
    {
        $retValue = null;
        try{
            //In this session, if the connection is not opened, open it. It is possible to re-ue it for subsequent calls of getData
            if (! $this->conn)
            {
                $this->connect();
            }
            //prepare and execute the SQL
            $stmt = $this->conn->prepare(dmlBank::getDML($sqlID));
            $stmt->execute($param);
            if ($stmt)
            {
                //catch the count of results and the result into an array to return
                $retValue = array();
                $retValue['status'] = 1;
                $retValue['count'] = $stmt->rowCount();
            }
            else
            {
                $retValue = array();
                $retValue['status'] = 0;
                $retValue['count'] = 1;
                $retValue['data'] = ['SQL failed. Data not updated.'];
            }
            return ($retValue);
        }
        finally{
            $stmt = null;
        }
    }
}