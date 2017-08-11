var nonce=false;
var httptestrunning=false;
var httptesturl=false;
var mailtestemail=false;
var mailtestrunning=new Array(false,false,false);
var dnstestrunning=false;
var runTime=0;
setInterval('updateRunTime()', 1000);

function updateRunTime() {
	runTime+=1;
	jQuery('.runningtime').html(runTime);
}


function emailCheck(testType, targetDiv) {

	jQuery(targetDiv).html('<span class="mws-email-running">Running <span class="runningtime">0</span> seconds</span>');
	mailtestrunning[testType]=true;
	nonce = jQuery('#dt-nonce').val();

	jQuery.post('admin-ajax.php', {'action' : 'diagnostic_tool_mail_check', 'email' : mailtestemail, 'dt-nonce' : nonce, 'type' : testType }, function(response) {
		if (typeof response != 'object' || typeof response.result != 'boolean') {
			jQuery(targetDiv).html('<span class="mws-email-failure">Nonsense returned from server. Response not JSON.</span>');
		} else if (response.result == true) {
			jQuery(targetDiv).html('<span class="mws-email-success">'+response.message+'</span>');
		} else {
			jQuery(targetDiv).html('<span class="mws-email-failure">Failed ('+response.message+')</span>');
		}
		mailtestrunning[testType]=false;
	}).fail(function() { jQuery(targetDiv).html('<span class="mws-email-failure">Nonsense returned from server. Debug at network level needed.</span>'); mailtestrunning[testType]=false; });
}

jQuery(document).ready(function(){

	jQuery('#mwsmailtestsubmit').bind('click', function(e){
		e.preventDefault();
		e.stopPropagation();

//		if (!mailtestrunning[1] && !mailtestrunning[2]) {
			runTime=0;
			mailtestemail = jQuery('#mwsmailtestemail').val();
			emailCheck(1, '#mwsmailtestresult');
			emailCheck(2, '#mwswpmailtestresult');
//		} else {
//			mailtestrunning[1]=false;
//			mailtestrunning[2]=false;
//		}

	});

	jQuery('#mwshttptestsubmit').click(function(e){
		e.preventDefault();
		e.stopPropagation();

		if (!httptestrunning) {
			runTime=0;
			httptestrunning=true;
			httptesturl = jQuery('#mwshttptesturl').val();
			nonce = jQuery('#dt-nonce').val();

			var targetDiv = '#mwshttptesturlresult';
			jQuery(targetDiv).html('<span class="mws-email-running">Running <span class="runningtime">0</span> seconds</span>');
			jQuery.post('admin-ajax.php', {'action' : 'diagnostic_tool_http_check', 'url' : httptesturl, 'dt-nonce' : nonce }, function(response) {
				if (typeof response != 'object' || typeof response.result != 'boolean') {
					jQuery(targetDiv).html('<span class="mws-email-failure">Nonsense returned from server. Response not JSON.</span>');
				} else if (response.result == true) {
					jQuery(targetDiv).html('<span class="mws-email-success">'+response.message+'</span>');
				} else {
					jQuery(targetDiv).html('<span class="mws-email-failure">'+response.message+'</span>');
				}

				httptestrunning=false;
			}).fail(function() { jQuery(targetDiv).html('<span class="mws-email-failure">Nonsense returned from server. Debug at network level needed.</span>'); httptestrunning=false; });

		} else {
			jQuery('#mwshttptesturlresult').html('Stopped');
			httptestrunning=false;
		}

	});

	jQuery('#mwsdnstestsubmit').click(function(e){
		e.preventDefault();
		e.stopPropagation();

		if (!dnstestrunning) {
			runTime=0;
			dnstestrunning=true;
			dnstesturl = jQuery('#mwsdnstesturl').val();
			nonce = jQuery('#dt-nonce').val();

			var targetDiv = '#mwsdnstestresult';
			jQuery(targetDiv).html('<span class="mws-email-running">Running <span class="runningtime">0</span> seconds</span>');
			jQuery.post('admin-ajax.php', {'action' : 'diagnostic_tool_dns_check', 'url' : dnstesturl, 'dt-nonce' : nonce }, function(response) {
				if (typeof response != 'object' || typeof response.result != 'boolean') {
					jQuery(targetDiv).html('<span class="mws-email-failure">Nonsense returned from server. Response not JSON.</span>');
				} else if (response.result == true) {
					jQuery(targetDiv).html('<span class="mws-email-success">'+response.message+'</span>');
				} else {
					jQuery(targetDiv).html('<span class="mws-email-failure">Failed ('+response.message+')</span>');
				}

				dnstestrunning=false;
			}).fail(function() { jQuery(targetDiv).html('<span class="mws-email-failure">Nonsense returned from server. Debug at network level needed.</span>'); dnstestrunning=false; });

		} else {
			jQuery('#mwsdnstesturlresult').html('Stopped');
			dnstestrunning=false;
		}

	});

});
