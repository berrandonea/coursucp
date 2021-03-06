/**
 * HTML5 richmedia player synchro manager
 * Author:
 * 	Adrien Jamot  (adrien_jamot [at] symetrix [dt] fr)
 * 
 * @package   mod_richmedia
 * @copyright 2011 Symetrix
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

(function() {
    var Cuepoint;
    Cuepoint = (function() {
        function Cuepoint() {
            this.nativeKeys = Object.keys;
        }
        Cuepoint.prototype = {
            init: function(slides, defaultview) {
                this.slides = slides;
                this.currentSlide = null;
                this.defaultview = defaultview;
                this.video = document.getElementById("video");
                this.inQuiz = false;
                var that = this;
                this.video.addEventListener("timeupdate", function() {
                    var video = this;
                    Player.updateProgressBar(this.currentTime);
                    $.each(slides,function(index){
                        var nextSlide = slides[index + 1] || null;
                        if (video.currentTime >= this.framein && (nextSlide && video.currentTime < parseInt(nextSlide.framein) || !nextSlide)) {
                            return that.update(this);
                        }
                    });
                }, false);
            },
            currentTime: function() {
                return this.video.currentTime;
            },
            update: function(slide) {
                if (slide != this.currentSlide && !this.inQuiz) {
                    if (!Player.$symQuizPlayer.is(':visible') && this.currentSlide && $.isNumeric(this.currentSlide.question) && (slide.framein > this.currentSlide.framein) && ((this.currentTime() <= (slide.framein + 0.3)) || (this.currentTime >= (slide.framein - 0.3)))) {
                        this.setQuiz();
                    }
                    else {
                        this.currentSlide = slide;
                        this.inQuiz = false;
                        this.setSubtitles(slide);
                    }
                }
            },
            setQuiz: function() {
                var that = this;
                this.inQuiz = true;
                this.pause();
                Player.changeDisplay(1);
                Player.$symQuizPlayer.detach();
                Player.$subtitles.html('').append(Player.$symQuizPlayer);
                Player.$symQuizPlayer.show();
                Player.displayQuestion(this.currentSlide.question);
                Player.$symQuizPlayer.unbind('submit').submit(function() {
                    quizPlayer.validateQuestion();
                    that.inQuiz = false;
                    that.play();
                    return false;
                });
            },
            setSubtitles: function(slide) {
                var view;
                if (!Player.locked){
                    view = Player.currentView;
                }
                else if (!$.isNumeric(slide.view)) {
                    view = this.defaultview;
                }
                else {
                    view = slide.view;
                }
                /*Player.$subtitles.fadeOut(200,function(){
                    $(this).html(slide.html).fadeIn(200,function(){
                        Player.changeDisplay(view,1);
                    });
                });*/
                Player.$subtitles.html(slide.html);
                Player.changeDisplay(view,1);
            },
            setTime: function(time) {
                this.inQuiz = false;
                this.video.currentTime = time;
                return this.video.play();
            },
            play: function() {
                $('#richmedia').addClass('play');
                return this.video.play();
            },
            pause: function() {
                if (!this.video.paused) {
                    $('#richmedia').removeClass('play');
                    return this.video.pause();
                }
            }
        };
        return Cuepoint;
    })();
    window.cuepoint = new Cuepoint;
}).call(this);
