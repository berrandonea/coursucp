var srtPlayer = {
    toSeconds : function(t) {
        var s = 0.0;
        if (t) {
            var p = t.split(':');
            for (i = 0; i < p.length; i++)
                s = s * 60 + parseFloat(p[i].replace(',', '.'));
        }
        return s;
    },
    playSubtitles : function(subtitleElement, subtitlesText) {
        var that = this;
        var videoId = subtitleElement.data('video');
        var subtitles = [];
        var srt = subtitlesText;
        srt = srt.replace(/\r\n|\r|\n/g, '\n');
        var srt_ = srt.split('\n');
        var result = [];
        result.push([]);
        for (var k in srt_) {
            if (srt_[k] == '') {
                result.push(new Array());
            }
            else {
                // finally, push the value in the last array
                result[result.length-1].push(srt_[k]);
            }
        }        
        for (s in result) {
            st = result[s];
            if (st.length >= 2) {
                var split = st[1].split(' --> ');
                n = st[0];
                i = split[0];
                o = split[1];
                t = st[2];
                if (st.length > 2) {
                    for (j = 3; j < st.length; j++)
                        t += '<br />' + st[j];
                }
                is = that.toSeconds(i);
                o = that.toSeconds(o);
                subtitles.push({is: is, o: o, t: t});
            }
        }
        console.log(subtitles);
        var currentSubtitle = -1;
        var ival = setInterval(function () {
            var currentTime = document.getElementById(videoId).currentTime;
            var subtitle = -1;
            for (s in subtitles) {
                if (subtitles[s].is > currentTime)
                    break
                subtitle = s;
            }
            if ((subtitle != -1) && subtitles[subtitle].is > 0) {
                if ((subtitle != currentSubtitle) && subtitles[subtitle].o > currentTime) {
                    subtitleElement.html(subtitles[subtitle].t);
                    currentSubtitle = subtitle;
                } else if (subtitles[subtitle].o < currentTime) {
                    subtitleElement.html('');
                }
            }
            else {
                subtitleElement.html('');
            }
        }, 100);
    },
    init : function() {
        var that = this;
        $('.srt').each(function () {
            var subtitleElement = $(this);
            var videoId = subtitleElement.data('video');
            if (!videoId)
                return;
            var srtUrl = subtitleElement.data('srt');
            if (srtUrl) {
                $(this).load(srtUrl, function (responseText, textStatus, req) {
                    that.playSubtitles(subtitleElement,responseText);
                });
            } else {
                that.playSubtitles(subtitleElement);
            }
        });
    }
};
