// JavaScript source code
var gcvFlow = null;
var goFlow = null;
var giDefaultHeight = 80

var canvas, flow;

function initPage() { //called when body is loaded
    try {
        //Get the flowchart canvas
        gcvFlow = document.getElementById('canvasFlow');
        //Create the flowchart control
        goFlow = new Lassalle.Flow(gcvFlow);//external software component used as diagramming tool

        //Background colour of the flowchart control
        goFlow.fillStyle = 'rgb(255,255,255)';//'#efe';
        goFlow.refresh();
        //The node line colour
        goFlow.nodeModel.strokeStyle = 'rgb(0,0,0)';// '#00f';
        //Node text colour
        goFlow.nodeModel.textFillStyle = 'rgb(0,0,0)';//#00f';
        //Node fill colour
        goFlow.nodeModel.fillStyle = 'rgb(255,255,255)';//#ff8';
        //default shape family. This can draw both 'Process' and 'decisions'
        goFlow.nodeModel.shapeFamily = "polygon";
        //link colour
        goFlow.linkModel.strokeStyle = 'rgb(0, 0, 0)';
        //Link text colour
        goFlow.linkModel.textFillStyle = 'rgb(0, 0, 0)';//'#f00';
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
        //url data function datatype expected by server
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
                                thisWF = JSON.parse(oWorkflowStr[i]);//.parse exchanges data to and from wweb server
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

//Create a node. [parameters] sText (string): caption of the node. sType (string): 'rectangle' or 'polygon'
function addNode(sText, sType) {
    var oNode = null;
    try {
        if (goFlow != null) {
            //Create a new node (by default rectangle is created because the goFlow.nodeModel.shapeFamily is set to 'ploygon').
            oNode = goFlow.addNode(10, 10, giDefaultHeight, giDefaultHeight, sText);
            //LEARNING: Never try to set like this: oNode.shapeFamily = sType; -It will make the node unselectable
            //if the type os 'polygon', change the rectangle to a polygon.
            if (sType == 'polygon') {
                oNode.polygon = [[oNode.getLeft(), oNode.getTop() - (oNode.getHeight() / 2)],
                [oNode.getLeft() + (oNode.getWidth() / 2), oNode.getTop() - oNode.getHeight()],
                [oNode.getLeft() + oNode.getWidth(), oNode.getTop() - (oNode.getHeight() / 2)],
                [oNode.getLeft() + (oNode.getWidth() / 2), oNode.getTop()]];
            }

            goFlow.refresh();
        }
    }
    catch (err) {
        alert(err.innerHTML);
    }
}
//update the text in a node or link. [parameters] sText (string): caption of the node/link.
function updateNode(sText) {
    try {
        if (goFlow != null) {
            //get the current item selected
            var selItems = goFlow.getSelectedItems();
            //allow single item
            if (selItems.length == 1) {
                selItems[0].text = sText; //Update the caption
                selItems[0].refresh();
            }
            else {
                alert('Please select a single item');
            }
        }
    }
    catch (err) {
        alert(err.innerHTML);
    }
}

function removeSelection() {
    try {
        if (goFlow != null) {
            //get the current item selected
            var selItems = goFlow.getSelectedItems();
            if (selItems.length > 0) {
                goFlow.deleteSel();
            }
            else {
                alert('Nothing selected!?');
            }
        }
    }
    catch (err) {
        alert(err.innerHTML);
    }
}

function clearWorkflow()
{
    try{
        if (goFlow != null) {
            var oItems = goFlow.getItems();
            if (oItems != null)
            {
                for (var iCtr in oItems)
                {
                    if (goFlow.isNode(oItems[iCtr])) //Item is a NODE
                    {
                        oItems[iCtr].setIsSelected(true);
                    }
                }
                goFlow.deleteSel();
            }
        }
    }
    catch (err) {
        alert(err.innerHTML);
    }
}

function SaveWorkflow(){
    var oNode = null;
    var oLink = null;
    var sItems = [];
    var sName = null;
    var apiParamData = {};
    try{
        if (goFlow != null) {
            sName = document.getElementById('wfName').value;
            var oItems = goFlow.getItems();
            if (oItems != null)
            {
                apiParamData.WF_NAME = $('#wfName').val();
                //sItems.push( JSON.stringify({"type":"NAME","wfName":sName}));
                for (var iCtr in oItems)
                {
                    oNode = null;
                    oLink = null;
                    if (goFlow.isNode(oItems[iCtr])) //Item is a NODE
                    {
                        oNode = new cWorkflowNode(oItems[iCtr]);
                        sItems.push(JSON.stringify(oNode));
                    }
                    else    //item is a LINK
                    {
                        oLink = new cWorkflowLink(oItems[iCtr]);
                        sItems.push(JSON.stringify(oLink));
                    }
                }
                if (sItems != null && sItems.length > 0)
                {
                    apiParamData.WF_DATA = sItems;
                    $.post('../api/wfHandler.php', {apiCommand:'CREATE',apiParam:JSON.stringify(apiParamData)}, 
                    function(data) 
                    {
                        alert (data)
                        /*
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
                            window.location.href = data.data[0];
                        }
                        */
                    });
                }
            }
        }

    }
    catch (err) {
        alert(err.innerHTML);
    }
    finally{
        oItems= null;
    }
}

function cWorkflow ()
{
    this.nodes = [];
    this.links = [];
}

function cWorkflowNode(oNode)
{
    this.type = 'NODE';
    this.left = oNode.getLeft();
    this.top = oNode.getTop();
    this.width = oNode.getWidth();
    this.height = oNode.getHeight();
    if (oNode.polygon != null)
        this.polygon = oNode.polygon;
    this.text = oNode.text;
    this.nodeIndex = oNode.index;
}
function cWorkflowLink(oLink)
{
    this.type = 'LINK';
    this.orgNodeIndex = oLink.org.index;
    this.dstNodeIndex = oLink.dst.index;
    this.points = oLink.points;
}

//------------------------------------------------------
//--------------NOT USED-------------------------------
//------------------------------------------------------
function createDiagram() {
    canvas = document.getElementById('canvasFlow');
    flow = new Lassalle.Flow(canvas);

    flow.fillStyle = 'rgb(255,255,190)';//'#efe';
    flow.refresh();

    flow.nodeModel.strokeStyle = 'rgb(0,0,0)';// '#00f';
    flow.nodeModel.textFillStyle = 'rgb(255,0,0)';//#00f';
    flow.nodeModel.fillStyle = 'rgb(190,255,255)';//#ff8';
    flow.nodeModel.shapeFamily = "rectangle";

    flow.linkModel.strokeStyle = 'rgb(0, 0, 255)';
    flow.linkModel.textFillStyle = 'rgb(255, 0, 255)';//'#f00';

    /*if (flow.taskManager.canUndoRedo) {
        flow.taskManager.beginAction("creatediagram");
    }*/

    var node0 = flow.addNode(50, 20, 80, 80, "node 0");
    //node0.fillShape = fillPredefinedProcess;

    //var node1 = flow.addNode(200, 250, 80, 80, "node 1");
    //node1.shapeFamily = "ellipse";

    //var link01 = flow.addLink(node0, node1, "link 01");
    //link01.addPoint(90, 170);

   // var node2 = flow.addNode(350, 120, 80, 80, "node 2");
   // node2.fillStyle = '#f88';
   // node2.shapeFamily = "other";
    //node2.drawShape = drawTerminator;

    //var link12 = flow.addLink(node1, node2, "link 12");
    //link12.setLineStyle("bezier");

    flow.refresh();

    /*if (flow.taskManager.canUndoRedo) {
        flow.taskManager.endAction();
    }*/
}

