function createZipAutoComplete(a,b,c){"use strict";a.autocomplete({source:function(a,d){c=$.getJSON(b,{zip:a.term,cmd:"schzip"}).done(function(a,b,e){e===c?(a&&a.error&&(a.gotopage&&window.open(a.gotopage),a.value=a.error),d(a)):d()}).fail(function(a,b,c){var d=b+", "+c;alert("Postal code request failed: "+d)})},position:{my:"left top",at:"left bottom",collision:"flip"},delay:100,minLength:3,select:function(a,b){if(b.item){var c=$(this).data("hhkindex"),d=$(this).data("hhkprefix");$("#"+d+"adrcity"+c).val(b.item.City),$("#"+d+"adrcountry"+c).val("US"),$("#"+d+"adrcountry"+c).change(),$("#"+d+"adrstate"+c).val(b.item.State),$("#"+d+"adrcounty"+c).length>0&&$("#"+d+"adrcounty"+c).val(b.item.County)}}})}function createAutoComplete(a,b,c,d,e,f,g){"use strict";var h={};void 0!==e&&null!==e||(e=!0),void 0!==f&&null!==f||(f="../house/roleSearch.php"),a.autocomplete({source:function(d,i){var j=d.term.substr(0,b);if(j in h){a.autocomplete("option","delay",0);var k,m,n,l=d.term.replace(",","").split(" ");return k=l.length>1?"\\b("+$.ui.autocomplete.escapeRegex(l[0])+").+\\b("+$.ui.autocomplete.escapeRegex(l[1])+")|\\b("+$.ui.autocomplete.escapeRegex(l[1])+").+\\b("+$.ui.autocomplete.escapeRegex(l[0])+")":"\\b("+$.ui.autocomplete.escapeRegex(d.term)+")",m=new RegExp(k,"i"),n=$.grep(h[j],function(a){return m.test(a.value)}),0===n.length&&(n.push({id:0,value:"No one found"}),h={}),e&&n.push({id:0,value:"New Person"}),void i(n)}a.autocomplete("option","delay",120),c.letters=d.term,g.length>0&&(c.basis=g.val()),$.getJSON(f,c,function(a,b,c){a.gotopage&&(i(),window.open(a.gotopage)),h[j]=a,i(a)})},position:{my:"left top",at:"left bottom",collision:"flip"},minLength:b,select:function(a,b){b.item&&d(b.item)}})}function verifyAddrs(a){"use strict";$(a).on("change","input.hhk-emailInput",function(){var a=/^[A-Z0-9._%+\-]+@(?:[A-Z0-9]+\.)+[A-Z]{2,4}$/i;""!==$.trim($(this).val())&&a.test($(this).val())===!1?$(this).addClass("ui-state-error"):$(this).removeClass("ui-state-error")}),$(a).on("change","input.hhk-phoneInput",function(){var c,a=/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/,b=/^(?:(?:[\+]?([\d]{1,3}(?:[ ]+|[\-.])))?[(]?([2-9][\d]{2})[\-\/)]?(?:[ ]+)?)?([2-9][0-9]{2})[\-.\/)]?(?:[ ]+)?([\d]{4})(?:(?:[ ]+|[xX]|(i:ext[\.]?)){1,2}([\d]{1,5}))?$/;if(""!=$.trim($(this).val())&&a.test($(this).val())===!1)$(this).addClass("ui-state-error");else if($(this).removeClass("ui-state-error"),b.lastIndex=0,c=b.exec($(this).val()),null!=c&&c.length>3){var d="";null!=c[1]&&""!=c[1]&&(d="+"+c[1]),$(this).val(d+"("+c[2]+") "+c[3]+"-"+c[4]),null!=c[6]&&""!=c[6]&&$(this).next("input").val(c[6])}}),$(a).on("change","input.ckzip",function(){var a=/^(?:[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}|[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]|[0-9]{5}(?:\-[0-9]{4})?)$/i;""===$(this).val()||a.test($(this).val())?$(this).removeClass("ui-state-error"):$(this).addClass("ui-state-error")})}
