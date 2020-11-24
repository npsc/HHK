function isNumber(e){"use strict";return!isNaN(parseFloat(e))&&isFinite(e)}var dtCols=[{targets:[0],title:"Date",data:"Date",render:function(e,t){return dateRender(e,t,dateFormat)}},{targets:[1],title:"Type",searchable:!1,sortable:!1,data:"Type"},{targets:[2],title:"Sub-Type",searchable:!1,sortable:!1,data:"Sub-Type"},{targets:[3],title:"User",searchable:!1,sortable:!0,data:"User"},{targets:[4],visible:!1,data:"Id"},{targets:[5],title:"Log Text",sortable:!1,data:"Log Text"}];function relationReturn(e){var t=$.parseJSON(e);if(t.error)t.gotopage&&window.open(t.gotopage,"_self"),flagAlertMessage(t.error,"error");else if(t.success){if(t.rc&&t.markup){var a=$("#acm"+t.rc);a.children().remove();var i=$(t.markup);a.append(i.children())}flagAlertMessage(t.success,"success")}}function setupPsgNotes(e,t){return t.notesViewer({linkId:e,linkType:"psg",newNoteAttrs:{id:"psgNewNote",name:"psgNewNote"},alertMessage:function(e,t){flagAlertMessage(e,t)}}),t}function manageRelation(e,t,a,i){$.post("ws_admin.php",{id:e,rId:t,rc:a,cmd:i},relationReturn)}function paymentRefresh(){var e=$("#psgList").tabs("option","active");$("#psgList").tabs("load",e)}$(document).ready(function(){"use strict";var e,t,a,i=memberData,o=1,s="../admin/ws_gen.php?cmd=chglog&vw=vguest_audit_log&uid="+i.id;if($.widget("ui.autocomplete",$.ui.autocomplete,{_resizeMenu:function(){var e=this.menu.element;e.outerWidth(1.1*Math.max(e.width("").outerWidth()+1,this.element.outerWidth()))}}),$("#divFuncTabs").tabs({collapsible:!0}),$("#vIncidentContent").incidentViewer({guestLabel:i.guestLabel,visitorLabel:i.visitorLabel,guestId:i.id,psgId:i.idPsg,alertMessage:function(e,t){flagAlertMessage(e,t)}}),useDocUpload&&$("#vDocsContent").docUploader({guestLabel:i.guestLabel,guestId:i.id,psgId:i.idPsg,alertMessage:function(e,t){flagAlertMessage(e,t)}}),$("#submit").dialog({autoOpen:!1,resizable:!1,width:300,modal:!0,buttons:{Exit:function(){$(this).dialog("close")}}}),$("#keysfees").dialog({autoOpen:!1,resizable:!0,modal:!0,close:function(){$("div#submitButtons").show()},open:function(){$("div#submitButtons").hide()}}),$("#pmtRcpt").dialog({autoOpen:!1,resizable:!0,modal:!0,title:"Payment Receipt"}),$("#faDialog").dialog({autoOpen:!1,resizable:!0,width:650,modal:!0,title:"Income Chooser"}),""!==rctMkup&&showReceipt("#pmtRcpt",rctMkup),""!==pmtMkup&&$("#paymentMessage").html(pmtMkup).show(),$(".hhk-view-visit").click(function(e){var t=$(this).data("vid"),a=$(this).data("gid"),o=$(this).data("span");$(e.target).hasClass("hhk-hospitalstay")||(viewVisit(a,t,{"Show Statement":function(){window.open("ShowStatement.php?vid="+t,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+t+"&span="+o,"_blank")},Save:function(){saveFees(a,t,o,!1,"GuestEdit.php?id="+a+"&psg="+i.idPsg)},Cancel:function(){$(this).dialog("close")}},"Edit Visit #"+t+"-"+o,"",o),$("#divAlert1").hide())}),$("#resvAccordion").accordion({heightStyle:"content",collapsible:!0,active:!1,icons:!1}),$("div.hhk-relations").each(function(){var e=$(this).attr("name");$(this).on("click","td.hhk-deletelink",function(){i.id>0&&confirm($(this).attr("title")+"?")&&manageRelation(i.id,$(this).attr("name"),e,"delRel")}),$(this).on("click","td.hhk-newlink",function(){if(i.id>0){var t=$(this).attr("title");$("#hdnRelCode").val(e),$("#submit").dialog("option","title",t),$("#submit").dialog("open")}})}),$("#cbNoVehicle").change(function(){this.checked?$("#tblVehicle").hide():$("#tblVehicle").show()}),$("#cbNoVehicle").change(),$("#btnNextVeh, #exAll, #exNone").button(),$("#btnNextVeh").click(function(){$("#trVeh"+o).show("fade"),++o>4&&$("#btnNextVeh").hide("fade")}),$("#divNametabs").tabs({beforeActivate:function(t,a){var o=$("#vvisitLog").find("table");"visitLog"===a.newTab.prop("id")&&0===o.length?$.post("ws_ckin.php",{cmd:"gtvlog",idReg:i.idReg},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error?(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,"error")):e.vlog&&$("#vvisitLog").append($(e.vlog))}}):"chglog"!==a.newTab.prop("id")||e||(e=$("#dataTbl").dataTable({columnDefs:dtCols,serverSide:!0,processing:!0,deferRender:!0,language:{search:"Search Log Text:"},sorting:[[0,"desc"]],displayLength:25,lengthMenu:[[25,50,100,-1],[25,50,100,"All"]],Dom:'<"top"ilf>rt<"bottom"ip>',ajax:{url:s}}))},collapsible:!0}),$("#btnSubmit, #btnReset, #btnCred").button(),$("#phEmlTabs").tabs(),$("#emergTabs").tabs(),$("#addrsTabs").tabs(),a=$("#psgList").tabs({collapsible:!0,beforeActivate:function(e,a){a.newPanel.length>0&&("fin"===a.newTab.prop("id")&&(getIncomeDiag(0,i.idReg),e.preventDefault()),"lipsg"!==a.newTab.prop("id")||t||(t=setupPsgNotes(i.idPsg,$("#psgNoteViewer"))))},load:function(e,t){"pmtsTable"===t.tab.prop("id")&&paymentsTable("feesTable","rptfeediv",paymentRefresh)}}),i.psgOnly&&a.tabs("disable"),a.tabs("enable",psgTabIndex),a.tabs("option","active",psgTabIndex),$("#cbnoReturn").change(function(){this.checked?$("#selnoReturn").show():$("#selnoReturn").hide()}),$("#cbnoReturn").change(),0===i.id)$("#divFuncTabs").tabs("option","disabled",[2,3,4]),$("#phEmlTabs").tabs("option","active",1),$("#phEmlTabs").tabs("option","disabled",[0]);else{var n=parseInt($("#addrsTabs").children("ul").data("actidx"),10);isNaN(n)&&(n=0),$("#addrsTabs").tabs("option","active",n)}if($.datepicker.setDefaults({yearRange:"-0:+02",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:1,dateFormat:"M d, yy"}),$(".ckdate").datepicker({yearRange:"-02:+03"}),$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"}),$("#cbLastConfirmed").change(function(){$(this).prop("checked")?$("#txtLastConfirmed").datepicker("setDate","+0"):$("#txtLastConfirmed").val($("#txtLastConfirmed").prop("defaultValue"))}),$("#txtLastConfirmed").change(function(){$("#txtLastConfirmed").val()==$("#txtLastConfirmed").prop("defaultValue")?$("#cbLastConfirmed").prop("checked",!1):$("#cbLastConfirmed").prop("checked",!0)}),verifyAddrs("div#nameTab, div#hospitalSection"),addrPrefs(i),createZipAutoComplete($("input.hhk-zipsearch"),"ws_admin.php",void 0),$("#btnSubmit").click(function(){if("Saving>>>>"===$(this).val())return!1;$(this).val("Saving>>>>")}),$("#txtsearch").keypress(function(e){var t=$(this).val();"13"==e.keyCode&&(""!=t&&isNumber(parseInt(t,10))?(t>0&&window.location.assign("GuestEdit.php?id="+t),e.preventDefault()):(alert("Don't press the return key unless you enter an Id."),e.preventDefault()))}),$("#cbdeceased").change(function(){$(this).prop("checked")?$("#disp_deceased").show():$("#disp_deceased").hide()}),$("select.hhk-multisel").each(function(){$(this).multiselect({selectedList:3})}),createAutoComplete($("#txtsearch"),3,{cmd:"role",mode:"mo",gp:"1"},function(e){e.id>0&&window.location.assign("GuestEdit.php?id="+e.id)}),createAutoComplete($("#txtPhsearch"),5,{cmd:"role",mode:"mo",gp:"1"},function(e){e.id>0&&window.location.assign("GuestEdit.php?id="+e.id)}),createAutoComplete($("#txtRelSch"),3,{cmd:"srrel",basis:$("#hdnRelCode").val(),id:i.id},function(e){$.post("ws_admin.php",{rId:e.id,id:i.id,rc:$("#hdnRelCode").val(),cmd:"newRel"},relationReturn)}),""!==resultMessage&&flagAlertMessage(resultMessage,"alert"),$("input.hhk-check-button").click(function(){"exAll"===$(this).prop("id")?$("input.hhk-ex").prop("checked",!0):$("input.hhk-ex").prop("checked",!1)}),$("#divFuncTabs").show(),$(".hhk-showonload").show(),$("#txtsearch").focus(),$(document).find("bfh-states").each(function(){$(this).data("dirrty-initial-value",$(this).data("state"))}),$(document).find("bfh-country").each(function(){$(this).data("dirrty-initial-value",$(this).data("country"))}),$("#btnCred").click(function(){cardOnFile($(this).data("id"),$(this).data("idreg"),"GuestEdit.php?id="+$(this).data("id")+"&psg="+i.idPsg,$(this).data("indx"))}),setupCOF($("#trvdCHNameg"),$("#btnCred").data("indx")),$("#keysfees").mousedown(function(e){var t=$(e.target);"pudiv"!==t[0].id&&0===t.parents("#pudiv").length&&$("div#pudiv").remove()}),$("#form1").dirrty(),showGuestPhoto||useDocUpload){var r=window.uploader;$(document).on("click",".upload-guest-photo",function(){$(r.container).removeClass().addClass("uppload-container"),r.updatePlugins(e=>[]),r.updateSettings({maxSize:[500,500],customClass:"guestPhotouploadContainer",uploader:function(e){return new Promise(function(t,a){var o=new FormData;o.append("cmd","putguestphoto"),o.append("guestId",i.id),o.append("guestPhoto",e),$.ajax({type:"POST",url:"ws_resc.php",dataType:"json",data:o,contentType:!1,processData:!1,success:function(e){e.error?a(e.error):(t("success"),$("#guestPhoto").prop("src","ws_resc.php?cmd=getguestphoto&guestId="+i.id+"r&x="+(new Date).getTime()),$(".delete-guest-photo").show()),r.navigate("local")},error:function(e){a(e)}})})}});var e=new Upploader.Local({maxFileSize:5e6,mimeTypes:["image/jpeg","image/png"]});window.camera=new Upploader.Camera,r.use([e,new Upploader.Crop({aspectRatio:1}),window.camera]),r.open()}),r.on("open",function(){1==r.effects.length?$(r.container).find(".effects-tabs").hide():$(r.container).find(".effects-tabs").show()}),r.on("close",function(){r.navigate("local");var e=r.services.filter(e=>"camera"==e.name);1==e.length&&e[0].stop()}),$(document).on("click","#hhk-guest-photo",function(e){e.preventDefault()}),$("#hhk-guest-photo").on({mouseenter:function(){$("#hhk-guest-photo-actions").show(),$("#hhk-guest-photo img").fadeTo(100,.5)},mouseleave:function(){$("#hhk-guest-photo-actions").hide(),$("#hhk-guest-photo img").fadeTo(100,1)}}),$(".delete-guest-photo").on("click",function(){confirm("Really Delete this photo?")&&$.ajax({type:"POST",url:"ws_resc.php",dataType:"json",data:{cmd:"deleteguestphoto",guestId:i.id},success:function(e){if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage("Server error - "+e.error,"error");$("#guestPhoto").prop("src","ws_resc.php?cmd=getguestphoto&guestId="+i.id+"&rx="+(new Date).getTime())},error:function(e){flagAlertMessage("AJAX error - "+e)}})})}});
