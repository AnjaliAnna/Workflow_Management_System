<?php
include_once 'SqlBank.php';
include_once 'Database.php';
class Organisations
{
    private $oDB = null;
    public function addRequest($orgData)
    {
        $param = null;
        $retValue = null;
        $iDBStatus = 0;//This flag is used to set DB to null.(1=db opened in this function, 0=DB already open )
        try
        {
            //echo json_encode($orgData);
            //exit();
            if ($this->oDB == null)
            {
                //Instantiate the database class
                $this->oDB = new Database();
                $iDBStatus = 1; //DB is opened in this function. hence close it as well in this function
            }
            //Check whether already associated with another organisation
            $param = ['REQ_BY_EMAIL_ID'=>$orgData->REQ_BY_EMAIL_ID];
            $ret =  $this->oDB->getData('SQL_ORG_001',$param);
            if ($ret)
            {
                if ($ret['count']>0)
                {
                    $retValue = array();
                    $retValue['status']=0;
                    $retValue['count']=1;
                    $retValue['data']=['Specified email address id already associated with another organisation. Organisation request not created.'];    
                }
                else
                {
                    //Generate a new organisation ID
                    $orgID = $this->oDB->getNewID(1);
                    $param = ['ORG_ID'=>$orgID,'ORG_NAME'=>$orgData->ORG_NAME,
                    'ORG_DETAILS'=>$orgData->ORG_DETAILS,'REQ_BY_EMAIL_ID'=>$orgData->REQ_BY_EMAIL_ID, 
                    'STATUS'=>0];
                    $ret = $this->oDB->putData('DML_ORG_001',$param);
                    if ($ret!= null && $ret['count']>0)
                    {
                        $retValue = array();
                        $retValue['status']=1;
                        $retValue['count']=1;
                        $retValue['data']=['Organisation request created. You will receive an email when done'];
                    }
                }
            }
            else
            {
                $retValue = array();
                $retValue['status']=0;
                $retValue['count']=1;
                $retValue['data']=['Failed to validate request.  Organisation request not created.'];
            }
            return $retValue;
        }
        finally
        {
            $param = null;
            $ret = null;
            if ($iDBStatus == 1)
                $oDB = null;
        }
    }

    public function getOrganisations($orgData)
    {
        $param = null;
        //Initialise the return value
        $retValue = array();
        $retValue['status']=0; 
        $retValue['count']=1;
        $retValue['data']=['Unknown error.'];
        $iDBStatus = 0;//This flag is used to set DB to null.(1=db opened in this function, 0=DB already open )
        try
        {
            if ($this->oDB == null)
            {
                //Instantiate the database class
                $this->oDB = new Database();
                $iDBStatus = 1; //DB is opened in this function. hence close it as well in this function
            }
            //If a specific Organisation ID is not specified, get all.
            if ($orgData->ORG_ID == 0)
            {
                $param = null;
                $ret =  $this->oDB->getData('SQL_ORG_002',$param);
                if ($ret!= null && $ret['status']==1)
                    $retValue['status']=1;
                $retValue['data'] = $ret['data'];
            }
            else
            {
                $param = ['ORG_ID'=>$orgData->ORG_ID];
                $ret =  $this->oDB->getData('SQL_ORG_003',$param);
                if ($ret!= null && $ret['status']==1)
                    $retValue['status']=1;
                $retValue['data'] = $ret['data'];
            }
            return $retValue;
        }
        finally
        {
            if ($iDBStatus == 1)
                $oDB = null;
        }
    }

    public function setStatus($orgData)
    {
        $param = null;
        //Initialise the return value
        $retValue = array();
        $retValue['status']=0; 
        $retValue['count']=1;
        $retValue['data']=['Unknown error.'];
        $iDBStatus = 0;//This flag is used to set DB to null.(1=db opened in this function, 0=DB already open )
        try
        {
            if ($this->oDB == null)
            {
                //Instantiate the database class
                $this->oDB = new Database();
                $iDBStatus = 1; //DB is opened in this function. hence close it as well in this function
            }

            if ($orgData->ORG_ID > 0 && $orgData->STATUS >0 && $orgData->STATUS <3) //status can b switched between 1 and 2
            {
                //generate a new ID for the user -as a new user account is to be created when approving an organisation
                if ($orgData->STATUS == 1)
                    $userID = $this->oDB->getNewID(1);
                else
                    $userID= 0;

                //Get the current status
                $param = ['ORG_ID'=>$orgData->ORG_ID];
                $ret =  $this->oDB->getData('SQL_ORG_003',$param);
                if ($ret!= null && $ret['count']>0)
                {
                    //Preserve the current status for constructing the email
                    $curStatus = $ret['data'][0]->STATUS;
                    $emailID = $ret['data'][0]->REQ_BY_EMAIL_ID;
                    $atPos  = stripos($emailID, '@', 0); 
                    if($atPos>0)
                        $userName = substr($emailID, 0,$atPos);
                    else
                        $userName = '';

                    if ($curStatus != $orgData->STATUS && $userName!='')
                    {
                        $param = ['STATUS'=>$orgData->STATUS,'ORG_ID'=>$orgData->ORG_ID];
                        $ret = $this->oDB->putData('DML_ORG_002',$param);
                        if ($ret!= null && $ret['status']>0)
                        {
                            //When approvig the org, create a user
                            if ($orgData->STATUS ==1)
                            {
                                
                                $param = ['USER_ID'=>$userID,'ORG_ID'=>$orgData->ORG_ID,'EMAIL_ID'=>$emailID, 'USER_NAME'=>$userName, 'PASSWORD'=>$userName, 'USER_TYPE'=>1];
                                $ret = $this->oDB->putData('DML_USR_001',$param);
                                if ($ret!= null && $ret['status']>0)
                                {
                                    //! Handle the emailing to owner here.
                                    //-----
                                    $retValue['status']=1;
                                    $retValue['data']=['Organisation status updated.'];
                                }
                                else
                                {
                                    $retValue['status']=0;
                                    $retValue['data']=['Organisation created but failed to create user'];
                                }
                            }
                            else
                            {
                                $retValue['status']=1;
                                $retValue['data']=['Organisation status updated.'];
                            }
                        }
                    }
                    else
                    {
                        $retValue['status']=0;
                        $retValue['data']=['Status is already the same or Invalid email ID. Nothing changed'];
                    }
                }
                else
                {
                    $retValue['status']=0;
                    $retValue['data']=['Invalid Organisation ID'];
                }
            }
            else
            {
                $retValue['status']=0;
                $retValue['data']=['Invalid inputs. Nothing updated'];
            }
            return $retValue;
        }
        finally
        {
            if ($iDBStatus == 1)
                $oDB = null;
        }
    }
}
