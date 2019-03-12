function resvManager(e){var t=this,D=e.patLabel,i=e.resvTitle,a=e.saveButtonLabel,M=e.patBD,P=e.patAddr,R=e.gstAddr,N=e.patAsGuest,_=void 0!==e.emergencyContact&&e.emergencyContact,r=void 0!==e.isCheckin&&e.isCheckin,I=e.addrPurpose,s=e.idPsg,n=e.rid,o=e.id,d=e.vid,l=e.span,E=new u,F=new u,m=new function(p){var c,e=this,h="divfamDetail",v=!1;function u(){var t=0;return $(".hhk-cbStay").each(function(){var e=$(this).data("prefix");$(this).prop("checked")?(E.list()[e].stay="1",t++):E.list()[e].stay="0"}),t}function f(){var e=$("input[type=radio][name=rbPriGuest]:checked").val();for(var t in E.list())E.list()[t].pri="0";void 0!==e&&(E.list()[e].pri="1")}function g(e){var t=$("#divfamDetail");!0===e?(t.show("blind"),t.prev("div").removeClass("ui-corner-all").addClass("ui-corner-top")):(t.hide("blind"),t.prev("div").addClass("ui-corner-all").removeClass("ui-corner-top"))}function m(e,t){if(void 0===e.No_Return||""===e.No_Return){if(void 0!==e.id)if(0<e.id&&null!==E.findItem("id",e.id))flagAlertMessage("This person is already listed here. ","alert");else{var a={id:e.id,rid:t.rid,idPsg:t.idPsg,isCheckin:r,cmd:"addResvGuest"};B(a)}}else flagAlertMessage("This person is set for No Return: "+e.No_Return+".","alert")}function C(e){"use strict";$("#ecSearch").dialog("close");var t=parseInt(e.id,10);if(!1===isNaN(t)&&0<t){var a=$("#hdnEcSchPrefix").val();if(""==a)return;$("#"+a+"txtEmrgFirst").val(e.first),$("#"+a+"txtEmrgLast").val(e.last),$("#"+a+"txtEmrgPhn").val(e.phone),$("#"+a+"txtEmrgAlt").val(""),$("#"+a+"selEmrgRel").val("")}}function y(e){var t=/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/,a=!1;return 0<$("#"+e+"incomplete").length&&!1===$("#"+e+"incomplete").prop("checked")&&($("."+e+"hhk-addr-val").not(".hhk-MissingOk").each(function(){""!==$(this).val()||$(this).hasClass("bfh-states")?$(this).removeClass("ui-state-error"):($(this).addClass("ui-state-error"),a=!0)}),a)?($("#"+e+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+e+"toggleAddr").click(),"Some or all of the indicated addresses are missing.  "):($('.hhk-phoneInput[id^="'+e+'txtPhone"]').each(function(){""!==$.trim($(this).val())&&!1===t.test($(this).val())?($(this).addClass("ui-state-error"),$("#"+e+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+e+"toggleAddr").click(),$("#"+e+"phEmlTabs").tabs("option","active",1),a=!0):$(this).removeClass("ui-state-error")}),"")}function k(e){var t=!1,a=$("#"+e+"txtEmrgFirst"),i=$("#"+e+"txtEmrgLast"),r=$("#"+e+"txtEmrgPhn"),s=$("#"+e+"selEmrgRel");return a.removeClass("ui-state-error"),i.removeClass("ui-state-error"),r.removeClass("ui-state-error"),s.removeClass("ui-state-error"),0<$("#"+e+"cbEmrgLater").length&&!1===$("#"+e+"cbEmrgLater").prop("checked")&&(""===a.val()&&""===i.val()&&(a.addClass("ui-state-error"),i.addClass("ui-state-error"),t=!0),""===r.val()&&(r.addClass("ui-state-error"),t=!0),""===s.val()&&(s.addClass("ui-state-error"),t=!0),t)?"Some or all of the indicated Emergency Contact Information is missing.  ":""}function b(e){for(var t in F.list())e!=t&&(""!==$("#"+t+"adraddress1"+I).val()&&""!==$("#"+t+"adrzip"+I).val()||($("#"+t+"adraddress1"+I).val(F.list()[e].Address_1),$("#"+t+"adraddress2"+I).val(F.list()[e].Address_2),$("#"+t+"adrcity"+I).val(F.list()[e].City),$("#"+t+"adrcounty"+I).val(F.list()[e].County),$("#"+t+"adrzip"+I).val(F.list()[e].Postal_Code),$("#"+t+"adrcountry"+I).val()!=F.list()[e].Country_Code&&$("#"+t+"adrcountry"+I).val(F.list()[e].Country_Code).change(),$("#"+t+"adrstate"+I).val(F.list()[e].State_Province),!0===$("#"+e+"incomplete").prop("checked")?$("#"+t+"incomplete").prop("checked",!0):x(t)&&!0===$("#"+t+"incomplete").prop("checked")&&$("#"+t+"incomplete").prop("checked",!1),A($("#"+t+"liaddrflag"))))}function x(e){return!(void 0===e||!e||""==e)&&(""!==$("#"+e+"adraddress1"+I).val()&&""!==$("#"+e+"adrzip"+I).val()&&""!==$("#"+e+"adrstate"+I).val()&&""!==$("#"+e+"adrcity"+I).val())}function w(e,t){$(".hhk-addrPicker").remove();var a=$('<select id="selAddrch" multiple="multiple" />'),i=0,r=[];for(var s in F.list())if(""!=F.list()[s].Address_1||""!=F.list()[s].Postal_Code){for(var n=!0,o=F.list()[s].Address_1+", "+(""==F.list()[s].Address_2?"":F.list()[s].Address_2+", ")+F.list()[s].City+", "+F.list()[s].State_Province+"  "+F.list()[s].Postal_Code,d=0;d<=r.length;d++)r[d]!=o||(n=!1);n&&(r[i]=o,i++,$('<option class="hhk-addrPickerPanel" value="'+s+'">'+o+"</option>").appendTo(a))}if(0<i){a.prop("size",i+1).prepend($('<option value="0" >(Cancel)</option>')),a.change(function(){!function(e,t){if(0==t)return $("#divSelAddr").remove();$("#"+e+"adraddress1"+I).val(F.list()[t].Address_1),$("#"+e+"adraddress2"+I).val(F.list()[t].Address_2),$("#"+e+"adrcity"+I).val(F.list()[t].City),$("#"+e+"adrcounty"+I).val(F.list()[t].County),$("#"+e+"adrzip"+I).val(F.list()[t].Postal_Code),$("#"+e+"adrcountry"+I).val()!=F.list()[t].Country_Code&&$("#"+e+"adrcountry"+I).val(F.list()[t].Country_Code).change();$("#"+e+"adrstate"+I).val(F.list()[t].State_Province),x(e)&&!0===$("#"+e+"incomplete").prop("checked")&&$("#"+e+"incomplete").prop("checked",!1);A($("#"+e+"liaddrflag")),$("#divSelAddr").remove()}(t,$(this).val())});var l=$('<div id="divSelAddr" style="position:absolute; vertical-align:top;" class="hhk-addrPicker hhk-addrPickerPanel"/>').append($('<p class="hhk-addrPickerPanel">Choose an Address: </p>')).append(a).appendTo($("body"));l.position({my:"left top",at:"right center",of:e})}}function S(e){void 0!==e&&(F.list()[e].Address_1=$("#"+e+"adraddress1"+I).val(),F.list()[e].Address_2=$("#"+e+"adraddress2"+I).val(),F.list()[e].City=$("#"+e+"adrcity"+I).val(),F.list()[e].County=$("#"+e+"adrcounty"+I).val(),F.list()[e].State_Province=$("#"+e+"adrstate"+I).val(),F.list()[e].Country_Code=$("#"+e+"adrcountry"+I).val(),F.list()[e].Postal_Code=$("#"+e+"adrzip"+I).val(),A($("#"+e+"liaddrflag")))}function A(e){var t=e.data("pref");!0===$("#"+t+"incomplete").prop("checked")?(e.show().find("span").removeClass("ui-icon-alert").addClass("ui-icon-check").attr("title","Incomplete Address is checked"),e.removeClass("ui-state-error").addClass("ui-state-highlight")):x(t)?e.hide():(e.show().find("span").removeClass("ui-icon-check").addClass("ui-icon-alert").attr("title","Address is Incomplete"),e.removeClass("ui-state-highlight").addClass("ui-state-error"))}e.findStaysChecked=u,e.findPrimaryGuest=f,e.setUp=function(t){var e;if(void 0===t.famSection||void 0===t.famSection.tblId||""===t.famSection.tblId)return;!1===v&&(a=t,i=$("<div/>").addClass("ui-widget-content ui-corner-bottom hhk-tdbox").prop("id",h).css("padding","5px"),c=$("<table/>").prop("id",a.famSection.tblId).addClass("hhk-table").append($("<thead/>").append($(a.famSection.tblHead))).append($("<tbody/>")),i.append(c).append($(a.famSection.adtnl)),s=$("<ul style='list-style-type:none; float:right;margin-left:5px;padding-top:2px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='f_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),(r=$('<div id="divfamHdr" style="padding:2px; cursor:pointer;"/>').append($(a.famSection.hdr)).append(s).append('<div style="clear:both;"/>')).addClass("ui-widget-header ui-state-default ui-corner-top"),r.click(function(){"none"===i.css("display")?(i.show("blind"),r.removeClass("ui-corner-all").addClass("ui-corner-top")):(i.hide("blind"),r.removeClass("ui-corner-top").addClass("ui-corner-all"))}),p.empty().append(r).append(i).show());var a,i,r,s;for(var n in t.famSection.mem){var o=E.findItem("pref",t.famSection.mem[n].pref);o&&(c.find("tr#"+o.id+"n").remove(),c.find("tr#"+o.id+"a").remove(),c.find("input#"+o.pref+"idName").parents("tr").next("tr").remove(),c.find("input#"+o.pref+"idName").parents("tr").remove(),E.removeIndex(o.pref),F.removeIndex(o.pref))}E.makeList(t.famSection.mem,"pref"),F.makeList(t.famSection.addrs,"pref"),void 0!==t.famSection.tblBody[1]&&c.find("tbody:first").prepend($(t.famSection.tblBody[1]));void 0!==t.famSection.tblBody[0]&&c.find("tbody:first").prepend($(t.famSection.tblBody[0]));for(var d in t.famSection.tblBody)"0"!==d&&"1"!==d&&c.find("tbody:first").append($(t.famSection.tblBody[d]));$(".hhk-cbStay").checkboxradio({classes:{"ui-checkboxradio-label":"hhk-unselected-text"}}),$(".hhk-lblStay").each(function(){"1"==$(this).data("stay")&&$(this).click()}),$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"}),$(".hhk-addrPanel").find("select.bfh-countries").each(function(){var e=$(this);e.bfhcountries(e.data()),$(this).data("dirrty-initial-value",$(this).data("country"))}),$(".hhk-addrPanel").find("select.bfh-states").each(function(){var e=$(this);e.bfhstates(e.data()),$(this).data("dirrty-initial-value",$(this).data("state"))}),$(".hhk-phemtabs").tabs(),verifyAddrs("#divfamDetail"),$("input.hhk-zipsearch").each(function(){createZipAutoComplete($(this),"ws_admin.php",void 0,S)}),!1===v&&($("#lnCopy").click(function(){var e=$("input.hhk-lastname").first().val();$("input.hhk-lastname").each(function(){""===$(this).val()&&$(this).val(e)})}),$("#adrCopy").click(function(){var e=$("li.hhk-AddrFlag").first().data("pref");b(e)}),$("#"+h).on("click",".hhk-togAddr",function(){e=$(this),$(this).siblings(),"none"===$(this).parents("tr").next("tr").css("display")?($(this).parents("tr").next("tr").show(),e.find("span").removeClass("ui-icon-circle-triangle-s").addClass("ui-icon-circle-triangle-n"),e.attr("title","Hide Address Section")):($(this).parents("tr").next("tr").hide(),e.find("span").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"),e.attr("title","Show Address Section"),isIE()&&$("#divSelAddr").remove())}),$("#"+h).on("click",".hhk-AddrFlag",function(){$("#"+$(this).data("pref")+"incomplete").click()}),$("#"+h).on("change",".hhk-copy-target",function(){S($(this).data("pref"))}),$("#"+h).on("click",".hhk-addrCopy",function(){w($(this),$(this).data("prefix"))}),$("#"+h).on("click",".hhk-addrErase",function(){var e;e=$(this).data("prefix"),$("#"+e+"adraddress1"+I).val(""),$("#"+e+"adraddress2"+I).val(""),$("#"+e+"adrcity"+I).val(""),$("#"+e+"adrcounty"+I).val(""),$("#"+e+"adrstate"+I).val(""),$("#"+e+"adrcountry"+I).val(""),$("#"+e+"adrzip"+I).val(""),A($("#"+e+"liaddrflag"))}),$("#"+h).on("click",".hhk-incompleteAddr",function(){A($("#"+$(this).data("prefix")+"liaddrflag"))}),$("#"+h).on("click",".hhk-removeBtn",function(){(""===$("#"+$(this).data("prefix")+"txtFirstName").val()&&""===$("#"+$(this).data("prefix")+"txtLastName").val()||!1!==confirm("Remove this person: "+$("#"+$(this).data("prefix")+"txtFirstName").val()+" "+$("#"+$(this).data("prefix")+"txtLastName").val()+"?"))&&(E.removeIndex($(this).data("prefix")),F.removeIndex($(this).data("prefix")),$(this).parentsUntil("tbody","tr").next().remove(),$(this).parentsUntil("tbody","tr").remove())}),$("#"+h).on("change",".patientRelch",function(){"slf"===$(this).val()?E.list()[$(this).data("prefix")].role="p":E.list()[$(this).data("prefix")].role="g"}),createAutoComplete($("#txtPersonSearch"),3,{cmd:"role",gp:"1"},function(e){m(e,t)}),$("#"+h).on("click",".hhk-emSearch",function(){$("#hdnEcSchPrefix").val($(this).data("prefix")),$("#ecSearch").dialog("open")}),createAutoComplete($("#txtemSch"),3,{cmd:"filter",add:"phone",basis:"g"},C),$("ul.hhk-ui-icons li").hover(function(){$(this).addClass("ui-state-hover")},function(){$(this).removeClass("ui-state-hover")}));for(var l in E.list())A($("#"+l+"liaddrflag"));$(".hhk-togAddr").each(function(){$(this).parents("tr").next("tr").hide(),$(this).find("span").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"),$(this).attr("title","Show Address Section")}),v=!0},e.newGuestMarkup=function(e,t){var a,i,r,s,n;if(void 0===e.tblId||""==e.tblId)return;if(0===c.length)return;r=c.children("tbody").children("tr").last().hasClass("odd")?"even":"odd";c.find("tbody:first").append($(e.ntr).addClass(r)).append($(e.atr).addClass(r)),$("#"+t+"cbStay").checkboxradio({classes:{"ui-checkboxradio-label":"hhk-unselected-text"}}),"1"==$("#"+t+"lblStay").data("stay")&&$("#"+t+"lblStay").click();$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"}),s=$("#"+t+"liaddrflag"),n=s.siblings(),A(s),n.parents("tr").next("tr").hide(),n.find("span").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"),n.attr("title","Show Address Section"),(a=$("#"+t+"adrcountry"+I)).bfhcountries(a.data()),$(this).data("dirrty-initial-value",$(this).data("country")),(i=$("#"+t+"adrstate"+I)).bfhstates(i.data()),$(this).data("dirrty-initial-value",$(this).data("state")),$("#"+t+"phEmlTabs").tabs(),$("input#"+t+"adrzip1").each(function(){createZipAutoComplete($(this),"ws_admin.php",void 0,S)})},e.verify=function(){var e=0,t=0,a=0,i=0,r=!1,s=0,n=!1;if($(".patientRelch").removeClass("ui-state-error"),$(".patientRelch").each(function(){""===$(this).val()?($(this).addClass("ui-state-error"),n=!0):$(this).removeClass("ui-state-error")}),n)return flagAlertMessage("Set the highlighted Relationship(s).","alert",V),!1;for(var o in f(),u(),E.list())e++,"p"===E.list()[o].role&&t++,"1"===E.list()[o].stay&&a++,"1"===E.list()[o].pri&&i++,$("#"+o+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-n")&&$("#"+o+"toggleAddr").click();{if(t<1)return flagAlertMessage("Choose a "+D+".","alert",V),$(".patientRelch").addClass("ui-state-error"),!1;if(1<t){for(var o in flagAlertMessage("Only 1 "+D+" is allowed.","alert",V),E.list())"p"===E.list()[o].role&&$("#"+o+"selPatRel").addClass("ui-state-error");return!1}}if(a<1)return flagAlertMessage("There is no one actually staying.  Pick someone to stay.","alert",V),!1;if($("input.hhk-rbPri").parent().removeClass("ui-state-error"),0===i&&1===e)for(var o in E.list())E.list()[o].pri="1";else if(0===i)return V.text("Set one guest as primary guest.").show(),flagAlertMessage("Set one guest as primary guest.","alert",V),$("input.hhk-rbPri").parent().addClass("ui-state-error"),!1;if(p.find(".hhk-lastname").each(function(){""==$(this).val()?($(this).addClass("ui-state-error"),r=!0):$(this).removeClass("ui-state-error")}),p.find(".hhk-firstname").each(function(){""==$(this).val()?($(this).addClass("ui-state-error"),r=!0):$(this).removeClass("ui-state-error")}),!0===r)return g(!0),flagAlertMessage("Enter a first and last name for the people highlighted.","alert",V),!1;_&&p.find(".hhk-EmergCb").each(function(){var e=k($(this).data("prefix"));!0!==$(this).prop("checked")&&""!==e||s++});for(var d in E.list()){if("p"===E.list()[d].role){if(M&""===$("#"+d+"txtBirthDate").val())return $("#"+d+"txtBirthDate").addClass("ui-state-error"),flagAlertMessage(D+" is missing the Birth Date.","alert",V),g(!0),!1;if($("#"+d+"txtBirthDate").removeClass("ui-state-error"),P||N){var l=y(d);if(""!==l)return flagAlertMessage(l,"alert",V),g(!0),$("#"+d+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+d+"toggleAddr").click(),!1}}else if(R){var l=y(d);if(""!==l)return flagAlertMessage(l,"alert",V),g(!0),$("#"+d+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+d+"toggleAddr").click(),!1}if(0<$("#"+d+"txtBirthDate").length&&""!==$("#"+d+"txtBirthDate").val()){var c=new Date($("#"+d+"txtBirthDate").val()),h=new Date;if(h<c)return $("#"+d+"txtBirthDate").addClass("ui-state-error"),flagAlertMessage("This birth date cannot be in the future.","alert",V),g(!0),!1;$("#"+d+"txtBirthDate").removeClass("ui-state-error")}if(_&&s<1){var l=k(d);if(""!==l)return flagAlertMessage(l,"alert",V),g(!0),$("#"+d+"toggleAddr").find("span").hasClass("ui-icon-circle-triangle-s")&&$("#"+d+"toggleAddr").click(),!1}}return!(v=!1)},e.divFamDetailId=h,e.$famTbl=c}($("#famSection")),c=new function(c){var h,p,v,u,f=this;function g(e){var t={},a=c.find("#btnFapp");0<a.length&&($("#faDialog").dialog({autoOpen:!1,resizable:!0,width:680,modal:!0,title:"Income Chooser",close:function(){$("div#submitButtons").show()},open:function(){$("div#submitButtons").hide()},buttons:{Save:function(){$.post("ws_ckin.php",$("#formf").serialize()+"&cmd=savefap&rid="+e.rid,function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Bad JSON Encoding")}if(e.gotopage&&window.open(e.gotopage,"_self"),e.rstat&&1==e.rstat){var t=$("#selRateCategory");e.rcat&&""!=e.rcat&&0<t.length&&(t.val(e.rcat),t.change())}}),$(this).dialog("close")},Exit:function(){$(this).dialog("close")}}}),a.button().click(function(){getIncomeDiag(e.rid)})),t.rateList=e.resv.rdiv.ratelist,t.resources=e.resv.rdiv.rooms,t.visitFees=e.resv.rdiv.vfee,t.idResv=n,setupRates(t),$("#selResource").change(function(){$("#selRateCategory").change();var e=$("option:selected",this),t=e.parent()[0].label;null==t?$("#hhkroomMsg").hide():$("#hhkroomMsg").text(t).show()})}function a(e){"use strict";var t="";return""===$("#car"+e+"txtVehLic").val()&&""===$("#car"+e+"txtVehMake").val()?"Enter vehicle info or check the 'No Vehicle' checkbox. ":(""===$("#car"+e+"txtVehLic").val()?(""===$("#car"+e+"txtVehModel").val()&&($("#car"+e+"txtVehModel").addClass("ui-state-highlight"),t="Enter Model"),""===$("#car"+e+"txtVehColor").val()&&($("#car"+e+"txtVehColor").addClass("ui-state-highlight"),t="Enter Color"),""===$("#car"+e+"selVehLicense").val()&&($("#car"+e+"selVehLicense").addClass("ui-state-highlight"),t="Enter state license plate registration")):""===$("#car"+e+"txtVehMake").val()&&""===$("#car"+e+"txtVehLic").val()&&($("#car"+e+"txtVehLic").addClass("ui-state-highlight"),t="Enter a license plate number."),t)}f.setupComplete=!1,f.checkPayments=!0,f.setUp=function(t){h=$("<div/>").addClass("ui-widget-content ui-corner-bottom hhk-tdbox").prop("id","divResvDetail").css("padding","5px"),void 0!==t.resv.rdiv.rChooser&&h.append($(t.resv.rdiv.rChooser));void 0!==t.resv.rdiv.rate&&h.append($(t.resv.rdiv.rate));void 0!==t.resv.rdiv.cof&&h.append(t.resv.rdiv.cof);void 0!==t.resv.rdiv.rstat&&h.append($(t.resv.rdiv.rstat));void 0!==t.resv.rdiv.vehicle&&(p=$(t.resv.rdiv.vehicle),h.append(p),a=1,i=(e=p).find("#cbNoVehicle"),r=e.find("#btnNextVeh"),s=e.find("#tblVehicle"),i.change(function(){this.checked?s.hide("scale, horizontal"):s.show("scale, horizontal")}),i.change(),r.button(),r.click(function(){e.find("#trVeh"+a).show("fade"),4<++a&&r.hide("fade")}));var e,a,i,r,s;void 0!==t.resv.rdiv.pay&&h.append($(t.resv.rdiv.pay));void 0!==t.resv.rdiv.notes&&h.append((n=t.rid,(o=$(t.resv.rdiv.notes)).notesViewer({linkId:n,linkType:"reservation",newNoteAttrs:{id:"taNewNote",name:"taNewNote"},alertMessage:function(e,t){flagAlertMessage(e,t)}}),o));var n,o;void 0!==t.resv.rdiv.wlnotes&&h.append($(t.resv.rdiv.wlnotes));u=$("<ul style='list-style-type:none; float:right; margin-left:5px; padding-top:2px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='r_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),(v=$('<div id="divResvHdr" style="padding:2px; cursor:pointer;"/>').append($(t.resv.hdr)).append(u).append('<div style="clear:both;"/>')).addClass("ui-widget-header ui-state-default ui-corner-top"),v.click(function(e){var t=$(e.target);"divResvHdr"!==t[0].id&&"r_drpDown"!==t[0].id||("none"===h.css("display")?(h.show("blind"),v.removeClass("ui-corner-all").addClass("ui-corner-top")):(h.hide("blind"),v.removeClass("ui-corner-top").addClass("ui-corner-all")))}),c.empty().append(v).append(h).show(),f.$totalGuests=$("#spnNumGuests"),f.origRoomId=$("#selResource").val(),f.checkPayments=!0,0<$(".hhk-viewResvActivity").length&&$(".hhk-viewResvActivity").click(function(){$.post("ws_ckin.php",{cmd:"viewActivity",rid:$(this).data("rid")},function(e){if((e=$.parseJSON(e)).error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,"error");e.activity&&($("div#submitButtons").hide(),$("#activityDialog").children().remove(),$("#activityDialog").append($(e.activity)),$("#activityDialog").dialog("open"))})});$("#btnShowCnfrm").button().click(function(){var e=$("#spnAmount").text();""===e&&(e=0),$.post("ws_ckin.php",{cmd:"confrv",rid:$(this).data("rid"),amt:e,eml:"0"},function(e){if((t=$.parseJSON(e)).error)return t.gotopage&&window.open(t.gotopage,"_self"),void flagAlertMessage(t.error,"error");t.confrv&&($("div#submitButtons").hide(),$("#frmConfirm").children().remove(),$("#frmConfirm").html(t.confrv).append($('<div style="padding-top:10px;" class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><span>Email Address </span><input type="text" id="confEmail" value="'+t.email+'"/></div>')),$("#confirmDialog").dialog("open"))})}),d=t.rid,y.idReservation=d,$("input.hhk-constraintsCB").change(function(){y.go($("#gstDate").val(),$("#gstCoDate").val())}),void 0!==t.resv.rdiv.rate&&g(t);var d;void 0!==t.resv.rdiv.pay&&(l=t,$("#paymentDate").datepicker({yearRange:"-1:+00",numberOfMonths:1}),setupPayments(l.resv.rdiv.rooms,$("#selResource"),$("#selRateCategory")));var l;0<$("#addGuestHeader").length&&((C=new k($("#addGuestHeader"))).openControl=!0,C.setUp(t.resv.rdiv,b),y.omitSelf=!1,y.idReservation=0,f.checkPayments=!1,$("#selResource").change(function(){""!==$("#gstDate").val()&&""!==$("#gstCoDate").val()&&($(this).val()!==f.origRoomId?($("#divRateChooser").show(),$("#divPayChooser").show(),f.checkPayments=!0):($("#divRateChooser").hide(),$("#divPayChooser").hide(),f.checkPayments=!1))}),$("#"+m.divFamDetailId).on("change",".hhk-cbStay",function(){y.numberGuests=m.findStaysChecked()}));f.setupComplete=!0},f.verify=function(){if(0<$("#cbNoVehicle").length){if(!1===$("#cbNoVehicle").prop("checked")){var e=a(1);if(""!=e){var t=a(2);if(""!=t)return $("#vehValidate").text(t),flagAlertMessage(e,"alert",V),!1}}$("#vehValidate").text("")}if(r&&!0===f.checkPayments){if($("#selCategory").val()==fixedRate&&0<$("#txtFixedRate").length&&""==$("#txtFixedRate").val())return flagAlertMessage("Set the Room Rate to an amount, or to 0.","alert",V),$("#txtFixedRate").addClass("ui-state-error"),!1;if($("#txtFixedRate").removeClass("ui-state-error"),0<$("input#feesPayment").length&&""==$("input#feesPayment").val())return flagAlertMessage("Set the Room Fees to an amount, or 0.","alert",V),$("#payChooserMsg").text("Set the Room Fees to an amount, or 0.").show(),$("input#feesPayment").addClass("ui-state-error"),!1;if($("input#feesPayment").removeClass("ui-state-error"),void 0!==verifyAmtTendrd&&!1===verifyAmtTendrd())return!1}return!0}}($("#resvSection")),h=new function(r){var s=this;s.setupComplete=!1,s.setUp=function(e){var t=$(e.div).addClass("ui-widget-content").prop("id","divhospDetail").hide(),a=$("<ul style='list-style-type:none; float:right;margin-left:5px;padding-top:2px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='h_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),i=$('<div id="divhospHdr" style="padding:2px; cursor:pointer;"/>').append($(e.hdr)).append(a).append('<div style="clear:both;"/>');i.addClass("ui-widget-header ui-state-default ui-corner-all"),i.click(function(){"none"===t.css("display")?(t.show("blind"),i.removeClass("ui-corner-all").addClass("ui-corner-top")):(t.hide("blind"),i.removeClass("ui-corner-top").addClass("ui-corner-all"))}),r.empty().append(i).append(t),$("#txtEntryDate, #txtExitDate").datepicker({yearRange:"-01:+01",changeMonth:!0,changeYear:!0,autoSize:!0,dateFormat:"M d, yy"}),0<$("#txtAgentSch").length&&(createAutoComplete($("#txtAgentSch"),3,{cmd:"filter",basis:"ra"},getAgent),""===$("#a_txtLastName").val()&&$(".hhk-agentInfo").hide()),0<$("#txtDocSch").length&&(createAutoComplete($("#txtDocSch"),3,{cmd:"filter",basis:"doc"},getDoc),""===$("#d_txtLastName").val()&&$(".hhk-docInfo").hide()),verifyAddrs("#divhospDetail"),r.on("change","#selHospital, #selAssoc",function(){var e=$("#selAssoc").find("option:selected").text();""!=e&&(e+="/ "),$("span#spnHospName").text(e+$("#selHospital").find("option:selected").text())}),r.show(),""===$("#selHospital").val()&&i.click(),s.setupComplete=!0},s.verify=function(){return r.find(".ui-state-error").each(function(){$(this).removeClass("ui-state-error")}),0<$("#selHospital").length&&!0===s.setupComplete&&""==$("#selHospital").val()?($("#selHospital").addClass("ui-state-error"),flagAlertMessage("Select a hospital.","alert",V),$("#divhospDetail").show("blind"),$("#divhospHdr").removeClass("ui-corner-all").addClass("ui-corner-top"),!1):($("#divhospDetail").hide("blind"),$("#divhospHdr").removeClass("ui-corner-top").addClass("ui-corner-all"),!0)}}($("#hospitalSection")),C=new k($("#datesSection")),y=new y,V=$("#pWarnings");function p(e){e}function k(t){var o=this;o.setupComplete=!1,o.ciDate=new Date,o.coDate=new Date,o.openControl=!1,o.setUp=function(i,r){if(t.empty(),i.mu&&""!==i.mu){t.append($(i.mu));var s=$("#gstDate"),n=$("#gstCoDate"),e=parseInt(i.defdays,10);(isNaN(e)||e<1)&&(e=21),$("#spnRangePicker").dateRangePicker({format:"MMM D, YYYY",separator:" to ",minDays:1,autoClose:!0,showShortcuts:!0,shortcuts:{"next-days":[e]},getValue:function(){return s.val()&&n.val()?s.val()+" to "+n.val():""},setValue:function(e,t,a){s.val(t),n.val(a)}}).bind("datepicker-change",function(e,t){var a=Math.ceil((t.date2.getTime()-t.date1.getTime())/864e5);$("#"+i.daysEle).val(a),0<$("#spnNites").length&&$("#spnNites").text(a),$("#gstDate").removeClass("ui-state-error"),$("#gstCoDate").removeClass("ui-state-error"),$.isFunction(r)&&r(t)}),t.show(),o.openControl&&$("#spnRangePicker").data("dateRangePicker").open()}setupComplete=!0},o.verify=function(){var e=$("#gstDate"),t=$("#gstCoDate");if(e.removeClass("ui-state-error"),t.removeClass("ui-state-error"),""===e.val())return e.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the check-in date.","alert",V),!1;if(o.ciDate=new Date(e.val()),isNaN(o.ciDate.getTime()))return e.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the check-in date.","alert",V),!1;if(void 0!==r&&!0===r){var a=moment($("#gstDate").val(),"MMM D, YYYY");if(moment().endOf("date")<a)return e.addClass("ui-state-error"),flagAlertMessage("Set the Check in date to today or earlier.","alert",V),!1}return""===t.val()?(t.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the expected departure date.","alert",V),!1):(o.coDate=new Date(t.val()),isNaN(o.coDate.getTime())?(t.addClass("ui-state-error"),flagAlertMessage("This "+i+" is missing the expected departure date","alert",V),!1):!(o.ciDate>o.coDate)||(e.addClass("ui-state-error"),flagAlertMessage("This "+i+"'s check-in date is after the expected departure date.","alert",V),!1))}}function b(e){V.text("").hide();var t=!1;for(var a in E.list())if(0<E.list()[a].id){t=!0;break}if(t){$(".hhk-stayIndicate").hide().parent("td").addClass("hhk-loading");var i={cmd:"updateAgenda",idPsg:s,idResv:n,idVisit:d,dt1:e.date1.getFullYear()+"-"+(e.date1.getMonth()+1)+"-"+e.date1.getDate(),dt2:e.date2.getFullYear()+"-"+(e.date2.getMonth()+1)+"-"+e.date2.getDate(),mems:E.list()};$.post("ws_resv.php",i,function(e){$(".hhk-stayIndicate").show().parent("td").removeClass("hhk-loading");try{e=$.parseJSON(e)}catch(e){return void flagAlertMessage(e.message,"error")}if(e.gotopage&&window.open(e.gotopage,"_self"),e.error&&flagAlertMessage(e.error,"error"),e.stayCtrl){for(var t in e.stayCtrl){var a;$("#sb"+t).empty().html(e.stayCtrl[t].ctrl),$("#"+t+"cbStay").checkboxradio({classes:{"ui-checkboxradio-label":"hhk-unselected-text"}}),E.list()[t].stay="0",0<(a=$("#"+t+"lblStay")).length&&"1"==a.data("stay")&&a.click()}$(".hhk-getVDialog").button(),""!=$("#gstDate").val()&&""!=$("#gstCoDate").val()&&y.go($("#gstDate").val(),$("#gstCoDate").val())}})}v(e.date1.t,n)}function v(e,t,a){var i=moment(e,"MMM D, YYYY"),r=moment().endOf("date");0<t&&i<=r&&!a?$("#btnCheckinNow").show():$("#btnCheckinNow").hide()}function y(){var r=this,s={};r.omitSelf=!0,r.numberGuests=0,r.idReservation=0,r.go=function(e,t){var a,i=$("#selResource");if(0===i.length)return;a=i.find("option:selected").val(),i.prop("disabled",!0),$("#hhk-roomChsrtitle").addClass("hhk-loading"),$("#hhkroomMsg").text("").hide(),s={},$("input.hhk-constraintsCB:checked").each(function(){s[$(this).data("cnid")]="ON"}),$.post("ws_ckin.php",{cmd:"newConstraint",rid:r.idReservation,numguests:r.numberGuests,expArr:e,expDep:t,idr:a,cbRS:s,omsf:r.omitSelf},function(e){var t;i.prop("disabled",!1),$("#hhk-roomChsrtitle").removeClass("hhk-loading");try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");e.selectr&&(t=$(e.selectr),i.children().remove(),t.children().appendTo(i),i.val(e.idResource).change(),e.msg&&""!==e.msg&&$("#hhkroomMsg").text(e.msg).show()),e.rooms&&p(e.rooms)})}}function u(){var i,r={},e=this;function s(e){return!1===t(e)&&(r[e[i]]=e,!0)}function t(e){return void 0!==r[e[i]]}e.hasItem=t,e.findItem=function(e,t){for(var a in r)if(r[a][e]==t)return r[a];return null},e.addItem=s,e.removeIndex=function(e){delete r[e]},e.list=function(){return r},e.makeList=function(e,t){for(var a in i=t,e)s(e[a])},e._list=r}function f(e,t){"use strict";$("input#txtPersonSearch").val(""),t.empty().append($(e.psgChooser)).dialog("option","buttons",{Open:function(){$(this).dialog("close"),B({idPsg:t.find("input[name=cbselpsg]:checked").val(),id:e.id,cmd:"getResv"})},Cancel:function(){$(this).dialog("close"),$("input#gstSearch").val("").focus()}}).dialog("option","title",e.patLabel+" Chooser"+(void 0===e.fullName?"":" For: "+e.fullName)).dialog("open")}function B(e){var t={id:e.id,rid:e.rid,idPsg:e.idPsg,vid:e.vid,span:e.span,isCheckin:r,cmd:e.cmd};$.post("ws_resv.php",t,function(e){try{e=$.parseJSON(e)}catch(e){return void flagAlertMessage(e.message,"error")}e.gotopage&&window.open(e.gotopage,"_self"),e.error&&(flagAlertMessage(e.error,"error",V),$("#btnDone").val("Save "+i).show()),g(e)})}function g(e){e.xfer||e.inctx?paymentRedirect(e,$("#xform")):e.resvChooser&&""!==e.resvChooser?function(e,t,a){"use strict";var i={};$("input#txtPersonSearch").val(""),t.empty().append($(e.resvChooser)).children().find("input:button").button(),t.children().find(".hhk-checkinNow").click(function(){window.open("CheckingIn.php?rid="+$(this).data("rid")+"&gid="+e.id,"_self")}),e.psgChooser&&""!==e.psgChooser&&(i[e.patLabel+" Chooser"]=function(){$(this).dialog("close"),f(e,a)}),e.resvTitle&&(i["New "+e.resvTitle]=function(){e.rid=-1,e.cmd="getResv",$(this).dialog("close"),B(e)}),i.Exit=function(){$(this).dialog("close"),$("input#gstSearch").val("").focus()},t.dialog("option","width","95%"),t.dialog("option","buttons",i),t.dialog("option","title",e.resvTitle+" Chooser"),t.dialog("open");var r=t.find("table").width();t.dialog("option","width",r+80)}(e,$("#resDialog"),$("#psgDialog")):e.psgChooser&&""!==e.psgChooser?f(e,$("#psgDialog")):(e.idPsg&&(s=e.idPsg),e.id&&(o=e.id),e.rid&&(n=e.rid),e.vid&&(d=e.vid),e.span&&(l=e.span),void 0!==e.hosp&&h.setUp(e.hosp),e.famSection&&(m.setUp(e),$("div#guestSearch").hide(),$("#btnDone").val("Save Family").show(),$("select.hhk-multisel").each(function(){$(this).multiselect({selectedList:3})})),void 0!==e.expDates&&""!==e.expDates&&C.setUp(e.expDates,b),void 0!==e.warning&&""!==e.warning&&flagAlertMessage(e.warning,"warning",V),void 0!==e.resv&&(e.resv.rdiv.rooms&&e.resv.rdiv.rooms,c.setUp(e),$("#"+m.divFamDetailId).on("change",".hhk-cbStay",function(){var e=m.findStaysChecked();c.$totalGuests.text(e),0<e?c.$totalGuests.parent().removeClass("ui-state-highlight"):c.$totalGuests.parent().addClass("ui-state-highlight")}),$("#"+m.divFamDetailId).on("click",".hhk-getVDialog",function(){var e=$(this).data("vid"),t=$(this).data("span");viewVisit(0,e,{"Show Statement":function(){window.open("ShowStatement.php?vid="+e,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+e,"_blank")},Save:function(){saveFees(0,e,t,!1,payFailPage)},Cancel:function(){$(this).dialog("close")}},"Edit Visit #"+e+"-"+t,"",t),$("#submitButtons").hide()}),$(".hhk-cbStay").change(),void 0!==e.resv.rdiv.hideCkinBtn&&e.resv.rdiv.hideCkinBtn?$("#btnDone").hide():$("#btnDone").val(a).show(),0<e.rid&&($("#btnDelete").val("Delete "+i).show(),$("#btnShowReg").show(),$("#spnStatus").text(""===e.resv.rdiv.rStatTitle?"":" - "+e.resv.rdiv.rStatTitle)),v($("#gstDate").val(),e.rid,e.resv.rdiv.hideCiNowBtn)),void 0!==e.addPerson&&($("input#txtPersonSearch").val(""),E.addItem(e.addPerson.mem)&&(F.addItem(e.addPerson.addrs),m.newGuestMarkup(e.addPerson,e.addPerson.mem.pref),m.findStaysChecked(),$(".hhk-cbStay").change(),$("#"+e.addPerson.mem.pref+"txtFirstName").focus())))}t.getReserve=B,t.verifyInput=function(){if(V.text("").hide(),!1===C.verify())return!1;if(!1===m.verify())return!1;if(!1===h.verify())return!1;if(!0===c.setupComplete&&!1===c.verify())return!1;return!0},t.loadResv=g,t.deleteReserve=function(e,a,i){var t="&cmd=delResv&rid="+e;$.post("ws_ckin.php",t,function(e){var t;try{t=$.parseJSON(e)}catch(e){flagAlertMessage(e.message,"error"),$(a).remove()}t.error&&(t.gotopage&&window.open(t.gotopage,"_self"),flagAlertMessage(t.error,"error"),$(a).remove()),t.warning&&(flagAlertMessage(t.warning,"warning"),i.hide()),t.result&&($(a).remove(),flagAlertMessage(t.result+' <a href="Reserve.php">Continue</a>',"success"))})},t.resvTitle=i,t.people=E,t.addrs=F,t.getIdPsg=function(){return s},t.getIdResv=function(){return n},t.getIdName=function(){return o},t.getIdVisit=function(){return d},t.getSpan=function(){return l},t.setRooms=p}
