<?php
include_once 'SqlBank.php';
include_once 'Database.php';
class Workflow
{
    private $oDB = null;
    public function createWorkflow($wfData)
    {
        $param = null;
        //Initialise the return value
        $retValue = array();
        $retValue['status']=0; 
        $retValue['count']=1;
        $retValue['data']=['Unknown error.'];

        $iTransStatus = 0; //Set this to denote no db transactions started yet.
        $item = null;
        $wfID = 0;
        try
        {
            if ($this->oDB == null)
            {
                //Instantiate the database class
                $this->oDB = new Database();
            }

            //Validate workflow data
            $validate = $this->wfValidate($wfData);
            if ($validate['status'] == 1)
            {
                //Generate required number of IDs
                $iNumID = 1+sizeof($wfData->WF_DATA); //one for workflow and one each for each stage and link
                $wfID = $this->oDB->getNewID($iNumID);
                if ($wfID>0)
                {
                    //Start a database transaction
                    $ret = $this->oDB->dbTrans('B');
                    if ($ret['status'] > 0) //Transaction succesful?
                    {
                        //When starting db transactions, if a db transaction is already existing, a new one
                        //would not have begun. in such cases, it should not be committed in this function. 
                        //this flag is used to decide this.
                        $iTransStatus = $ret['status'];

                        //Create the workflow
                        $wfMasterID = $wfID++;
                        $param = ['WF_ID'=>$wfMasterID, 'WF_NAME'=>$wfData->WF_NAME, 'STATUS'=>0, 'WF_STRING'=> json_encode($wfData->WF_DATA)];
                        $ret = $this->oDB->putData('DML_WF_001',$param);
                        if ($ret!= null && $ret['status']==1)
                        {
                            //Iterate through the collection of nodes and links to save them
                            //WF_DATA is the array containing json strings representing each node and link 
                            $itemCount = sizeof($wfData->WF_DATA);
                            for ($i=0; $i < $itemCount; $i++)
                            {
                                //get the json string in the array to a node/ link object
                                $item = json_decode($wfData->WF_DATA[$i]);

                                if($item->type == 'NODE')
                                {
                                    //Assign values to the SQL parameters for inserting a node
                                    $param = ['STG_ID'=>$wfID++, 'WF_ID'=>$wfMasterID,
                                    'STG_INDEX'=>$item->nodeIndex, 'STG_TEXT'=>$item->text,
                                    'STG_LEFT'=>$item->left, 'STG_TOP'=>$item->top,
                                    'STG_WIDTH'=>$item->width, 'STG_HEIGHT'=>$item->height];
                                    //Execute the sql
                                    $ret = $this->oDB->putData('DML_WF_002',$param);
                                    if ($ret== null || $ret['count']<1)
                                    {
                                        $retValue['data']=['failed to create one of the nodes. Please try again'];
                                        break;
                                    }
                                }
                                else //LINK
                                {
                                    //Assign values to the SQL parameters for inserting a node
                                    $param = ['LNK_ID'=>$wfID++, 'WF_ID'=>$wfMasterID,
                                    'ORG_INDEX'=>$item->orgNodeIndex, 'DST_INDEX'=>$item->dstNodeIndex];
                                    //Execute the sql
                                    $ret = $this->oDB->putData('DML_WF_003',$param);
                                    if ($ret== null || $ret['count']<1)
                                    {
                                        $retValue['data']=['failed to create one of the links. Please try again'];
                                        break;
                                    }
                                }
                            } //END Loop

                            //If all in the above loop succeeded, the loop counter should be equal to the size of the array.
                            //Otherwise, it would have exited from the loop during an error.
                            if ($i == $itemCount)
                            {
                                if ($iTransStatus == 1) //are We working on a local transaction which can be committed?
                                {
                                    $iTransStatus == 0; //to state that pending transaction is handled.
                                    $ret = $this->oDB->dbTrans('C'); //Commit the transaction
                                    if ($ret['status'] == 1)
                                    {
                                        $retValue['status']=1;
                                        $retValue['data']=['Workflow Saved.'];
                                        $oDB=null;
                                    }
                                    else
                                    {
                                        $retValue['data']=['Failed to update database. Workflow is not saved.'];
                                    }
                                } //ELSE: the transaction was not begun in this function. hence it should not be committed here.

                            } 
                            else //ELSE: error message is already furnished in the loop
                            {
                                if ($iTransStatus == 1) //are We working on a local transaction which can be rolled back?
                                {
                                    $iTransStatus == 0; //to state that pending transaction is handled.
                                    $ret = $this->oDB->dbTrans('R');
                                    $oDB=null;
                                }
                            }
                        }
                        else //failed to create workflow (WF_MST)
                        {
                            $retValue['data']=['Failed to create workflow.'];
                            if ($iTransStatus==1)//If there is a pending transaction created locally, it has to be rolled back.
                            {
                                $iTransStatus == 0; //to state that pending transaction is handled.
                                $ret = $this->oDB->dbTrans('R');
                                $oDB=null;
                            }
                        }
                    }
                    else
                    {
                        $retValue['data']=['Failed to start database transaction.'];
                    }
                }
                else
                {
                    $retValue['data']=['Failed to generate/ fetch identifier for the workflow.'];
                }    
            }
            else
            {
                $retValue = $validate;
            }
            return $retValue;
        }
        finally
        {
            //If there is a pending transaction created locally, it has to be rolled back.
            if ($iTransStatus==1 && $odb != null)
            {
                $ret = $this->oDB->dbTrans('R');
                $oDB=null;
            }
            $param = null;
            $ret = null;
        }        
    }

    private function wfValidate($wfData)
    {
        $sNodes = '';
        $item = null;
        $retValue = array();
        $retValue['status']=0;
        $retValue['count']=1;
        $retValue['data']=['Failed to validate workflow data. Unknown Error.'];
        $iCount = 0;

        //Is wf data empty?
        if ($wfData != null && sizeof($wfData->WF_DATA)>0)
        {
            $iCount = sizeof($wfData->WF_DATA);
            //check the workflow name
            if ($wfData->WF_NAME != '' )
            {
                //check whether stage names duplicate
                for ($i=0; $i<$iCount; $i++)
                {
                    $item = json_decode($wfData->WF_DATA[$i]);
                    //if ($wfData->type == 'NODE')
                    if ($item->type == 'NODE')
                    {
                        if (strpos($sNodes,$item->text)>0)
                            break;
                        else
                            $sNodes .= ','. $item->type;
                    }
                }

                if ($i == 0) //No data found
                {
                    $retValue['data']=['workflow is empty!'];
                }
                else if ($i<$iCount) //Full data not processed. duplication detected.
                {
                    $retValue['data']=['Multiple workflow stages cannot have the same name.'];
                }
                else
                {
                    $retValue['status']=1;
                    $retValue['data']=['Valid.'];
                }
            }
            else
            {
                $retValue['data']=['Workflow Name cannot be empty'];
            }
        }
        else
        {
            $retValue['data']=['Invalid Workflow Data. Workflow not saved.'];
        }
        return $retValue;
    }

    public function approveWorkflow($wfData)
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
            //approve the workflow
            $param = ['WF_ID'=>$wfData->WF_ID];
            $ret = $this->oDB->putData('DML_WF_004',$param);
            if ($ret!= null && $ret['status']==1)
            {
                $retValue['status']=1; 
                $retValue['data'] = ['Approved'];
            }
            else
            {
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

    public function getWorkflow($wfData)
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
            //If a specific workflow ID is not specified, get all.
            if ($wfData->WF_ID == 0)
            {
                $param = null;
                $ret =  $this->oDB->getData('SQL_WF_001',$param);
                if ($ret!= null && $ret['status']==1)
                    $retValue['status']=1;
                $retValue['data'] = $ret['data'];
            }
            else
            {
                $param = ['WF_ID'=>$wfData->WF_ID];
                $ret =  $this->oDB->getData('SQL_WF_002',$param);
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
}
