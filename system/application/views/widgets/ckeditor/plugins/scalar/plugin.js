CKEDITOR._scalar = {
	selectcontent : function(options) {
		$('<div></div>').content_selector(options);
	},
	contentoptions : function(options) {
		$('<div></div>').content_options(options);
	},
	external_link : function(editor, options) {
		CKEDITOR.dialog.add( 'external_link', function(editor) {
			return {
				title : 'Insert External Link',
				width : 500,
				minHeight : 100,
				contents : [{
					id : 'general',
					label : 'External Link',
					elements : [{
						type : 'html',
						html : 'Add a hyperlink to an external site. Scalar will maintain a header bar that allows easy navigation back to your book (unless disallowed by the external site).'
					},{
						type : 'text',
						id : 'href',
						label : 'URL',
						'default' : 'http://',
						validate : CKEDITOR.dialog.validate.notEmpty( 'URL is a required field' ),
						required : true,
						setup : function(element) {
							if (null!==element.getAttribute('href')) this.setValue(element.getAttribute('href'));
						},
						commit : function(data) {
							data.href = this.getValue();
						}
					},{
						type : 'checkbox',
						id : 'target_blank',
						label : 'Open in a new browser window',
						'default' : false,
						setup : function(element) {
							if ('_blank'==element.getAttribute('target')) this.setValue('checked');
						},
						commit : function(data) {
							data.target = (this.getValue()) ? '_blank' : false;
						}
					}]
				}],
				onShow : function() {
					var sel = editor.getSelection(), element = sel.getStartElement();
					if (sel.getRanges()[0].collapsed) {
						alert('Please select text to transform into a link');
					    ckCancel = this._.buttons['cancel'],
					    ckCancel.click();
						return;
					}
					this.element = editor.document.createElement('a');
					this.element.setHtml(sel.getSelectedText());
					if (element) element = element.getAscendant('a', true);
					// Browsers won't allow href attribute to be re-written, so doing this round-about by always creating a new <a> but propogating it with the existing element's values if it exists
					if (!element || element.getName() != 'a' || element.data('cke-realelement' )) {
						this.setupContent(this.element);
					} else {
						this.setupContent(element);
					}
				},
				onOk : function() {
					var dialog = this, data = {};
					this.commitContent(data);
					if (data.href.length) this.element.setAttribute('href', data.href);
					if (data.target) this.element.setAttribute('target', data.target);
					editor.insertElement(this.element);
				}
			};
		});
		var _command = new CKEDITOR.dialogCommand('external_link');
		editor.addCommand('_external_link_dialog', _command);
		editor.execCommand("_external_link_dialog");
	}
};

CKEDITOR.plugins.add( 'scalar', {
    icons: 'scalar1,scalar2,scalar3,scalar4,scalar5,scalar6,scalar7',
    requires: 'dialog',
    init: function( editor ) {

			//Callback functions for content options selection
				annotationLinkCallback = function(node,element){
					var isEdit = typeof element.$.href != 'undefined' && element.$.href != '';
					CKEDITOR._scalar.contentoptions({data:reference_options['insertAnnotation'],node:node,element:element,callback:function(options) {
						var node = options.node;
						delete(options.node);

						if(typeof node.targets !== 'undefined' && node.targets.length > 0){
							var href = node.targets[0].version['http://simile.mit.edu/2003/10/ontologies/artstor#url'][0].value+'#'+node.slug;
							var resource = node.targets[0].slug;
						}else{
							var href = node.parent.url+'#'+node.slug;
							var resource = node.parent.slug;
						}

						element.setAttribute('href', href);
						if(isEdit){
							element.data('cke-saved-href',href);
						}
						element.setAttribute('resource', resource);
						for (var key in options) {
							element.setAttribute('data-'+key, options[key]);
						}
						if(!isEdit){
							editor.insertElement(element);
						}else{
							editor.updateElement(element);
						}

						(function(thisSlug,e){
							if(scalarapi.loadPage( thisSlug, false, function(){
								addCKLinkedMediaPreview(thisSlug,e);
							}) == "loaded"){
								addCKLinkedMediaPreview(thisSlug,e);
							}
						})(resource,element);

					}});
				};
				inlineAnnotationCallback = function(node,element){
					var isEdit = typeof element.$.href != 'undefined' && element.$.href != '';
					CKEDITOR._scalar.contentoptions({data:reference_options['insertInlineAnnotation'],node:node,element:element,callback:function(options) {
							var node = options.node;
							delete(options.node);

							if(typeof node.targets !== 'undefined' && node.targets.length > 0){
								var href = node.targets[0].version['http://simile.mit.edu/2003/10/ontologies/artstor#url'][0].value+'#'+node.slug;
								var resource = node.targets[0].slug;
							}else{
								var href = node.parent.url+'#'+node.slug;
								var resource = node.parent.slug;
							}

							element.setAttribute('name','scalar-inline-annotation');  // Required to let empty <a> through
							element.setAttribute('class', 'inline');
							element.setAttribute('href', href);
							if(isEdit){
								element.data('cke-saved-href',href);
							}
							element.setAttribute('resource', resource);
							for (var key in options) {
								element.setAttribute('data-'+key, options[key]);
							}
							if(!isEdit){
								editor.insertElement(element);
							}else{
								editor.updateElement(element);
							}

							if(cke_loadedScalarInline.indexOf(resource)==-1){
								(function(thisSlug){
									if(scalarapi.loadPage( thisSlug, false, function(){
											addCKInlineMediaPreview(thisSlug,element);
									}) == "loaded"){

											addCKInlineMediaPreview(thisSlug,element);
									}
								})(resource,element);
							}
					}});
				};
				mediaLinkCallback = function(node,element){
					var isEdit = typeof element.$.href != 'undefined' && element.$.href != '';
					CKEDITOR._scalar.contentoptions({data:reference_options['insertMediaLink'],node:node,element:element,callback:function(options) {
								var node = options.node;
								delete(options.node);

								if(typeof node.version !== 'undefined'){
									var href = node.version['http://simile.mit.edu/2003/10/ontologies/artstor#url'][0].value;
								}else{
									var href = node.current.sourceFile;
								}

								element.setAttribute('href', href);

								//Also have to set cke-saved-href if this is an edit, so that we can actually change the href value!
								if(isEdit){
									element.data('cke-saved-href',href);
								}

								element.setAttribute('resource', node.slug);
								for (var key in options) {
									element.setAttribute('data-'+key, options[key]);
								}
								if(!isEdit){
									editor.insertElement(element);
								}else{
									editor.updateElement(element);
								}

								var ckeFrame = $('.cke_contents>iframe').contents();
								var slug = node.slug;

								(function(thisSlug,e){
									if(scalarapi.loadPage( thisSlug, false, function(){
										addCKLinkedMediaPreview(thisSlug,e);
									}) == "loaded"){
										addCKLinkedMediaPreview(thisSlug,e);
									}
								})(slug,element);
					}});
				};
				inlineMediaCallback = function(node,element){
					var isEdit = typeof element.$.href != 'undefined' && element.$.href != '';
					CKEDITOR._scalar.contentoptions({data:reference_options['insertMediaelement'],node:node,element:element,callback:function(options) {
							var node = options.node;
							delete(options.node);

							element.setAttribute('name','scalar-inline-media');  // Required to let empty <a> through
							element.setAttribute('class', 'inline');

							if(typeof node.version !== 'undefined'){
								var href = node.version['http://simile.mit.edu/2003/10/ontologies/artstor#url'][0].value;
							}else{
								var href = node.current.sourceFile;
							}

							element.setAttribute('href', href);
							//Also have to set cke-saved-href if this is an edit, so that we can actually change the href value!
							if(isEdit){
								element.data('cke-saved-href',href);
							}
							element.setAttribute('resource', node.slug);
							for (var key in options) {
								element.setAttribute('data-'+key, options[key]);
							}
							if(!isEdit){
								editor.insertElement(element);
							}else{
								editor.updateElement(element);
							}


							if(cke_loadedScalarInline.indexOf(node.slug)==-1){
								(function(thisSlug,element){
									if(scalarapi.loadPage( thisSlug, false, function(){
											addCKInlineMediaPreview(thisSlug,element);
									}) == "loaded"){
											addCKInlineMediaPreview(thisSlug,element);
									}
								})(node.slug,element)
							}
					}});
				};
			addCKInlineMediaPreview = function(slug,element){
				var $element = element.$;
				var ckeFrame = $('.cke_contents>iframe').contents();
				var node = scalarapi.getNode(slug);
				var slug = slug;
				cke_loadedScalarInline.push(slug);
				if(node.thumbnail != null){
					var cssElement = '<style>'+
															'a[resource="'+slug+'"].inline,a[href$="#'+slug+'"].inline{ background-size: contain; background-repeat: no-repeat; background-position: center center; background-image: url('+node.thumbnail+');}'+
													 '</style>';
					$('.cke_contents>iframe').contents().find('head').append(cssElement);
				}
				$($element).data({
					element: element,
					type: element.getAttribute('href').indexOf('#')<0?'media':'annotation'
				}).off('mouseout mouseover').hover(function(){
						var position = $(this).position();
						var framePosition = $('.cke_contents>iframe').offset();
						var frameScroll = $('.cke_contents>iframe').contents().scrollTop();
						var pageScroll = $(window).scrollTop();
						var topPos = framePosition.top+position.top-frameScroll+10;
						if(frameScroll > position.top){
							topPos = framePosition.top+10;
						}
						if(frameScroll-position.top < 30){
							$('#scalarInlineGearIcon').data({
								element: $(this).data('element'),
								type: $(this).data('type')
							}).css({left: framePosition.left+position.left+$(this).outerWidth()+parseInt($(this).css('margin-left'))-40, top: topPos}).show().off('click').click(function(){
								$('#scalarInlineGearIcon').hide();
								var element = $(this).data('element');
								isEdit = true;
								CKEDITOR._scalar.selectcontent($(element.$).data('selectOptions'));
							});

							window.clearTimeout($('#scalarInlineGearIcon').data('timeout'));
						}
				},function(){
					$('#scalarInlineGearIcon').data('timeout',window.setTimeout(function(){	$('#scalarInlineGearIcon').hide(); },200));
				});
			};
			addCKLinkedMediaPreview = function(slug,element){
				$element = element.$;
				var node = scalarapi.getNode(slug);
				var slug = slug;
				$($element).off('mouseout mouseover').hover(function(){
					var position = $(this).position();
					var framePosition = $('.cke_contents>iframe').offset();
					var frameScroll = $('.cke_contents>iframe').contents().scrollTop();
					var pageScroll = $(window).scrollTop();
					var thumbnail = node.thumbnail;
					var topPos = framePosition.top+position.top-frameScroll-pageScroll+30;

					if(thumbnail == null){
						thumbnail = widgets_uri+'/ckeditor/plugins/scalar/styles/missingThumbnail.png';
					}
					var data = {
						element : element,
						type : element.getAttribute('href').indexOf('#')<0?'media':'annotation'
					};

					$('#scalarLinkTooltip').css({left: framePosition.left+position.left+($(this).width()/2)-50, top: topPos}).show().data(data).find('.thumbnail').html('<img src="'+thumbnail+'">');
					$('#scalarLinkTooltip .gearIcon').off('click').click(function(){
						var $tooltip = $('#scalarLinkTooltip');
						$tooltip.hide();
						var element = $tooltip.data('element');
						isEdit = true;
						CKEDITOR._scalar.selectcontent($(element.$).data('selectOptions'));
					});
					window.clearTimeout($('#scalarLinkTooltip').data('timeout'));
				},function(){
					$('#scalarLinkTooltip').data('timeout',window.setTimeout(function(){	$('#scalarLinkTooltip').hide(); },200));
				});
			};
			cke_loadedScalarInline = [];

			editor.on('mode',function(e){
				var editor = e.editor;

				if(editor.mode == 'source'){
					cke_loadedScalarInline = [];
					return;
				}

				var ckeFrame = $('.cke_contents>iframe').contents();
				var tooltip = $('<div id="scalarLinkTooltip"><div class="gearIcon"></div><div class="thumbnail"></div></div>');
				tooltip.hover(function(){
					window.clearTimeout($('#scalarLinkTooltip').data('timeout'));
				},function(){
					$('#scalarLinkTooltip').data('timeout',window.setTimeout(function(){	$('#scalarLinkTooltip').hide(); },200));
				});
				tooltip.appendTo('body');
				var inlineGearIcon = $('<div id="scalarInlineGearIcon" class="gearIcon"></div>');
				inlineGearIcon.hover(function(){
					window.clearTimeout($('#scalarInlineGearIcon').data('timeout'));
				},function(){
					$('#scalarInlineGearIcon').data('timeout',window.setTimeout(function(){	$('#scalarInlineGearIcon').hide(); },200));
				});
				inlineGearIcon.appendTo('body');
				var anchors = editor.document.find('a[resource]');
				var num_anchors = anchors.count();
				for(var i = 0; i < num_anchors; i++){
						var element = anchors.getItem(i);

						var href = element.getAttribute('href');

						var currentSlug = element.getAttribute('resource');
						if(currentSlug == null || element.data('widget') != null){
							continue;
						}

						var isAnnotation = false;
						if(href != null && href.indexOf('#')>=0){
							isAnnotation = true;
							currentSlug = element.$.hash.replace('#','');
						}

						if(!element.hasClass('inline')){
							if(!isAnnotation){
								$(element.$).data({
									element : element,
									contentOptionsCallback : mediaLinkCallback
								});
								$(element.$).data('selectOptions',{type:'media',changeable:false,multiple:false,msg:'Insert Scalar Media Link',element:element,callback:$(element.$).data('contentOptionsCallback')});
							}else{
								$(element.$).data({
									element : element,
									contentOptionsCallback : annotationLinkCallback
								});

								$(element.$).data('selectOptions',{type:'annotation',changeable:false,multiple:false,rec:1,msg:'Insert Scalar Annotation',element:element,callback:$(element.$).data('contentOptionsCallback')});
							}
							(function(thisSlug, e){
								if(scalarapi.loadPage( thisSlug, false, function(){
									addCKLinkedMediaPreview(thisSlug,e);
								}) == "loaded"){
									addCKLinkedMediaPreview(thisSlug,e);
								}
							})(currentSlug,element);
						}else if(cke_loadedScalarInline.indexOf(currentSlug)==-1){
							if(!isAnnotation){
								$(element.$).data({
									element : element,
									contentOptionsCallback : inlineMediaCallback
								});
								$(element.$).data('selectOptions',{type:'media',changeable:false,multiple:false,msg:'Insert Inline Scalar Media Link',element:element,callback:$(element.$).data('contentOptionsCallback')});
							}else{
								$(element.$).data({
									element : element,
									contentOptionsCallback : inlineAnnotationCallback
								});

								$(element.$).data('selectOptions',{type:'annotation',changeable:false,multiple:false,rec:1,msg:'Insert Inline Scalar Annotation',element:element,callback:$(element.$).data('contentOptionsCallback')});
							}
							(function(thisSlug,element){
								if(scalarapi.loadPage( thisSlug, false, function(){
										addCKInlineMediaPreview(thisSlug,element);
								}) == "loaded"){
										addCKInlineMediaPreview(thisSlug,element);
								}
							})(currentSlug,element);
						}
				};
			});
	    var pluginDirectory = this.path;

	    	editor.addContentsCss( pluginDirectory + 'styles/scalar.css' );
        editor.addCommand( 'insertScalar1', {
            exec: function( editor ) {
			    		var sel = editor.getSelection();
							var isEdit = false;
							var element = sel.getStartElement();

							//Check to see if we currently have an anchor tag - if so, make sure it's a non-inline media link
							if ( element.data('widget') == null && element.getAscendant( 'a', true ) ) {
								element = element.getAscendant( 'a', true );
								if(element.getAttribute('resource')!=null && !element.hasClass('inline') && element.getAttribute('href').indexOf('#')<0){
									//Not inline, no annotation
									isEdit = true;
								}
							}
							if(!isEdit){
				    		if (sel.getRanges()[0].collapsed) {
									alert('Please select text to transform into a media link');
				    			return;
								}else{
									var sel = editor.getSelection();
									element = editor.document.createElement('a');
									element.setHtml(sel.getSelectedText());
									$(element.$).data({
										element : element,
										contentOptionsCallback : mediaLinkCallback
									});
									$(element.$).data('selectOptions',{type:'media',changeable:false,multiple:false,msg:'Insert Scalar Media Link',element:element,callback:$(element.$).data('contentOptionsCallback')});
								}
							}
							CKEDITOR._scalar.selectcontent($(element.$).data('selectOptions'));
            }
        });
        editor.addCommand( 'insertScalar2', {
            exec: function( editor ) {
        		var sel = editor.getSelection();
						var element = sel.getStartElement();
						var isEdit = false;
						//Check to see if we currently have an anchor tag - if so, make sure it's a non-inline media link
						if ( element.data('widget') == null && element.getAscendant( 'a', true ) ) {
							element = element.getAscendant( 'a', true );
							if(element.getAttribute('resource')!=null && element.hasClass('inline') && element.getAttribute('href').indexOf('#')<0){
								//Is inline, no annotation
								isEdit = true;
							}
						}

						if(!isEdit){
							element = editor.document.createElement('a')
							$(element.$).data({
								element : element,
								contentOptionsCallback : inlineMediaCallback
							});
							$(element.$).data('selectOptions',{type:'media',changeable:false,multiple:false,msg:'Insert Inline Scalar Media Link',element:element,callback:$(element.$).data('contentOptionsCallback')});
						}

        		CKEDITOR._scalar.selectcontent($(element.$).data('selectOptions'));
					}
        });
        editor.addCommand( 'insertScalar3', {
            exec: function( editor ) {
			    		var sel = editor.getSelection();
							var isEdit = false;
							var element = sel.getStartElement();

							//Check to see if we currently have an anchor tag - if so, make sure it's a non-inline media link
							if ( element.data('widget') == null && element.getAscendant( 'a', true ) ) {
								element = element.getAscendant( 'a', true );
								if(element.getAttribute('resource')!=null && !element.hasClass('inline') && element.getAttribute('href').indexOf('#')>=0){
									//Not inline, with annotation
									isEdit = true;
								}
							}
							if(!isEdit){
								if (sel.getRanges()[0].collapsed) {
									alert('Please select text to transform into a media link');
									return;
								}else{
									var sel = editor.getSelection();
											element = editor.document.createElement('a');
											element.setHtml(sel.getSelectedText());
									$(element.$).data({
										element : element,
										contentOptionsCallback : annotationLinkCallback
									});

									$(element.$).data('selectOptions',{type:'annotation',changeable:false,multiple:false,rec:1,msg:'Insert Scalar Annotation',element:element,callback:$(element.$).data('contentOptionsCallback')});
								}
							}

							CKEDITOR._scalar.selectcontent($(element.$).data('selectOptions'));
            }
        });
        editor.addCommand( 'insertScalar4', {
            exec: function( editor ) {
							var sel = editor.getSelection();
							var element = sel.getStartElement();
							var isEdit = false;
							//Check to see if we currently have an anchor tag - if so, make sure it's a non-inline media link
							if ( element.data('widget') == null && element.getAscendant( 'a', true ) ) {
								element = element.getAscendant( 'a', true );
								if(element.getAttribute('resource')!=null && element.hasClass('inline') && element.getAttribute('href').indexOf('#')>=0){
									//Is inline, with annotation
									isEdit = true;
								}
							}
							if(!isEdit){
								element = editor.document.createElement('a');
								$(element.$).data({
									element : element,
									contentOptionsCallback : inlineAnnotationCallback
								});

								$(element.$).data('selectOptions',{type:'annotation',changeable:false,multiple:false,rec:1,msg:'Insert Inline Scalar Annotation',element:element,callback:$(element.$).data('contentOptionsCallback')});
							}

							CKEDITOR._scalar.selectcontent($(element.$).data('selectOptions'));
            }
        });
        editor.addCommand( 'insertScalar5', {
            exec: function( editor ) {
	    		var sel = editor.getSelection();
	    		if (sel.getRanges()[0].collapsed) {
	    			alert('Please select text to transform into a note link');
	    			return;
	    		}
        		CKEDITOR._scalar.selectcontent({changeable:true,multiple:false,onthefly:true,msg:'Insert Scalar Note',callback:function(node){
        			CKEDITOR._scalar.contentoptions({data:reference_options['insertNote'],callback:function(options) {
	        			var sel = editor.getSelection();
	            		element = editor.document.createElement('span');
	            		element.setHtml(sel.getSelectedText());
	            		element.setAttribute('class', 'note');
	        			element.setAttribute('rev', 'scalar:has_note');
	        			element.setAttribute('resource', node.slug);
            			for (var key in options) {
            				element.setAttribute('data-'+key, options[key]);
            			}
	        			editor.insertElement(element);
        			}});
        		}});
            }
        });
        editor.addCommand( 'insertScalar6', {
            exec: function( editor ) {
	    		var sel = editor.getSelection();
	    		if (sel.getRanges()[0].collapsed) {
	    			alert('Please select text to transform into a link');
	    			return;
	    		}
        		CKEDITOR._scalar.selectcontent({changeable:true,multiple:false,onthefly:true,msg:'Insert link to Scalar content',callback:function(node){
        			CKEDITOR._scalar.contentoptions({data:reference_options['createInternalLink'],callback:function(options) {
	        			var sel = editor.getSelection();
	            		element = editor.document.createElement('a');
	            		element.setHtml(sel.getSelectedText());
	        				element.setAttribute('href', node.slug);
            			for (var key in options) {
            				element.setAttribute('data-'+key, options[key]);
            			}
	        			editor.insertElement(element);
        			}});
        		}});
            }
        });
        editor.addCommand( 'insertScalar7', {  // External link
            exec: function( editor ) {
        		CKEDITOR._scalar.external_link(editor, {});
            }
        });
        editor.ui.addButton( 'Scalar1', {
            label: 'Insert Scalar Media Link',
            command: 'insertScalar1',
            toolbar: 'links'
        });
        editor.ui.addButton( 'Scalar2', {
            label: 'Insert Inline Scalar Media Link',
            command: 'insertScalar2',
            toolbar: 'links'
        });
        editor.ui.addButton( 'Scalar3', {
            label: 'Insert Scalar Annotation',
            command: 'insertScalar3',
            toolbar: 'links'
        });
        editor.ui.addButton( 'Scalar4', {
            label: 'Insert Inline Scalar Annotation',
            command: 'insertScalar4',
            toolbar: 'links'
        });
        editor.ui.addButton( 'Scalar5', {
            label: 'Insert Scalar Note',
            command: 'insertScalar5',
            toolbar: 'links'
        });
        editor.ui.addButton( 'Scalar6', {
            label: 'Insert Link to another Scalar Page',
            command: 'insertScalar6',
            toolbar: 'links'
        });
        editor.ui.addButton( 'Scalar7', {
            label: 'Insert External Link',
            command: 'insertScalar7',
            toolbar: 'links'
        });
    }
});
