<?php
ob_start();
ob_flush();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head><meta http-equiv="X-UA-Compatible" content="IE=8" /><meta name="robots" content="noindex,nofollow"></head>
    <body>
         <div id="loader" style="width:100px;background-color:red;color:white">Loading...</div>
    </body>
    <?php ob_flush(); ?>
    <?php ob_clean(); ?>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
		<link link type="text/css" rel="stylesheet" media="screen" href="<?php echo GADGET_BASE_URI; ?>resources/css/jquery-ui.css" >
        <?php $this->renderSkin(); ?>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
        <script type="text/javascript" src="<?php echo GADGET_BASE_URI; ?>resources/scripts/gadgets.js" ></script>
        <?php $this->renderScripts(); ?>
        <script type="text/javascript">
    $(document).ready(function(){
        $("#loader").hide();
        $(".view-container").show();
    });
    </script>
	<script type="text/javascript">
	(function( $ ) {
		$.widget( "ui.combobox", {
			_create: function() {
				var self = this,
					select = this.element.hide(),
					selected = select.children( ":selected" ),
					value = selected.val() ? selected.text() : "";
				var input = this.input = $( "<input>" )
					.insertAfter( select )
					.val( value )
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: function( request, response ) {
							var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
							$(input).removeClass("invalidInputData");
							response( select.children( "option" ).map(function() {
								var text = $( this ).text();
								if ( this.value && ( !request.term || matcher.test(text) ) )
									return {
										label: text.replace(
											new RegExp(
												"(?![^&;]+;)(?!<[^<>]*)(" +
												$.ui.autocomplete.escapeRegex(request.term) +
												")(?![^<>]*>)(?![^&;]+;)", "gi"
											), "<strong>$1</strong>" ),
										value: text.replace(/\?/g,''),
										parentid: $(this).data("parentid"),
										id: $(this).val(),
										option: this
									};
							}) );
						},
						select: function( event, ui ) {
							ui.item.option.selected = true;
							self._trigger( "selected", event, {
								item: ui.item.option
							});
						},
						change: function( event, ui ) {
							if ( !ui.item ) {
								var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
									valid = false;
								select.children( "option" ).each(function() {
									if ( $( this ).text().match( matcher ) ) {
										this.selected = valid = true;
										return false;
									}
								});
								if ( !valid ) {
									// remove invalid value, as it didn't match anything
									$( this ).val( "" );
									select.children(":selected").prop("selected", false);
									select.val( "-1" );
									input.data( "autocomplete" ).term = "";
									return false;
								}
							}
							$(input).removeClass("invalidInputData");
						}
					})
					.addClass( "ui-widget ui-widget-content ui-corner-left" );

				input.data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						  var label = item.label || "";
						  var txt = label.replace(/\?/g,"");
						  var len = label.match(/\?/g);
						  if( len ){
							  len = len.length;
						  }else{
							  len = 0;
						  }
						  label = "";
						  for(var i=0; i<len; i+=1){
							  label += "<span style='padding-left:10px;display: inline-box;'></span>";
						  }
						  label += "<span style='display: inline-block;white-space:nowrap;overflow:hidden;padding:0;margin:0;text-align:left;'>" + txt + "</span>";
						  label = "<div data-parentid='"+item.parentid+"' style='overflow:hidden;;white-space:nowrap;width: 100%;padding:0;margin:0;text-align:left;'>" + label + "</div>";
						  var _crawlitems = function(dom,id){
							  $.each($(dom).children("li"), function(i,e){
									if( ($(e).data("parentid")<<0) == (id<<0) ){
										$(e).addClass("treeitems");
										_crawlitems(dom,$(e).data("id"));
									}
								});
						  };
						  return $( "<li data-parentid='"+item.parentid+"' data-id='"+item.id+"'></li>" )
							  .data( "item.autocomplete", item )
							  .bind("mousemove", function(ev){
								$(".treeitems").removeClass("treeitems");
								var id = $(this).data("id");
								$(this).addClass("treeitems");
								_crawlitems($(this).parent(),id);
								
							}).bind("mouseleave", function(ev){
								$(".treeitems").removeClass("treeitems");
							})
							.append( "<a>" + label + "</a>" )
							.appendTo( ul );
					  };

				input.bind("keyup",function(){
					var txt = $(input).val(), found = false;
					if(txt==""){
						found = true;
						$(input).removeClass("invalidInputData");
						return;
					}else{
						select.children("option").each(function(){
							if($(this).text().toLowerCase()==txt.toLowerCase()){
								found=true;
								return false;
							}
						});
					}
					if(found ){
						$(input).removeClass("invalidInputData");
					} else {
						$(input).addClass("invalidInputData");
					}
					
				}).bind("blur", function(){
					$(input).removeClass("invalidInputData");
				});

				this.button = $( "<button type='button'>&nbsp;</button>" )
					.attr( "tabIndex", -1 )
					.attr( "title", "Show All Items" )
					.insertAfter( input )
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.removeClass( "ui-corner-all" )
					.addClass( "ui-corner-right ui-button-icon" )
					.on("click", function() {
						// close if already visible
						if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
							input.autocomplete( "close" );
							return;
						}

						// work around a bug (likely same cause as #5265)
						$( this ).blur();

						// pass empty string as value to search for, displaying all results
						input.autocomplete( "search", "" );
						input.focus();
					});
			},

			destroy: function() {
				this.input.remove();
				this.button.remove();
				this.element.show();
				$.Widget.prototype.destroy.call( this );
			}
		});
	})( jQuery );
	$(document).ready(function(){
		$("select.searchdroplist").parent().addClass("ui-widget");
		$("select.searchdroplist").combobox();
	});
	</script>
    </head>
    <body>
        <div id="ajaxloader" style="display:none" >
            <img alt="" style="position:absolute;display:inline-block;top:0;left:0;z-index:1000;width:100%;height:100%;opacity:0.4;filter:alpha(opacity=40);-moz-opacity: .4;" src="<?php echo GADGET_BASE_URI; ?>resources/images/white.png" />
            <img alt="" style="position:absolute;display:inline-block;width:40px;height:40px;top:45%;left:45%;dispaly:none;" src="<?php echo GADGET_BASE_URI; ?>resources/images/ajax-loader.gif" />
        </div>
        <div class="view-container" style="display:none;">
                <?php
                $this->renderView();
                ?>
        </div>
    </body>
</html>
<?php ob_end_flush(); ?>
