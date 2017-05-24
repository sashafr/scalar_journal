<?if (!defined('BASEPATH')) exit('No direct script access allowed')?>
<?$this->template->add_css('system/application/views/widgets/ckeditor/custom.css')?>
<?$this->template->add_css('system/application/views/widgets/edit/jquery-ui-custom/jquery-ui.min.css')?>
<?$this->template->add_css('system/application/views/widgets/farbtastic/farbtastic.css')?>
<?$this->template->add_css('system/application/views/widgets/edit/content_selector.css')?>
<?$this->template->add_js('system/application/views/widgets/ckeditor/ckeditor.js')?>
<?$this->template->add_js('system/application/views/widgets/edit/jquery-ui-custom/jquery-ui.min.js')?>
<?$this->template->add_js('system/application/views/widgets/edit/jquery.ui.touch-punch.min.js')?>
<?$this->template->add_js('system/application/views/widgets/edit/jquery.select_view.js')?>
<?$this->template->add_js('system/application/views/widgets/edit/jquery.add_metadata.js')?>
<?$this->template->add_js('system/application/views/widgets/edit/jquery.content_selector.js')?>
<?$this->template->add_js('system/application/views/widgets/edit/jquery.predefined.js')?>
<?$this->template->add_js('system/application/views/melons/cantaloupe/js/bootbox.min.js');?>
<?$this->template->add_js('system/application/views/widgets/farbtastic/farbtastic.js')?>
<?$this->template->add_js('system/application/views/widgets/spinner/spin.min.js')?>
<?
if ($this->config->item('reference_options')) {
	$this->template->add_js('var reference_options='.json_encode($this->config->item('reference_options')), 'embed');
}
$this->template->add_js('var views='.json_encode($this->config->item('views')), 'embed');
$this->template->add_js('var media_views='.json_encode( $this->config->item('media_views') ? $this->config->item('media_views') : array(key($views)=>reset($views)) ), 'embed');
if ($this->config->item('predefined_css')) {
	$this->template->add_js('var predefined_css='.json_encode($this->config->item('predefined_css')), 'embed');
}
$css = <<<END

article > *:not(span) {display:none !important;}
.ci-template-html {font-family:Georgia,Times,serif !important; padding-left:7.2rem; padding-right:7.2rem;}
.body_copy {max-width:100% !important;}
label {padding-right:8px;}
label, .sortable li, .ui-sortable-handle {cursor:pointer;}
.ui-sortable-helper {background-color:#dddddd; font-family:"Lato",Arial,sans-serif; font-size:16px;}
form > table {width:100%;}
table, .content_selector, .bootbox {font-family:"Lato",Arial,sans-serif !important;}
table .p > td {padding-top:12px;}
table .b > td:first-of-type {padding-top:10px;}
div.p {padding-top:8px;}
.content_selector .howto {font-size:16px !important;}
#edit_content td {padding-right:9px;} /* CKEditor extends to the right */
#edit_content .cke_combo_text {width:18px !important;} /* Header pulldown */
tr#select_view td, tr#relationships td, tr#styling td, tr#metadata td {vertical-align:top;}
tr#relationships td {vertical-align:top;}
tr#styling button:first-of-type, tr#metadata button:first-of-type {width:100%; font-size:16px; color:#026697}
tr#styling table, tr#metadata table {width:100%;}
tr#styling table td, tr#metadata table td {vertical-align:middle;}
tr#styling table td:first-of-type, tr#metadata table td:first-of-type  {width:120px;}
.bootbox h1, .bootbox h4 {margin-left:0px; padding-left:0px;}
.bootbox h1 {margin-bottom:12px; padding-bottom:0px; font-size:30px; line-height:100%;}
.thumb_preview {max-height:100px; max-width:100%;}
.tab-pane {min-height:225px;}
#editor-tabs {margin-bottom:1.2rem;}
p {margin-bottom: 1.2rem;}
#annotation_of li {margin-bottom:1.2rem;}
.predefined_wrapper {padding-top:10px;}
.predefined_wrapper select {max-width:350px;}
.predefined_wrapper .desc {padding-top:4px; color:#333333;}
END;
$this->template->add_css($css, 'embed');
$js = <<<'END'

$(document).ready(function() {
	// If the type is passed via GET
	checkTypeSelect();
	if (-1!=document.location.href.indexOf('new.edit') && -1!=document.location.href.indexOf('type=media')) {
		$("#type_text").removeAttr('checked');
		$("#type_media").attr("checked", "checked");
		checkTypeSelect();
	}
	// Relationships (path, comment, annotation, tag)
	$(document).on('click', '.tab-pane .remove a', function() {  // Delegated
		if (!confirm('Are you sure you wish to remove this relationship?')) return;
		$(this).closest('li').remove();
	});
	if ($('#path_of').find('li').length) {
		$('.path_of_msg').show();
		$('.path_of_continue_msg').show();
	}
	var path_of_continue_msg = $('.path_of_continue_msg');
	path_of_continue_msg.find('a:first').click(function() {
		$('<div></div>').content_selector({changeable:true,multiple:false,msg:'Choose a page to continue to',callback:function(node){
			var urn = node.content["http://scalar.usc.edu/2012/01/scalar-ns#urn"][0].value;
			var content_id = urn.substr(urn.lastIndexOf(':')+1);
			var title = node.version["http://purl.org/dc/terms/title"][0].value;
			path_of_continue_msg.find('input[name="scalar:continue_to_content_id"]').val(content_id);
			path_of_continue_msg.find('.title').html(title);
		}});
	});
	path_of_continue_msg.find('a:last').click(function() {
		path_of_continue_msg.find('input[name="scalar:continue_to_content_id"]').val('');
		path_of_continue_msg.find('.title').html('[no destination set]');
	});
	$('.path_of_msg').find('a').click(function() {
		$('<div></div>').content_selector({changeable:true,multiple:true,onthefly:true,msg:'Choose contents of the path',callback:function(nodes){
			console.log(nodes);
			for (var j = 0; j < nodes.length; j++) {
				var slug = nodes[j].slug;
				var title = nodes[j].version["http://purl.org/dc/terms/title"][0].value;
				$('#path_of').append('<li><input type="hidden" name="container_of" value="'+slug+'" />'+title+'&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span></li>');
				$('.path_of_msg:first').html('<b>This <span class="content_type">page</span> is a path</b> which contains:');
				$('.path_of_msg').show();
				$('.path_of_continue_msg').show();
			}
		}});
	});
	if ($('#reply_of').find('li').length) {
		$('.reply_of_msg').show();
	}
	$('.reply_of_msg').find('a').click(function() {
		$('<div></div>').content_selector({changeable:true,multiple:true,onthefly:true,msg:'Choose items to be commented on',callback:function(nodes){
			for (var j = 0; j < nodes.length; j++) {
				var slug = nodes[j].slug;
				var title = nodes[j].version["http://purl.org/dc/terms/title"][0].value;
				$('#reply_of').append('<li><input type="hidden" name="reply_of" value="'+slug+'" /><input type="hidden" name="reply_of_rel_created" value="">'+title+'&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span></li>');
				$('.reply_of_msg:first').html('<b>This <span class="content_type">page</span> is a comment</b> on:');
				$('.reply_of_msg').show();
			}
		}});
	});
	if ($('#annotation_of').find('li').length) {
		$('.annotation_of_msg').show();
	}
	$('.annotation_of_msg').find('a').click(function() {
		$('<div></div>').content_selector({type:'media',changeable:false,multiple:true,msg:'Choose items to be annotated',callback:function(nodes){
			for (var j = 0; j < nodes.length; j++) {
				var slug = nodes[j].slug;
				var title = nodes[j].version["http://purl.org/dc/terms/title"][0].value;
				var url = nodes[j].version["http://simile.mit.edu/2003/10/ontologies/artstor#url"][0].value;
				var annotation_type = scalarapi.parseMediaSource(url).contentType;
				var annotation = $('<li><input type="hidden" name="annotation_of" value="'+slug+'" />'+title+'&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span><br /></li>').appendTo('#annotation_of');
				switch (annotation_type) {
					case "audio":
					case "video":
						var str = '<div class="form-inline"><div class="form-group"><label>Start seconds&nbsp; <input class="form-control" type="text" style="width:75px;" name="annotation_of_start_seconds" value="" /></label>';
						str += ' <label>End seconds&nbsp; <input class="form-control" type="text" style="width:75px;" name="annotation_of_end_seconds" value="" /></label></div></div>';
						str += '<input type="hidden" name="annotation_of_start_line_num" value="" />';
						str += '<input type="hidden" name="annotation_of_end_line_num" value="" />';
						str += '<input type="hidden" name="annotation_of_points" value="" />';
						annotation.append('<div>'+str+'</div>');
						break;
					case "html":
					case "text":
					case "document":
						var str = '<div class="form-inline"><div class="form-group"><label>Start line&nbsp; <input class="form-control" type="text" style="width:75px;" name="annotation_of_start_line_num" value="" /></label></div>';
						str += ' <label>End line&nbsp; <input class="form-control" type="text" style="width:75px;" name="annotation_of_end_line_num" value="" /></label></div></div>';
						str += '<input type="hidden" name="annotation_of_start_seconds" value="" />';
						str += '<input type="hidden" name="annotation_of_end_seconds" value="" />';
						str += '<input type="hidden" name="annotation_of_points" value="" />';
						annotation.append('<div>'+str+'</div>');
						break;
					case "image":
						var str = '<div class="form-inline"><div class="form-group"><label>Left (x), Top (y), Width, Height&nbsp; <input style="form-control" type="text" style="width:125px;" name="annotation_of_points" value="0,0,0,0" /></label></div></div>';
						str += '<small>May be pixel or percentage values; for percentage add "%" after each value.</small>';
						str += '<input type="hidden" name="annotation_of_start_line_num" value="" />';
						str += '<input type="hidden" name="annotation_of_end_line_num" value="" />';
						str += '<input type="hidden" name="annotation_of_start_seconds" value="" />';
						str += '<input type="hidden" name="annotation_of_end_seconds" value="" />';
						annotation.append('<div>'+str+'</div>');
						break;
					default:
						alert('A selected media ('+title+') is of a type not presently supported for annotation.');
						return false;
				}
				$('.annotation_of_msg:first').html('<b>This <span class="content_type">page</span> is an annotation</b> of:');
				$('.annotation_of_msg').show();
			}
		}});
	});
	if ($('#tag_of').find('li').length) {
		$('.tag_of_msg').show();
	}
	$('.tag_of_msg').find('a').click(function() {
		$('<div></div>').content_selector({changeable:true,multiple:true,onthefly:true,msg:'Choose items to be tagged',callback:function(nodes){
			for (var j = 0; j < nodes.length; j++) {
				var title = nodes[j].version["http://purl.org/dc/terms/title"][0].value;
				var slug = nodes[j].slug;
				$('#tag_of').append('<li><input type="hidden" name="tag_of" value="'+slug+'" />'+title+'&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span></li>');
			}
			$('.tag_of_msg:first').html('<p><b>This <span class="content_type">page</span> is a tag</b> of:</p>');
			$('.tag_of_msg').show();
		}});
	});
	if ($('#has_tag').find('li').length) {
		$('.has_tag_msg').show();
	}
	$('.has_tag_msg').find('a').click(function() {
		$('<div></div>').content_selector({changeable:true,multiple:true,onthefly:true,msg:'Choose items that tag the current page',callback:function(nodes){
			for (var j = 0; j < nodes.length; j++) {
				var title = nodes[j].version["http://purl.org/dc/terms/title"][0].value;
				var slug = nodes[j].slug;
				$('#has_tag').append('<li><input type="hidden" name="has_tag" value="'+slug+'" />'+title+'&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span></li>');
			}
			$('.has_tag_msg:first').html('<br><p><b>This <span class="content_type">page</span> is tagged by</b> the following:</p>');
			$('.has_tag_msg').show();
		}});
	});
	// Check for <script> and <style> tags in custom code text boxes
	$('textarea[name="scalar:custom_style"],textarea[name="scalar:custom_scripts"]').on('paste keyup',function() {
    	var $this = $(this);
   		var type = 'script';
		if($this.prop('name') === 'scalar:custom_style') {
   			type = 'style';
		}
    	if($("#"+type+"-confirm").data('confirmed') !== true) {
			if($this.val().search(/<\/?(style|script)>/i) != -1) {
				$("#"+type+"-confirm").modal('show');
			}
		}
	});
	$('#script-confirm .submit, #style-confirm .submit').click(function() {
		$(this).parents('#style-confirm,#script-confirm').data('confirmed',true).modal('hide');
	})
	// Taxonomies for title typeahead
	var fcroot = document.getElementById("approot").href.replace('/system/application/','');
	var book_slug = document.getElementById("parent").href.substring(fcroot.length);
	book_slug = book_slug.replace(/\//g,'');
	$.getJSON(fcroot+"/system/api/get_onomy", {slug:book_slug}, function(data) {
		var suggestions = [];
		for (var index in data) {
			var taxonomy_name;
			for (var key in data[index]) {
				if('undefined'!=typeof(data[index][key]["http://purl.org/dc/terms/title"])) {
					taxonomy_name = data[index][key]["http://purl.org/dc/terms/title"][0].value;
					break;
				}
			}
			for(var key in data[index]) {
				if (key.match(/term\/[0-9]*$/g) != null) {
					if('undefined'!=typeof(data[index][key]["http://www.w3.org/2004/02/skos/core#prefLabel"])) {
						var term_label = data[index][key]["http://www.w3.org/2004/02/skos/core#prefLabel"][0].value;
						suggestions.push({label:term_label+" ("+taxonomy_name+")", value:term_label});
					}
				}
			}
		}
		suggestions.sort(function(a,b) {
			if (a.value < b.value) return -1;
			if (a.value > b.value) return 1;
			return 0;
		})
		$('#title').autocomplete({source:suggestions});
	});
	// Color Picker (in editor)
	if ($.isFunction($.fn.farbtastic)) {
		$('#colorpicker').farbtastic('#color_select');
	}
	// Thumbnail
	var choose_thumb = $('#choose_thumbnail');
	var thumbnail = $('input[name="scalar:thumbnail"]');
	var chosen_thumb = choose_thumb.find('option:selected').val();
	if (chosen_thumb.length) thumbnail.val(chosen_thumb);
	if (thumbnail.val().length) choose_thumb.parent().parent().append('<div class="well col-md-4"><img src="'+thumbnail.val()+'" class="thumb_preview" /></div>');
	choose_thumb.change(function() {
		thumbnail.val($(this).find('option:selected').val());
		$(this).parent().parent().find('.thumb_preview').parent().remove();
		$(this).parent().parent().append('<div class="well"><img src="'+thumbnail.val()+'" class="thumb_preview" /></div>');
	});
	thumbnail.change(function() {
		$(this).parent().parent().find('.thumb_preview').parent().remove();
		$(this).parent().parent().append('<div class="well"><img src="'+thumbnail.val()+'" class="thumb_preview" /></div>');
	});
	// Background
	var choose_background = $('#choose_background');
	var chosen_background = choose_background.find('option:selected').val();
	choose_background.change(function() {
		$(this).parent().parent().find('.thumb_preview').parent().remove();
		chosen_background = choose_background.find('option:selected').val();
		$(this).parent().parent().append('<div class="well"><img src="'+chosen_background+'" class="thumb_preview" /></div>');
	});
	// Banner
	var choose_banner = $('#choose_banner');
	var chosen_banner = choose_banner.find('option:selected').val();
	choose_banner.change(function() {
		$(this).parent().parent().find('.thumb_preview').parent().remove();
		chosen_banner = choose_banner.find('option:selected').val();
		$(this).parent().parent().append('<div class="well"><img src="'+chosen_banner+'" class="thumb_preview" /></div>');
	});
	// Predefined CSS
	if ('undefined'!=window['predefined_css'] && !$.isEmptyObject(window['predefined_css'])) {
    	$('textarea[name="scalar:custom_style"]').predefined({msg:'Insert predefined CSS:',data:window['predefined_css']});
	}
	// Protect from clicking away from edit page
	$('a').not('form a').click(function() {
		if (!confirm('Are you sure you wish to leave this page? Any unsaved changes will be lost.')) return false;
	});
	// Additional metadata
	$('.add_additional_metadata:first').click(function() {
		var ontologies_url = $('link#approot').attr('href').replace('/system/application/','')+'/system/ontologies';
		$('#metadata_rows').add_metadata({title:'Add additional metadata',ontologies_url:ontologies_url});
	});
	$('.populate_exif_fields:first').click(function() {
		if (!confirm('This feature will find any IPTC metadata fields embedded in the file, and add the field/values as additional metadata. IPTC metadata is typically embedded in JPEG and TIFF files by external applications. Do you wish to continue?')) return;
		var url = $('input[name="scalar:url"]').val();
		if (!url.length) {
			alert('Media File URL is empty');
			return;
		}
		if (-1==url.indexOf('://')) {
			url = $('link#parent').attr('href')+url;
		}
		var image_metadata_url = $('link#approot').attr('href').replace('/system/application/','')+'/system/image_metadata';
		$('#metadata_rows').find_and_add_exif_metadata({parser_url:image_metadata_url,url:url,button:this});
	});
	// Sortable path items (extra care needed to work within Boostrap Tabs)
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		if ('#path-pane'==$(e.relatedTarget).attr('href')) {
		  $('#path-pane').find(".sortable").sortable("disable");
		}
		var etarget = $(e.target);
		if (etarget.data('sortable_init')) {
		  $('#path-pane').find(".sortable").sortable("enable");
	    } else if ('#path-pane'==etarget.attr('href')) {
	      etarget.data('sortable_init',true);
		  $('#path-pane').find(".sortable").sortable({
		  	scroll:false,
		  	helper:'clone',
			appendTo:'body'
		  });
	    }
	});
	// Badges
	badges();
	$('a[role="tab"] .badge').closest('a').click(function() { badges(); });

	//Added to prevent accidental navigation away from edit/add page - matches all anchor tags
	//with an href attribute that doesn't start with # or javascript:
	$(document).on('click', 'a[href]:not([href=""], [href^="#"], [href^="javascript"])', function(e){
		if(!window.confirm('You are now leaving the page. If you have made any changes to this page, they will be lost. Continue?')){
			e.preventDefault();
			return false;
		}
	});

});
// Determine if the page is a composite or media and show/hide certain elements accordingly
function checkTypeSelect() {
	var selected =  $("input:radio[name='rdf:type']:checked").val();
	if (selected.indexOf('Composite')!=-1) {  // Page
		$('.type_media').hide();
		$('.type_composite').show();
		$('.content_type').html('page');
		$('#select_view').select_view({data:views,default_value:$('link#default_view').attr('href')});
	} else {  // Media
		$('.type_media').show();
		$('.type_composite').hide();
		$('.content_type').html('media file');
		$('#select_view').select_view({data:media_views,default_value:$('link#default_view').attr('href')});
	}
}
// Set Badges for Relationship tab
function badges() {
	var total = 0;
	$('.badge').each(function() {
		var self = $(this);
		var j = 0;
		switch(self.parent().attr('href')) {
			case '#path-pane':
				j = $('input[name="container_of"]').length + $('input[name="has_container"]').length;
				break;
			case '#comment-pane':
				j = $('input[name="reply_of"]').length + $('input[name="has_reply"]').length;
				break;
			case '#annotation-pane':
				j = $('input[name="annotation_of"]').length + $('input[name="has_annotation"]').length;
				break;
			case '#tag-pane':
				j = $('input[name="tag_of"]').length + $('input[name="has_tag"]').length;
				break;
		};
		self.html(((j>0)?j:''));
		total = total + j;
	});
	$('a[role="tab"] .badge').html(((total>0)?total:''));
}
// Validate form data before sending to Scalar's save API
function validate_form(form, no_action) {
	no_action = ('undefined'==typeof(no_action) || !no_action) ? false : true;
	var commit = function() {
		// Commit editor content
		var textarea = CKEDITOR.instances['sioc:content'].getData();
		form.find('textarea[name="sioc:content"]').val(textarea);
		// Make sure title is present
		var title = $('#title').val();
		if (title.length==0) {
			alert('Title is a required field.  Please enter a title at the top of the form.');
			return false;
		}
		// Make sure slug is present if the page has already been created (otherwise the API will create)
		var action = $('input[name="action"]').val().toLowerCase();
		if ('add'!=action) {
			var slug = $('#slug').val();
			if (slug.length==0) {
				alert('Page URL is a required field.  Please enter a URL segment in the Metadata tab at the bottom of the page.');
				return false;
			}
		}
		if (no_action) {
			send_form_no_action(form);
		} else {
			send_form(form);
		}
	}

	// If in source mode, switch to WYSIWYG to invoke formatting
	if ('source'==CKEDITOR.instances['sioc:content'].mode) {
		CKEDITOR.instances['sioc:content'].setMode('wysiwyg', function() {
			commit();
		});
	} else {
		commit();
	}
}

END;
$this->template->add_js($js, 'embed');
$page = (isset($page->version_index)) ? $page : null;
$version = (isset($page->version_index)) ? $page->versions[$page->version_index] : null;
?>
<div id="script-confirm" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h2 class="modal-title">Extra HTML Tags</h2>
      </div>
      <div class="modal-body">
        <p>You have HTML tags included in the Custom JS box. Adding HTML to this box will cause Javascript errors which may cause problems with your Scalar book. Note that &lt;script&gt; and &lt;/script&gt; tags are automatically included by Scalar.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="submit btn btn-primary">Continue</button>
      </div>
    </div>
  </div>
</div>
<div id="style-confirm" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h2 class="modal-title">Extra HTML Tags</h2>
      </div>
      <div class="modal-body">
        <p>You have HTML tags included in the Custom CSS box. Adding HTML to this box will cause style errors which may cause problems with your Scalar book. Note that &lt;style&gt; and &lt;/style&gt; tags are automatically included by Scalar.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="submit btn btn-primary">Continue</button>
      </div>
    </div>
  </div>
</div>
<form id="edit_form" class="caption_font" method="post" enctype="multipart/form-data" onsubmit="validate_form($(this));return false;">
<input type="hidden" name="action" value="<?=(isset($page->version_index))?'update':'add'?>" />
<input type="hidden" name="native" value="1" />
<input type="hidden" name="scalar:urn" value="<?=(isset($page->version_index)) ? $page->versions[$page->version_index]->urn : ''?>" />
<input type="hidden" name="id" value="<?=@$login->email?>" />
<input type="hidden" name="api_key" value="" />
<?if (!isset($page->version_index)): ?>
<input type="hidden" name="scalar:child_urn" value="<?=$book->urn?>" />
<input type="hidden" name="scalar:child_type" value="http://scalar.usc.edu/2012/01/scalar-ns#Book" />
<input type="hidden" name="scalar:child_rel" value="page" />
<? endif ?>
<input type="hidden" name="urn:scalar:book" value="<?=$book->urn?>" />

<div class="form-horizontal">
	<div class="form-group">
		<label for="title" class="col-sm-2">Title</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="title" name="dcterms:title" value="<?=@htmlspecialchars($version->title)?>" />
		</div>
	</div>
	<div class="form-group">
		<label for="page_description" class="col-sm-2">Description</label>
		<div class="col-sm-10">
			<input id="page_description" type="text" class="form-control" name="dcterms:description" value="<?=@htmlspecialchars($version->description)?>" />
		</div>
	</div>
</div>

<div class="type_media form-horizontal">
	<div class="form-group">
		<label for="media_file_url" class="col-sm-2">Media file URL</label>
		<div class="col-sm-10">
			<input id="media_file_url" class="form-control" name="scalar:url" value="<?=(!empty($file_url))?$file_url:'http://'?>" style="width:100%;" onfocus="if (this.value=='http://') this.value='';" />
		</div>
	</div>
<? if (isset($page) && $this->versions->url_is_local($page->versions[0]->url)): ?>
	<div class="form-group">
		<p class="type_media col-sm-10 col-md-offset-2">File can be replaced with another upload at <a href="<?=confirm_slash(base_url()).$book->slug?>/upload#replace=<?=$version->version_id?>">Import > Local Media Files</a></p>
	</div>
<? endif ?>
</div>

<table>
<tr id="edit_content" class="p type_composite">
	<td colspan="2">
		<textarea class="ckeditor" wrap="soft" name="sioc:content" style="visibility:hidden;"><?
		if (isset($page->version_index)):
			$content = $page->versions[$page->version_index]->content;
			if (!empty($content)) {
				echo cleanbr(htmlspecialchars($content));
			}
		endif;
		?></textarea>
	</td>
</tr>
</table>

<div id="editor-tabpanel" role="tabpanel" class="p">
	<ul id="editor-tabs" class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active"><a href="#layout-pane" aria-controls="layout-pane" role="tab" data-toggle="tab">Layout</a></li>
		<li role="presentation" class="dropdown"><a class="dropdown-toggle" href="#" role="tab" data-toggle="dropdown">Relationships <span class="badge"></span><span class="caret"></span></a>
			<ul class="dropdown-menu" role="menu">
				<li role="presentation"><a role="menuitem" tabindex="-1" href="#path-pane" aria-controls="path-pane" data-toggle="tab"><span class="path_icon"></span> Path <span class="badge"></span></a></li>
				<li role="presentation"><a role="menuitem" tabindex="-1" href="#comment-pane" aria-controls="comment-pane" data-toggle="tab"><span class="reply_icon"></span> Comment <span class="badge"></span></a></li>
				<li role="presentation"><a role="menuitem" tabindex="-1" href="#annotation-pane" aria-controls="annotation-pane" data-toggle="tab"><span class="annotation_icon"></span> Annotation <span class="badge"></span></a></li>
				<li role="presentation"><a role="menuitem" tabindex="-1" href="#tag-pane" aria-controls="tag-pane" data-toggle="tab"><span class="tag_icon"></span> Tag <span class="badge"></span></a></li>
			</ul>
		</li>
		<li role="presentation" class="dropdown"><a class="dropdown-toggle" href="#" role="tab" data-toggle="dropdown">Styling <span class="caret"></span></a>
			<ul class="dropdown-menu" role="menu">
				<li role="presentation"><a role="menuitem" tabindex="-1" href="#thumbnail-pane" aria-controls="thumbnail-pane" data-toggle="tab">Thumbnail</a></li>
				<li role="presentation" class="type_composite"><a role="menuitem" tabindex="-1" href="#banner-image-pane" aria-controls="#banner-image-pane" data-toggle="tab">Key Image</a></li>
				<li role="presentation" class="type_composite"><a role="menuitem" tabindex="-1" href="#background-image-pane" aria-controls="background-image-pane" data-toggle="tab">Background Image</a></li>
				<li role="presentation" class="type_composite"><a role="menuitem" tabindex="-1" href="#custom-css-pane" aria-controls="custom-css-pane" data-toggle="tab">CSS</a></li>
				<li role="presentation" class="type_composite"><a role="menuitem" tabindex="-1" href="#custom-javascript-pane" aria-controls="custom-javascript-pane" data-toggle="tab">JavaScript</a></li>
				<li role="presentation" class="type_composite"><a role="menuitem" tabindex="-1" href="#background-audio-pane" aria-controls="background-audio-pane" data-toggle="tab">Audio</a></li>
			</ul>
		</li>
		<li role="presentation"><a href="#metadata-pane" aria-controls="metadata-pane" role="tab" data-toggle="tab">Metadata</a></li>
	</ul>
	<div class="tab-content">

		<div id="layout-pane" role="tabpanel" class="tab-pane active">
			<div class="row p">
				<div class="col-md-8"><p>Select the layout that will be used on this page:</p></div>
			</div>
			<div class="well col-md-8">
				<div id="select_view"></div>
				<div style="clear: both;"></div>
			</div>
		</div>

		<div id="path-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
					<?
					if (empty($page) || empty($page->versions[$page->version_index]->path_of)) {
						echo '<p><span class="path_of_msg"><b>To make this <span class="content_type">page</span> a path</b>, <a href="javascript:void(null);">choose the items that it contains.</a></span></p>'."\n";
					} else {
						echo '<p><span class="path_of_msg"><b>This <span class="content_type">page</span> is a path,</b> and it contains:</span></p>'."\n";
					}
					echo '      <ol id="path_of" class="edit_relationship_list sortable">'."\n";
					if (!empty($page)&&!empty($page->versions[$page->version_index]->path_of)) {
						foreach ($page->versions[$page->version_index]->path_of as $node) {
							$title = $node->versions[0]->title;
							$rel_uri = $base_uri.$node->slug;
							echo '      <li>';
							echo '<input type="hidden" name="container_of" value="'.$node->slug.'" />';
							echo $title;
							echo '&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span>';
							echo '</li>'."\n";
						}
					}
					echo "      </ol>\n";
					?>
			      	<div class="form_fields_sub_element path_of_msg" style="display:none;"><a class="btn btn-default" role="button">Add more content</a>&nbsp; or drag items to reorder</div>
			      	<div class="form_fields_sub_element form_fields_sub_element_border_top form_fields_sub_element_border_bottom path_of_continue_msg" style="display:none;margin-top:18px;"><p><b>When readers reach the end of this path,</b> direct them to: <input type="hidden" name="scalar:continue_to_content_id" value="<?=((!empty($continue_to))?$continue_to->content_id:'')?>" /><span class="title"><b><?=((!empty($continue_to))?$continue_to->versions[$continue_to->version_index]->title:'[no destination set]')?></b></span></p><a class="btn btn-default" href="javascript:;">Change destination</a> <a class="btn btn-default" href="javascript:">Clear</a></div>
					<? if (!empty($page)&&!empty($page->versions[$page->version_index]->has_paths)): ?>
						<br>
						<div id="has_path" class="p">
					    	<b>This <span class="content_type">page</span> is contained by</b> the following paths:<br />
				        	<ul class="edit_relationship_list">
					<?
							foreach ($page->versions[$page->version_index]->has_paths as $node) {
								$title = $node->versions[0]->title;
								$rel_uri = $base_uri.$node->slug;
								$rel_sort_number = 0;
								$count = 1;
								foreach ($node->versions[0]->path_of as $path_of) {
									if ($path_of->content_id == $page->content_id) {
										$rel_sort_number = $count;
										break;
									}
									$count++;
								}
								echo '<li>';
								echo '<input type="hidden" name="has_container" value="'.$node->slug.'" />';
								echo '<input type="hidden" name="has_container_sort_number" value="'.$rel_sort_number.'" />';
								echo $title.' (page '.$rel_sort_number.')';
								echo '&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span>';
								echo '</li>';
							}
					?>
						</div>
					<? endif ?>
				</div>
			</div>
		</div>

		<div id="comment-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
					<?
					if (empty($page) || empty($page->versions[$page->version_index]->reply_of)) {
						echo '<span class="reply_of_msg"><b>To make this <span class="content_type">page</span> a comment</b>, <a href="javascript:void(null);">choose the items that it comments on.</a></span><br />'."\n";
					} else {
						echo '<p><span class="reply_of_msg"><b>This <span class="content_type">page</span> is a comment</b> on:</span></p>'."\n";
					}
					echo '      <ul id="reply_of" class="edit_relationship_list">'."\n";
					if (!empty($page)&&!empty($page->versions[$page->version_index]->reply_of)) {
						foreach ($page->versions[$page->version_index]->reply_of as $node) {
							$title = $node->versions[0]->title;
							$rel_slug = $base_uri.$node->slug;
							echo '      <li>';
							echo '<input type="hidden" name="reply_of" value="'.$node->slug.'" />';
							echo '<input type="hidden" name="reply_of_paragraph_num" value="'.$node->versions[0]->paragraph_num.'" />';
							echo '<input type="hidden" name="reply_of_datetime" value="'.$node->versions[0]->datetime.'" />';
							echo $title.' ('.date("j F Y", strtotime($node->versions[0]->datetime)).')&nbsp; ';
							echo '<span class="remove">(<a href="javascript:;">remove</a>)</span>';
							echo '</li>'."\n";
						}
					}
					echo "      </ul>\n";
					?>
					      <div class="form_fields_sub_element reply_of_msg" style="display:none;"><a class="btn btn-default" role="button">Add more content</a></div>

					<? if (!empty($page)&&!empty($page->versions[$page->version_index]->has_replies)): ?>
					 	  <div id="has_reply" class="p">
					      	<b>This <span class="content_type">page</span> is commented on by</b>:<br />
					      	<ul class="edit_relationship_list">
					<?
						foreach ($page->versions[$page->version_index]->has_replies as $node) {
							$title = $node->versions[0]->title;
							$rel_slug = $base_uri.$node->slug;
							echo' <li resource="'.$node->slug.'">';
							echo '<input type="hidden" name="has_reply" value="'.$node->slug.'" />';
							echo '<input type="hidden" name="has_reply_paragraph_num" value="'.$node->versions[$node->version_index]->paragraph_num.'" />';
							echo '<input type="hidden" name="has_reply_datetime" value="'.$node->versions[$node->version_index]->datetime.'" />';
							echo $title.' ('.date("j F Y", strtotime($node->versions[$node->version_index]->datetime)).')';
							echo '&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span>';
							echo '</li>';
						}
					?>
					      	</ul>
					      </div>
					<? endif ?>
				</div>
			</div>
		</div>

		<div id="annotation-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
			    <?
					if (empty($page) || empty($page->versions[$page->version_index]->annotation_of)) {
						echo '<span class="annotation_of_msg"><b>To make this <span class="content_type">page</span> an annotation</b>, <a href="javascript:void(null);">choose the media that it annotates.</a></span><br />'."\n";
					} else {
						echo '<p><span class="annotation_of_msg"><b>This <span class="content_type">page</span> is an annotation</b> of:</span></p>'."\n";
					}
					echo '      <ul id="annotation_of" class="edit_relationship_list">'."\n";
					if (!empty($page)&&!empty($page->versions[$page->version_index]->annotation_of)) {
						foreach ($page->versions[$page->version_index]->annotation_of as $node) {
							$title = $node->versions[0]->title;
							$rel_slug = $base_uri.$node->slug;
							echo '      <li>';
							echo '<input type="hidden" name="annotation_of" value="'.$node->slug.'" />';
							echo $title.'&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span><br />';
							if (!empty($node->versions[0]->start_seconds) || !empty($node->versions[0]->end_seconds)) {
								echo '<div class="form-inline"><div class="form-group"><label>Start seconds&nbsp; <input class="form-control" onblur="check_start_end_values(this, $(this).nextAll(\'input:first\'))" type="text" style="width:75px;" name="annotation_of_start_seconds" value="'.$node->versions[0]->start_seconds.'" /></label>';
								echo '<label>End seconds&nbsp; <input class="form-control" onblur="check_start_end_values($(this).prevAll(\'input:first\'), this)" type="text" style="width:75px;" name="annotation_of_end_seconds" value="'.$node->versions[0]->end_seconds.'" /></label></div></div>';
								echo '<input type="hidden" name="annotation_of_start_line_num[]" value="'.@$node->versions[0]->start_line_num.'" />';
								echo '<input type="hidden" name="annotation_of_end_line_num[]" value="'.@$node->versions[0]->end_line_num.'" />';
								echo '<input type="hidden" name="annotation_of_points[]" value="'.@$node->versions[0]->points.'" />';
							} elseif (!empty($node->versions[0]->start_line_num) || !empty($node->versions[0]->end_line_num)) {
								echo '<div class="form-inline"><div class="form-group"><label>Start line&nbsp; <input class="form-control" onblur="check_start_end_values(this, $(this).nextAll(\'input:first\'))" type="text" style="width:75px;" name="annotation_of_start_line_num" value="'.$node->versions[0]->start_line_num.'" /></label>';
								echo '<label for="annotation_of_end_line_num">End line&nbsp; <input id="annotation_of_end_line_num" class="form-control" onblur="check_start_end_values($(this).prevAll(\'input:first\'), this)" type="text" style="width:75px;" name="annotation_of_end_line_num" value="'.$node->versions[0]->end_line_num.'" /></label></div></div>';
								echo '<input type="hidden" name="annotation_of_start_seconds" value="'.@$node->versions[0]->start_seconds.'" />';
								echo '<input type="hidden" name="annotation_of_end_seconds" value="'.@$node->versions[0]->end_seconds.'" />';
								echo '<input type="hidden" name="annotation_of_points" value="'.@$node->versions[0]->points.'" />';
							} elseif (!empty($node->versions[0]->points)) {
								echo '<div class="form-inline"><div class="form-group"><label>Left (x), Top (y), Width, Height&nbsp; <input type="text" class="form-control" style="width:125px;" name="annotation_of_points" value="'.$node->versions[0]->points.'" /></label></div></div>';
								echo '<input type="hidden" name="annotation_of_start_seconds" value="'.@$node->versions[0]->start_seconds.'" />';
								echo '<input type="hidden" name="annotation_of_end_seconds" value="'.@$node->versions[0]->end_seconds.'" />';
								echo '<input type="hidden" name="annotation_of_start_line_num" value="'.@$node->versions[0]->start_line_num.'" />';
								echo '<input type="hidden" name="annotation_of_end_line_num" value="'.@$node->versions[0]->end_line_num.'" />';
								echo '<small>May be pixel or percentage values; for percentage add "%" after each value.</small>';
							}
							echo '</li>'."\n";
						}
					}
					echo "      </ul>\n";
				?>
					<div class="form_fields_sub_element annotation_of_msg" style="display:none;"><a class="btn btn-default" role="button">Annotate additional media</a></div>

				<?
				   if (!empty($page)&&!empty($page->versions[$page->version_index]->has_annotations)): ?>

				      <div id="has_annotation" class="p">
				        <b>This <span class="content_type">page</span> is annotated by</b> the following annotations:<br />
				        <ul class="edit_relationship_list">
				<?
					foreach ($page->versions[$page->version_index]->has_annotations as $node) {
						$title = $node->versions[0]->title;
						$rel_slug = $base_uri.$node->slug;
						echo' <li resource="'.$node->slug.'">';
						echo '<input type="hidden" name="has_annotation" value="'.$node->slug.'" />';
						echo $title.'<br />';
						if (!empty($node->versions[0]->start_seconds) || !empty($node->versions[0]->end_seconds)) {
							echo 'Start seconds: <input type="text" style="width:75px;" name="has_annotation_start_seconds" value="'.$node->versions[0]->start_seconds.'" />';
							echo '&nbsp; End seconds<input type="text" style="width:75px;" name="has_annotation_end_seconds" value="'.$node->versions[0]->end_seconds.'" />';
							echo '<input type="hidden" name="has_annotation_start_line_num" value="'.@$node->versions[0]->start_line_num.'" />';
							echo '<input type="hidden" name="has_annotation_end_line_num" value="'.@$node->versions[0]->end_line_num.'" />';
							echo '<input type="hidden" name="has_annotation_points" value="'.@$node->versions[0]->points.'" />';
						} elseif (!empty($node->versions[0]->start_line_num) || !empty($node->versions[0]->end_line_num)) {
							echo 'Start line #: <input type="text" style="width:75px;" name="has_annotation_start_line_num" value="'.$node->versions[0]->start_line_num.'" />';
							echo '&nbsp; End line #<input type="text" style="width:75px;" name="has_annotation_end_line_num" value="'.$node->versions[0]->end_line_num.'" />';
							echo '<input type="hidden" name="has_annotation_start_seconds" value="'.@$node->versions[0]->start_seconds.'" />';
							echo '<input type="hidden" name="has_annotation_end_seconds" value="'.@$node->versions[0]->end_seconds.'" />';
							echo '<input type="hidden" name="has_annotation_points" value="'.@$node->versions[0]->points.'" />';
						} elseif (!empty($node->versions[0]->points)) {
							echo 'Left (x), Top (y), Width, Height: <input type="text" style="width:125px;" name="has_annotation_points" value="'.$node->versions[0]->points.'" />';
							echo '<input type="hidden" name="has_annotation_start_seconds" value="'.@$node->versions[0]->start_seconds.'" />';
							echo '<input type="hidden" name="has_annotation_end_seconds" value="'.@$node->versions[0]->end_seconds.'" />';
							echo '<input type="hidden" name="has_annotation_start_line_num" value="'.@$node->versions[0]->start_line_num.'" />';
							echo '<input type="hidden" name="has_annotation_end_line_num" value="'.@$node->versions[0]->end_line_num.'" />';
							echo '<br /><small>May be pixel or percentage values; for percentage add "%" after each value.</small>';
						}
						echo '&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span>';
						echo '</li>';
					}
				?>
				       </ul>
				     </div>
				<? endif ?>
				</div>
			</div>
		</div>

		<div id="tag-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
			    <?
				if (empty($page) || empty($page->versions[$page->version_index]->tag_of)) {
					echo '<span class="tag_of_msg"><p><b>To make this <span class="content_type">page</span> a tag</b>, <a href="javascript:void(null);">choose the items that it tags.</a></p></span>'."\n";
				} else {
					echo '<span class="tag_of_msg"><p><b>This <span class="content_type">page</span> is a tag</b> of:</p></span>'."\n";
				}
				echo '<ul id="tag_of" class="p">'."\n";
				if (isset($page->version_index) && !empty($page->versions[$page->version_index]->tag_of)) {
					foreach ($page->versions[$page->version_index]->tag_of as $node) {
						$title = $node->versions[0]->title;
						$rel_uri = $base_uri.$node->slug;
						echo '<li>';
						echo '<input type="hidden" name="tag_of" value="'.$node->slug.'" />';
						echo $title;
						echo '&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span>';
						echo '</li>'."\n";
					}
				}
				echo "</ul>\n";
				?>
					<div class="form_fields_sub_element tag_of_msg" style="display:none;"><a class="btn btn-default" role="button">Tag additional content</a></div>

					<div id="tr_has_tag">
				<?
					if (empty($page) || empty($page->versions[$page->version_index]->has_tags)) {
						echo '<span class="has_tag_msg"><br><p><b>To tag this <span class="content_type">page</span>,</b> <a href="javascript:void(null);">choose the items that tag it.</a></p></span>'."\n";
					} else {
						echo '<span class="has_tag_msg"><br><p><b>This <span class="content_type">page</span> is tagged by</b> the following:</p></span>'."\n";
					}
				?>
					<ul id="has_tag" class="p">
				<?
					if (isset($page->version_index) && !empty($page->versions[$page->version_index]->has_tags)) {
						foreach ($page->versions[$page->version_index]->has_tags as $node):
							$title = $node->versions[$page->version_index]->title;
							$rel_uri = $base_uri.$node->slug;
							echo '<li>';
							echo '<input type="hidden" name="has_tag" value="'.$node->slug.'" />';
							echo $title;
							echo '&nbsp; <span class="remove">(<a href="javascript:;">remove</a>)</span>';
							echo '</li>';
						endforeach;
					}
				?>
					</ul>

					<div class="form_fields_sub_element has_tag_msg" style="display:none;"><a class="btn btn-default" role="button">Add additional tags</a></div>
					</div>
				</div>
			</div>
		</div>

		<div id="thumbnail-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
					<div class="form-group">
						<label for="choose_thumbnail">Choose an image from your library to use as a thumbnail for this page:</label>
			  			<select id="choose_thumbnail" class="form-control"><option value="">Choose an image</option><?
			  				foreach ($book_images as $book_image_row) {
			  					$slug_version = get_slug_version($book_image_row->slug);
			  					echo '<option value="'.$book_image_row->versions[$book_image_row->version_index]->url.'" '.((@$page->thumbnail==$book_image_row->versions[$book_image_row->version_index]->url)?'selected':'').'>'.$book_image_row->versions[$book_image_row->version_index]->title.((!empty($slug_version))?' ('.$slug_version.')':'').'</option>';
			  				}
			  			?></select>
					</div>
					<div class="form-group">
			  			<label for="enter_thumbnail_url">Or enter any image URL:</label>
			  			<input id="enter_thumbnail_url" class="form-control" type="text" name="scalar:thumbnail" value="<?=@$page->thumbnail?>" />
					</div>
	  			</div>
			</div>
		</div>

		<div id="background-image-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
					<p>Choose an image from your library to use as the background for this page (leaving this blank will cause the page to inherit the book's background):</p>
						<div class="form-group">
						<select id="choose_background" name="scalar:background" class="form-control"><option value="">Choose an image</option><?
		  				$matched = false;
		  				foreach ($book_images as $book_image_row) {
		  					if (@$page->background==$book_image_row->versions[$book_image_row->version_index]->url) $matched = true;
		  					$slug_version = get_slug_version($book_image_row->slug);
		  					echo '<option value="'.$book_image_row->versions[$book_image_row->version_index]->url.'" '.((@$page->background==$book_image_row->versions[$book_image_row->version_index]->url)?'selected':'').'>'.$book_image_row->versions[$book_image_row->version_index]->title.((!empty($slug_version))?' ('.$slug_version.')':'').'</option>';
		  				}
		  				if (@!empty($page->background) && !$matched) {
		  					echo '<option value="'.@$page->background.'" selected>'.@$page->background.'</option>';
		  				}
		  			?></select></div>
					<?=((@!empty($page->background))?'<div class="well"><img src="'.abs_url($page->background,confirm_slash(base_url()).confirm_slash($book->slug)).'" class="thumb_preview" /></div>':'')?>
				</div>
			</div>
		</div>

		<div id="banner-image-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
					<p>Choose an image from your library to use as the primary visual for the Image Header, Splash, and Book Splash layouts:</p>
						<div class="form-group">
						<select id="choose_banner" name="scalar:banner" class="form-control"><option value="">Choose an image</option><?
		  				$matched = false;
		  				foreach ($book_images as $book_image_row) {
		  					if (@$page->banner==$book_image_row->versions[$book_image_row->version_index]->url) $matched = true;
		  					$slug_version = get_slug_version($book_image_row->slug);
		  					echo '<option value="'.$book_image_row->versions[$book_image_row->version_index]->url.'" '.((@$page->banner==$book_image_row->versions[$book_image_row->version_index]->url)?'selected':'').'>'.$book_image_row->versions[$book_image_row->version_index]->title.((!empty($slug_version))?' ('.$slug_version.')':'').'</option>';
		  				}
		  				if (@!empty($page->banner) && !$matched) {
		  					echo '<option value="'.@$page->banner.'" selected>'.@$page->banner.'</option>';
		  				}
		  			?></select></div>
					<?=((@!empty($page->banner))?'<div class="well"><img src="'.abs_url($page->banner,confirm_slash(base_url()).confirm_slash($book->slug)).'" class="thumb_preview" /></div>':'')?>
				</div>
			</div>
		</div>

		<div id="custom-css-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-12">
					<p>Enter custom CSS to be applied to this page and its path or tag children:</p>
					<small>e.g., .navbar {background-color:red;}, no &lt;style&gt; tags required</small>
					<textarea class="form-control monospace_font" rows="10" name="scalar:custom_style"><?=!empty($page->custom_style) ? $page->custom_style : ''?></textarea>
				</div>
			</div>
		</div>

		<div id="custom-javascript-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-12">
					<p>Enter custom JavaScript to be applied to this page and its path or tag children:</p>
	   				<small>Javascript or jQuery source, no &lt;script&gt; tags required</small>
	   				<textarea class="form-control monospace_font" rows="10" name="scalar:custom_scripts"><?=!empty($page->custom_scripts) ? $page->custom_scripts : ''?></textarea>
				</div>
			</div>
		</div>

		<div id="background-audio-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-8">
					<p>Select an audio file from your library to play on this page (please use judiciously, and consider accessibility for the hearing impaired):</p>
						<div class="form-group">
			   			<select name="scalar:audio" class="form-control"><option value="">Choose an audio file</option><?
		  			  	$matched = false;
		  				foreach ($book_audio as $book_audio_row) {
		  					if (@$page->audio==$book_audio_row->versions[$book_audio_row->version_index]->url) $matched = true;
		  					$slug_version = get_slug_version($book_audio_row->slug);
		  					echo '<option value="'.$book_audio_row->versions[$book_audio_row->version_index]->url.'" '.((@$page->audio==$book_audio_row->versions[$book_audio_row->version_index]->url)?'selected':'').'>'.$book_audio_row->versions[$book_audio_row->version_index]->title.((!empty($slug_version))?' ('.$slug_version.')':'').'</option>';
		  				}
		  				if (@!empty($page->audio) && !$matched) {
		  					echo '<option value="'.@$page->audio.'" selected>'.@$page->audio.'</option>';
		  				}
		  			?></select></div>
				</div>
			</div>
		</div>

		<div id="color-pane" role="tabpanel" class="tab-pane">
			<div class="row p" style="display: none;">
				<div class="col-md-8">
					<h4>Color</h4>
					<small>e.g., for path nav bar</small>
					<input class="form-control" type="text" id="color_select" name="scalar:color" value="<?=(!empty($page->color))?$page->color:'#ffffff'?>" />
					<a href="javascript:;" class="btn btn-default btn-xs" role="button" onclick="$(this).next().toggle();">Choose</a>
  					<div style="display:none;margin-top:6px;"><div id="colorpicker"></div></div>
				</div>
			</div>
		</div>

		<div id="metadata-pane" role="tabpanel" class="tab-pane">
			<div class="row p">
				<div class="col-md-12">
					<div id="metadata_rows" class="form-horizontal">
						<div class="form-group">
							<label for="slug" class="col-sm-3 control-label">Scalar URL</label>
							<div class="col-sm-9">
								<?=$base_uri?><input class="form-control" style="width:200px;display:inline;" name="scalar:slug" value="<?=$page_url?>" id="slug" /><? if (empty($page_url)) echo '<span style="white-space:nowrap;">&nbsp; (will auto-generate)</span>';?>
							</div>
						</div>
						<? if (@!empty($page->versions[$page->version_index]->attribution->fullname)): ?>
						<div class="form-group">
							<label for="attribution" class="col-sm-3 control-label">Attribution</label>
							<div class="col-sm-9">
								<input id="attribution" class="form-control" name="scalar:fullname" value="<?=htmlspecialchars($page->versions[$page->version_index]->attribution->fullname)?>" style="width:180px;" />&nbsp; &nbsp; <small style="white-space:nowrap;">Alerts readers that page is by someone other than the book's authors (e.g. comments)</small>
							</div>
						</div>
						<? endif ?>
						<div class="form-group">
							<label for="visibility" class="col-sm-3 control-label">Page visibility</label>
							<div class="col-sm-2">
								<select name="scalar:is_live" class="form-control"><option value="0" <?=((@!$page->is_live)?'selected':'')?>>Hidden</option><option value="1" <?=(!isset($page->version_index)||(@$page->is_live)?'selected':'')?>>Visible</option></select>
							</div>
						</div>
						<div class="form-group">
							<label for="content_type" class="col-sm-3 control-label">Content type</label>
							<div class="col-sm-2">
							    <span style="display:none;"><!-- jQuery uses these fields to show/hide form fields related to page or media -->
						   	 	<input type="radio" name="rdf:type" id="type_text" onchange="checkTypeSelect()" value="http://scalar.usc.edu/2012/01/scalar-ns#Composite"<?=((!isset($page->type)||$page->type=='composite')?' CHECKED':'')?>><label for="type_text">Page</label>
						    	<input type="radio" name="rdf:type" id="type_media" onchange="checkTypeSelect()" value="http://scalar.usc.edu/2012/01/scalar-ns#Media"<?=((isset($page->type)&&$page->type!='composite')?' CHECKED':'')?>><label for="type_media">Media File</label>
						    	&nbsp; &nbsp;
							    </span>
							    <select id="content_type" name="scalar:category" class="form-control"><?
								$category =@ (!empty($page->category)) ? $page->category : null;
								if (empty($category) && $is_new) {
									if (strtolower($user_level)=='commentator') $category = 'commentary';
									if (strtolower($user_level)=='reviewer') $category = 'review';
								}
							  	echo '<option value="" '.((empty($category))?'selected':'').'>'.@ucwords($book->scope).' content</option>';
							  	foreach ($categories as $_category) {
							  		echo '<option value="'.strtolower($_category).'" '.((strtolower($_category)==strtolower($category))?'selected':'').'>'.ucwords($_category).'</option>';
							  	}
								?></select>
							</div>
						</div>
						<?
						if (isset($page->version_index) && isset($page->versions[$page->version_index]->rdf) && !empty($page->versions[$page->version_index]->rdf)):
							$counter = 0;
							foreach ($page->versions[$page->version_index]->rdf as $p => $values):
								$p = toNS($p, $ns);
								foreach ($values as $value) {
									echo '<div class="form-group '.$p.'">';
									echo '<label for="'.$p.'-input-'.$counter.'" class="col-sm-3 control-label">';
									echo $p;
									echo '</label>';
									echo '<div class="col-sm-9">';
									$o = trim($value['value']);
									echo '<input id="'.$p.'-input-'.$counter.'" class="form-control" type="text" name="'.$p.'" value="'.htmlspecialchars($o).'" />';
									echo '</div>';
									echo "</div>\n";
									$counter++;
								}
							endforeach;
						endif;
						?>
					</div>
					<div class="form-horizontal">
						<div class="form-group">
							<div class="col-sm-9 col-md-offset-3">
								<a href="javascript:;" class="btn btn-default btn-sm add_additional_metadata" role="button">Add additional metadata</a>&nbsp;
								<a href="javascript:;" class="btn btn-default btn-sm populate_exif_fields" role="button">Auto-populate IPTC fields</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<hr>

<div class="row clearboth">
	<div class="col-md-12" style="text-align:right;margin-top:10px;">
		<span id="saved_text" class="text-success" style="float:left;display:none;"></span>
		<div id="spinner_wrapper" style="width:30px;display:inline-block;">&nbsp;</div> &nbsp;
		<a href="javascript:;" class="btn btn-default" onclick="if (confirm('Are you sure you wish to cancel edits?  Any unsaved data will be lost.')) {document.location.href='<?=$base_uri?><?=@$page->slug?>'} else {return false;}">Cancel</a>&nbsp; &nbsp;
		<input type="button" class="btn btn-default" value="Save" onclick="validate_form($('#edit_form'),true);" />&nbsp; &nbsp;
		<input type="submit" class="btn btn-primary" value="Save and view" />
	</div>
</div>
<br />

<?
if (isset($page->version_index)):
	// Has references
	if (!empty($page->versions[$page->version_index]->has_references)) {
		foreach ($page->versions[$page->version_index]->has_references as $node) {
			echo '<input type="hidden" name="has_reference" value="'.$node->slug.'" />';
			echo '<input type="hidden" name="has_reference_reference_text" value="'.htmlspecialchars(@$node->versions[0]->reference_text).'" />';
		}
	}
	// Table of Contents
	echo '<input type="hidden" name="scalar:sort_number" value="'.$page->versions[$page->version_index]->sort_number.'" />';
endif;
?>

</form>




