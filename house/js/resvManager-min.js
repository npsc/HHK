function resvManager(e){var t=this,k=e.patLabel,i=e.resvTitle,a=e.saveButtonLabel,x=e.patBD,b=e.patAddr,w=e.gstAddr,S=e.patAsGuest,A=void 0!==e.emergencyContact&&e.emergencyContact,D=void 0!==e.isCheckin&&e.isCheckin,M=e.addrPurpose,r=e.idPsg,d=e.rid,s=e.id,n=e.vid,o=e.span,c=e.arrival,l=[],R=new y,P=new y,h=new function(h){var o,e=this,n="divfamDetail",p=!1;function u(){var t=0;return $(".hhk-cbStay").each(function(){var e=$(this).data("prefix");$(this).prop("checked")?(R.list()[e].stay="1",t++):1===$(".hhk-cbStay").length?R.list()[e].stay="1":R.list()[e].stay="0"}),t}function v(){var e=$("input[type=radio][name=rbPriGuest]:checked").val();for(var t in R.list())R.list()[t].pri="0";void 0!==e&&(R.list()[e].pri="1")}function g(e){var t=$("#divfamDetail");!0===e?(t.show("blind"),t.prev("div").removeClass("ui-corner-all").addClass("ui-corner-top")):(t.hide("blind"),t.prev("div").addClass("ui-corner-all").removeClass("ui-corner-top"))}function d(e){"use strict";$("#ecSearch").dialog("close");var t=parseInt(e.id,10);if(!1===isNaN(t)&&0<t){var a=$("#hdnEcSchPrefix").val();if(""==a)return;$("#"+a+"txtEmrgFirst").val(e.first),$("#"+a+"txtEmrgLast").val(e.last),$("#"+a+"txtEmrgPhn").val(e.phone),$("#"+a+"txtEmrgAlt").val(""),$("#"+a+"selEmrgRel").val("")}}function f(e){var t=/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/,a=!1;return 0<$("#"+e+"incomplete").length&&!1===$("#"+e+"incomplete").prop("checked")&&($("."+e+"hhk-addr-val").not(".hhk-MissingOk").each(function(){""!==$(this).val()||$(this).hasClass("bfh-states")?$(this).removeClass("ui-state-error"):($(this).addClass("ui-state-error"),a=!0)}),a)?($("#"+e+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+e+"toggleAddr").click(),"Some or all of the indicated addresses are missing.  "):($('.hhk-phoneInput[id^="'+e+'txtPhone"]').each(function(){""!==$.trim($(this).val())&&!1===t.test($(this).val())?($(this).addClass("ui-state-error"),$("#"+e+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+e+"toggleAddr").click(),$("#"+e+"phEmlTabs").tabs("option","active",1),a=!0):$(this).removeClass("ui-state-error")}),"")}function m(e){var t=!1,a=$("#"+e+"txtEmrgFirst"),i=$("#"+e+"txtEmrgLast"),r=$("#"+e+"txtEmrgPhn"),s=$("#"+e+"selEmrgRel");return a.removeClass("ui-state-error"),i.removeClass("ui-state-error"),r.removeClass("ui-state-error"),s.removeClass("ui-state-error"),0<$("#"+e+"cbEmrgLater").length&&!1===$("#"+e+"cbEmrgLater").prop("checked")&&(""===a.val()&&""===i.val()&&(a.addClass("ui-state-error"),i.addClass("ui-state-error"),t=!0),""===r.val()&&(r.addClass("ui-state-error"),t=!0),""===s.val()&&(s.addClass("ui-state-error"),t=!0),t)?"Some or all of the indicated Emergency Contact Information is missing.  ":""}function l(e){return!(void 0===e||!e||""==e)&&(""!==$("#"+e+"adraddress1"+M).val()&&""!==$("#"+e+"adrzip"+M).val()&&""!==$("#"+e+"adrstate"+M).val()&&""!==$("#"+e+"adrcity"+M).val())}function c(e,t){$(".hhk-addrPicker").remove();var a=$('<select id="selAddrch" multiple="multiple" />'),i=0,r=[];for(var s in P.list())if(""!=P.list()[s].Address_1||""!=P.list()[s].Postal_Code){for(var n=!0,o=P.list()[s].Address_1+", "+(""==P.list()[s].Address_2?"":P.list()[s].Address_2+", ")+P.list()[s].City+", "+P.list()[s].State_Province+"  "+P.list()[s].Postal_Code,d=0;d<=r.length;d++)r[d]!=o||(n=!1);n&&(r[i]=o,i++,$('<option class="hhk-addrPickerPanel" value="'+s+'">'+o+"</option>").appendTo(a))}0<i&&(a.prop("size",i+1).prepend($('<option value="0" >(Cancel)</option>')),a.change(function(){!function(e,t){if(0==t)return $("#divSelAddr").remove();$("#"+e+"adraddress1"+M).val(P.list()[t].Address_1),$("#"+e+"adraddress2"+M).val(P.list()[t].Address_2),$("#"+e+"adrcity"+M).val(P.list()[t].City),$("#"+e+"adrcounty"+M).val(P.list()[t].County),$("#"+e+"adrzip"+M).val(P.list()[t].Postal_Code),$("#"+e+"adrcountry"+M).val()!=P.list()[t].Country_Code&&$("#"+e+"adrcountry"+M).val(P.list()[t].Country_Code).change();$("#"+e+"adrstate"+M).val(P.list()[t].State_Province),l(e)&&!0===$("#"+e+"incomplete").prop("checked")&&$("#"+e+"incomplete").prop("checked",!1);y($("#"+e+"liaddrflag")),$("#divSelAddr").remove()}(t,$(this).val())}),$('<div id="divSelAddr" style="position:absolute; vertical-align:top;" class="hhk-addrPicker hhk-addrPickerPanel"/>').append($('<p class="hhk-addrPickerPanel">Choose an Address: </p>')).append(a).appendTo($("body")).position({my:"left top",at:"right center",of:e}))}function C(e){void 0!==e&&(P.list()[e].Address_1=$("#"+e+"adraddress1"+M).val(),P.list()[e].Address_2=$("#"+e+"adraddress2"+M).val(),P.list()[e].City=$("#"+e+"adrcity"+M).val(),P.list()[e].County=$("#"+e+"adrcounty"+M).val(),P.list()[e].State_Province=$("#"+e+"adrstate"+M).val(),P.list()[e].Country_Code=$("#"+e+"adrcountry"+M).val(),P.list()[e].Postal_Code=$("#"+e+"adrzip"+M).val(),y($("#"+e+"liaddrflag")))}function y(e){var t=e.data("pref");!0===$("#"+t+"incomplete").prop("checked")?(e.show().find("span").removeClass("ui-icon-alert").addClass("ui-icon-check").attr("title","Incomplete Address is checked"),e.removeClass("ui-state-error").addClass("ui-state-highlight")):l(t)?e.hide():(e.show().find("span").removeClass("ui-icon-check").addClass("ui-icon-alert").attr("title","Address is Incomplete"),e.removeClass("ui-state-highlight").addClass("ui-state-error"))}e.findStaysChecked=u,e.findStays=function(e){var t=0;for(var a in R.list())R.list()[a].stay===e&&t++;return t},e.findPrimaryGuest=v,e.setUp=function(t){var e;if(void 0!==t.famSection&&void 0!==t.famSection.tblId&&""!==t.famSection.tblId){for(var a in!1===p&&function(e){var t,a,i;t=$("<div/>").addClass("ui-widget-content ui-corner-bottom hhk-tdbox").prop("id",n).css("padding","5px"),o=$("<table/>").prop("id",e.famSection.tblId).addClass("hhk-table").append($("<thead/>").append($(e.famSection.tblHead))).append($("<tbody/>")),t.append(o).append($(e.famSection.adtnl)),i=$("<ul style='list-style-type:none; float:right;margin-left:5px;padding-top:2px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='f_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),(a=$('<div id="divfamHdr" style="padding:2px; cursor:pointer;"/>').append($(e.famSection.hdr)).append(i).append('<div style="clear:both;"/>')).addClass("ui-widget-header ui-state-default ui-corner-top"),a.click(function(){"none"===t.css("display")?(t.show("blind"),a.removeClass("ui-corner-all").addClass("ui-corner-top")):(t.hide("blind"),a.removeClass("ui-corner-top").addClass("ui-corner-all"))}),h.empty().append(a).append(t).show()}(t),t.famSection.mem){var i=R.findItem("pref",t.famSection.mem[a].pref);i&&(o.find("tr#"+i.id+"n").remove(),o.find("tr#"+i.id+"a").remove(),o.find("input#"+i.pref+"idName").parents("tr").next("tr").remove(),o.find("input#"+i.pref+"idName").parents("tr").remove(),R.removeIndex(i.pref),P.removeIndex(i.pref))}for(var r in R.makeList(t.famSection.mem,"pref"),P.makeList(t.famSection.addrs,"pref"),void 0!==t.famSection.tblBody[1]&&o.find("tbody:first").prepend($(t.famSection.tblBody[1])),void 0!==t.famSection.tblBody[0]&&o.find("tbody:first").prepend($(t.famSection.tblBody[0])),t.famSection.tblBody)"0"!==r&&"1"!==r&&o.find("tbody:first").append($(t.famSection.tblBody[r]));for(var s in $(".hhk-cbStay").checkboxradio({classes:{"ui-checkboxradio-label":"hhk-unselected-text"}}),$(".hhk-lblStay").each(function(){"1"==$(this).data("stay")&&$(this).click()}),$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"}),$(".hhk-addrPanel").find("select.bfh-countries").each(function(){var e=$(this);e.bfhcountries(e.data()),$(this).data("dirrty-initial-value",$(this).data("country"))}),$(".hhk-addrPanel").find("select.bfh-states").each(function(){var e=$(this);e.bfhstates(e.data()),$(this).data("dirrty-initial-value",$(this).data("state"))}),$(".hhk-phemtabs").tabs(),verifyAddrs("#divfamDetail"),$("input.hhk-zipsearch").each(function(){createZipAutoComplete($(this),"ws_admin.php",void 0,C)}),!1===p&&($("#lnCopy").click(function(){var e=$("input.hhk-lastname").first().val();$("input.hhk-lastname").each(function(){""===$(this).val()&&$(this).val(e)})}),$("#adrCopy").click(function(){!function(e){for(var t in P.list())e!=t&&(""!==$("#"+t+"adraddress1"+M).val()&&""!==$("#"+t+"adrzip"+M).val()||($("#"+t+"adraddress1"+M).val(P.list()[e].Address_1),$("#"+t+"adraddress2"+M).val(P.list()[e].Address_2),$("#"+t+"adrcity"+M).val(P.list()[e].City),$("#"+t+"adrcounty"+M).val(P.list()[e].County),$("#"+t+"adrzip"+M).val(P.list()[e].Postal_Code),$("#"+t+"adrcountry"+M).val()!=P.list()[e].Country_Code&&$("#"+t+"adrcountry"+M).val(P.list()[e].Country_Code).change(),$("#"+t+"adrstate"+M).val(P.list()[e].State_Province),!0===$("#"+e+"incomplete").prop("checked")?$("#"+t+"incomplete").prop("checked",!0):l(t)&&!0===$("#"+t+"incomplete").prop("checked")&&$("#"+t+"incomplete").prop("checked",!1),y($("#"+t+"liaddrflag"))))}($("li.hhk-AddrFlag").first().data("pref"))}),$("#"+n).on("click",".hhk-togAddr",function(){e=$(this),$(this).siblings(),"none"===$(this).parents("tr").next("tr").css("display")?($(this).parents("tr").next("tr").show(),e.find("span").removeClass("ui-icon-circle-triangle-s").addClass("ui-icon-circle-triangle-n"),e.attr("title","Hide Address Section")):($(this).parents("tr").next("tr").hide(),e.find("span").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"),e.attr("title","Show Address Section"),isIE()&&$("#divSelAddr").remove())}),$("#"+n).on("click",".hhk-AddrFlag",function(){$("#"+$(this).data("pref")+"incomplete").click()}),$("#"+n).on("change",".hhk-copy-target",function(){C($(this).data("pref"))}),$("#"+n).on("click",".hhk-addrCopy",function(){c($(this),$(this).data("prefix"))}),$("#"+n).on("click",".hhk-addrErase",function(){!function(e){$("#"+e+"adraddress1"+M).val(""),$("#"+e+"adraddress2"+M).val(""),$("#"+e+"adrcity"+M).val(""),$("#"+e+"adrcounty"+M).val(""),$("#"+e+"adrstate"+M).val(""),$("#"+e+"adrcountry"+M).val(""),$("#"+e+"adrzip"+M).val(""),y($("#"+e+"liaddrflag"))}($(this).data("prefix"))}),$("#"+n).on("click",".hhk-incompleteAddr",function(){y($("#"+$(this).data("prefix")+"liaddrflag"))}),$("#"+n).on("click",".hhk-removeBtn",function(){(""===$("#"+$(this).data("prefix")+"txtFirstName").val()&&""===$("#"+$(this).data("prefix")+"txtLastName").val()||!1!==confirm("Remove this person: "+$("#"+$(this).data("prefix")+"txtFirstName").val()+" "+$("#"+$(this).data("prefix")+"txtLastName").val()+"?"))&&(R.removeIndex($(this).data("prefix")),P.removeIndex($(this).data("prefix")),$(this).parentsUntil("tbody","tr").next().remove(),$(this).parentsUntil("tbody","tr").remove())}),$("#"+n).on("change",".patientRelch",function(){"slf"===$(this).val()?R.list()[$(this).data("prefix")].role="p":R.list()[$(this).data("prefix")].role="g"}),createAutoComplete($("#txtPersonSearch"),3,{cmd:"role",gp:"1"},function(e){!function(e,t){void 0===e.No_Return||""===e.No_Return?void 0!==e.id&&(0<e.id&&null!==R.findItem("id",e.id)?flagAlertMessage("This person is already listed here. ","alert"):I({id:e.id,rid:t.rid,idPsg:t.idPsg,isCheckin:D,gstDate:$("#gstDate").val(),gstCoDate:$("#gstCoDate").val(),cmd:"addResvGuest"})):flagAlertMessage("This person is set for No Return: "+e.No_Return+".","alert")}(e,t)}),$("#"+n).on("click",".hhk-emSearch",function(){$("#hdnEcSchPrefix").val($(this).data("prefix")),$("#ecSearch").dialog("open")}),createAutoComplete($("#txtemSch"),3,{cmd:"filter",add:"phone",basis:"g"},d),$("ul.hhk-ui-icons li").hover(function(){$(this).addClass("ui-state-hover")},function(){$(this).removeClass("ui-state-hover")})),R.list())y($("#"+s+"liaddrflag"));$(".hhk-togAddr").each(function(){$(this).parents("tr").next("tr").hide(),$(this).find("span").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"),$(this).attr("title","Show Address Section")}),p=!0}},e.newGuestMarkup=function(e,t){var a,i,r,s,n;void 0!==e.tblId&&""!=e.tblId&&0!==o.length&&(r=o.children("tbody").children("tr").last().hasClass("odd")?"even":"odd",o.find("tbody:first").append($(e.ntr).addClass(r)).append($(e.atr).addClass(r)),$("#"+t+"cbStay").checkboxradio({classes:{"ui-checkboxradio-label":"hhk-unselected-text"}}),"1"==$("#"+t+"lblStay").data("stay")&&$("#"+t+"lblStay").click(),$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"}),n=(s=$("#"+t+"liaddrflag")).siblings(),y(s),n.parents("tr").next("tr").hide(),n.find("span").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"),n.attr("title","Show Address Section"),(a=$("#"+t+"adrcountry"+M)).bfhcountries(a.data()),$(this).data("dirrty-initial-value",$(this).data("country")),(i=$("#"+t+"adrstate"+M)).bfhstates(i.data()),$(this).data("dirrty-initial-value",$(this).data("state")),$("#"+t+"phEmlTabs").tabs(),$("input#"+t+"adrzip1").each(function(){createZipAutoComplete($(this),"ws_admin.php",void 0,C)}))},e.verify=function(){var e=0,t=0,a=0,i=0,r=!1,s=0,n=!1;if($(".patientRelch").removeClass("ui-state-error"),$(".patientRelch").each(function(){""===$(this).val()?($(this).addClass("ui-state-error"),n=!0):$(this).removeClass("ui-state-error")}),n)return flagAlertMessage("Set the highlighted Relationship(s).","alert",_),!1;for(var o in v(),u(),R.list())e++,"p"===R.list()[o].role&&t++,"1"===R.list()[o].stay&&a++,"1"===R.list()[o].pri&&i++,$("#"+o+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-n")&&$("#"+o+"toggleAddr").click();if(t<1)return flagAlertMessage("Choose a "+k+".","alert",_),$(".patientRelch").addClass("ui-state-error"),!1;if(1<t){for(var o in flagAlertMessage("Only 1 "+k+" is allowed.","alert",_),R.list())"p"===R.list()[o].role&&$("#"+o+"selPatRel").addClass("ui-state-error");return!1}if(a<1)return flagAlertMessage("There is no one actually staying.  Pick someone to stay.","alert",_),!1;if($("input.hhk-rbPri").parent().removeClass("ui-state-error"),0===i&&1===e)for(var o in R.list())R.list()[o].pri="1";else if(0===i)return _.text("Set one guest as primary guest.").show(),flagAlertMessage("Set one guest as primary guest.","alert",_),$("input.hhk-rbPri").parent().addClass("ui-state-error"),!1;if(h.find(".hhk-lastname").each(function(){""==$(this).val()?($(this).addClass("ui-state-error"),r=!0):$(this).removeClass("ui-state-error")}),h.find(".hhk-firstname").each(function(){""==$(this).val()?($(this).addClass("ui-state-error"),r=!0):$(this).removeClass("ui-state-error")}),!0===r)return g(!0),flagAlertMessage("Enter a first and last name for the people highlighted.","alert",_),!1;for(var d in A&&h.find(".hhk-EmergCb").each(function(){var e=m($(this).data("prefix"));!0!==$(this).prop("checked")&&""!==e||s++}),R.list()){if("p"===R.list()[d].role){if(x&""===$("#"+d+"txtBirthDate").val())return $("#"+d+"txtBirthDate").addClass("ui-state-error"),flagAlertMessage(k+" is missing the Birth Date.","alert",_),g(!0),!1;if($("#"+d+"txtBirthDate").removeClass("ui-state-error"),b||S)if(""!==(c=f(d)))return flagAlertMessage(c,"alert",_),g(!0),$("#"+d+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+d+"toggleAddr").click(),!1}else{if(w)if(""!==(c=f(d)))return flagAlertMessage(c,"alert",_),g(!0),$("#"+d+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+d+"toggleAddr").click(),!1}if(0<$("#"+d+"txtBirthDate").length&&""!==$("#"+d+"txtBirthDate").val()){var l=new Date($("#"+d+"txtBirthDate").val());if(new Date<l)return $("#"+d+"txtBirthDate").addClass("ui-state-error"),flagAlertMessage("This birth date cannot be in the future.","alert",_),g(!0),!1;$("#"+d+"txtBirthDate").removeClass("ui-state-error")}var c;if(A&&s<1)if(""!==(c=m(d)))return flagAlertMessage(c,"alert",_),g(!0),$("#"+d+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+d+"toggleAddr").click(),!1}return!(p=!1)},e.divFamDetailId=n,e.$famTbl=o}($("#famSection")),p=new function(i){var a,e,r,s,n=this;function o(e){"use strict";var t="";return""===$("#car"+e+"txtVehLic").val()&&""===$("#car"+e+"txtVehMake").val()?"Enter vehicle info or check the 'No Vehicle' checkbox. ":(""===$("#car"+e+"txtVehLic").val()?(""===$("#car"+e+"txtVehModel").val()&&($("#car"+e+"txtVehModel").addClass("ui-state-highlight"),t="Enter Model"),""===$("#car"+e+"txtVehColor").val()&&($("#car"+e+"txtVehColor").addClass("ui-state-highlight"),t="Enter Color"),""===$("#car"+e+"selVehLicense").val()&&($("#car"+e+"selVehLicense").addClass("ui-state-highlight"),t="Enter state license plate registration")):""===$("#car"+e+"txtVehMake").val()&&""===$("#car"+e+"txtVehLic").val()&&($("#car"+e+"txtVehLic").addClass("ui-state-highlight"),t="Enter a license plate number."),t)}n.setupComplete=!1,n.checkPayments=!0,n.setUp=function(t){a=$("<div/>").addClass(" hhk-tdbox").prop("id","divResvDetail").css("padding","5px"),void 0!==t.resv.rdiv.rChooser&&a.append($(t.resv.rdiv.rChooser)),void 0!==t.resv.rdiv.rate&&a.append($(t.resv.rdiv.rate)),void 0!==t.resv.rdiv.cof&&a.append(t.resv.rdiv.cof),void 0!==t.resv.rdiv.rstat&&a.append($(t.resv.rdiv.rstat)),void 0!==t.resv.rdiv.vehicle&&(e=$(t.resv.rdiv.vehicle),a.append(e),function(e){var t=1,a=e.find("#cbNoVehicle"),i=e.find("#btnNextVeh"),r=e.find("#tblVehicle");a.change(function(){this.checked?r.hide("scale, horizontal"):r.show("scale, horizontal")}),a.change(),i.button(),i.click(function(){e.find("#trVeh"+t).show("fade"),4<++t&&i.hide("fade")})}(e)),void 0!==t.resv.rdiv.pay&&a.append($(t.resv.rdiv.pay)),void 0!==t.resv.rdiv.notes&&a.append(function(e,t){return t.notesViewer({linkId:e,linkType:"reservation",newNoteAttrs:{id:"taNewNote",name:"taNewNote"},alertMessage:function(e,t){flagAlertMessage(e,t)}}),t}(t.rid,$(t.resv.rdiv.notes))),void 0!==t.resv.rdiv.wlnotes&&a.append($(t.resv.rdiv.wlnotes)),s=$("<ul style='list-style-type:none; float:right; margin-left:5px; padding-top:2px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='r_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),(r=$('<div id="divResvHdr" style="padding:2px; cursor:pointer;"/>').append($(t.resv.hdr)).append(s).append('<div style="clear:both;"/>')).addClass("ui-widget-header ui-state-default ui-corner-top"),r.click(function(e){var t=$(e.target);"divResvHdr"!==t[0].id&&"r_drpDown"!==t[0].id||("none"===a.css("display")?(a.show("blind"),r.removeClass("ui-corner-all").addClass("ui-corner-top")):(a.hide("blind"),r.removeClass("ui-corner-top").addClass("ui-corner-all")))}),i.empty().append(r).append(a).show(),n.$totalGuests=$("#spnNumGuests"),n.origRoomId=$("#selResource").val(),n.checkPayments=!0,0<$(".hhk-viewResvActivity").length&&$(".hhk-viewResvActivity").click(function(){$.post("ws_ckin.php",{cmd:"viewActivity",rid:$(this).data("rid")},function(e){if((e=$.parseJSON(e)).error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,"error");e.activity&&($("div#submitButtons").hide(),$("#activityDialog").children().remove(),$("#activityDialog").append($(e.activity)),$("#activityDialog").dialog("open"))})}),$("#btnShowCnfrm").button().click(function(){var e=$("#spnAmount").text();""===e&&(e=0),$.post("ws_ckin.php",{cmd:"confrv",rid:$(this).data("rid"),amt:e,eml:"0"},function(e){if((t=$.parseJSON(e)).error)return t.gotopage&&window.open(t.gotopage,"_self"),void flagAlertMessage(t.error,"error");t.confrv&&($("div#submitButtons").hide(),$("#frmConfirm").children().remove(),$("#frmConfirm").html(t.confrv).append($('<div style="padding-top:10px;" class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><span>Email Address </span><input type="text" id="confEmail" value="'+t.email+'"/></div>')),$("#confirmDialog").dialog("open"))})}),function(e){g.idReservation=e,$("input.hhk-constraintsCB").change(function(){g.go($("#gstDate").val(),$("#gstCoDate").val())})}(t.rid),void 0!==t.resv.rdiv.rate&&function(e){var t={},a=i.find("#btnFapp");0<a.length&&($("#faDialog").dialog({autoOpen:!1,resizable:!0,width:680,modal:!0,title:"Income Chooser",close:function(){$("div#submitButtons").show()},open:function(){$("div#submitButtons").hide()},buttons:{Save:function(){$.post("ws_ckin.php",$("#formf").serialize()+"&cmd=savefap&rid="+e.rid,function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Bad JSON Encoding")}if(e.gotopage&&window.open(e.gotopage,"_self"),e.rstat&&1==e.rstat){var t=$("#selRateCategory");e.rcat&&""!=e.rcat&&0<t.length&&(t.val(e.rcat),t.change())}}),$(this).dialog("close")},Exit:function(){$(this).dialog("close")}}}),a.button().click(function(){getIncomeDiag(e.rid)})),e.resv.rdiv.ratelist&&(t.rateList=e.resv.rdiv.ratelist,t.resources=e.resv.rdiv.rooms,t.visitFees=e.resv.rdiv.vfee,t.idResv=d,setupRates(t)),0<$("#selResource").length&&$("#selResource").change(function(){$("#selRateCategory").change();var e=$("option:selected",this).parent()[0].label;null==e?$("#hhkroomMsg").hide():$("#hhkroomMsg").text(e).show()})}(t),void 0!==t.resv.rdiv.pay&&0<$("#selResource").length&&0<$("#selRateCategory").length&&(setupPayments($("#selRateCategory")),$("#paymentDate").datepicker({yearRange:"-1:+00",numberOfMonths:1})),void 0!==t.resv.rdiv.cof&&setupCOF(),0<$("#addGuestHeader").length&&(v.openControl=!0,v.setUp(t.resv.rdiv,m,$("#addGuestHeader"))),n.setupComplete=!0},n.verify=function(){if(0<$("#cbNoVehicle").length){if(!1===$("#cbNoVehicle").prop("checked")){var e=o(1);if(""!=e){var t=o(2);if(""!=t)return $("#vehValidate").text(t),flagAlertMessage(e,"alert",_),!1}}$("#vehValidate").text("")}if(D&&!0===n.checkPayments){if($("#selCategory").val()==fixedRate&&0<$("#txtFixedRate").length&&""==$("#txtFixedRate").val())return flagAlertMessage("Set the Room Rate to an amount, or to 0.","alert",_),$("#txtFixedRate").addClass("ui-state-error"),!1;if($("#txtFixedRate").removeClass("ui-state-error"),0<$("input#feesPayment").length&&""==$("input#feesPayment").val())return flagAlertMessage("Set the Room Fees to an amount, or 0.","alert",_),$("#payChooserMsg").text("Set the Room Fees to an amount, or 0.").show(),$("input#feesPayment").addClass("ui-state-error"),!1;if($("input#feesPayment").removeClass("ui-state-error"),void 0!==verifyAmtTendrd&&!1===verifyAmtTendrd())return!1}return!0}}($("#resvSection")),u=new function(r){var s=this;s.setupComplete=!1,s.setUp=function(e){var t=$(e.div).addClass("ui-widget-content").prop("id","divhospDetail").hide(),a=$("<ul style='list-style-type:none; float:right;margin-left:5px;padding-top:2px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='h_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),i=$('<div id="divhospHdr" style="padding:2px; cursor:pointer;"/>').append($(e.hdr)).append(a).append('<div style="clear:both;"/>');i.addClass("ui-widget-header ui-state-default ui-corner-all"),i.click(function(){"none"===t.css("display")?(t.show("blind"),i.removeClass("ui-corner-all").addClass("ui-corner-top")):(t.hide("blind"),i.removeClass("ui-corner-top").addClass("ui-corner-all"))}),r.empty().append(i).append(t),$("#txtEntryDate, #txtExitDate").datepicker({yearRange:"-01:+01",changeMonth:!0,changeYear:!0,autoSize:!0,dateFormat:"M d, yy"}),0<$("#txtAgentSch").length&&(createAutoComplete($("#txtAgentSch"),3,{cmd:"filter",basis:"ra"},getAgent),""===$("#a_txtLastName").val()&&$(".hhk-agentInfo").hide()),0<$("#txtDocSch").length&&(createAutoComplete($("#txtDocSch"),3,{cmd:"filter",basis:"doc"},getDoc),""===$("#d_txtLastName").val()&&$(".hhk-docInfo").hide()),verifyAddrs("#divhospDetail"),r.on("change","#selHospital, #selAssoc",function(){var e=$("#selAssoc").find("option:selected").text();""!=e&&(e+="/ "),$("span#spnHospName").text(e+$("#selHospital").find("option:selected").text())}),r.show(),""===$("#selHospital").val()&&i.click(),s.setupComplete=!0},s.verify=function(){return r.find(".ui-state-error").each(function(){$(this).removeClass("ui-state-error")}),0<$("#selHospital").length&&!0===s.setupComplete&&""==$("#selHospital").val()?($("#selHospital").addClass("ui-state-error"),flagAlertMessage("Select a hospital.","alert",_),$("#divhospDetail").show("blind"),$("#divhospHdr").removeClass("ui-corner-all").addClass("ui-corner-top"),!1):($("#divhospDetail").hide("blind"),$("#divhospHdr").removeClass("ui-corner-top").addClass("ui-corner-all"),!0)}}($("#hospitalSection")),v=new function(){var l=this;l.setupComplete=!1,l.ciDate=new Date,l.coDate=new Date,l.openControl=!1,l.setUp=function(i,r,e){if(e.empty(),i.mu&&""!==i.mu){e.append($(i.mu));var t,s=$("#gstDate"),n=$("#gstCoDate"),a=parseInt(i.defdays,10),o=!1,d=!1;""===s.val()&&c&&s.val(c),i.startDate&&(o=i.startDate),i.endDate&&(d=i.endDate),t=$("#spnRangePicker").dateRangePicker({format:"MMM D, YYYY",separator:" to ",minDays:1,autoClose:!0,showShortcuts:!0,shortcuts:{"next-days":[a]},getValue:function(){return s.val()&&n.val()?s.val()+" to "+n.val():""},setValue:function(e,t,a){s.val(t),n.val(a)},startDate:o,endDate:d}),i.updateOnChange&&t.bind("datepicker-change",function(e,t){var a=Math.ceil((t.date2.getTime()-t.date1.getTime())/864e5);$("#"+i.daysEle).val(a),0<$("#spnNites").length&&$("#spnNites").text(a),$("#gstDate").removeClass("ui-state-error"),$("#gstCoDate").removeClass("ui-state-error"),$.isFunction(r)&&r(t)}),e.show(),l.openControl&&$("#spnRangePicker").data("dateRangePicker").open()}setupComplete=!0},l.verify=function(){var e=$("#gstDate"),t=$("#gstCoDate");if(e.removeClass("ui-state-error"),t.removeClass("ui-state-error"),""===e.val())return e.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the check-in date.","alert",_),!1;if(l.ciDate=new Date(e.val()),isNaN(l.ciDate.getTime()))return e.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the check-in date.","alert",_),!1;if(void 0!==D&&!0===D){var a=moment($("#gstDate").val(),"MMM D, YYYY");if(moment().endOf("date")<a)return e.addClass("ui-state-error"),flagAlertMessage("Set the Check in date to today or earlier.","alert",_),!1}return""===t.val()?(t.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the expected departure date.","alert",_),!1):(l.coDate=new Date(t.val()),isNaN(l.coDate.getTime())?(t.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the expected departure date","alert",_),!1):!(l.ciDate>l.coDate)||(e.addClass("ui-state-error"),flagAlertMessage("This "+i+"'s check-in date is after the expected departure date.","alert",_),!1))}},g=new g,_=$("#pWarnings");function f(e){l=e}function m(e){_.text("").hide();var t=!1;for(var a in R.list())if(0<R.list()[a].id){t=!0;break}if(t){$(".hhk-stayIndicate").hide().parent("td").addClass("hhk-loading");var i={cmd:"updateAgenda",idPsg:r,idResv:d,idVisit:n,span:o,dt1:e.date1.getFullYear()+"-"+(e.date1.getMonth()+1)+"-"+e.date1.getDate(),dt2:e.date2.getFullYear()+"-"+(e.date2.getMonth()+1)+"-"+e.date2.getDate(),mems:R.list()};$.post("ws_resv.php",i,function(e){$(".hhk-stayIndicate").show().parent("td").removeClass("hhk-loading");try{e=$.parseJSON(e)}catch(e){return void flagAlertMessage(e.message,"error")}if(e.gotopage&&window.open(e.gotopage,"_self"),e.error&&flagAlertMessage(e.error,"error"),e.stayCtrl){for(var t in e.stayCtrl){var a;$("#sb"+t).empty().html(e.stayCtrl[t].ctrl),$("#"+t+"cbStay").checkboxradio({classes:{"ui-checkboxradio-label":"hhk-unselected-text"}}),R.list()[t].stay="0",0<(a=$("#"+t+"lblStay")).length&&"1"==a.data("stay")&&a.click()}$(".hhk-getVDialog").button(),""!=$("#gstDate").val()&&""!=$("#gstCoDate").val()&&g.go($("#gstDate").val(),$("#gstCoDate").val()),$(".hhk-cbStay").change()}})}C(e.date1.t,d)}function C(e,t,a){var i=moment(e,"MMM D, YYYY"),r=moment().endOf("date");0<t&&i<=r&&!a?$("#btnCheckinNow").show():$("#btnCheckinNow").hide()}function g(){var r=this,s={};r.omitSelf=!0,r.numberGuests=0,r.idReservation=0,r.go=function(e,t){var a,i=$("#selResource");if(0===i.length)return;a=i.find("option:selected").val(),i.prop("disabled",!0),$("#hhk-roomChsrtitle").addClass("hhk-loading"),$("#hhkroomMsg").text("").hide(),s={},$("input.hhk-constraintsCB:checked").each(function(){s[$(this).data("cnid")]="ON"}),$.post("ws_ckin.php",{cmd:"newConstraint",rid:r.idReservation,numguests:r.numberGuests,expArr:e,expDep:t,idr:a,cbRS:s,omsf:r.omitSelf},function(e){var t;i.prop("disabled",!1),$("#hhk-roomChsrtitle").removeClass("hhk-loading");try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");e.selectr&&(t=$(e.selectr),i.children().remove(),t.children().appendTo(i),i.val(e.idResource).change(),e.msg&&""!==e.msg&&$("#hhkroomMsg").text(e.msg).show()),e.rooms&&f(e.rooms)})}}function y(){var i,r={},e=this;function s(e){return!1===t(e)&&(r[e[i]]=e,!0)}function t(e){return void 0!==r[e[i]]}e.hasItem=t,e.findItem=function(e,t){for(var a in r)if(r[a][e]==t)return r[a];return null},e.addItem=s,e.removeIndex=function(e){delete r[e]},e.list=function(){return r},e.makeList=function(e,t){for(var a in i=t,e)s(e[a])},e._list=r}function N(e,t){"use strict";$("input#txtPersonSearch").val(""),t.empty().append($(e.psgChooser)).dialog("option","buttons",{Open:function(){$(this).dialog("close"),I({idPsg:t.find("input[name=cbselpsg]:checked").val(),id:e.id,cmd:"getResv"})},Cancel:function(){$(this).dialog("close"),$("input#gstSearch").val("").focus()}}).dialog("option","title",e.patLabel+" Chooser"+(void 0===e.fullName?"":" For: "+e.fullName)).dialog("open")}function I(e){var t={id:e.id,rid:e.rid,idPsg:e.idPsg,vid:e.vid,span:e.span,isCheckin:D,gstDate:e.gstDate,gstCoDate:e.gstCoDate,cmd:e.cmd};$.post("ws_resv.php",t,function(e){try{e=$.parseJSON(e)}catch(e){return void flagAlertMessage(e.message,"error")}e.gotopage&&window.open(e.gotopage,"_self"),e.error&&(flagAlertMessage(e.error,"error",_),$("#btnDone").val("Save "+i).show()),E(e)})}function E(e){e.xfer||e.inctx?function(e){paymentRedirect(e,$("#xform"))}(e):e.resvChooser&&""!==e.resvChooser?function(e,t,a){"use strict";var i={};$("input#txtPersonSearch").val(""),t.empty().append($(e.resvChooser)).children().find("input:button").button(),t.children().find(".hhk-checkinNow").click(function(){window.open("CheckingIn.php?rid="+$(this).data("rid")+"&gid="+e.id,"_self")}),e.psgChooser&&""!==e.psgChooser&&(i[e.patLabel+" Chooser"]=function(){$(this).dialog("close"),N(e,a)}),e.resvTitle&&(i["New "+e.resvTitle]=function(){e.rid=-1,e.cmd="getResv",$(this).dialog("close"),I(e)}),i.Exit=function(){$(this).dialog("close"),$("input#gstSearch").val("").focus()},t.dialog("option","width","95%"),t.dialog("option","buttons",i),t.dialog("option","title",e.resvTitle+" Chooser"),t.dialog("open");var r=t.find("table").width();t.dialog("option","width",r+80)}(e,$("#resDialog"),$("#psgDialog")):e.psgChooser&&""!==e.psgChooser?N(e,$("#psgDialog")):(e.idPsg&&(r=e.idPsg),e.id&&(s=e.id),e.rid&&(d=e.rid),e.vid&&(n=e.vid),e.span&&(o=e.span),void 0!==e.hosp&&u.setUp(e.hosp),e.famSection&&(h.setUp(e),$("div#guestSearch").hide(),$("#btnDone").val("Save Family").show(),$("select.hhk-multisel").each(function(){$(this).multiselect({selectedList:3})})),void 0!==e.expDates&&""!==e.expDates&&(v.openControl=!1,v.setUp(e.expDates,m,$("#datesSection"))),void 0!==e.warning&&""!==e.warning&&flagAlertMessage(e.warning,"warning",_),void 0!==e.resv&&(e.resv.rdiv.rooms&&(l=e.resv.rdiv.rooms),p.setUp(e),$("#"+h.divFamDetailId).on("change",".hhk-cbStay",function(){var e=h.findStaysChecked()+h.findStays("r");if(p.$totalGuests.text(e),0<$("#selResource").length&&"0"!==$("#selResource").val()){var t="Room may be too small";e>l[$("#selResource").val()].maxOcc?$("#hhkroomMsg").text(t).show():$("#hhkroomMsg").text()==t&&$("#hhkroomMsg").text("").hide()}0<e?p.$totalGuests.parent().removeClass("ui-state-highlight"):p.$totalGuests.parent().addClass("ui-state-highlight")}),$("#"+h.divFamDetailId).on("click",".hhk-getVDialog",function(){var e=$(this).data("vid"),t=$(this).data("span");viewVisit(0,e,{"Show Statement":function(){window.open("ShowStatement.php?vid="+e,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+e+"&span="+t,"_blank")},Save:function(){saveFees(0,e,t,!1,payFailPage)},Cancel:function(){$(this).dialog("close")}},"Edit Visit #"+e+"-"+t,"",t),$("#submitButtons").hide()}),$(".hhk-cbStay").change(),void 0!==e.resv.rdiv.hideCkinBtn&&e.resv.rdiv.hideCkinBtn?$("#btnDone").hide():$("#btnDone").val(a).show(),0<e.rid&&($("#btnDelete").val("Delete "+i).show(),$("#btnShowReg").show(),$("#spnStatus").text(""===e.resv.rdiv.rStatTitle?"":" - "+e.resv.rdiv.rStatTitle)),C($("#gstDate").val(),e.rid,!1)),void 0!==e.addPerson&&($("input#txtPersonSearch").val(""),R.addItem(e.addPerson.mem)&&(P.addItem(e.addPerson.addrs),h.newGuestMarkup(e.addPerson,e.addPerson.mem.pref),h.findStaysChecked(),$(".hhk-cbStay").change(),$("#"+e.addPerson.mem.pref+"txtFirstName").focus())))}t.getReserve=I,t.verifyInput=function(){if(_.text("").hide(),!1===v.verify())return!1;if(!1===h.verify())return!1;if(!1===u.verify())return!1;if(!0===p.setupComplete&&!1===p.verify())return!1;return!0},t.loadResv=E,t.deleteReserve=function(e,a,i){var t="&cmd=delResv&rid="+e;$.post("ws_ckin.php",t,function(e){var t;try{t=$.parseJSON(e)}catch(e){flagAlertMessage(e.message,"error"),$(a).remove()}t.error&&(t.gotopage&&window.open(t.gotopage,"_self"),flagAlertMessage(t.error,"error"),$(a).remove()),t.warning&&(flagAlertMessage(t.warning,"warning"),i.hide()),t.result&&($(a).remove(),flagAlertMessage(t.result+' <a href="Reserve.php">Continue</a>',"success"))})},t.resvTitle=i,t.people=R,t.addrs=P,t.getIdPsg=function(){return r},t.getIdResv=function(){return d},t.getIdName=function(){return s},t.getIdVisit=function(){return n},t.getSpan=function(){return o},t.setRooms=f}
