var showVimeoThumb = function(data){
    var id_img = "#vimeo-" + data[0].id;
    $(id_img).attr('src',data[0].thumbnail_medium);
};

var loadVimeoImage = function(videoId, imgSelector){
    var vimeoApiUrl = "//vimeo.com/api/v2/video/" + videoId + ".json?callback=showVimeoThumb";
    var script = document.createElement( 'script' );
    script.src = vimeoApiUrl;
    $(imgSelector).before(script);
};

jQuery(document).ready(function($){
	var image_gallery_frame;
	var $image_gallery_ids = $('#image_gallery');
	var $gallery_items = $('.gallery-container');

	$('.add-gallery-image').click(function( event ) {

        var $el = $(this);
        var attachment_ids = $image_gallery_ids.val();


        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( image_gallery_frame ) {
            image_gallery_frame.open();
            return;
        }

        // Create the media frame.
        image_gallery_frame = wp.media.frames.downloadable_file = wp.media({
            // Set the title of the modal.
            title: 'Add Images to Gallery',
            button: {
                text: 'Add to gallery'
            },
            multiple: true
        });

        // When an image is selected, run a callback.
        image_gallery_frame.on( 'select', function() {

            var selection = image_gallery_frame.state().get('selection');

            selection.map( function( attachment ) {

                attachment = attachment.toJSON();

                if ( attachment.id ) {
                    attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;
                    $gallery_items.append('\
                        <div class="image attachment details" data-type="image" data-attachment_id="' + attachment.id + '" data-title="' + attachment.title + '" data-attachment_url="' + attachment.url + '">\
                            <div class="thumbnail">\
                                <img src="' + attachment.url + '" />\
                            </div>\
                            <a href="#" class="show check" title="Show"><span class="dashicons dashicons-search"></span></a>\
                            <a href="#" class="edit check" title="Edit title"><span class="dashicons dashicons-welcome-write-blog"></span></a>\
                            <a href="#" class="delete check" title="Remove video"><span class="dashicons dashicons-trash"></span></a>\
                            <span class="dashicons dashicons-format-image type-icon"></span> \
                        </div>');
                }

            } );

            //refreshData();
            $image_gallery_ids.val( attachment_ids );
        });

        // Finally, open the modal.
        image_gallery_frame.open();
    });

    $('.add-gallery-video').click( function(event){
        var $el = $(this);

        event.preventDefault();

        var video_gallery_frame = tb_show('<h1>Add new video to Gallery</h1>', '#TB_inline?width=500&height=300&inlineId=add_gallery_videos_modal');
        $("#TB_title").css('height','60px');
        $('#TB_window :input.url, #TB_window :input.title').val("");
        $('#TB_window :input.url').prop("required", true);

        return false;
    });

    $('.gallery-setting').click( function(event){
        var $el = $(this);

        event.preventDefault();

        var video_gallery_frame = tb_show('<h1>Gallery Settings</h1>', '#TB_inline?width=600&height=300&inlineId=gallery_settings_modal');
        $("#TB_title").css('height','60px');
        
        return false;
    });

    var parseYoutubeUrl = function(url) {
        var ytRegExp = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var ytMatch = url.val().match(ytRegExp);

        if(!(ytMatch && ytMatch[2] && ytMatch[2].length == 11)) {
            return false;
        }

        return {
            ref: ytMatch[2],
            thumbnail: '<img src="//img.youtube.com/vi/'+ytMatch[2]+'/hqdefault.jpg" />',
            provider: 'youtube'
        }
    }

    var parseVimeoUrl = function(url) {
        var vimeoRegExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
        var vimeoMatch = url.val().match(vimeoRegExp);

        if(!(vimeoMatch && vimeoMatch[5])){
            return false;
        }

        return {
            ref: vimeoMatch[5],
            thumbnail: '<img id="vimeo-'+ vimeoMatch[5] +'" />',
            provider: 'vimeo'
        }
    }

    var parseDailyMotionUrl = function(url) {
        var dailyMotionRegExp = /^.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/;
        var dailyMotionMatch = url.val().match(dailyMotionRegExp);
        var dailyMotionMatchResult;
        if (dailyMotionMatch !== null) {
            if(dailyMotionMatch[4] !== undefined) {
                dailyMotionMatchResult = dailyMotionMatch[4];
            } else {
                dailyMotionMatchResult = dailyMotionMatch[2];
            }
        }
        if(!dailyMotionMatchResult){
            return false;
        }

        return {
            ref: dailyMotionMatchResult,
            thumbnail: '<img src="//www.dailymotion.com/thumbnail/video/'+dailyMotionMatchResult+'" />',
            provider: 'dailymotion'
        }
    }

    var parseFacebookUrl = function(url) {
        var facebookRegExp1 = /^.+facebook.com\/(.+)\/videos\/([0-9]+)/;
        var facebookMatch1 = url.val().match(facebookRegExp1);

        var ref;

        if (facebookMatch1 && facebookMatch1[2]){
            ref = facebookMatch1[2];
        } else {
            var facebookRegExp2 = /^.+facebook.com\/watch\/\?v=([0-9]+)/;
            var facebookMatch2 = url.val().match(facebookRegExp2);

            if (facebookMatch2 && facebookMatch2[1]){
                ref = facebookMatch2[1];
            } else {
                return false;
            }
        }

        return {
            ref: ref,
            thumbnail: '<img src="//graph.facebook.com/'+ref+'/picture" />',
            provider: 'facebook'
        }
    }

    var parseUrl = function(url) {
        var result

        result = parseYoutubeUrl(url);
        if (result) {
            return result
        }

        result = parseVimeoUrl(url)
        if (result) {
            return result
        }

        result = parseDailyMotionUrl(url)
        if (result) {
            return result
        }

        result = parseFacebookUrl(url)
        if (result) {
            return result
        }
    }

    $(document).on('click', '#TB_window :input[name="add_video"]', function(e){
        var url = $('#TB_window :input.url');

        if(!url.get(0).validity.valid){
            return false;
        }

        var title = $('#TB_window :input.title');

        var videoData = parseUrl(url)

        if(!videoData){
            return false;
        }

        $gallery_items.append('\
            <div class="image attachment details" data-type="video" data-ref="'+videoData.ref+'" data-provider="'+videoData.provider+'" data-title="'+title.val()+'">\
                <div class="thumbnail">\
                    '+videoData.thumbnail+'\
                </div>\
                <a href="#" class="show check" title="Show"><span class="dashicons dashicons-search"></span></a>\
                <a href="#" class="edit check" title="Edit title"><span class="dashicons dashicons-welcome-write-blog"></span></a>\
                <a href="#" class="delete check" title="Remove video"><span class="dashicons dashicons-trash"></span></a>\
                <span class="dashicons dashicons-video-alt2 type-icon"></span> \
            </div>');

        if (videoData.provider === 'vimeo') {
            loadVimeoImage(videoData.ref, '#vimeo-' + videoData.ref);
        }

        url.prop("required", false);
        tb_remove();
    });

    // Remove images
    $('.gallery-container').on( 'click', 'a.delete', function() {
        $(this).closest('div.image').remove();
        //refreshData();
        return false;
    } );

    // Edit title modal
    var currentElemToEdit;
    $('.gallery-container').on('click', 'a.edit', function(){
        currentElemToEdit = $(this).parents('div.image');
        var title = currentElemToEdit.data('title');

        var video_gallery_frame = tb_show('<h1>Edit title</h1>', '#TB_inline?width=500&height=200&inlineId=edit_title_modal');
        $("#TB_title").css('height','60px');

        var titleInput = $('#TB_window :input.title');
        titleInput.val(title);

    });

    $(document).on('click', '#TB_window :input[name="change_title"]', function(e){
        e.preventDefault();

        if(!currentElemToEdit){
            return false;
        }

        var titleInput = $('#TB_window :input.title');

        currentElemToEdit.data('title', titleInput.val());
        //refreshData();
        tb_remove();
        return false;
    });

    $(document).on('click', '#TB_window :input[name="save_setting"]', function(e){
        e.preventDefault();

        $.ajax({
            url: ajaxObj.ajaxurl,
            method: 'POST',
            data: {'action' : 'get_instagram_feed', 'username':'natashawilona12'},
            success: function(result){
                console.log(result);
            }
        })
    });

    // Show modal
    $('.gallery-container').on('click', 'a.show', function(){
        var elem = $(this).parents('div.image');
        var title = elem.data('title');
        var type = elem.data('type');
        if(!title && type == 'image'){
            title = "Untitled image";
        }else if(!title && type == 'video'){
            title = "Untitled video";
        }

        var video_gallery_frame = tb_show('<h1>' + title + '</h1>', '#TB_inline?inlineId=show_modal');
        var contentElem = $("#TB_ajaxContent");
        contentElem.css('width', 'auto');
        contentElem.css('height', 'auto');
        $("#TB_title").css('height','60px');

        if(type == 'image'){
            contentElem.html('<img src="' + elem.data('attachment_url') + '" class="image-preview" />');
        }else if(type == 'video'){
            var provider = elem.data('provider');
            var ref = elem.data('ref');
            var url;
            var src;
            if(provider == 'youtube'){
                src = '//www.youtube.com/embed/' + ref;
                url = '//www.youtube.com/watch?v=' + ref;
            }else if(provider == 'vimeo'){
                src = '//player.vimeo.com/video/' + ref;
                url = '//vimeo.com/' + ref;
            }else if(provider == 'dailymotion'){
                src = '//www.dailymotion.com/embed/video/' + ref;
                url = '//www.dailymotion.com/video/' + ref;
            }else if (provider == 'facebook'){
                src = '//www.facebook.com/plugins/video.php?href=https://www.facebook.com/facebook/videos/' + ref;
                url = '//www.facebook.com/watch/?v=' + ref;
            }else{
                return;
            }

            var iframe = $('<iframe src="' + src + '" class="video-preview" />');
            iframe.height(contentElem.width() / 1.333333);
            contentElem.html(iframe);
            contentElem.append('<h3 class="label">Video URL:</h3><a target="_blank" href="' + url +'">http:' + url + '</a>');
        }


    });
});