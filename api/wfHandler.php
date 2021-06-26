<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
//Includes
include_once '..\System\Workflow.php';

$retValue = null;
$oWF = null;
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

    $apiParam = json_decode($apiParam);

    if ($apiCommand != '')
    {
        if ($apiCommand == 'CREATE')
        {
            $oWF = new Workflow();
            $retValue = $oWF->createWorkflow($apiParam);
        }
        elseif ($apiCommand == 'APPROVE')
        {
            $oWF = new Workflow();
            $retValue = $oWF->approveWorkflow($apiParam);
        }
        elseif ($apiCommand == 'GET')
        {
            $oWF = new Workflow();
            $retValue = $oWF->getWorkflow($apiParam);
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
    $oWF = null;
}
