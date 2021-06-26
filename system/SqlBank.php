<?php
class SqlBank
{
    public static function getSQL($sqlID)
    {
        switch ($sqlID)
        {
            case "SQL_USERS_001":
                return 'select password from USERS where EMAIL_ID = :EMAIL_ID';
                break;
            case "SQL_ORG_001": //get organisation detail based on owner
                return 'select ORG_ID,ORG_NAME,ORG_DETAILS,REQ_BY_EMAIL_ID,STATUS from ORGANISATIONS
                 where REQ_BY_EMAIL_ID = :REQ_BY_EMAIL_ID';
                break;
            case "SQL_ORG_002": //Get list of organisations
                return 'select ORG_ID, ORG_NAME, ORG_DETAILS, REQ_BY_EMAIL_ID, STATUS from ORGANISATIONS order by ORG_NAME';
                break;
            case "SQL_ORG_003": ////get organisation detail based on ID
                return 'select ORG_ID, ORG_NAME, ORG_DETAILS, REQ_BY_EMAIL_ID, STATUS from ORGANISATIONS where ORG_ID = :ORG_ID';
                break;

            case "SQL_WF_001":
                return 'select WF_ID, WF_NAME, STATUS from WF_MST order by WF_NAME';
                break;
            case "SQL_WF_002":
                return 'select WF_ID, WF_NAME, STATUS, WF_STRING from WF_MST where WF_ID = :WF_ID';
                break;
            default:
                throw new Exception ('Program error: Wrong SQL ID. System halted');
        }
    }
}

class DmlBank
{
    public static function getDML($sqlID)
    {
        switch ($sqlID)
        {
            case "DML_ORG_001":
                return 'insert into ORGANISATIONS (ORG_ID,ORG_NAME,ORG_DETAILS,REQ_BY_EMAIL_ID,STATUS) values (:ORG_ID,:ORG_NAME,:ORG_DETAILS,:REQ_BY_EMAIL_ID,:STATUS)';
                break;
            case "DML_ORG_002": //Approve / depricate
                return 'update ORGANISATIONS set STATUS = :STATUS where ORG_ID = :ORG_ID'; 
                break;
            case "DML_WF_001": //create workflow.
                return 'insert into WF_MST (WF_ID, WF_NAME, STATUS, WF_STRING) values (:WF_ID, :WF_NAME, :STATUS,:WF_STRING)';
                break;
            case "DML_WF_002": //create stage
                return 'insert into WF_MST_STG(STG_ID, WF_ID, STG_INDEX, STG_TEXT, STG_LEFT, STG_TOP, STG_WIDTH, STG_HEIGHT) values (:STG_ID, :WF_ID, :STG_INDEX, :STG_TEXT, :STG_LEFT, :STG_TOP, :STG_WIDTH, :STG_HEIGHT)';
                break;
            case "DML_WF_003": //create link
                return 'insert into WF_MST_LNK(LNK_ID, WF_ID, ORG_INDEX, DST_INDEX) values (:LNK_ID, :WF_ID, :ORG_INDEX, :DST_INDEX)';
                break;
            case "DML_WF_004": //approve workflow
                return 'update WF_MST set STATUS = 1 where WF_ID = :WF_ID';
                break;
            case 'DML_USR_001': //add user
                return 'insert into USERS (USER_ID, ORG_ID, USER_NAME, EMAIL_ID, PASSWORD, USER_TYPE) values (:USER_ID, :ORG_ID, :USER_NAME, :EMAIL_ID, :PASSWORD, :USER_TYPE)';
                break;
            default:
                throw new Exception ('Program error: Wrong SQL ID. System halted');
        }
    }
}
