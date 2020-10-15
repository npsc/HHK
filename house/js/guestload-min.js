function isNumber(e){"use strict";return!isNaN(parseFloat(e))&&isFinite(e)}var dtCols=[{targets:[0],title:"Date",data:"Date",render:function(e,t){return dateRender(e,t,dateFormat)}},{targets:[1],title:"Type",searchable:!1,sortable:!1,data:"Type"},{targets:[2],title:"Sub-Type",searchable:!1,sortable:!1,data:"Sub-Type"},{targets:[3],title:"User",searchable:!1,sortable:!0,data:"User"},{targets:[4],visible:!1,data:"Id"},{targets:[5],title:"Log Text",sortable:!1,data:"Log Text"}];function relationReturn(e){var t,a,i=$.parseJSON(e);i.error?(i.gotopage&&window.open(i.gotopage,"_self"),flagAlertMessage(i.error,"error")):i.success&&(i.rc&&i.markup&&((t=$("#acm"+i.rc)).children().remove(),a=$(i.markup),t.append(a.children())),flagAlertMessage(i.success,"success"))}function setupPsgNotes(e,t){return t.notesViewer({linkId:e,linkType:"psg",newNoteAttrs:{id:"psgNewNote",name:"psgNewNote"},alertMessage:function(e,t){flagAlertMessage(e,t)}}),t}function manageRelation(e,t,a,i){$.post("ws_admin.php",{id:e,rId:t,rc:a,cmd:i},relationReturn)}function paymentRefresh(){var e=$("#psgList").tabs("option","active");$("#psgList").tabs("load",e)}$(document).ready(function(){"use strict";var i,a,e,t,o,s,n=memberData,d=1,r="../admin/ws_gen.php?cmd=chglog&vw=vguest_audit_log&uid="+n.id;$.widget("ui.autocomplete",$.ui.autocomplete,{_resizeMenu:function(){var e=this.menu.element;e.outerWidth(1.1*Math.max(e.width("").outerWidth()+1,this.element.outerWidth()))}}),$("#divFuncTabs").tabs({collapsible:!0}),$("#vIncidentContent").incidentViewer({guestId:n.id,psgId:n.idPsg,alertMessage:function(e,t){flagAlertMessage(e,t)}}),useDocUpload&&$("#vDocsContent").docUploader({guestId:n.id,psgId:n.idPsg,alertMessage:function(e,t){flagAlertMessage(e,t)}}),$("#submit").dialog({autoOpen:!1,resizable:!1,width:300,modal:!0,buttons:{Exit:function(){$(this).dialog("close")}}}),$("#keysfees").dialog({autoOpen:!1,resizable:!0,modal:!0,close:function(){$("div#submitButtons").show()},open:function(){$("div#submitButtons").hide()}}),$("#pmtRcpt").dialog({autoOpen:!1,resizable:!0,modal:!0,title:"Payment Receipt"}),$("#faDialog").dialog({autoOpen:!1,resizable:!0,width:650,modal:!0,title:"Income Chooser"}),""!==rctMkup&&showReceipt("#pmtRcpt",rctMkup),""!==pmtMkup&&$("#paymentMessage").html(pmtMkup).show(),$(".hhk-view-visit").click(function(){var e=$(this).data("vid"),t=$(this).data("gid"),a=$(this).data("span");viewVisit(t,e,{"Show Statement":function(){window.open("ShowStatement.php?vid="+e,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+e+"&span="+a,"_blank")},Save:function(){saveFees(t,e,a,!1,"GuestEdit.php?id="+t+"&psg="+n.idPsg)},Cancel:function(){$(this).dialog("close")}},"Edit Visit #"+e+"-"+a,"",a),$("#divAlert1").hide()}),$("#resvAccordion").accordion({heightStyle:"content",collapsible:!0,active:!1,icons:!1}),$("div.hhk-relations").each(function(){var t=$(this).attr("name");$(this).on("click","td.hhk-deletelink",function(){0<n.id&&confirm($(this).attr("title")+"?")&&manageRelation(n.id,$(this).attr("name"),t,"delRel")}),$(this).on("click","td.hhk-newlink",function(){var e;0<n.id&&(e=$(this).attr("title"),$("#hdnRelCode").val(t),$("#submit").dialog("option","title",e),$("#submit").dialog("open"))})}),$("#cbNoVehicle").change(function(){this.checked?$("#tblVehicle").hide():$("#tblVehicle").show()}),$("#cbNoVehicle").change(),$("#btnNextVeh, #exAll, #exNone").button(),$("#btnNextVeh").click(function(){$("#trVeh"+d).show("fade"),4<++d&&$("#btnNextVeh").hide("fade")}),$("#divNametabs").tabs({beforeActivate:function(e,t){var a=$("#vvisitLog").find("table");"visitLog"===t.newTab.prop("id")&&0===a.length?$.post("ws_ckin.php",{cmd:"gtvlog",idReg:n.idReg},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error?(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,"error")):e.vlog&&$("#vvisitLog").append($(e.vlog))}}):"chglog"!==t.newTab.prop("id")||i||(i=$("#dataTbl").dataTable({columnDefs:dtCols,serverSide:!0,processing:!0,deferRender:!0,language:{search:"Search Log Text:"},sorting:[[0,"desc"]],displayLength:25,lengthMenu:[[25,50,100,-1],[25,50,100,"All"]],Dom:'<"top"ilf>rt<"bottom"ip>',ajax:{url:r}}))},collapsible:!0}),$("#btnSubmit, #btnReset, #btnCred").button(),$("#phEmlTabs").tabs(),$("#emergTabs").tabs(),$("#addrsTabs").tabs(),e=$("#psgList").tabs({collapsible:!0,beforeActivate:function(e,t){0<t.newPanel.length&&("fin"===t.newTab.prop("id")&&(getIncomeDiag(0,n.idReg),e.preventDefault()),"lipsg"!==t.newTab.prop("id")||a||(a=setupPsgNotes(n.idPsg,$("#psgNoteViewer"))))},load:function(e,t){"pmtsTable"===t.tab.prop("id")&&paymentsTable("feesTable","rptfeediv",paymentRefresh)}}),n.psgOnly&&e.tabs("disable"),e.tabs("enable",psgTabIndex),e.tabs("option","active",psgTabIndex),$("#cbnoReturn").change(function(){this.checked?$("#selnoReturn").show():$("#selnoReturn").hide()}),$("#cbnoReturn").change(),0===n.id?($("#divFuncTabs").tabs("option","disabled",[2,3,4]),$("#phEmlTabs").tabs("option","active",1),$("#phEmlTabs").tabs("option","disabled",[0])):(t=parseInt($("#addrsTabs").children("ul").data("actidx"),10),isNaN(t)&&(t=0),$("#addrsTabs").tabs("option","active",t)),$.datepicker.setDefaults({yearRange:"-0:+02",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:1,dateFormat:"M d, yy"}),$(".ckdate").datepicker({yearRange:"-02:+03"}),$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"}),$("#cbLastConfirmed").change(function(){$(this).prop("checked")?$("#txtLastConfirmed").datepicker("setDate","+0"):$("#txtLastConfirmed").val($("#txtLastConfirmed").prop("defaultValue"))}),$("#txtLastConfirmed").change(function(){$("#txtLastConfirmed").val()==$("#txtLastConfirmed").prop("defaultValue")?$("#cbLastConfirmed").prop("checked",!1):$("#cbLastConfirmed").prop("checked",!0)}),verifyAddrs("div#nameTab, div#hospitalSection"),addrPrefs(n),createZipAutoComplete($("input.hhk-zipsearch"),"ws_admin.php",void 0),$("#btnSubmit").click(function(){return"Saving>>>>"!==$(this).val()&&void $(this).val("Saving>>>>")}),$("#txtsearch").keypress(function(e){var t=$(this).val();"13"==e.keyCode&&(""!=t&&isNumber(parseInt(t,10))?0<t&&window.location.assign("GuestEdit.php?id="+t):alert("Don't press the return key unless you enter an Id."),e.preventDefault())}),$("#cbdeceased").change(function(){$(this).prop("checked")?$("#disp_deceased").show():$("#disp_deceased").hide()}),$("select.hhk-multisel").each(function(){$(this).multiselect({selectedList:3})}),createAutoComplete($("#txtAgentSch"),3,{cmd:"filter",add:"phone",basis:"ra"},getAgent),""===$("#a_txtLastName").val()&&$(".hhk-agentInfo").hide(),createAutoComplete($("#txtDocSch"),3,{cmd:"filter",basis:"doc"},getDoc),""===$("#d_txtLastName").val()&&$(".hhk-docInfo").hide(),createAutoComplete($("#txtsearch"),3,{cmd:"role",mode:"mo",gp:"1"},function(e){0<e.id&&window.location.assign("GuestEdit.php?id="+e.id)}),createAutoComplete($("#txtPhsearch"),5,{cmd:"role",mode:"mo",gp:"1"},function(e){0<e.id&&window.location.assign("GuestEdit.php?id="+e.id)}),createAutoComplete($("#txtRelSch"),3,{cmd:"srrel",basis:$("#hdnRelCode").val(),id:n.id},function(e){$.post("ws_admin.php",{rId:e.id,id:n.id,rc:$("#hdnRelCode").val(),cmd:"newRel"},relationReturn)}),""!==resultMessage&&flagAlertMessage(resultMessage,"alert"),$("input.hhk-check-button").click(function(){"exAll"===$(this).prop("id")?$("input.hhk-ex").prop("checked",!0):$("input.hhk-ex").prop("checked",!1)}),$("#divFuncTabs").show(),$(".hhk-showonload").show(),$("#txtsearch").focus(),$(document).find("bfh-states").each(function(){$(this).data("dirrty-initial-value",$(this).data("state"))}),$(document).find("bfh-country").each(function(){$(this).data("dirrty-initial-value",$(this).data("country"))}),$("#btnCred").click(function(){cardOnFile($(this).data("id"),$(this).data("idreg"),"GuestEdit.php?id="+$(this).data("id")+"&psg="+n.idPsg,$(this).data("indx"))}),setupCOF($("#trvdCHNameg"),$("#btnCred").data("indx")),$("#keysfees").mousedown(function(e){var t=$(e.target);"pudiv"!==t[0].id&&0===t.parents("#pudiv").length&&$("div#pudiv").remove()}),$("#form1").dirrty(),showGuestPhoto&&(o=new Upploader.Uppload({call:[".upload-guest-photo"],maxSize:[500,500],lang:Upploader.en,uploader:function(i){return new Promise(function(t,a){var e=new FormData;e.append("cmd","putguestphoto"),e.append("guestId",n.id),e.append("guestPhoto",i),$.ajax({type:"POST",url:"ws_resc.php",dataType:"json",data:e,contentType:!1,processData:!1,success:function(e){e.error?a(e.error):(t("success"),$("#guestPhoto").prop("src","ws_resc.php?cmd=getguestphoto&guestId="+n.id+"r&x="+(new Date).getTime()),$(".delete-guest-photo").show())},error:function(e){a(e)}})})}}),s=new Upploader.Local({maxFileSize:5e6,mimeTypes:["image/jpeg","image/png"]}),o.use([s,new Upploader.Crop({aspectRatio:1}),new Upploader.Camera]),o.on("open",function(){1==o.effects.length?$(o.container).find(".effects-tabs").hide():$(o.container).find(".effects-tabs").show()}),$(document).on("click","#hhk-guest-photo",function(e){e.preventDefault()}),$("#hhk-guest-photo").on({mouseenter:function(){$("#hhk-guest-photo-actions").show(),$("#hhk-guest-photo img").fadeTo(100,.5)},mouseleave:function(){$("#hhk-guest-photo-actions").hide(),$("#hhk-guest-photo img").fadeTo(100,1)}}),$(".delete-guest-photo").on("click",function(){confirm("Really Delete this photo?")&&$.ajax({type:"POST",url:"ws_resc.php",dataType:"json",data:{cmd:"deleteguestphoto",guestId:n.id},success:function(e){if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage("Server error - "+e.error,"error");$("#guestPhoto").prop("src","ws_resc.php?cmd=getguestphoto&guestId="+n.id+"&rx="+(new Date).getTime())},error:function(e){flagAlertMessage("AJAX error - "+e)}})}))});