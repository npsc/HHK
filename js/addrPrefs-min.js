function addrPrefs(e){"use strict";$("input.prefPhone").each(function(){this.checked&&(e.phonePref=this.value)}),$("input.prefEmail").each(function(){this.checked&&(e.emailPref=this.value)}),$("input.addrPrefs").each(function(){this.checked&&(e.addrPref=this.value)}),$("input.addrPrefs").click(function(){var t,i,a,r=this.value;t=document.getElementById("adraddress1"+r),i=document.getElementById("adrcity"+r),(null!=t&&""==t.value||null!=i&&""==i.value)&&(alert("This address is blank.  It cannot be the 'preferred' address."),this.checked=!1,a=!1,""!=e.addrPref&&""!=$("#adraddress1"+e.addrPref).val()&&($("#rbPrefMail"+e.addrPref).prop("checked",!0),a=!0),a||$("input.addrPrefs").each(function(){""!=$("#adraddress1"+this.value).val()&&($(this).prop("checked",!0),e.addrPref=this.value)}))}),$("input.prefPhone").change(function(){var t,i=$("#txtPhone"+this.value);null!==i&&""==i.val()&&(alert("This Phone Number is blank.  It cannot be the 'preferred' phone number."),this.checked=!1,t=!1,""!=e.phonePref&&""!=$("#txtPhone"+e.phonePref).val()&&($("#ph"+e.phonePref).prop("checked",!0),t=!0),t||$("input.prefPhone").each(function(){if(""!=$("#txtPhone"+this.value).val())return $(this).prop("checked",!0),void(e.phonePref=this.value)}))}),$("input.prefEmail").change(function(){var t,i=$("#txtEmail"+this.value);null!=i&&""==i.val()&&(alert("This Email Address is blank.  It cannot be the 'preferred' Email address."),t=!1,this.checked=!1,""!=e.emailPref&&""!=$("#txtEmail"+e.emailPref).val()&&($("#em"+e.emailPref).prop("checked",!0),t=!0),t||$("input.prefEmail").each(function(){if(""!=$("#txtEmail"+this.value).val())return $(this).prop("checked",!0),void(e.emailPref=this.value)}))})}function verifyAddrs(e){"use strict";var t;(t="string"==typeof e?$(e):e).on("change","input.hhk-emailInput",function(){""!==$.trim($(this).val())&&!1===/^[A-Z0-9._%+\-]+@(?:[A-Z0-9]+\.)+[A-Z]{2,20}$/i.test($(this).val())?$(this).addClass("ui-state-error"):$(this).removeClass("ui-state-error")}),t.on("change","input.hhk-phoneInput",function(){var e,t=/^(?:(?:[\+]?([\d]{1,3}(?:[ ]+|[\-.])))?[(]?([2-9][\d]{2})[\-\/)]?(?:[ ]+)?)?([2-9][0-9]{2})[\-.\/)]?(?:[ ]+)?([\d]{4})(?:(?:[ ]+|[xX]|(i:ext[\.]?)){1,2}([\d]{1,5}))?$/;if(""!=$.trim($(this).val())&&!1===/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/.test($(this).val()))$(this).addClass("ui-state-error");else if($(this).removeClass("ui-state-error"),t.lastIndex=0,null!=(e=t.exec($(this).val()))&&e.length>3){var i="";null!=e[1]&&""!=e[1]&&(i="+"+e[1]),$(this).val(i+"("+e[2]+") "+e[3]+"-"+e[4]),null!=e[6]&&""!=e[6]&&$(this).next("input").val(e[6])}}),t.on("change","input.ckzip",function(){""===$(this).val()||/^(?:[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}|[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]|[0-9]{5}(?:\-[0-9]{4})?)$/i.test($(this).val())?$(this).removeClass("ui-state-error"):$(this).addClass("ui-state-error")})}
