
<div class="container">
<div class="config-wrap">

	<h2>Configure Instuments Here</h2>


	<input class="form-control form-control-lg" value="cifs://smb-host/folder/file*pattern" >

	<input class="form-control form-control-lg" value="ftp://0.0.0.0/folder/file*pattern" >

	<button class="btn btn-outline-primary">Activate Pump</button>

</div>
</div>


<script>
$(function() {

	$('.btn-machine-metric-pump').on('click', function() {

		// But a Button After Each EndPoint
		// Fire Event for Each button, Using URL in the fild next to it.



		var e1 = new CustomEvent('openthc_lab_machine_poll');
		e1.search_url = val;
		window.dispatchEvent(e1);

	});

});
</script>
