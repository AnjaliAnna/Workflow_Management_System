function orgRequest()
{
    var apiParam = {};
    var apiParamData = {};
    apiParamData.ORG_NAME = $('#orgName').val();
    apiParamData.ORG_DETAILS = $('#orgDetails').val();
    apiParamData.REQ_BY_EMAIL_ID = $('#orgEmail').val();
    //apiParam.COMMAND = 'ADD';
    //apiParam.PARAM = paramData;
    //alert (JSON.stringify(apiParam));
    $.post('../api/orgHandler.php', {apiCommand:'ADD',apiParam:JSON.stringify(apiParamData)}, 
    function(data) 
    {
        if (data.status != 1)
        {
            if (data.count>0)
            {
                alert(data.data[0]);
            }
            else
            {
                alert('Unknown Error!');
            }
        }
        else
        {
            alert(data.data[0]);
        }
    });
}

function getOrganisations(iOrgID)
{

    var apiParam = {};
    var apiParamData = {};
    apiParamData.ORG_ID = iOrgID;
    $.post('../api/orgHandler.php', {apiCommand:'GET',apiParam:JSON.stringify(apiParamData)}, 
    function(data) 
    {
        if (data.status != 1)
        {
            if (data.count>0)
            {
                $('#result').html(data.data[0]);
            }
            else
            {
                $('#result').html('Unknown Error!');
            }
        }
        else
        {
            if (data.count>0)
            {
                if (iOrgID ==0) //Showing list of organisations
                {
                    var tblOrg = document.getElementById("tblOrgList");
                    var iCount = data.data.length;
                    var oRow = null;
                    var oCell = null;
                    for(var i = 0; i<iCount; i++) {
                        oRow = tblOrg.insertRow();
                        oCell = oRow.insertCell(0);
                        oCell.innerHTML = data.data[i].ORG_ID;
                        oCell = oRow.insertCell(1);
                        oCell.innerHTML = data.data[i].ORG_NAME;
                        oCell = oRow.insertCell(2);
                        oCell.innerHTML = data.data[i].REQ_BY_EMAIL_ID;
                        oCell = oRow.insertCell(3);
                        oCell.innerHTML = data.data[i].ORG_DETAILS;
                        oCell = oRow.insertCell(4);
                        oCell.innerHTML = data.data[i].STATUS;
                    }
                }
                else
                {
                    //Getting a single org
                }
            }
            else
            {
                $('#result').html('No data found');
            }
        }
    });
}


function onClick_btnOrgApprove()
{
    var apiParam = {};
    var apiParamData = {};
    apiParamData.ORG_ID = $('#txtOrgID').val();
    apiParamData.STATUS = $('#txtStatus').val();
    $.post('../api/orgHandler.php', {apiCommand:'SET_STATUS',apiParam:JSON.stringify(apiParamData)}, 
    function(data) 
    {
        if (data.status != 1)
            {
                if (data.count>0)
                {
                    alert(data.data[0]);
                }
                else
                {
                    alert('Unknown Error!');
                }
            }
            else
            {
               alert(data.data[0]);
            }
    });

}

