/**
 * Created with JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 04.01.13
 * Time: 13:57
 * To change this template use File | Settings | File Templates.
 */
function passwordStrength(password){
	var desc = new Array();
	desc[0] = "Very Weak";
	desc[1] = "Weak";
	desc[2] = "Better";
	desc[3] = "Medium";
	desc[4] = "Strong";
	desc[5] = "Strongest";

	var score   = 0;

	//if password bigger than 6 give 1 point
	if (password.length > 6) score++;

	//if password has both lower and uppercase characters give 1 point	
	if ( ( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ) ) score++;

	//if password has at least one number give 1 point
	if (password.match(/\d+/)) score++;

	//if password has at least one special caracther give 1 point
	if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) )	score++;

	//if password bigger than 12 give another 1 point
	if (password.length > 12) score++;

	 document.getElementById("passwordDescription").innerHTML = desc[score];
	 document.getElementById("passwordStrength").className = "strength" + score;
}

function hide_errors(){
    $(".error_block_wrap").each(function(i,val){
        var input = $(this).prev();
        input.focus(function(){
            $(val).hide();
        }).blur(function(){
            if($(this).val() == '') $(val).show();
        });
    });
}