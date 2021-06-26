<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
//Includes
include_once '..\System\Organisations.php';

$retValue = null;
$oOrg = null;
try{
    //! This is funny:?
    //The $_POST will not fetch parameters when received from POSTMAN but $_GET will
    //The $_GET will not fetch parameters when received from AJAX but $_POST will
    if (isset($_POST) && sizeof($_POST)>0)
    {
        $apiCommand = $_POST['apiCommand'];
        $apiParam = $_POST['apiParam'];
    }
    else if (isset($_GET) && sizeof($_GET)>0)
    {
        $apiCommand = $_GET['apiCommand'];
        $apiParam = $_GET['apiParam'];
    }
    else
    {
        $apiCommand='';
        $apiParam='';
    }
   
    if ($apiCommand != '')
    {
        if ($apiCommand == 'ADD')
        {
            $param = json_decode($apiParam);
            $oOrg = new Organisations();
            $retValue = $oOrg->addRequest($param);
        }
        else if ($apiCommand == 'GET')
        {
            $param = json_decode($apiParam);
            $oOrg = new Organisations();
            $retValue = $oOrg->getOrganisations($param);
        }
        else if ($apiCommand == 'SET_STATUS')
        {
            $param = json_decode($apiParam);
            $oOrg = new Organisations();
            $retValue = $oOrg->setStatus($param);
        }
    }
    else //command not specified.
    {
        $retValue = array();
        $retValue['status']=0;
        $retValue['count']=1;
        $retValue['data']=['No data specified. Request not created.'];
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
    $oOrg = null;
}
