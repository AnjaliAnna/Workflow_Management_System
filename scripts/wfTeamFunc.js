var gcvFlow = null;
var goFlow = null;
var giDefaultHeight = 80

function wfInit() {
    try {
        //Get the flowchart canvas
        gcvFlow = document.getElementById('canvasFlow');
        //Create the flowchart control
        goFlow = new Lassalle.Flow(gcvFlow);

        //Background colour of the flowchart control
        goFlow.fillStyle = 'rgb(255,255,190)';//'#efe';
        goFlow.refresh();
        //The node line colour
        goFlow.nodeModel.strokeStyle = 'rgb(0,0,0)';// '#00f';
        //Node text colour
        goFlow.nodeModel.textFillStyle = 'rgb(255,0,0)';//#00f';
        //Node fill colour
        goFlow.nodeModel.fillStyle = 'rgb(190,255,255)';//#ff8';
        //default shape family. This can draw both 'Process' and 'decisions'
        goFlow.nodeModel.shapeFamily = "polygon";
        //link colour
        goFlow.linkModel.strokeStyle = 'rgb(0, 0, 255)';
        //Link text colour
        goFlow.linkModel.textFillStyle = 'rgb(255, 0, 255)';//'#f00';
        //Show grids in the background to help draw better.
        goFlow.gridDraw = true;
        
        goFlow.refresh();

        getWorkflows(0);
    }
    catch (err) {
        alert("Error while loading flow : " + err.innerHTML);
    }
}

//Get the lit os workflows or details of one workflow
function getWorkflows(iID)
{
    try
    {
        var apiParamData = {};
        var thisWF = null;
        var oNode=null;
        var oLink = null;
        var oPoint = null;
        var iLCount=0;
        apiParamData.WF_ID = iID;
        
        clearWorkflow();

        $.post('../api/wfHandler.php', {apiCommand:'GET',apiParam:JSON.stringify(apiParamData)}, 
        function(data) 
        {
            var option=null;
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
                    if (iID ==0) //Showing list of workflows
                    {
                        var eSelect = document.getElementById("wfList");
                        option = document.createElement('option');
                            option.value = 0;
                            option.text = '';
                        eSelect.add(option);

                        var iCount = data.data.length;
                        for(var i = 0; i<iCount; i++) {
                            option = document.createElement('option');
                            option.value = data.data[i].WF_ID;
                            option.text = data.data[i].WF_NAME;
                            eSelect.add(option);
                        }
                    }
                    else //Displaying a workflow
                    {
                        if (goFlow != null)
                        {
                            
                            //alert (data.data[0].WF_STRING);
                            var oWorkflowStr = JSON.parse(data.data[0].WF_STRING);
                            //alert (JSON.parse(oWorkflowStr[0]).type);
                            var iCount = oWorkflowStr.length;
                            //alert(iCount);
                            for(var i = 0; i<iCount; i++) {
                                thisWF = JSON.parse(oWorkflowStr[i]);
                                if (thisWF.type == 'NODE'){
                                    //Create a new node (by default rectangle is created because the goFlow.nodeModel.shapeFamily is set to 'ploygon').
                                    oNode = goFlow.addNode(thisWF.left, thisWF.top, thisWF.width, thisWF.height, thisWF.text);
                                    if (oNode != null && thisWF.polygon != null)
                                    {
                                        oNode.polygon = thisWF.polygon;
                                    }
                                }
                                else if(thisWF.type == 'LINK'){
                                    oLink = goFlow.addLink(goFlow.getItems()[thisWF.orgNodeIndex], goFlow.getItems()[thisWF.dstNodeIndex], "");
                                    if (oLink != null && thisWF.points!=null && thisWF.points.length>0)
                                    {
                                        //The first and last points are to be skipped as these are points on the source and destination nodes -which should not be added
                                        iLCount = thisWF.points.length-1;
                                        for (j=1;j<iLCount; j++)
                                        {
                                            oLink.addPoint(thisWF.points[j].x, thisWF.points[j].y);
                                        }
                                    }
                                }              
                            }
                            goFlow.refresh();
                        }

                    }
                }
            }
        });
    }
    catch (err) {
        alert("Error while loading flow : " + err.innerHTML);
    }
}