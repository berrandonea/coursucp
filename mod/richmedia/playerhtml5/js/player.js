var quizPlayer = null;
if (typeof player !== 'undefined') {
    var quizPlayer = player;
}
var Player = {
    init: function (params, audioMode) {
        var that = this;
        this.video = document.querySelector('#video');
        this.volumeBar = document.querySelector('#volume-bar');
        this.duration = Math.round(this.video.duration);
        this.slides = [];
        this.richmediaid = parseInt(params.richmediaid);
        if (typeof params.time != 'undefined') {
            this.startTime = params.time;
        }

        if (typeof params.recovery != 'undefined') {
            this.recovery = params.recovery;
        }

        $.each(params.tabslides, function (index) {
            var pos = index + 1;
            this.framein = parseInt(this.framein);
            that.slides.push(this);
            $('#richmedia-summary table').append('<tr data-time="' + this.framein + '"><td class="index">' + pos + '</td><td class="title">' + this.slide + '</td><td class="time">' + Player.convertTime(this.framein) + '</td></tr>');
        });

        this.currentView;
        this.defaultview = params.defaultview;
        this.autoplay = params.autoplay;
        this.haspicture = params.haspicture;
        this.audioMode = audioMode;
        this.KEY_SPACE = 32;
        this.KEY_LEFT = 37;
        this.KEY_RIGHT = 39;
        this.locked = true;
        this.$symQuizPlayer = $('#symquiz_player');
        this.$subtitles = $('#subtitles');
        this.$cuePlayer = $('#cuePlayer');
        this.$contentContainer = $('#richmedia-content-container');
        this.$video = $('#video');
        this.$text = $('#text');
        this.$richmedia = $('#richmedia');
        this.richmediaWidth = this.$richmedia.width();
        this.ratioWidth = 0;
        this.ratioHeight = 0;
        this.imgWidth = 0;
        this.imgHeight = 0;

        this.initHTML(params);

        this.initStrings();

        this.preloadPlayerImages();

        this.resizePlayer();

        this.cuePlayerStyle = this.getStyleObject(this.$cuePlayer);
        this.subtitlesStyle = this.getStyleObject(this.$subtitles);
        this.contentContainerStyle = this.getStyleObject(this.$contentContainer);
        this.textStyle = this.getStyleObject(this.$text);
        this.videoStyle = this.getStyleObject(this.$video);

        this.initButtons();

        this.initKeyboard();

        this.$subtitles.draggable({appendTo: '#richmedia-content', containment: '#richmedia-fullcontent'}).draggable("enable");
        this.$cuePlayer.draggable({appendTo: '#richmedia-content', containment: '#richmedia-fullcontent'}).draggable("enable");

        cuepoint.init(that.slides, this.defaultview);

        // check video mode
        if ($.isEmptyObject(this.slides)) {
            $('#next, #list').hide();
        }
        if ($.isEmptyObject(this.slides) || this.haspicture == 0) {
            this.videoDisplay();
            $('#selectview, #closed').hide();
        }

        this.initDialogs();

        this.initProgressBar();

        this.initEvents();

        if (quizPlayer) {
            quizPlayer.init(false);
        }

        if (this.autoplay == 1) {
            this.video.play();
        }
        if (typeof (params.subtitles) !== 'undefined') {
            $('#srt').show();
            $('.srt').data('srt', params.subtitles);
            srtPlayer.init();
        }
    },
    initEvents: function () {
        var that = this;

        this.$subtitles.bind("DOMNodeInserted", function () {
            if (that.currentView == 2) {
                that.centerImg();
            }
            if ($.isEmptyObject(that.slides) || that.haspicture == 0) {
                that.$subtitles.hide();
            }
        });

        //CHROME
        this.video.ondurationchange = function () {
            that.duration = video.duration;
            that.initProgressBar();
        };

        //IOS
        this.video.addEventListener('loadedmetadata', function () {
            that.duration = this.duration;
            that.initProgressBar();
        });

        this.volumeBar.addEventListener("change", function () {
            that.video.volume = this.value;
        });

        this.video.addEventListener("ended", function () {
            if (quizPlayer) {
                quizPlayer.endQuiz();
                cuepoint.setQuiz();
            }
        });

        window.onresize = function (event) {
            var fullscreen = document.webkitIsFullScreen || document.mozFullScreen;
            if (!fullscreen) {
                that.resizePlayer();
                that.initProgressBar();
            }
        };
        window.onbeforeunload = function (event) {
            $.ajax({
                url: M.cfg.wwwroot + '/mod/richmedia/close.php',
                data: {richmediaid: that.richmediaid, time: Math.round(cuepoint.currentTime())},
                async: false
            });
        };

        this.video.addEventListener("loadeddata", function () {
            if (typeof that.recovery != 'undefined' && typeof that.startTime != 'undefined') {
                if (that.recovery == 0) {
                    this.currentTime = 0;
                }
                else if (that.recovery == 1) {
                    var video = this;
                    $.each(that.slides, function (index) {
                        var nextFramein;
                        if (typeof that.slides[index + 1] != 'undefined') {
                            nextFramein = that.slides[index + 1].framein;
                        }
                        else {
                            nextFramein = that.duration;
                        }
                        if (that.startTime >= this.framein && that.startTime <= nextFramein) {
                            video.currentTime = this.framein;
                            return false;
                        }
                    });
                }
                else if (that.recovery == 2) {
                    this.currentTime = that.startTime;
                }
            }
        });

        $(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', function () {
            that.resizePlayer();
            if (that.currentView > 1) {
                that.contentFullscreen();
            }
            that.initProgressBar();
            that.changeDisplay(that.currentView, true);
        });
    },
    initStrings: function () {
        this.str = [];
        if (typeof M != 'undefined') {
            this.str['close'] = M.util.get_string('close', 'mod_richmedia');
            this.str['summary'] = M.util.get_string('summary', 'mod_richmedia');
            this.str['prev'] = M.util.get_string('prev', 'mod_richmedia');
            this.str['next'] = M.util.get_string('next', 'mod_richmedia');
            this.str['srt'] = M.util.get_string('srt', 'mod_richmedia');
            this.str['display'] = M.util.get_string('display', 'mod_richmedia');
            this.str['tile'] = M.util.get_string('tile', 'mod_richmedia');
            this.str['slide'] = M.util.get_string('slide', 'mod_richmedia');
            this.str['video'] = M.util.get_string('video', 'mod_richmedia');
        }
        else {
            this.str['close'] = 'Close';
            this.str['summary'] = 'Summary';
            this.str['prev'] = 'Previous';
            this.str['next'] = 'Next';
            this.str['srt'] = 'Subtitles';
            this.str['display'] = 'Display';
            this.str['tile'] = 'Tile';
            this.str['slide'] = 'Slide';
            this.str['video'] = 'Video';
        }
        $('#list').attr('title', this.str['summary']);
        $('#richmedia-summary').attr('title', this.str['summary']);
        $('#prev').attr('title', this.str['prev']);
        $('#next').attr('title', this.str['next']);
        $('#srt').attr('title', this.str['srt']);
        $('#selectview option:first').html(this.str['display']);
        $('#selectview option').eq(1).html(this.str['tile']);
        $('#selectview option').eq(2).html(this.str['slide']);
        $('#selectview option').eq(3).html(this.str['video']);
    },
    initHTML: function (params) {
        var extension = params.filevideo.split('.').pop().split(/\#|\?/)[0];
        if (this.audioMode) {
            $('#video').html('<source src="' + params.filevideo + '" type="audio/' + extension + '"></source>');
        }
        else {
            $('#video').html('<source src="' + params.filevideo + '" type="video/' + extension + '"></source>');
        }

        $('#presentername').html(params.presentername);
        $('#presenterbio').html(params.presenterbio);

        $('#richmedia-title').html(params.title).css({
            'color': '#' + params.fontcolor
        });

        $('#richmedia-fullcontent').css({
            'font-family': params.font,
            'background-image': 'url("themes/' + params.background + '")'
        });
        if (params.logo) {
            $('#richmedia-logo').attr('src', 'themes/' + params.logo);
        }
    },
    preloadPlayerImages: function () {
        var that = this;
        var spinner = '<div class="spinner"><div class="spinner-container container1"><div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div></div><div class="spinner-container container2"><div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div></div><div class="spinner-container container3"><div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div></div></div>';
        $('.loading').append(spinner);

        var sources = [];
        for (s in this.slides) {
            var src = this.slides[s].src;
            if (src) {
                if (typeof (src) == 'string' && src.substring(src.length - 1) != '/') {
                    sources.push(src);
                }
            }
        }
        if (typeof M != 'undefined') {
            var imgRep = M.cfg.wwwroot + '/mod/richmedia/playerhtml5/pix/';
        }
        else {
            var imgRep = 'playerhtml5/pix/';
        }
        var icons = [
            'delimiter.png',
            'delimiter_question.png',
            'richmedia_bt_spritesheet.png'
        ];
        
        $.each(icons, function () {
            sources.push(imgRep + this);
        });
        
        this.preloadImages(sources).done(function () {
            $('.loading').css('display', 'none');
            that.$richmedia.css('display', 'block');
        });
    },
    preloadImages: function (arr) {
        var that = this;
        for (var i = 0; i < arr.length; i++) {
            if (typeof (arr[i]) != 'string') {
                arr.splice(i, 1);
            }
        }
        var newimages = [], loadedimages = 0;
        var postaction = function () {};
        var arr = (typeof arr != "object") ? [arr] : arr;
        function imageloadpost() {
            loadedimages++;
            if (loadedimages == arr.length) {
                postaction(newimages);
            }
        }
        for (var i = 0; i < arr.length; i++) {
            newimages[i] = new Image();
            newimages[i].src = arr[i];
            newimages[i].onload = function () {
                if ((that.ratioWidth == 0) && (this.width > 180)) {
                    that.ratioWidth  = this.width / this.height;
                    that.ratioHeight = this.height / this.width;
                    that.imgWidth = this.width;
                    that.imgHeight = this.height;
                }
                imageloadpost();
            };
            newimages[i].onerror = function () {
                imageloadpost();
            };
        }
        return {
            done: function (f) {
                postaction = f || postaction;
            }
        };
    },
    isInt: function (input) {
        return typeof (input) == 'number' && parseInt(input) == input;
    },
    convertTime: function (nbsecondes) {
        var temp = nbsecondes % 3600;
        var time2 = temp % 60;
        var time1 = (temp - time2) / 60;

        if (time1 == 0 || (this.isInt(time1) && time1 < 10)) {
            time1 = '0' + time1;
        }
        if (this.isInt(time2) && time2 < 10) {
            time2 = '0' + time2;
        }
        return time1 + ':' + time2;
    },
    changeDisplay: function (id, forced) {
        if (this.audioMode == 1)
            id = 2;
        else if (this.haspicture == 0) {
            id = 3;
        }
        if (forced || (this.currentView != id)) {
            this.currentView = id;
            if (id == 1) {
                this.defaultDisplay();
            }
            else if (id == 2) {
                this.slideDisplay();
            }
            else if (id == 3) {
                this.videoDisplay();
            }
            if (this.currentView != id) {
                this.checkThumbnail();
            }
        }
    },
    videoDisplay: function () {
        $('#thumbnail').show();
        this.$contentContainer.css('height', '100%');
        $('#head').hide();

        $('#richmedia-content').addClass('no-padding').height($('#richmedia-fullcontent').height());
        this.$cuePlayer.css({
            width: '100%',
            height: '100%',
            position: 'absolute',
            top: '0',
            left: '0',
            margin: '0',
            'z-index': '0'
        }).show();

        this.$video.css({
            'width': '100%',
            'height': this.$cuePlayer.css('height')
        });

        if (this.subtitlesStyle) {
            this.$subtitles.css(this.subtitlesStyle).css({
                position: 'relative',
                width: '25%',
                height: '25%',
                'margin-right': '0',
                'z-index': '100'
            });
            this.$subtitles.draggable({containment: "#richmedia-content-container"}).draggable("enable");
            $('#subtitles').show();
        }

        this.$text.hide();

        this.$cuePlayer.draggable({containment: "#richmedia-content-container"}).draggable("disable");

        this.centerVideo();
        this.centerImg();
    },
    slideDisplay: function () {
        $('#thumbnail').show();
        this.$contentContainer.css('height', '100%');
        $('#head').hide();
        $('#richmedia-content').addClass('no-padding').height($('#richmedia-fullcontent').height());
        this.$subtitles.css({
            width: '100%',
            height: '100%',
            'vertical-align': 'center',
            position: 'absolute',
            top: '0',
            left: '0',
            margin: '0',
            'z-index': '0',
            'background-color': '#000000'
        }).show();
        this.$subtitles.draggable("disable");

        this.$cuePlayer.css(this.cuePlayerStyle).css({
            width: '30%',
            position: 'absolute',
            'z-index': '100'
        });
        this.$video.css(this.videoStyle);
        if (this.audioMode == 1) {
            this.$cuePlayer.css('top', '425px').css('left', '340px');
        }
        else {
            this.$cuePlayer.css('top', '0');
        }
        this.$cuePlayer.draggable({containment: "#richmedia-content-container"}).draggable("enable");

        this.$text.hide();

        this.centerImg();
    },
    defaultDisplay: function () {
        $('#thumbnail').hide();
        $('#left').show();
        this.$cuePlayer.css(this.cuePlayerStyle).show();

        var subtitlesWidth = this.richmediaWidth * 0.63;
        this.$subtitles.css(this.subtitlesStyle).css({
            'width': subtitlesWidth,
            'margin-top': 0,
            'height': $('#left').height()
        }).show();
        this.centerImg();
        $('#subtitles img').css('margin-top', 0);

        this.$video.css(this.videoStyle);
        this.$contentContainer.css(this.contentContainerStyle);
        this.$text.css(this.textStyle).css('position', 'relative').show();

        this.$subtitles.draggable("disable");
        this.$cuePlayer.draggable("disable");
        $('#head').show();
        var padding = this.$richmedia.width() * 0.04;
        var headHeight = $('#richmedia-fullcontent').height() * 0.126;
        var height = $('#richmedia-fullcontent').height() - headHeight - padding;
        $('#richmedia-content').removeClass('no-padding').css('padding-top', '4%').height(height + 'px');
    },
    prev: function () {
        var previndex;
        if (cuepoint.currentSlide) {
            if ((cuepoint.currentTime() - cuepoint.currentSlide.framein > 1)) {
                previndex = cuepoint.slides.indexOf(cuepoint.currentSlide);
            }
            else {
                previndex = cuepoint.slides.indexOf(cuepoint.currentSlide) - 1;
            }
            var prev = Math.max(previndex, 0);
            cuepoint.setTime(cuepoint.slides[prev].framein);
        }
        else {
            cuepoint.setTime(0);
        }
    },
    next: function () {
        if (cuepoint.currentSlide) {
            var next = Math.min(cuepoint.slides.indexOf(cuepoint.currentSlide) + 1, cuepoint.slides.length - 1);
            cuepoint.setTime(cuepoint.slides[next].framein);
        }
    },
    playControl: function () {
        if (this.video.paused == false) {
            this.pauseVideo();
        } else {
            this.playVideo();
        }
    },
    checkEventObj: function (_event_) {
        if (window.event)
            return window.event;
        else
            return _event_;
    },
    playVideo: function () {
        cuepoint.play();
    },
    pauseVideo: function () {
        cuepoint.pause();
    },
    showCredits: function () {
        var $copyright = $("#richmedia-copyright");
        if (!$copyright.dialog("isOpen")) {
            $copyright.dialog("open");
        }
        else {
            $copyright.dialog("close");
        }
    },
    displaySlides: function () {
        var $summary = $("#richmedia-summary");
        if (!$summary.dialog("isOpen")) {
            $('.ui-dialog, .ui-dialog *').css('overflow', 'hidden');
            $summary.dialog("open");
        }
        else {
            $summary.dialog("close");
        }
    },
    getStyleObject: function (elem) {
        var dom = elem.get(0);
        var style;
        var returns = {};
        if (window.getComputedStyle) {
            var camelize = function (a, b) {
                return b.toUpperCase();
            };
            style = window.getComputedStyle(dom, null);
            for (var i = 0, l = style.length; i < l; i++) {
                var prop = style[i];
                var camel = prop.replace(/\-([a-z])/g, camelize);
                var val = style.getPropertyValue(prop);
                returns[camel] = val;
            }
            return returns;
        }
        if (style = dom.currentStyle) {
            for (var prop in style) {
                returns[prop] = style[prop];
            }
            return returns;
        }
        return elem.css();
    },
    centerImg: function () {
        if (this.currentView == 2) {
            var subtitlesheight = $('#richmedia-content').height();
            var subtitleswidth = $('#richmedia').width();
        }
        else {
            var subtitlesheight = this.$subtitles.height();
            var subtitleswidth = this.$subtitles.width();
        }
        var $img = $('#subtitles img');
        if ($img.length > 0) {
            var imgWidth = $img.width();
            var imgHeight = $img.height();
            
            imgWidth = this.imgWidth != 0 ? this.imgWidth : subtitleswidth;
            imgHeight = this.imgHeight != 0 ? this.imgHeight : imgWidth * this.ratioHeight;

            if (this.ratioWidth == 0) {
                this.ratioWidth = imgWidth / imgHeight;
            }
            if (this.ratioHeight == 0) {
                this.ratioHeight = imgHeight / imgWidth;
            }

            if (imgHeight != 0) {
                while ((imgWidth > subtitleswidth) || (imgHeight > subtitlesheight)) {
                    if (imgWidth > subtitleswidth) {
                        imgWidth = subtitleswidth;
                        imgHeight = imgWidth * this.ratioHeight;
                    }

                    if (imgHeight > subtitlesheight) {
                        imgHeight = subtitlesheight;
                        imgWidth = imgHeight * this.ratioWidth;
                    }
                }

                $img.width(imgWidth);
                $img.height(imgHeight);
            }

            var marginTop = (subtitlesheight - $img.height()) / 2;
            $img.css('margin-top', marginTop);
        }
    },
    centerVideo: function () {
        var cueplayerheight = this.$cuePlayer.height();
        var marginTop = (cueplayerheight - this.$video.height()) / 2;
        this.$video.css('margin-top', marginTop);
    },
    resizePlayer: function () {
        var playerFullScreen = document.webkitIsFullScreen || document.mozFullScreen;
        var controlesHeight = $('#controles').height();
        if (playerFullScreen) {
            var height = $(document).height();
            if (height > 830) {
                height = 830;
            }
            var width = height * 1.5079;
        }
        else {
            var width = this.$richmedia.parent().width();
            var height = width * 0.6122 + controlesHeight;
            if (width > 1254) {
                width = 1254;
            }
        }
        var $fullcontent = $('#richmedia-fullcontent');

        this.$richmedia.css({
            width: width + 'px',
            height: height + 'px'
        });
        this.richmediaWidth = width;

        $('.loading').height(height);
        height = height - controlesHeight;
        $fullcontent.css({
            height: height + 'px',
            width: width + 'px'
        });
        $('#controles').width(width);

        height = height - (width * 0.12);
        $('#left').height(height);
        var textHeight = height - 200;
        this.$text.height(textHeight);

        height = this.$subtitles.width() * 0.75;
        if (height > $('#richmedia-content-container').height()) {
            height = $('#richmedia-content-container').height();
        }
        this.$subtitles.height(height);

        if ($.isEmptyObject(this.slides) || this.haspicture == 0) {
            $('#cuePlayer, #video').css({
                width: $fullcontent.css('width'),
                height: $fullcontent.css('height')
            });
        }

        if (playerFullScreen) {
            if (!this.isChrome()) {
                var screenHeight = $(window).height();
                var marginTop = (screenHeight - $fullcontent.height() - $('#controles').height()) / 2;
                $fullcontent.css('margin-top', marginTop + 'px');
            }
            else {
                $fullcontent.css('margin-top', 0);
                this.$richmedia.css('margin-top', 0);
            }
            $('#title').css('font-size', '31px');
        }
        else {
            $fullcontent.css('margin-top', 0);
            $('#title').css('font-size', '24px');
        }

    },
    initButtons: function () {
        var that = this;
        //buttons management	
        $('#playbutton').click(function () {
            that.playControl();
        });

        //clic on prev button
        $('#prev').click(function () {
            that.prev();
        });

        //clic on next button
        $('#next').click(function () {
            that.next();
        });

        //clic on lock button
        $('#closed').click(function () {
            that.locked = !that.locked;
            if (that.locked) {
                that.$richmedia.removeClass('locked');
                $('#selectview').attr('disabled', 'disabled');
            }
            else {
                that.$richmedia.addClass('locked');
                $('#selectview').removeAttr("disabled");
            }
        });

        $('#selectview').on('change', function () {
            that.changeDisplay($(this).val());
        });
        $('#credit').on('click', function () {
            that.showCredits();
        });
        $('#list').on('click', function () {
            that.displaySlides();
        });

        $('#srt').on('click', function () {
            if ($(this).hasClass('active')) {
                $('.srt').hide();
                $(this).removeClass('active');
            }
            else {
                $('.srt').show();
                $(this).addClass('active');
            }
        });

        $('#thumbnail').on('click', function () {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
            }
            else {
                $(this).addClass('active');
            }
            that.checkThumbnail();
        });

        $("#richmedia-summary tr").click(function () {
            var time = $(this).data('time');
            cuepoint.setTime(time);
            $("#richmedia-summary").dialog("close");
        });

        if (!this.fullScreenSupport()) {
            $('#fullscreen').hide();
        }
        else {
            $('#fullscreen').click(function () {
                var fullscreen = document.webkitIsFullScreen || document.mozFullScreen;
                if (fullscreen) {
                    that.exitFullscreen();
                }
                else {
                    that.setFullScreen(document.getElementById('richmedia'));
                }
            });
        }
    },
    initDialogs: function () {
        $("#richmedia-summary").dialog({
            autoOpen: false,
            appendTo: '#richmedia',
            resizable: false,
            draggable: false,
            closeText: this.str['close'],
            height: 350,
            width: 350,
            position: {my: "left bottom", at: "left bottom", of: '#progress-bar'},
            show: {
                effect: 'slide',
                complete: function () {
                    $('.ui-dialog, .ui-dialog *').css('overflow', 'auto');
                }
            },
            hide: 'slide'
        });

        $("#richmedia-copyright").dialog({
            autoOpen: false,
            appendTo: '#richmedia',
            resizable: false,
            draggable: false,
            closeText: this.str['close'],
            width: 390,
            position: {my: "center center", at: "center center", of: '#richmedia-fullcontent'}
        });
    },
    initProgressBar: function () {
        if ($.isNumeric(this.duration) || this.slides.length > 0) {
            $('.img-preview').remove();
            var that = this;
            var width = $('#richmedia-fullcontent').width();
            for (var s in this.slides) {
                s = parseInt(s);
                var delimiterImgUrl = 'playerhtml5/pix/delimiter.png';
                if ((s - 1 > 0) && this.slides[s - 1].question) {
                    delimiterImgUrl = 'playerhtml5/pix/delimiter_question.png';
                }
                var next = s + 1;
                var slide = this.slides[s];
                var posX = slide.framein * width / this.duration;
                var nextFrame = this.duration;
                if (typeof this.slides[next] != 'undefined') {
                    nextFrame = this.slides[next].framein;
                }
                var slideWidth = (nextFrame - slide.framein) * width / this.duration;
                $('<div class="img-preview" data-time="' + slide.framein + '" data-pos="' + s + '" title=""><img class="delimiter" src="' + delimiterImgUrl + '" title="' + that.convertTime(slide.framein) + '" data-pos="' + s + '"/></div>').appendTo('#progress-bar').css({
                    left: posX,
                    width: slideWidth
                }).tooltip({
                    content: function () {
                        var pos = $(this).data('pos');
                        if (that.haspicture == 1) {
                            return that.slides[pos].html + that.convertTime(that.slides[pos].framein);
                        }
                        else {
                            return that.slides[pos].slide + '<br />' + that.convertTime(that.slides[pos].framein);
                        }
                    },
                    position: {my: "bottom-20", at: "top left"}
                });
            }

            $('.img-preview').click(function () {
                cuepoint.setTime($(this).data('time'));
                $(this).tooltip("close");
            });

            $('#progress-bar').click(function (e) {
                var parentOffset = $(this).parent().offset();
                var relX = e.pageX - parentOffset.left;
                var width = $(this).width();
                var time = relX * that.duration / width;
                cuepoint.setTime(time);
            });
        }
    },
    updateProgressBar: function (time) {
        if ($.isNumeric(this.duration)) {
            var ratio = time / this.duration * 100;
            if (ratio > 100) {
                ratio = 100;
            }
            $('#progress').css('width', ratio + '%');
        }
    },
    initKeyboard: function () {
        var that = this;
        document.onkeydown = function (e) {
            var winObj = that.checkEventObj(e);
            var intKeyCode = winObj.keyCode;
            var $focused = $(':focus');
            if (!$focused.is('input[type="text"], textarea')) {
                if (intKeyCode === that.KEY_RIGHT) {
                    that.next();
                    return false;
                }
                else if (intKeyCode === that.KEY_LEFT) {
                    that.prev();
                    return false;
                }
                else if (intKeyCode === that.KEY_SPACE) {
                    that.playControl();
                    return false;
                }
            }
        };
    },
    displayQuestion: function (questionid) {
        var position = this.getQuestionPosition(questionid);
        quizPlayer.openQuestion(position);
    },
    getQuestionPosition: function (questionid) {
        var questions = quizPlayer.currentQuiz.getQuestions();
        for (var q in questions) {
            if (questions[q].id == questionid) {
                return parseInt(q);
            }
        }
    },
    fullScreenSupport: function () {
        var docElm = document.documentElement;
        return docElm.requestFullScreen || docElm.mozRequestFullScreen || docElm.webkitRequestFullScreen;
    },
    setFullScreen: function (element) {
        if (element.requestFullScreen) {
            element.requestFullScreen();
        }
        else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
        else if (element.webkitRequestFullScreen) {
            element.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else {
            alert('Not supported by your browser');
        }
    },
    exitFullscreen: function () {
        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        }
        else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        }
    },
    isIE: function () {
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf('MSIE ');
        var trident = ua.indexOf('Trident/');

        if (msie > 0) {
            return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
        }

        if (trident > 0) {
            var rv = ua.indexOf('rv:');
            return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
        }
        return false;
    },
    isChrome: function () {
        return navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
    },
    loadJsCssFile: function (filename, filetype) {
        if (filetype == "js") { //if filename is a external JavaScript file
            var fileref = document.createElement('script');
            fileref.setAttribute("type", "text/javascript");
            fileref.setAttribute("src", filename);
        }
        else if (filetype == "css") { //if filename is an external CSS file
            var fileref = document.createElement("link");
            fileref.setAttribute("rel", "stylesheet");
            fileref.setAttribute("type", "text/css");
            fileref.setAttribute("href", filename);
        }
        if (typeof fileref != "undefined") {
            document.getElementsByTagName("head")[0].appendChild(fileref);
        }
    },
    checkThumbnail: function () {
        var that = this;
        $thumbnail = $('#thumbnail');
        if ($thumbnail.hasClass('active')) {
            if (that.currentView == 2) {
                $('#left').hide();
                $('#subtitles').show();
            }
            else if (that.currentView == 3) {
                $('#subtitles').hide();
                $('#left').show();
            }
        }
        else {
            $('#left').show();
            $('#subtitles').show();
        }
    },
    contentFullscreen: function () {
        $('#richmedia-content').height($('#richmedia-fullcontent').height());
    }
};