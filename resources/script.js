$(document).ready(function() {

	$('table').not(".nodatatable").dataTable({
		"oLanguage": {
			"sSearch": "<i class=\"fa fa-search\"></i> <span style=\"font-weight: 700; margin-right: 4px;\">Filter </span>"
		}
	});

	$(".staff_delete").click(function() {
		$(".staff_delete_button").attr("href", "index.php?p=admin&staff&id=" + $(this).data('id') + "&delete");
	});
	
	$(".tag_delete").click(function() {
		$(".tag_delete_button").attr("href", "index.php?p=admin&tags&name=" + $(this).data('name') + "&delete");
	});

	$(".building_delete").click(function() {
		$(".building_delete_button").attr("href", "index.php?p=admin&buildings&name=" + $(this).data('name') + "&delete");
	});

	$('#side-menu').metisMenu();

	$(".linkrow").on('mousedown', function(e) {
		if(e.which <= 2)
		{
			if(e.which == 2)
			{
				var win = window.open($(this).attr("href"), '_blank');
				win.focus();
				e.preventDefault();
			}
			else if(e.which == 1)
			{
				window.document.location = $(this).attr("href");
				e.preventDefault();
			}
		}
	});

	var last = ""

	function OnClientIDChanged()
	{
		var current = $("#clientid").val();
		if(current != last)
		{
			last = current;
			$.getJSON("index.php?p=typeahead&un=" + encodeURIComponent(current), function(data) {
				$("#name").val("name" in data ? data["name"] : "");
				$("#community").val("community" in data ? data["community"] : "");
				$("#building").val("building" in data ? data["building"] : "");
				$("#room").val("location" in data ? data["location"] : "");
			});
		}
	};

	$("#clientid").bind("keyup", OnClientIDChanged);
	$('#clientid').bind('typeahead:selected', OnClientIDChanged);

	$(window).bind("load resize", function() {
		width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
		if (width < 768) {
			$('div.sidebar-collapse').addClass('collapse')
		} else {
			$('div.sidebar-collapse').removeClass('collapse')
		}
	});

	var clientids = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'index.php?p=typeahead&q=%QUERY',
			filter: function (data) {
				return $.map(data, function (id) {
					return { value: id };
				});
			}
		}
	});

	clientids.initialize();

	$('#clientid').typeahead({
		hint: true,
		highlight: true,
		minLength: 1,
		limit: 10
	},
	{
		displayKey: 'value',
		source: clientids.ttAdapter()
	});
	
	var staffusernames = new Bloodhound({
		datumTokenizer: function (d) {
			return Bloodhound.tokenizers.whitespace(d.value);
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'index.php?p=typeahead&sq=%QUERY',
			filter: function (data) {
				return $.map(data, function (id) {
					return { value: id };
				});
			}
		}
	});

	staffusernames.initialize();

	$('#staffusername').typeahead({
		hint: true,
		highlight: true,
		minLength: 1,
		limit: 10
	},
	{
		displayKey: 'value',
		source: staffusernames.ttAdapter()
	});

	$('#tags').selectpicker();

	$('#building').selectpicker();
});
