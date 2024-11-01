var PAYoutubeThumb = {
    showVideo : function(target,width,height) {
        var popupMaxWidth = screen.width, popupMaxHeight = screen.height;
        if(typeof(width) != 'number') {
            width = 425;
        }
        if(typeof(height) != 'number') {
            height = 344;
        }
        
        var url = $(target).attr('href');
        var obj =   '<object style="width: '+width+'px; height: '+height+'px;">'
                +       '<param name="movie" value="'+url+'?version=3&rel=1&egm=1&color1=0x000000&color2=0x1a1a1a&hd=1&cc_load_policy=1&iv_load_policy=3&feature=player_embedded">'
                +       '<param name="menu" value="false">'
                +       '<param name="wmode" value="opaque">'
                +       '<param name="allowFullScreen" value="false">'
                +       '<param name="allowScriptAccess" value="always">'
                +       '<embed '
                +           'src="'+url+'?version=3&rel=1&egm=1&color1=0x000000&color2=0x1a1a1a&hd=1&cc_load_policy=1&iv_load_policy=3&feature=player_embedded" '
                +           'type="application/x-shockwave-flash" '
                +           'allowfullscreen="false" '
                +           'menu="false" '
                +           'wmode="opaque" '
                +           'allowScriptAccess="always" '
                +           'width="'+width+'" '
                +           'height="'+height+'">'
                +   '</object>';

        $.colorbox({
            innerWidth: width,
            innerHeight: height,
            maxWidth: popupMaxWidth,
            maxHeight: popupMaxHeight,
            opacity:0.7,
            slideshow:false,
            html: obj
        });
        $('#cboxContent').find('#cboxTitle').remove();
        return false;
    }
};

jQuery(document).ready(function($) {
    $('.pa-youtubethumb-list').find('img').each(function () {
        $(this).fadeTo(300, 0.7);
    });
    $('.pa-youtubethumb-list img').hover(
        function(){ 
            $(this).stop().fadeTo(300, 1);
        },
        function(){ 
            $(this).stop().fadeTo(300, 0.7);
        }
    );
});