function FoToolTip() {
    var tt_id="fotooltip";
    var tt_bgcolor="#bf3503";
    var tt_bordercolor="#bf3503";
    this.init=function (obj,text) {
        tt=getInstance();
        tt.style.display='block';
        obj.onmousemove=mouseMouveEx;
        obj.onmouseout=hide;
        loadcontent(text);
    }
    function loadcontent(text) {
        tt=getInstance();
        if (text.substr(0,4)!='http') {
            tt.innerHTML=text;
        } else {
            tt.innerHTML='Chargement en cours ...';
        }
    }
    function hide() {
        tt=getInstance();
        tt.style.display='none';
    }
    function mouseMouveEx(e) {
        var pos=getPos(e);
        tt=getInstance();
        tt.style.left=(pos.x+20)+"px";
        tt.style.top=pos.y+"px";
    }
    function getInstance() {
        var tt=document.getElementById(tt_id);
        if (!tt) {
            tt=document.createElement("div");
            tt.style.position="absolute";
            tt.style.backgroundColor=tt_bgcolor;
            tt.style.border='1px solid '+tt_bordercolor;
            tt.style.padding="5px";
            tt.id=tt_id;
            document.body.appendChild(tt);
            console.debug("create new foToolTip");
            return tt;
        } else {
            return tt;
        }
    }
    function getPos(e) {
        e = e || window.event;
        var cursor = {
            x:0,
            y:0
        };
        if (e.pageX || e.pageY) {
            cursor.x = e.pageX;
            cursor.y = e.pageY;
        }
        else {
            cursor.x = e.clientX
            (document.documentElement.scrollLeft ||
                document.body.scrollLeft) -
            document.documentElement.clientLeft;
            cursor.y = e.clientY
            (document.documentElement.scrollTop ||
                document.body.scrollTop) -
            document.documentElement.clientTop;
        }
        return cursor;
    }
}
FoToolTip.show=function(obj,text) {
    var foToolTip=new FoToolTip();
    foToolTip.init(obj, text);
};