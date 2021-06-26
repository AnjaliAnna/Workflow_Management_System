<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
//Includes
include_once '..\System\Database.php';

$oDB = null;
$retValue = null;
try{
    //! This is funny:?
    //The $_POST will not fetch parameters when received from POSTMAN but $_GET will
    //The $_GET will not fetch parameters when received from AJAX but $_POST will

    if (isset($_POST) && sizeof($_POST)>0)
    {
        $sName = $_POST['loginName'];
        $sPass = $_POST['password'];
    }
    else if (isset($_GET) && sizeof($_GET)>0)
    {
        $sName = $_GET['loginName'];
        $sPass = $_GET['password'];
    }
    else
    {
        //$_POST = json_decode(file_get_contents('php://input'), true);
        $sName = '';
        $sPass = '';
    }
   
    //! Do not use like this: this was not getting the param values - always null
//        $sName = $_POST['loginName'];
//        $sPass = $_POST['password'];
    //$sName = isset($_GET['loginName']) ? $_GET['loginName'] : null;
    //$sPass = isset($_GET['password']) ? $_GET['password'] : null;
    if ($sName != '')
    {
        //Instantiate the database class
        $oDB = new Database();
        //get login nameinto the param array - it is used in the SQL
        $param = [$sName];
        //Get user data
        $acDet =  $oDB->getData('SQL_USERS_001',$param);
        if ($acDet)
        {
            if ($acDet['count']>0)
            {
                //Compare the passwords
                if ($acDet['data'][0]->password == $sPass)
                {
                    //Redirect when success
                    //header("Location: http://www.google.com"); /* Redirect browser */
                    $retValue = array();
                    $retValue['status']=1;
                    $retValue['count']=1;
                    $retValue['data']=['..\pages\home.html'];
                }
                else{ //Wrong password
                    $retValue = array();
                    $retValue['status']=0;
                    $retValue['count']=1;
                    $retValue['data']=['Inalid Password'];
                }
            }
            else //No data for the specified login name
            {
                $retValue = array();
                $retValue['status']=0;
                $retValue['count']=1;
                $retValue['data']=['Inalid login name'];
            }
        }
        else //Nothing fetched from table
        {
            $retValue = array();
            $retValue['status']=0;
            $retValue['count']=1;
            $retValue['data']=['Login failed'];
        }
    }
    else //Login name not specified.
    {
        $retValue = array();
        $retValue['status']=0;
        $retValue['count']=1;
        $retValue['data']=['Login name not specified. Login failed'];
    }
    echo json_encode($retValue);
}
catch(Exception $eX)
{
    $retValue = array();
    $retValue['status']=0;
    $retValue['count']=1;
    $retValue['data']=['Unexpected Error : '.$eX->getMessage()];
    echo json_encode($retValue);
}
finally
{
    $oDB = null;
}
