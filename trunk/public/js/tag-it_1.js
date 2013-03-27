(function($) {

	$.fn.tagit = function(options) {

		var el = this;

		var BACKSPACE		= 8;
		var ENTER			= 13;
		var COMMA			= 44;

		// add the tagit CSS class.
		el.addClass("tagit");

		// create the input field.
		var html_input_field = "<li class=\"tagit-new\"><input class=\"tagit-input\" type=\"text\" /></li>\n";
		el.html (html_input_field);
                
		tag_input		= el.children(".tagit-new").children(".tagit-input");
                if (options.existTags){
                    $.each(options.existTags,function (index, value) {
                        create_choice (value);	
                    });
                    
		}
		$(this).click(function(e){
			if (e.target.tagName == 'A') {
				// Removes a tag when the little 'x' is clicked.
				// Event is binded to the UL, otherwise a new tag (LI > A) wouldn't have this event attached to it.
				$(e.target).parent().remove();
                                if (options.selectorName=="dealGeo"){
                                    var tagitchoice =  $(el).find(".tagit-choice");
                                    if (tagitchoice.length==0){
                                        $("#delete-dealGeo").hide();
                                    }
                                }
			}
			else {
				// Sets the focus() to the input field, if the user clicks anywhere inside the UL.
				// This is needed because the input field needs to be of a small size.
                              el.children(".tagit-new").children(".tagit-input").focus();
			}
		});

		tag_input.keypress(function(event){
                        tag_input = el.children(".tagit-new").children(".tagit-input");
			if (event.which == BACKSPACE) {
				if (tag_input.val() == "") {
					// When backspace is pressed, the last tag is deleted.
                                        $(el).children(".tagit-choice:last").remove();
                                        if (options.selectorName=="dealGeo"){
                                           var tagitchoice =  $(el).find(".tagit-choice");
                                           if (tagitchoice.length==0){
                                                $("#delete-dealGeo").hide();
                                           }
                                        }
				}
			}
			// Comma/Space/Enter are all valid delimiters for new tags.
			else if (event.which == COMMA || event.which == ENTER) {
				event.preventDefault();

                                var typed = tag_input.val();
                                typed = typed.replace(/,+$/,"");
                                typed = typed.trim();

                                if (typed != "") {
                                    if (is_new (typed)) {
                                        
                                                create_choice(typed);
                                    }else {
                                        // Cleaning the input.
                                        tag_input.val("");
                                        $("#notifier-box-"+options.selectorName).show();
                                        if(options.selectorName == "dealGeo")
                                            $("#message-"+options.selectorName).append(" The city "+typed+" already exists </br>");
                                        else  $("#message-"+options.selectorName).append(" The tags "+typed+" already exists </br>");
                                    }
                                }
			}
		});

		el.children(".tagit-new").children(".tagit-input").autocomplete({
			source: options.availableTags,
			select: function(event,ui){
                                tag_input = el.children(".tagit-new").children(".tagit-input");
                                
				if (is_new (ui.item.value)) {
                                        
                                                create_choice(ui.item.value);
				}else {
                                    // Cleaning the input.
                                    tag_input.val("");
                                     $("#notifier-box-"+options.selectorName).show();
                                     if(options.selectorName == "dealGeo")
                                        $("#message-"+options.selectorName).append(" The city "+ui.item.value+" already exists </br>");
                                     else  $("#message-"+options.selectorName).append(" The tags "+ui.item.value+" already exists </br>");
                                    
                                }
				// Preventing the tag input to be update with the chosen value.
				return false;
			}
		});

		function is_new (value){
			var is_new = true;
			this.tag_input.parents("ul").children(".tagit-choice").each(function(i){
				n = $(this).children("input").val();
				if (value == n) {
					is_new = false;
				}
			})
			return is_new;
		}
		function create_choice (value){
			var el = "";
			el  = "<li class=\"tagit-choice\">\n";
			el += value + "\n";
			el += "<a class=\"close\">x</a>\n";
			el += "<input type=\"hidden\" style=\"display:none;\" value=\""+value+"\" name=\"item["+options.selectorName+"][]\">\n";
			el += "</li>\n";
			var li_search_tags = this.tag_input.parent();
			$(el).insertBefore (li_search_tags);
			this.tag_input.val("");
                        if (options.selectorName=="dealGeo" && $("#delete-dealGeo").is(':hidden')){
                           $("#delete-dealGeo").show();
                        }
                        
                         $(".tagit-choice input").live("click", function(){
                            $(this).parent().click(); 
                        });
		}
           
	};

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	};

})(jQuery);
