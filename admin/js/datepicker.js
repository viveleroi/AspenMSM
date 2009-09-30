/*
        DatePicker v4.4 by frequency-decoder.com

        Released under a creative commons Attribution-ShareAlike 2.5 license (http://creativecommons.org/licenses/by-sa/2.5/)

        Please credit frequency-decoder in any derivative work - thanks.
        
        You are free:

        * to copy, distribute, display, and perform the work
        * to make derivative works
        * to make commercial use of the work

        Under the following conditions:

                by Attribution.
                --------------
                You must attribute the work in the manner specified by the author or licensor.

                sa
                --
                Share Alike. If you alter, transform, or build upon this work, you may distribute the resulting work only under a license identical to this one.

        * For any reuse or distribution, you must make clear to others the license terms of this work.
        * Any of these conditions can be waived if you get permission from the copyright holder.
*/

var datePickerController = (function datePickerController() {
        var languageInfo        = navigator.language ? navigator.language.toLowerCase().replace(/-[a-z]+$/, "") : navigator.userLanguage ? navigator.userLanguage.toLowerCase().replace(/-[a-z]+$/, "") : "en",
            datePickers         = {},
            uniqueId            = 0,
            weeksInYearCache    = {},
            localeImport        = false,
            nbsp                = String.fromCharCode(160),
            nodrag              = false,
            splitAppend         = ["-dd","-mm"],
            formatMasks         = ["Y-sl-m-sl-d","m-sl-d-sl-Y","d-sl-m-sl-Y","Y-ds-m-ds-d","m-ds-d-ds-Y","d-ds-m-ds-Y"];                
        
        void function() {
                var scriptFiles = document.getElementsByTagName('head')[0].getElementsByTagName('script'),                    
                    scriptInner = scriptFiles[scriptFiles.length - 1].innerHTML.replace(/[\n\r\s\t]+/g, " ").replace(/^\s+/, "").replace(/\s+$/, ""),                    
                    json        = parseJSON(scriptInner);                
               
                if(typeof json === "object" && !("err" in json)) {                          
                        affectJSON(json);
                };
       
                if(typeof(fdLocale) != "object" && languageInfo != "en") {
                        var loc    = scriptFiles[scriptFiles.length - 1].src.substr(0, scriptFiles[scriptFiles.length - 1].src.lastIndexOf("/")) + "/lang/" + languageInfo + ".js",
                            script = document.createElement('script');
                                                          
                        script.type = "text/javascript";                         
                        script.src  = loc;
                        script.setAttribute("charset", "utf-8");
                        /*@cc_on
                        /*@if(@_win32)
                        var bases = document.getElementsByTagName('base');
                        if (bases.length && bases[0].childNodes.length) {
                                bases[0].appendChild(script);
                        } else {
                                document.getElementsByTagName('head')[0].appendChild(script);
                        };
                        bases = null;
                        @else @*/
                        document.getElementsByTagName('head')[0].appendChild(script);
                        /*@end
                        @*/    
                        
                        script = null;                      
                };                              
        }();
        
        function affectJSON(json) {
                if(typeof json !== "object") { return; };
                for(key in json) {
                        value = json[key];                                                                
                        switch(key.toLower()) { 
                                case "lang":
                                        if(value.search(/^[a-z]{2}$/i) != -1) {
                                                languageInfo = value;
                                        };
                                        break;
                                case "split":                                                 
                                        if(typeof value === 'object') {
                                                if(value.length && value.length == 2) {                                                        
                                                        splitAppend = value;
                                                };
                                        }; 
                                case "formats":                                                 
                                        if(typeof value === 'object') {
                                                if(value.length) {
                                                        formatMasks = value;
                                                };
                                        };
                                        break;
                                case "nodrag":
                                        nodrag = !!value;                                                                                                                                         
                        };          
                };        
        };
                
        // Functions shared between the datePickerController object & the datePicker objects    
        function pad(value, length) { 
                length = length || 2; 
                return "0000".substr(0,length - Math.min(String(value).length, length)) + value; 
        };
        
        function addEvent(obj, type, fn) {
                if( obj.attachEvent ) {
                        obj["e"+type+fn] = fn;
                        obj[type+fn] = function(){obj["e"+type+fn]( window.event );};
                        obj.attachEvent( "on"+type, obj[type+fn] );
                } else {
                        obj.addEventListener( type, fn, true );
                };
        };
        
        function removeEvent(obj, type, fn) {
                try {
                        if( obj.detachEvent ) {
                                obj.detachEvent( "on"+type, obj[type+fn] );
                                obj[type+fn] = null;
                        } else {
                                obj.removeEventListener( type, fn, true );
                        };
                } catch(err) {};
        };   

        function stopEvent(e) {
                e = e || document.parentWindow.event;
                if(e.stopPropagation) {
                        e.stopPropagation();
                        e.preventDefault();
                };
                /*@cc_on
                @if(@_win32)
                e.cancelBubble = true;
                e.returnValue = false;
                @end
                @*/
                return false;
        };
        
        function parseJSON(str) {
                // Check we have a String
                if(typeof str !== 'string' || str == "") { return {}; };                 
                try {
                        // Does the Douglas Crockford JSON parser exist in the global scope?
                        if("JSON" in window && "parse" in window.JSON && typeof window.JSON.parse == "function") {                                               
                                return window.JSON.parse(str);  
                        // Genious code taken from: http://kentbrewster.com/badges/                                                      
                        } else if(/lang|split|formats|nodrag/.test(str.toLower())) {                                               
                                var f = Function(['var document,top,self,window,parent,Number,Date,Object,Function,',
                                        'Array,String,Math,RegExp,Image,ActiveXObject;',
                                        'return (' , str.replace(/<\!--.+-->/gim,'').replace(/\bfunction\b/g,'function­') , ');'].join(''));
                                return f();                          
                        };
                } catch (e) {                                 
                        return {"err":"Trouble parsing JSON object"};
                };
                return {};                                            
        };        

        // The datePicker object itself 
        function datePicker(options) {                                      
                this.dateSet             = null;                 
                this.timerSet            = false;
                this.visible             = false;
                this.fadeTimer           = null;
                this.timer               = null;
                this.yearInc             = 0;
                this.monthInc            = 0;
                this.dayInc              = 0;
                this.mx                  = 0;
                this.my                  = 0;
                this.x                   = 0;
                this.y                   = 0;                  
                this.date                = new Date();
                this.defaults            = {};
                this.created             = false;
                this.id                  = options.id;
                this.opacity             = 0;          
                this.firstDayOfWeek      = 0; 
                this.buttonWrapper       = "buttonWrapper" in options ? options.buttonWrapper : false;                
                this.staticPos           = "staticPos" in options ? !!options.staticPos : false;
                this.disabledDays        = "disabledDays" in options && options.disabledDays.length ? options.disabledDays : [0,0,0,0,0,0,0];
                this.disabledDates       = "disabledDates" in options ? options.disabledDates : {};
                this.enabledDates        = "enabledDates" in options ? options.enabledDates : {};
                this.showWeeks           = "showWeeks" in options ? !!options.showWeeks : false;
                this.low                 = options.low || "";
                this.high                = options.high || "";
                this.dragDisabled        = nodrag ? true : ("dragDisabled" in options ? !!options.dragDisabled : false);
                this.positioned          = "positioned" in options ? options.positioned : false;
                this.hideInput           = this.staticPos ? false : "hideInput" in options ? !!options.hideInput : false;
                this.splitDate           = "splitDate" in options ? !!options.splitDate : false;
                this.format              = options.format || "d-sl-m-sl-Y";
                this.statusFormat        = options.statusFormat || "";
                this.highlightDays       = options.highlightDays && options.highlightDays.length ? options.highlightDays : [0,0,0,0,0,1,1];
                this.noFadeEffect        = "noFadeEffect" in options ? !!options.noFadeEffect : false;
                this.opacityTo           = this.noFadeEffect || this.staticPos ? 99 : 90;
                this.callbacks           = {};
                this.fillGrid            = !!options.fillGrid;
                this.constrainSelection  = this.fillGrid && !!options.constrainSelection;
                this.finalOpacity        = !this.staticPos && "finalOpacity" in options ? +options.finalOpacity : 90;                
                this.dynDisabledDates    = {};
                this.inUpdate            = false;
                /*@cc_on
                /*@if(@_win32)
                this.interval            = new Date();
                this.iePopUp             = null;
                /*@end@*/
                
                for(var thing in options.callbacks) {
                        this.callbacks[thing] = options.callbacks[thing];                 
                };
                
                this.date.setHours(12);              
          
                this.startDrag = function(e) {
                        e = e || document.parentWindow.event;
                        o.mx = e.pageX?e.pageX:e.clientX?e.clientX:e.x;
                        o.my = e.pageY?e.pageY:e.clientY?e.clientY:e.Y;
                        o.x = parseInt(o.div.style.left);
                        o.y = parseInt(o.div.style.top);
                        addEvent(document,'mousemove',o.trackDrag, false);
                        addEvent(document,'mouseup',o.stopDrag, false);
                        o.div.style.zIndex = 10000;
                        return stopEvent(e);
                };
                this.trackDrag = function(e) {
                        e = e || window.event;
                        var diffx = (e.pageX?e.pageX:e.clientX?e.clientX:e.x) - o.mx;
                        var diffy = (e.pageY?e.pageY:e.clientY?e.clientY:e.Y) - o.my;
                        o.div.style.left = Math.round(o.x + diffx) > 0 ? Math.round(o.x + diffx) + 'px' : "0px";
                        o.div.style.top  = Math.round(o.y + diffy) > 0 ? Math.round(o.y + diffy) + 'px' : "0px";
                        /*@cc_on
                        @if(@_jscript_version <= 5.6)
                        if(o.staticPos) return;
                        o.iePopUp.style.top    = o.div.style.top;
                        o.iePopUp.style.left   = o.div.style.left;
                        @end
                        @*/
                };
                this.stopDrag = function(e) {
                        removeEvent(document,'mousemove',o.trackDrag, false);
                        removeEvent(document,'mouseup',o.stopDrag, false);
                        o.div.style.zIndex = 9999;
                };
                this.changeHandler = function() {                        
                        o.setDateFromInput();                        
                        if(o.created) o.updateTable();
                };
                this.reposition = function() {
                        if(!o.created || !o.getElem() || o.staticPos) { return; };

                        o.div.style.visibility = "hidden";
                        o.div.style.left = o.div.style.top = "0px";
                        o.div.style.display = "block";

                        var osh         = o.div.offsetHeight,
                            osw         = o.div.offsetWidth,
                            elem        = document.getElementById('fd-but-' + o.id),
                            pos         = o.truePosition(elem),
                            trueBody    = (document.compatMode && document.compatMode!="BackCompat") ? document.documentElement : document.body,
                            scrollTop   = window.devicePixelRatio || window.opera ? 0 : trueBody.scrollTop,
                            scrollLeft  = window.devicePixelRatio || window.opera ? 0 : trueBody.scrollLeft;

                        o.div.style.visibility = "visible";

                        o.div.style.left  = Number(parseInt(trueBody.clientWidth+scrollLeft) < parseInt(osw+pos[0]) ? Math.abs(parseInt((trueBody.clientWidth+scrollLeft) - osw)) : pos[0]) + "px";
                        o.div.style.top   = Number(parseInt(trueBody.clientHeight+scrollTop) < parseInt(osh+pos[1]+elem.offsetHeight+2) ? Math.abs(parseInt(pos[1] - (osh + 2))) : Math.abs(parseInt(pos[1] + elem.offsetHeight + 2))) + "px";

                        /*@cc_on
                        @if(@_jscript_version <= 5.6)
                        o.iePopUp.style.top    = o.div.style.top;
                        o.iePopUp.style.left   = o.div.style.left;
                        o.iePopUp.style.width  = osw + "px";
                        o.iePopUp.style.height = (osh - 2) + "px";
                        @end
                        @*/
                }; 
                this.updateTable = function(noCallback) {  
                        if(o.inUpdate) return;
                         
                        o.inUpdate = true;
                        o.removeHighlight();
                                             
                        if(o.timerSet) {
                                o.date.setDate(Math.min(o.date.getDate()+o.dayInc, daysInMonth(o.date.getMonth()+o.monthInc,o.date.getFullYear()+o.yearInc)) );
                                o.date.setMonth(o.date.getMonth() + o.monthInc);
                                o.date.setFullYear(o.date.getFullYear() + o.yearInc);
                        }; 
        
                        o.outOfRange();
                        o.disableTodayButton();
                        o.showHideButtons(o.date);
                
                        var cd = o.date.getDate(),
                            cm = o.date.getMonth(),
                            cy = o.date.getFullYear(),
                            cursorDate = (String(cy) + pad(cm+1) + pad(cd)),
                            tmpDate    = new Date(cy, cm, 1);                      
                
                        tmpDate.setHours(5);
                        
                        var dt, cName, td, i, currentDate, cellAdded, col, currentStub, abbr, bespokeRenderClass,
                        weekDayC            = ( tmpDate.getDay() + 6 ) % 7,                
                        firstColIndex       = (((weekDayC - o.firstDayOfWeek) + 7 ) % 7) - 1,
                        dpm                 = daysInMonth(cm, cy),
                        today               = new Date(),
                        dateSetD            = (o.dateSet != null) ? o.dateSet.getFullYear() + pad(o.dateSet.getMonth()+1) + pad(o.dateSet.getDate()) : false,
                        stub                = String(tmpDate.getFullYear()) + pad(tmpDate.getMonth()+1),
                        cellAdded           = [4,4,4,4,4,4],                                                                   
                        lm                  = new Date(cy, cm-1, 1),
                        nm                  = new Date(cy, cm+1, 1),                          
                        daySub              = daysInMonth(lm.getMonth(), lm.getFullYear()),                
                        stubN               = String(nm.getFullYear()) + pad(nm.getMonth()+1),
                        stubP               = String(lm.getFullYear()) + pad(lm.getMonth()+1),                
                        weekDayN            = (nm.getDay() + 6) % 7,
                        weekDayP            = (lm.getDay() + 6) % 7,                                       
                        today               = today.getFullYear() + pad(today.getMonth()+1) + pad(today.getDate());
                        
                        o.firstDateShown    = !o.constrainSelection && o.fillGrid && (0 - firstColIndex < 1) ? String(stubP) + (daySub + (0 - firstColIndex)) : stub + "01";            
                        o.lastDateShown     = !o.constrainSelection && o.fillGrid ? stubN + pad(41 - firstColIndex - dpm) : stub + String(dpm);
                        o.currentYYYYMM     = stub;                    
                
                        bespokeRenderClass  = o.callback("redraw", {id:o.id, dd:pad(cd), mm:pad(cm+1), yyyy:cy, firstDateDisplayed:o.firstDateShown, lastDateDisplayed:o.lastDateShown}) || {};                    
                        o.dynDisabledDates  = o.getDisabledDates(cy, cm + 1);
                       
                        for(var curr = 0; curr < 42; curr++) {
                                row  = Math.floor(curr / 7);                         
                                td   = o.tds[curr];
                                
                                while(td.firstChild) td.removeChild(td.firstChild);
                                if((curr > firstColIndex && curr <= (firstColIndex + dpm)) || o.fillGrid) {
                                        currentStub     = stub;
                                        weekDay         = weekDayC;                                
                                        dt              = curr - firstColIndex;
                                        cName           = [];                                         
                                        selectable      = true;
                                
                                        if(dt < 1) {
                                                dt              = daySub + dt;
                                                currentStub     = stubP;
                                                weekDay         = weekDayP;                                        
                                                selectable      = !o.constrainSelection;
                                                cName.push("month-out");                                                  
                                        } else if(dt > dpm) {
                                                dt -= dpm;
                                                currentStub     = stubN;
                                                weekDay         = weekDayN;                                        
                                                selectable      = !o.constrainSelection; 
                                                cName.push("month-out");                                                                                           
                                        }; 
                                                                                                   
                                        td.appendChild(document.createTextNode(dt));
                                        currentDate = currentStub + String(dt < 10 ? "0" : "") + dt;                            
                                        
                                        if(o.low && +currentDate < +o.low || o.high && +currentDate > +o.high) {                                          
                                                td.className = "out-of-range";                                                
                                                if(o.showWeeks) { cellAdded[row] = Math.min(cellAdded[row], 2); };                                                  
                                        
                                        } else {                               
                                                if(selectable) {                                                 
                                                        cName.push("cd-" + currentDate + " yyyymm-" + currentStub + " mmdd-" + currentStub.substr(4,2) + pad(dt));
                                                } else {                                                    
                                                        cName.push("not-selectable yyyymm-" + currentStub + " mmdd-" + currentStub.substr(4,2) + pad(dt));
                                                };                                                                                                      
                                        
                                                weekDay = ( weekDay + dt + 6 ) % 7;

                                                if(currentDate == today) { cName.push("date-picker-today"); };

                                                if(dateSetD == currentDate) { cName.push("date-picker-selected-date"); };

                                                if(o.disabledDays[weekDay] || currentDate in o.dynDisabledDates) { cName.push("day-disabled"); }
                                        
                                                if(currentDate in bespokeRenderClass) { cName.push(bespokeRenderClass[currentDate]); }
                                        
                                                if(o.highlightDays[weekDay]) { cName.push("date-picker-highlight"); };

                                                if(cursorDate == currentDate) { td.id = o.id + "-date-picker-hover"; cName.push("date-picker-hover"); }
                                                else { td.id = ""; };
                                        
                                                td.className = cName.join(" ");

                                                if(o.showWeeks) {                                                         
                                                        cellAdded[row] = Math.min(cName[0] == "month-out" ? 3 : 1, cellAdded[row]);                                                          
                                                }; 
                                        };                       
                                } else {
                                        td.className = "date-picker-unused";
                                        td.id = "";                                         
                                        td.appendChild(document.createTextNode(nbsp));                                        
                                };                                 
                                
                                if(o.showWeeks && curr - (row * 7) == 6) { 
                                        while(o.wkThs[row].firstChild) o.wkThs[row].removeChild(o.wkThs[row].firstChild);                                         
                                        o.wkThs[row].appendChild(document.createTextNode(cellAdded[row] == 4 && !o.fillGrid ? nbsp : getWeekNumber(cy, cm, curr - firstColIndex - 6)));
                                        o.wkThs[row].className = "date-picker-week-header" + (["",""," out-of-range"," month-out",""][cellAdded[row]]);                                          
                                };                                
                        };

                        // Title Bar
                        var span = o.titleBar.getElementsByTagName("span");
                        while(span[0].firstChild) span[0].removeChild(span[0].firstChild);
                        while(span[1].firstChild) span[1].removeChild(span[1].firstChild);
                        span[0].appendChild(document.createTextNode(getMonthTranslation(cm, false) + nbsp));
                        span[1].appendChild(document.createTextNode(cy));
                        
                        if(o.timerSet) {
                                o.timerInc = 50 + Math.round(((o.timerInc - 50) / 1.8));
                                o.timer = window.setTimeout(o.updateTable, o.timerInc);
                        };
                        
                        o.inUpdate = false;                          
                };
                
                this.show = function() {
                        var elem = this.getElem();
                        if(!elem || this.visible || elem && elem.disabled) { return; };

                        if(!document.getElementById('fd-' + this.id)) {
                                this.created = false;
                                this.create();
                        } else {                           
                                this.setDateFromInput();
                                this.outOfRange();
                                this.updateTable();
                        };                         
                        
                        if(!this.staticPos) this.reposition();                        

                        addEvent(this.staticPos ? this.table : document, "mousedown", this.events.onmousedown);
                        this.opacityTo = this.finalOpacity;
                        this.div.style.display = "block";
                        if(!this.staticPos) {
                                /*@cc_on
                                @if(@_jscript_version <= 5.6)
                                this.iePopUp.style.width = this.div.offsetWidth + "px";
                                this.iePopUp.style.height = this.div.offsetHeight + "px";
                                this.iePopUp.style.display = "block";
                                @end
                                @*/
                                this.addKeyboardEvents();
                                this.fade();
                                var butt = document.getElementById('fd-but-' + this.id);
                                if(butt) butt.className = butt.className.replace("dp-button-active", "") + " dp-button-active";

                        } else {
                                this.opacity = this.opacityTo;
                        };
                };
                this.hide = function() {
                        if(!this.visible) return;
                        this.stopTimer();
                        if(this.staticPos) return;

                        var butt = document.getElementById('fd-but-' + this.id);
                        if(butt) butt.className = butt.className.replace("dp-button-active", "");
                
                        removeEvent(document, "mousedown", this.events.onmousedown);
                        removeEvent(document, "mouseup",  this.events.clearTimer);
                        this.removeKeyboardEvents();

                        /*@cc_on
                        @if(@_jscript_version <= 5.6)
                        this.iePopUp.style.display = "none";
                        @end
                        @*/

                        this.opacityTo = 0;
                        this.fade();
                        
                        //var elem = this.getElem();
                        //if(!elem.type || elem.type && elem.type != "hidden") { elem.focus(); };
                };
                this.destroy = function() {
                        // Cleanup for Internet Explorer
                        removeEvent(this.staticPos ? this.table : document, "mousedown", o.events.onmousedown);
                        removeEvent(document, "mouseup",   o.events.clearTimer);
                        o.removeKeyboardEvents();
                        clearTimeout(o.fadeTimer);
                        clearTimeout(o.timer);

                        /*@cc_on
                        @if(@_jscript_version <= 5.6)
                        if(!o.staticPos) {
                                o.iePopUp.parentNode.removeChild(o.iePopUp);
                                o.iePopUp = null;
                        };
                        @end
                        @*/

                        if(!this.staticPos && document.getElementById(this.id.replace(/^fd-/, 'fd-but-'))) {
                                var butt = document.getElementById(this.id.replace(/^fd-/, 'fd-but-'));
                                butt.onclick = butt.onpress = null;
                        };

                        if(this.div && this.div.parentNode) {
                                this.div.parentNode.removeChild(this.div);
                        };

                        o = null;
                };
                this.resizeInlineDiv = function()  {                        
                        o.div.style.width = o.table.offsetWidth + "px";
                };
                this.create = function() {
                        if(this.created) { return; };

                        function createTH(details) {
                                var th = document.createElement('th');
                                if(details.thClassName) th.className = details.thClassName;
                                if(details.colspan) {
                                        /*@cc_on
                                        /*@if (@_win32)
                                        th.setAttribute('colSpan',details.colspan);
                                        @else @*/
                                        th.setAttribute('colspan',details.colspan);
                                        /*@end
                                        @*/
                                };
                                /*@cc_on
                                /*@if (@_win32)
                                th.unselectable = "on";
                                /*@end@*/
                                return th;
                        };
                        function createThAndButton(tr, obj) {
                                for(var i = 0, details; details = obj[i]; i++) {
                                        var th = createTH(details);
                                        tr.appendChild(th);
                                        var but = document.createElement('span');
                                        but.className = details.className;
                                        but.id = o.id + details.id;
                                        but.appendChild(document.createTextNode(details.text || o.nbsp));
                                        but.title = details.title || "";
                                        if(details.onmousedown) but.onmousedown = details.onmousedown;
                                        if(details.onclick)     but.onclick     = details.onclick;
                                        if(details.onmouseout)  but.onmouseout  = details.onmouseout;
                                        /*@cc_on
                                        /*@if(@_win32)
                                        th.unselectable = but.unselectable = "on";
                                        /*@end@*/
                                        th.appendChild(but);
                                };
                        };

                        
                        this.div                     = document.createElement('div');
                        this.div.id                  = "fd-" + this.id;
                        this.div.className           = "datePicker";                   
                        
                        var tr, row, col, tableHead, tableBody, tableFoot;

                        this.table             = document.createElement('table');
                        this.table.className   = "datePickerTable";                         
                        this.table.onmouseover = this.events.ontablemouseover;
                        this.table.onmouseout  = this.events.ontablemouseout;

                        this.div.appendChild(this.table);   
                                
                        if(!this.staticPos) {
                                this.div.style.visibility = "hidden";
                                if(!this.dragDisabled) { this.div.className += " drag-enabled"; };
                                document.getElementsByTagName('body')[0].appendChild(this.div);
                                                                
                                /*@cc_on
                                @if(@_jscript_version <= 5.6)                                            
                                this.iePopUp = document.createElement('iframe');
                                this.iePopUp.src = "javascript:'<html></html>';";
                                this.iePopUp.setAttribute('className','iehack');
                                this.iePopUp.scrolling="no";
                                this.iePopUp.frameBorder="0";
                                this.iePopUp.name = this.iePopUp.id = this.id + "-iePopUpHack";
                                document.body.appendChild(this.iePopUp);
                                @end
                                @*/
                        } else {
                                elem = this.positioned ? document.getElementById(this.positioned) : this.getElem();
                                if(!elem) {
                                        this.div = null;
                                        throw this.positioned ? "Could not locate a datePickers associated parent element with an id:" + this.positioned : "Could not locate a datePickers associated input with an id:" + this.id;
                                };

                                this.div.className += " static-datepicker";                               
                                                               
                                // tabIndex
                                this.div.setAttribute(!/*@cc_on!@*/false ? "tabIndex" : "tabindex", "0");
                                this.div.tabIndex = 0;

                                this.div.onfocus = this.events.onfocus;
                                this.div.onblur  = this.events.onblur;                                                                                                                         
                                
                                if(this.positioned) {
                                        elem.appendChild(this.div);
                                } else {
                                        elem.parentNode.insertBefore(this.div, elem.nextSibling);
                                };
                                
                                if(this.hideInput) {
                                        var elemList = [elem];                                        
                                        if(this.splitDate) {
                                                elemList[elemList.length] = document.getElementById(this.id + splitAppend[1]);
                                                elemList[elemList.length] = document.getElementById(this.id + splitAppend[0]);                                         
                                        };
                                        for(var i = 0; i < elemList.length; i++) {
                                                if(elemList[i].tagName) elemList[i].className += " fd-hidden-input";        
                                        };
                                };                                                                  
                                                                          
                                setTimeout(this.resizeInlineDiv, 300);                               
                        };

                        
                        if(this.statusFormat) {
                                tableFoot = document.createElement('tfoot');
                                this.table.appendChild(tableFoot);
                                tr = document.createElement('tr');
                                tr.className = "date-picker-tfoot";
                                tableFoot.appendChild(tr);
                                this.statusBar = createTH({thClassName:"date-picker-statusbar", colspan:this.showWeeks ? 8 : 7});
                                tr.appendChild(this.statusBar);
                                this.updateStatus();
                                if(!this.dragDisabled) {
                                        this.statusBar.className += " drag-enabled";
                                        addEvent(this.statusBar,'mousedown',this.startDrag,false);
                                };
                        };

                        tableHead = document.createElement('thead');
                        this.table.appendChild(tableHead);

                        tr  = document.createElement('tr');
                        tableHead.appendChild(tr);

                        // Title Bar
                        this.titleBar = createTH({thClassName:!this.dragDisabled ? "date-picker-title drag-enabled" : "date-picker-title", colspan:this.showWeeks ? 8 : 7});
                        if(!this.dragDisabled) {
                                addEvent(this.titleBar,'mousedown',o.startDrag,false);
                        };

                        tr.appendChild(this.titleBar);
                        tr = null;

                        var span = document.createElement('span');
                        span.appendChild(document.createTextNode(nbsp));
                        span.className = !this.dragDisabled ? "month-display drag-enabled" : "month-display";
                        this.titleBar.appendChild(span);

                        span = document.createElement('span');
                        span.appendChild(document.createTextNode(nbsp));
                        span.className = !this.dragDisabled ? "year-display drag-enabled" : "year-display";
                        this.titleBar.appendChild(span);

                        span = null;

                        tr  = document.createElement('tr');
                        tableHead.appendChild(tr);

                        createThAndButton(tr, [
                        {className:"prev-but prev-year", id:"-prev-year-but", text:"\u00AB", title:getTitleTranslation(2), onmousedown:function(e) { addEvent(document, "mouseup", o.events.clearTimer);  o.events.incDec(e,0,-1,0); }, onmouseout:this.events.clearTimer },
                        {className:"prev-but prev-month", id:"-prev-month-but", text:"\u2039", title:getTitleTranslation(0), onmousedown:function(e) { addEvent(document, "mouseup", o.events.clearTimer); if(o.currentYYYYMM > Number(o.date.getFullYear() + pad(o.date.getMonth()+1))) { o.stopTimer(); o.updateTable(); o.timer = window.setTimeout(function() { o.events.incDec(e,0,0,-1); }, 800); return; }; o.events.incDec(e,0,0,-1); }, onmouseout:this.events.clearTimer },
                        {colspan:this.showWeeks ? 4 : 3, className:"today-but", id:"-today-but", text:getTitleTranslation(4), onclick:this.events.gotoToday},
                        {className:"next-but next-month", id:"-next-month-but", text:"\u203A", title:getTitleTranslation(1), onmousedown:function(e) { addEvent(document, "mouseup", o.events.clearTimer); if(o.currentYYYYMM < Number(o.date.getFullYear() + pad(o.date.getMonth()+1))) { o.stopTimer(); o.updateTable(); o.timer = window.setTimeout(function() { o.events.incDec(e,0,0,1); }, 800);  return; }; o.events.incDec(e,0,0,1); }, onmouseout:this.events.clearTimer },
                        {className:"next-but next-year", id:"-next-year-but", text:"\u00BB", title:getTitleTranslation(3), onmousedown:function(e) { addEvent(document, "mouseup", o.events.clearTimer); o.events.incDec(e,0,1,0); }, onmouseout:this.events.clearTimer }]);

                        tableBody = document.createElement('tbody');
                        this.table.appendChild(tableBody);

                        var colspanTotal = this.showWeeks ? 8 : 7,
                            colOffset    = this.showWeeks ? 0 : -1,
                            but, abbr;   
                
                        for(var rows = 0; rows < 7; rows++) {
                                row = document.createElement('tr');

                                if(rows != 0) {
                                        tableBody.appendChild(row);   
                                } else {
                                        tableHead.appendChild(row);
                                };

                                for(var cols = 0; cols < colspanTotal; cols++) {
                                                                                
                                        if(rows === 0 || (this.showWeeks && cols === 0)) {
                                                col = document.createElement('th');
                                        } else {
                                                col = document.createElement('td');
                                        };
                                        
                                        /*@cc_on@*/
                                        /*@if(@_win32)
                                        col.unselectable = "on";
                                        /*@end@*/  
                                        
                                        row.appendChild(col);
                                        if((this.showWeeks && cols > 0 && rows > 0) || (!this.showWeeks && rows > 0)) {
                                                col.onclick = this.events.onclick;
                                        } else {
                                                if(rows === 0 && cols > colOffset) {
                                                        col.className = "date-picker-day-header";
                                                        col.scope = "col";                                           
                                                } else {
                                                        col.className = "date-picker-week-header";
                                                        col.scope = "row";
                                                };
                                        };
                                };
                        };

                        col = row = null; 
                
                        this.ths = this.table.getElementsByTagName('thead')[0].getElementsByTagName('tr')[2].getElementsByTagName('th');
                        for (var y = 0; y < colspanTotal; y++) {
                                if(y == 0 && this.showWeeks) {
                                        this.ths[y].appendChild(document.createTextNode(getTitleTranslation(6)));
                                        this.ths[y].title = getTitleTranslation(8);
                                        continue;
                                };

                                if(y > (this.showWeeks ? 0 : -1)) {
                                        but = document.createElement("span");
                                        but.className = "fd-day-header";
                                        but.onclick = this.ths[y].onclick = this.setFirstDayOfWeek;
                                        /*@cc_on@*/
                                        /*@if(@_win32)
                                        but.unselectable = "on";
                                        /*@end@*/
                                        this.ths[y].appendChild(but);
                                };
                        };
                
                        but = null; 
                                        
                        this.trs             = this.table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                        this.tds             = this.table.getElementsByTagName('tbody')[0].getElementsByTagName('td');
                        this.butPrevYear     = document.getElementById(this.id + "-prev-year-but");
                        this.butPrevMonth    = document.getElementById(this.id + "-prev-month-but");
                        this.butToday        = document.getElementById(this.id + "-today-but");
                        this.butNextYear     = document.getElementById(this.id + "-next-year-but"); 
                        this.butNextMonth    = document.getElementById(this.id + "-next-month-but");
        
                        if(this.showWeeks) {
                                this.wkThs = this.table.getElementsByTagName('tbody')[0].getElementsByTagName('th');
                                this.div.className += " weeks-displayed";
                        };

                        tableBody = tableHead = tr = createThAndButton = createTH = null;

                        if(this.low && this.high && (this.high - this.low < 7)) { this.equaliseDates(); }; 
                        
                        this.setDateFromInput();                                       
                        this.updateTableHeaders();
                        this.created = true;                         
                        this.callback("create", {id:this.id});                        
                        this.updateTable();                         
                        
                        if(this.staticPos) {                                 
                                this.visible = true;
                                this.show();
                                this.div.style.visibility = "visible";
                                this.opacity = this.opacityTo;
                        } else {                                     
                                this.reposition();
                                this.div.style.visibility = "visible";
                                this.fade();
                        };                                               
                };
                this.setFirstDayOfWeek = function(e) {
                        e = e || document.parentWindow.event;
                        var elem = e.target != null ? e.target : e.srcElement;
                        while(elem.tagName.toLowerCase() != "th") elem = elem.parentNode;                         
                        var cnt = o.showWeeks ? -1 : 0;
                        while(elem.previousSibling) {
                                elem = elem.previousSibling;
                                if(elem.tagName.toLowerCase() == "th") cnt++;
                        };
                        o.firstDayOfWeek = (o.firstDayOfWeek + cnt) % 7;
                        o.updateTableHeaders();
                        return stopEvent(e);
                };
                this.fade = function() {
                        window.clearTimeout(o.fadeTimer);
                        o.fadeTimer = null;   
                        var diff = Math.round(o.opacity + ((o.opacityTo - o.opacity) / 4)); 
                        o.setOpacity(diff);  
                        if(Math.abs(o.opacityTo - diff) > 3 && !o.noFadeEffect) {
                                o.fadeTimer = window.setTimeout(o.fade, 50);
                        } else {
                                o.setOpacity(o.opacityTo);
                                if(o.opacityTo == 0) {
                                        o.div.style.display = "none";
                                        o.visible = false;
                                } else {
                                        o.visible = true;
                                };
                        };
                };          
                this.events = {
                        onblur:function(e) {                                    
                                o.removeKeyboardEvents();                                 
                                if(o.statusBar) { o.updateStatus(getTitleTranslation(9)); };
                        },
                        onfocus:function(e) {                                 
                                o.addKeyboardEvents();                                 
                                if(o.statusBar) { o.updateStatus(printFormattedDate(o.date, o.statusFormat, true)); };
                        },
                        onkeydown: function (e) {
                                o.stopTimer();
                                if(!o.visible) return false;

                                if(e == null) e = document.parentWindow.event;
                                var kc = e.keyCode ? e.keyCode : e.charCode;
                                
                                if( kc == 13 ) {
                                        // RETURN/ENTER: close & select the date
                                        var td = document.getElementById(o.id + "-date-picker-hover");                                         
                                        if(!td || td.className.search(/cd-([0-9]{8})/) == -1 || td.className.search(/no-selection|out-of-range|day-disabled/) != -1) return stopEvent(e);
                                        o.dateSet = new Date(o.date);
                                        o.returnFormattedDate();
                                        o.hide();
                                        return stopEvent(e);
                                } else if(kc == 27) {
                                        // ESC: close, no date selection 
                                        o.hide();
                                        return stopEvent(e);
                                } else if(kc == 32 || kc == 0) {
                                        // SPACE: goto today's date 
                                        o.date = new Date();
                                        o.updateTable();
                                        return stopEvent(e);
                                };    
                                 
                                // Internet Explorer fires the keydown event faster than the JavaScript engine can
                                // update the interface. The following attempts to fix this.
                                /*@cc_on
                                @if(@_win32)
                                if(new Date().getTime() - o.interval.getTime() < 50) return stopEvent(e);
                                o.interval = new Date();
                                @end
                                @*/
                        
                                if ((kc > 49 && kc < 56) || (kc > 97 && kc < 104)) {
                                        if (kc > 96) kc -= (96-48);
                                        kc -= 49;
                                        o.firstDayOfWeek = (o.firstDayOfWeek + kc) % 7;
                                        o.updateTableHeaders();
                                        return stopEvent(e);
                                };

                                if ( kc < 33 || kc > 40 ) return true;

                                var d = new Date(o.date), tmp, cursorYYYYMM = o.date.getFullYear() + pad(o.date.getMonth()+1); 

                                // HOME: Set date to first day of current month
                                if(kc == 36) {
                                        d.setDate(1); 
                                // END: Set date to last day of current month                                 
                                } else if(kc == 35) {
                                        d.setDate(daysInMonth(d.getMonth(),d.getFullYear())); 
                                // PAGE UP & DOWN                                   
                                } else if ( kc == 33 || kc == 34) {
                                        var add = (kc == 34) ? 1 : -1; 
                                        // CTRL + PAGE UP/DOWN: Moves to the same date in the previous/next year
                                        if(e.ctrlKey) {                                                                                                               
                                                d.setFullYear(d.getFullYear() + add);
                                        // PAGE UP/DOWN: Moves to the same date in the previous/next month                                            
                                        } else {                                                     
                                                if(!((kc == 33 && o.currentYYYYMM > cursorYYYYMM) || (kc == 34 && o.currentYYYYMM < cursorYYYYMM))) {                                                    
                                                        tmp = new Date(d);
                                                        tmp.setDate(2);
                                                        tmp.setMonth(d.getMonth() + add);                                         
                                                        d.setDate(Math.min(d.getDate(), daysInMonth(tmp.getMonth(),tmp.getFullYear())));                                        
                                                        d.setMonth(d.getMonth() + add);
                                                };      
                                        };                                 
                                // LEFT ARROW                                    
                                } else if ( kc == 37 ) {                                         
                                        d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() - 1);                                       
                                // RIGHT ARROW
                                } else if ( kc == 39 || kc == 34) {                                         
                                        d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() + 1 ); 
                                // UP ARROW                                        
                                } else if ( kc == 38 ) {                                          
                                        d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() - 7);  
                                // DOWN ARROW                                        
                                } else if ( kc == 40 ) {                                          
                                        d = new Date(o.date.getFullYear(), o.date.getMonth(), o.date.getDate() + 7);                                         
                                };

                                if(o.outOfRange(d)) return stopEvent(e);
                                o.date = d;
                        
                                if(o.statusBar) { o.updateStatus(printFormattedDate(o.date, o.statusFormat, true)); };
                                var t = String(o.date.getFullYear()) + pad(o.date.getMonth()+1) + pad(o.date.getDate())

                                if(e.ctrlKey || (kc == 33 || kc == 34) || t < o.firstDateShown || t > o.lastDateShown) {                                         
                                        o.updateTable(); 
                                        o.interval = new Date();                                        
                                } else {                                    
                                        o.disableTodayButton();
                                        o.removeHighlight();
                                
                                        var dt = "cd-" + o.date.getFullYear() + pad(o.date.getMonth()+1) + pad(o.date.getDate());
                                            
                                        for(var i = 0, td; td = o.tds[i]; i++) {  
                                                td.className = td.className.replace(/date-picker-hover/g, "");                                               
                                                if(td.className.search(dt) == -1) continue;                                                 
                                                o.showHideButtons(o.date);
                                                td.id = o.id + "-date-picker-hover";
                                                td.className = td.className.replace(/date-picker-hover/g, "") + " date-picker-hover";
                                                break;
                                        };
                                };

                                return stopEvent(e);
                        },
                        gotoToday: function(e) {
                                o.date = new Date();
                                o.updateTable();
                                return stopEvent(e);
                        },
                        onmousedown: function(e) {
                                e = e || document.parentWindow.event;
                                var el = e.target != null ? e.target : e.srcElement;
                                while(el.parentNode) {
                                        if(el.id && (el.id == "fd-" + o.id || el.id == "fd-but-" + o.id)) {
                                                return true;
                                        };
                                        try { el = el.parentNode; } catch(err) { break; };
                                };
                                o.stopTimer();
                                hideAll();
                        },
                        ontablemouseout:function(e) {
                                e = e || document.parentWindow.event;
                                var p = e.toElement || e.relatedTarget;
                                while (p && p != this) try { p = p.parentNode } catch(e) { p = this; };
                                if (p == this) return false;
                                if(o.currentTR) {
                                        o.currentTR.className = o.currentTR.className.replace('dp-row-highlight', '');
                                        o.currentTR = null;
                                };
                                if(o.statusBar) { o.updateStatus(printFormattedDate(o.date, o.statusFormat, true)); };
                        },
                        ontablemouseover: function(e) {
                                e = e || document.parentWindow.event;
                                var el = e.target != null ? e.target : e.srcElement;
                                while ( el.nodeType != 1 ) el = el.parentNode;

                                if(!el || ! el.tagName) { return; };
                                var statusText = getTitleTranslation(9);
                                switch (el.tagName.toLowerCase()) {
                                case "td":    
                                        if(el.className.search(/date-picker-unused|out-of-range/) != -1) {
                                                statusText = getTitleTranslation(9);
                                        } else if(el.className.search(/cd-([0-9]{8})/) == -1) {
                                                break;
                                        } else {                                                
                                                o.stopTimer();
                                                var cellDate = el.className.match(/cd-([0-9]{8})/)[1];                                                                              
                                                o.removeHighlight();
                                                el.id = o.id+"-date-picker-hover";
                                                el.className = el.className.replace(/date-picker-hover/g, "") + " date-picker-hover";                                                 
                                                o.date = new Date(cellDate.substr(0,4),cellDate.substr(4,2)-1,cellDate.substr(6,2));                                                
                                                o.disableTodayButton();
                                                statusText = printFormattedDate(o.date, o.statusFormat, true);

                                        };
                                        break;
                                case "th":
                                        if(!o.statusBar) { break; };
                                        if(el.className.search(/drag-enabled/) != -1) {
                                                statusText = getTitleTranslation(10);
                                        } else if(el.className.search(/date-picker-week-header/) != -1) {
                                                var txt = el.firstChild ? el.firstChild.nodeValue : "";
                                                statusText = txt.search(/^(\d+)$/) != -1 ? getTitleTranslation(7, [txt, txt < 3 && o.date.getMonth() == 11 ? getWeeksInYear(o.date.getFullYear()) + 1 : getWeeksInYear(o.date.getFullYear())]) : getTitleTranslation(9);
                                        };
                                        break;
                                case "span":
                                        if(!o.statusBar) { break; };
                                        if(el.className.search(/drag-enabled/) != -1) {
                                                statusText = getTitleTranslation(10);
                                        } else if(el.className.search(/day-([0-6])/) != -1) {
                                                var day = el.className.match(/day-([0-6])/)[1];
                                                statusText = getTitleTranslation(11, [getDayTranslation(day, false)]);
                                        } else if(el.className.search(/prev-year/) != -1) {
                                                statusText = getTitleTranslation(2);
                                        } else if(el.className.search(/prev-month/) != -1) {
                                                statusText = getTitleTranslation(0);
                                        } else if(el.className.search(/next-year/) != -1) {
                                                statusText = getTitleTranslation(3);
                                        } else if(el.className.search(/next-month/) != -1) {
                                                statusText = getTitleTranslation(1);
                                        } else if(el.className.search(/today-but/) != -1 && el.className.search(/disabled/) == -1) {
                                                statusText = getTitleTranslation(12);
                                        };
                                        break;
                                default:
                                        statusText = "";
                                };
                                while(el.parentNode) {
                                        el = el.parentNode;
                                        if(el.nodeType == 1 && el.tagName.toLowerCase() == "tr") {
                                                if(el == o.currentTR) break;
                                                if(o.currentTR) {
                                                        o.currentTR.className = o.currentTR.className.replace('dp-row-highlight', '');
                                                };
                                                el.className = el.className + " dp-row-highlight";
                                                o.currentTR = el;
                                                break;
                                        };
                                };                                                          
                                if(o.statusBar && statusText) { o.updateStatus(statusText); };
                        },
                        onclick: function(e) {                                                  
                                if(o.opacity != o.opacityTo || this.className.search(/date-picker-unused|out-of-range|day-disabled|no-selection/) != -1) return false;
                                e = e || document.parentWindow.event;
                                var el = e.target != null ? e.target : e.srcElement;
                                while (el.nodeType != 1 || (el.tagName && el.tagName != "TD")) el = el.parentNode;
                                var cellDate = el.className.match(/cd-([0-9]{8})/)[1];                                                                                                                                                                           
                                o.date = new Date(cellDate.substr(0,4),cellDate.substr(4,2)-1,cellDate.substr(6,2));
                                o.dateSet = new Date(o.date);                                 
                                o.returnFormattedDate(true);
                                if(!o.staticPos) { o.hide(); }
                                else { o.updateTable();};
                                o.stopTimer();
                                return stopEvent(e);
                        },
                        incDec: function(e) {                            
                                e = e || document.parentWindow.event;
                                var el = e.target != null ? e.target : e.srcElement;
                                if(el && el.className && el.className.search('fd-disabled') != -1) { return false; }                                
                                o.timerInc      = 800;
                                o.dayInc        = arguments[1];
                                o.yearInc       = arguments[2];
                                o.monthInc      = arguments[3];                         
                                o.timerSet      = true;                                                
                       
                                o.updateTable();
                                return true;
                        },
                        clearTimer: function(e) {
                                o.stopTimer();
                                o.timerInc      = 800;
                                o.yearInc       = 0;
                                o.monthInc      = 0;
                                o.dayInc        = 0;
                                removeEvent(document, "mouseup", o.events.clearTimer);
                        }
                };
        
                this.setFormElementEvents = function() {
                        var elem = this.getElem();
                        if(elem && elem.tagName.search(/select|input/i) != -1) {                                         
                                addEvent(elem, "change", o.changeHandler);
                                if(this.splitDate) {                                                                         
                                        addEvent(document.getElementById(this.id + splitAppend[1]), "change", o.changeHandler);
                                        addEvent(document.getElementById(this.id + splitAppend[0]), "change", o.changeHandler);
                                };
                        };
                };                 
                
                var o = this;
                
                o.setFormElementEvents();
                
                if(this.staticPos) { this.create(); this.setDateFromInput(); }
                else { 
                        this.createButton();
                        this.setDateFromInput();                         
                };
        };
        datePicker.prototype.createButton = function() {
                
                if(this.staticPos || document.getElementById("fd-but-" + this.id)) { return; };

                var inp         = this.getElem(),
                    span        = document.createElement('span'),
                    but         = document.createElement('a');

                but.href        = "#";
                but.className   = "date-picker-control";
                but.title       = getTitleTranslation(5);
                but.id          = "fd-but-" + this.id;

                span.appendChild(document.createTextNode(nbsp));
                but.appendChild(span);

                if(this.buttonWrapper && document.getElementById(this.buttonWrapper)) {
                        document.getElementById(this.buttonWrapper).appendChild(but);
                } else if(inp.nextSibling) {
                        inp.parentNode.insertBefore(but, inp.nextSibling);
                } else {
                        inp.parentNode.appendChild(but);
                };                   

                but.onclick = but.onpress = function(e) {
                        e = e || window.event;                      
                
                        var inpId     = this.id.replace('fd-but-',''),
                            dpVisible = isVisible(inpId);  
                
                        if(e.type == "press") {
                                var kc = e.keyCode != null ? e.keyCode : e.charCode;
                                if(kc != 13) return true; 
                                if(dpVisible) {
                                        this.className = this.className.replace("dp-button-active", "");
                                        datePickerController.hideAll();
                                        return false;
                                };
                        };

                        this.className = this.className.replace("dp-button-active", "");
                        
                        if(!dpVisible) {
                                this.className += " dp-button-active";
                                hideAll(inpId);
                                showDatePicker(inpId);
                        } else {
                                hideAll();
                        };
                
                        return false;
                };
        
                but = null;
        };  
        datePicker.prototype.setRangeLow = function(range) {
                this.low = (String(range).search(/^(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/) == -1) ? false : range;                
                if(this.created) { this.updateTable(); };
        };
        datePicker.prototype.setRangeHigh = function(range) {
                this.high = (String(range).search(/^(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/) == -1) ? false : range;                
                if(this.created) { this.updateTable(); };
        };
        datePicker.prototype.setDisabledDays = function(dayArray) {
                this.disabledDays = dayArray;
                if(this.created) { this.updateTable(); };
        };
        datePicker.prototype.setDisabledDates = function(dateArray) {                
                this.disabledDates = {};
                this.addDisabledDates(dateArray);                
        }; 
        datePicker.prototype.addDisabledDates = function(dateArray) {
                var disabledDateObj = {};
                if(typeof dateArray !== "object") dateArray = [dateArray];                
                for(var i = dateArray.length; i-- ;) {
                        if(dateArray[i].match(/^(\d\d\d\d|\*\*\*\*)(0[1-9]|1[012]|\*\*)(0[1-9]|[12][0-9]|3[01])$/) != -1) {
                                this.disabledDates[dateArray[i]] = 1;
                        };
                }; 
                if(this.created) { this.updateTable(); };                                  
        };
        datePicker.prototype.addKeyboardEvents = function() {
                addEvent(document, "keypress", this.events.onkeydown);
                /*@cc_on
                @if(@_win32)
                removeEvent(document, "keypress", this.events.onkeydown);
                addEvent(document, "keydown", this.events.onkeydown);
                @end
                @*/
                if(window.devicePixelRatio) {
                        removeEvent(document, "keypress", this.events.onkeydown);
                        addEvent(document, "keydown", this.events.onkeydown);
                };
        };  
        datePicker.prototype.removeKeyboardEvents = function() {
                removeEvent(document, "keypress", this.events.onkeydown);
                removeEvent(document, "keydown",  this.events.onkeydown);
        };
        datePicker.prototype.removeHighlight = function() {
                var el = document.getElementById(this.id+"-date-picker-hover");
                if(el) {
                        el.className = el.className.replace("date-picker-hover", "");
                        el.id = "";                                                                
                };
        };
        datePicker.prototype.stopTimer = function() {
                this.timerSet = false;
                window.clearTimeout(this.timer);
        };
        datePicker.prototype.setOpacity = function(op) {
                this.div.style.opacity = op/100;
                this.div.style.filter = 'alpha(opacity=' + op + ')';
                this.opacity = op;
        };         
        datePicker.prototype.getElem = function() {
                return document.getElementById(this.id.replace(/^fd-/, '')) || false;
        };
        datePicker.prototype.getDisabledDates = function(y, m) {
                m = pad(m);                 
                
                var obj = {},            
                    lower  = this.firstDateShown,
                    upper  = this.lastDateShown,             
                    dt1, dt2, rngLower, rngUpper;  
                
                if(!upper || !lower) {
                        lower = this.firstDateShown = y + pad(m) + "01";
                        upper = this.lastDateShown  = y + pad(m) + pad(daysInMonth(m, y));                        
                };
                
                for(var dt in this.disabledDates) {                            
                        dt1 = dt.replace(/^(\*\*\*\*)/, y).replace(/^(\d\d\d\d)(\*\*)/, "$1"+m);
                        dt2 = this.disabledDates[dt];

                        if(dt2 == 1) {                                 
                                if(+lower <= +dt1 && +upper >= +dt1) {
                                        obj[dt1] = 1;                                         
                                };
                                continue;
                        };

                        // Range of disabled dates                        
                        if(Number(dt1.substr(0,6)) <= +String(this.firstDateShown).substr(0,6) && Number(dt2.substr(0,6)) >= +String(this.lastDateShown).substr(0,6)) {
                                // Same month
                                if(Number(dt1.substr(0,6)) == Number(dt2.substr(0,6))) {
                                        for(var i = dt1; i <= dt2; i++) {
                                                obj[i] = 1;
                                        };
                                        continue;
                                };

                                // Different months but we only want this month
                                rngLower = Number(dt1.substr(0,6)) == +String(this.firstDateShown).substr(0,6) ? dt1 : lower;
                                rngUpper = Number(dt2.substr(0,6)) == +String(this.lastDateShown).substr(0,6) ? dt2 : upper;
                                for(var i = +rngLower; i <= +rngUpper; i++) {
                                        obj[i] = 1;                                        
                                };
                        };
                };
                
                for(dt in this.enabledDates) {
                        dt1 = dt.replace(/^(\*\*\*\*)/, y).replace(/^(\d\d\d\d)(\*\*)/, "$1"+m);
                        dt2 = this.enabledDates[dt];

                        if(dt2 == 1) {
                                if(dt1 in obj) {                                          
                                        obj[dt1] = null;
                                        delete obj[dt1];
                                };
                                continue;
                        };

                        // Range
                        if(Number(dt1.substr(0,6)) <= +String(this.firstDateShown).substr(0,6) && Number(dt2.substr(0,6)) >= +String(this.lastDateShown).substr(0,6)) {
                                // Same month
                                if(Number(dt1.substr(0,6)) == Number(dt2.substr(0,6))) {
                                        for(var i = dt1; i <= dt2; i++) {
                                                if(i in obj) {
                                                        obj[i] = null;
                                                        delete obj[i];
                                                };
                                        };
                                        continue;
                                };

                                // Different months but we only want this month
                                rngLower = Number(dt1.substr(0,6)) == +String(this.firstDateShown).substr(0,6) ? dt1 : lower;
                                rngUpper = Number(dt2.substr(0,6)) == +String(this.lastDateShown).substr(0,6) ? dt2 : upper;
                                for(var i = +rngLower; i <= +rngUpper; i++) {
                                        if(i in obj) {
                                                obj[i] = null;
                                                delete obj[i];
                                        };
                                };
                        };
                };
                return obj;
        };
        datePicker.prototype.truePosition = function(element) {
                var pos = this.cumulativeOffset(element);
                if(window.opera) { return pos; };
                var iebody      = (document.compatMode && document.compatMode != "BackCompat")? document.documentElement : document.body,
                    dsocleft    = document.all ? iebody.scrollLeft : window.pageXOffset,
                    dsoctop     = document.all ? iebody.scrollTop  : window.pageYOffset,
                    posReal     = this.realOffset(element);
                return [pos[0] - posReal[0] + dsocleft, pos[1] - posReal[1] + dsoctop];
        };
        datePicker.prototype.realOffset = function(element) {
                var t = 0, l = 0;
                do {
                        t += element.scrollTop  || 0;
                        l += element.scrollLeft || 0;
                        element = element.parentNode;
                } while(element);
                return [l, t];
        };
        datePicker.prototype.cumulativeOffset = function(element) {
                var t = 0, l = 0;
                do {
                        t += element.offsetTop  || 0;
                        l += element.offsetLeft || 0;
                        element = element.offsetParent;
                } while(element);
                return [l, t];
        };
        datePicker.prototype.equaliseDates = function() {
                var clearDayFound = false, tmpDate;
                for(var i = this.low; i <= this.high; i++) {
                        tmpDate = String(i);
                        if(!this.disabledDays[new Date(tmpDate.substr(0,4), tmpDate.substr(6,2), tmpDate.substr(4,2)).getDay() - 1]) {
                                clearDayFound = true;
                                break;
                        };
                };
                if(!clearDayFound) { this.disabledDays = [0,0,0,0,0,0,0] };
        };
        datePicker.prototype.outOfRange = function(tmpDate) {
                if(!this.low && !this.high) { return false; };

                var level = false;
                if(!tmpDate) {
                        level   = true;
                        tmpDate = this.date;
                };

                var d  = pad(tmpDate.getDate()),
                    m  = pad(tmpDate.getMonth() + 1),
                    y  = tmpDate.getFullYear(),
                    dt = String(y)+String(m)+String(d);

                if(this.low && +dt < +this.low) {
                        if(!level) return true;
                        this.date = new Date(this.low.substr(0,4), this.low.substr(4,2)-1, this.low.substr(6,2), 5, 0, 0);
                        return false;
                };
                if(this.high && +dt > +this.high) {
                        if(!level) return true;
                        this.date = new Date(this.high.substr(0,4), this.high.substr(4,2)-1, this.high.substr(6,2), 5, 0, 0);
                };
                return false;
        };         
        datePicker.prototype.updateStatus = function(msg) {
                while(this.statusBar.firstChild) { this.statusBar.removeChild(this.statusBar.firstChild); };
                if(msg && this.statusFormat.search(/-S|S-/) != -1) {
                        msg = msg.replace(/([0-9]{1,2})(st|nd|rd|th)/, "$1<sup>$2</sup>");
                        msg = msg.split(/<sup>|<\/sup>/);
                        var dc = document.createDocumentFragment();
                        for(var i = 0, nd; nd = msg[i]; i++) {
                                if(/^(st|nd|rd|th)$/.test(nd)) {
                                        var sup = document.createElement("sup");
                                        sup.appendChild(document.createTextNode(nd));
                                        dc.appendChild(sup);
                                } else {
                                        dc.appendChild(document.createTextNode(nd));
                                };
                        };
                        this.statusBar.appendChild(dc);
                } else {
                        this.statusBar.appendChild(document.createTextNode(msg ? msg : getTitleTranslation(9)));
                };
        };
        datePicker.prototype.setDateFromInput = function() {
                this.dateSet = null;

                var elem = this.getElem(), 
                    upd  = false, 
                    dt;
                    
                if(!elem || elem.tagName.search(/select|input/i) == -1) return; 

                if(!this.splitDate && elem.value.replace(/\s/g, "") !== "") {
                        var dynFormatMasks = formatMasks.concat([this.format]).reverse();                                                
                        for(var i = 0, fmt; fmt = dynFormatMasks[i]; i++) {
                                dt = parseDateString(elem.value, fmt);                                                              
                                if(dt) {                                    
                                        upd = true;                                       
                                        break;
                                };
                        };                                                                        
                } else if(this.splitDate) {
                        var mmN  = document.getElementById(this.id + splitAppend[1]),
                            ddN  = document.getElementById(this.id + splitAppend[0]),
                            tm   = parseInt(mmN.tagName.toLowerCase() == "input"  ? mmN.value  : mmN.options[mmN.selectedIndex || 0].value, 10),
                            td   = parseInt(ddN.tagName.toLowerCase() == "input"  ? ddN.value  : ddN.options[ddN.selectedIndex || 0].value, 10),
                            ty   = parseInt(elem.tagName.toLowerCase() == "input" ? elem.value : elem.options[elem.selectedIndex || 0].value, 10);
                                             
                        if(!(/\d\d\d\d/.test(ty)) || !(/^(0?[1-9]|1[012])$/.test(tm)) || !(/^(0?[1-9]|[12][0-9]|3[01])$/.test(td))) {
                                dt = false;
                        } else {
                                if(+td > daysInMonth(+tm - 1, +ty)) {                                         
                                        dt = false;
                                } else {
                                        dt = new Date(ty,tm-1,td);
                                };
                        };                        
                };

                if(!dt || isNaN(dt)) {                                                              
                        this.date = new Date();
                        this.date.setHours(5);
                        this.outOfRange();
                        return;
                };

                dt.setHours(5);
                this.date = new Date(dt);                            
                this.outOfRange();                 
                
                var dtYYYYMMDD = dt.getFullYear() + pad(dt.getMonth() + 1) + pad(dt.getDate()),
                    weekDay    = ( dt.getDay() + 6 ) % 7;
                
                if(dt.getTime() == this.date.getTime() && !(dtYYYYMMDD in this.dynDisabledDates || this.disabledDays[weekDay])) {                        
                        this.dateSet = new Date(this.date);
                };
                
                if(upd) { this.returnFormattedDate(); };
        };
        datePicker.prototype.setSelectIndex = function(elem, indx) {
                for(var opt = elem.options.length-1; opt >= 0; opt--) {
                        if(elem.options[opt].value == +indx) {
                                elem.selectedIndex = opt;
                                return;
                        };
                };
        };
        datePicker.prototype.returnFormattedDate = function(noChange) {
                noChange = noChange || false;                 
                
                var elem = this.getElem();
                if(!elem) return;

                var d                   = pad(this.date.getDate()),
                    m                   = pad(this.date.getMonth() + 1),
                    yyyy                = this.date.getFullYear(),
                    disabledDates       = this.getDisabledDates(+yyyy, +m),
                    weekDay             = (this.date.getDay() + 6) % 7;

                
                
                //if(!(this.disabledDays[weekDay] || String(yyyy)+m+d in this.disabledDates)) {
                        if(this.splitDate) {
                                var ddE = document.getElementById(this.id+splitAppend[0]),
                                    mmE = document.getElementById(this.id+splitAppend[1]);
                                    
                                if(ddE.tagName.toLowerCase() == "input") { ddE.value = d; }
                                else { this.setSelectIndex(ddE, d); };
                                if(mmE.tagName.toLowerCase() == "input") { mmE.value = m; }
                                else { this.setSelectIndex(mmE, m); };
                                if(elem.tagName.toLowerCase() == "input") elem.value = yyyy;
                                else { this.setSelectIndex(elem, yyyy); };
                                
                        } else if(elem.tagName.toLowerCase() == "input") {  
                                //alert(printFormattedDate(this.date, this.format))                                 
                                elem.value = printFormattedDate(this.date, this.format);                                
                        };
                        
                        if(elem.type && elem.type != "hidden") { elem.focus(); }                         
                                                                  
                        this.callback("dateselect", { "id":this.id, "date":this.dateSet, "dd":d, "mm":m, "yyyy":yyyy });                        
                        
                        if(this.staticPos) {                                 
                                this.updateTable();
                        };           
                                                                        
                        if(noChange || elem.tagName.search(/input|select/i) == -1) return;
                        
                        /*
                        if(document.createEvent) {
                                var onchangeEvent = document.createEvent('HTMLEvents');
                                onchangeEvent.initEvent('change', true, false);
                                elem.dispatchEvent(onchangeEvent);
                        } else if(document.createEventObject) {
                                elem.fireEvent('onchange');
                        };
                        */
                //};
        };
        datePicker.prototype.disableTodayButton = function() {
                var today = new Date();                     
                this.butToday.className = this.butToday.className.replace("fd-disabled", "");
                if(this.outOfRange(today) || (this.date.getDate() == today.getDate() && this.date.getMonth() == today.getMonth() && this.date.getFullYear() == today.getFullYear())) {
                        this.butToday.className += " fd-disabled";
                        this.butToday.onclick = null;
                } else {
                        this.butToday.onclick = this.events.gotoToday;
                };
        };
        datePicker.prototype.updateTableHeaders = function() {
                var colspanTotal = this.showWeeks ? 8 : 7,
                    colOffset    = this.showWeeks ? 1 : 0,
                    d, but;

                for(var col = colOffset; col < colspanTotal; col++ ) {
                        d = (this.firstDayOfWeek + (col - colOffset)) % 7;
                        this.ths[col].title = getDayTranslation(d, false);

                        if(col > colOffset) {
                                but = this.ths[col].getElementsByTagName("span")[0];
                                while(but.firstChild) { but.removeChild(but.firstChild); };
                                but.appendChild(document.createTextNode(getDayTranslation(d, true)));
                                but.title = this.ths[col].title;
                                but.className = but.className.replace(/day-([0-6])/, "") + " day-" + d;
                                but = null;
                        } else {
                                while(this.ths[col].firstChild) { this.ths[col].removeChild(this.ths[col].firstChild); };
                                this.ths[col].appendChild(document.createTextNode(getDayTranslation(d, true)));
                        };

                        this.ths[col].className = this.ths[col].className.replace(/date-picker-highlight/g, "");
                        if(this.highlightDays[d]) {
                                this.ths[col].className += " date-picker-highlight";
                        };
                };
                
                if(this.created) { this.updateTable(); }
        };

        datePicker.prototype.callback = function(type, args) {                                                     
                if(!type || !(type in this.callbacks)) return false;
                
                var ret = false;                   
                for(var func = 0; func < this.callbacks[type].length; func++) {                         
                        ret = this.callbacks[type][func](args || this.id);
                        if(!ret) return false;
                };                      
                return ret;
        };
        
        datePicker.prototype.showHideButtons = function(tmpDate) {
                var tdm = tmpDate.getMonth(),
                    tdy = tmpDate.getFullYear();

                this.butPrevYear.className = this.butPrevYear.className.replace("fd-disabled", "");
                if(this.outOfRange(new Date((tdy - 1), tdm, daysInMonth(+tdm, tdy-1)))) {
                        this.butPrevYear.className += " fd-disabled";
                        if(this.yearInc == -1) this.stopTimer();
                };    
                
                this.butPrevMonth.className = this.butPrevMonth.className.replace("fd-disabled", "");
                if(this.outOfRange(new Date(tdy, (+tdm - 1), daysInMonth(+tdm-1, tdy)))) {
                        this.butPrevMonth.className += " fd-disabled";
                        if(this.monthInc == -1) this.stopTimer();
                };
         
                this.butNextYear.className = this.butNextYear.className.replace("fd-disabled", "");
                if(this.outOfRange(new Date((tdy + 1), +tdm, 1))) {
                        this.butNextYear.className += " fd-disabled";
                        if(this.yearInc == 1) this.stopTimer();
                };
                
                this.butNextMonth.className = this.butNextMonth.className.replace("fd-disabled", "");
                if(this.outOfRange(new Date(tdy, +tdm + 1, 1))) {
                        this.butNextMonth.className += " fd-disabled";
                        if(this.monthInc == 1) this.stopTimer();
                };
        };        
        var localeDefaults = {
                fullMonths:["January","February","March","April","May","June","July","August","September","October","November","December"],
                monthAbbrs:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                fullDays:  ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
                dayAbbrs:  ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"],
                titles:    ["Previous month","Next month","Previous year","Next year", "Today", "Show Calendar", "wk", "Week [[%0%]] of [[%1%]]", "Week", "Select a date", "Click \u0026 Drag to move", "Display \u201C[[%0%]]\u201D first", "Go to Today\u2019s date"],
                imported:  false
        };        
        var grepRangeLimits = function(sel) {
                var range = [];
                for(var i = 0; i < sel.options.length; i++) {
                        if(sel.options[i].value.search(/^\d\d\d\d$/) == -1) { continue; };
                        if(!range[0] || Number(sel.options[i].value) < range[0]) { range[0] = Number(sel.options[i].value); };
                        if(!range[1] || Number(sel.options[i].value) > range[1]) { range[1] = Number(sel.options[i].value); };
                };
                return range;
        };
        var joinNodeLists = function() {
                if(!arguments.length) { return []; }
                var nodeList = [];
                for (var i = 0; i < arguments.length; i++) {
                        for (var j = 0, item; item = arguments[i][j]; j++) {
                                nodeList[nodeList.length] = item;
                        };
                };
                return nodeList;
        };
        var cleanUp = function() {
                var dp;
                for(dp in datePickers) {
                        if(!document.getElementById(datePickers[dp].id)) {
                                if(datePickers[dp].created) datePickers[dp].destroy();
                                datePickers[dp] = null;
                                delete datePickers[dp];
                        };
                };
        };         
        var hideAll = function(exception) {
                var dp;
                for(dp in datePickers) {
                        if(!datePickers[dp].created || datePickers[dp].staticPos || (exception && exception == datePickers[dp].id)) continue;
                        datePickers[dp].hide();
                };
        };
        var showDatePicker = function(inpID) {
                if(!(inpID in datePickers)) return false;                 
                datePickers[inpID].show();
                return true;        
        };
        var destroy = function() {
                for(dp in datePickers) {
                        if(datePickers[dp].created) datePickers[dp].destroy();
                        datePickers[dp] = null;
                        delete datePickers[dp];
                };
                datePickers = null;
                removeEvent(window, 'load',   datePickerController.create);
                removeEvent(window, 'unload', datePickerController.destroy);
        }; 
        var getTitleTranslation = function(num, replacements) {
                replacements = replacements || [];
                if(localeImport.titles.length > num) {
                         var txt = localeImport.titles[num];
                         if(replacements && replacements.length) {
                                for(var i = 0; i < replacements.length; i++) {
                                        txt = txt.replace("[[%" + i + "%]]", replacements[i]);
                                };
                         };
                         return txt.replace(/[[%(\d)%]]/g,"");
                };
                return "";
        };
        var getDayTranslation = function(day, abbreviation) {
                var titles = localeImport[abbreviation ? "dayAbbrs" : "fullDays"];
                return titles.length && titles.length > day ? titles[day] : "";
        };
        var getMonthTranslation = function(month, abbreviation) {
                var titles = localeImport[abbreviation ? "monthAbbrs" : "fullMonths"];
                return titles.length && titles.length > month ? titles[month] : "";
        };
        var daysInMonth = function(nMonth, nYear) {
                nMonth = (nMonth + 12) % 12;
                return (((0 == (nYear%4)) && ((0 != (nYear%100)) || (0 == (nYear%400)))) && nMonth == 1) ? 29: [31,28,31,30,31,30,31,31,30,31,30,31][nMonth];
        };
        var getWeeksInYear = function(Y) {
                if(Y in weeksInYearCache) {
                        return weeksInYearCache[Y];
                };
                var X1, X2, NW;
                with (X1 = new Date(Y, 0, 4)) {
                        setDate(getDate() - (6 + getDay()) % 7);
                };
                with (X2 = new Date(Y, 11, 28)) {
                        setDate(getDate() + (7 - getDay()) % 7);
                };
                weeksInYearCache[Y] = Math.round((X2 - X1) / 604800000);
                return weeksInYearCache[Y];
        };
        var parseRangeFromString = function(str) {
                if(!str) return "";
                
                var low = str.search(/^range-low-/) != -1;
                str = str.replace(/range-(low|high)-/, "");

                if(str.search(/^(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])$/) != -1) { return str; };

                var tmpDate = new Date();
                
                if(str.search(/^today$/) != -1) { return tmpDate.getFullYear() + pad(tmpDate.getMonth() + 1) + pad(tmpDate.getDate()); };
                
                var regExp = /^(\d)-(day|week|month|year)$/;
                
                if(str.search(regExp) != -1) {
                        var parts       = str.match(regExp),
                            acc         = { day:0,week:0,month:0,year:0 };
                            
                        acc[parts[2]]   = low ? -(+parts[1]) : +parts[1];
                        tmpDate.setFullYear(tmpDate.getFullYear() + +acc.year);
                        tmpDate.setMonth(tmpDate.getMonth() + +acc.month);
                        tmpDate.setDate(tmpDate.getDate() + +acc.day + (7 * +acc.week));
                        return !tmpDate || isNaN(tmpDate) ? "" : tmpDate.getFullYear() + pad(tmpDate.getMonth() + 1) + pad(tmpDate.getDate());
                };
                
                return "";
        };
        var getWeekNumber = function(y,m,d) {
                var d = new Date(y, m, d, 0, 0, 0);
                var DoW = d.getDay();
                d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
                var ms = d.valueOf(); // GMT
                d.setMonth(0);
                d.setDate(4); // Thu in Week 1
                return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
        };
        var printFormattedDate = function(date, fmt, useImportedLocale) {
                if(!date || isNaN(date)) { return ""; };
                
                var parts = fmt.split("-"),
                      str = [],
                        d = date.getDate(),
                        D = date.getDay(),
                        m = date.getMonth(),
                        y = date.getFullYear(),
                    flags = {
                                "sp":" ",
                                "dt":".",
                                "sl":"/",
                                "ds":"-",
                                "cc":",",
                                "d":pad(d),
                                "D":useImportedLocale ? localeImport.dayAbbrs[D == 0 ? 6 : D - 1] : localeDefaults.dayAbbrs[D == 0 ? 6 : D - 1],
                                "l":useImportedLocale ? localeImport.fullDays[D == 0 ? 6 : D - 1] : localeDefaults.fullDays[D == 0 ? 6 : D - 1],
                                "j":d,
                                "N":D == 0 ? 7 : D,
                                "w":D,
                                /*"S":String(d).substr(-(Math.min(String(d).length, 2))) > 3 && String(d).substr(-(Math.min(String(d).length, 2))) < 21 ? "th" : ["th", "st", "nd", "rd", "th"][Math.min(+d%10, 4)],*/
                                "z":"?",
                                "W":getWeekNumber(date),
                                "M":useImportedLocale ? localeImport.monthAbbrs[m] : localeDefaults.monthAbbrs[m],
                                "F":useImportedLocale ? localeImport.fullMonths[m] : localeDefaults.fullMonths[m],
                                "m":pad(++m),
                                "n":++m,
                                "t":daysInMonth(++m, y),
                                "Y":y,
                                "o":y,
                                "y":String(y).substr(2,2),
                                "S":["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
                            };

                for(var pt = 0, part; part = parts[pt]; pt++) {                        
                        str.push(!(part in flags) ? "" : flags[part]);
                };

                return str.join("");
        };
        var parseDateString = function(str, fmt) {
                var d     = false,
                    m     = false,
                    y     = false,
                    now   = new Date(),
                    parts = fmt.replace(/-sp(-sp)+/g, "-sp").split("-"),
                    divds = { "dt":".","sl":"/","ds":"-","cc":"," };                    

                loopLabel:
                for(var pt = 0, part; part = parts[pt]; pt++) {                        
                        if(str.length == 0) { return false; };
                                
                        switch(part) {
                                // Dividers
                                case "sp": // Space " "
                                                if(str.charAt(0).search(/\s/) != -1) {
                                                        // Be easy on multiple spaces...
                                                        while(str.charAt(0).search(/\s/) != -1) { str = str.substr(1); };
                                                        break;
                                                } else return "";
                                case "dt":
                                case "sl":
                                case "ds":
                                case "cc":
                                                if(str.charAt(0) == divds[part]) {
                                                        str = str.substr(1);
                                                        break;
                                                } else return "";
                                // DAY
                                case "d": // Day of the month, 2 digits with leading zeros (01 - 31)
                                case "j": // Day of the month without leading zeros (1 - 31)  
                                          // Accept both when parsing                                                          
                                                if(str.search(/^(3[01]|[12][0-9]|0?[1-9])/) != -1) {
                                                        d = +str.match(/^(3[01]|[12][0-9]|0?[1-9])/)[0];
                                                        str = str.substr(str.match(/^(3[01]|[12][0-9]|0?[1-9])/)[0].length);                                                        
                                                        break;
                                                } else return "";
                                                
                                case "D": // A textual representation of a day, three letters (Mon - Sun)
                                case "l": // A full textual representation of the day of the week (Monday - Sunday)
                                                l = part == "D" ? localeDefaults.dayAbbrs : localeDefaults.fullDays;
                                                for(var i = 0; i < 7; i++) {
                                                        if(new RegExp("^" + l[i], "i").test(str)) {
                                                                str = str.substr(l[i].length);
                                                                continue loopLabel;
                                                        };
                                                };
                                                return "";
                                /*
                                case "j": // Day of the month without leading zeros (1 - 31)
                                                if(str.search(/^([1-9]|[12][0-9]|3[01])/) != -1) {
                                                        d = +str.match(/^([1-9]|[12][0-9]|3[01])/)[0];
                                                        str = str.substr(str.match(/^(\s?[1-9]|[12][0-9]|3[01])/)[0].length);
                                                        break;
                                                } else return "";
                                */
                                case "N": // ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0) 1 (for Monday) through 7 (for Sunday)
                                case "w": // Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)
                                                if(str.search(part == "N" ? /^([1-7])/ : /^([0-6])/) != -1) {
                                                        str = str.substr(1);
                                                        break;
                                                } else return "";
                                case "S": // English ordinal suffix for the day of the month, 2 characters: st, nd, rd or th
                                                if(str.search(/^(st|nd|rd|th)/i) != -1) {
                                                        str = str.substr(2);
                                                        break;
                                                } else return "";
                                case "z": // The day of the year (starting from 0): 0 - 365
                                                if(str.search(/^([0-9]|[1-9][0-9]|[12][0-9]{2}|3[0-5][0-9]|36[0-5])/) != -1) {
                                                        str = str.substr(str.match(/^([0-9]|[1-9][0-9]|[12][0-9]{2}|3[0-5][0-9]|36[0-5])/)[0].length);
                                                        break;
                                                } else return "";
                                // WEEK
                                case "W": // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0): 1 - 53
                                                if(str.search(/^([1-9]|[1234[0-9]|5[0-3])/) != -1) {
                                                        str = str.substr(str.match(/^([1-9]|[1234[0-9]|5[0-3])/)[0].length);
                                                        break;
                                                } else return "";
                                // MONTH
                                case "M": // A short textual representation of a month, three letters
                                case "F": // A full textual representation of a month, such as January or March
                                                l = localeDefaults.fullMonths.concat(localeDefaults.monthAbbrs); // : localeDefaults.fullMonths;
                                                for(var i = 0; i < 24; i++) {
                                                        if(str.search(new RegExp("^" + l[i],"i")) != -1) {
                                                                str = str.substr(l[i].length);
                                                                m = ((i + 12) % 12);                                                                 
                                                                continue loopLabel;
                                                        };
                                                };
                                                return "";
                                case "m": // Numeric representation of a month, with leading zeros
                                case "n": // Numeric representation of a month, without leading zeros
                                                //l = part == "m" ? /^(0[1-9]|1[012])/ : /^([1-9]|1[012])/;
                                                // Accept either when parsing
                                                l = /^(1[012]|0?[1-9])/;
                                                if(str.search(l) != -1) {
                                                        m = +str.match(l)[0] - 1;
                                                        str = str.substr(str.match(l)[0].length);
                                                        break;
                                                } else return "";
                                case "t": // Number of days in the given month: 28 through 31
                                                if(str.search(/2[89]|3[01]/) != -1) {
                                                        str = str.substr(2);
                                                        break;
                                                } else return "";
                                // YEAR
                                case "Y": // A full numeric representation of a year, 4 digits
                                case "o": // ISO-8601 year number. This has the same value as Y
                                                if(str.search(/^(\d{4})/) != -1) {
                                                        y = str.substr(0,4);
                                                        str = str.substr(4);
                                                        break;
                                                } else return "";
                                case "y": // A two digit representation of a year
                                                if(str.search(/^(0[0-9]|[1-9][0-9])/) != -1) {
                                                        y = +str.substr(0,2);
                                                        y = +y < 50 ? '20' + y : '19' + y;
                                                        str = str.substr(2);
                                                        break;
                                                } else return "";
                                default:
                                                return "";
                        };
                };   
                
                d = d === false ? now.getDate() : d;
                m = m === false ? now.getMonth() - 1 : m;
                y = y === false ? now.getFullYear() : y;
                   
                var tmpDate = new Date(y,m,d);
                return isNaN(tmpDate) ? "" : tmpDate;
        };
        var repositionDatePickers = function(e) {
                for(dp in datePickers) {
                        if(!datePickers[dp].created || datePickers[dp].staticPos || (!datePickers[dp].staticPos && !datePickers[dp].dragDisabled)) continue;
                        datePickers[dp].reposition();
                };
        };
        var addDatePicker = function(options) {
                if(!options.id) { throw "A datePicker requires an associated element with an id attribute"; };
                if(options.id in datePickers) { return; };
                var elem = document.getElementById(options.id);
                if(!elem) throw "Cannot locate a datePicker's associated element with an id of:" + options.id;
                if(elem.tagName.search(/select|input/i) == -1) {
                        if(!("callbacks" in options) || !("dateselect" in options.callbacks)) {
                                throw "A 'dateselect' callback function is required for datePickers not associated with a form element";
                        };
                        options.staticPos       = true;
                        options.splitDate       = false;
                        options.hideInput       = false;
                        options.noFadeEffect    = true;
                        options.dragDisabled    = true;
                        options.positioned      = false;
                } else if(!options.staticPos) {
                        options.hideInput       = false;                                                 
                } else {
                        options.noFadeEffect    = true;
                        options.dragDisabled    = true;
                };

                datePickers[options.id] = new datePicker(options);
        };
        var parseCallbacks = function(cbs) {
                if(cbs == null) { return {}; };
                var func,
                    type,
                    cbObj = {},
                    parts,
                    obj;
                for(var i = 0, fn; fn = cbs[i]; i++) {
                        type = fn.match(/(cb_(dateselect|redraw|create)_)([^\s|$]+)/i)[1].replace(/^cb_/i, "").replace(/_$/, "");
                        fn   = fn.replace(/cb_(dateselect|redraw|create)_/i, "").replace(/-/g, ".");
                        
                        try {
                                if(fn.indexOf(".") != -1) {
                                        parts = fn.split('.');
                                        obj   = window;
                                        for (var x = 0, part; part = obj[parts[x]]; x++) {
                                                if(part instanceof Function) {
                                                        (function() {
                                                                var method = part;
                                                                func = function (data) { method.apply(obj, [data]) };
                                                        })();
                                                } else {
                                                obj = part;
                                                };
                                        };
                                } else {
                                        func = window[fn];
                                };

                                if(!(func instanceof Function)) continue;
                                if(!(type in cbObj)) { cbObj[type] = []; };
                                cbObj[type][cbObj[type].length] = func;
                        } catch (err) {};
                };
                return cbObj;
        };
        // Used by the button to dictate whether to open or close the datePicker
        var isVisible = function(id) {
                return (!id || !(id in datePickers)) ? false : datePickers[id].visible;
        };                
        var create = function(inp) {
                if(!(typeof document.createElement != "undefined" && typeof document.documentElement != "undefined" && typeof document.documentElement.offsetWidth == "number")) { return; };

                // Has the locale file loaded?
                if(typeof(fdLocale) == "object" && !localeImport) {
                        localeImport = {
                                titles          : fdLocale.titles,
                                fullMonths      : fdLocale.fullMonths,
                                monthAbbrs      : fdLocale.monthAbbrs,
                                fullDays        : fdLocale.fullDays,
                                dayAbbrs        : fdLocale.dayAbbrs,
                                firstDayOfWeek  : ("firstDayOfWeek" in fdLocale) ? fdLocale.firstDayOfWeek : 0,
                                imported        : true
                        };
                } else if(!localeImport) {
                        localeImport = localeDefaults;
                };  
                
                var formElements = (inp && inp.tagName) ? [inp] : joinNodeLists(document.getElementsByTagName('input'), document.getElementsByTagName('select')),
                    disableDays  = /disable-days-([1-7]){1,6}/g,
                    highlight    = /highlight-days-([1-7]{1,7})/,
                    rangeLow     = /range-low-(((\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01]))|((\d)-(day|week|month|year))|(today))/,
                    rangeHigh    = /range-high-(((\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01]))|((\d)-(day|week|month|year))|(today))/,
                    dateFormat   = /dateformat(-((sp|dt|sl|ds|cc)|([d|D|l|j|N|w|S|z|W|M|F|m|n|t|Y|o|y|O|p])))+/,
                    statusFormat = /statusformat(-((sp|dt|sl|ds|cc)|([d|D|l|j|N|w|S|z|W|M|F|m|n|t|Y|o|y|O|p])))+/,                    
                    disableDates = /disable((-(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])){2}|(-((\d\d\d\d)|(xxxx))((0[1-9]|1[012])|(xx))(0[1-9]|[12][0-9]|3[01])))/g,
                    enableDates  = /enable((-(\d\d\d\d)(0[1-9]|1[012])(0[1-9]|[12][0-9]|3[01])){2}|(-((\d\d\d\d)|(xxxx))((0[1-9]|1[012])|(xx))(0[1-9]|[12][0-9]|3[01])))/g,
                    callbacks    = /((cb_(dateselect|redraw|create)_)([^\s|$]+))/ig,
                    positioned   = /display-inline-([^\s|$]+)/i,
                    bPositioned  = /button-([^\s|$]+)/i,
                    range,tmp,j,t,options,dts,parts;                  
                    
                for(var i = 0, elem; elem = formElements[i]; i++) {  
                        if(elem.className && (elem.className.search(dateFormat) != -1 || elem.className.search(/split-date/) != -1) && ((elem.tagName.toLowerCase() == "input" && (elem.type == "text" || elem.type == "hidden")) || elem.tagName.toLowerCase() == "select")) {
                                
                                if(elem.id && elem.id in datePickers) {                                                                                                                        
                                        if(!datePickers[elem.id].staticPos) { datePickers[elem.id].createButton(); }
                                        else { 
                                                if(!document.getElementById("fd-" + elem.id)) {
                                                        datePickers[elem.id].created = false;                                                         
                                                        datePickers[elem.id].create();                                                     
                                                } else if(inp) {    
                                                        // Only do this if called from an ajax update etc                                                    
                                                        datePickers[elem.id].setDateFromInput();  
                                                        datePickers[elem.id].updateTable();      
                                                };                                        
                                        };                                          
                                        continue;
                                };
                                
                                if(!elem.id) { elem.id = "fdDatePickerInput-" + uniqueId++; };
                                
                                options = {
                                        id:elem.id,
                                        low:"",
                                        high:"",
                                        format:"d-sl-m-sl-Y",
                                        statusFormat:"",
                                        monthsToShow:1,
                                        highlightDays:[0,0,0,0,0,1,1],
                                        disabledDays:[0,0,0,0,0,0,0],
                                        disabledDates:{},
                                        enabledDates:{},
                                        noFadeEffect:elem.className.search(/no-animation/i) != -1,
                                        staticPos:elem.className.search(/display-inline/i) != -1,
                                        hideInput:elem.className.search(/hide-input/i) != -1,
                                        showWeeks:elem.className.search(/show-week/i) != -1,
                                        dragDisabled:nodrag ? true : elem.className.search(/disable-drag/i) != -1,
                                        positioned:false,
                                        firstDayOfWeek:localeImport.firstDayOfWeek,
                                        fillGrid:elem.className.search(/fill-grid/i) != -1,
                                        constrainSelection:elem.className.search(/fill-grid-no-select/i) != -1,
                                        callbacks:parseCallbacks(elem.className.match(callbacks)),
                                        buttonWrapper:""
                                };                            
                                
                                // Positioning of static dp's
                                if(options.staticPos && elem.className.search(positioned) != -1) {
                                        options.positioned = elem.className.match(positioned)[1];                                        
                                };
                                
                                // Positioning of non-static dp's button
                                if(!options.staticPos && elem.className.search(bPositioned) != -1) {
                                        options.buttonWrapper = elem.className.match(bPositioned)[1];                                        
                                };
                                
                                // Opacity of non-static datePickers
                                if(!options.staticPos) {
                                        options.finalOpacity = elem.className.search(/opacity-([1-9]{1}[0-9]{1})/i) != -1 ? elem.className.match(/opacity-([1-9]{1}[0-9]{1})/i)[1] : 90                              
                                };
                                
                                // Dates to disable
                                dts = elem.className.match(disableDates);
                                if(dts) {
                                        for(t = 0; t < dts.length; t++) {
                                                parts = dts[t].replace(/xxxx/, "****").replace(/xx/, "**").replace("disable-", "").split("-");
                                                options.disabledDates[parts[0]] = (parts.length && parts.length == 2) ? parts[1] : 1;                                                
                                        };
                                };

                                // Dates to enable
                                dts = elem.className.match(enableDates);
                                if(dts) {
                                        for(t = 0; t < dts.length; t++) {
                                                parts = dts[t].replace(/xxxx/, "****").replace(/xx/, "**").replace("enable-", "").split("-");
                                                options.enabledDates[parts[0]] = (parts.length && parts.length == 2) ? parts[1] : 1;                                                
                                        };
                                };
                                             
                                // Split the date into three parts ?                                
                                options.splitDate = (elem.className.search(/split-date/) != -1 && document.getElementById(elem.id+splitAppend[0]) && document.getElementById(elem.id+splitAppend[1]) && document.getElementById(elem.id+splitAppend[0]).tagName.search(/input|select/i) != -1 && document.getElementById(elem.id+splitAppend[1]).tagName.search(/input|select/i) != -1);                              
                                
                                // Date format
                                if(!options.splitDate && elem.className.search(dateFormat) != -1) {
                                        options.format = elem.className.match(dateFormat)[0].replace('dateformat-','');
                                };

                                // Status bar date format
                                if(elem.className.search(statusFormat) != -1) {
                                        options.statusFormat = elem.className.match(statusFormat)[0].replace('statusformat-','');
                                };
                                
                                // The days of the week to highlight
                                if(elem.className.search(highlight) != -1) {
                                        tmp = elem.className.match(highlight)[0].replace(/highlight-days-/, '');
                                        options.highlightDays = [0,0,0,0,0,0,0];
                                        for(j = 0; j < tmp.length; j++) {
                                                options.highlightDays[tmp.charAt(j) - 1] = 1;
                                        };
                                };
                                
                                // The days of the week to disable
                                if(elem.className.search(disableDays) != -1) {
                                        tmp = elem.className.match(disableDays)[0].replace(/disable-days-/, '');
                                        options.disabledDays = [0,0,0,0,0,0,0];                                         
                                        for(j = 0; j < tmp.length; j++) {
                                                options.disabledDays[tmp.charAt(j) - 1] = 1;
                                        };
                                };

                                // The lower limit
                                if(elem.className.search(rangeLow) != -1) {
                                        options.low = parseRangeFromString(elem.className.match(rangeLow)[0]);
                                };

                                // The higher limit
                                if(elem.className.search(rangeHigh) != -1) {
                                        options.high = parseRangeFromString(elem.className.match(rangeHigh)[0]);
                                };

                                // Always round lower & higher limits if a selectList involved
                                if(elem.tagName.search(/select/i) != -1) {
                                        range        = grepRangeLimits(elem);
                                        options.low  = options.low  ? range[0] + String(options.low).substr(4,4)  : range[0] + "0101";
                                        options.high = options.high ? range[1] + String(options.high).substr(4,4) : range[1] + "1231";
                                };

                                addDatePicker(options);
                        };
                };
        };

        addEvent(window, 'load',   create);
        addEvent(window, 'unload', destroy);
        addEvent(window, 'resize', repositionDatePickers);

        return {
                addEvent:               function(obj, type, fn) { return addEvent(obj, type, fn); },
                removeEvent:            function(obj, type, fn) { return removeEvent(obj, type, fn); },
                stopEvent:              function(e) { return stopEvent(e); },
                show:                   function(inpID) { return showDatePicker(inpID); },
                create:                 function(inp) { create(inp); },                 
                repositionDatePickers:  function() { repositionDatePickers(); },
                newDatePicker:          function(opts) { addDatePicker(opts); },
                overrideAppendID:       function(arr) { splitAppend = (arr && arr.length && arr.length == 2) ? arr : splitAppend },
                printFormattedDate:     function(dt, fmt, useImportedLocale) { return printFormattedDate(dt, fmt, useImportedLocale); },
                setDateFromInput:       function(inpID) { if(!inpID || !(inpID in datePickers) || !datePickers[inpID].created) return false; datePickers[inpID].setDateFromInput(); },
                setRangeLow:            function(inpID, yyyymmdd) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setRangeLow(yyyymmdd); },
                setRangeHigh:           function(inpID, yyyymmdd) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setRangeHigh(yyyymmdd); },
                parseDateString:        function(str, format) { return parseDateString(str, format); },
                disableDrag:            function() { noDrag = true; },
                setGlobalVars:          function(json) { affectJSON(json); },
                addDisabledDates:       function(inpID, dts) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].addDisabledDates(dts); },
                setDisabledDates:       function(inpID, dts) { if(!inpID || !(inpID in datePickers)) return false; datePickers[inpID].setDisabledDates(dts); }                                              
        }; 
})();

// Change this to use your own month & day id appendages
// It can also be passed using JSON within the script tag 
// datePickerController.overrideAppendID(["Day", "Month"]);