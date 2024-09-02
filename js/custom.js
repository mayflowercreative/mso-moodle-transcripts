$(document).ready(function () {
	$("#cr-searchuser").on("keyup", function() {
		var value = $(this).val().toLowerCase();
		$("#cr-resultuser tr").filter(function() {
		  $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
		});
	  });
});