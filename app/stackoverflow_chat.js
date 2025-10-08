// Automatically combined file using master-chat.jsbundle

// Begin PartialJS/masterAndMasterChat.jsbundle

// Automatically combined file using PartialJS/masterAndMasterChat.jsbundle

// Begin PartialJS/namespace.js

window.CHAT = {
    RoomUsers: {}
};
if (/^#nonewmob;/.test(location.hash)) {
    location.hash = location.hash.substr(10);
    if (location.hash.length <= 1 && window.history && window.history.replaceState)
        history.replaceState(null, null, location.href.replace(/#$/, "")); // remove the ugly lonely "#"
}


// End PartialJS/namespace.js

// Begin PartialJS/../third-party/jquery.scrollTo.min.js

/**
 * Copyright (c) 2007-2015 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
 * Licensed under MIT
 * @author Ariel Flesler
 * @version 2.1.2
 */
; (function (f) { "use strict"; "function" === typeof define && define.amd ? define(["jquery"], f) : "undefined" !== typeof module && module.exports ? module.exports = f(require("jquery")) : f(jQuery) })(function ($) { "use strict"; function n(a) { return !a.nodeName || -1 !== $.inArray(a.nodeName.toLowerCase(), ["iframe", "#document", "html", "body"]) } function h(a) { return $.isFunction(a) || $.isPlainObject(a) ? a : { top: a, left: a } } var p = $.scrollTo = function (a, d, b) { return $(window).scrollTo(a, d, b) }; p.defaults = { axis: "xy", duration: 0, limit: !0 }; $.fn.scrollTo = function (a, d, b) { "object" === typeof d && (b = d, d = 0); "function" === typeof b && (b = { onAfter: b }); "max" === a && (a = 9E9); b = $.extend({}, p.defaults, b); d = d || b.duration; var u = b.queue && 1 < b.axis.length; u && (d /= 2); b.offset = h(b.offset); b.over = h(b.over); return this.each(function () { function k(a) { var k = $.extend({}, b, { queue: !0, duration: d, complete: a && function () { a.call(q, e, b) } }); r.animate(f, k) } if (null !== a) { var l = n(this), q = l ? this.contentWindow || window : this, r = $(q), e = a, f = {}, t; switch (typeof e) { case "number": case "string": if (/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(e)) { e = h(e); break } e = l ? $(e) : $(e, q); case "object": if (e.length === 0) return; if (e.is || e.style) t = (e = $(e)).offset() }var v = $.isFunction(b.offset) && b.offset(q, e) || b.offset; $.each(b.axis.split(""), function (a, c) { var d = "x" === c ? "Left" : "Top", m = d.toLowerCase(), g = "scroll" + d, h = r[g](), n = p.max(q, c); t ? (f[g] = t[m] + (l ? 0 : h - r.offset()[m]), b.margin && (f[g] -= parseInt(e.css("margin" + d), 10) || 0, f[g] -= parseInt(e.css("border" + d + "Width"), 10) || 0), f[g] += v[m] || 0, b.over[m] && (f[g] += e["x" === c ? "width" : "height"]() * b.over[m])) : (d = e[m], f[g] = d.slice && "%" === d.slice(-1) ? parseFloat(d) / 100 * n : d); b.limit && /^\d+$/.test(f[g]) && (f[g] = 0 >= f[g] ? 0 : Math.min(f[g], n)); !a && 1 < b.axis.length && (h === f[g] ? f = {} : u && (k(b.onAfterFirst), f = {})) }); k(b.onAfter) } }) }; p.max = function (a, d) { var b = "x" === d ? "Width" : "Height", h = "scroll" + b; if (!n(a)) return a[h] - $(a)[b.toLowerCase()](); var b = "client" + b, k = a.ownerDocument || a.document, l = k.documentElement, k = k.body; return Math.max(l[h], k[h]) - Math.min(l[b], k[b]) }; $.Tween.propHooks.scrollLeft = $.Tween.propHooks.scrollTop = { get: function (a) { return $(a.elem)[a.prop]() }, set: function (a) { var d = this.get(a); if (a.options.interrupt && a._last && a._last !== d) return $(a.elem).stop(); var b = Math.round(a.now); d !== b && ($(a.elem)[a.prop](b), a._last = this.get(a)) } }; return p });

// End PartialJS/../third-party/jquery.scrollTo.min.js

// Begin PartialJS/../third-party/jquery.cookie.js

/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * https://www.opensource.org/licenses/mit-license.php
 * https://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Create a cookie with the given name and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String name The name of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given name.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String name The name of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

// End PartialJS/../third-party/jquery.cookie.js

// Begin PartialJS/../third-party/jquery.preload.js

/**
 * jQuery.Preload
 * Copyright (c) 2008 Ariel Flesler - aflesler(at)gmail(dot)com
 * Dual licensed under MIT and GPL.
 * Date: 3/25/2009
 *
 * Small addition 2010/11/3 to get around the load event not being fired for already available images
 *
 * @projectDescription Multifunctional preloader
 * @author Ariel Flesler
 * @version 1.0.8
 *
 * @id jQuery.preload
 * @param {String, jQuery, Array< String, <a>, <link>, <img> >} original Collection of sources to preload
 * @param {Object} settings Hash of settings.
 *
 * @id jQuery.fn.preload
 * @param {Object} settings Hash of settings.
 * @return {jQuery} Returns the same jQuery object, for chaining.
 *
 * @example Link Mode:
 *	$.preload( '#images a' );
 *
 * @example Rollover Mode:
 *	$.preload( '#images img', {
 *		find:/\.(gif|jpg)/,
 *		replace:'_over.$1'
 *	});
 *
 * @example Src Mode:
 *	$.preload( [ 'red', 'blue', 'yellow' ], {
 *		base:'images/colors/',
 *		ext:'.jpg'
 *	});
 *
 * @example Placeholder Mode:
 *	$.preload( '#images img', {
 *		placeholder:'placeholder.jpg',
 *		notFound:'notfound.jpg'
 *	});
 *
 * @example Placeholder+Rollover Mode(High res):
 *	$.preload( '#images img', {
 *		placeholder:true,
 *		find:/\.(gif|jpg)/,
 *		replace:'_high.$1'
 *	});
 */

;(function( $ ){

    var $preload = $.preload = function( original, settings ){
        if( original.split ) // selector
            original = $(original);

        settings = $.extend( {}, $preload.defaults, settings );
        var sources = $.map( original, function( source ){
            if( !source )
                return; // skip
            if( source.split ) // URL Mode
                return settings.base + source + settings.ext;
            var url = source.src || source.href; // save the original source
            if( typeof settings.placeholder == 'string' && source.src ) // Placeholder Mode, if it's an image, set it.
                source.src = settings.placeholder;
            if( url && settings.find ) // Rollover mode
                url = url.replace( settings.find, settings.replace );
            return url || null; // skip if empty string
        });

        var data = {
            loaded:0, // how many were loaded successfully
            failed:0, // how many urls failed
            next:0, // which one's the next image to load (index)
            done:0, // how many urls were tried
            /*
            index:0, // index of the related image
            found:false, // whether the last one was successful
            */
            total:sources.length // how many images are being preloaded overall
        };

        if( !data.total ) // nothing to preload
            return finish();

        var imgs = $(Array(settings.threshold+1).join('<img/>'))
            .load(handler).error(handler).bind('abort',handler).each(fetch);

        function handler( e ){
            data.element = this;
            data.found = e.type == 'load';
            data.image = this.src;
            data.index = this.index;
            var orig = data.original = original[this.index];
            data[data.found?'loaded':'failed']++;
            data.done++;

            // This will ensure that the images aren't "un-cached" after a while
            if( settings.enforceCache )
                $preload.cache.push(
                    $('<img/>').attr('src',data.image)[0]
                );

            if( settings.placeholder && orig.src ) // special case when on placeholder mode
                orig.src = data.found ? data.image : settings.notFound || orig.src;
            if( settings.onComplete )
                settings.onComplete( data );
            if( data.done < data.total ) // let's continue
                fetch( 0, this );
            else{ // we are finished
                if( imgs && imgs.unbind )
                    imgs.unbind('load').unbind('error').unbind('abort'); // cleanup
                imgs = null;
                finish();
            }
        };
        function fetch( i, img, retry ){
            // IE problem, can't preload more than 15
            if( img.attachEvent /* msie */ && data.next && data.next % $preload.gap == 0 && !retry ){
                setTimeout(function(){ fetch( i, img, true ); }, 0);
                return false;
            }
            if( data.next == data.total ) return false; // no more to fetch
            img.index = data.next; // save it, we'll need it.
            img.src = sources[data.next++];
            if( settings.onRequest ){
                data.index = img.index;
                data.element = img;
                data.image = img.src;
                data.original = original[data.next-1];
                settings.onRequest( data );
            }

            // (our custom addition to the plugin: if the image is already available, the browser may never
            // fire the load event, so we force it)
            if (img.complete) {
                $(img).trigger("load");
            }
        };
        function finish(){
            if( settings.onFinish )
                settings.onFinish( data );
        };
    };

    // each time we load this amount and it's IE, we must rest for a while, make it lower if you get stack overflow.
    $preload.gap = 14;
    $preload.cache = [];

    $preload.defaults = {
        threshold:2, // how many images to load simultaneously
        base:'', // URL mode: a base url can be specified, it is prepended to all string urls
        ext:'', // URL mode:same as base, but it's appended after the original url.
        replace:'' // Rollover mode: replacement (can be left empty)
        /*
        enforceCache: false, // If true, the plugin will save a copy of the images in $.preload.cache
        find:null, // Rollover mode: a string or regex for the replacement
        notFound:'' // Placeholder Mode: Optional url of an image to use when the original wasn't found
        placeholder:'', // Placeholder Mode: url of an image to set while loading
        onRequest:function( data ){ ... }, // callback called every time a new url is requested
        onComplete:function( data ){ ... }, // callback called every time a response is received(successful or not)
        onFinish:function( data ){ ... } // callback called after all the images were loaded(or failed)
        */
    };

    $.fn.preload = function( settings ){
        $preload( this, settings );
        return this;
    };

    // SE change:
    // IE barfs when accessing (even reading, but especially writing)
    // image.src if it's a particularly broken URL (http://meta.stackexchange.com/a/250589).
    // This can prevent a room from loading at all. Thus we wrap the preload plugin
    // in try/catch.
    (function () {
        var originalFnPreload = $.fn.preload;
        var originalPreload = $.preload;
        $.fn.preload = function () {
            try {
                return originalPreload.apply(this, arguments);
            } catch (ex) {
                return this;
            }
        }
        $.preload = function () {
            try {
                originalPreload.apply(this, arguments);
            } catch (ex) {
            }
        }
    })();

})( jQuery );

// End PartialJS/../third-party/jquery.preload.js

// Begin PartialJS/../third-party/jquery.typewatch.js

/*
 *	TypeWatch 2.0 - Original by Denny Ferrassoli / Refactored by Charles Christolini
 *
 *	Examples/Docs: www.dennydotnet.com
 *
 *  Copyright(c) 2007 Denny Ferrassoli - DennyDotNet.com
 *  Coprright(c) 2008 Charles Christolini - BinaryPie.com
 *
 *  Dual licensed under the MIT and GPL licenses:
 *  https://www.opensource.org/licenses/mit-license.php
 *  https://www.gnu.org/licenses/gpl.html
*/

(function(jQuery) {
    jQuery.fn.typeWatch = function(o){
        // Options
        var options = jQuery.extend({
            wait : 750,
            callback : function() { },
            highlight : true,
            captureLength : 2
        }, o);

        function checkElement(timer, override) {
            var elTxt = jQuery(timer.el).val();

            // Fire if text > options.captureLength AND text != saved txt OR if override AND text > options.captureLength
            if ((elTxt.length > options.captureLength && elTxt.toUpperCase() != timer.text)
                || (override && elTxt.length > options.captureLength)) {
                timer.text = elTxt.toUpperCase();
                timer.cb(elTxt);
            }
        };

        function watchElement(elem) {
            // Must be text or textarea
            if (elem.type.toUpperCase() == "TEXT" || elem.nodeName.toUpperCase() == "TEXTAREA") {

                // Allocate timer element
                var timer = {
                    timer : null,
                    text : jQuery(elem).val().toUpperCase(),
                    cb : options.callback,
                    el : elem,
                    wait : options.wait
                };

                // Set focus action (highlight)
                if (options.highlight) {
                    jQuery(elem).focus(
                        function() {
                            this.select();
                        });
                }

                // Key watcher / clear and reset the timer
                var startWatch = function(evt) {
                    var timerWait = timer.wait;
                    var overrideBool = false;

                    if (evt.keyCode == 13 && this.type.toUpperCase() == "TEXT") {
                        timerWait = 1;
                        overrideBool = true;
                    }

                    var timerCallbackFx = function()
                    {
                        checkElement(timer, overrideBool)
                    }

                    // Clear timer
                    clearTimeout(timer.timer);
                    timer.timer = setTimeout(timerCallbackFx, timerWait);

                };

                jQuery(elem).keydown(startWatch);
            }
        };

        // Watch Each Element
        return this.each(function(index){
            watchElement(this);
        });

    };

})(jQuery);

// End PartialJS/../third-party/jquery.typewatch.js

// Begin PartialJS/../third-party/lyfe.js

/*!
 * Copyright (c) 2011, 2012, 2013 Benjamin Dumke-von der Ehe
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

(function (global) {

    var arrIndexOf;
    if (Array.prototype.indexOf) {
        arrIndexOf = function (arr, val) { return arr.indexOf(val); };
    } else {
        arrIndexOf = function (arr, val) {
            var len = arr.length;
            for (var i = 0; i < len; i++)
                if (i in arr && arr[i] === val)
                    return i;
            return -1;
        };
    }

    var BreakIteration = {};

    var Generator = function (source) {
        if (!(this instanceof Generator))
            return new Generator(source);

        if (typeof source === "function")
            this.forEach = makeForEach_fromFunction(source);
        else if (source.constructor === Array)
            this.forEach = makeForEach_fromArray(source);
        else
            this.forEach = makeForEach_fromObject(source);
    };

    var asGenerator = function (source) {
        if (source instanceof Generator)
            return source;

        return new Generator(source);
    };

    var stopIteration = function () {
        throw BreakIteration;
    };

    var IterationError = function (message) {
        this.message = message;
        this.name = "IterationError";
    };
    IterationError.prototype = Error.prototype;

    var makeForEach_fromFunction = function (f) {
        return function (g, thisObj) {
            var stopped = false,
                index = 0,
                Yield = function (val) {
                    if (stopped)
                        throw new IterationError("yield after end of iteration");
                    var send = g.call(thisObj, val, index, stopIteration);
                    index++;
                    return send;
                },
                yieldMany = function (source) {
                    asGenerator(source).forEach(function (val) { Yield(val); })
                };
            try {
                f(Yield, yieldMany, stopIteration);
            } catch (ex) {
                if (ex !== BreakIteration)
                    throw ex;
            } finally {
                stopped = true;
            }
        };
    };

    var makeForEach_fromArray = function (arr) {
        return makeForEach_fromFunction(function (Yield) {
            var len = arr.length;
            for (var i = 0; i < len; i++)
                if (i in arr)
                    Yield(arr[i]);
        });
    };

    var makeForEach_fromObject = function (obj) {
        return makeForEach_fromFunction(function (Yield) {
            for (var key in obj)
                if (obj.hasOwnProperty(key))
                    Yield([key, obj[key]]);
        });
    };

    var selector = function (f) {
        if (typeof f === "string")
            return function (o) { return o[f]; }
        return f;
    };

    Generator.prototype = {
        toArray: function () {
            var result = [];
            this.forEach(function (val) { result.push(val); });
            return result;
        },
        filter: function (pred, thisObj) {
            var source = this;
            pred = selector(pred);
            return new Generator(function (Yield) {
                source.forEach(function (val) {
                    if (pred.call(thisObj, val))
                        Yield(val);
                });
            });
        },
        take: function (n) {
            var source = this;
            return new Generator(function (Yield) {
                source.forEach(function (val, index, stop) {
                    if (index >= n)
                        stop();
                    Yield(val);
                });
            });
        },
        skip: function (n) {
            var source = this;
            return new Generator(function (Yield) {
                source.forEach(function(val, index) {
                    if (index >= n)
                        Yield(val);
                });
            });
        },
        map: function (f, thisObj) {
            var source = this;
            f = selector(f);
            return new Generator(function (Yield) {
                source.forEach(function (val) {
                    Yield(f.call(thisObj, val));
                });
            });
        },
        zipWithArray: function (arr, zipper) {
            if (typeof zipper === "undefined")
                zipper = function (a, b) { return [a, b]; };

            var source = this;

            return new Generator(function (Yield) {
                var len = arr.length,
                    delta = 0;

                source.forEach(function (val, index, stop) {
                    while (!(index + delta in arr) && index + delta < len)
                        delta++;
                    if (index + delta >= len)
                        stop();

                    Yield(zipper(val, arr[index + delta]));
                });
            });
        },
        reduce: function (f, firstValue) {
            var first,
                current;

            if (arguments.length < 2) {
                first = true;
            } else {
                first = false;
                current = firstValue;
            }

            this.forEach(function (val) {
                if (first) {
                    current = val;
                    first = false;
                } else {
                    current = f(current, val);
                }
            });
            return current;
        },
        and: function (other) {
            var source = this;
            return new Generator(function (Yield, yieldMany) {
                yieldMany(source);
                yieldMany(other);
            });
        },
        takeWhile: function (pred) {
            var source = this;
            pred = selector(pred);
            return new Generator(function (Yield) {
                source.forEach(function (val, index, stop) {
                    if (pred(val))
                        Yield(val);
                    else
                        stop();
                });
            });
        },
        skipWhile: function (pred) {
            var source = this;
            pred = selector(pred);
            return new Generator(function (Yield) {
                var skipping = true;

                source.forEach(function (val) {
                    skipping = skipping && pred(val);
                    if (!skipping)
                        Yield(val);
                });
            });
        },
        all: function (pred) {
            var result = true;
            pred = selector(pred);
            this.forEach(function (val, index, stop) {
                if (!(pred ? pred(val) : val)) {
                    result = false;
                    stop();
                }
            });
            return result;
        },
        any: function (pred) {
            var result = false;
            pred = selector(pred);
            this.forEach(function (val, index, stop) {
                if (pred ? pred(val) : val) {
                    result = true;
                    stop();
                }
            });
            return result;
        },
        first: function () {
            var result;
            this.forEach(function (val, index, stop) {
                result = val;
                stop();
            });
            return result;
        },
        groupBy: function (grouper) {
            var source = this;
            grouper = selector(grouper);
            return new Generator(function (Yield, yieldMany) {
                var groups = [],
                    group_contents = [];

                source.forEach(function (val) {
                    var group = grouper(val);
                    var i = arrIndexOf(groups, group);
                    if (i === -1) {
                        groups.push(group);
                        group_contents.push([val]);
                    } else {
                        group_contents[i].push(val);
                    }
                });

                yieldMany(new Generator(groups).zipWithArray(group_contents, function (group, contents) {
                    var result = new Generator(contents);
                    result.key = group;
                    return result;
                }));
            });
        },
        evaluated: function () {
            return new Generator(this.toArray());
        },
        except: function (what) {
            return this.filter(function (x) { return x !== what; });
        },
        sortBy: function (keyFunc) {
            var source = this;
            keyFunc = selector(keyFunc);
            return new Generator(function (Yield) {
                var arr = source.toArray(),
                    indexes = Range(0, arr.length).toArray(),
                    keys = Generator(arr).map(keyFunc).toArray();

                indexes.sort(function (a, b) {
                    var ka = keys[a],
                        kb = keys[b];
                    if (typeof ka === typeof kb) {
                        if (ka === kb)
                            return a < b ? -1 : 1;
                        if (ka < kb)
                            return -1;
                        if (ka > kb)
                            return 1;
                    }
                    throw new TypeError("cannot compare " + ka + " and " + kb);
                });
                new Generator(indexes).forEach(function (index) {
                    Yield(arr[index]);
                });
            });
        },
        count: function () {
            var result = 0;
            this.forEach(function () { result++; });
            return result;
        }
    };

    var Count = function (start, step) {
        var i = start;
        if (typeof step === "undefined")
            step = 1;
        return new Generator(function (Yield) {
            while (true) {
                Yield(i);
                i += step;
            }
        });
    };

    var Range = function (start, len) {
        return Count(start, 1).take(len);
    };

    var originalGenerator = global.Generator;
    global.Generator = Generator;
    Generator.BreakIteration = BreakIteration;
    Generator.Count = Count;
    Generator.Range = Range;
    Generator.IterationError = IterationError;
    Generator.noConflict = function () {
        global.Generator = originalGenerator;
        return Generator;
    }

})(this);



// End PartialJS/../third-party/lyfe.js

// Begin PartialJS/quote.js


function handleQuoteMessage(orig) {

    var html = $.trim(orig);

    var quote = html.match(/^&gt;\s+(.*)$/);
    if (quote) {
        var span = $("<div/>").addClass("quote").html(quote[1])
        return $("<p/>").append(span).html();
    } else if (/^<div[^>]*?>&gt;/i.test(html)) { // possibly a multi-line quote
        var jDiv = $(html);
        if (jDiv.hasClass("partial") || jDiv.hasClass("full")) {
            jDiv[0].normalize();
            var children = jDiv[0].childNodes;
            var first = true,
                anyQuoted = false,
                anyNotQuoted = false;
            // we go textnode-by-textnode, which essentially means line-by-line
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                if (child.nodeType !== 3)
                    continue;
                if (!/\S/.test(child.nodeValue))
                    continue;
                var thisQuoted = /^\s*>\s/.test(child.nodeValue);
                if (!first) {
                    anyQuoted |= thisQuoted;
                    anyNotQuoted |= !thisQuoted;
                }
                first = false;
                if (thisQuoted) {
                    child.nodeValue = child.nodeValue.replace(/^\s*>\s+/, "");
                }

            }

            // it's a quote if either all of the lines, or the first and only the first line
            // starts with "> "
            if (!anyNotQuoted || !anyQuoted)
                return jDiv.addClass("quote")[0].outerHTML;
        }
    }
    return orig;
}

// End PartialJS/quote.js

// Begin PartialJS/popup.js

/* popup.js */
;
// Be sure to call evt.stopPropagation() on the click event that caused the
// popup to be created, to prevent the click handler in popupDismisser() from
// firing. popUp() will dismiss a possibly present other popup itself.
function popUp(left, top, parent, keepOld) {
    if(!keepOld) $(".popup").remove();
    var css = {};
    var x = left - $(window).scrollLeft();
    var y = top - $(window).scrollTop();
    if (x < $(window).width() / 2)
        css.left = x;
    else
        css.right = $(window).width() - x;
    if (y < $(window).height() / 2)
        css.top = y;
    else
        css.bottom = $(window).height() - y;
    var menu = div("popup").css(css).hide();
    var close = $("<div class='btn-close'>X</div>");
    close.click(function () { $(this).closest(".popup").fadeOut(200, function () { $(this).remove(); }); }).prependTo(menu);

    menu.appendTo(parent || $("body")).fadeIn(200);
    menu.close = function () { menu.fadeOut(200, function () { menu.remove(); }) };
    return menu;
}


function popupDismisser() {
    $(document).click(function (evt) {
        if ($(evt.target).closest(".popup").length == 0 && $(evt.target).closest(".ac_results").length == 0) // the click was *not* on a popup or auto-complete
        {
            $(".popup:not(.mini-help)").fadeOut(200, function () { $(this).remove(); });
        }
    });
    $(document).bind("keydown", function (evt) {
        // note that pressing escape might stop running ajax requests in some
        // browsers -- open to ideas how to handle that. So better use
        // the "click anywhere".
        if (evt.which == 27) // escape
            $(".popup:not(.mini-help)").fadeOut(200, function () { $(this).remove(); })
    });
}
function fkey(body) {
    if (!body) body = {};
    if (!body.fkey) body.fkey = $("input[name='fkey']").attr("value");
    return body;
}
function repNumber(n) {
    if (n < 10000)
        return n;
    else if (n < 100000) {
        var pre = Math.floor(Math.round(n / 100) / 10);
        var post = Math.round((n - pre * 1000) / 100);
        return pre + (post > 0 ? "." + post : "") + "k";
    } else
        return Math.round(n / 1000) + "k";
}
function htmlEncode(s) {
    return document.createElement('div').appendChild(document.createTextNode(s)).parentNode.innerHTML;
}
function selectStackSite(evt, userId, showUser, callback, parent, keepOld) {
    var popup = popUp(evt.pageX, evt.pageY, parent, keepOld).css({ width: "auto", position: "absolute" });
    var ld = $("<p/>").text("Loading available sites...");
    popup.append(ld);
    var body = showUser ? {} : { sort: 'site' };
    var showSites = function (data) {
        if (data && data.length) {
            ld.text('select a site:');
            var inp = $('<input type="text"/>').appendTo(popup);
            inp.autocomplete(data, {
                minChars: 0,
                width: 310,
                matchContains: "word",
                autoFill: false,
                formatItem: function (row, i, max) {
                    return '<img class="icon-16" src="' + row.SiteIcon + '"/> ' + htmlEncode(row.SiteCaption);
                },
                formatMatch: function (row, i, max) {
                    return htmlEncode(row.SiteCaption + " " + row.Host);
                },
                formatResult: function (row) {
                    return row.Host;
                }
            }).result(function log(event, row, formatted) {
                if (row) {
                    popup.close();
                    callback(row.Host, row.SiteCaption, row.SiteIcon);
                }
            }).focus();
        } else {
            ld.text('(no sites found)');
        }
    };
    if (userId) {
        $.post("/users/sites/" + userId, body, showSites);
    } else {
        $.get("/rooms/sites", showSites);
    }

}
function PERMALINK(message_id) {
    return "/transcript/message/" + message_id + '#' + message_id;
}

var moderatorTools = function (notify) {
    var result = {};

    // used in both the full "flagged" page and the popup; hence the tests for whether we're in a <table> or in a <ul>

    var initDismissFlags = function () {
        $(document).on("click", ".quick-unmod", function () {
            var flagContainer = $(this).closest("tr");
            var flagId, messageRemoveContainer;
            if (flagContainer.length) {
                flagId = flagContainer.attr("id").replace("fl-", "");
                if (flagContainer.prev().hasClass("monologue-row") && (flagContainer.next().length == 0 || flagContainer.next().hasClass("monologue-row"))) // it was the last flag on this message
                    messageRemoveContainer = flagContainer.prev();
            } else {
                flagContainer = $(this).closest("li");
                flagId = flagContainer.data("flag_id");
                if (flagContainer.closest("ul").find("li").length == 1) {
                    messageRemoveContainer = flagContainer.closest(".flagged-message");
                }
            }
            var noise = "";
            var noiseConfirm = "";
            if (flagContainer.find("input[name=noise]").prop("checked")) {
                noise = "?noise=true";
                noiseConfirm = " and notify the flagger that moderator flags should only be used for serious issues"
            }

            if (confirm('Dismiss this flag' + noiseConfirm + '?')) {
                $.post("/flags/" + flagId + "/clear" + noise, fkey(), function () {
                    flagContainer.remove();
                    if (messageRemoveContainer)
                        messageRemoveContainer.remove();
                });
            }
        });
    };

    result.initFlagSupport = function (isMod, userId, noDataCallback) {
        $(document).on('click', ".reflag,.counterflag,.mehflag", function (evt) {
            evt.preventDefault();
            var jThis = $(this);
            var row = jThis.closest("tr");
            var loaderContainer;
            var messageId;
            // TODO: switch all instances to proper data-* pattern
            if (row.length) {
                var prevRow = row.prev("tr");
                if (!prevRow.attr("id") || !$.isNumeric(prevRow.attr("id").replace("msg-", ""))) {
                    prevRow = row.prev("tr").prev("tr");
                }
                messageId = prevRow.attr("id").replace("msg-", "");
                loaderContainer = jThis.closest("td");
            } else {
                row = jThis.closest(".flagged-message");
                messageId = row.data("message_id");
                loaderContainer = jThis.parent();
            }

            var verb = jThis.hasClass("reflag") ? "flag" : jThis.hasClass("counterflag") ? "counter-flag" : "meh-flag";
            if (isMod && verb != "meh-flag") {
                if (!confirm("Please note that since you're a moderator, your vote is binding. Continue?")) return;
            }
            var loader = $("<img/>").attr("src", IMAGE("progress-dots.gif")).appendTo(loaderContainer);
            var successCB = function (result) {
                if (result == "ok") {
                    row.fadeOut(function () { $(this).remove(); });
                } else {
                    var tmp = result || GENERIC_ERROR;
                    if (notify && tmp) notify(tmp);
                    loader.remove();
                }
            };
            var errorCB = function (errorMessage) { loader.remove(); if (notify) notify(errorMessage) };
            messageActionById(messageId, verb, null, successCB, errorCB);
        });
        BindFlagListPopup("#flag-count, .global-flags:not(.mod-flag)", "/admin/flagged?json=true&show=new", "/admin/flagged?show=all", notify, noDataCallback);
        BindFlagListPopup("#modflag-count, .global-flags.mod-flag", "/admin/flagged-moderator?json=true", "/admin/flagged-moderator", notify);
        if (isMod)
            initDismissFlags();
    };
    return result;
};

$.fn.fadeOutAndRemove = function () {
    return this.each(function () {
        var toHide = $(this);
        toHide.fadeOut('fast', function () { toHide.trigger('removing').remove(); });
    });
}

// End PartialJS/popup.js

// Begin PartialJS/notify.js

; /* notify.js */
function div(cls) {
    return $("<div/>").addClass(cls);
}
function span(cls) {
    return $("<span/>").addClass(cls);
}

GENERIC_ERROR = "An error occurred performing this action";

function confirmFlag(isMod) {
    return confirm("Do you want to flag this message as spam, inappropriate, or offensive?" +
        (isMod ? " Since you're a moderator, this flag is binding." : ""));
}

// note that this function is also given to the sidebar
function messageActionById(msgid, verb, info, callback, notify) {
    if (!info) info = {};
    $.ajax({
        type: "POST",
        url: "/messages/" + msgid + "/" + verb,
        data: fkey(info),
        success: function (result) {
            if (callback) {
                callback(result);
            } else if (result != "ok" && notify) {
                notify(result || GENERIC_ERROR);
            }
        },
        dataType: "json",
        error: function (xhr, failType) {
            var message = failType == 'error' ? (xhr.status == 409 ? xhr.responseText : GENERIC_ERROR) : failType;
            if (callback)
                callback(message)
            else if (notify)
                notify(message);
        }
    });
}

function Notifier(icc, mobile) {

    var mobile_old = mobile && !CHAT.NEW_MOBILE;
    var mobile_new = mobile && CHAT.NEW_MOBILE;

    if (!icc)
        icc = { broadcast: function () { } };

    // broadcast defaults to true
    function dismissSingleNotification(text, broadcast) {
        if (typeof text != "string")
            text = text.text();
        if (broadcast == undefined || broadcast)
            icc.broadcast({ command: "dismiss notification", notification: text });
        var bar = $(".notification").not(".closing");
        bar.find("p.notification-message").not(".dismissed").each(function () {
            if ($(this).text() == text) {
                $(this).addClass("dismissed").slideUp(function () { $(this).remove(); });
            }
        });
        if (!bar.find("p.notification-message").not(".dismissed").length) {
            bar.slideUp(function () { $(this).remove(); });
        }
    }

    function notify(html, cls) {
        if (html && html.message) html = html.message;
        var bar = $(".notification").not(".closing");
        var waspresent = true;
        if (bar.length == 0) {
            waspresent = false;
            bar = div("notification").hide().appendTo("body");
            var dismisser = $("<div/>").addClass("notify-close-info").text(mobile_old ? "Ok" : mobile_new ? "remove notification" : "click here to remove the notification bar")
                .appendTo(bar).click(function () {
                    if (mobile_old) {
                        bar.find("p.notification-message:last").remove();
                        var notificationsLeft = bar.find("p.notification-message").length;
                        if (notificationsLeft === 0)
                            bar.remove();
                        else
                            $(this).text("Ok" + (notificationsLeft > 1 ? " (" + notificationsLeft + ")" : ""));
                    } else {
                        bar.find("p.notification-message").not(".dismissed").each(function () {
                            icc.broadcast({ command: "dismiss notification", notification: $(this).text() });
                        });
                        bar.addClass("closing").slideUp(function () {
                            $(this).remove();
                            if ($(".banner-container").length) {
                                $(".banner-container").css("top", "34px");
                            }
                        });
                    }
                });
            if (mobile_old) {
                dismisser.addClass("button");

                // Thanks to the ultimate wisdom of Apple (tm), we can't use position fixed
                // while iOS 4 is still supported.
                bar.css("top", $(window).scrollTop() + $(window).height() / 20);
            }
        } else {
            lastmsg = bar.find("p.notification-message:" + (mobile_old ? "first" : "last"));
            if (lastmsg.html() == $("<span />").html(html).html()) // it's the same message as the most recent one
                return lastmsg;
        }
        var msg = $("<p/>").addClass("notification-message").html(html).hide();
        if (mobile_old)
            msg.prependTo(bar);
        else
            msg.insertBefore(bar.find(".notify-close-info"));
        if (cls && cls.length > 0) msg.addClass(cls);
        if (waspresent) {
            msg.slideDown();
            if (mobile_old) {
                var totalCount = bar.find("p.notification-message").length;
                bar.find(".notify-close-info").text("Ok" + (totalCount > 1 ? " (" + totalCount + ")" : ""));
            }
        }
        else {
            msg.show();
            bar.slideDown();
        }
        msg.find("a").click(function () { dismissSingleNotification($(this).closest(".notification-message")) });
        return msg;
    }
    var desktop = null;
    if (window.Notification && "permission" in Notification) {

        var toasts = [];
        function eatToast(popup) {
            popup.close();
            toasts = Generator(toasts).filter(function (x) { return x !== popup; }).toArray();
        }

        function eatAllToasts() {
            Generator(toasts).forEach(function (p) { p.close(); });
            toasts = [];
        }

        function makeToast(popup, timeout) {
            toasts.push(popup);
            popup.onclick = function () { window.focus(); };
            if (timeout)
                setTimeout(function () { eatToast(popup); }, timeout);
        }

        desktop = function (obj) {
            if (obj && obj.text) { // treat as "show a message" - otherwise is a query/request for permission
                if (window.Notification.permission !== "granted") return false;
                if (!obj.icon || !obj.icon.length) obj.icon = $('link[rel="apple-touch-icon"]').attr("href");
                if (!obj.icon || !obj.icon.length) obj.icon = $('link[rel="shortcut icon"]').attr("href");
                var popup = new Notification(obj.title, {body: obj.text, icon: obj.icon});
                if (popup) {
                    makeToast(popup, obj.timeout);
                    return true;
                }
                return false;
            } else if (window.Notification.permission === "granted") { // already have permission
                if (obj && obj.callback) obj.callback();
                return true;
            } else if (obj && obj.callback) { // has callback, so is a request for permission
                window.Notification.requestPermission(obj.callback);
                return false; // not granted **yet**
            } else { // query permission, but not granted
                return false;
            }
        };

        desktop.removeAll = eatAllToasts;
    }
    return { notify: notify, dismissSingleNotification: dismissSingleNotification, desktop: desktop };
};
$(function () {

    CHAT.inputHint = {show: show};

    var elem = $(".input-hint");
    queue = [];

    // If the user has the new JS, but not the new CSS, the input hint will cause ugly breakage.
    // This check can be removed after a few days. (Today is 2016/2/24)
    if (elem.css("position") != "absolute") {
        elem.remove();
    }

    elem.on("click", ".dismiss-input-hint", function () {
        var pref = parseInt($(this).data("set-pref"), 10);
        elem.empty();
        if (queue.length) {
            show.apply(null, queue.shift());
        }
        if (pref > 0) {
            $.post("/users/set-pref/" + pref, fkey());
        }
    });

    function show(html, dismissText, setPrefOnDismiss) {
        if (!elem.is(":empty")) {
            queue.push(Array.prototype.slice.call(arguments));
            return;
        }
        elem.html(html);
        elem.append($("<p><button class='dismiss-input-hint button'/></p>").find("button").text(dismissText || "Ok").data("set-pref", setPrefOnDismiss || -1).end());
    }

});


// End PartialJS/notify.js

// Begin PartialJS/searchbox.js

; /* searchbox.js */

function initSearchBox() {
    var box = $("#searchbox");
    var caption = "search";
    box.focus(function () {
        if ($(this).val() == caption)
            $(this).val("");
    });
    box.blur(function () {
        if ($(this).val() == "") {
            $(this).val(caption);
        }
    });
    var startVal = box.val();
    if (startVal === "" || startVal === caption) {
        box.val(caption);
    }
    // the following is irrelevant (but doesn't hurt) outside of the live chat
    box.closest("form").on("submit", function () {
        // empty the box *after* the search is submitted, hence the timeout
        setTimeout(function () { box.val(caption).addClass("watermark").blur(); }, 0);
    });
}

// End PartialJS/searchbox.js

// Begin PartialJS/flagListPopup.js

/* flagListPopup.js */
;
function BindFlagListPopup(selector, ajaxUrl, linkUrl, notify, noDataCallback) {
    $(selector).click(function (evt) {
        evt.stopPropagation();
        evt.preventDefault();
        var menu = popUp(evt.pageX, evt.pageY).css({ width: "auto", maxWidth: 600, minWidth: 300 }).addClass("flags-popup")
            .append("<h3>Loading flags <img class='ajax-loader' src='" + IMAGE("progress-dots.gif") + "' /></h3>");
        $.getJSON(ajaxUrl, function (data) { populateFlagListPopup(data, menu); });
    });

    function afterDelete(container) {
        return function () {
            container.find(".ajax-loader").remove();
            container.closest("li").find("button:contains('delete')").replaceWith("<span>message deleted</span>");
        };
    }
    function deleter(message_id, container) {
        return function () {
            if (!confirm("Delete this message?"))
                return;
            $("<img class='ajax-loader' src='" + IMAGE("progress-dots.gif") + "' />").appendTo(container);
            messageActionById(message_id, "delete", null, afterDelete(container), notify);
        }
    }

    function populateFlagListPopup(data, menu) {
        menu.find("img.ajax-loader").remove();
        menu.find("h3").text(ajaxUrl.indexOf("moderator") != -1 ? "Moderator flags" : "New spam/offensive flags")
            .append(" <a href='" + linkUrl + "'>show all</a>");
        var list = $("<ul />").appendTo(menu);

        if (!(data.messages && data.messages.length)) {
            $("<div>There are no flags to display.</div>").appendTo(menu);
            if (noDataCallback)
                noDataCallback();
            return;
        }

        for (var i = 0; i < data.messages.length; i++) {
            var message = data.messages[i];
            var isSpamFlag = !message.is_mod;
            var why = isSpamFlag ? "as spam/offensive" : "for moderator attention";

            var item = $("<li class='flagged-message' />").data("message_id", message.message_id).appendTo(list);
            $("<h4>This message was flagged " + why + " by " + message.flag_count + " user" + (message.flag_count > 1 ? "s" : "") + ":</h4>").appendTo(item);
            $("<div class='content'/>").appendTo(item).html(message.content);
            var link;
            if (message.deleted) {
                link = " message is deleted &ndash; <a class='room-name' href='/messages/" + message.message_id + "/history'>history</a>";
            } else {
                link = "<a class='room-name' href='" + PERMALINK(message.message_id) + "'>see in context</a>";
            }
            $("<div style='text-align:right'> posted by <a href='/users/" + message.user_id + "'></a> "
                + (message.time ? ToRelativeTimeMini(message.time) : "") +
                " &ndash; " + link + "</div>")
                .appendTo(item).find("a:first").text(message.username).end().find('a.room-name').removeClass("room-name").text(message.room_name);

            if (isSpamFlag) {
                var actions = $("<div />");
                actions.append("<button class='button reflag' title='agree that this message is spam or offensive'>valid</button> ");
                actions.append("<button class='button counterflag' title='this message is neither spam nor offensive'>invalid</button> ");
                actions.append("<button class='button mehflag' title='no strong opinion'>not sure</button> ");
                actions.appendTo(item);
                if(message.modflags && message.modflags.length) {
                    var flaggedBy = $("<p>(&#9830; only) flagged by</p>").appendTo(item);
                    for (var j = 0; j < message.modflags.length; j++) {
                        var flag = message.modflags[j];
                        flaggedBy.append(j == 0 ? ': ' : ', ').append($("<a href='/users/" + flag.user_id + "'></a>").text(flag.username));
                    }
                }
            } else { // mod flags
                var container = $("<ul />").appendTo(item);
                if (!message.deleted) {
                    $("<button class='button' title='delete this message'>delete</button>").click(deleter(message.message_id, container))
                        .appendTo(item.find("div:last").append("<span> &ndash; </span>"));
                }
                for (var j = 0; j < message.modflags.length; j++) {
                    var flag = message.modflags[j];
                    var flagger = $("<span> &ndash; <a href='/users/" + flag.user_id + "'></a></span>");
                    flagger.find("a").text(flag.username);
                    var dismiss = $('<span title="dismiss this moderator flag" class="quick-unmod btn-delete"> </span>');
                    var modmsg = $("<span class='mod-text' />");
                    if (flag.html)
                        modmsg.html(flag.html);
                    else
                        modmsg.text(flag.text);

                    var noise;
                    if (flag.user_id > 0)
                        noise = $("<span><input type='checkbox' name='noise'/> this flag is noise</span>").find("input").css({marginLeft:10,verticalAlign:"bottom"}).end()
                            .attr("title", "If this box is checked, dismissing the flag will show a notification to the flagger that moderator flags are reserved for serious issues.");
                    else
                        noise = $([]);
                    container.append($("<li />").append(dismiss, modmsg, flagger, noise).data("flag_id", flag.flag_id));
                }
            }

        }

    };
}

// End PartialJS/flagListPopup.js

// Begin PartialJS/mobileSwitcher.js

$(function() {
    var switchMobile = function (onOffAuto) {
        $.post("/mobile/" + onOffAuto).done(function () { window.location.reload(true); });
    };
    window.CHAT.switchMobile  = switchMobile;
    $(".mobile-on").click(function () {
        switchMobile("on");
        return false;
    })
    $(".mobile-off").click(function () {
        switchMobile("off");
        return false;
    })

});

// End PartialJS/mobileSwitcher.js

// End of file



// End PartialJS/masterAndMasterChat.jsbundle

// Begin PartialJS/hub.js

(function () {
    /*
     * This is pretty much an event emitter, except that it's a bit more "strongly" typed
     * in the sense that events aren't just strings. In other words, if you make a typo
     * when specifying the event to trigger or subscribe to, then that'll be an error,
     * not a silent no-op.
     *
     * The point of the hub is to help with decoupling code. For example, there are several
     * ways to end editing mode -- submitting the edit via button click, submitting the edit
     * via enter, clicking "cancel editing", pressing escape --, and both chat.js and mobile-new.js
     * have stuff to do when editing ends. Instead of both of them making sure to do their thing
     * for every relevant button click etc., they just add a callback to CHAT.Hub.endEditing.
     * Conversely, any code that adds a new situation that ends editing mode can just fire the callbacks,
     * instead of having to make sure that all code which needs to know about this is updated.
     *
     */
    CHAT.Hub = {
        endEditing: $.Callbacks(),
        firstTyping: $.Callbacks(),
        roomReady: $.Callbacks()
    }
})();

// End PartialJS/hub.js

// Begin PartialJS/../third-party/jquery.jplayer.min.js

/*! jPlayer 2.9.2 for jQuery ~ (c) 2009-2014 Happyworm Ltd ~ MIT License */
!function (a, b) { "function" == typeof define && define.amd ? define(["jquery"], b) : b("object" == typeof exports ? require("jquery") : a.jQuery ? a.jQuery : a.Zepto) }(this, function (a, b) {
    a.fn.jPlayer = function (c) { var d = "jPlayer", e = "string" == typeof c, f = Array.prototype.slice.call(arguments, 1), g = this; return c = !e && f.length ? a.extend.apply(null, [!0, c].concat(f)) : c, e && "_" === c.charAt(0) ? g : (this.each(e ? function () { var e = a(this).data(d), h = e && a.isFunction(e[c]) ? e[c].apply(e, f) : e; return h !== e && h !== b ? (g = h, !1) : void 0 } : function () { var b = a(this).data(d); b ? b.option(c || {}) : a(this).data(d, new a.jPlayer(c, this)) }), g) }, a.jPlayer = function (b, c) { if (arguments.length) { this.element = a(c), this.options = a.extend(!0, {}, this.options, b); var d = this; this.element.bind("remove.jPlayer", function () { d.destroy() }), this._init() } }, "function" != typeof a.fn.stop && (a.fn.stop = function () { }), a.jPlayer.emulateMethods = "load play pause", a.jPlayer.emulateStatus = "src readyState networkState currentTime duration paused ended playbackRate", a.jPlayer.emulateOptions = "muted volume", a.jPlayer.reservedEvent = "ready flashreset resize repeat error warning", a.jPlayer.event = {}, a.each(["ready", "setmedia", "flashreset", "resize", "repeat", "click", "error", "warning", "loadstart", "progress", "suspend", "abort", "emptied", "stalled", "play", "pause", "loadedmetadata", "loadeddata", "waiting", "playing", "canplay", "canplaythrough", "seeking", "seeked", "timeupdate", "ended", "ratechange", "durationchange", "volumechange"], function () { a.jPlayer.event[this] = "jPlayer_" + this }), a.jPlayer.htmlEvent = ["loadstart", "abort", "emptied", "stalled", "loadedmetadata", "canplay", "canplaythrough"], a.jPlayer.pause = function () { a.jPlayer.prototype.destroyRemoved(), a.each(a.jPlayer.prototype.instances, function (a, b) { b.data("jPlayer").status.srcSet && b.jPlayer("pause") }) }, a.jPlayer.timeFormat = { showHour: !1, showMin: !0, showSec: !0, padHour: !1, padMin: !0, padSec: !0, sepHour: ":", sepMin: ":", sepSec: "" }; var c = function () { this.init() }; c.prototype = { init: function () { this.options = { timeFormat: a.jPlayer.timeFormat } }, time: function (a) { a = a && "number" == typeof a ? a : 0; var b = new Date(1e3 * a), c = b.getUTCHours(), d = this.options.timeFormat.showHour ? b.getUTCMinutes() : b.getUTCMinutes() + 60 * c, e = this.options.timeFormat.showMin ? b.getUTCSeconds() : b.getUTCSeconds() + 60 * d, f = this.options.timeFormat.padHour && 10 > c ? "0" + c : c, g = this.options.timeFormat.padMin && 10 > d ? "0" + d : d, h = this.options.timeFormat.padSec && 10 > e ? "0" + e : e, i = ""; return i += this.options.timeFormat.showHour ? f + this.options.timeFormat.sepHour : "", i += this.options.timeFormat.showMin ? g + this.options.timeFormat.sepMin : "", i += this.options.timeFormat.showSec ? h + this.options.timeFormat.sepSec : "" } }; var d = new c; a.jPlayer.convertTime = function (a) { return d.time(a) }, a.jPlayer.uaBrowser = function (a) { var b = a.toLowerCase(), c = /(webkit)[ \/]([\w.]+)/, d = /(opera)(?:.*version)?[ \/]([\w.]+)/, e = /(msie) ([\w.]+)/, f = /(mozilla)(?:.*? rv:([\w.]+))?/, g = c.exec(b) || d.exec(b) || e.exec(b) || b.indexOf("compatible") < 0 && f.exec(b) || []; return { browser: g[1] || "", version: g[2] || "0" } }, a.jPlayer.uaPlatform = function (a) { var b = a.toLowerCase(), c = /(ipad|iphone|ipod|android|blackberry|playbook|windows ce|webos)/, d = /(ipad|playbook)/, e = /(android)/, f = /(mobile)/, g = c.exec(b) || [], h = d.exec(b) || !f.exec(b) && e.exec(b) || []; return g[1] && (g[1] = g[1].replace(/\s/g, "_")), { platform: g[1] || "", tablet: h[1] || "" } }, a.jPlayer.browser = {}, a.jPlayer.platform = {}; var e = a.jPlayer.uaBrowser(navigator.userAgent); e.browser && (a.jPlayer.browser[e.browser] = !0, a.jPlayer.browser.version = e.version); var f = a.jPlayer.uaPlatform(navigator.userAgent); f.platform && (a.jPlayer.platform[f.platform] = !0, a.jPlayer.platform.mobile = !f.tablet, a.jPlayer.platform.tablet = !!f.tablet), a.jPlayer.getDocMode = function () { var b; return a.jPlayer.browser.msie && (document.documentMode ? b = document.documentMode : (b = 5, document.compatMode && "CSS1Compat" === document.compatMode && (b = 7))), b }, a.jPlayer.browser.documentMode = a.jPlayer.getDocMode(), a.jPlayer.nativeFeatures = { init: function () { var a, b, c, d = document, e = d.createElement("video"), f = { w3c: ["fullscreenEnabled", "fullscreenElement", "requestFullscreen", "exitFullscreen", "fullscreenchange", "fullscreenerror"], moz: ["mozFullScreenEnabled", "mozFullScreenElement", "mozRequestFullScreen", "mozCancelFullScreen", "mozfullscreenchange", "mozfullscreenerror"], webkit: ["", "webkitCurrentFullScreenElement", "webkitRequestFullScreen", "webkitCancelFullScreen", "webkitfullscreenchange", ""], webkitVideo: ["webkitSupportsFullscreen", "webkitDisplayingFullscreen", "webkitEnterFullscreen", "webkitExitFullscreen", "", ""], ms: ["", "msFullscreenElement", "msRequestFullscreen", "msExitFullscreen", "MSFullscreenChange", "MSFullscreenError"] }, g = ["w3c", "moz", "webkit", "webkitVideo", "ms"]; for (this.fullscreen = a = { support: { w3c: !!d[f.w3c[0]], moz: !!d[f.moz[0]], webkit: "function" == typeof d[f.webkit[3]], webkitVideo: "function" == typeof e[f.webkitVideo[2]], ms: "function" == typeof e[f.ms[2]] }, used: {} }, b = 0, c = g.length; c > b; b++) { var h = g[b]; if (a.support[h]) { a.spec = h, a.used[h] = !0; break } } if (a.spec) { var i = f[a.spec]; a.api = { fullscreenEnabled: !0, fullscreenElement: function (a) { return a = a ? a : d, a[i[1]] }, requestFullscreen: function (a) { return a[i[2]]() }, exitFullscreen: function (a) { return a = a ? a : d, a[i[3]]() } }, a.event = { fullscreenchange: i[4], fullscreenerror: i[5] } } else a.api = { fullscreenEnabled: !1, fullscreenElement: function () { return null }, requestFullscreen: function () { }, exitFullscreen: function () { } }, a.event = {} } }, a.jPlayer.nativeFeatures.init(), a.jPlayer.focus = null, a.jPlayer.keyIgnoreElementNames = "A INPUT TEXTAREA SELECT BUTTON"; var g = function (b) { var c, d = a.jPlayer.focus; d && (a.each(a.jPlayer.keyIgnoreElementNames.split(/\s+/g), function (a, d) { return b.target.nodeName.toUpperCase() === d.toUpperCase() ? (c = !0, !1) : void 0 }), c || a.each(d.options.keyBindings, function (c, e) { return e && a.isFunction(e.fn) && ("number" == typeof e.key && b.which === e.key || "string" == typeof e.key && b.key === e.key) ? (b.preventDefault(), e.fn(d), !1) : void 0 })) }; a.jPlayer.keys = function (b) { var c = "keydown.jPlayer"; a(document.documentElement).unbind(c), b && a(document.documentElement).bind(c, g) }, a.jPlayer.keys(!0), a.jPlayer.prototype = {
        count: 0, version: { script: "2.9.2", needFlash: "2.9.0", flash: "unknown" }, options: { swfPath: "js", solution: "html, flash", supplied: "mp3", auroraFormats: "wav", preload: "metadata", volume: .8, muted: !1, remainingDuration: !1, toggleDuration: !1, captureDuration: !0, playbackRate: 1, defaultPlaybackRate: 1, minPlaybackRate: .5, maxPlaybackRate: 4, wmode: "opaque", backgroundColor: "#000000", cssSelectorAncestor: "#jp_container_1", cssSelector: { videoPlay: ".jp-video-play", play: ".jp-play", pause: ".jp-pause", stop: ".jp-stop", seekBar: ".jp-seek-bar", playBar: ".jp-play-bar", mute: ".jp-mute", unmute: ".jp-unmute", volumeBar: ".jp-volume-bar", volumeBarValue: ".jp-volume-bar-value", volumeMax: ".jp-volume-max", playbackRateBar: ".jp-playback-rate-bar", playbackRateBarValue: ".jp-playback-rate-bar-value", currentTime: ".jp-current-time", duration: ".jp-duration", title: ".jp-title", fullScreen: ".jp-full-screen", restoreScreen: ".jp-restore-screen", repeat: ".jp-repeat", repeatOff: ".jp-repeat-off", gui: ".jp-gui", noSolution: ".jp-no-solution" }, stateClass: { playing: "jp-state-playing", seeking: "jp-state-seeking", muted: "jp-state-muted", looped: "jp-state-looped", fullScreen: "jp-state-full-screen", noVolume: "jp-state-no-volume" }, useStateClassSkin: !1, autoBlur: !0, smoothPlayBar: !1, fullScreen: !1, fullWindow: !1, autohide: { restored: !1, full: !0, fadeIn: 200, fadeOut: 600, hold: 1e3 }, loop: !1, repeat: function (b) { b.jPlayer.options.loop ? a(this).unbind(".jPlayerRepeat").bind(a.jPlayer.event.ended + ".jPlayer.jPlayerRepeat", function () { a(this).jPlayer("play") }) : a(this).unbind(".jPlayerRepeat") }, nativeVideoControls: {}, noFullWindow: { msie: /msie [0-6]\./, ipad: /ipad.*?os [0-4]\./, iphone: /iphone/, ipod: /ipod/, android_pad: /android [0-3]\.(?!.*?mobile)/, android_phone: /(?=.*android)(?!.*chrome)(?=.*mobile)/, blackberry: /blackberry/, windows_ce: /windows ce/, iemobile: /iemobile/, webos: /webos/ }, noVolume: { ipad: /ipad/, iphone: /iphone/, ipod: /ipod/, android_pad: /android(?!.*?mobile)/, android_phone: /android.*?mobile/, blackberry: /blackberry/, windows_ce: /windows ce/, iemobile: /iemobile/, webos: /webos/, playbook: /playbook/ }, timeFormat: {}, keyEnabled: !1, audioFullScreen: !1, keyBindings: { play: { key: 80, fn: function (a) { a.status.paused ? a.play() : a.pause() } }, fullScreen: { key: 70, fn: function (a) { (a.status.video || a.options.audioFullScreen) && a._setOption("fullScreen", !a.options.fullScreen) } }, muted: { key: 77, fn: function (a) { a._muted(!a.options.muted) } }, volumeUp: { key: 190, fn: function (a) { a.volume(a.options.volume + .1) } }, volumeDown: { key: 188, fn: function (a) { a.volume(a.options.volume - .1) } }, loop: { key: 76, fn: function (a) { a._loop(!a.options.loop) } } }, verticalVolume: !1, verticalPlaybackRate: !1, globalVolume: !1, idPrefix: "jp", noConflict: "jQuery", emulateHtml: !1, consoleAlerts: !0, errorAlerts: !1, warningAlerts: !1 }, optionsAudio: { size: { width: "0px", height: "0px", cssClass: "" }, sizeFull: { width: "0px", height: "0px", cssClass: "" } }, optionsVideo: { size: { width: "480px", height: "270px", cssClass: "jp-video-270p" }, sizeFull: { width: "100%", height: "100%", cssClass: "jp-video-full" } }, instances: {}, status: { src: "", media: {}, paused: !0, format: {}, formatType: "", waitForPlay: !0, waitForLoad: !0, srcSet: !1, video: !1, seekPercent: 0, currentPercentRelative: 0, currentPercentAbsolute: 0, currentTime: 0, duration: 0, remaining: 0, videoWidth: 0, videoHeight: 0, readyState: 0, networkState: 0, playbackRate: 1, ended: 0 }, internal: { ready: !1 }, solution: { html: !0, aurora: !0, flash: !0 }, format: { mp3: { codec: "audio/mpeg", flashCanPlay: !0, media: "audio" }, m4a: { codec: 'audio/mp4; codecs="mp4a.40.2"', flashCanPlay: !0, media: "audio" }, m3u8a: { codec: 'application/vnd.apple.mpegurl; codecs="mp4a.40.2"', flashCanPlay: !1, media: "audio" }, m3ua: { codec: "audio/mpegurl", flashCanPlay: !1, media: "audio" }, oga: { codec: 'audio/ogg; codecs="vorbis, opus"', flashCanPlay: !1, media: "audio" }, flac: { codec: "audio/x-flac", flashCanPlay: !1, media: "audio" }, wav: { codec: 'audio/wav; codecs="1"', flashCanPlay: !1, media: "audio" }, webma: { codec: 'audio/webm; codecs="vorbis"', flashCanPlay: !1, media: "audio" }, fla: { codec: "audio/x-flv", flashCanPlay: !0, media: "audio" }, rtmpa: { codec: 'audio/rtmp; codecs="rtmp"', flashCanPlay: !0, media: "audio" }, m4v: { codec: 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"', flashCanPlay: !0, media: "video" }, m3u8v: { codec: 'application/vnd.apple.mpegurl; codecs="avc1.42E01E, mp4a.40.2"', flashCanPlay: !1, media: "video" }, m3uv: { codec: "audio/mpegurl", flashCanPlay: !1, media: "video" }, ogv: { codec: 'video/ogg; codecs="theora, vorbis"', flashCanPlay: !1, media: "video" }, webmv: { codec: 'video/webm; codecs="vorbis, vp8"', flashCanPlay: !1, media: "video" }, flv: { codec: "video/x-flv", flashCanPlay: !0, media: "video" }, rtmpv: { codec: 'video/rtmp; codecs="rtmp"', flashCanPlay: !0, media: "video" } }, _init: function () { var c = this; if (this.element.empty(), this.status = a.extend({}, this.status), this.internal = a.extend({}, this.internal), this.options.timeFormat = a.extend({}, a.jPlayer.timeFormat, this.options.timeFormat), this.internal.cmdsIgnored = a.jPlayer.platform.ipad || a.jPlayer.platform.iphone || a.jPlayer.platform.ipod, this.internal.domNode = this.element.get(0), this.options.keyEnabled && !a.jPlayer.focus && (a.jPlayer.focus = this), this.androidFix = { setMedia: !1, play: !1, pause: !1, time: 0 / 0 }, a.jPlayer.platform.android && (this.options.preload = "auto" !== this.options.preload ? "metadata" : "auto"), this.formats = [], this.solutions = [], this.require = {}, this.htmlElement = {}, this.html = {}, this.html.audio = {}, this.html.video = {}, this.aurora = {}, this.aurora.formats = [], this.aurora.properties = [], this.flash = {}, this.css = {}, this.css.cs = {}, this.css.jq = {}, this.ancestorJq = [], this.options.volume = this._limitValue(this.options.volume, 0, 1), a.each(this.options.supplied.toLowerCase().split(","), function (b, d) { var e = d.replace(/^\s+|\s+$/g, ""); if (c.format[e]) { var f = !1; a.each(c.formats, function (a, b) { return e === b ? (f = !0, !1) : void 0 }), f || c.formats.push(e) } }), a.each(this.options.solution.toLowerCase().split(","), function (b, d) { var e = d.replace(/^\s+|\s+$/g, ""); if (c.solution[e]) { var f = !1; a.each(c.solutions, function (a, b) { return e === b ? (f = !0, !1) : void 0 }), f || c.solutions.push(e) } }), a.each(this.options.auroraFormats.toLowerCase().split(","), function (b, d) { var e = d.replace(/^\s+|\s+$/g, ""); if (c.format[e]) { var f = !1; a.each(c.aurora.formats, function (a, b) { return e === b ? (f = !0, !1) : void 0 }), f || c.aurora.formats.push(e) } }), this.internal.instance = "jp_" + this.count, this.instances[this.internal.instance] = this.element, this.element.attr("id") || this.element.attr("id", this.options.idPrefix + "_jplayer_" + this.count), this.internal.self = a.extend({}, { id: this.element.attr("id"), jq: this.element }), this.internal.audio = a.extend({}, { id: this.options.idPrefix + "_audio_" + this.count, jq: b }), this.internal.video = a.extend({}, { id: this.options.idPrefix + "_video_" + this.count, jq: b }), this.internal.flash = a.extend({}, { id: this.options.idPrefix + "_flash_" + this.count, jq: b, swf: this.options.swfPath + (".swf" !== this.options.swfPath.toLowerCase().slice(-4) ? (this.options.swfPath && "/" !== this.options.swfPath.slice(-1) ? "/" : "") + "jquery.jplayer.swf" : "") }), this.internal.poster = a.extend({}, { id: this.options.idPrefix + "_poster_" + this.count, jq: b }), a.each(a.jPlayer.event, function (a, d) { c.options[a] !== b && (c.element.bind(d + ".jPlayer", c.options[a]), c.options[a] = b) }), this.require.audio = !1, this.require.video = !1, a.each(this.formats, function (a, b) { c.require[c.format[b].media] = !0 }), this.options = this.require.video ? a.extend(!0, {}, this.optionsVideo, this.options) : a.extend(!0, {}, this.optionsAudio, this.options), this._setSize(), this.status.nativeVideoControls = this._uaBlocklist(this.options.nativeVideoControls), this.status.noFullWindow = this._uaBlocklist(this.options.noFullWindow), this.status.noVolume = this._uaBlocklist(this.options.noVolume), a.jPlayer.nativeFeatures.fullscreen.api.fullscreenEnabled && this._fullscreenAddEventListeners(), this._restrictNativeVideoControls(), this.htmlElement.poster = document.createElement("img"), this.htmlElement.poster.id = this.internal.poster.id, this.htmlElement.poster.onload = function () { (!c.status.video || c.status.waitForPlay) && c.internal.poster.jq.show() }, this.element.append(this.htmlElement.poster), this.internal.poster.jq = a("#" + this.internal.poster.id), this.internal.poster.jq.css({ width: this.status.width, height: this.status.height }), this.internal.poster.jq.hide(), this.internal.poster.jq.bind("click.jPlayer", function () { c._trigger(a.jPlayer.event.click) }), this.html.audio.available = !1, this.require.audio && (this.htmlElement.audio = document.createElement("audio"), this.htmlElement.audio.id = this.internal.audio.id, this.html.audio.available = !!this.htmlElement.audio.canPlayType && this._testCanPlayType(this.htmlElement.audio)), this.html.video.available = !1, this.require.video && (this.htmlElement.video = document.createElement("video"), this.htmlElement.video.id = this.internal.video.id, this.html.video.available = !!this.htmlElement.video.canPlayType && this._testCanPlayType(this.htmlElement.video)), this.flash.available = this._checkForFlash(10.1), this.html.canPlay = {}, this.aurora.canPlay = {}, this.flash.canPlay = {}, a.each(this.formats, function (b, d) { c.html.canPlay[d] = c.html[c.format[d].media].available && "" !== c.htmlElement[c.format[d].media].canPlayType(c.format[d].codec), c.aurora.canPlay[d] = a.inArray(d, c.aurora.formats) > -1, c.flash.canPlay[d] = c.format[d].flashCanPlay && c.flash.available }), this.html.desired = !1, this.aurora.desired = !1, this.flash.desired = !1, a.each(this.solutions, function (b, d) { if (0 === b) c[d].desired = !0; else { var e = !1, f = !1; a.each(c.formats, function (a, b) { c[c.solutions[0]].canPlay[b] && ("video" === c.format[b].media ? f = !0 : e = !0) }), c[d].desired = c.require.audio && !e || c.require.video && !f } }), this.html.support = {}, this.aurora.support = {}, this.flash.support = {}, a.each(this.formats, function (a, b) { c.html.support[b] = c.html.canPlay[b] && c.html.desired, c.aurora.support[b] = c.aurora.canPlay[b] && c.aurora.desired, c.flash.support[b] = c.flash.canPlay[b] && c.flash.desired }), this.html.used = !1, this.aurora.used = !1, this.flash.used = !1, a.each(this.solutions, function (b, d) { a.each(c.formats, function (a, b) { return c[d].support[b] ? (c[d].used = !0, !1) : void 0 }) }), this._resetActive(), this._resetGate(), this._cssSelectorAncestor(this.options.cssSelectorAncestor), this.html.used || this.aurora.used || this.flash.used ? this.css.jq.noSolution.length && this.css.jq.noSolution.hide() : (this._error({ type: a.jPlayer.error.NO_SOLUTION, context: "{solution:'" + this.options.solution + "', supplied:'" + this.options.supplied + "'}", message: a.jPlayer.errorMsg.NO_SOLUTION, hint: a.jPlayer.errorHint.NO_SOLUTION }), this.css.jq.noSolution.length && this.css.jq.noSolution.show()), this.flash.used) { var d, e = "jQuery=" + encodeURI(this.options.noConflict) + "&id=" + encodeURI(this.internal.self.id) + "&vol=" + this.options.volume + "&muted=" + this.options.muted; if (a.jPlayer.browser.msie && (Number(a.jPlayer.browser.version) < 9 || a.jPlayer.browser.documentMode < 9)) { var f = '<object id="' + this.internal.flash.id + '" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="0" height="0" tabindex="-1"></object>', g = ['<param name="movie" value="' + this.internal.flash.swf + '" />', '<param name="FlashVars" value="' + e + '" />', '<param name="allowScriptAccess" value="always" />', '<param name="bgcolor" value="' + this.options.backgroundColor + '" />', '<param name="wmode" value="' + this.options.wmode + '" />']; d = document.createElement(f); for (var h = 0; h < g.length; h++)d.appendChild(document.createElement(g[h])) } else { var i = function (a, b, c) { var d = document.createElement("param"); d.setAttribute("name", b), d.setAttribute("value", c), a.appendChild(d) }; d = document.createElement("object"), d.setAttribute("id", this.internal.flash.id), d.setAttribute("name", this.internal.flash.id), d.setAttribute("data", this.internal.flash.swf), d.setAttribute("type", "application/x-shockwave-flash"), d.setAttribute("width", "1"), d.setAttribute("height", "1"), d.setAttribute("tabindex", "-1"), i(d, "flashvars", e), i(d, "allowscriptaccess", "always"), i(d, "bgcolor", this.options.backgroundColor), i(d, "wmode", this.options.wmode) } this.element.append(d), this.internal.flash.jq = a(d) } this.status.playbackRateEnabled = this.html.used && !this.flash.used ? this._testPlaybackRate("audio") : !1, this._updatePlaybackRate(), this.html.used && (this.html.audio.available && (this._addHtmlEventListeners(this.htmlElement.audio, this.html.audio), this.element.append(this.htmlElement.audio), this.internal.audio.jq = a("#" + this.internal.audio.id)), this.html.video.available && (this._addHtmlEventListeners(this.htmlElement.video, this.html.video), this.element.append(this.htmlElement.video), this.internal.video.jq = a("#" + this.internal.video.id), this.internal.video.jq.css(this.status.nativeVideoControls ? { width: this.status.width, height: this.status.height } : { width: "0px", height: "0px" }), this.internal.video.jq.bind("click.jPlayer", function () { c._trigger(a.jPlayer.event.click) }))), this.aurora.used, this.options.emulateHtml && this._emulateHtmlBridge(), !this.html.used && !this.aurora.used || this.flash.used || setTimeout(function () { c.internal.ready = !0, c.version.flash = "n/a", c._trigger(a.jPlayer.event.repeat), c._trigger(a.jPlayer.event.ready) }, 100), this._updateNativeVideoControls(), this.css.jq.videoPlay.length && this.css.jq.videoPlay.hide(), a.jPlayer.prototype.count++ }, destroy: function () { this.clearMedia(), this._removeUiClass(), this.css.jq.currentTime.length && this.css.jq.currentTime.text(""), this.css.jq.duration.length && this.css.jq.duration.text(""), a.each(this.css.jq, function (a, b) { b.length && b.unbind(".jPlayer") }), this.internal.poster.jq.unbind(".jPlayer"), this.internal.video.jq && this.internal.video.jq.unbind(".jPlayer"), this._fullscreenRemoveEventListeners(), this === a.jPlayer.focus && (a.jPlayer.focus = null), this.options.emulateHtml && this._destroyHtmlBridge(), this.element.removeData("jPlayer"), this.element.unbind(".jPlayer"), this.element.empty(), delete this.instances[this.internal.instance] }, destroyRemoved: function () { var b = this; a.each(this.instances, function (a, c) { b.element !== c && (c.data("jPlayer") || (c.jPlayer("destroy"), delete b.instances[a])) }) }, enable: function () { }, disable: function () { }, _testCanPlayType: function (a) { try { return a.canPlayType(this.format.mp3.codec), !0 } catch (b) { return !1 } }, _testPlaybackRate: function (a) { var b, c = .5; a = "string" == typeof a ? a : "audio", b = document.createElement(a); try { return "playbackRate" in b ? (b.playbackRate = c, b.playbackRate === c) : !1 } catch (d) { return !1 } }, _uaBlocklist: function (b) { var c = navigator.userAgent.toLowerCase(), d = !1; return a.each(b, function (a, b) { return b && b.test(c) ? (d = !0, !1) : void 0 }), d }, _restrictNativeVideoControls: function () { this.require.audio && this.status.nativeVideoControls && (this.status.nativeVideoControls = !1, this.status.noFullWindow = !0) }, _updateNativeVideoControls: function () { this.html.video.available && this.html.used && (this.htmlElement.video.controls = this.status.nativeVideoControls, this._updateAutohide(), this.status.nativeVideoControls && this.require.video ? (this.internal.poster.jq.hide(), this.internal.video.jq.css({ width: this.status.width, height: this.status.height })) : this.status.waitForPlay && this.status.video && (this.internal.poster.jq.show(), this.internal.video.jq.css({ width: "0px", height: "0px" }))) }, _addHtmlEventListeners: function (b, c) { var d = this; b.preload = this.options.preload, b.muted = this.options.muted, b.volume = this.options.volume, this.status.playbackRateEnabled && (b.defaultPlaybackRate = this.options.defaultPlaybackRate, b.playbackRate = this.options.playbackRate), b.addEventListener("progress", function () { c.gate && (d.internal.cmdsIgnored && this.readyState > 0 && (d.internal.cmdsIgnored = !1), d._getHtmlStatus(b), d._updateInterface(), d._trigger(a.jPlayer.event.progress)) }, !1), b.addEventListener("loadeddata", function () { c.gate && (d.androidFix.setMedia = !1, d.androidFix.play && (d.androidFix.play = !1, d.play(d.androidFix.time)), d.androidFix.pause && (d.androidFix.pause = !1, d.pause(d.androidFix.time)), d._trigger(a.jPlayer.event.loadeddata)) }, !1), b.addEventListener("timeupdate", function () { c.gate && (d._getHtmlStatus(b), d._updateInterface(), d._trigger(a.jPlayer.event.timeupdate)) }, !1), b.addEventListener("durationchange", function () { c.gate && (d._getHtmlStatus(b), d._updateInterface(), d._trigger(a.jPlayer.event.durationchange)) }, !1), b.addEventListener("play", function () { c.gate && (d._updateButtons(!0), d._html_checkWaitForPlay(), d._trigger(a.jPlayer.event.play)) }, !1), b.addEventListener("playing", function () { c.gate && (d._updateButtons(!0), d._seeked(), d._trigger(a.jPlayer.event.playing)) }, !1), b.addEventListener("pause", function () { c.gate && (d._updateButtons(!1), d._trigger(a.jPlayer.event.pause)) }, !1), b.addEventListener("waiting", function () { c.gate && (d._seeking(), d._trigger(a.jPlayer.event.waiting)) }, !1), b.addEventListener("seeking", function () { c.gate && (d._seeking(), d._trigger(a.jPlayer.event.seeking)) }, !1), b.addEventListener("seeked", function () { c.gate && (d._seeked(), d._trigger(a.jPlayer.event.seeked)) }, !1), b.addEventListener("volumechange", function () { c.gate && (d.options.volume = b.volume, d.options.muted = b.muted, d._updateMute(), d._updateVolume(), d._trigger(a.jPlayer.event.volumechange)) }, !1), b.addEventListener("ratechange", function () { c.gate && (d.options.defaultPlaybackRate = b.defaultPlaybackRate, d.options.playbackRate = b.playbackRate, d._updatePlaybackRate(), d._trigger(a.jPlayer.event.ratechange)) }, !1), b.addEventListener("suspend", function () { c.gate && (d._seeked(), d._trigger(a.jPlayer.event.suspend)) }, !1), b.addEventListener("ended", function () { c.gate && (a.jPlayer.browser.webkit || (d.htmlElement.media.currentTime = 0), d.htmlElement.media.pause(), d._updateButtons(!1), d._getHtmlStatus(b, !0), d._updateInterface(), d._trigger(a.jPlayer.event.ended)) }, !1), b.addEventListener("error", function () { c.gate && (d._updateButtons(!1), d._seeked(), d.status.srcSet && (clearTimeout(d.internal.htmlDlyCmdId), d.status.waitForLoad = !0, d.status.waitForPlay = !0, d.status.video && !d.status.nativeVideoControls && d.internal.video.jq.css({ width: "0px", height: "0px" }), d._validString(d.status.media.poster) && !d.status.nativeVideoControls && d.internal.poster.jq.show(), d.css.jq.videoPlay.length && d.css.jq.videoPlay.show(), d._error({ type: a.jPlayer.error.URL, context: d.status.src, message: a.jPlayer.errorMsg.URL, hint: a.jPlayer.errorHint.URL }))) }, !1), a.each(a.jPlayer.htmlEvent, function (e, f) { b.addEventListener(this, function () { c.gate && d._trigger(a.jPlayer.event[f]) }, !1) }) }, _addAuroraEventListeners: function (b, c) { var d = this; b.volume = 100 * this.options.volume, b.on("progress", function () { c.gate && (d.internal.cmdsIgnored && this.readyState > 0 && (d.internal.cmdsIgnored = !1), d._getAuroraStatus(b), d._updateInterface(), d._trigger(a.jPlayer.event.progress), b.duration > 0 && d._trigger(a.jPlayer.event.timeupdate)) }, !1), b.on("ready", function () { c.gate && d._trigger(a.jPlayer.event.loadeddata) }, !1), b.on("duration", function () { c.gate && (d._getAuroraStatus(b), d._updateInterface(), d._trigger(a.jPlayer.event.durationchange)) }, !1), b.on("end", function () { c.gate && (d._updateButtons(!1), d._getAuroraStatus(b, !0), d._updateInterface(), d._trigger(a.jPlayer.event.ended)) }, !1), b.on("error", function () { c.gate && (d._updateButtons(!1), d._seeked(), d.status.srcSet && (d.status.waitForLoad = !0, d.status.waitForPlay = !0, d.status.video && !d.status.nativeVideoControls && d.internal.video.jq.css({ width: "0px", height: "0px" }), d._validString(d.status.media.poster) && !d.status.nativeVideoControls && d.internal.poster.jq.show(), d.css.jq.videoPlay.length && d.css.jq.videoPlay.show(), d._error({ type: a.jPlayer.error.URL, context: d.status.src, message: a.jPlayer.errorMsg.URL, hint: a.jPlayer.errorHint.URL }))) }, !1) }, _getHtmlStatus: function (a, b) { var c = 0, d = 0, e = 0, f = 0; isFinite(a.duration) && (this.status.duration = a.duration), c = a.currentTime, d = this.status.duration > 0 ? 100 * c / this.status.duration : 0, "object" == typeof a.seekable && a.seekable.length > 0 ? (e = this.status.duration > 0 ? 100 * a.seekable.end(a.seekable.length - 1) / this.status.duration : 100, f = this.status.duration > 0 ? 100 * a.currentTime / a.seekable.end(a.seekable.length - 1) : 0) : (e = 100, f = d), b && (c = 0, f = 0, d = 0), this.status.seekPercent = e, this.status.currentPercentRelative = f, this.status.currentPercentAbsolute = d, this.status.currentTime = c, this.status.remaining = this.status.duration - this.status.currentTime, this.status.videoWidth = a.videoWidth, this.status.videoHeight = a.videoHeight, this.status.readyState = a.readyState, this.status.networkState = a.networkState, this.status.playbackRate = a.playbackRate, this.status.ended = a.ended }, _getAuroraStatus: function (a, b) { var c = 0, d = 0, e = 0, f = 0; this.status.duration = a.duration / 1e3, c = a.currentTime / 1e3, d = this.status.duration > 0 ? 100 * c / this.status.duration : 0, a.buffered > 0 ? (e = this.status.duration > 0 ? a.buffered * this.status.duration / this.status.duration : 100, f = this.status.duration > 0 ? c / (a.buffered * this.status.duration) : 0) : (e = 100, f = d), b && (c = 0, f = 0, d = 0), this.status.seekPercent = e, this.status.currentPercentRelative = f, this.status.currentPercentAbsolute = d, this.status.currentTime = c, this.status.remaining = this.status.duration - this.status.currentTime, this.status.readyState = 4, this.status.networkState = 0, this.status.playbackRate = 1, this.status.ended = !1 }, _resetStatus: function () { this.status = a.extend({}, this.status, a.jPlayer.prototype.status) }, _trigger: function (b, c, d) { var e = a.Event(b); e.jPlayer = {}, e.jPlayer.version = a.extend({}, this.version), e.jPlayer.options = a.extend(!0, {}, this.options), e.jPlayer.status = a.extend(!0, {}, this.status), e.jPlayer.html = a.extend(!0, {}, this.html), e.jPlayer.aurora = a.extend(!0, {}, this.aurora), e.jPlayer.flash = a.extend(!0, {}, this.flash), c && (e.jPlayer.error = a.extend({}, c)), d && (e.jPlayer.warning = a.extend({}, d)), this.element.trigger(e) }, jPlayerFlashEvent: function (b, c) { if (b === a.jPlayer.event.ready) if (this.internal.ready) { if (this.flash.gate) { if (this.status.srcSet) { var d = this.status.currentTime, e = this.status.paused; this.setMedia(this.status.media), this.volumeWorker(this.options.volume), d > 0 && (e ? this.pause(d) : this.play(d)) } this._trigger(a.jPlayer.event.flashreset) } } else this.internal.ready = !0, this.internal.flash.jq.css({ width: "0px", height: "0px" }), this.version.flash = c.version, this.version.needFlash !== this.version.flash && this._error({ type: a.jPlayer.error.VERSION, context: this.version.flash, message: a.jPlayer.errorMsg.VERSION + this.version.flash, hint: a.jPlayer.errorHint.VERSION }), this._trigger(a.jPlayer.event.repeat), this._trigger(b); if (this.flash.gate) switch (b) { case a.jPlayer.event.progress: this._getFlashStatus(c), this._updateInterface(), this._trigger(b); break; case a.jPlayer.event.timeupdate: this._getFlashStatus(c), this._updateInterface(), this._trigger(b); break; case a.jPlayer.event.play: this._seeked(), this._updateButtons(!0), this._trigger(b); break; case a.jPlayer.event.pause: this._updateButtons(!1), this._trigger(b); break; case a.jPlayer.event.ended: this._updateButtons(!1), this._trigger(b); break; case a.jPlayer.event.click: this._trigger(b); break; case a.jPlayer.event.error: this.status.waitForLoad = !0, this.status.waitForPlay = !0, this.status.video && this.internal.flash.jq.css({ width: "0px", height: "0px" }), this._validString(this.status.media.poster) && this.internal.poster.jq.show(), this.css.jq.videoPlay.length && this.status.video && this.css.jq.videoPlay.show(), this.status.video ? this._flash_setVideo(this.status.media) : this._flash_setAudio(this.status.media), this._updateButtons(!1), this._error({ type: a.jPlayer.error.URL, context: c.src, message: a.jPlayer.errorMsg.URL, hint: a.jPlayer.errorHint.URL }); break; case a.jPlayer.event.seeking: this._seeking(), this._trigger(b); break; case a.jPlayer.event.seeked: this._seeked(), this._trigger(b); break; case a.jPlayer.event.ready: break; default: this._trigger(b) }return !1 }, _getFlashStatus: function (a) { this.status.seekPercent = a.seekPercent, this.status.currentPercentRelative = a.currentPercentRelative, this.status.currentPercentAbsolute = a.currentPercentAbsolute, this.status.currentTime = a.currentTime, this.status.duration = a.duration, this.status.remaining = a.duration - a.currentTime, this.status.videoWidth = a.videoWidth, this.status.videoHeight = a.videoHeight, this.status.readyState = 4, this.status.networkState = 0, this.status.playbackRate = 1, this.status.ended = !1 }, _updateButtons: function (a) { a === b ? a = !this.status.paused : this.status.paused = !a, a ? this.addStateClass("playing") : this.removeStateClass("playing"), !this.status.noFullWindow && this.options.fullWindow ? this.addStateClass("fullScreen") : this.removeStateClass("fullScreen"), this.options.loop ? this.addStateClass("looped") : this.removeStateClass("looped"), this.css.jq.play.length && this.css.jq.pause.length && (a ? (this.css.jq.play.hide(), this.css.jq.pause.show()) : (this.css.jq.play.show(), this.css.jq.pause.hide())), this.css.jq.restoreScreen.length && this.css.jq.fullScreen.length && (this.status.noFullWindow ? (this.css.jq.fullScreen.hide(), this.css.jq.restoreScreen.hide()) : this.options.fullWindow ? (this.css.jq.fullScreen.hide(), this.css.jq.restoreScreen.show()) : (this.css.jq.fullScreen.show(), this.css.jq.restoreScreen.hide())), this.css.jq.repeat.length && this.css.jq.repeatOff.length && (this.options.loop ? (this.css.jq.repeat.hide(), this.css.jq.repeatOff.show()) : (this.css.jq.repeat.show(), this.css.jq.repeatOff.hide())) }, _updateInterface: function () { this.css.jq.seekBar.length && this.css.jq.seekBar.width(this.status.seekPercent + "%"), this.css.jq.playBar.length && (this.options.smoothPlayBar ? this.css.jq.playBar.stop().animate({ width: this.status.currentPercentAbsolute + "%" }, 250, "linear") : this.css.jq.playBar.width(this.status.currentPercentRelative + "%")); var a = ""; this.css.jq.currentTime.length && (a = this._convertTime(this.status.currentTime), a !== this.css.jq.currentTime.text() && this.css.jq.currentTime.text(this._convertTime(this.status.currentTime))); var b = "", c = this.status.duration, d = this.status.remaining; this.css.jq.duration.length && ("string" == typeof this.status.media.duration ? b = this.status.media.duration : ("number" == typeof this.status.media.duration && (c = this.status.media.duration, d = c - this.status.currentTime), b = this.options.remainingDuration ? (d > 0 ? "-" : "") + this._convertTime(d) : this._convertTime(c)), b !== this.css.jq.duration.text() && this.css.jq.duration.text(b)) }, _convertTime: c.prototype.time, _seeking: function () { this.css.jq.seekBar.length && this.css.jq.seekBar.addClass("jp-seeking-bg"), this.addStateClass("seeking") }, _seeked: function () { this.css.jq.seekBar.length && this.css.jq.seekBar.removeClass("jp-seeking-bg"), this.removeStateClass("seeking") }, _resetGate: function () { this.html.audio.gate = !1, this.html.video.gate = !1, this.aurora.gate = !1, this.flash.gate = !1 }, _resetActive: function () { this.html.active = !1, this.aurora.active = !1, this.flash.active = !1 }, _escapeHtml: function (a) { return a.split("&").join("&amp;").split("<").join("&lt;").split(">").join("&gt;").split('"').join("&quot;") }, _qualifyURL: function (a) {
            var b = document.createElement("div");
            return b.innerHTML = '<a href="' + this._escapeHtml(a) + '">x</a>', b.firstChild.href
        }, _absoluteMediaUrls: function (b) { var c = this; return a.each(b, function (a, d) { d && c.format[a] && "data:" !== d.substr(0, 5) && (b[a] = c._qualifyURL(d)) }), b }, addStateClass: function (a) { this.ancestorJq.length && this.ancestorJq.addClass(this.options.stateClass[a]) }, removeStateClass: function (a) { this.ancestorJq.length && this.ancestorJq.removeClass(this.options.stateClass[a]) }, setMedia: function (b) { var c = this, d = !1, e = this.status.media.poster !== b.poster; this._resetMedia(), this._resetGate(), this._resetActive(), this.androidFix.setMedia = !1, this.androidFix.play = !1, this.androidFix.pause = !1, b = this._absoluteMediaUrls(b), a.each(this.formats, function (e, f) { var g = "video" === c.format[f].media; return a.each(c.solutions, function (e, h) { if (c[h].support[f] && c._validString(b[f])) { var i = "html" === h, j = "aurora" === h; return g ? (i ? (c.html.video.gate = !0, c._html_setVideo(b), c.html.active = !0) : (c.flash.gate = !0, c._flash_setVideo(b), c.flash.active = !0), c.css.jq.videoPlay.length && c.css.jq.videoPlay.show(), c.status.video = !0) : (i ? (c.html.audio.gate = !0, c._html_setAudio(b), c.html.active = !0, a.jPlayer.platform.android && (c.androidFix.setMedia = !0)) : j ? (c.aurora.gate = !0, c._aurora_setAudio(b), c.aurora.active = !0) : (c.flash.gate = !0, c._flash_setAudio(b), c.flash.active = !0), c.css.jq.videoPlay.length && c.css.jq.videoPlay.hide(), c.status.video = !1), d = !0, !1 } }), d ? !1 : void 0 }), d ? (this.status.nativeVideoControls && this.html.video.gate || this._validString(b.poster) && (e ? this.htmlElement.poster.src = b.poster : this.internal.poster.jq.show()), "string" == typeof b.title && (this.css.jq.title.length && this.css.jq.title.html(b.title), this.htmlElement.audio && this.htmlElement.audio.setAttribute("title", b.title), this.htmlElement.video && this.htmlElement.video.setAttribute("title", b.title)), this.status.srcSet = !0, this.status.media = a.extend({}, b), this._updateButtons(!1), this._updateInterface(), this._trigger(a.jPlayer.event.setmedia)) : this._error({ type: a.jPlayer.error.NO_SUPPORT, context: "{supplied:'" + this.options.supplied + "'}", message: a.jPlayer.errorMsg.NO_SUPPORT, hint: a.jPlayer.errorHint.NO_SUPPORT }) }, _resetMedia: function () { this._resetStatus(), this._updateButtons(!1), this._updateInterface(), this._seeked(), this.internal.poster.jq.hide(), clearTimeout(this.internal.htmlDlyCmdId), this.html.active ? this._html_resetMedia() : this.aurora.active ? this._aurora_resetMedia() : this.flash.active && this._flash_resetMedia() }, clearMedia: function () { this._resetMedia(), this.html.active ? this._html_clearMedia() : this.aurora.active ? this._aurora_clearMedia() : this.flash.active && this._flash_clearMedia(), this._resetGate(), this._resetActive() }, load: function () { this.status.srcSet ? this.html.active ? this._html_load() : this.aurora.active ? this._aurora_load() : this.flash.active && this._flash_load() : this._urlNotSetError("load") }, focus: function () { this.options.keyEnabled && (a.jPlayer.focus = this) }, play: function (a) { var b = "object" == typeof a; b && this.options.useStateClassSkin && !this.status.paused ? this.pause(a) : (a = "number" == typeof a ? a : 0 / 0, this.status.srcSet ? (this.focus(), this.html.active ? this._html_play(a) : this.aurora.active ? this._aurora_play(a) : this.flash.active && this._flash_play(a)) : this._urlNotSetError("play")) }, videoPlay: function () { this.play() }, pause: function (a) { a = "number" == typeof a ? a : 0 / 0, this.status.srcSet ? this.html.active ? this._html_pause(a) : this.aurora.active ? this._aurora_pause(a) : this.flash.active && this._flash_pause(a) : this._urlNotSetError("pause") }, tellOthers: function (b, c) { var d = this, e = "function" == typeof c, f = Array.prototype.slice.call(arguments); "string" == typeof b && (e && f.splice(1, 1), a.jPlayer.prototype.destroyRemoved(), a.each(this.instances, function () { d.element !== this && (!e || c.call(this.data("jPlayer"), d)) && this.jPlayer.apply(this, f) })) }, pauseOthers: function (a) { this.tellOthers("pause", function () { return this.status.srcSet }, a) }, stop: function () { this.status.srcSet ? this.html.active ? this._html_pause(0) : this.aurora.active ? this._aurora_pause(0) : this.flash.active && this._flash_pause(0) : this._urlNotSetError("stop") }, playHead: function (a) { a = this._limitValue(a, 0, 100), this.status.srcSet ? this.html.active ? this._html_playHead(a) : this.aurora.active ? this._aurora_playHead(a) : this.flash.active && this._flash_playHead(a) : this._urlNotSetError("playHead") }, _muted: function (a) { this.mutedWorker(a), this.options.globalVolume && this.tellOthers("mutedWorker", function () { return this.options.globalVolume }, a) }, mutedWorker: function (b) { this.options.muted = b, this.html.used && this._html_setProperty("muted", b), this.aurora.used && this._aurora_mute(b), this.flash.used && this._flash_mute(b), this.html.video.gate || this.html.audio.gate || (this._updateMute(b), this._updateVolume(this.options.volume), this._trigger(a.jPlayer.event.volumechange)) }, mute: function (a) { var c = "object" == typeof a; c && this.options.useStateClassSkin && this.options.muted ? this._muted(!1) : (a = a === b ? !0 : !!a, this._muted(a)) }, unmute: function (a) { a = a === b ? !0 : !!a, this._muted(!a) }, _updateMute: function (a) { a === b && (a = this.options.muted), a ? this.addStateClass("muted") : this.removeStateClass("muted"), this.css.jq.mute.length && this.css.jq.unmute.length && (this.status.noVolume ? (this.css.jq.mute.hide(), this.css.jq.unmute.hide()) : a ? (this.css.jq.mute.hide(), this.css.jq.unmute.show()) : (this.css.jq.mute.show(), this.css.jq.unmute.hide())) }, volume: function (a) { this.volumeWorker(a), this.options.globalVolume && this.tellOthers("volumeWorker", function () { return this.options.globalVolume }, a) }, volumeWorker: function (b) { b = this._limitValue(b, 0, 1), this.options.volume = b, this.html.used && this._html_setProperty("volume", b), this.aurora.used && this._aurora_volume(b), this.flash.used && this._flash_volume(b), this.html.video.gate || this.html.audio.gate || (this._updateVolume(b), this._trigger(a.jPlayer.event.volumechange)) }, volumeBar: function (b) { if (this.css.jq.volumeBar.length) { var c = a(b.currentTarget), d = c.offset(), e = b.pageX - d.left, f = c.width(), g = c.height() - b.pageY + d.top, h = c.height(); this.volume(this.options.verticalVolume ? g / h : e / f) } this.options.muted && this._muted(!1) }, _updateVolume: function (a) { a === b && (a = this.options.volume), a = this.options.muted ? 0 : a, this.status.noVolume ? (this.addStateClass("noVolume"), this.css.jq.volumeBar.length && this.css.jq.volumeBar.hide(), this.css.jq.volumeBarValue.length && this.css.jq.volumeBarValue.hide(), this.css.jq.volumeMax.length && this.css.jq.volumeMax.hide()) : (this.removeStateClass("noVolume"), this.css.jq.volumeBar.length && this.css.jq.volumeBar.show(), this.css.jq.volumeBarValue.length && (this.css.jq.volumeBarValue.show(), this.css.jq.volumeBarValue[this.options.verticalVolume ? "height" : "width"](100 * a + "%")), this.css.jq.volumeMax.length && this.css.jq.volumeMax.show()) }, volumeMax: function () { this.volume(1), this.options.muted && this._muted(!1) }, _cssSelectorAncestor: function (b) { var c = this; this.options.cssSelectorAncestor = b, this._removeUiClass(), this.ancestorJq = b ? a(b) : [], b && 1 !== this.ancestorJq.length && this._warning({ type: a.jPlayer.warning.CSS_SELECTOR_COUNT, context: b, message: a.jPlayer.warningMsg.CSS_SELECTOR_COUNT + this.ancestorJq.length + " found for cssSelectorAncestor.", hint: a.jPlayer.warningHint.CSS_SELECTOR_COUNT }), this._addUiClass(), a.each(this.options.cssSelector, function (a, b) { c._cssSelector(a, b) }), this._updateInterface(), this._updateButtons(), this._updateAutohide(), this._updateVolume(), this._updateMute() }, _cssSelector: function (b, c) { var d = this; if ("string" == typeof c) if (a.jPlayer.prototype.options.cssSelector[b]) { if (this.css.jq[b] && this.css.jq[b].length && this.css.jq[b].unbind(".jPlayer"), this.options.cssSelector[b] = c, this.css.cs[b] = this.options.cssSelectorAncestor + " " + c, this.css.jq[b] = c ? a(this.css.cs[b]) : [], this.css.jq[b].length && this[b]) { var e = function (c) { c.preventDefault(), d[b](c), d.options.autoBlur ? a(this).blur() : a(this).focus() }; this.css.jq[b].bind("click.jPlayer", e) } c && 1 !== this.css.jq[b].length && this._warning({ type: a.jPlayer.warning.CSS_SELECTOR_COUNT, context: this.css.cs[b], message: a.jPlayer.warningMsg.CSS_SELECTOR_COUNT + this.css.jq[b].length + " found for " + b + " method.", hint: a.jPlayer.warningHint.CSS_SELECTOR_COUNT }) } else this._warning({ type: a.jPlayer.warning.CSS_SELECTOR_METHOD, context: b, message: a.jPlayer.warningMsg.CSS_SELECTOR_METHOD, hint: a.jPlayer.warningHint.CSS_SELECTOR_METHOD }); else this._warning({ type: a.jPlayer.warning.CSS_SELECTOR_STRING, context: c, message: a.jPlayer.warningMsg.CSS_SELECTOR_STRING, hint: a.jPlayer.warningHint.CSS_SELECTOR_STRING }) }, duration: function (a) { this.options.toggleDuration && (this.options.captureDuration && a.stopPropagation(), this._setOption("remainingDuration", !this.options.remainingDuration)) }, seekBar: function (b) { if (this.css.jq.seekBar.length) { var c = a(b.currentTarget), d = c.offset(), e = b.pageX - d.left, f = c.width(), g = 100 * e / f; this.playHead(g) } }, playbackRate: function (a) { this._setOption("playbackRate", a) }, playbackRateBar: function (b) { if (this.css.jq.playbackRateBar.length) { var c, d, e = a(b.currentTarget), f = e.offset(), g = b.pageX - f.left, h = e.width(), i = e.height() - b.pageY + f.top, j = e.height(); c = this.options.verticalPlaybackRate ? i / j : g / h, d = c * (this.options.maxPlaybackRate - this.options.minPlaybackRate) + this.options.minPlaybackRate, this.playbackRate(d) } }, _updatePlaybackRate: function () { var a = this.options.playbackRate, b = (a - this.options.minPlaybackRate) / (this.options.maxPlaybackRate - this.options.minPlaybackRate); this.status.playbackRateEnabled ? (this.css.jq.playbackRateBar.length && this.css.jq.playbackRateBar.show(), this.css.jq.playbackRateBarValue.length && (this.css.jq.playbackRateBarValue.show(), this.css.jq.playbackRateBarValue[this.options.verticalPlaybackRate ? "height" : "width"](100 * b + "%"))) : (this.css.jq.playbackRateBar.length && this.css.jq.playbackRateBar.hide(), this.css.jq.playbackRateBarValue.length && this.css.jq.playbackRateBarValue.hide()) }, repeat: function (a) { var b = "object" == typeof a; this._loop(b && this.options.useStateClassSkin && this.options.loop ? !1 : !0) }, repeatOff: function () { this._loop(!1) }, _loop: function (b) { this.options.loop !== b && (this.options.loop = b, this._updateButtons(), this._trigger(a.jPlayer.event.repeat)) }, option: function (c, d) { var e = c; if (0 === arguments.length) return a.extend(!0, {}, this.options); if ("string" == typeof c) { var f = c.split("."); if (d === b) { for (var g = a.extend(!0, {}, this.options), h = 0; h < f.length; h++) { if (g[f[h]] === b) return this._warning({ type: a.jPlayer.warning.OPTION_KEY, context: c, message: a.jPlayer.warningMsg.OPTION_KEY, hint: a.jPlayer.warningHint.OPTION_KEY }), b; g = g[f[h]] } return g } e = {}; for (var i = e, j = 0; j < f.length; j++)j < f.length - 1 ? (i[f[j]] = {}, i = i[f[j]]) : i[f[j]] = d } return this._setOptions(e), this }, _setOptions: function (b) { var c = this; return a.each(b, function (a, b) { c._setOption(a, b) }), this }, _setOption: function (b, c) { var d = this; switch (b) { case "volume": this.volume(c); break; case "muted": this._muted(c); break; case "globalVolume": this.options[b] = c; break; case "cssSelectorAncestor": this._cssSelectorAncestor(c); break; case "cssSelector": a.each(c, function (a, b) { d._cssSelector(a, b) }); break; case "playbackRate": this.options[b] = c = this._limitValue(c, this.options.minPlaybackRate, this.options.maxPlaybackRate), this.html.used && this._html_setProperty("playbackRate", c), this._updatePlaybackRate(); break; case "defaultPlaybackRate": this.options[b] = c = this._limitValue(c, this.options.minPlaybackRate, this.options.maxPlaybackRate), this.html.used && this._html_setProperty("defaultPlaybackRate", c), this._updatePlaybackRate(); break; case "minPlaybackRate": this.options[b] = c = this._limitValue(c, .1, this.options.maxPlaybackRate - .1), this._updatePlaybackRate(); break; case "maxPlaybackRate": this.options[b] = c = this._limitValue(c, this.options.minPlaybackRate + .1, 16), this._updatePlaybackRate(); break; case "fullScreen": if (this.options[b] !== c) { var e = a.jPlayer.nativeFeatures.fullscreen.used.webkitVideo; (!e || e && !this.status.waitForPlay) && (e || (this.options[b] = c), c ? this._requestFullscreen() : this._exitFullscreen(), e || this._setOption("fullWindow", c)) } break; case "fullWindow": this.options[b] !== c && (this._removeUiClass(), this.options[b] = c, this._refreshSize()); break; case "size": this.options.fullWindow || this.options[b].cssClass === c.cssClass || this._removeUiClass(), this.options[b] = a.extend({}, this.options[b], c), this._refreshSize(); break; case "sizeFull": this.options.fullWindow && this.options[b].cssClass !== c.cssClass && this._removeUiClass(), this.options[b] = a.extend({}, this.options[b], c), this._refreshSize(); break; case "autohide": this.options[b] = a.extend({}, this.options[b], c), this._updateAutohide(); break; case "loop": this._loop(c); break; case "remainingDuration": this.options[b] = c, this._updateInterface(); break; case "toggleDuration": this.options[b] = c; break; case "nativeVideoControls": this.options[b] = a.extend({}, this.options[b], c), this.status.nativeVideoControls = this._uaBlocklist(this.options.nativeVideoControls), this._restrictNativeVideoControls(), this._updateNativeVideoControls(); break; case "noFullWindow": this.options[b] = a.extend({}, this.options[b], c), this.status.nativeVideoControls = this._uaBlocklist(this.options.nativeVideoControls), this.status.noFullWindow = this._uaBlocklist(this.options.noFullWindow), this._restrictNativeVideoControls(), this._updateButtons(); break; case "noVolume": this.options[b] = a.extend({}, this.options[b], c), this.status.noVolume = this._uaBlocklist(this.options.noVolume), this._updateVolume(), this._updateMute(); break; case "emulateHtml": this.options[b] !== c && (this.options[b] = c, c ? this._emulateHtmlBridge() : this._destroyHtmlBridge()); break; case "timeFormat": this.options[b] = a.extend({}, this.options[b], c); break; case "keyEnabled": this.options[b] = c, c || this !== a.jPlayer.focus || (a.jPlayer.focus = null); break; case "keyBindings": this.options[b] = a.extend(!0, {}, this.options[b], c); break; case "audioFullScreen": this.options[b] = c; break; case "autoBlur": this.options[b] = c }return this }, _refreshSize: function () { this._setSize(), this._addUiClass(), this._updateSize(), this._updateButtons(), this._updateAutohide(), this._trigger(a.jPlayer.event.resize) }, _setSize: function () { this.options.fullWindow ? (this.status.width = this.options.sizeFull.width, this.status.height = this.options.sizeFull.height, this.status.cssClass = this.options.sizeFull.cssClass) : (this.status.width = this.options.size.width, this.status.height = this.options.size.height, this.status.cssClass = this.options.size.cssClass), this.element.css({ width: this.status.width, height: this.status.height }) }, _addUiClass: function () { this.ancestorJq.length && this.ancestorJq.addClass(this.status.cssClass) }, _removeUiClass: function () { this.ancestorJq.length && this.ancestorJq.removeClass(this.status.cssClass) }, _updateSize: function () { this.internal.poster.jq.css({ width: this.status.width, height: this.status.height }), !this.status.waitForPlay && this.html.active && this.status.video || this.html.video.available && this.html.used && this.status.nativeVideoControls ? this.internal.video.jq.css({ width: this.status.width, height: this.status.height }) : !this.status.waitForPlay && this.flash.active && this.status.video && this.internal.flash.jq.css({ width: this.status.width, height: this.status.height }) }, _updateAutohide: function () { var a = this, b = "mousemove.jPlayer", c = ".jPlayerAutohide", d = b + c, e = function (b) { var c, d, e = !1; "undefined" != typeof a.internal.mouse ? (c = a.internal.mouse.x - b.pageX, d = a.internal.mouse.y - b.pageY, e = Math.floor(c) > 0 || Math.floor(d) > 0) : e = !0, a.internal.mouse = { x: b.pageX, y: b.pageY }, e && a.css.jq.gui.fadeIn(a.options.autohide.fadeIn, function () { clearTimeout(a.internal.autohideId), a.internal.autohideId = setTimeout(function () { a.css.jq.gui.fadeOut(a.options.autohide.fadeOut) }, a.options.autohide.hold) }) }; this.css.jq.gui.length && (this.css.jq.gui.stop(!0, !0), clearTimeout(this.internal.autohideId), delete this.internal.mouse, this.element.unbind(c), this.css.jq.gui.unbind(c), this.status.nativeVideoControls ? this.css.jq.gui.hide() : this.options.fullWindow && this.options.autohide.full || !this.options.fullWindow && this.options.autohide.restored ? (this.element.bind(d, e), this.css.jq.gui.bind(d, e), this.css.jq.gui.hide()) : this.css.jq.gui.show()) }, fullScreen: function (a) { var b = "object" == typeof a; b && this.options.useStateClassSkin && this.options.fullScreen ? this._setOption("fullScreen", !1) : this._setOption("fullScreen", !0) }, restoreScreen: function () { this._setOption("fullScreen", !1) }, _fullscreenAddEventListeners: function () { var b = this, c = a.jPlayer.nativeFeatures.fullscreen; c.api.fullscreenEnabled && c.event.fullscreenchange && ("function" != typeof this.internal.fullscreenchangeHandler && (this.internal.fullscreenchangeHandler = function () { b._fullscreenchange() }), document.addEventListener(c.event.fullscreenchange, this.internal.fullscreenchangeHandler, !1)) }, _fullscreenRemoveEventListeners: function () { var b = a.jPlayer.nativeFeatures.fullscreen; this.internal.fullscreenchangeHandler && document.removeEventListener(b.event.fullscreenchange, this.internal.fullscreenchangeHandler, !1) }, _fullscreenchange: function () { this.options.fullScreen && !a.jPlayer.nativeFeatures.fullscreen.api.fullscreenElement() && this._setOption("fullScreen", !1) }, _requestFullscreen: function () { var b = this.ancestorJq.length ? this.ancestorJq[0] : this.element[0], c = a.jPlayer.nativeFeatures.fullscreen; c.used.webkitVideo && (b = this.htmlElement.video), c.api.fullscreenEnabled && c.api.requestFullscreen(b) }, _exitFullscreen: function () { var b, c = a.jPlayer.nativeFeatures.fullscreen; c.used.webkitVideo && (b = this.htmlElement.video), c.api.fullscreenEnabled && c.api.exitFullscreen(b) }, _html_initMedia: function (b) { var c = a(this.htmlElement.media).empty(); a.each(b.track || [], function (a, b) { var d = document.createElement("track"); d.setAttribute("kind", b.kind ? b.kind : ""), d.setAttribute("src", b.src ? b.src : ""), d.setAttribute("srclang", b.srclang ? b.srclang : ""), d.setAttribute("label", b.label ? b.label : ""), b.def && d.setAttribute("default", b.def), c.append(d) }), this.htmlElement.media.src = this.status.src, "none" !== this.options.preload && this._html_load(), this._trigger(a.jPlayer.event.timeupdate) }, _html_setFormat: function (b) { var c = this; a.each(this.formats, function (a, d) { return c.html.support[d] && b[d] ? (c.status.src = b[d], c.status.format[d] = !0, c.status.formatType = d, !1) : void 0 }) }, _html_setAudio: function (a) { this._html_setFormat(a), this.htmlElement.media = this.htmlElement.audio, this._html_initMedia(a) }, _html_setVideo: function (a) { this._html_setFormat(a), this.status.nativeVideoControls && (this.htmlElement.video.poster = this._validString(a.poster) ? a.poster : ""), this.htmlElement.media = this.htmlElement.video, this._html_initMedia(a) }, _html_resetMedia: function () { this.htmlElement.media && (this.htmlElement.media.id !== this.internal.video.id || this.status.nativeVideoControls || this.internal.video.jq.css({ width: "0px", height: "0px" }), this.htmlElement.media.pause()) }, _html_clearMedia: function () { this.htmlElement.media && (this.htmlElement.media.src = "about:blank", this.htmlElement.media.load()) }, _html_load: function () { this.status.waitForLoad && (this.status.waitForLoad = !1, this.htmlElement.media.load()), clearTimeout(this.internal.htmlDlyCmdId) }, _html_play: function (a) { var b = this, c = this.htmlElement.media; if (this.androidFix.pause = !1, this._html_load(), this.androidFix.setMedia) this.androidFix.play = !0, this.androidFix.time = a; else if (isNaN(a)) c.play(); else { this.internal.cmdsIgnored && c.play(); try { if (c.seekable && !("object" == typeof c.seekable && c.seekable.length > 0)) throw 1; c.currentTime = a, c.play() } catch (d) { return void (this.internal.htmlDlyCmdId = setTimeout(function () { b.play(a) }, 250)) } } this._html_checkWaitForPlay() }, _html_pause: function (a) { var b = this, c = this.htmlElement.media; if (this.androidFix.play = !1, a > 0 ? this._html_load() : clearTimeout(this.internal.htmlDlyCmdId), c.pause(), this.androidFix.setMedia) this.androidFix.pause = !0, this.androidFix.time = a; else if (!isNaN(a)) try { if (c.seekable && !("object" == typeof c.seekable && c.seekable.length > 0)) throw 1; c.currentTime = a } catch (d) { return void (this.internal.htmlDlyCmdId = setTimeout(function () { b.pause(a) }, 250)) } a > 0 && this._html_checkWaitForPlay() }, _html_playHead: function (a) { var b = this, c = this.htmlElement.media; this._html_load(); try { if ("object" == typeof c.seekable && c.seekable.length > 0) c.currentTime = a * c.seekable.end(c.seekable.length - 1) / 100; else { if (!(c.duration > 0) || isNaN(c.duration)) throw "e"; c.currentTime = a * c.duration / 100 } } catch (d) { return void (this.internal.htmlDlyCmdId = setTimeout(function () { b.playHead(a) }, 250)) } this.status.waitForLoad || this._html_checkWaitForPlay() }, _html_checkWaitForPlay: function () { this.status.waitForPlay && (this.status.waitForPlay = !1, this.css.jq.videoPlay.length && this.css.jq.videoPlay.hide(), this.status.video && (this.internal.poster.jq.hide(), this.internal.video.jq.css({ width: this.status.width, height: this.status.height }))) }, _html_setProperty: function (a, b) { this.html.audio.available && (this.htmlElement.audio[a] = b), this.html.video.available && (this.htmlElement.video[a] = b) }, _aurora_setAudio: function (b) { var c = this; a.each(this.formats, function (a, d) { return c.aurora.support[d] && b[d] ? (c.status.src = b[d], c.status.format[d] = !0, c.status.formatType = d, !1) : void 0 }), this.aurora.player = new AV.Player.fromURL(this.status.src), this._addAuroraEventListeners(this.aurora.player, this.aurora), "auto" === this.options.preload && (this._aurora_load(), this.status.waitForLoad = !1) }, _aurora_resetMedia: function () { this.aurora.player && this.aurora.player.stop() }, _aurora_clearMedia: function () { }, _aurora_load: function () { this.status.waitForLoad && (this.status.waitForLoad = !1, this.aurora.player.preload()) }, _aurora_play: function (b) { this.status.waitForLoad || isNaN(b) || this.aurora.player.seek(b), this.aurora.player.playing || this.aurora.player.play(), this.status.waitForLoad = !1, this._aurora_checkWaitForPlay(), this._updateButtons(!0), this._trigger(a.jPlayer.event.play) }, _aurora_pause: function (b) { isNaN(b) || this.aurora.player.seek(1e3 * b), this.aurora.player.pause(), b > 0 && this._aurora_checkWaitForPlay(), this._updateButtons(!1), this._trigger(a.jPlayer.event.pause) }, _aurora_playHead: function (a) { this.aurora.player.duration > 0 && this.aurora.player.seek(a * this.aurora.player.duration / 100), this.status.waitForLoad || this._aurora_checkWaitForPlay() }, _aurora_checkWaitForPlay: function () { this.status.waitForPlay && (this.status.waitForPlay = !1) }, _aurora_volume: function (a) { this.aurora.player.volume = 100 * a }, _aurora_mute: function (a) { a ? (this.aurora.properties.lastvolume = this.aurora.player.volume, this.aurora.player.volume = 0) : this.aurora.player.volume = this.aurora.properties.lastvolume, this.aurora.properties.muted = a }, _flash_setAudio: function (b) { var c = this; try { a.each(this.formats, function (a, d) { if (c.flash.support[d] && b[d]) { switch (d) { case "m4a": case "fla": c._getMovie().fl_setAudio_m4a(b[d]); break; case "mp3": c._getMovie().fl_setAudio_mp3(b[d]); break; case "rtmpa": c._getMovie().fl_setAudio_rtmp(b[d]) }return c.status.src = b[d], c.status.format[d] = !0, c.status.formatType = d, !1 } }), "auto" === this.options.preload && (this._flash_load(), this.status.waitForLoad = !1) } catch (d) { this._flashError(d) } }, _flash_setVideo: function (b) { var c = this; try { a.each(this.formats, function (a, d) { if (c.flash.support[d] && b[d]) { switch (d) { case "m4v": case "flv": c._getMovie().fl_setVideo_m4v(b[d]); break; case "rtmpv": c._getMovie().fl_setVideo_rtmp(b[d]) }return c.status.src = b[d], c.status.format[d] = !0, c.status.formatType = d, !1 } }), "auto" === this.options.preload && (this._flash_load(), this.status.waitForLoad = !1) } catch (d) { this._flashError(d) } }, _flash_resetMedia: function () { this.internal.flash.jq.css({ width: "0px", height: "0px" }), this._flash_pause(0 / 0) }, _flash_clearMedia: function () { try { this._getMovie().fl_clearMedia() } catch (a) { this._flashError(a) } }, _flash_load: function () { try { this._getMovie().fl_load() } catch (a) { this._flashError(a) } this.status.waitForLoad = !1 }, _flash_play: function (a) { try { this._getMovie().fl_play(a) } catch (b) { this._flashError(b) } this.status.waitForLoad = !1, this._flash_checkWaitForPlay() }, _flash_pause: function (a) { try { this._getMovie().fl_pause(a) } catch (b) { this._flashError(b) } a > 0 && (this.status.waitForLoad = !1, this._flash_checkWaitForPlay()) }, _flash_playHead: function (a) { try { this._getMovie().fl_play_head(a) } catch (b) { this._flashError(b) } this.status.waitForLoad || this._flash_checkWaitForPlay() }, _flash_checkWaitForPlay: function () { this.status.waitForPlay && (this.status.waitForPlay = !1, this.css.jq.videoPlay.length && this.css.jq.videoPlay.hide(), this.status.video && (this.internal.poster.jq.hide(), this.internal.flash.jq.css({ width: this.status.width, height: this.status.height }))) }, _flash_volume: function (a) { try { this._getMovie().fl_volume(a) } catch (b) { this._flashError(b) } }, _flash_mute: function (a) { try { this._getMovie().fl_mute(a) } catch (b) { this._flashError(b) } }, _getMovie: function () { return document[this.internal.flash.id] }, _getFlashPluginVersion: function () { var a, b = 0; if (window.ActiveXObject) try { if (a = new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) { var c = a.GetVariable("$version"); c && (c = c.split(" ")[1].split(","), b = parseInt(c[0], 10) + "." + parseInt(c[1], 10)) } } catch (d) { } else navigator.plugins && navigator.mimeTypes.length > 0 && (a = navigator.plugins["Shockwave Flash"], a && (b = navigator.plugins["Shockwave Flash"].description.replace(/.*\s(\d+\.\d+).*/, "$1"))); return 1 * b }, _checkForFlash: function (a) { var b = !1; return this._getFlashPluginVersion() >= a && (b = !0), b }, _validString: function (a) { return a && "string" == typeof a }, _limitValue: function (a, b, c) { return b > a ? b : a > c ? c : a }, _urlNotSetError: function (b) { this._error({ type: a.jPlayer.error.URL_NOT_SET, context: b, message: a.jPlayer.errorMsg.URL_NOT_SET, hint: a.jPlayer.errorHint.URL_NOT_SET }) }, _flashError: function (b) { var c; c = this.internal.ready ? "FLASH_DISABLED" : "FLASH", this._error({ type: a.jPlayer.error[c], context: this.internal.flash.swf, message: a.jPlayer.errorMsg[c] + b.message, hint: a.jPlayer.errorHint[c] }), this.internal.flash.jq.css({ width: "1px", height: "1px" }) }, _error: function (b) { this._trigger(a.jPlayer.event.error, b), this.options.errorAlerts && this._alert("Error!" + (b.message ? "\n" + b.message : "") + (b.hint ? "\n" + b.hint : "") + "\nContext: " + b.context) }, _warning: function (c) { this._trigger(a.jPlayer.event.warning, b, c), this.options.warningAlerts && this._alert("Warning!" + (c.message ? "\n" + c.message : "") + (c.hint ? "\n" + c.hint : "") + "\nContext: " + c.context) }, _alert: function (a) { var b = "jPlayer " + this.version.script + " : id='" + this.internal.self.id + "' : " + a; this.options.consoleAlerts ? window.console && window.console.log && window.console.log(b) : alert(b) }, _emulateHtmlBridge: function () { var b = this; a.each(a.jPlayer.emulateMethods.split(/\s+/g), function (a, c) { b.internal.domNode[c] = function (a) { b[c](a) } }), a.each(a.jPlayer.event, function (c, d) { var e = !0; a.each(a.jPlayer.reservedEvent.split(/\s+/g), function (a, b) { return b === c ? (e = !1, !1) : void 0 }), e && b.element.bind(d + ".jPlayer.jPlayerHtml", function () { b._emulateHtmlUpdate(); var a = document.createEvent("Event"); a.initEvent(c, !1, !0), b.internal.domNode.dispatchEvent(a) }) }) }, _emulateHtmlUpdate: function () { var b = this; a.each(a.jPlayer.emulateStatus.split(/\s+/g), function (a, c) { b.internal.domNode[c] = b.status[c] }), a.each(a.jPlayer.emulateOptions.split(/\s+/g), function (a, c) { b.internal.domNode[c] = b.options[c] }) }, _destroyHtmlBridge: function () { var b = this; this.element.unbind(".jPlayerHtml"); var c = a.jPlayer.emulateMethods + " " + a.jPlayer.emulateStatus + " " + a.jPlayer.emulateOptions; a.each(c.split(/\s+/g), function (a, c) { delete b.internal.domNode[c] }) }
    }, a.jPlayer.error = { FLASH: "e_flash", FLASH_DISABLED: "e_flash_disabled", NO_SOLUTION: "e_no_solution", NO_SUPPORT: "e_no_support", URL: "e_url", URL_NOT_SET: "e_url_not_set", VERSION: "e_version" }, a.jPlayer.errorMsg = { FLASH: "jPlayer's Flash fallback is not configured correctly, or a command was issued before the jPlayer Ready event. Details: ", FLASH_DISABLED: "jPlayer's Flash fallback has been disabled by the browser due to the CSS rules you have used. Details: ", NO_SOLUTION: "No solution can be found by jPlayer in this browser. Neither HTML nor Flash can be used.", NO_SUPPORT: "It is not possible to play any media format provided in setMedia() on this browser using your current options.", URL: "Media URL could not be loaded.", URL_NOT_SET: "Attempt to issue media playback commands, while no media url is set.", VERSION: "jPlayer " + a.jPlayer.prototype.version.script + " needs Jplayer.swf version " + a.jPlayer.prototype.version.needFlash + " but found " }, a.jPlayer.errorHint = { FLASH: "Check your swfPath option and that Jplayer.swf is there.", FLASH_DISABLED: "Check that you have not display:none; the jPlayer entity or any ancestor.", NO_SOLUTION: "Review the jPlayer options: support and supplied.", NO_SUPPORT: "Video or audio formats defined in the supplied option are missing.", URL: "Check media URL is valid.", URL_NOT_SET: "Use setMedia() to set the media URL.", VERSION: "Update jPlayer files." }, a.jPlayer.warning = { CSS_SELECTOR_COUNT: "e_css_selector_count", CSS_SELECTOR_METHOD: "e_css_selector_method", CSS_SELECTOR_STRING: "e_css_selector_string", OPTION_KEY: "e_option_key" }, a.jPlayer.warningMsg = { CSS_SELECTOR_COUNT: "The number of css selectors found did not equal one: ", CSS_SELECTOR_METHOD: "The methodName given in jPlayer('cssSelector') is not a valid jPlayer method.", CSS_SELECTOR_STRING: "The methodCssSelector given in jPlayer('cssSelector') is not a String or is empty.", OPTION_KEY: "The option requested in jPlayer('option') is undefined." }, a.jPlayer.warningHint = { CSS_SELECTOR_COUNT: "Check your css selector and the ancestor.", CSS_SELECTOR_METHOD: "Check your method name.", CSS_SELECTOR_STRING: "Check your css selector is a string.", OPTION_KEY: "Check your option name." }
});

// End PartialJS/../third-party/jquery.jplayer.min.js

// Begin PartialJS/../third-party/jquery.caret.min.js

/*
 *
 * Copyright (c) 2010 C. F., Wong (<a href="http://cloudgen.w0ng.hk">Cloudgen Examplet Store</a>)
 * Licensed under the MIT License:
 * https://www.opensource.org/licenses/mit-license.php
 *
 */
﻿(function($,len,createRange,duplicate){
    var browser= !("selectionStart" in $("<input type='text' />")[0]);
    $.fn.caret=function(options,opt2){
        var start,end,t=this[0];
        if(typeof options==="object" && typeof options.start==="number" && typeof options.end==="number") {
            start=options.start;
            end=options.end;
        } else if(typeof options==="number" && typeof opt2==="number"){
            start=options;
            end=opt2;
        } else if(typeof options==="string"){
            if((start=t.value.indexOf(options))>-1) end=start+options[len];
            else start=null;
        } else if(Object.prototype.toString.call(options)==="[object RegExp]"){
            var re=options.exec(t.value);
            if(re != null) {
                start=re.index;
                end=start+re[0][len];
            }
        }
        if(typeof start!="undefined"){
            if(browser){
                var selRange = this[0].createTextRange();
                selRange.collapse(true);
                selRange.moveStart('character', start);
                selRange.moveEnd('character', end-start);
                selRange.select();
            } else {
                this[0].selectionStart=start;
                this[0].selectionEnd=end;
            }
            this[0].focus();
            return this
        } else {
            // Modification as suggested by Андрей Юткин
            if(browser){
                var selection=document.selection;
                if (this[0].tagName.toLowerCase() != "textarea") {
                    var val = this.val(),
                        range = selection[createRange]()[duplicate]();
                    range.moveEnd("character", val[len]);
                    var s = (range.text == "" ? val[len]:val.lastIndexOf(range.text));
                    range = selection[createRange]()[duplicate]();
                    range.moveStart("character", -val[len]);
                    var e = range.text[len];
                } else {
                    var range = selection[createRange](),
                        stored_range = range[duplicate]();
                    stored_range.moveToElementText(this[0]);
                    stored_range.setEndPoint('EndToEnd', range);
                    var s = stored_range.text[len] - range.text[len],
                        e = s + range.text[len]
                }
                // End of Modification
            } else {
                var s=t.selectionStart,
                    e=t.selectionEnd;
            }
            var te=t.value.substring(s,e);
            return {start:s,end:e,text:te,replace:function(st){
                    return t.value.substring(0,s)+st+t.value.substring(e,t.value[len])
                }}
        }
    }
})(jQuery,"length","createRange","duplicate");

// End PartialJS/../third-party/jquery.caret.min.js

// Begin PartialJS/MarkdownMini.js

/* markdownmini.js */
;

var markdownMini = (function () {

    var _markdownMiniBold = /(^|.(?=\*)|[\s,('"[{-])(?:\*\*|__)(?=\S)(.+?\S)(?:\*\*|__(?=[\s,?!.;:)\]}-]|$))/g;
    var _markdownMiniItalic = /(^|.(?=\*)|[\s,('">[{-])(?:\*|_)(?=\S)(.+?\S)(?:\*|_(?=[\s,?!.;:)<\]}-]|$))/g;
    var _markdownMiniStrike = /(^|[\s,('">[{-])---(?=\S)(.+?\S)---(?=[\s,?!.;:)<\]}-]|$)/g;
    var _markdownMiniCode = /(^|\W|_)(`+)(?!\s)(.*?[^`])\2(?!`)/g;

    // $1: lookbehind, $2: name, $3: url, $4: title
    var _markdownMiniLink = /(^|\s)\[([^\]]+)\]\(((?:https?|ftp):\/\/(?:\([^()\s]*\)|[^)\s])+?)(?:\s(?:"|&quot;)([^"]+?)(?:"|&quot;))?\)/g;

    return function (text) {

        text = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

        if (/[\n\r]/.test(text)) {// it's a multiline string
            var isCode = !/^ {0,3}[^ ]/m.test(text); // read: there is no line that does not start with 4 spaces, IOW: every line starts with four spaces
            if (isCode)
                return "<pre class='full'>" + text.replace(/^    /mg, "") + "</pre>";
            else
                return "<div>" + text.replace(/\r\n?|\n/g, "<br/>") + "</div>";
        }

        text = text.replace(/\\`/g, "&#96;");
        text = text.replace(/\\\*/g, "&#42;");
        text = text.replace(/\\_/g, "&#95;");
        text = text.replace(/\\\[/g, "&#91;");
        text = text.replace(/\\\]/g, "&#93;");
        text = text.replace(/\\\(/g, "&#40;");
        text = text.replace(/\\\)/g, "&#41;");
        // TODO: replace special characters in code blocks
        text = text.replace(_markdownMiniCode, "$1<code>$3</code>");
        text = text.replace(_markdownMiniBold, "$1<b>$2</b>");
        text = text.replace(_markdownMiniItalic, "$1<i>$2</i>");
        text = text.replace(_markdownMiniStrike, "$1<strike>$2</strike>");
        // not doing any sanitzing here -- we're on the client anyway
        text = text.replace(_markdownMiniLink, "$1<a href=\"$3\" title=\"$4\">$2</a>");
        return text;
    }

})();

var autoLink = (function () {

    var _autoLink = /([^">;]|^)\b((?:https?|ftp):\/\/[A-Za-z0-9][-A-Za-z0-9+&@#\/%?=~_|\[\]\(\)!:,.;]*[-A-Za-z0-9+&@#\/%=~_|\[\])])/gi;
    var _urlProtocol = /^(https?|ftp):\/\/(www\.)?|(\/$)/gi;

    // depending on the call to autoLink, the space forcer can appear both as html &entities; and
    // as unicode chars, so handle both.
    var _spaceForcer = "&zwnj;&#8203;";
    var _spaceForcerEntities = new RegExp(_spaceForcer, "g");;
    var _spaceForcerChars = new RegExp($("<span>" + _spaceForcer + "</span>").text(), "g");

    function shortenUrl(url, maxlen)
    {
        url = removeUrlProtocol(url);
        if (url.length < maxlen) return url;

        for (var i = url.length - 1; i > 0; i--) {
            if ((url[i] == '/') && (i < maxlen))
                return url.substring(0, i) + "/&hellip;";
        }

        return url.substring(0, maxlen - 1) + "&hellip;";
    }

    function removeUrlProtocol(url)
    {
        return url.replace(_urlProtocol, "");
    }

    function makeAnchor(url) {
        return "<a href=\"" + url.replace(_spaceForcerEntities, "") + "\" rel=\"nofollow noopener noreferrer\">" + shortenUrl(url, 30) + "</a>"
    }

    function handleTrailingParens(wholeMatch, lookbehind, link) {
        if (link.charAt(link.length - 1) !== ")")
            return lookbehind + makeAnchor(link);
        var parens = link.match(/[()]/g);
        var level = 0;
        for (var i = 0; i < parens.length; i++) {
            if (parens[i] === "(") {
                if (level <= 0)
                    level = 1;
                else
                    level++;
            }
            else {
                level--;
            }
        }
        var tail = "";
        if (level < 0) {
            var re = new RegExp("\\){1," + (-level) + "}$");
            link = link.replace(re, function (trailingParens) {
                tail = trailingParens;
                return "";
            });
        }

        return lookbehind + makeAnchor(link) + tail;
    }

    return function (text) {
        return text.replace(_spaceForcerChars, _spaceForcer).replace(_autoLink, handleTrailingParens);
    }

})();

var diacSubstitutions = {
    "àåáâäãåąɐᵄᶛ": "a",
    "æǣǽᴂᵆ": "ae",
    "çćčĉ": "c",
    "đƍðÐ": "d",
    "èéêëęǝᴈᵌ": "e",
    "ⅎ": "f",
    "ğĝᵷ": "g",
    "ĥɥʮᶣ": "h",
    "ìíîïıᴉᵎ": "i",
    "ĵ": "j",
    "ʞ": "k",
    "ł": "l",
    "ɯɰᵚᶭᴟ": "m",
    "ñń": "n",
    "òóôõöøő": "o",
    "œɶᴔ": "oe",
    "řɹɺɻʴʵ": "r",
    "śşšŝ": "s",
    "ß": "ss",
    "ʇ": "t",
    "Þ": "th",
    "ùúûüŭů": "u",
    "ʌᶺ": "v",
    "ʍ": "w",
    "ýŸÿʎ": "y",
    "żźž": "z"
};
var diacritics = "";
(function() {
    for (var d in diacSubstitutions)
        diacritics += d;
})();

// note that this only works on lowercase
function noDiac(s) {
    if(s.match(/^[ƍƜǝɅɐɒɥɯɰɹɺɻʇʌʍʎʞʮʯʴʵʻ̒ᴂᴈᴉᴔᴚᴟᵄᵆᵌᵎᵚᵷᶛᶣᶭᶺⅎ]+$/)) s = s.split('').reverse().join(''); // all "turned"
    for (var orig in diacSubstitutions)
        s = s.replace(new RegExp("[" + orig + "]", "g"), diacSubstitutions[orig]);
    return s;
}

function urlFriendly(s) {
    s = noDiac(s.toLowerCase());
    s = s.replace(/[\s,.\/\\_-]+/g, "-");
    s = s.replace(/[^0-9a-z-]/g, "");
    s = s.replace(/^-/, "").replace(/--+/g, "-").substring(0, 80).replace(/-$/, "");
    return s;
}

// End PartialJS/MarkdownMini.js

// Begin PartialJS/json.js


if (window.JSON)
    window.stringify = JSON.stringify;
else
    window.stringify = function (obj) {
        if (typeof obj == "string")
            return "\"" + obj.replace(/\\/g, "\\\\").replace(/"/g, "\\\"").replace(/\n/g, "\\n").replace(/\r/g, "\\r") + "\"";
        else if (typeof obj == "number")
            return obj.toString();
        else if (obj == null)
            return "null";
        else if (obj == undefined)
            return "undefined"
        else if (obj.length != undefined)
            return "[" + $.map(obj, stringify).join(",") + "]"

        var result = "{";
        for (var key in obj) {
            result = result + stringify(key) + ":" + stringify(obj[key]) + ",";
        }
        if (result.length > 1)
            result = result.substr(0, result.length - 1);  // remove trailing comma
        return result + "}";
    };

// End PartialJS/json.js

// Begin PartialJS/bytheway.js

(function (window) {

    var currentTime = Date.now || function () { return new Date().getTime(); }
    var myId = currentTime() + "-" + Math.random();

    var messengers = {};

    window.ByTheWay = function (localStorageKey, receiveOwn) {
        var k = "m_" + localStorageKey;
        var result = messengers[k];
        if (!result) {
            messengers[k] = result = makeMessenger(localStorageKey);
        }
        return {
            broadcast: result.broadcast,
            unicast: result.unicast,
            onReceive: function (callback) {
                return result.onReceive(callback, receiveOwn);
            },
            request: function (message, callback, recipient, timeoutMs) {
                return result.request(message, callback, recipient, timeoutMs, receiveOwn);
            },
            id: result.id
        };
    }

    function regexEscape(s) {
        return s.replace(/[$^.+*\\[\]()?|{}]/g, "\\$&");
    }
    // the postMessage prefix ends with an exclamation point, so we want to
    // prevent that character from being part of the prefix itself.
    function postMessageEscape(s) {
        return s.replace(/%/g, "%%").replace(/!/g, "%-");
    }

    var postMessagePrefix = "bytheway-self-" + myId + "-";
    var localNotifiers;
    function ensurePostMessageListener() {
        if (localNotifiers)
            return;
        localNotifiers = {};
        var messagePrefixRe = new RegExp("^" + postMessagePrefix + "([^!]*)!(.*)$"); // the prefix contains no regex-active characters, so no need for escaping
        window.addEventListener("message", function (evt) {
            if (evt.source !== window)
                return;
            if (evt.data) {
                evt.data.replace(messagePrefixRe, function (wholeMatch, escapedLocalStorageKey, serializedEnvelope) {
                    var notifier = localNotifiers["m_" + escapedLocalStorageKey];
                    if (notifier)
                        notifier(JSON.parse(serializedEnvelope));
                });
            }
        }, false)
    }

    var storageNotifiers;
    function ensureStorageListener() {
        if (storageNotifiers || ! window.addEventListener)
            return;
        storageNotifiers = {};
        window.addEventListener("storage", function onStorage(evt) {
            var notifier = storageNotifiers["m_" + evt.key];
            if (!notifier)
                return;
            if (!evt.newValue)
                return;

            try {
                var envelope = JSON.parse(evt.newValue);
            } catch (ex) {
                return;
            }

            if (envelope.sender === myId)
                return;

            notifier(envelope);
        });
    }

    function makeMessenger(localStorageKey) {

        var counter = 0;

        var externalListeners = [];
        var ownListeners = [];

        var pendingRequests = {};

        function makeEnvelope(message) {
            var result = {
                sender: myId,
                time: currentTime(),
                id: counter,
                message: message
            };
            counter++;
            return result;
        }

        var notifySelfAsync;
        // In non-foreground tabs, some browsers will delay setTimeouts by quite a bit,
        // but message events are passed immediately.
        if (window.postMessage && window.addEventListener) {
            ensurePostMessageListener();
            var escaped = postMessageEscape(localStorageKey);
            var channelPrefix = postMessagePrefix + escaped + "!";
            localNotifiers["m_" + escaped] = function (envelope) {
                notifyListeners(envelope, ownListeners)
            }
            notifySelfAsync = function (serialized) {
                window.postMessage(channelPrefix + serialized, "*");
            }
        } else {
            notifySelfAsync = function (serialized) {
                setTimeout(function () {
                    notifyListeners(JSON.parse(serialized), ownListeners);
                }, 0);
            }
        }

        ensureStorageListener();
        storageNotifiers["m_" + localStorageKey] = function (envelope) {
            notifyListeners(envelope, externalListeners);
        }

        function send(envelope) {
            var serialized = JSON.stringify(envelope);
            try {
                localStorage.setItem(localStorageKey, serialized);
            } catch (ex) {}

            if (ownListeners.length) {
                notifySelfAsync(serialized);
            }
        }

        function broadcast(message) {
            send(makeEnvelope(message));
        }

        function unicast(message, recipient) {
            var envelope = makeEnvelope(message);
            envelope.recipient = recipient;
            send(envelope);
        }

        function request(message, callback, recipient, timeoutMs, receiveOwn) {
            var envelope = makeEnvelope(message);
            if (recipient)
                envelope.recipient = recipient;
            envelope.rsvp = true;
            pendingRequests[envelope.id] = [];
            setTimeout(function () {
                var responses = pendingRequests[envelope.id];
                delete pendingRequests[envelope.id];
                var messages = [];
                var envelopes = [];
                for (var i = 0; i < responses.length; i++) {
                    if (receiveOwn || responses[i].sender !== myId) {
                        messages.push(responses[i].message);
                        envelopes.push(responses[i]);
                    }
                }
                callback(messages, envelopes);
            }, timeoutMs || 100);
            send(envelope);
        }

        function responderFor(envelope) {
            return function (responseMsg) {
                var responseEnv = makeEnvelope(responseMsg);
                responseEnv.recipient = envelope.sender;
                responseEnv.inResponseTo = envelope.id;
                send(responseEnv);
            }
        }

        function onReceive(callback, receiveOwn) {
            externalListeners.push(callback);
            if (receiveOwn)
                ownListeners.push(callback);
        }

        function notifyListeners(envelope, listenersArray) {
            if ("recipient" in envelope) {
                if (envelope.recipient !== myId)
                    return;
                if ("inResponseTo" in envelope) {
                    var pr = pendingRequests[envelope.inResponseTo];
                    if (!pr)
                        return; // reponse came after timeout -- too late
                    pr.push(envelope);
                    return;
                }
            }
            for (var i = 0; i < listenersArray.length; i++) {
                if (envelope.rsvp)
                    listenersArray[i](envelope.message, envelope, responderFor(envelope));
                else
                    listenersArray[i](envelope.message, envelope);
            }
        }

        return {
            broadcast: broadcast,
            unicast: unicast,
            onReceive: onReceive,
            request: request,
            id: myId
        };

    }

})(window);


// End PartialJS/bytheway.js

// Begin PartialJS/icc.js

/// <reference path="~/Views/Room/SingleRoom.aspx" />
/* icc.js */
;
function InterClientCommunicator() {

    var messenger = ByTheWay("chat:icc2");

    function wrapped(callback) {
        return function (message, envelope) {
            callback({
                sender: envelope.sender,
                time: envelope.time,
                content: message,
                senderIsSenior: messenger.id.localeCompare(envelope.sender) > 0
            })
        }
    }

    function broadcast(content) {
        messenger.broadcast(content);
    }

    function receive(callback) {
        messenger.onReceive(wrapped(callback));
    }

    reloadOldIccClients();

    return { broadcast: broadcast, receive: receive, id: messenger.id };



    function reloadOldIccClients() {
        try {
            var queue = localStorage["chat:icc"];
            if (!queue)
                return;
            var data={reset:1};
            var msgs=JSON.parse(localStorage["chat:icc"]);
            if (msgs.length === 1 && msgs[0].sender === "0000000000000")
                return;
            for (var i = 0; i<msgs.length;i++) {
                var msg=msgs[i];
                if (msg.content.command==="poll" && msg.content.data) {
                    Generator(msg.content.data).map("0").forEach(function (k) { if (/^[rh]\d+$/.test(k)) data[k]={};});
                }
            }
            localStorage["chat:icc"]=JSON.stringify([{sender:"0000000000000",time:new Date().getTime(),content:{command:"poll",data:data,since:1,until:999999999999}}]);
        } catch (ex) {}

    }

}

// End PartialJS/icc.js

// Begin PartialJS/feedticker.js

/// <reference path="~/Views/Room/SingleRoom.aspx" />
/* feedticker.js */
;
function FeedTicker() {
    var ticker = $("#feed-ticker");
    var items = $("#ticker-items");
    var MAX_ITEMS = 8;
    var updateTimeout;

    $("#dismiss-ticker").click(function () {
        items.find(".ticker-item").addClass("dismissed");
        ticker.slideUp(function() {
            $(this).find(".ticker-item.dismissed").remove();
        });
    });

    function showHide() {
        updateTimeout = null;
        items.find(".ticker-item").slice(MAX_ITEMS).slideUp(function () { $(this).remove(); });
        ticker.slideDown();
        items.find(".ticker-item").slice(0, MAX_ITEMS).slideDown();
    };


    function add(content) {
        $(content).hide().prependTo(items);
        if (updateTimeout)
            window.clearTimeout(updateTimeout);
        updateTimeout = window.setTimeout(showHide, 1000);
    }

    return { add: add }
}

// End PartialJS/feedticker.js

// Begin PartialJS/tools.js

/* tools.js */
;




function Container() {
    var jcontainer = $([]);

    // deliberately not using non-jQuery names here to avoid confusion in the code
    return {
        put: function (what) {
            jcontainer = jcontainer.add(what);
        },
        withAll: function (f) {
            jcontainer.each(f);
        },
        withAllButTakeYourTime: function (f) {
            var len = jcontainer.length;
            jcontainer.each(function (i, obj) {
                window.setTimeout(function () {
                    f.call(obj);
                }, (len - i) * 100); // go backwards

            });
        },
        pull: function (what) {
            jcontainer = jcontainer.not(what);
        },
        spill: function () {
            jcontainer = $([]);
        }
    };
}

window.DelayedReaction = DelayedReaction;

function DelayedReaction(callback, delay, options) {
    options = options || {};
    var timeout,
        args,
        deferred,
        always = options.always;

    var go = function () {
        timeout = null;
        var d = deferred;
        deferred = null;
        callback.apply(null, args);
        if (d)
            d.resolve();
    };
    return {
        trigger: function () {
            args = arguments;
            if (always)
                always.apply(null, args);
            if (timeout) {
                if (options.sliding) {
                    clearTimeout(timeout);
                    timeout = setTimeout(go, delay);
                }
            } else {
                timeout = setTimeout(go, delay);
            }
            deferred = deferred || $.Deferred();
            return deferred.promise();
        },
        cancel: function () {
            if (timeout) {
                clearTimeout(timeout);
                timeout = null;
            }
            if (deferred) {
                var d = deferred;
                deferred = null;
                d.reject();
            }
        }
    };
}

// A helper for batching up requests for data given certain keys (e.g. user ids)
// where the server provides a route for getting the data for multiple keys at once,
// but the code requests the data for a single key, however it does so for several keys
// in short succession. The keys must be fine to use as object properties (i.e. they'll
// be treated as strings, and please don't have keys like __proto__ or hasOwnProperty).
// The server is expected to return a JSON object whose properties
// are the (string representations of the) keys.
//
// Parameters:
// - requester:       A function that receives an array of keys and makes the actual request
//                    to the server. It should return a promise that will resolved with the
//                    server response. It can return a string, which is then interpreteted as
//                    a URL to which a GET request is made.
// - delayMs:         The number of miliseconds to wait before starting a request, in anticipation
//                    of additional keys to request.
// - missingIsError:  If truthy, and a key was requested but is not present in the response, then
//                    the promise for that key is rejected. If falsy, the promised is resolved with
//                    with the value undefined.
// - maxCount:        If given, then at most this many keys will be requested in a single request.
//                    Once this many keys have been requested, then the server request will be made
//                    immediately, without waiting until delayMs has been reached. If this is not
//                    given, there is no limit on the number of keys per request.
//
// Methods of the result:
// - get              A function that receives a key and returns the promise to be resolved with the
//                    corresponding value. This can directly be passed to a MonsterCache, e.g.
//
//                      var batcher = BatchGetter(myRequester, 50, false);
//                      var cache = MonsterCache(batcher.get, "my_ls_key", 60, 120);
function BatchGetter(requester, delayMs, missingIsError, maxCount) {
    var DESIRED = [];        // these have been requested via get(), but not yet from the server
    var DEFERREDS = {};

    var delayed = DelayedReaction(performRequest, delayMs);

    function get(key) {
        var def = DEFERREDS[key];
        if (def)
            return def.promise();

        def = $.Deferred();
        DEFERREDS[key] = def;
        DESIRED.push(key);
        if (DESIRED.length >= maxCount) { // always false if maxCount was not provided
            delayed.cancel();
            performRequest();
        } else {
            delayed.trigger();
        }
        return def.promise();
    }

    function performRequest() {
        var requested = DESIRED;
        DESIRED = [];

        var req = requester(requested);
        if (typeof req === "string") {
            req = $.get(req);
        }
        req.done(function (response) {
            for (var i = 0; i < requested.length; i++) {
                var key = requested[i];
                var def = DEFERREDS[key];
                delete DEFERREDS[key];
                if (!(key in response)) {
                    if (missingIsError)
                        def.reject();
                    else
                        def.resolve(undefined);
                } else {
                    def.resolve(response[key]);
                }
            }
        }).fail(function () {
            for (var i = 0; i < requested.length; i++) {
                var key = requested[i];
                var def = DEFERREDS[key];
                delete DEFERREDS[key];
                def.reject();
            }
        });
    }
    return {get: get};
}

function GoodGetter(cacheMs, delayMs) {

    var cache = {};
    var getting = {};
    var currentKey;
    var pending;

    var loadDelayed = DelayedReaction(function (url) {
        var key = "c_" + url;
        getting[key] = pending;
        pending = null;
        $.get(url).done(function (result) {
            cache[key] = result;
            setTimeout(function () {
                delete cache[key];
            }, cacheMs);
            var def = getting[key];
            delete getting[key];
            if (currentKey === key) {
                def.resolve(result);
                currentKey = null;
            } else {
                def.reject();
            }
        }).fail(function () {
            var def = getting[key];
            delete getting[key];
            def.reject();
        });
    }, delayMs, {sliding: true});

    function cancel() {
        if (pending) {
            pending.reject();
            pending = null;
            currentKey = null;
        }
    }


    var result = function(url) {
        var key = "c_" + url;
        if (key in cache)
            return $.Deferred().resolve(cache[key]).promise();
        if (pending && key === currentKey)
            return pending.promise();
        if (key in getting)
            return getting[key].promise();
        cancel();
        currentKey = key;
        pending = $.Deferred();
        loadDelayed.trigger(url);
        return pending.promise();
    };
    result.cancel = cancel;
    return result;

}

// End PartialJS/tools.js

// Begin PartialJS/users.js

/* users.js */
;

var users = {};

function userContainer(user_id) {
    var container = $("<div/>").addClass("user-container user-" + user_id);
    if (user_id) {
        CHAT.RoomUsers.updateUserContainer(container.data("user", user_id));
    }
    return container;
}

// it's important that these are ordered!
var DesiredImageSizes = [16, 18, 24, 28, 32, 42, 48, 50, 64, 65, 90, 128, 256];
function desiredImageSizeAtLeast(requestedSize) {
    var l = DesiredImageSizes.length;
    for (var i = 0; i < l; i++)
        if (DesiredImageSizes[i] >= requestedSize)
            return DesiredImageSizes[i];
    return DesiredImageSizes[l - 1];
}
var imageServiceProfileImage = /^https?:\/\/i(-\w*)?\.sstatic\.net\//i;
function gravatarUrl(userId, emailHash, size) {
    if (emailHash.charAt(0) === '!') {
        var result = emailHash.substr(1);
        if (imageServiceProfileImage.test(result)) {
            return createImageProfileUrl(result, size);
        }
        return result;
    }

    return "//www.gravatar.com/avatar/" + emailHash + "?s=" + size.toString() + "&d=identicon&r=PG";
}

function createImageProfileUrl(url, size, additionalParams) {
    // TODO: be careful here - additionalParams relies on being passed a trailing & - probably a better way to pass
    // old school default params - required for old JS build
    additionalParams = additionalParams || "";

    var replaced = false;
    url = url.replace(/([&?]s=)\d+/, function (wholematch, paramname) {
        replaced = true;
        return paramname + desiredImageSizeAtLeast(size);
    });
    if (!replaced) {
        var sep = /\?/.test(url) ? "&" : "?";
        url += sep + additionalParams + "s=" + desiredImageSizeAtLeast(size);
    }
    return url;
}

// Every dash that is preceded and followed by a character that's neither another dash nor a space
// is considered a hyphen. For the tiny signature, we want to hyphens to be broken to the next line
// (in other words, not to be displayed at all), so double names like "Hans-Otto" get shortened
// to "Hans", not "Hans-", when there's not enough space. To do this, we replace the dash with
// a zero-width space and a hard hyphen. For the large signature (where the hyphen should be placed
// on the first line), we replace it with a regular hyphen.
//
// The $("<span />").text(username).html() part is just for HTML-Encoding potentially eval user names.
function lineBreakBeforeHyphen(username) {
    return $("<span />").text(username).html().replace(/([^\s-])-([^\s-])/g, "$1&#8203;&#8209;$2")
}
function lineBreakAfterHyphen(username) {
    return $("<span />").text(username).html().replace(/([^\s-])-([^\s-])/g, "$1&#8208;$2")
}
function normalizeUserName(name) {
    return noDiac(name.toLowerCase()).replace(/\s/g, "");
}


// End PartialJS/users.js

// Begin PartialJS/roomusers.jsbundle

// Automatically combined file using PartialJS/roomusers.jsbundle

// Begin PartialJS/roomusers/00_beginclosure.js

;

(function () {

    var RoomUsers = CHAT.RoomUsers;

// End PartialJS/roomusers/00_beginclosure.js

// Begin PartialJS/roomusers/01_core.js


    ROOM_USERS_BY_ID = {};


    IDS_TO_BE_LOADED_FROM_SERVER = [];
    PENDING_SERVER_LOADS_BY_ID = {};

    function getOrCreate(id) {
        if (!(id in ROOM_USERS_BY_ID))
            ROOM_USERS_BY_ID[id] = new RoomUser(id);
        return ROOM_USERS_BY_ID[id];
    }

// returns a promise that is resolved with the server-provided data;
// does not update our user record
    function loadFromServer(id) {
        var pending = PENDING_SERVER_LOADS_BY_ID[id];
        if (!pending) {
            PENDING_SERVER_LOADS_BY_ID[id] = pending = $.Deferred();
            IDS_TO_BE_LOADED_FROM_SERVER.push(id);
            startRequestDelayed.trigger();
        }
        return pending.promise();
    }



    var startRequestDelayed = DelayedReaction(function () {
        var joined = IDS_TO_BE_LOADED_FROM_SERVER.join(",");

        // note this empties IDS_TO_BE_LOADED_FROM_SERVER
        var requested = IDS_TO_BE_LOADED_FROM_SERVER.splice(0);

        $.post("/user/info", { ids: joined, roomId: CHAT.CURRENT_ROOM_ID }, "json").done(function (data) {
            var received = {};
            Generator(data.users).forEach(function (user_data) {
                received[user_data.id] = true;
                var def = PENDING_SERVER_LOADS_BY_ID[user_data.id];
                delete PENDING_SERVER_LOADS_BY_ID[user_data.id];
                def.resolve(user_data);
            });
            Generator(requested).forEach(function (id) {
                if (received[id])
                    return;

                // leftovers, the server didn't know about these users
                var def = PENDING_SERVER_LOADS_BY_ID[id];
                delete PENDING_SERVER_LOADS_BY_ID[id];
                def.reject();

                // the request completed fine, but didn't include this user. So the user was deleted; the least we can do is kick it out of the "who's here" list
                $("#present-user-" + id).remove();
            });
        }).fail(function() {
            Generator(requested).forEach(function (id) {
                var def = PENDING_SERVER_LOADS_BY_ID[id];
                delete PENDING_SERVER_LOADS_BY_ID[id];
                def.reject();
            });
        });
    }, 500, {sliding: true});

    RoomUsers.loadPingables = function () {
        $.get("/rooms/pingable/" + CHAT.CURRENT_ROOM_ID).done(function (a) {
            var additionalData = {};
            Generator(a).forEach(function (u) {
                var ru = getOrCreate(u[0]);
                ru.updateFrom({ name: u[1], last_seen: u[2], last_post: u[3], status: u[4] }, /* skipUiUpdate=*/true, /*incomplete=*/true);
                if (u.length > 5)
                    additionalData[u[0]] = u.slice(5);
            });
            CHAT.RoomUsers.trigger("loadpingable", Generator(a).map("0"), additionalData);
        })
    }

    RoomUsers.get = function(id) {
        var u = getOrCreate(id);
        if (u.complete) {
            return $.Deferred().resolve(u).promise();
        } else {
            return u.updateFromServer();
        }
    };
    RoomUsers.forceUpdate = function (userid) {
        getOrCreate(userid).updateFromServer();
    };
    RoomUsers.forceUpdateIfNecessary = function (userid) {
        var u = ROOM_USERS_BY_ID[userid];
        if (!u)
            return;
        if (!u.isVisibleOnPage() && !u.isPingable())
            return;
        u.updateFromServer();
    };
    RoomUsers.pingableUsersIncludeIncomplete = function () {
        return RoomUsers.allIncludeIncomplete().filter(function (u) { return u.isPingable(); }).sortBy(function (u) { return -(u.last_post || 0); });
    };
    RoomUsers.current = function () {
        return getOrCreate(CHAT.CURRENT_USER_ID);
    }
    RoomUsers.update = function (userId, data, skipUiUpdate, incomplete) {
        getOrCreate(userId).updateFrom(data, skipUiUpdate, incomplete);
    }
    RoomUsers.all = function () {
        return RoomUsers.allIncludeIncomplete().filter("complete");
    };
    RoomUsers.allIncludeIncomplete = function () {
        return Generator(ROOM_USERS_BY_ID).map("1");
    };
    RoomUsers.allPresent = function () {
        return RoomUsers.all().filter(function (u) { return u.isPresent(); });
    };
    RoomUsers.getIfAvailable = function(userid, includeIncomplete) {
        var u = ROOM_USERS_BY_ID[userid];
        if (u && (includeIncomplete || u.complete))
            return u;
        return undefined;
    }

    function RoomUser(id, data) {
        if (!(this instanceof RoomUser))
            return new RoomUser(id, data);

        this.id = id;
        if (data)
            this.updateFrom(data);
    }

    RoomUser.prototype = {
        updateFrom: function (data, skipUiUpdate, incomplete) {
            var that = this;
            function copy(field) {
                var result = false;
                if (field in data) {
                    result = that[field] !== data[field];
                    that[field] = data[field];
                }
                return result;
            }

            var visibleChange = false;

            visibleChange = copy("name") || visibleChange;
            visibleChange = copy("email_hash") || visibleChange;
            visibleChange = copy("reputation") || visibleChange;
            visibleChange = copy("is_moderator") || visibleChange;
            visibleChange = copy("can_moderate") || visibleChange;
            visibleChange = copy("is_owner") || visibleChange;

            copy("status")
            copy("last_post");
            copy("last_seen");

            if (!incomplete) {
                this.complete = true;
                this.last_server_refresh = now();
            }

            if (visibleChange && !skipUiUpdate) {
                RoomUsers.updateAllUserContainersFor(this);
            }
        },
        updateFromServer: function () {
            var that = this;
            return loadFromServer(this.id).pipe(function (data) {
                that.updateFrom(data);
                return that;
            }).promise();
        },
        setIfBigger: function(key, value) {
            var old = this[key];
            if (typeof old !== "number" || old < value)
                this[key] = value;
        },
        // in company-internal rooms, the prototype is modified to use a different isPingable
        isPingable: function() {
            return this.id > 0 && this.id !== CHAT.CURRENT_USER_ID && (this.isPresent() || (this.hasEverSpoken() && secondsSince(this.last_seen || 0) < 7 * 24 * 60 * 60));
        },
        hasEverSpoken: function () {
            return !!this.last_post;
        },
        isPresent: function() {
            return this.is_present;
        },
        isVisibleOnPage: function () {
            return !!$(".user-container.user-" + this.id + ":first").length;
        },
        leave: function () {
            this.is_present = false;
            RoomUsers.sidebarLeave(this.id);
        },
        enter: function () {
            this.is_present = true;
            this.seen();
            RoomUsers.sidebarActivity(this.id, this.name, /*highlight=*/true, now(), this.email_hash);
        },
        talk: function (time) {
            this.setIfBigger("last_post", time || now());
        },
        activity: function (time) {
            RoomUsers.sidebarActivity(this.id, this.name, /*highlight=*/false, time || now(), this.email_hash);
        },
        seen: function () {
            this.last_seen = now();
        }
    }





// End PartialJS/roomusers/01_core.js

// Begin PartialJS/roomusers/02_display.js


    RoomUsers.updateUserContainer = function(element, optionalUserId) {
        var userid = typeof optionalUserId !== "undefined" ? optionalUserId : element.data("user");
        if (!userid || userid.toString().length == 0)
            return $.Deferred().reject().promise();

        return RoomUsers.get(userid).done(function (userdata) {

            var canModerate = !!userdata.can_moderate;

            element.find(".username").each(function () {
                var $username = $(this);
                var isTinySignature = $username.closest(".tiny-signature").length != 0;

                var displayNameHtml = isTinySignature ? lineBreakBeforeHyphen(userdata.name) : lineBreakAfterHyphen(userdata.name);

                if (canModerate) {
                    if (isTinySignature) {
                        displayNameHtml = '<span style="float:right">&nbsp;&#9830;</span>' + displayNameHtml;
                    } else {
                        displayNameHtml += '&nbsp;&#9830;';
                    }
                }

                $username
                    .toggleClass("moderator", canModerate)
                    .toggleClass("owner", !!userdata.is_owner)
                    .html(displayNameHtml);
            });

            element.find(".userlink,a.signature").attr("href", "/users/" + userdata.id + "/" + urlFriendly(userdata.name));
            element.find(".avatar > img").each(function () {
                $(this).attr("src", gravatarUrl(userid, userdata.email_hash, $(this).attr("width") * (window.devicePixelRatio||1))).attr("alt", userdata.name).attr("title", userdata.name);
            });
            if (userid > 0) {
                element.find(".flair").text(repNumber(userdata.reputation)).attr("title", userdata.reputation);
            }
        });
    };

    RoomUsers.updateAllUserContainersFor = function(user) {
        $(".user-container.user-" + user.id).each(function () {
            RoomUsers.updateUserContainer($(this));
        });
    };

    RoomUsers.monologueSignature = function(user_id) {

        // Note that we're producing invalid HTML4 here (wrapping block elements in an anchor).
        // But since most browsers are fine with, and it will be supported in HTML5 anyway,
        // we're not really illegal -- we're avant-garde!
        var signature = $("<a href='/users/" + user_id + "' />").addClass("signature user-" + user_id);
        var userlink = $("<div/>").addClass("username").hide();
        var flair = $("<div/>").addClass("flair").hide();
        var tiny = $("<div/>").addClass("tiny-signature");

        var avatar, tinyavatar;

        var tinyusername = $("<div/>").addClass("username");

        avatar = $("<div><img width='32' height='32'/><div>").addClass("avatar avatar-32 clear-both").hide();
        tinyavatar = $("<div><img width='16' height='16'/></div>").addClass("avatar avatar-16");

        tiny.append(tinyavatar, tinyusername);
        signature.append(tiny, avatar, userlink, flair);

        if (user_id == 0) {
            signature.find("img").attr("src", IMAGE("anon.png"));
            return signature;
        }
        userlink.add(tinyusername).html("<i>loading&hellip;</i>");
        RoomUsers.updateUserContainer(signature, user_id);
        return signature;
    }

// moves the user causing the given event to the top of the "who's here" list,
// creating the user if necessary. If highlight is true, the user will be inserted
// with a sliding motion to represent room enering
    RoomUsers.sidebarActivity = function (userid, username, highlight, time, hash) {
        var list = $("#present-users");
        highlight = highlight && list.find("li.present-user").length <= 32;
        if (time == undefined)
            time = now();
        var pu = $("#present-user-" + userid);
        pu.find(".data > .last-activity-time").text(time);
        if (pu.length == 0 || pu.hasClass("leaving")) {
            pu.remove();
            pu = makePresentUserElement(userid, username, time);
        }
        CHAT.RoomUsers.updateUserContainer(pu);
        setUserOpacity(pu, time);
        if (highlight && !CHAT.IS_MOBILE && shouldShowUser(userid)) {
            pu.prependTo(list);
            pu.css({ visibility: "hidden", width: 0 });
            pu.addClass("arriving");

            // When starting a width or height animation, jquery helpfully sets overflow to hidden
            // to make sure that really only the desired part is visible. It means well, but in this
            // case it's a) unnecessary because the element is visibility:hidden anyway, and b)
            // harmful because overflow:hidden causes an unwanted change of the vertical alignment
            // of inline-block elements. That's why we immediately undo this change.
            pu.animate({ "width": 32 }, 3000).css("overflow", "");

            var actor = div("user-container fly-in-user").data("user", userid);
            actor.css({ top: -100, left: pu.offset().left }).appendTo("#main");
            actor.append(pu.find(".avatar").clone());
            actor.append(span("username").text(username || ""));
            var flying = false;
            var flyIn = function () {
                if (flying)
                    return;
                flying = true;
                actor.animate({ top: [pu.offset().top - $(window).scrollTop() - 11, "swing"], left: [pu.offset().left - $(window).scrollLeft() - 11, "linear"] }, 3000, function () {
                    pu.css({ visibility: "visible" });
                    pu.removeClass("arriving");
                    actor.fadeOut(500, function () { actor.remove(); });
                });
            };
            CHAT.RoomUsers.updateUserContainer(actor).done(flyIn);
            window.setTimeout(flyIn, 5000); // fallback in case the user container update fails

        }
        else { // just move it to the top; whether new or not
            pu.prependTo(list);
        }
        pu.show();
        updatePresentUsersDisplay(list);
    }

    function updatePresentUsersDisplay(list) {
        if (CHAT.IS_MOBILE) {
            if (Mobile.updatePeopleCount)
                Mobile.updatePeopleCount();
            else if (Mobile.page)
                Mobile.page(list);
        } else
            list.updateCollapsible();
    }

// remove the user from the "who's here' list with a flying-out motion to represent leaving
    RoomUsers.sidebarLeave = function(userid) {
        var remover = function () {
            $(this).remove();
            updatePresentUsersDisplay($("#present-users"));
        };
        var pu = $("#present-user-" + userid).addClass("leaving");
        if (!pu.length)
            return;
        if ($("#present-users li.present-user").length > 32) {
            remover.call(pu);
            return;
        }
        if (!CHAT.IS_MOBILE && shouldShowUser(userid)) {
            var actor = pu.find(".avatar").clone().css({ zIndex: 3, position: "fixed", top: pu.offset().top - $(window).scrollTop(),
                left: pu.offset().left - $(window).scrollLeft() }).appendTo("body").hide().fadeIn(500, function() { pu.css({visibility: "hidden"}); });
            actor.animate({ left: ["-=100px", "linear"], top: ["+=200px", "swing"], opacity: 0}, 3000, remover);
        }
        // see userActivity() for an explanation of the overflow change
        pu.animate({ width: 0 }, 3000, remover).css("overflow", "");
    }

    RoomUsers.createAvatarImage = function(userid, size) {
        var user = RoomUsers.getIfAvailable(userid);
        var url;
        if (user) {
            url = gravatarUrl(userid, user.email_hash, size * (window.devicePixelRatio||1));
        } else {
            url = IMAGE("anon.png");
        }
        var elem = $("<img />").attr("width", size).attr("height", size).attr("src", url);
        if (!user) {
            RoomUsers.get(userid).done(function (user) {
                elem.attr("src", gravatarUrl(userid, user.email_hash, size * (window.devicePixelRatio||1)));
            });
        }
        return elem;
    }

    function addInitiallyPresentUsersToSidebar(usersGenerator) {
        var n = 0;
        var elems = $([]);
        usersGenerator.forEach(function (u) {
            n++;
            var time = u.last_post || 0;
            var pu = makePresentUserElement(u.id, u.name, time);
            CHAT.RoomUsers.updateUserContainer(pu);
            setUserOpacity(pu, time);
            if (n > 32)
                pu.hide();
            elems = elems.add(pu);
        });

        $("#present-users").prepend(elems)
            .closest(".sidebar-widget").find(".more")
            .setVisible(n > 32)
            .attr("title", "show " + (n - 32) + " more"); // yeah, that might be negative -- but then it will be invisble anyway
        if (CHAT.IS_MOBILE && Mobile.updatePeopleCount)
            Mobile.updatePeopleCount();
    }

    function makePresentUserElement(userid, username, time) {
        var pu = $('<li class="present-user"/>').attr("id", "present-user-" + userid)
            .addClass("user-container user-" + userid).data("user", userid);//.hover(showUserPopupMenu, hideUserPopupMenu);
        if (!shouldShowUser(userid))
            pu.addClass("ignored");
        var img = $('<img class="user-gravatar32" width="32" height="32"/>').attr("alt", username).attr("title", username);
        if (CHAT.IS_MOBILE) {
            var userlink = $("<a class=\"userlink\"/>").appendTo(pu);
            userlink.append(div("avatar").append(img)).append(div("username"));
        } else {
            pu.append(div("avatar").append(img));
        }
        pu.append(span("data").append(span("last-activity-time").text(time)));
        return pu;
    }

    function setUserOpacity(jElem, activityTime) {
        if (CHAT.IS_MOBILE)
            return;
        var s = secondsSince(activityTime);
        var opacity = 1 - Math.max(Math.min(s / 3600, 1), 0) * 0.85;
        jElem.css({ opacity: opacity });
    }

    function updateUserOpacities() {
        if (CHAT.IS_MOBILE)
            return;
        $("#present-users").find(".present-user").each(function () {
            var jElem = $(this);
            setUserOpacity(jElem, jElem.find(".data > .last-activity-time").text());
        });
    }

// End PartialJS/roomusers/02_display.js

// Begin PartialJS/roomusers/03_events.js

    ROOM_USERS_HANDLERS = {};
    RoomUsers.on = function (evt, cb) {
        evt = "e_" + evt;
        var handlers = ROOM_USERS_HANDLERS[evt];
        if (!handlers)
            handlers =  ROOM_USERS_HANDLERS[evt] = $.Callbacks();
        handlers.add(cb);
    }

    RoomUsers.trigger = function (evt) {
        evt = "e_" + evt;
        var handlers = ROOM_USERS_HANDLERS[evt];
        if (!handlers)
            return;
        handlers.fire.apply(handlers, Array.prototype.slice.call(arguments, 1));
    }

// End PartialJS/roomusers/03_events.js

// Begin PartialJS/roomusers/05_initialization.js

    var REFRESH_USER_IF_OLDER_THAN_SECONDS = 3600;
    var CHECK_FOR_USER_REFRESH_EVERY_MS = 5 * 60 * 1000;
    var UPDATE_LAST_SEEN_EVERY_MS = 60 * 1000;

    function setVariousIntervals() {
        setInterval(function () {
            var currentTime = now();
            RoomUsers.all()
                .filter(function (u) {
                    return currentTime - (u.last_server_refresh || 0) > REFRESH_USER_IF_OLDER_THAN_SECONDS;
                })
                .forEach(function (u) {
                    u.updateFromServer();
                });
        }, CHECK_FOR_USER_REFRESH_EVERY_MS);

        setInterval(function () {
            RoomUsers.allPresent().forEach(function (u) {
                u.seen();
            });
        }, UPDATE_LAST_SEEN_EVERY_MS);

        if (!CHAT.IS_MOBILE)
            setInterval(updateUserOpacities, 10000);
    }
    RoomUsers.initPresent = function () {
        var data = $('.js-present').data();

        addInitiallyPresentUsersToSidebar(Generator(data.users).map(function (d) {
            var u = getOrCreate(d.id);
            u.updateFrom(d, /*skipUiUpdate =*/ true);
            u.is_present = true;
            u.seen();
            return u;
        }));

        if (data.joining) {
            RoomUsers.update(data.joining.id, data.joining);
        }
    }

// stuff that's not immediately necessary; called once the chat is loaded
    RoomUsers.initializeLate = function (currentUserCanTalk) {
        setVariousIntervals();
        if (currentUserCanTalk)
            RoomUsers.loadPingables();
    }

// End PartialJS/roomusers/05_initialization.js

// Begin PartialJS/roomusers/06_permissions.js

    function makeCurrentCheck() {
        var properties = arguments;
        return function () {
            if (CHAT.CURRENT_USER_ID > 0) {
                for (var i = 0; i < properties.length; i++) {
                    if (RoomUsers.current()[properties[i]]) {
                        return true;
                    }
                }
            }
            return false;
        };
    };

    CHAT.user = {
        canSuperPing: makeCurrentCheck('is_moderator'),
        isOwner: makeCurrentCheck('is_owner'),
        canModerate: makeCurrentCheck('can_moderate'),
        canEditAndDeleteOldPosts: makeCurrentCheck('is_moderator'),
        canUndelete: makeCurrentCheck('is_moderator'),
        isLoggedIn: function () { return CHAT.CURRENT_USER_ID > 0; },
        canKick: makeCurrentCheck('can_moderate', 'is_owner'),
        canTalkDuringTimeout: makeCurrentCheck('can_moderate', 'is_owner'),
        getName: function () { return RoomUsers.current().name; }
    };


// End PartialJS/roomusers/06_permissions.js

// Begin PartialJS/roomusers/99_endclosure.js

})();

// End PartialJS/roomusers/99_endclosure.js

// End of file



// End PartialJS/roomusers.jsbundle

// Begin PartialJS/side.js

/* ------------------------ */
/* JQUERY EXTENSION METHODS */
/* ------------------------ */
/* side.js */
;


// if speed is 0, these two are considerably faster than slideUp/slideDown,
// with the caveat that it only works on elements whose standard display style is "block"
$.fn.fastHide = function (speed) {
    if (!speed)
        this.css("display", "none");
    else
        this.slideUp(speed);
}

$.fn.fastShow = function (speed) {
    if (!speed)
        this.each(function () {
            var jThis = $(this);
            jThis.css("display", jThis.hasClass("present-user") ? "inline-block" : jThis.hasClass("file-thumbnail") ? "inline-flex" : "block");
        });
    else
        this.slideDown(speed);
}

$.fn.updateCollapsible = function (quick) {
    var lis = this.find("li").not(".more");
    var delay = (quick || lis.length > 32) ? 0 : 500;
    var widget = this.closest(".sidebar-widget");
    var more = widget.find(".more");
    var setMoreText;
    if (more.is("li"))
        setMoreText = function (s) { more.attr("title", s); };
    else
        setMoreText = function (s) { more.text(s); };

    var max = this.data("show_max");
    var autohide = !!this.data("autohide");
    if (max == undefined)
        max = 5;
    var visible = lis.slice(0, max).add(lis.filter(".always-visible"));
    if (lis.length > max) {
        if (this.hasClass("expanded")) {
            lis.fastShow(delay);
        } else {
            visible.fastShow(delay);
            lis.filter(".always-visible").fastShow(delay);
            lis.not(visible).fastHide(delay);
        }
        if (this.hasClass("expanded"))
            setMoreText("only show top " + visible.length);
        else
            setMoreText("show " + (lis.length - visible.length) + " more")
        more.show();
    } else {
        more.hide();
        lis.fastShow(delay);
    }
    if (autohide) {
        if (lis.length > 0)
            widget.fastShow(delay);
        else
            widget.fastHide(delay);
    }
}

function Sidebar(ROOM_INFO, messageActionById, objects, toggleMessageAdmin, toggleSelectMode, mayBookmark) {

    var notify = objects.notify;
    var icc = objects.icc;

    var this_room_id = ROOM_INFO.id;

    var flagLimit = 10, starLimit = 10, fileLimit = 10;

    var conversationSelector = ConversationSelector(ROOM_INFO.id, toggleSelectMode, notify, $("#chat"));

    // called every 10 seconds to update the colors of my rooms depending
    // on when the last activity happened there
    function updateRoomColors() {
        $("#my-rooms > li").each(function () {
            var time = $(this).find(".room-info > .last-message > .time").html();
            var delta = secondsSince(time);

            // fade from activity = 0 to activity = 6 logarithmically over approx. 100 minutes
            // if the message comes in very quick, delta may be slightly negative, thus the "|| 0"
            // to turn NaN into 0.
            var activityLevel = Math.min(6, Math.max(0, Math.floor(Math.log(delta / 15) || 0)));
            var oldClasses = $(this).prop("className").split(" ");

            // remove the old activity-class (not all browsers support Array.filter) and add the new one
            var newClasses = ["activity-" + activityLevel];
            for (var i in oldClasses) {
                var c = oldClasses[i];
                if (c != "" && c.substr(0, 9) != "activity-")
                    newClasses.push(c);
            }
            $(this).prop("className", newClasses.join(" "));
        });
    }

    function showHideRoomData() {
        var knownRooms = $("#my-rooms .room-info");
        knownRooms.each(function () {
            var lastmsg = $(this).find(".last-message");
            if (lastmsg.find(".text").text() == "")
                lastmsg.hide();
            else
                lastmsg.show()
        });
    }

    // called by clicking on one of the "leave" link for one of my other rooms
    // in the sidebar. evt is a JavaScript event in this case.
    function leaveOtherRoomClicked(evt) {
        evt.preventDefault();
        if ($(this).hasClass("quickleave")) {
            var roomlink = $(this).closest("li").find("a").not(".reply-count").eq(0);
            // if the room name was truncated for display, the title holds the full name
            if (!confirm("Do you want to leave " + (roomlink.attr("title").substr("switch to ".length) || roomlink.text()) + "?"))
                return;
        }
        var room_id = $(this).closest("li").attr("id").replace("room-", "");
        $.post("/chats/leave/" + room_id, fkey({ quiet: true }));
        leaveOtherRoom(room_id);
    }

    function leaveThisOrAllClicked() {
        var all = false;
        if (this.id == "leaveall") {
            if (!confirm('This will remove you from all rooms; continue?'))
                return false;
            all = true;
        }
        $.post($(this).attr("href"), fkey({ quiet: true }), function () {
            if (all)
                icc.broadcast({ command: "leave all" });
            window.location = "/";
        });
        return false;
    }

    function initCollapsibles() {
        $(".sidebar-widget:has(ul.collapsible)").find(".more").click(function () {
            $(this).closest(".sidebar-widget").find("ul.collapsible").toggleClass("expanded").updateCollapsible();
            window.setTimeout(relayout, 700);
        });
    }
    function fileDelete() {
        if (confirm("This will permanently delete this file; are you sure?")) {
            var key = $(this).attr("id").replace("file-", "");
            var li = $(this).closest("li");
            $.post("/files/delete/" + this_room_id + "/" + key, fkey(), function () {
                li.slideUp(function () { li.remove(); });
            });
        }
    }
    function quickAction(el, caption, verb, compensatorCallback) {
        if (!caption || confirm(caption)) {
            var li = $(el).closest("li");
            var id = li.attr("id").replace("summary_", "");
            messageActionById(li.attr("id").replace("summary_", ""), verb, null, function (result) {
                if (result != "ok") {
                    notify(result || GENERIC_ERROR);
                    if (compensatorCallback) compensatorCallback();
                }
                else if (caption && caption.length > 0) {
                    li.slideUp(function () { $(this).remove(); });
                }
            }, notify);
        }
    }
    function quickDelete() {
        quickAction(this, 'Delete this post?', 'delete');
    }
    function quickUnflag() {
        quickAction(this, 'Remove this flags against this post?', 'unflag');
    }
    function quickFlag() {
        if (!confirmFlag(CHAT.user.canModerate()))
            return;
        var el = $(this);
        var toggle = function () { el.toggleClass("user-flag"); }; // note this may be wrong, but the callback will fix it
        quickAction(this, null, 'flag', toggle);
        toggle();
    }
    function quickUnpin() {
        quickAction(this, 'Remove the pins against this post?', 'unowner-star');
    }
    function quickUnstar() {
        quickAction(this, 'Remove the stars against this post?', 'unstar');
    }
    function quickPin() {
        quickAction(this, null, 'owner-star');
    }
    function quickStar() {
        var el = $(this);
        var toggle = function () { el.toggleClass("user-star"); }; // note this may be wrong, but the callback will fix it
        quickAction(this, null, 'star', toggle);
        toggle();
    }

    var _otherRoomMessagesContainer = {};

    // how many messages should be remembered (per room)?
    var REMEMBER_OLDER_MESSAGE_COUNT = 5;

    function otherRoomMessages(roomId) {
        roomId = parseInt(roomId);
        if (roomId in _otherRoomMessagesContainer)
            return _otherRoomMessagesContainer[roomId];

        var messages = [];
        var mentions = {};

        function add(messageId, messageText, userName, time) {
            var newArr = [];
            if (messageText) {
                var newMsg = {
                    id: messageId,
                    text: messageText,
                    user: userName,
                    time: time
                };
                newArr.push(newMsg);
            }
            for (var i in messages) {
                var msg = messages[i];
                if (msg.id != messageId)
                    newArr.push(msg);
            }
            messages = newArr.sort(function (a, b) { return b.id - a.id }).slice(0, REMEMBER_OLDER_MESSAGE_COUNT); //sort by messageId descending, so the newest one is messages[0];
        }
        function getLast() {
            if (messages.length > 0)
                return messages[0];
            return {
                id: null,
                text: "",
                user: "",
                time: null
            }
        }
        function leave() {
            delete _otherRoomMessagesContainer[roomId];
        }
        function addMention(message_id) {
            mentions[message_id] = true;
        }
        // if message_id is not given, this means "all of them"
        function dismissMention(message_id) {
            if (message_id)
                delete mentions[message_id];
            else
                mentions = {};
        }
        function getMentionCount() {
            var result = 0;
            for (id in mentions)
                result++;
            return result;
        }

        var result = {
            add: add,
            getLast: getLast,
            leave: leave,
            addMention: addMention,
            dismissMention: dismissMention,
            getMentionCount: getMentionCount
        };
        _otherRoomMessagesContainer[roomId] = result;
        return result;
    }

    function updateReplyCount(roomId) {
        var count = otherRoomMessages(roomId).getMentionCount();
        var room = $("#room-" + roomId);
        if (room.length == 0) // I'm not in that room
            return false;
        var countElement = room.find(".reply-count");

        if (count == 0) {
            countElement.remove();
            return true;
        }
        if (countElement.length == 0) {
            countElement = $("<a/>").attr("href", room.find("a:first").attr("href")).attr("target", "_self")
                .attr("title", "someone mentioned you in that room").addClass("reply-count").prependTo(room);
        }
        countElement.text(count);
        return true;
    }

    /* ------------------------ */
    /*      PUBLIC METHODS      */
    /* ------------------------ */

    var relayout_timer;

    function relayout() {
        if (relayout_timer)
            clearTimeout(relayout_timer);
        relayout_timer = setTimeout(function () {
            relayout_timer = null;
            _relayout();
        }, 500);
    }

    function _relayout(iteration, embiggen, multisOnly) {
        if (iteration == undefined)
            iteration = 10;

        var totalspace = $("#sidebar").height() - $("#input-area").height() - 8 /* <- input area padding */;
        var freespace = totalspace - $("#sidebar-content").height();

        if (embiggen != undefined) {
            if (embiggen && freespace < 10) {// we went too far -- do one step backwards and then stop
                _relayout(0, false, multisOnly);
                if (!multisOnly)
                    _relayout(5, true, true);
                return;
            }
            else if (!embiggen && freespace > 10) {// we've reached our goal
                if (!multisOnly)
                    _relayout(5, true, true);
                return;
            }

        }
        if (embiggen == undefined)
            embiggen = freespace > 0;

        var hasChanged = false;
        $(multisOnly ? "#sidebar .collapsible.many-per-line": "#sidebar .collapsible").not(".fixed-max").each(function () {
            var max = $(this).data("show_max") || 5;
            var maxmax = multisOnly ? 20 : 10;
            if (!embiggen && max > 1) {
                $(this).data("show_max", max - 1);
                hasChanged = true;
            } else if (embiggen && max < maxmax) {
                $(this).data("show_max", max + 1);
                hasChanged = true;
            }
            if (hasChanged)
                $(this).updateCollapsible(true);
        });
        if (iteration > 0 && hasChanged)
            _relayout(iteration - 1, embiggen, multisOnly);
        else if (!multisOnly)
            _relayout(5, true, true);

    }



    // returns the text-only representation of the given HTML snippet, inserting a few spaces
    // in places where spaces make faces look happy
    function getInnerText(html) {
        if (html.search(/[<>&]/) == -1)
            return html;

        var tempElement = $("<div>" + html + "</div>").eq(0);

        // append a space after the common block elements, assuming e.g. two adjacent divs
        // aren't usually styled to look like their texts are connected
        tempElement.find("p,div,h1,h2,h3,h4,h5").each(function () {
            $("<span> </span>").insertAfter(this);
        });

        // replace common "breaking" elements with spaces
        tempElement.find("br,hr,img").replaceWith("<span> </span>");

        return tempElement.text();
    }

    var mouseOverRooms = false;
    var pendingMoveToTop = [];
    $("#my-rooms").on("mouseenter", function () {
        mouseOverRooms = true;
    }).on("mouseleave", function () {
        mouseOverRooms = false;
        if (pendingMoveToTop.length) {
            for (var i = 0; i < pendingMoveToTop.length; i++) {
                var elem = pendingMoveToTop[i];
                if (elem.parent().is("#my-rooms"))
                    elem.prependTo("#my-rooms");
            }
            pendingMoveToTop = [];
            $("#my-rooms").updateCollapsible();
        }
    })


    function otherRoomActivity(roomid, roomname, username, message_text, time, message_id) {
        var room = $("#room-" + roomid);
        var is_new = false;
        if (room.length == 0 || room.hasClass("leaving")) { // also handles quick re-entry
            room.remove();
            room = $("<li/>").attr("id", "room-" + roomid).hide();
            roomname = roomname || "(unknown)";
            var inner;
            if (roomname.length > 40) {
                inner = $("<a target='_self' href='/rooms/" + roomid + "/" + urlFriendly(roomname) + "' />").text(roomname.substring(0, 37))
                    .append("<span>&hellip;</span>");
            } else {
                inner = $("<a target='_self' href='/rooms/" + roomid + "/" + urlFriendly(roomname) + "' />").text(roomname)
            }
            inner.attr("title", "switch to " + roomname).appendTo(room);
            $("<span class='quickleave'/>").insertAfter(inner).attr("title", "leave that room");
            room.append(div("room-info").append(div("last-message").append(span("user-name"), ": ", span("text"), div("time data"))));
            is_new = true;
            if (mouseOverRooms)
                room.appendTo("#my-rooms");
        }
        if (arguments.length > 2) {
            var container = otherRoomMessages(roomid);
            container.add(message_id, message_text, username, time);
            var msg = container.getLast();
            room.find(".room-info > .last-message > .user-name").text(msg.user);
            room.find(".room-info > .last-message > .text").text(getInnerText(msg.text || ""));
            room.find(".room-info > .last-message > .time").text(msg.time);
        }
        if (mouseOverRooms)
            pendingMoveToTop.push(room);
        else
            room.prependTo("#my-rooms").slideDown();
        updateRoomColors();
        if (is_new) {
            $("#room-ad").slideUp(function () { $("#room-ad").remove(); });
            relayout();
        } //  else
        if (!mouseOverRooms)
            $("#my-rooms").updateCollapsible();
        showHideRoomData();
    }

    function lastOtherRoomMessageTime() {
        var timediv = $("#my-rooms > li:first .time.data");
        if (!timediv.length)
            return 1;
        return parseInt(timediv.text());
    }

    function leaveOtherRoom(roomid) {
        otherRoomMessages(roomid).leave();
        $("#room-" + roomid).addClass("leaving").slideUp(function () { $(this).remove(); showHideRoomData(); $("#my-rooms").updateCollapsible(); relayout(); });
    }

    function otherRoomMention(roomid, message_id) {
        otherRoomMessages(roomid).addMention(message_id);
        return updateReplyCount(roomid); // true if I'm in that room, false if not
    }

    function dismissOtherRoomMention(roomid, message_id) {
        otherRoomMessages(roomid).dismissMention(message_id);
        updateReplyCount(roomid);
    }

    function updateStarTimes() {
        $("#starred-posts ul li").each(function () {
            var time = parseInt($(this).attr("data-time")) + SERVER_TIME_OFFSET;
            if (isNaN(time))
                return;
            $(".relativetime", this).text(ToRelativeTimeMini(time, true));
        });
    }
    var updateStarTimesInterval;

    function updateStars() {
        if (updateStarTimesInterval) {
            window.clearInterval(updateStarTimesInterval);
            updateStarTimesInterval = null;
        }

        starLimit = 0;
        $("#starred-posts ul").load("/chats/stars/" + this_room_id + "?count=" + starLimit, function () {
            $("#starred-posts ul").updateCollapsible(true);
            relayout();
            updateStarTimesInterval = window.setInterval(updateStarTimes, 120000); // two minutes
        });
    }

    function noFlags() {
        hideFlagCounter($("#flag-count").text(0));
    }
    function hideFlagCounter(element) {
        element.fadeOut();
    }

    function updateAdminCounters(event_id) {
        var el = $("#flag-count");
        if (el.length > 0) {
            var url = "/admin/counters";
            if (event_id)
                url += "?id=" + event_id;
            $.get(url, function (data) {
                el.find("a").text(data.flags);
                if (data.flags) show(el); else hideFlagCounter(el);
                el = $("#modflag-count");
                el.find("a").text(data.modflags);
                if (data.modflags) show(el); else hideFlagCounter(el);
            });
        }
        function show(element) {
            element.css("visibility", "visible");
            element.fadeIn();
        }
    }
    function updateFiles() {
        fileLimit = 0;
        $("#room-files ul").load("/chats/files/" + this_room_id + "?count=" + fileLimit, function () {
            /*$("#room-files .expandvotes").click(function () {
            fileLimit = 0; updateFiles(); return false;
            });*/
            $("#room-files ul").updateCollapsible(true);
            relayout();
        });
    }
    function updateRoomMeta() {
        $.getJSON("/rooms/thumbs/" + this_room_id, updateRoomMetaFromThumb);
    }
    function updateRoomMetaFromThumb(data) {
        if (!data)
            return;
        if (data.name)
            $("#roomname").text(data.name);
        if (data.description)
            $("#roomdesc").html(data.description);

        if (data.isFavorite)
            $("#toggle-favorite").addClass("favorite-room");
        else
            $("#toggle-favorite").removeClass("favorite-room");

        $("#room-tags").html(data.tags ?? "");

        document.title = document.title.replace(/^(\(\d*\*?\) )?(.*)( \| [^|]*)$/, "$1" + data.name.replace(/\$/g, "$$$$") + "$3");
    }
    function toggleFavorite() {
        $("#toggle-favorite").toggleClass("favorite-room");
        $.post("/rooms/favorite", fkey({ roomId: this_room_id }), updateRoomMetaFromThumb);
    }
    function summaryMenu(evt) {
        evt.stopPropagation();
        var message = $(this).closest("li");
        var menu = popUp(evt.pageX, evt.pageY, message);
        var verb;
        var verbs = [];
        var info = $("<div/>").appendTo(menu);
        var id = message.attr("id").replace("summary_", "");
        info.html("<a href=\"" + PERMALINK(id) + "\">permalink</a> | <a href=\"/messages/" + id + "/history\">history</a><br/>");

        if (message.find(".sidebar-vote.user-star").length > 0) {
            verb = "unstar";
            verbs.push("starred");
        } else
            verb = "star";
        var isMine = false;
        var isOwnerOrRoomMod = CHAT.user.isOwner() || CHAT.user.canModerate();
        if (isOwnerOrRoomMod || !isMine) {
            $("<span/>").addClass("star").html("<span class='sprite sprite-icon-star'/> " + verb + " as interesting").click(quickStar).click(menu.close).attr("title", "click to " + verb).appendTo(menu);
        }
        var hasFlagged = false;
        if (message.find(".sidebar-vote.user-flag").length > 0) {
            hasFlagged = true;
            verbs.push("flagged");
        }

        if (verbs.length > 0)
            info.html(info.html() + "You have " + verbs.join(" and ") + " this message.<br/>");

        if (!hasFlagged && (isOwnerOrRoomMod || !isMine)) {
            menu.append("<br/>");
            $("<span/>").addClass("flag").html("<span class='sprite sprite-icon-flag'/> flag as spam/offensive").click(quickFlag).click(menu.close).attr("title", "click to " + verb).appendTo(menu);
        }
        menu.append($("<br/><br/>"));

        if (isOwnerOrRoomMod) {
            if ($(this).hasClass("quick-unstar")) {
                var txt = "pin this item";
                var func = quickPin;

                if ($(this).siblings(".sidebar-vote.stars.owner-star").length > 0) {
                    txt = "unpin this item";
                    func = quickUnpin;
                }
                menu.append($("<span/>").addClass("owner-star").text(txt).prepend('<span class="img"></span>').click(func).click(menu.close).attr("title", txt));
                menu.append(" | ");
                menu.append($("<span/>").addClass("star").html("cancel stars ").click(quickUnstar).click(menu.close).attr("title", "cancel stars"));
            }
            if ($(this).hasClass("quick-unflag")) {
                menu.append($("<span/>").addClass("delete").html("delete").click(quickDelete).click(menu.close).attr("title", "click to delete"));
                menu.append(" | ");
                menu.append($("<span/>").addClass("flag").html("cancel flags").click(quickUnflag).click(menu.close).attr("title", "cancel flags"));
            }
        }
    }
    function roomMenu(evt) {
        if (evt.shiftKey || evt.ctrlKey || evt.altKey) return true;
        var roomSuffix = $("#about-room").attr("href").replace("/rooms/info/", "");
        var roomId = ROOM_INFO.id;
        var roomName = $("#roomname").text()
        evt.preventDefault();
        evt.stopPropagation();
        var menu = popUp(evt.pageX, evt.pageY).addClass("room-popup");
        $("<h2/>").text(roomName).appendTo(menu);

        // Assuming that *most* rooms will have an activity graph, reserve the space in beforehand
        // and only free it if not graph is available. This way, the menu won't resize/shift on load
        // in the usual case. Note that we only load the graph at the end, so all the links are available
        // from the start
        var graphContainer = $("<div/>").css({ width: 192, height: 25 }).appendTo(menu);

        var section_owner, section_mod, section_talk;

        section_talk = $("<div/>").appendTo(menu);

        if (CHAT.user.isOwner()) {
            $("<h5/>").text("Room owner").appendTo(menu);
            section_owner = $("<div/>").appendTo(menu);
        }

        if (CHAT.user.canModerate()) {
            $("<h5/>").text("Moderator").appendTo(menu);
            section_mod = $("<div/>").appendTo(menu);
        }

        var makeLink = function (text, href, self) {
            var link = $("<a/>").text(text).attr("href", href).attr('rel', 'nofollow noreferrer');
            if (self)
                link.attr("target", "_self");
            return $("<div/>").append(link);
        }
        makeLink("full transcript", "/transcript/" + roomId).appendTo(section_talk);
        if (mayBookmark) {
            makeLink("create new bookmark", "#").click(function (evt) {
                evt.preventDefault();
                conversationSelector.Dialog();
            }).appendTo(section_talk)

        }

        if (section_owner || section_mod) {

            makeLink("schedule events", "/rooms/info/" + roomSuffix + "?tab=schedule").appendTo(section_owner || section_mod);

            if (section_owner) {
                makeLink("control access", "/rooms/info/" + roomSuffix + "?tab=access").appendTo(section_owner);
            }

            makeLink("manage feeds", "/rooms/info/" + roomSuffix + "?tab=feeds").appendTo(section_owner || section_mod);

            makeLink("timeout", "#").click(function (evt) {
                evt.preventDefault();
                var dur = timeoutPrompt(evt, "<h2>Timeout</h2>" +
                    "<p>This should only be used to help control an off-topic discussion.</p>" +
                    "<p>You should also explain why the room is in timeout.</p>" +
                    "<p>Enter the time in seconds:</p>", function (dur, message) {
                    if (dur && dur.length > 0) {
                        $.post("/rooms/timeout/" + roomSuffix, fkey({ duration: dur, reason: message }))
                            .done(function (result) {
                                if (result != "Timeout cleared" && result != "Timeout applied") {
                                    notify(result || GENERIC_ERROR);
                                }
                            });
                    }
                });
                //menu.close();
                return false;
            }).appendTo(section_owner || section_mod);
        }

        if (section_mod) {
            makeLink((ROOM_INFO.frozen ? "un" : "") + "freeze this room", "#").click(function (evt) {
                evt.preventDefault();
                var message = ROOM_INFO.frozen ? "Do you want to unfreeze this room, allowing new messages to be posted?"
                    : "Do you want to freeze this room, preventing regular users from talking?";

                if (window.confirm(message)) {
                    $.post('/rooms/setfrozen/' + roomId, fkey({ freeze: !ROOM_INFO.frozen }));
                    menu.close();
                }
            }).appendTo(section_mod);

            makeLink((ROOM_INFO.deleted ? "un" : "") + "delete this room", "#").click(function (evt) {
                evt.preventDefault();
                var message = ROOM_INFO.deleted ? "Do you want to undelete this room, making it visible again to regular users?"
                    : "Do you want to delete this room and remove all users from it, including yourself?";
                if (window.confirm(message)) {
                    $.post('/rooms/setdeleted/' + roomId, fkey({ "delete": !ROOM_INFO.deleted }));
                    menu.close();
                }
            }).appendTo(section_mod);
        }
        if (toggleMessageAdmin != null) {
            var messageAdminText = section_mod ? "move/delete messages" : "move messages";

            makeLink(messageAdminText, "#").click(function () {
                menu.close(); toggleMessageAdmin(); return false;
            }).appendTo(section_mod || section_owner);
        }

        var loader_icon = $("<img/>").attr("src", IMAGE("ajax-loader.gif")).appendTo(graphContainer);

        var url = "/rooms/thumbs/" + roomId;
        $.getJSON(url, {showUsage: true, host: ROOM_INFO.host}, function (data) {
            loader_icon.remove();
            if (data.usage)
                graphContainer.html(data.usage);
            else
                graphContainer.slideUp();
        });
    }

    /* ------------------------ */
    /*      INITIALIZATION      */
    /* ------------------------ */
    updateRoomColors();
    showHideRoomData();
    initSearchBox();

    $("#starred-posts").on("click", ".quick-unstar", summaryMenu)
        .on("click", ".sidebar-vote", quickStar);

    $("#my-rooms").on("click", "span.quickleave", leaveOtherRoomClicked);
    $("#leave, #leaveall").click(leaveThisOrAllClicked);
    $("#room-files").on("click", ".quick-delete", fileDelete);
    $("#my-rooms").data("autohide", true).updateCollapsible(); /* it's prepopulated server-side */
    $("#my-rooms,#starred-posts ul").data("autohide", true)
    $("#toggle-favorite").click(toggleFavorite);
    $("#room-menu").click(roomMenu);
    $("#present-users").data("show_max", 32);
    $("#rejoin-favs").click(function () {
        $.post("/chats/join/favorite", fkey({quiet: true, immediate: true}), function() {
            $("#rejoin-favs").fadeOut(function() { $("#rejoin-favs").remove(); });
        });
    });
    initCollapsibles();

    window.setInterval(function () {
        updateRoomColors();
    }, 10000);
    $(window).resize(relayout);


    var sidebarObject = {
        relayout: relayout,
        leaveOtherRoom: leaveOtherRoom,
        otherRoomActivity: otherRoomActivity,
        otherRoomMention: otherRoomMention,
        updateStars: updateStars,
        updateFiles: updateFiles,
        updateRoomMeta: updateRoomMeta,
        updateAdminCounters: updateAdminCounters,
        dismissOtherRoomMention: dismissOtherRoomMention,
        lastOtherRoomMessageTime: lastOtherRoomMessageTime,
        noFlags : noFlags
    };
    return sidebarObject;
}

// End PartialJS/side.js

// Begin PartialJS/crosstab.js



function CrossTab(thisRoomId) {

    var player;
    function initSound(options) {
        var playerEl = $("#jplayer");
        if (playerEl.length > 0 && playerEl.jPlayer) {
            playerEl.jPlayer({
                ready: function () {
                    playerEl.jPlayer("setMedia", { mp3: options.file, oga: options.file.replace(/\.mp3$/, ".ogg") });
                    player = playerEl;
                },
                swfPath: options.swfPath,
                solution: "html, flash",
                supplied: "mp3,oga",
                volume: options.vol / 100
            });
        }
    }

    var desktopNotify;
    function initDesktopNotificatons(n) {
        desktopNotify = n;
    }

    var messenger = ByTheWay("chat:crosstab", /*receiveOwn=*/ true);

    var actions = {
        "sound": function () {
            if (player) {
                player.jPlayer("play")
                return true;
            }
            return false;
        },
        "toast": function (opts) {
            if (!desktopNotify)
                return false;
            return desktopNotify(opts);
        }
    }

    var KNOWN = {};
    var LAST_OWN_ANNOUNCE = 0;

    function announceSelf(initial) {
        messenger.broadcast({command: "announce", roomId: thisRoomId, focus: document.hasFocus && document.hasFocus(), initial: initial})
        LAST_OWN_ANNOUNCE = now();
    }

    $(window).on("unload", function () { messenger.broadcast({command: "gone"})});
    $(window).on("focus", function () { announceSelf(); });
    announceSelf(true);
    setInterval(function () {
        if (now() - LAST_OWN_ANNOUNCE > 30)
            announceSelf();
    }, 60000);

    function performAction(action, param) {
        var act = actions[action];
        if (!act)
            return false;
        return act(param);
    }

    messenger.onReceive(function (message, envelope, respond) {
        if (message.command === "announce") {
            var focusTime = message.focus ? now() : (KNOWN[envelope.sender] || {focus: 0}).focus;
            KNOWN[envelope.sender] = {
                lastfocus: focusTime,
                roomId: message.roomId,
                seen: now(),
                sender: envelope.sender
            }
            if (message.initial)
                announceSelf();
        } else if (message.command === "gone") {
            delete KNOWN[envelope.sender];
        }
        else if (message.command === "doit") {
            if (Date.now() - envelope.time > (message.maxage || 300)) {
                return respond(false);
            }
            return respond(performAction(message.action, message.param));
        }
    });

    function initiateAction(name, roomid, param) {

        var sorted = Generator(KNOWN).map("1").sortBy(function (r) {
            var byRoom = r.roomId === roomid ? 0 : 1;
            var byFocus = r.lastfocus ? .5 / r.lastfocus : .6;
            return byRoom + byFocus;
        }).toArray();
        var first = true;
        tryNext();
        function callback(successes) {
            if (!successes.length || !successes[0])
                tryNext();
        }
        function tryNext() {
            var r = sorted.shift();
            if (!r) {
                performAction(name, param); // nobody there -- let's do it ourselves (note that this implies that the local message took longer to deliver than the timeout allowed)
                return;
            }
            messenger.request({command: "doit", action: name, param: param, maxage: first ? 1000 : 300}, callback, r.sender, first ? 1500 : 500);
            first = false;
        }
    }

    function playSound(roomId) {
        initiateAction("sound", roomId)
    }

    function desktopNotification(roomId, options) {
        initiateAction("toast", roomId, options)
    }


    return {
        initSound: initSound,
        playSound: playSound,
        initDesktopNotificatons: initDesktopNotificatons,
        desktopNotification: desktopNotification
    }

}

// End PartialJS/crosstab.js

// Begin PartialJS/thethingthatgetsdatafromtheserver.js

(function () {

    var LAST_KNOWN_ID, ROOM_ID, ROOM_KEY, GET_POLLING_DELAY, DATA_IS_GOOD, options,
        MIN_SECONDS_BETWEEN_SOCKET_TRIES = 30;

    window.TheThingThatGetsDataFromTheServer = function (opts) {
        if (options)
            throw "already initialized";
        options = opts;
        LAST_KNOWN_ID = opts.lastKnownId;
        ROOM_ID = opts.roomId;
        ROOM_KEY = opts.roomKey; // "r" + ROOM_ID or "h" + ROOM_ID
        GET_POLLING_DELAY = opts.getPollingDelay;

        var conn = ServerConnection(opts.useWebsocket);
        var iccConn = ICCConnection();
        var iccActive;
        iccConn.onData = function (data, since) {
            noProblem();
            if (!iccActive)
                debugMessage("got data from ICC; closing server connection");
            iccActive = true;
            conn.close();
            opts.onData(data, since);
        }
        iccConn.onClose = function () {
            debugMessage("opening server connection");
            iccActive = false;
            conn.open();
        }
        conn.onData = function (data, since, source) {
            noProblem();
            data.since = since;
            if (options.icc)
                options.icc.broadcast({command: "poll", data: data, since: since, until: LAST_KNOWN_ID, source: source});
            opts.onData(data);
        };
        conn.onClose = function (deliberate) {
            if (!deliberate) {
                problem();
                setTimeout(function () { conn.open(); }, 4000);
            }
        };
        if (options.icc) {
            options.icc.receive(function (message) {
                if (message.content.command === "data expected" && !iccActive) {
                    conn.dataExpected();
                }
            });
        }
        conn.open();
        return {
            windowClosing: function () {
                if (!iccActive) {
                    opts.icc.broadcast({command: "master closing"});
                    conn.close();
                }
            },
            dataExpected: function () {
                if (iccActive)
                    options.icc.broadcast({command: "data expected"});
                else
                    conn.dataExpected();
            }
        };
    }

    var lastNoProblem = now();
    setInterval(function () {
        if (secondsSince(lastNoProblem) > 120 && !houstonWeHave) {
            // We should never end up here. In the very worst case (no socket data after 40 seconds, followed by
            // three failed polls, each of which can be at most 15 seconds delay plus 10 seconds timeout) the user
            // should have been notified of a "problem connecting to the server". If we indeed end up here,
            // there is a bug somewhere.
            debugMessage("connection problem was only noticed by life check");
            problem();
        }
    }, 20000);

    var houstonWeHave;
    function noProblem() {
        lastNoProblem = now();
        if (!houstonWeHave)
            return;
        houstonWeHave = false;
        setTimeout(options.onProblemResolved, 0);
    }

    function problem() {
        houstonWeHave = true;
        setTimeout(options.onProblem, 0);
    }

    function ICCConnection() {
        var result = {};

        if (!options.icc)
            return result;

        options.icc.receive(function (message) {
            var command = message.content.command;
            if (command !== "poll" && command !== "master closing")
                return;
            if (!message.senderIsSenior)
                return debugMessage("sender icc is not senior; ignoring");

            if (command === "master closing") {
                debugMessage("master is closing");
                expectDataIn(1);
                return;
            }

            var data = message.content.data;
            if (!(ROOM_KEY in data)) // most likely: we're an anonymous user in a different room than the icc master
                return;

            var since = message.content.since,
                until = message.content.until;
            if (!since)
                return; // sent by an old client
            if (since > LAST_KNOWN_ID) // don't handle -- there may be missing events
                return debugMessage("icc data is too new");

            if (until < LAST_KNOWN_ID) // nothing new. Note we allow "==", so the socket's courtesy ping is counted
                return debugMessage("icc data is too old");

            LAST_KNOWN_ID = until;
            expectDataIn(message.content.source === "socket" ? 40000 : 2 * GET_POLLING_DELAY())

            result.onData(data, since);
        });

        var dataExpectedTimeoutId;
        function expectDataIn(ms) {
            if (dataExpectedTimeoutId)
                clearTimeout(dataExpectedTimeoutId);
            dataExpectedTimeoutId = setTimeout(dataExpected, ms);
            $("#timer").text("icc " + (ms | 0));
        }
        function dataExpected() {
            dataExpectedTimeoutId = null;
            debugMessage("no data received via icc in time");
            result.onClose();
        }
        return result;
    }


    function ServerConnection(useWebsocket) {
        var pollingConn = PollingConnection(),
            socketConn = useWebsocket ? SocketConnection() : null,
            openConn,
            retrySocketSeconds = MIN_SECONDS_BETWEEN_SOCKET_TRIES,
            retrySocketTimeoutId,
            result;

        result = {
            open: open,
            close: close,
            dataExpected: dataExpected
        };

        if (socketConn) {
            socketConn.onClose = function (deliberate) {
                if (openConn !== this)
                    return;
                if (deliberate) {
                    result.onClose(true);
                    openConn = null;
                } else {
                    debugMessage("socket closed or unable to open; starting polling, will retry socket in " + retrySocketSeconds + "s");
                    openConn = pollingConn;
                    pollingConn.open();
                    retrySocketTimeoutId = setTimeout(function () {
                        retrySocketTimeoutId = null;
                        if (openConn !== pollingConn) {
                            debugMessage("not retrying socket since not polling");
                            return;
                        }
                        openConn = socketConn;
                        pollingConn.close();
                        socketConn.open();
                    }, retrySocketSeconds * 1000);
                    retrySocketSeconds *= 2;
                }
            }
            socketConn.onData = connData;
        }
        pollingConn.onClose = function (deliberate) {
            if (openConn !== this)
                return;
            result.onClose(deliberate);
            openConn = null;
        }
        pollingConn.onData = connData;

        // attached to both connections
        function connData(data, since) {
            if (openConn === this) {
                var source = this === pollingConn ? "poll" : "socket"
                if (ROOM_KEY in data && "t" in data[ROOM_KEY])
                    LAST_KNOWN_ID = data[ROOM_KEY].t
                result.onData(data, since, source);
            }
            if (socketConn === this) {
                retrySocketSeconds = MIN_SECONDS_BETWEEN_SOCKET_TRIES;
            }
        }

        function open() {
            if (openConn)
                return;
            if (retrySocketTimeoutId)
                openConn = pollingConn;
            else
                openConn = socketConn || pollingConn;
            openConn.open();
        }

        function close() {
            if (!openConn)
                return;
            openConn.close();
            openConn = null;
        }

        function dataExpected() {
            if (openConn == pollingConn)
                pollingConn.dataExpected();
        }

        return result;
    }

    function PollingConnection() {

        var isOpen, currentRequest, pollTimeoutId,
            result = {
                open: open,
                close: function () { close(true); },
                dataExpected: function () { queuePoll(2000); }
            };

        function open() {
            if (isOpen)
                return;
            isOpen = true;
            poll();
        }

        function close(deliberate) {
            if (!isOpen)
                return;
            isOpen = false;
            if (pollTimeoutId) {
                clearTimeout(pollTimeoutId);
                pollTimeoutId = null;
            }
            result.onClose(deliberate);
        }

        function poll() {

            if (currentRequest || !isOpen)
                return;

            var body = fkey();
            var since = LAST_KNOWN_ID;
            body[ROOM_KEY] = since;
            currentRequest = $.ajax("/events", {
                data: body,
                timeout: 10000,
                type: "POST"
            }).always(function () { currentRequest = null; setTimeout(function () { queuePoll(); }, 0); }) // queuePoll is wrapped to prevent the ms-offset sent by some browsers to the timeout callback from being passed
                .done(function (data) { dataReceived(data, LAST_KNOWN_ID); })
                .fail(pollFailed);
        }

        function queuePoll(optionalMS) {
            if (isOpen) {
                if (pollTimeoutId)
                    clearTimeout(pollTimeoutId);
                var ms;
                if (typeof optionalMS === "number")
                    ms = optionalMS;
                else
                    ms = GET_POLLING_DELAY();
                pollTimeoutId = setTimeout(function () { pollTimeoutId = null; poll(); }, ms);
                $("#timer").text("poll " + (ms | 0));
            }
        }

        var failCount = 0;

        function dataReceived(data, since) {
            failCount = 0;
            if (!isOpen)
                return;
            result.onData(data, since);
        }

        function pollFailed() {
            failCount++;
            if (failCount >= 3)
                close(false);
        }

        return result;


    }

    var SocketConstructor = window.WebSocket || window.MozWebSocket;

    function SocketConnection() {

        if (!SocketConstructor)
            return null;

        var result, currentSocket;

        result = {
            open: open,
            close: function () { close(true); }
        };

        function haveActiveSocket() {
            return currentSocket && (currentSocket.readyState === SocketConstructor.OPEN || currentSocket.readyState === SocketConstructor.CONNECTING);
        }

        function open() {
            if (haveActiveSocket())
                return;

            getSocketUrl().done(function (url) {
                try {
                    openSocket(url);
                } catch (e) {
                    debugMessage("exception thrown when opening websocket; treating as closed");
                    result.onClose(false);
                }
            }).fail(function () { result.onClose(false); });
        }
        function close(deliberately) {
            if (!haveActiveSocket())
                return;

            // couldn't get closeEvent.reason to work, hence this way
            currentSocket.closingDeliberately = deliberately;
            currentSocket.close();
            currentSocket = null;
        }

        function getSocketUrl() {
            return $.post('/ws-auth', fkey({ roomid: ROOM_ID })).pipe(function (authResult) { return { url: authResult.url + '?l=' + LAST_KNOWN_ID, since: LAST_KNOWN_ID }; });
        }

        var dataExpectedTimeoutId;
        function expectDataIn(ms) {
            if (dataExpectedTimeoutId)
                clearTimeout(dataExpectedTimeoutId);
            dataExpectedTimeoutId = setTimeout(dataExpected, ms);
            $("#timer").text("ws " + (ms | 0));
        }
        function dataExpected() {
            dataExpectedTimeoutId = null;
            if (!haveActiveSocket()) {
                debugMessage("no data received via socket in time, but ignoring because there's no active socket: " +
                    (currentSocket? currentSocket.readyState : "none")  );
                return;
            }
            debugMessage("no data received via socket in time");
            close(false);
        }

        function openSocket(info) {
            if (haveActiveSocket())
                return;

            var url = info.url,
                since = info.since;

            currentSocket = new SocketConstructor(url);

            currentSocket.onopen = function () {
                debugMessage("socket successfully opened");
            };
            currentSocket.onclose = function () {
                currentSocket = null;
                result.onClose(this.closingDeliberately);
            };
            currentSocket.onmessage = function (msg) {
                if (msg && msg.data) {
                    var data = $.parseJSON(msg.data);
                    result.onData(data, since);

                    if (ROOM_KEY in data && "t" in data[ROOM_KEY]) {
                        since = data[ROOM_KEY].t
                    }
                    expectDataIn(40000);
                }
            };
            currentSocket.onerror = function () {
                /* At least in Chrome, onerror is called when .close()ing
                 * a socket that's still in the CONNECTING phase (I don't think it should,
                 * but it does anyway). We obviously don't want to onClose(false) in that
                 * case, and the closure takes care of setting currentSocket to null. Hence
                 * the early return.
                 *
                 * Note that closing while connecting is a pretty likely situation:
                 * Imagine that browser tab A is the ICC senior and has an active websocket
                 * connection, and tab B is listening to the ICC. Now in tab A, the user
                 * clicks on a link to a different room.
                 *
                 * 1. A broadcasts "master closing", then loads a new page.
                 * 2. B receives "master closing", thus starts connecting to the socket server.
                 * 3. B's socket connection has reached the "open" state
                 * 4. B's socket connection receives the initial message, passes on via ICC
                 * 5. A has finished loading the page and starts connecting to the socket server.
                 * 6. A's socket connection has reached the "open" state.
                 *
                 * (A while later A will receive the next ICC message, note that it itself
                 * isn't senior, and then close).
                 *
                 * Now that's all fine and dandy, but especially with higher-latency connections,
                 * there's a pretty good chance that the time between between 2. and 4. is long
                 * enough for 5. to happen before 4. And in that case, A receives the ICC message
                 * from 4. and already has a CONNECTING but not OPEN socket, which it will then close.
                */
                if (this.closingDeliberately) {
                    debugMessage("ignoring error on deliberately closed socket");
                    return;
                }
                currentSocket = null;
                result.onClose(false);
            };
            expectDataIn(10000);
        }
        return result;

    }

})();

// End PartialJS/thethingthatgetsdatafromtheserver.js

// Begin PartialJS/chat.js

/// <reference path="~/Views/Room/SingleRoom.cshtml" />
/* chat.js */
;
var hiddenUsers = {};
var SERVER_TIME_OFFSET;

var MIN_MENTION_LENGTH = 2;

// declared outside the StartChat() closure so it can be changed from the console for debugging purposes
var TITLE_UPDATE_DELAY = 200;

// Returns the height the element would have if it were visible.
// Note that this is somewhat special-cased to its (currently) only
// use in updateMonologue (in particular, note "clear: both")
$.fn.potentialHeight = function () {
    if (this.is(":visible"))
        return this.height();
    var wrapper = $("<div/>").css({ height: 0, clear: "both", overflow: "hidden" });
    var result = this.wrap(wrapper).show().height();
    this.hide().unwrap();
    wrapper.removeData(); // for some reason, unwrap() doesn't clear the cache
    return result;
}

$.fn.putInto = function (target) {
    target.put(this);
    return this;
};

$.fn.setVisible = function (yesno) {
    if (yesno)
        this.show();
    else
        this.hide();
    return this;
}

$.fn.messageId = function () {
    return parseInt(this.attr("id").substr(8)); // "message-".length or "summary-"(starred messages)
}

// used for time, edits, starred, and flagged on messages, so these four can be set in one go
// in newMessage(), where it has a large impact on page load time
$.fn.info = function (key, val) {
    var i = this.data("info");
    if (arguments.length == 1) {
        if (!i)
            return undefined;
        return i[key];
    }
    else {
        if (i)
            i[key] = val;
        else {
            i = {};
            i[key] = val;
            this.data("info", i);
        }
        return this;
    }
}

var containers = {
    needyMonologues: Container(),
    timeTreatmentNeedy: Container() // .needs-elapsed or .needs-timestamp
};

// options needs .file and .vol
function SoundManager(crossTab) {
    var next_sound_level = 99;
    var next_room_id = null;
    var isIcc;

    function setSoundIcon() {
        var level = getNoiseLevel();
        if (level > 1) level--;
        $("#sound").prop("className", "sprite sprite-sound-" + level);
    }

    // 0: off, 1: mention, 2: visible room, 3: allRooms
    function getNoiseLevel() {
        var nl = $.cookie("sl");
        if (nl == null)
            return 1; // mention
        else
            return nl;
    }
    function setNoiseLevel(level) {
        $.cookie("sl", level, { path: "/", expires: 90 });
    }

    /// <summary>
    /// makes noise
    /// </summary>
    /// <returns>noise</returns>
    function makeNoise(level, roomId) {
        if (level > getNoiseLevel())
            return;
        crossTab.playSound(roomId);
    }

    // TODO: sidebar?
    function soundMenu(evt) {
        evt.stopPropagation();
        var menu = popUp(evt.pageX, evt.pageY);
        var callback = function (evt) {
            setNoiseLevel($(this).attr("id").replace("sound-level-", ""));
            menu.close();
            setSoundIcon();
            evt.preventDefault();
        }
        $("<h2/>").html("Sound notifications").appendTo(menu);
        var list = $("<ul/>").addClass("no-bullets").appendTo(menu);
        var choices = ["none", "when mentioned", "visible room", "all my rooms"];
        var nl = getNoiseLevel()
        for (var i = 0; i < choices.length; i++) {
            var curr = i == nl ? " (current setting)" : "";
            $("<li/>").append($("<a/>").attr("href", "#").text(choices[i] + curr).attr("id", "sound-level-" + i)
                .click(callback).appendTo(menu)).appendTo(list);
        }
        evt.preventDefault();
    }

    /* --- Initialization --- */
    setSoundIcon();
    $("#sound").click(soundMenu);

    /* --- Public Methods --- */
    function queue(level, roomid) {
        /*  - If I receive the events from the server, play a sound if and only if I should, according to sound settings
            - If I receive them from ICC:
              - if it's a mention, don't play it (the client that's talking to the server already did)
              - if the sound setting is "all my rooms", don't play it (same thing)
              - if the sound setting is "visible rooms", play it if and only if there's an event in this room (i.e. level = 2) */

        if (!isIcc || level == 2) {
            if (level < next_sound_level) {
                next_sound_level = level;
                if (roomid)
                    next_room_id = roomid;
            }
        }
    }

    function play() {
        makeNoise(next_sound_level, next_room_id);
        next_room_id = null;
        next_sound_level = 99;
    }

    function setIcc(yesno) {
        isIcc = yesno;
    }

    return {
        queue: queue,
        play: play,
        setIcc: setIcc
    };
}

(function() {
    var eventHandlerHooks = [];
    CHAT.addEventHandlerHook = function (hook) {
        eventHandlerHooks.push(hook);
    };
    CHAT.getEventHandlerHooks = function () {
        return eventHandlerHooks;
    };
})();

function StartChat(OPTIONS, current_user_id, ROOM_INFO, ignoreList, mobile, highlights, pendingReplies) {

    var Generator = window.Generator;

    CHAT.CURRENT_ROOM_ID = ROOM_INFO.id;
    CHAT.CURRENT_USER_ID = current_user_id;
    CHAT.IS_MOBILE = mobile;
    var EventType = {
        MessagePosted: 1,
        MessageEdited: 2,
        UserEntered: 3,
        UserLeft: 4,
        RoomNameChanged: 5,
        MessageStarred: 6,
        DebugMessage: 7,
        UserMentioned: 8,
        MessageFlagged: 9,
        MessageDeleted: 10,
        FileAdded: 11,
        ModeratorFlag: 12,
        UserSettingsChanged: 13,
        GlobalNotification: 14,
        AccessLevelChanged: 15,
        UserNotification: 16,
        Invitation: 17,
        MessageReply: 18,
        MessageMovedOut: 19,
        MessageMovedIn: 20,
        TimeBreak: 21,
        FeedTicker: 22,
        UserSuspended: 29,
        UserMerged: 30,
        UserNameOrAvatarChanged: 34,
        MessageUndeleted: 37,
    };
    var serverThing;
    var TIMESTAMP_DIST = 900;      // a monologue will get a time stamp if it started
    // at least this long after the last time stamp.
    // Note that (unlike with the next constant)
    // this will not force creation of a new dialog.
    // So if the user rambles on and on and on,
    // we patiently wait until a) he pauses for ten
    // minutes (MAX_PAUSE) or b) someone else says something.

    var MAX_PAUSE = 600;           // if two messages are longer apart than this,
    // a new monologue will be created and a time
    // stamp will be inserted
    var MAX_ELAPSED = 3600;        // must be greater than TIMESTAMP_DIST

    var TIMESTAMP_MIN_FREQ = mobile ? 0 : 5;   // every n'th monoluge must have a timestamp
    // (where actually n is this value plus one)

    var MAX_MESSAGES = mobile ? 50 : 100;
    var MAX_MESSAGES_DELTA = MAX_MESSAGES;

    // how many messages are allowed above the MAX_MESSAGES count?
    // Essentially this means that 20 messages will be removed at a time, instead
    // of removing them one by one. Particularly Firefox users will appreciate this,
    // since the message removal often causes flickering (https://meta.stackoverflow.com/questions/73538)
    var MESSAGE_REMOVAL_THRESHOLD = mobile ? 0 : 19;

    var lasttime = 0; // this isn't actually a time, but an event id
    var first_run = true; // set to false in pollDone() after the first AJAX return
    var windowHasFocus = true; // This isn't necessarily true, but it most cases
    // it probably would be, so sufficient for now.
    var waitingMessages = 0, waitingAlert = false;
    var next_pending_id = 0;
    var alwaysOnBottom = true;
    var scrollSpeed = 0;
    var last_message_on_blur, last_message_on_focus;

    // This will be set to true when anything comes in that causes the client to reload
    // or leave this page, preventing additional events from being handled. This ensures
    // that only the first such event is handled.
    var is_heading_out = false;

    var canTalk = $('#input').length != 0;

    function setInput(s) {
        var old = $("#input").val();
        if (old === s)
            return $("#input");
        else
            return $("#input").val(s).trigger("change");
    }

    var sidebar;
    var icc; // inter-client communicator, letting several chat windows opened by the user coordinate their work
    var sound;
    var notify, notifier;
    var replyQueue, feedTicker;
    var throttler = Throttler();
    var CROSS_TAB;

    var singleSelectCallback;
    var placeHolderImage = IMAGE('ajax-loader.gif'), notFoundImage = IMAGE('ImageNotFound.png');
    var onNewMessage; // if this is set, it will be called with every new message being created, passing the correspond jQuery DOM object and the id

    var FIXED_HEADER = $("html").hasClass("fixed-header");

    // In some browsers (Firefox), the body may have a fractional height,
    // but $().height() returns an integer (it's basically element.offsetHeight).
    // This can cause mismatches when checking "are we all the way on the bottom?".
    // Hence we try getComputedStyle first, which returns a fractional value in FF
    function scrollableHeight() {
        var scrollable = mainScrollable();
        if (window.getComputedStyle)
            return parseFloat(getComputedStyle(scrollable, null).height);
        else
            return $(scrollable).height();
    }

    function mainScrollable(windowNotBody) {
        return CHAT.NEW_MOBILE && !FIXED_HEADER ? $("main")[0] : windowNotBody ? window : document.body;
    }

    var distanceToBottom = mobile ?
        // See the problems in "Measuring the visual viewport" in https://www.quirksmode.org/mobile/viewports2.html
        // for the reason we have a fallback for window.innerHeight; not sure if that's relevant anymore, but it doesn't hurt either.
        function () {
            var s = mainScrollable(true);
            if (CHAT.NEW_MOBILE && !FIXED_HEADER)
                return s.scrollHeight - ($(s).scrollTop() + (scrollableHeight() ));
            else
                return scrollableHeight() - (window.pageYOffset + (window.innerHeight || screen.height));
        }
        :
        function () { return scrollableHeight() - ($(window).scrollTop() + $(window).height()); }


    function ReplyQueue() {
        var count = $("#reply-count");
        var queue = [];
        uniquePush = function (x) {
            if ($.inArray(x, queue) < 0)
                queue.push(x);
        };

        function show() {
            count.css("visibility", "visible");
            count.fadeIn();
        }
        function hide() {
            count.fadeOut(); // display: none destroys the gradient in IE
        }

        function updateCount() {
            var len = queue.length;
            if (len) {
                count.text(len).attr("title", "You have been mentioned" + (len == 2 ? " twice" : len > 2 ? (" " + len + " times") : "") + ". Click to show.");
                show();
            }
            else {
                hide();
            }
            updateTitle();
        };

        function add(message_id) {
            uniquePush(message_id);
            updateCount();
        }

        function len() {
            return queue.length;
        }

        // if message_id is not given, this is understood as "all of them"
        function broadcastClearance(message_id) {
            icc.broadcast({ command: "clear mention", roomid: ROOM_INFO.id, messageid: message_id });
        }

        function clear(noBroadcast) {
            if (queue.length && !noBroadcast) {
                $.post("/messages/ack", fkey({ id: queue.join(',') }));
            }
            queue = [];
            if (!noBroadcast)
                broadcastClearance();
            updateCount();
        }

        count.on("click touchstart", function (e) {
            var message_id = queue.shift();
            if (message_id) {
                var message = $("#message-" + message_id);
                if (message.length)
                    highlightElement(message, undefined, { offset: -100, onAfter: checkIfOnBottom })
                else
                    window.open(PERMALINK(message_id));
                $.post("/messages/ack", fkey({ id: message_id }));
            }
            if (!queue.length)
                broadcastClearance();
            else
                broadcastClearance(message_id);
            updateCount();
            e.preventDefault();
        });

        count.hide().css("visibility", "visible");

        return {
            add: add,
            len: len,
            clear: clear
        };
    }

    function findUsernameFromMsg($msg) {
        var userId = $msg.closest(".monologue").data("user");
        var user = CHAT.RoomUsers.getIfAvailable(userId);
        if (user) {
            return user.name;
        } else {
            return $msg.closest(".monologue").find(".username a:first").attr("title");
        }
        return null;
    }

    // Try to save the current content of the input box and the reply state to the HTML5 local storage.
    // Returns true on success (where "there's nothing there to save" is also
    // defined as success).
    function trySaveDraftAndReplyState() {
        var key = `chat:saved:${ROOM_INFO.id}`;

        var inp = $("#input");
        if (inp.length == 0)
            return true;
        var draft = inp.val();

        var replyBox = $('#reply-box');
        var replyParentId = replyBox.attr('data-parent-id');
        var $replyParentMsg = $(`#message-${replyParentId}`);
        var replyParentUsername = findUsernameFromMsg($replyParentMsg);
        var replyParentText = replyBox.find('a.s-link').text();

        var success = false;

        if (!draft && !replyParentId && !replyParentText) {
            window.localStorage.removeItem(key);
            return true;
        }

        // if there's no draft and no parent id, but somehow there's parent text or parent username, ignore it and remove
        if (!draft && !replyParentId && (replyParentText || replyParentUsername)) {
            window.localStorage.removeItem(key);
            return true;
        }

        var savedObj = { draft, replyParentId, replyParentText, replyParentUsername };

        try {
            window.localStorage.setItem(key, JSON.stringify(savedObj));
            success = true;
        } catch (ex) {}
        return success;
    }

    // If possible, restore the previously saved content and reply state of the input box
    function tryRestoreDraftAndReplyState() {
        var saved = window.localStorage.getItem(`chat:saved:${ROOM_INFO.id}`);
        try {
            var savedObj = JSON.parse(saved);
            var { draft, replyParentId, replyParentText, replyParentUsername } = { ...savedObj };

            if (!draft && !replyParentId)
                return;
            if (replyParentId)
                createReplyBox(replyParentId, replyParentText, null, replyParentUsername);
            var selectAll = !/^:\d+ $/.test(draft); // unless this is a "reply to" (probably coming from the transcript) ...
            setInput(draft).caret(selectAll ? 0 : draft.length, draft.length); // ... highlight the text for easy erasing in case of "I don't care"
        } catch (ex) {}
    }

    // This is testing for a horrible iOS bug. Setting scrollLeft in Safari/Chrome on iOS
    // sets scrollTop to 0. We check of the presence of this braindead behavior and if it's
    // there, we only scroll along the y-axis to prevent this interference. Scrolling along
    // the x-axis is almost never necessary anyway, so it's not a huge loss. Once
    // unbrokenScrollAxis has conclusively found out whether the browser is affected or not
    // it replaces itself with a function that no longer tests.
    function unbrokenScrollAxis() {
        var body = document.body;
        var top = body.scrollTop;
        var left = body.scrollLeft;
        if (top === 0) // we're at the top; the body like doesn't have enough content yet to be scrollable; assume it's not broken, but recheck next time.
            return "xy";
        body.scrollLeft = 0;
        if (body.scrollTop !== top) { // this browser is broken; only scroll along the y-axis
            unbrokenScrollAxis = function() { return "y"; }
        } else { // this browser is sane, we can scroll along both
            unbrokenScrollAxis = function() { return "xy"; }
        }
        body.scrollLeft = left;
        body.scrollTop = top;
        return unbrokenScrollAxis(); // note that this is now a different function; if we're here, the replacement has been made
    }

    var ScrollTo;
    (function () {
        var scrolling = 0;
        ScrollTo = function (target, duration, settings) {
            if (!settings && typeof duration === "object") {
                settings = duration;
                duration = null;
            }
            settings = settings || {};
            settings.axis = unbrokenScrollAxis();
            if (FIXED_HEADER && !settings.noHeaderAdjustment)
                settings.offset = (settings.offset || 0) - 44;
            var oldCallback = settings.onAfter;
            if (oldCallback) {
                settings.onAfter = function () { setTimeout(function () {scrolling--}, 0); oldCallback.apply(this, arguments); };
            } else {
                settings.onAfter = function () { setTimeout(function () {scrolling--}, 0); };
            }
            scrolling++;
            var scrollElement = $(mainScrollable(true));
            if (duration === null)
                scrollElement.scrollTo(target, settings);
            else
                scrollElement.scrollTo(target, duration, settings);
        }
        $(mainScrollable(true)).scroll(function (e) {
            var checked;

            // This may always be set to true, but shouldn't be set to false for non-manual scrolling.
            // For those cases where it should, the ScrollTo() caller will checkIfOnBottom onAfter
            if (!alwaysOnBottom) {
                checkIfOnBottom();
                checked = true;
            }

            if (scrolling) {// programmatic scrolling
                return;
            }

            var newDocHeight = mainScrollable().scrollHeight;
            if (docHeight !== newDocHeight) { // scrolling caused by change of document height (e.g. messages at the top wereremoved)
                docHeight = newDocHeight;
                return;
            }

            if (!checked)
                checkIfOnBottom();

            $(window).trigger("manualScroll");
        });
        window.setInterval(function () {
            docHeight = mainScrollable().scrollHeight;
            if (!scrolling && alwaysOnBottom && distanceToBottom() > 1) {
                ScrollTo($("#bottom"), mobile ? 0 : scrollSpeed);
            }
        }, 1000);
    })();

    function checkIfOnBottom() {
        alwaysOnBottom = distanceToBottom() <= (mobile ? 15 : 5);
    }
    var docHeight = $(document).height();

    function initHiddenUsers(newList) {
        var oldList = hiddenUsers;
        hiddenUsers = {};
        if (newList) $(newList).each(function (idx, key) {
            showHideForUser(key, true, true); // hides anything in newList
        });
        for (key in oldList) {
            if (oldList[key] && !hiddenUsers[key]) {
                showHideForUser(key, false, true);   // unhides anything no longer hidden
            }
        }
    }


    var anyHost = "[.\\w]+";
    var oneBoxRe = new RegExp("^https?://(?:"
        + "area51.stackexchange.com/proposals/\\d+" // Area51
        + "|"
        + anyHost + "/rooms/(\\d+)/conversation/[\\w-]" // bookmarked chat conversation
        + "|"
        + "(?:careers\\.)?stackoverflow.com/jobs/\\d+" // careers job listing
        + "|"
        + "\\S+\\.(?:jpe?g|png|gif|bmp|svg|webp)$" // image
        + "|"
        + "(?:bugs\\.)?(?:edge\\.)?launchpad\\.net/(?:[^/]*/\\+bug|bugs)/(\\d+)" // launchpad bug
        + "|"
        + anyHost + "/(?:transcript|chats)/(?:message/\\d+|\\d+\\?m=)" // chat message permalink
        + "|"
        + anyHost + "/rooms/\\d+(?:/[^/]*)?$" // chat room
        + "|"
        + anyHost + "/(?:q|a|questions|users)/\\d+(?:$|[?#/])" // SE question, answer, or user
        + "|"
        + "manpages\\.ubuntu\\.com/manpages/.+\\.html" // ubuntu-hosted manpage
        + "|"
        + "[a-z-]{2,15}\\.wikipedia\\.org/wiki/\\S+" // wikipedia article
        + "|"
        + "(?:www\\.)?(xkcd|xckd)\\.(?:com|org)/\\d+" // XKCD
        + "|"
        + "(?:www\\.)?youtu(?:\\.be|be\\.com)/.+" // YouTube
        + "|"
        + "blog\\.(?:serverfault|stackoverflow|stackexchange|superuser)\\.com/(?:post/|\\d{4})" // SO/SF/SU blog post
        + "|"
        + "[a-z0-9]+.blogoverflow.com/([0-9]{4})/([0-9]{1,2})/([^/]+?)/?" // Blog Overflow beta blogs
        + "|"
        + "blog.[a-z0-9]+.stackexchange.com/([0-9]{4})/([0-9]{1,2})/([^/]+?)/?" // Blog Overflow graduated blogs
        + "|"
        + "anidb\\.net/(a\\d+|perl-bin/animedb\.pl)"
        + ")", "i");

    var magicLinkRe = /\[\S+\](?!\()/;

    function looksLikeOnebox(text) {
        text = $.trim(text.replace(/^:\d+ /, ""));
        return text.substr(0, 5).toLowerCase() == "!http"
            || oneBoxRe.test(text)
            || magicLinkRe.test(text)
            || (OPTIONS.additionalOneBox && OPTIONS.additionalOneBox.test(text));
    }

    // Given the user's input, returns the HTML that should be displayed as the message
    // until the "real" rendered version comes back from the server. This should only ever
    // ever be used with input by the current user (*not* with something that was received
    // from elsewhere), since parts of this are not written with too much security consideration;
    // the only purpose is to display something that comes as close as possible to the server-rendered
    // version for the few seconds we're waiting for the server response
    function clientSideRender(text) {
        var html = addLinks(markdownExtensions(markdownMini(text), false));
        if (looksLikeOnebox(text))
            html += " <img src=\"" + IMAGE("progress-dots.gif") + "\" class=\"progressbar\" />";

        return html;
    }

    var mentionRegex = new RegExp("@((?:[^\\s!?();:,\\/+&<]|&#\\d{2,};){" + MIN_MENTION_LENGTH + ",})", "ig");
    function markdownExtensions(text, applyMentions) {
        if (!text) return "<span class='deleted'>(removed)</span>";
        if (text.substring(0, 4) == '<pre') return text;

        if (applyMentions) {
            text = text.replace(mentionRegex, function (match, grp) {
                var entititiesReplaced = grp.replace(/&#(\d+);/g, function(m, g) { return String.fromCharCode(parseInt(g)); });
                var normalized = normalizeUserName(entititiesReplaced);
                var normalizedCurrent = normalizeUserName(CHAT.user.getName());

                var mention, rest;

                function tryOmitting(whatNormalized, whatOriginal) {
                    if (whatNormalized.test(normalized))
                    {
                        var shortened = normalized.replace(whatNormalized, "");
                        if (shortened.length >= MIN_MENTION_LENGTH && normalizedCurrent.indexOf(shortened) === 0) {
                            whatOriginal = whatOriginal || whatNormalized;
                            mention = match.replace(whatOriginal, function (match) { rest = match; return ""; });
                            return true;
                        }
                    }
                    return false;
                }
                if (CHAT.IsAdditionalMention && CHAT.IsAdditionalMention(normalized)) {
                    mention = match.match(/^@\w+/)[0];
                    rest = match.substring(mention.length);
                }
                else if (normalized.length >= MIN_MENTION_LENGTH && normalizedCurrent.indexOf(normalized) == 0) { // mentions the current user
                    mention = match;
                    rest = "";
                } else if (normalizedCurrent.indexOf(normalized.substring(0, MIN_MENTION_LENGTH)) == 0) { // any chance at all?
                    tryOmitting(/\.$/) || tryOmitting(/'$/, /(&#39;|')$/) || tryOmitting(/'s$/i, /(&#39;|')s$/i) || tryOmitting(/'\.$/, /(&#39;|')\.$/) || tryOmitting(/'s\.$/i, /(&#39;|')s\.$/i);
                }

                if (!mention || (mention.replace(/&#(\d+);/g," ").length === 3 && normalizedCurrent.length > 2)) //note that match includes @
                    return match;
                else
                    return "<span class='mention'>" + mention + "</span>" + rest;
            });
        }
        text = handleQuoteMessage(text);
        // @username for explicit replies
        var reply_to = text.match(/^:(\d+)\s/);
        if (reply_to) {
            var msg = $("#message-" + reply_to[1]);
            var userid = msg.closest(".monologue").data("user");
            var user = CHAT.RoomUsers.getIfAvailable(userid);
            if (user) {
                text = "@" + htmlEncode(user.name.replace(/\s/g, "")) + " " + text.substring(reply_to[0].length);
            }
        }
        return text;
    }
    function gimmeMoreMine() {
        var first_id = $("#chat div.message").eq(0).messageId();
        var originalHtml = $("#getmore-mine").html();
        $("#getmore-mine").html("finding your last message&hellip;");
        $.post("/chats/" + ROOM_INFO.id + "/lastMessage", { beforeId: first_id, highlights: (highlights ? true : undefined) }, function (data) {
            if (data.msgid) {
                if (data.gap <= 300) {
                    gimmeMore(function () {
                        highlightElement($("#message-" + data.msgid));
                        $("#getmore-mine").html(originalHtml);
                    }, data.gap + 5, "#getmore-mine");
                } else {
                    notify("Your last message is too far back; please use the <a href=\"" + PERMALINK(data.msgid) + "\">transcript</a> instead");
                    $("#getmore-mine").html(originalHtml);
                }
            } else {
                $("#getmore-mine").fadeOut().slideUp();
            }
        });
    }
    function gimmeMore(callback, size, captionHost) {
        // when this function is called as an event handler, the first argument will
        // be an event object
        if ("which" in callback) {
            callback.preventDefault();
            callback = null;
        }
        size = size || MAX_MESSAGES_DELTA;
        MAX_MESSAGES += size;
        var fetch_count = MAX_MESSAGES - $("#chat div.message").length;
        var first_message = $("#chat div.message").eq(0);
        var getmore = $("#getmore");
        if (first_message.length == 0) { // the room is empty
            $("#getmore").fadeOut().slideUp();
            return;
        }
        var first_id = first_message.messageId();
        var jScrollable = $(mainScrollable(true));
        var offset = jScrollable.scrollTop() - first_message.offset().top + (CHAT.NEW_MOBILE && !FIXED_HEADER ? jScrollable.offset().top : 0);
        var url = "/chats/" + ROOM_INFO.id + "/events?before=" + first_id + "&mode=Messages";
        $(captionHost || "#getmore").html("loading&hellip;");
        url += "&msgCount=" + fetch_count;
        if (highlights) url += "&highlights=true";
        var requestStart = new Date().getTime();
        $.post(url, fkey(), function (data) {
            var clientStart = new Date().getTime();
            if (!data.events || data.events.length == 0)
                $("#getmore,#getmore-mine").fadeOut().slideUp();
            $("#chat .timestamp").eq(0).remove();
            rawMessages(data).prependTo("#chat");
            $.preload('.message img.user-image', { placeholder: placeHolderImage, notFound: notFoundImage });
            $("#getmore").html("load older messages");
            updateMonologues();
            ScrollTo(first_message, { offset: offset, noHeaderAdjustment: true }); // because the offset is measured, it's already the correct *actual* scroll offset, not the desired *visible* offset
            if (callback) callback();
            debugMessage("server: " + (data.ms ? (data.ms + "ms") : "n/a") + "; client: " + (new Date().getTime() - clientStart) + "ms; request (inc server): " + (clientStart - requestStart) + "ms");
        });
    }


    function getMessage(obj, edit, callback) {
        if (edit) {
            var source = obj.data("source");
            if (source) {
                callback(source);
                return;
            }
        }
        var cb = callback;
        var msgId = $(obj).messageId();
        var getMessageUrl = "/message/" + msgId;
        if (edit) {
            getMessageUrl += "?plain=true";
            cb = function (markdown) { obj.data("source", markdown); callback(markdown); }
        }
        $.get(getMessageUrl, cb);
    }

    function updateTitle() {
        var newtitle = document.title.replace(/^\(\d*\*?\) /, "");
        if (waitingMessages > 0 || replyQueue.len()) {
            newtitle = "(" + (waitingMessages > 0 ? waitingMessages : "") + (replyQueue.len() ? "*" : "") + ") " + newtitle;
        }
        // There is a bug in Chrome that causes the tab title not to be updated if it
        // changes at the same time the tab gains focus.
        // That's my interpretation of the behavior, anyway. And since 0.2 seconds
        // later it works, we'll just wait for a moment. Who cares, right?
        window.setTimeout(function () { $(document).attr("title", newtitle); }, TITLE_UPDATE_DELAY);
    }

    function trimInlineReply($content) {
        var $repliedTo = $content.find(".replied-to");
        return $content.text().replace($repliedTo.text(), '').trim();
    }

    var editTimeWarningTimeout;
    function editTimeWarning(msg) {
        if (CHAT.user.canEditAndDeleteOldPosts())
            return;

        clearEditWarning();

        var timeLeft = 120 - secondsSince(msg.info("time"));

        if (timeLeft <= 10) {
            inputError.secondsLeft(timeLeft);
            editTimeWarningTimeout = setTimeout(function () {
                inputError.secondsLeft(-1);
                editTimeWarningTimeout = null;
            }, timeLeft * 1000);
        } else {
            editTimeWarningTimeout = setTimeout(function () {
                inputError.secondsLeft(10);
                editTimeWarningTimeout = setTimeout(function () {
                    inputError.secondsLeft(-1);
                    editTimeWarningTimeout = null;
                }, 10000);
            }, (timeLeft - 10) * 1000);
        }
    }
    function clearEditWarning() {
        if (editTimeWarningTimeout)
            clearTimeout(editTimeWarningTimeout);
        editTimeWarningTimeout = null;
        inputError.secondsLeft(null);
    }

    // event handler for .click() on the message edit link
    function edit() {
        $("#chat div.message").removeClass("editing");
        editMessage($(this).closest(".message"));
    }
    function editMessage(msg, options) {
        msg.addClass("editing");
        var loader = $("<img/>").attr("alt", "please wait").attr("src", IMAGE("ajax-loader.gif")).css({ position: "absolute", margin: 3, padding: 5, backgroundColor: "white" }).hide().insertBefore("#input");

        // add reply box if msg is a reply to a parent msg
        if (msg.info("parent_id") && msg.info("show_parent")) {
            var parentId = msg.info("parent_id");
            var $parentMsg = $(`#message-${parentId}`);
            var parentText = trimInlineReply($parentMsg.find(".content"));
            createReplyBox(parentId, parentText, $parentMsg, null, true);
        }

        window.setTimeout(function() {loader.show();}, 200) // to prevent blinking if the message source is already available
        $("#cancel-editing-button").stop().fadeIn();
        if (!options || options.scroll)
            ScrollTo(msg, 500, { offset: -200, onAfter: checkIfOnBottom });
        getMessage(msg, true, function (markdown) {
            loader.remove();
            $('#input').addClass("editing");
            setInput(markdown).focus().caret(markdown.length, markdown.length);
            inputError.clear();
            checkInput();
            editTimeWarning(msg);
        });
    }

    // both used as an event handler for the "cancel editing" button .click() event
    // and as a regular function
    function cancelEditing() {
        CHAT.Hub.endEditing.fire();
    }
    CHAT.Hub.endEditing.add(function () {
        $("#chat div.editing").removeClass("editing");
        $('#input').removeClass("editing").prev("img").remove();
        $("#cancel-editing-button").fadeOut();
        setInput("");
        clearEditWarning();
        cancelReplying();
    });

    // returns the message that's currently marked as
    // being edited, if any
    function getEditing() {
        return $("#chat").find("div.editing").eq(0);
    }

    function clearerDiv() {
        // giving the div actual content (and hence a height of 0, so it doesn't take up any space)
        // happens solely for opera. The div being empty causes an awful lot of shaking (especially when
        // moving the mouse), because the monologue margins get randomly readjusted.
        // I assume (but am not sure) that this is related to http://css-class.com/test/bugs/opera/opera-margin-disappearance-bug.htm
        return div("clear-both").html("&nbsp;").css("height", 0);
    }

    // creates a new monologue for the given user, but doesn't insert
    // it into the DOM yet
    function newMonologue(user_id, defaultName) {
        var new_monologue = userContainer(user_id || 0);
        new_monologue.addClass("monologue");
        if (user_id == current_user_id) {
            new_monologue.addClass("mine");
        }
        var messages = $("<div/>").addClass("messages");
        if (user_id && user_id.toString().length > 0) {
            new_monologue.append(CHAT.RoomUsers.monologueSignature(user_id));
        } else if (defaultName && defaultName.length != 0) {
            new_monologue.append(CHAT.RoomUsers.monologueSignature(0)).find(".username").each(function () { $(this).text(defaultName); });
        }
        new_monologue.append(messages);
        new_monologue.append(clearerDiv());
        return new_monologue;
    }

    function updateMonologue(monologue, quick) {
        var message_count = monologue.find("div.message").length;
        if (message_count == 0) {
            var elapsed_before = monologue.prev(".system-message-container");
            var elapsed_after = monologue.next(".system-message-container");
            if (elapsed_after.length || elapsed_before.length) {
                var next_monologue = (elapsed_after.length ? elapsed_after : monologue).next(".monologue");
                if (next_monologue.length) {
                    next_monologue.find(".timestamp").remove();
                    next_monologue.addClass("needs-elapsed");
                    next_monologue.putInto(containers.timeTreatmentNeedy);
                }
                elapsed_after.remove();
                elapsed_before.remove();
            }
            monologue.remove();
            return;
        }
        var messages_height = monologue.find("div.messages").height();
        var username_height = monologue.find("a.signature > .username").potentialHeight(); // **direct** descendent only; this excludes the one in "tiny-signature"
        var speed = quick ? 0 : 400;
        if (mobile || messages_height - username_height < 37) {
            monologue.find(".avatar-32,.signature > .username,.flair").slideUp(speed);
            monologue.find(".tiny-signature").slideDown(speed);
        } else if (messages_height - username_height < 50) {
            monologue.find(".avatar-32,.signature > .username").slideDown(speed);
            monologue.find(".tiny-signature,.flair").slideUp(speed);
        } else {
            monologue.find(".avatar-32,.signature > .username,.flair").slideDown(speed);
            monologue.find(".tiny-signature").slideUp(speed);
        }
        monologue.trigger("monologue-updated");
    }

    function updateMonologues() {
        if (alwaysOnBottom) { // only enforce if we're on bottom; the user might be reading up
            // on what happenend; removing causes a scroll
            var too_much = $("#chat div.message").slice(0, -MAX_MESSAGES);
            var is_too_much = too_much.length > MESSAGE_REMOVAL_THRESHOLD;
            if (is_too_much) {
                $("#getmore").show();
                too_much.closest(".monologue").putInto(containers.needyMonologues);
                too_much.remove();
                $(".monologue").eq(0).putInto(containers.needyMonologues);
            }
        }
        containers.needyMonologues.withAll(function () { updateMonologue($(this)); });
        containers.needyMonologues.spill();
        if (is_too_much)
            putOrUpdateTimeStamp($("#chat > div.monologue").eq(0)); // the first monologue should get a new one

        //$(".monologue.new,.monologue:has(.message.new)").each(function () { updateMonologue($(this)); });
        putTimeStamps();
    }

    // Splits the given message's monolugue such that the message is the last one in it.
    // Does nothing if this is already the case.
    function splitMonolougeAtMessage(msg, defaultName) {
        var monologue = msg.closest(".monologue");
        var after = msg.nextAll(".message");
        if (after.length == 0)
            return
        var second = newMonologue(monologue.data("user"), defaultName);
        after.appendTo(second.find("div.messages"));
        second.insertAfter(monologue);
        second.trigger("monologue-updated");
    }

    // returns the monologue that the message with the given id by the given user
    // should be inserted into (or is already in). If there is no such monolgue
    // yet, creates one and inserts it at the right place.
    // if the message preceding the newly to-be-inserted one has a timestamp less
    // then mintime, this will create a new monologue, regardless of whether
    // the previous message came from the same user.
    function getMonologue(user_id, message_id, message_time, defaultName, noAutoCreate) {
        var msg = $("#message-" + message_id);
        if (msg.length > 0) {
            return msg.closest(".monologue");
        }
        if (noAutoCreate) return;
        var earlier = $("#chat div.message").not(".pending").filter(function () {
            return $(this).messageId() < message_id;
        });
        if (earlier.length > 0) {
            var earlier_monologue = earlier.last().closest(".monologue");
            var prev_time = earlier.last().info("time")
            var after_timebreak = earlier_monologue.next().hasClass("timebreak")
            var need_new = after_timebreak || prev_time < message_time - MAX_PAUSE;
            var need_elapsed = prev_time < message_time - MAX_ELAPSED;
            if (!need_new && earlier_monologue.data("user") == user_id) {
                return earlier_monologue;
            }
            splitMonolougeAtMessage(earlier.last(), defaultName);
            var nm = newMonologue(user_id, defaultName).insertAfter(after_timebreak ? earlier_monologue.next() : earlier_monologue);
            if (need_new)
                nm.addClass("needs-timestamp").putInto(containers.timeTreatmentNeedy);
            if (need_elapsed)
                nm.addClass("needs-elapsed").putInto(containers.timeTreatmentNeedy);
            return nm

        }
        else {
            return newMonologue(user_id, defaultName).prependTo($("#chat")).trigger("monologue-updated");
        }
    }


    function replyToMessage(msg) {
        if (!msg || !msg.messageId)
            msg = $(this).closest(".message");
        var id = msg.messageId();
        var text = trimInlineReply($(msg).find(".content"));
        createReplyBox(id, text, msg);
        $("#input").focus();
    }

    function createReplyBox(id, text = null, $msg = null, pUName = null, isEditing = false) {

        // remove old reply box first before creating
        var $oldReplyBox = $('#reply-box');
        if ($oldReplyBox.length > 0) {
            $oldReplyBox.remove();
        }

        if (!pUName && $msg) {
            pUName = findUsernameFromMsg($msg);
        }

        var sanitizedPName = pUName ? htmlEncode(pUName) : null;
        var replyingTextPre = isEditing ? 'This is a reply to' : 'You are replying to';
        var replyingText = pUName ? `${replyingTextPre} ${sanitizedPName}&#8217;s message` : `${replyingTextPre} a message`;

        var msgText = text || "View original message";
        var $replyToBox = $(`
            <div id="reply-box" class="bg-black-150 ps-fixed fc-light px8" data-parent-id=${id}>
                <div class="reply-box-content d-flex ai-center jc-space-between">
                    <span class="overflow-hidden truncate">
                        <svg width = "14" height = "14" viewBox = "0 0 14 14" fill = "none" xmlns = "http://www.w3.org/2000/svg" >
                            <path d="M5 4V1L0 6.5L5 12V8.425C8.75 8.425 11.625 9.625 13.5 12.25C12.75 8.5 10.25 4.75 5 4Z" fill="hsl(210, 8%, 42%)" />
                        </svg>
                        ${replyingText}:
                        <a class="s-link ml4" href=${PERMALINK(id)}>${htmlEncode(msgText)}</a>
                    </span>
                    <button class="cancel-reply s-btn">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 3.41L10.59 2L7 5.59L3.41 2L2 3.41L5.59 7L2 10.59L3.41 12L7 8.41L10.59 12L12 10.59L8.41 7L12 3.41Z" fill="hsl(210, 8%, 5%)"/>
                        </svg>
                    </button>
                </div>
            </div>`);

        if ($msg !== null) {
            $replyToBox.find("a").on("click", function () {
                return scrollToReply($msg);
            });
        }

        if (mobile) {
            $("#chat-body.mob #chat").addClass("with-reply-box");
        }

        $("#input-area").prepend($replyToBox);
        $(".cancel-reply").on("click", cancelReplying);
    }
    function cancelReplying() {
        $("#reply-box").remove();
        if (mobile) {
            $("#chat-body.mob #chat").removeClass("with-reply-box");
        }
    }
    function flashRelated() {
        $("#message-" + $(this).info("parent_id")).addClass("reply-parent");
        $(".message.pid-" + $(this).messageId()).addClass("reply-child");
    }
    function unflashRelated() {
        $("#message-" + $(this).info("parent_id")).removeClass("reply-parent");
        $(".message.pid-" + $(this).messageId()).removeClass("reply-child");
    }
    function scrollToReply($parent) {
        if ($parent.length == 1) {
            highlightElement($parent, null, { onAfter: checkIfOnBottom });
            return false;
        }
    }
    function highlightElement(el, duration, scrollOptions) {
        var target = $(el);
        target.addClass("highlight");
        window.setTimeout(function () { target.removeClass("highlight"); }, duration || 2000);
        ScrollTo(target, 200, scrollOptions);
    }

    function messageDetails(jMessage) {

        var isMine = jMessage.closest(".monologue").hasClass("mine");
        var isModeratorMessage = jMessage.closest(".monologue").find(".username").hasClass("moderator");
        var loggedIn = CHAT.user.isLoggedIn();
        var isRoomOwner = CHAT.user.isOwner();
        var isRoomMod = CHAT.user.canModerate();

        if (jMessage.find(".deleted").length) {
            return {
                isDeleted: true,
                canSeeHistory: isMine || isRoomOwner || isRoomMod
            };
        }

        var canEditAndDeleteOldPosts = CHAT.user.canEditAndDeleteOldPosts();
        var hasStarred = jMessage.find(".meta .stars").hasClass("user-star");
        var hasPinned = jMessage.find(".meta .stars").hasClass("user-owner-star");
        var hasFlagged = jMessage.find(".meta .flags").hasClass("user-flag");

        var canStar = loggedIn && !isMine;
        var canPin = isRoomOwner || isRoomMod;
        var canEdit = loggedIn && (isRoomMod || (isMine && canEditAndDeleteOldPosts) || (isMine && secondsSince(jMessage.info("time")) < 115));

        return {
            canReply: canTalk && !jMessage.closest(".user-container").hasClass("mine"),
            canStar: canStar && !hasStarred,
            canUnstar: canStar && hasStarred,
            canPin: canPin && !hasPinned,
            canUnpin: canPin && hasPinned,
            canFlag: loggedIn && !hasFlagged && (isRoomOwner || !isMine), // TODO: WTF? why should room owners be allowed to flag their own message?
            canEdit: canEdit,
            canDelete: canEdit || (isRoomOwner && !isModeratorMessage),
            canKick: loggedIn && CHAT.user.canKick(),
            canModflag: loggedIn,

            hasStarred: hasStarred,
            hasPinned: hasPinned,
            hasFlagged: hasFlagged
        };

    }

    function messageMenu(evt) {
        evt.stopPropagation();
        evt.preventDefault(); // the message menu is a link
        var message = $(this).closest(".message");
        var details = messageDetails(message);
        var menu = popUp(evt.pageX, evt.pageY, message);
        var verb;
        var verbs = [];
        var info = $("<div/>").appendTo(menu);
        var id = message.messageId();
        var when = $("<span/>").text("posted " + ToRelativeTimeMini(message.info("time"))).attr("title", localTimeSimple(message.info("time")));
        if (!details.isDeleted) {
            info.html(" &ndash; <a rel=\"noreferrer noopener\" href=\"" + PERMALINK(id) + "\">permalink</a><br/>");
            info.prepend(when);
            if (details.canReply) {
                $("<span/>").addClass("reply").html("<span class=\"newreply\"> </span> reply to this message").click(replyToMessage).click(menu.close).appendTo(menu);
                menu.append("<br/>");
            }
            menu.append("<br/>");
            if (details.hasStarred) {
                verb = "unstar";
                verbs.push("starred");
            } else
                verb = "star";
            if (details.canStar || details.canUnstar) {
                $("<span/>").addClass("star").html("<span class=\"img\"/> " + verb + " as interesting").click(star).click(menu.close).attr("title", "Add a star to indicate an interesting message, for example to display in the room's highlights").appendTo(menu);
                menu.append("<br/>");
            }

            if (details.canPin) {
                if (details.hasPinned) {
                    verb = "unpin";
                    verbs.push("pinned");
                } else
                    verb = "pin";
                $("<span/>").addClass("owner-star").html("<span class=\"img\"/> " + verb + " this message").click(ownerStar).click(menu.close).attr("title", "Pinning is like adding a star, but pinned items takes priority; this option is only available to the room owner.").appendTo(menu);
                menu.append("<br/>");
            }
            if (details.hasFlagged) {
                verbs.push("flagged");
            }

            if (verbs.length > 0)
                info.html(info.html() + "You have " + verbs.join(" and ") + " this message.<br/>");
            var edits = message.info("edits");
            var moved = message.info("moved");
            var pinned = message.find(".meta .stars").hasClass("owner-star");
            if (edits || moved || pinned) {
                var msg = "This message has been ";
                if (moved) msg += "moved from another room " + ((edits || pinned) ? "and " : "");
                if (edits) msg += "edited " + (edits == 1 ? "once" : edits == 2 ? "twice" : edits + " times") + (pinned ? " and " : "");
                if (pinned) msg += "pinned";
                info.append(msg + " - ");
                info.append($("<a/>").attr("href", "/messages/" + id + "/history").text("history"));
            }
            if (details.canFlag) {
                $("<span/>").addClass("flag").html("<span class=\"img\"/> flag as spam/offensive").click(flag).click(menu.close).attr("title", "Flagging a message helps bring inappropriate content to the attention of moderators and other users, for example spam or abusive messages.").appendTo(menu);
                menu.append("<br/>");
            }
            menu.append($("<br/>"));

            if (details.canEdit) {
                $("<span/>").addClass("edit").html("edit ").click(edit).click(menu.close).attr("title", "click to edit").appendTo(menu);
                menu.append(" | ");
            }
            if (details.canDelete) {
                delete_button = $("<span/>").addClass("delete").html("delete ").click(del).click(menu.close).attr("title", "click to delete");
                menu.append(delete_button);
                menu.append(" | ");
            }
            if (details.canModflag) {
                $("<span/>").addClass("flag").html("flag for moderator").click(flagMod).click(menu.close).attr("title", "Moderator flags are seen only by the site-moderators, and should be used to indicate serious issues with a message, and other administrative issues.").appendTo(menu);
            }
        } else {
            info.html("<br/>This message has been deleted");
            info.prepend(when);
            if (details.canSeeHistory)
            {
                info.append(" - ").append($("<a/>").attr("href", "/messages/" + id + "/history").text("history"));
            }

            if (CHAT.user.canUndelete())
            {
                undelete_button = $("<span/>").addClass("undelete").html("undelete ").click(undel).click(menu.close).attr("title", "click to undelete").css({ 'cursor': 'pointer' });
                info.append(" | ").append(undelete_button);
            }

        }
    }
    function flagMod(evt) {
        var elm = $(this).closest(".message");
        var msg = '<h2>Flag for moderator</h2><p>Please <b>do not use</b> this feature for anything other than informing moderators of <b>serious</b> issues that require their attention.</p><p>Please indicate why this requires moderator attention:</p>'
        promptUser(evt, msg, "", function (why) {
            if (why && why.length > 0) {
                messageAction(elm, "mod", { info: why }, function (result) {
                    if (result == "ok")
                        notify("Thanks, we'll take a look at it.");
                    else
                        notify(result || GENERIC_ERROR);
                });
            }
        }, true, null, function (txt) { return txt.length > 200 ? "Maximum length exceeded" : ""; });
    }

    function addLinks(text) {
        if (!text || text.length == 0 || text.substring(0, 4) == '<pre') return text;
        var root = $("<div/>").html(text);
        root.find("*").add(root).contents().filter(function () {
            return this.nodeType == 3 /*text node*/ && $(this).closest("a,code").length == 0
        }).each(function () {
            var text_content = $(this).text();
            if (text_content && text_content.search("/") != -1) // if there's no slash in it, then there's no link in it.
                // more importantly, this circumvents a jQuery caching bug
                // with texts like "constructor" or "hasOwnProperty"
                $(this).replaceWith(autoLink(text_content.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"))); // we're turning a text node into HTML
        });
        return root.html();
    }

    function toggleMessageAdmin() {
        $("#main").toggleClass("message-admin-mode");
        toggleSelectMode();
    }

    // toggles if setOn isn't given; otherwise setOn is a bool.
    // if setOn is true and callback is given, turns on single selectmode;
    // the callback will be called with the select message (as a jQuery object)
    // as the single argument or with null, meaning the selection is cancelled.
    function toggleSelectMode(setOn, callback) {
        var main = $("#main");
        var isOn = main.hasClass("select-mode");
        if (singleSelectCallback)
            singleSelectCallback(null);
        if (setOn && callback)
            singleSelectCallback = callback;
        else
            singleSelectCallback = null;
        if (setOn == undefined)
            setOn = !isOn;
        $("#chat div.message.selected").removeClass("selected");
        if (setOn || !isOn) {
            main.addClass("select-mode");
        }
        else if (!setOn || isOn) {
            main.removeClass("select-mode");
        }
    }

    function getSelectedMessages() {
        var id = '', count = 0;
        $("#chat div.message.selected").each(function (idx, el) { id += ',' + el.id.replace("message-", ""); count++; });
        if (id.length > 0) id = id.substr(1);
        return { id: id, count: count };
    }
    function deleteMultiple() {
        var data = getSelectedMessages();
        if (data.count > 0) {
            if (window.confirm("Delete the " + data.count + " selected posts?")) {
                $.post("/admin/deletePosts/" + ROOM_INFO.id, fkey({ id: data.id }));
                toggleMessageAdmin();
            }
        } else {
            window.alert("You have not selected any posts to delete.");
        }
    }
    function moveMultiple(evt) {
        $("#roomsearchresult").off("click", "button"); // stop leaking any previously attached live handlers

        evt.preventDefault();
        var data = getSelectedMessages();
        if (!data.count) {
            window.alert("You have not selected any posts to relocate.");
            return false;
        }
        var menu = popUp($(window).width() - 50, evt.pageY + 20).css("width", 750);
        $("<h2>Move posts</h2><p>This will move the selected posts to a different room. Enter the room name to <b>search</b> for the intended target room.</p>").appendTo(menu);
        var searchbox = $("<input type=\"text\" name=\"roomname\" />").appendTo(menu).focus();
        var loader = $("<img/>").attr("src", IMAGE("ajax-loader.gif")).appendTo(menu).hide();
        if (CHAT.user.canModerate()) {
            $("<p>You can also automatically <b>create a new room</b> as the target room by entering the <b>new room's name</b> and clicking \"create\".</p>").insertBefore(searchbox);
            var error = $("<div/>").css("color", "red").insertBefore(searchbox);
            $("<span>&nbsp;</span>").appendTo(menu);
            $("<button class='button'>create</button>").appendTo(menu).click(function () {
                var name = searchbox.val();
                if (name == "") {
                    error.text("Please enter a name for the new room.");
                    return;
                }
                if (!confirm("Do you want to create a new room named \"" + name + "\", copy all user permissions from here to the new room, and move the selected messages there?"))
                    return;
                loader.show();
                $.post("/admin/movePostsToNew/" + ROOM_INFO.id, fkey({ ids: data.id, newTitle: name }), function (data) {
                    loader.hide();
                    if (data != "ok") {
                        error.text(data);
                        return;
                    }
                    toggleMessageAdmin(false);
                    $("#roomsearchresult").off("click", "button");
                    menu.close();
                }, "json");
            });
        }
        $("<div id=\"roomsearchresult\" />").appendTo(menu);
        $("#roomsearchresult").on("click", "button", function () {
            var roomid = $(this).closest(".room-mini").find("a:first").attr("href").replace(/^\/rooms\/(\d+)\/.*$/, "$1");
            $.post("/admin/movePosts/" + ROOM_INFO.id, fkey({ ids: data.id, to: roomid }));
            toggleMessageAdmin(false);
            $("#roomsearchresult").off("click", "button");
            menu.close();
        });


        var doSearch = function () {
            loader.show();
            $("#roomsearchresult").load("/rooms/minilist", { filter: searchbox.val().toLowerCase(), forWriting: true }, function (response, status, xhr) {
                loader.hide();
                $("#roomsearchresult .room-mini").each(function () {
                    $("<button class='button'>choose</button>").prependTo($(".room-mini-header", this)).css({ "float": "right", "margin-top": 0 });
                });
            });
        };


        searchbox.keyup(function (evt) {
            if (evt.which == 13) doSearch(searchbox.val());
        }).typeWatch({ callback: doSearch, wait: 500, captureLength: 2 })

        return false;
    }

    function messagesBetween(a, b) {
        var id_a = a.messageId();
        var id_b = b.messageId();
        if (id_b < id_a) {
            var _ = id_a;
            id_a = id_b;
            id_b = _;
        }
        return $("#chat div.message").filter(function () {
            var id = $(this).messageId();
            return id_a <= id && id <= id_b;
        });
    }


    function singleSelect(callback) {
        singleSelectCallback = callback
    }

    var THIS_COMPUTER_HAS_A_HALF_EATEN_APPLE_ON_THE_BACK = /^mac/i.test(navigator.platform);
    var SELECT_MODIFIER = THIS_COMPUTER_HAS_A_HALF_EATEN_APPLE_ON_THE_BACK ? "metaKey" : "ctrlKey";
    if (THIS_COMPUTER_HAS_A_HALF_EATEN_APPLE_ON_THE_BACK) {
        $("#message-control-modifier-key").text("Command");
    }

    function selectMessage(evt) {
        if (!$("#main").hasClass("select-mode")) return;
        evt.preventDefault();
        evt.stopImmediatePropagation();
        var el = $(this).closest(".message");
        if (singleSelectCallback) {
            singleSelectCallback(el);
            return;
        }

        if (evt[SELECT_MODIFIER]) {
            el.toggleClass("selected");
        } else if (evt.shiftKey) {
            var sel = $("#chat div.message.selected");
            var first = sel.first();
            var last = sel.last();
            var to_first = messagesBetween(el, first);
            var to_last = messagesBetween(el, last);
            if (to_first.length >= to_last.length)
                to_last.addClass("selected");
            else
                to_first.addClass("selected");
        } else {
            $("#chat div.message.selected").removeClass("selected");
            el.addClass("selected");
        }
    }

    function createRepliedTo($message, parentId, parentUserName = null, parentText = null, displayInline = false) {
        var $replyInfoContainer = $(`
            <a href=${PERMALINK(parentId)} 
               class='s-link s-link__muted reply-info-container reply-info fc-black-400 mt4${displayInline ? ' inline mr4' : ' d-flex ai-center'}'
               title='This is a reply to an earlier message'>
            </a>`);

        if (displayInline) {
            $replyInfoContainer.prependTo($message.find(".message-info-container .content"));
        } else {
            $replyInfoContainer.prependTo($message);
        }

        var $replyIcon = $(`<svg class="reply-line-icon svg-icon iconReplySm${displayInline ? ' pb2' : ''}" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5 4V1L0 6.5L5 12V8.425C8.75 8.425 11.625 9.625 13.5 12.25C12.75 8.5 10.25 4.75 5 4Z" fill="hsl(210, 8%, 42%)"/>
</svg>
`);
        $replyIcon.appendTo($replyInfoContainer);

        var $parentMsgContainer = $(document).find(`#message-${parentId}`);

        if (!parentUserName) {
            parentUserName = findUsernameFromMsg($parentMsgContainer);
        }

        // fall back to the parent text rendered on the page
        var renderedParentText = trimInlineReply($parentMsgContainer.find('.content'));
        var displayedParentText;

        if (parentText)
            displayedParentText = parentText;
        else if (renderedParentText)
            displayedParentText = renderedParentText;
        else
            displayedParentText = "View original message";

        var repliedToText = parentUserName ? `@${htmlEncode(parentUserName)}` : 'replied to a message';
        var highlightUserName = parentUserName && CHAT.user.getName() && parentUserName.trim() === CHAT.user.getName().trim();

        var $repliedTo = displayInline ?
            $(`<span class="replied-to ml2 overflow-hidden${highlightUserName ? ' mention' : ''}">@${htmlEncode(parentUserName)}</span>`) :
            $(`<span class='replied-to ml2 overflow-hidden truncate w75'><span class="${highlightUserName ? 'mention' : ''}">${repliedToText}</span>:${htmlEncode(displayedParentText)}</span>`);

        $repliedTo.appendTo($replyInfoContainer);

        $replyInfoContainer.on('click', function () {
            var $parent = $("#message-" + $(this).closest(".message").info("parent_id"));
            return scrollToReply($parent);
        });
    }

    function newMessage(evt, showReplyInline) {
        var message = $("<div class=\"message\"/>").attr("id", "message-" + evt.message_id);
        var messageInfoContainer = $(`<div class="message-info-container break-word" />`);
        messageInfoContainer.appendTo(message);

        //if (canTalk) {/* change: always allow star/flag etc */
        var menu = $(`<a class="action-link h100" title="click for message actions"><span class="img menu"> </span></a>`).attr("href", PERMALINK(evt.message_id)).appendTo(messageInfoContainer);
        //}
        message.hover(flashRelated, unflashRelated);
        if (evt.moved) {
            $("<span/>").addClass("moved").attr("title", "This message was moved from another room; see edit history").html("&larr;").appendTo(messageInfoContainer);
        }

        var message_content = $("<div class=\"content\">" + addLinks(markdownExtensions(evt.content, evt.user_id != current_user_id)) + "</div>").appendTo(messageInfoContainer);
        var stars = $("<span class=\"stars vote-count-container" + (evt.message_stars > 0 || evt.message_owner_stars > 0 ? " always" : "") + (evt.message_owner_stars > 0 ? " owner-star" : "") + "\"><span class=\"img\"/><span class=\"times\">" + times(evt.message_stars) + "</span></span>");
        var flags = $("<span class=\"flags vote-count-container" + (evt.message_flags > 0 ? " always" : "") + "\"><span class=\"img\"/><span class=\"times\">" + times(evt.message_flags) + "</span></span>");

        if (evt.parent_id && evt.content) {
            if (evt.show_parent) {
                createRepliedTo(message, evt.parent_id, evt.parent_username, evt.parent_text, showReplyInline);
            }
        }

        // this is the overlay
        var message_meta = $("<span class=\"meta\"/>");
        message_meta.append(flags, "&nbsp;", stars);
        if (current_user_id > 0 && !(evt.user_id == current_user_id) && evt.content) {
            stars.find(".img").addClass("vote").attr("title", "star this message as useful / interesting for the transcript").click(star);
            if(evt.message_flagged)
                flags.find(".img").attr("title", "you have flagged this message as spam, inappropriate, or offensive");
            else
                flags.find(".img").addClass("vote").attr("title", "flag this message as spam, inappropriate, or offensive").click(flag);
            message_meta.append("&nbsp;");
            message_meta.append($("<span class=\"newreply\"/>").click(replyToMessage).attr("title", "link my next chat message as a reply to this"));
        }
        if (evt.message_starred) stars.addClass("user-star");
        if (evt.message_owner_starred) stars.addClass("user-owner-star");
        if (evt.message_flagged) flags.addClass("user-flag");

        // this is the bit in the middle
        var flash = $("<span class=\"flash\"/>");
        if ((gc && evt.message_flags > 0) || evt.message_flagged /* if they're not a mod, they should at least see their own flags */) {
            flash.addClass("flag-indicator");
        }
        flash.append(stars.clone(true));
        if (evt.message_edits > 0 && evt.content) menu.addClass("edits");

        messageInfoContainer.append(message_meta, flash);
        if (evt.parent_id) message.addClass("pid-" + evt.parent_id);
        message.data("info", {
            time: evt.time_stamp,
            edits: evt.message_edits,
            moved: evt.moved,
            parent_id: evt.parent_id,
            show_parent: evt.show_parent
        });


        var prePartial = message_content.find('.partial');
        if (prePartial.length == 1 && !prePartial.parent().hasClass("quote")) {
            var showall = $("<a/>").addClass("more-data").text("(see full text)").attr("href", "/messages/" + ROOM_INFO.id + "/" + evt.message_id);
            showall.click(function (evt) {
                if (evt.button != 0 || evt.ctrlKey)
                    return; // only AJAX the text in on left-click
                var loading = $("<span/>").html("loading&hellip;");

                // we hide $(this) here and only remove it in the AJAX callback, because when the click event bubbles up
                // to the document, a selector test in a .live() handler throws an exception if the element has no parent anymore
                loading.insertAfter($(this).hide());

                $.ajax({
                    type: "GET",
                    url: $(this).attr("href"),
                    success: function (data) {
                        if (prePartial.get(0).tagName.toLowerCase() != "pre") { // handle newlines
                            var html = "<div class='full'>" + data.toString().replace(/\r\n?|\n/g, " <br> ") + "</div>";
                            prePartial.replaceWith(addLinks(markdownExtensions(html)));
                        } else {
                            prePartial.removeClass("partial").addClass("full").html(data.replace(/^    /mg, ""));
                        }
                        loading.add(showall).remove();

                    }
                });
                evt.preventDefault();
            });
            message_content.append(" ", showall);
        }
        if (onNewMessage)
            onNewMessage(message, evt.message_id);
        return message;
    }

    // inserts or updates the message represented by the event; creating a new monologue if
    // necessary
    function updateMessage(evt) {

        var monologue = getMonologue(evt.user_id, evt.message_id, evt.time_stamp, evt.user_name, !(evt.event_type == EventType.MessagePosted || evt.event_type == EventType.MessageMovedIn || evt.event_type == EventType.UserMentioned || evt.event_type == EventType.MessageReply));
        if (!monologue) return;
        monologue.putInto(containers.needyMonologues).putInto(containers.timeTreatmentNeedy);
        if (!shouldShowUser(evt.user_id)) showHideMonologue(monologue, true);
        var showReplyInline = false;
        if (evt.parent_id) {
            var msgId = evt.event_type == EventType.MessageEdited ? evt.message_id : null;
            showReplyInline = isParentWithinDistance(evt.parent_id, msgId) || hasSameParentReplyInMonologue(monologue, evt.parent_id);
        }
        var message = newMessage(evt, showReplyInline).addClass("neworedit");
        var original = monologue.find("#message-" + evt.message_id);
        if (original.length > 0) {
            // this message is already there
            $.each(["time", "starred", "flagged"], function (i, key) {
                message.info(key, original.info(key)); // keep the data of the original, not the edit
            });
            $.each(["editing"], function (i, cls) { // if we were editing the original, we're now editing the new one
                if (original.hasClass(cls))
                    message.addClass(cls)
            });
            message.data("source", original.data("source"));
            if (original.find(".flash").length) { // it's not present in the original pending message
                message.find(".meta").replaceWith(original.find(".meta"));
                message.find(".flash").replaceWith(original.find(".flash"));
            }
            original.replaceWith(message);
        } else {
            var earlier = monologue.find("div.message").filter(function () {
                return $(this).messageId() < evt.message_id;
            });
            if (earlier.length == 0) {
                message.prependTo(monologue.find("div.messages"));
            } else {
                message.insertAfter(earlier.last());
            }
        }
        if (evt.content && evt.content.match(/^[-=]{3,}$/)) {
            var prev = message.prev(".message");
            if (prev.length > 0)
                splitMonolougeAtMessage(prev, evt.user_name);
        }
        $.preload('#message-' + evt.message_id + ' img.user-image', {
            placeholder: placeHolderImage,
            notFound: notFoundImage
        });
    }

    // if the given monologe doesn't have a timestamp yet, adds one. If it has
    // one, update it. element should be a jQuery object with zero or one element,
    // and can be either a monologue or a timestamp. If time is not given,
    // picks it up from the first message of the monologue.
    function putOrUpdateTimeStamp(element, time) {
        if (element.length == 0)
            return;
        var monologue = element;
        if (element.hasClass(".timestamp"))
            monologue = element.closest(".monologue");
        if (!time)
            time = monologue.find("div.message:first").info("time");
        var ts = monologue.find(".timestamp");
        if (ts.length == 0) {
            if (mobile) {
                ts = $("<a href='#'/>");
            } else {
                ts = $("<div/>");
            }
            ts.addClass("timestamp").prependTo(monologue.find("div.messages"));
        }
        ts.text(localTimeSimple(time))
    }

    function systemMessage(text, cls) {
        var msg = div("system-message-container").append(div("system-message-spacer").html("&nbsp;"), div("system-message").html(text)).append(clearerDiv());
        if (cls)
            msg.addClass(cls);
        return msg;
    }

    function putTimeStamps() {
        containers.timeTreatmentNeedy.withAll(function () {
            if ($(this).find(".timestamp").length > 0)
                return;
            var thistime = $(this).find("div.message:first").info("time");
            if (!thistime)
                return;
            // TODO: I'm sure there's room for optimizing all this selector play
            var prev = $(this).prevAll(".monologue:has(.timestamp)").eq(0);
            var tsdiff = thistime - (prev.find("div.message:first").info("time") || 0);     // how long ago was the previous timestamp?
            var stampless = $(this).prevUntil(".monologue:has(.timestamp)");
            if (tsdiff > TIMESTAMP_DIST || stampless.length >= TIMESTAMP_MIN_FREQ || $(this).hasClass("needs-timestamp")) {
                putOrUpdateTimeStamp($(this), thistime);
                $(this).removeClass("needs-timestamp");
            }
            if ($(this).hasClass("needs-elapsed")) {
                var elapsed_time = thistime - $(this).prevAll(".monologue").eq(0).find("div.message:last").info("time");
                if (elapsed_time)
                    systemMessage(timeSpanString(elapsed_time, true) + " later&hellip;").insertBefore($(this));
                $(this).removeClass("needs-elapsed");
            }
        });
        containers.timeTreatmentNeedy.spill();
    }
    function showHideSilenceNote(only_hide, quick) {
        var silence_time = secondsSince($("div.monologue:last div.message:last").info("time"));
        var silence_note = $("#silence-note:not(.removing)");
        var speed = quick ? 0 : 600;
        if (!only_hide && silence_time > 3600) { // note that NaN > 3600 is false; hence this does the right thing when the room is empty
            var text = "The last message was posted " + timeSpanString(silence_time, true) + " ago.";
            if (!silence_note.length)
                silence_note = systemMessage(text).attr("id", "silence-note").hide().appendTo("#chat").slideDown(speed);
            else
                silence_note.find(".system-message").html(text);
        } else {
            silence_note.addClass("removing").slideUp(speed, function () { $(this).remove() });
        }
    }


    function getImage(category, title) {
        return "<span title='" + (title || category) + "' class='sprite sprite-bullet-" + category + "'/>";
    }
    function times(n) {
        return n > 1 ? n : "";
    }

    function updateStars(evt, refreshRhs) {
        if (evt != null) {
            var msg = $("#message-" + evt.message_id);
            var star = msg.find(".stars");
            star.find(".times").html(times(evt.message_stars));
            if (evt.user_id === current_user_id) {
                star.toggleClass("user-star", !!evt.message_starred);
                star.toggleClass("user-owner-star", !!evt.message_owner_starred);
            }
            star.toggleClass("owner-star", evt.message_owner_stars > 0);

            if (!evt.message_stars)
                star.removeClass("user-star");
            if (!evt.message_owner_stars)
                star.removeClass("user-owner-star");

            star.toggleClass("always", evt.message_stars > 0 || evt.message_owner_stars > 0);
        }
        if (refreshRhs) {
            sidebar.updateStars();
        }
    }
    function updateFlags(evt, refreshRhs) {
        if (evt != null) {
            var msg = $("#message-" + evt.message_id);
            var flag = msg.find(".flags");
            flag.find(".times").html(times(evt.message_flags));
            var myFlag = evt.user_id == current_user_id;
            if (evt.message_flags > 0) {
                flag.addClass("always");
                if (gc || myFlag) msg.find(".flash").addClass("flag-indicator");
                if (myFlag)
                    flag.addClass("user-flag").find(".img").unbind("click").removeClass("vote").attr("title", "you have flagged this message as spam, inappropriate, or offensive");
            } else {
                flag.removeClass("always");
                flag.removeClass("user-flag");
                if (gc) msg.find(".flash").removeClass("flag-indicator");
            }
        }
        if (refreshRhs) {
            sidebar.updateAdminCounters(lasttime);
        }
    }

    function updateEdits(evt) {
        if (evt != null) {
            var msg = $("#message-" + evt.message_id);
            msg.info("edits", evt.message_edits);
            var edit = msg.find(".meta .edits");

            if (evt.message_edits > 0) {
                edit.show();
            }
        }
    }

    function handleEventInOtherRoom(evt, suppressGlobals, isIcc) {
        if (!evt.room_id || evt.room_id === ROOM_INFO.id)
            return false;
        switch (evt.event_type) {
            case EventType.ModeratorFlag:
            case EventType.Invitation:
            case EventType.GlobalNotification:
                return false;
            case EventType.UserLeft:
                if (evt.user_id == current_user_id)
                    sidebar.leaveOtherRoom(evt.room_id);
                break;
            case EventType.MessagePosted:
            case EventType.MessageEdited:
                throttler.report_speaking();
                if (shouldShowUser(evt.user_id)) {
                    sidebar.otherRoomActivity(evt.room_id, evt.room_name, evt.user_name, evt.content, evt.time_stamp, evt.message_id);
                    if (evt.user_id != current_user_id)
                        sound.queue(3, evt.room_id);
                    else
                        sidebar.dismissOtherRoomMention(evt.room_id);
                }
                break;
            case EventType.MessageUndeleted:
            case EventType.MessageDeleted:
                sidebar.otherRoomActivity(evt.room_id, evt.room_name, evt.user_name, null, evt.time_stamp, evt.message_id);
                break;
            case EventType.UserMentioned:
            case EventType.MessageReply:
                if (evt.target_user_id == current_user_id) {
                    sound.queue(1, evt.room_id);
                    if (sidebar.otherRoomMention(evt.room_id, evt.message_id)) { // this returns true if I'm really in that room
                        if (!isIcc)
                            queueDesktopReply(evt);
                    } else {
                        var msg = "You have been <a href=\"" + PERMALINK(evt.message_id) + "\">mentioned</a> in " + $("<span />").text(evt.room_name).html() + ", a room you're not currently in.";
                        notify(msg);
                    }
                }
                break;
            case EventType.MessageFlagged:
                sidebar.updateAdminCounters(lasttime);
                break;
            case EventType.UserEntered:
                if (evt.user_id == current_user_id)
                    sidebar.otherRoomActivity(evt.room_id, evt.room_name, evt.user_name, evt.content, evt.time_stamp);
                break;
            case EventType.UserNameOrAvatarChanged:
                if (evt.target_user_id === current_user_id) {
                    is_heading_out = true;
                    window.location.reload();
                } else {
                    CHAT.RoomUsers.forceUpdateIfNecessary(evt.target_user_id);
                }
                break;
        }
        return true;
    }
    CHAT.addEventHandlerHook(handleEventInOtherRoom);

    var desktopReplyQueue = {};
    function queueDesktopReply(evt) {
        desktopReplyQueue[evt.message_id] = evt;
    }
    function showQueuedDesktopReplies() {
        for (var msgid in desktopReplyQueue)
            showReplyOnDesktop(desktopReplyQueue[msgid]);
        desktopReplyQueue = {};
    }
    function showReplyOnDesktop(evt) {
        if (notifier.desktop && OPTIONS.desktopNotify && evt.content && evt.content.length) {
            var title = "New event";
            switch(evt.event_type) {
                case EventType.UserMentioned: title = "You were mentioned by " + evt.user_name; break;
                case EventType.MessageReply: title = "You received a reply from " + evt.user_name; break;
            }
            var text = $('<span/>').html(evt.content).text();
            if (evt.room_name && evt.room_name.length > 0) {
                text = text + ' (' + evt.room_name + ')'
            }
            var icon = null;
            var user;
            if (evt.user_id)
                user = CHAT.RoomUsers.getIfAvailable(evt.user_id);
            if (user) {
                icon = gravatarUrl(evt.user_id, user.email_hash, 48);
            }
            var opts = { title: title, text: text, icon: icon, timeout: 15000 };
            if (evt.room_id)
                CROSS_TAB.desktopNotification(evt.room_id, opts)
            else
                notifier.desktop(opts);
        }
    }

    // event dispatcher called by pollDone() on all events that where received from the server
    function handleChatEvent(evt, suppressGlobals, isIcc) {
        var eventHandlerHooks = CHAT.getEventHandlerHooks();
        if (is_heading_out)
            return;
        throttler.report_happening();
        for (var i = 0; i < eventHandlerHooks.length; ++i) {
            if (eventHandlerHooks[i](evt, suppressGlobals, isIcc))
                return;
        }
        switch (evt.event_type) {
            case EventType.MessagePosted:
                if (evt.user_id > 0)
                    CHAT.RoomUsers.get(evt.user_id).done(function (u) { u.talk(evt.time_stamp); });
            // intentional fall-through
            case EventType.MessageMovedIn:
            case EventType.MessageEdited:
            case EventType.MessageDeleted:
            case EventType.MessageUndeleted:
                updateMessage(evt);
                if (evt.user_id > 0)
                    CHAT.RoomUsers.get(evt.user_id).done(function (u) { u.activity(evt.time_stamp); });
                if (!first_run) {
                    if (evt.message_flags > 0) updateFlags(null, true);
                    if (evt.message_stars > 0) updateStars(null, true);
                }
                throttler.report_speaking();
                break;
            case EventType.MessageMovedOut:
                var msg = $("#message-" + evt.message_id);
                var mon = msg.closest(".monologue");
                msg.remove();
                mon.find(".timestamp").remove();
                mon.putInto(containers.needyMonologues).putInto(containers.timeTreatmentNeedy);
                break;
            case EventType.DebugMessage:
                if(!suppressGlobals) debugMessage(evt.content);
                break;
            case EventType.UserEntered:
                CHAT.RoomUsers.get(evt.user_id).done(function (u) { u.enter(); });
                if (evt.user_id == current_user_id) {
                    sidebar.isLeaving = undefined;
                }
                break;
            case EventType.UserLeft:
                if (evt.user_id == current_user_id && !suppressGlobals) {
                    sidebar.isLeaving = true;
                    is_heading_out = true;
                    if ($("#leave").length == 0) // anonymous users can't leave (edit: can they be targetted by user_id though?)
                        window.location.href = "/";
                    else
                        $("#leave").click();
                } else {
                    CHAT.RoomUsers.get(evt.user_id).done(function (u) { u.leave(); });
                }
                break;
            case EventType.MessageStarred:
                updateStars(evt, true);
                break;
            case EventType.AccessLevelChanged:
                if (evt.target_user_id > 0)
                    CHAT.RoomUsers.trigger("access", evt.target_user_id, evt.content);
            // intentional fall-through
            case EventType.UserSuspended:
            case EventType.UserMerged:
                if (evt.target_user_id == current_user_id || evt.target_user_id == null
                    || (evt.event_type == EventType.UserMerged && new RegExp("\\b" + current_user_id + "\\b").test(evt.content))
                )
                {
                    is_heading_out = true;
                    window.location.reload(); // *our* or the *room's* access has changed
                }
                break;
            case EventType.ModeratorFlag:
                updateFlags(null, true);
                break;
            case EventType.MessageFlagged:
                updateFlags(evt, true);
                break;
            case EventType.RoomNameChanged:
                sidebar.updateRoomMeta();
                break;
            case EventType.MessageReply:
            case EventType.UserMentioned:
                if (evt.target_user_id == current_user_id && shouldShowUser(evt.user_id)) {
                    if (highlights) updateMessage(evt); // message might not be on-screen in this case
                    replyQueue.add(evt.message_id);
                    sound.queue(1, evt.room_id);
                    if (!isIcc) { queueDesktopReply(evt); }
                }
                break;
            case EventType.FileAdded:
                sidebar.updateFiles();
                break;
            case EventType.UserSettingsChanged:
                $.getJSON("/users/ignorelist", function (data) { initHiddenUsers(data); });
                break;
            case EventType.GlobalNotification:
            case EventType.UserNotification:
                if (suppressGlobals) break;
                if (evt.content && evt.content.length > 0) {
                    sound.queue(1); // treat like @mention
                    notify(evt.content);
                }
                break;
            case EventType.Invitation:
                sound.queue(1);
                notify(evt.content);
                break;
            case EventType.TimeBreak:
                debugMessage("time break");
                break;
            case EventType.FeedTicker:
                if (!evt.user_id || shouldShowUser(evt.user_id)) feedTicker.add(evt.content);
                break;
            case EventType.UserNameOrAvatarChanged:
                if (evt.target_user_id === current_user_id) {
                    is_heading_out = true;
                    window.location.reload();
                } else {
                    CHAT.RoomUsers.forceUpdateIfNecessary(evt.target_user_id);
                }
                break;
            default:
                debugMessage("Unknown event type " + evt.event_type + "; content: " + (evt.content ? evt.content : "").substring(0, 100) + "...");
                break;
        }
    }


    var jackBauerEndTimeout,  // a setTimeout id
        jackBauerEndTime,
        jackBauerAnimationInterval;

    function jackBauerEnd()
    {
        if (!jackBauerEndTimeout) // never started (this function is called on every pollDone, so *usually* we end here)
            return;

        jackBauerEndTimeout = null;
        jackBauerEndTime = null;
        clearInterval(jackBauerAnimationInterval);
        jackBauerAnimationInterval = null;
        $("#input-table").fadeIn();
        if ($("#timeout-reason").length > 0) $("#timeout-reason").remove();
    }
    function jackBauer(seconds, reason) {
        jackBauerEndTime = now() + seconds;

        if ($("#timeout-reason").length === 0 && reason) {
            $("<div id='timeout-reason' />").append("This room was placed in timeout for the following reason: " + reason).appendTo("#input-area");
        }

        if (jackBauerEndTimeout) {
            clearTimeout(jackBauerEndTimeout);
            jackBauerEndTimeout = setTimeout(jackBauerEnd, seconds * 1000);
            return;
        }

        jackBauerEndTimeout = setTimeout(jackBauerEnd, seconds * 1000);

        $("#input-table").fadeOut();

        jackBauerAnimationInterval = setInterval(function () {
            var secondsLeft = Math.round(-secondsSince(jackBauerEndTime));
            if (!secondsLeft)
                return;

            var jackB = $("<span/>").text(secondsLeft).attr("title", "Timeout");
            jackB.css({
                position: "fixed",
                zIndex: 10,
                height: 10,
                bottom: 0,
                left: 0,
                opacity: 1,
                fontSize: 1
            }).appendTo("#input-area")
                .animate({
                    fontSize: 200,
                    opacity: 0,
                    height: 220
                }, 5000, function () { jackB.remove(); });

        }, 3000);
    }

    // AJAX callback
    function pollDone(data, requestStart) {
        if (!data) return;
        if (data.reset) // data too old to catch up; reset the client
        {
            is_heading_out = true;
            window.location.reload();
            return;
        } else if (data.exit) {
            window.location.href = "/";
            return;
        } else if (data.since && lasttime && data.since < lasttime) {
            // not our data... just miss a beat
            return;
        }
        var clientStart = new Date().getTime();
        if (data.timeout) {
            if (!CHAT.user.canTalkDuringTimeout()) {
                jackBauer(data.timeout, data.reason);
            }
        } else {
            jackBauerEnd();
        }
        var lastKnown = lasttime;
        if (data.time) {
            if (!lasttime || data.time > lasttime) { lasttime = data.time;}
            else if (lasttime && data.time < lasttime) { debugMessage("replay: " + lasttime + " (client) vs " + data.time + " (server)"); }
        }
        if (data.sync) {
            SERVER_TIME_OFFSET = data.sync - (new Date()).getTime() / 1000;
        }
        if (first_run) {
            doFirstRun(data);
            debugMessage("server: " + (data.ms ? (data.ms + "ms") : "n/a") + "; client: " + (new Date().getTime() - clientStart) + "ms; request (inc server): " + (clientStart - requestStart) + "ms");
            return;
        }

        var newcount = 0; // the number of actual new messages posted into this room by other users

        if (data.events) {
            $.each(data.events, function (i, evt) {
                if (evt.id > lastKnown) {
                    if (evt.event_type == EventType.MessagePosted && evt.user_id != current_user_id
                        && evt.room_id == ROOM_INFO.id && shouldShowUser(evt.user_id))
                        newcount++;
                }
            });
            if (newcount > 0) {
                sound.queue(2, ROOM_INFO.id);
            }
            if (newcount > 0 && !windowHasFocus) {
                waitingMessages += newcount;
                updateTitle();
            }
            $.each(data.events, function (i, evt) {
                if (evt.id > lastKnown) { handleChatEvent(evt, false, data.icc); }
            });
        }
        updateMonologues();

        if (data.events && data.events.length > 0) {// did anything happen at all?
            sidebar.relayout();
            setTimeout(function () {
                $("#chat .neworedit").removeClass("neworedit");
            }, 2000);
        }
        showHideSilenceNote();
    }

    // this does (read: should do) essentially the same as pollDone() (plus some one-time things),
    // but is optimized for some specialties that occur in the first run, i.e. when populating the page:
    // * all events are messages
    // * they are in correct order
    // * there's quite a few
    function doFirstRun(data) {
        var chat = $("#chat");
        if (data.events && data.events.length)
            throttler.report_speaking(data.events[data.events.length - 1].time_stamp);

        throttler.report_speaking(sidebar.lastOtherRoomMessageTime(), /*onlyIfLater =*/ true)

        rawMessages(data).appendTo(chat);
        $.preload('.message img.user-image', { placeholder: null, notFound: notFoundImage });
        showHideSilenceNote(false, true);
        var checks_left = 10; // if not all images are loaded after 10 tries (i.e. about 2 seconds), show the chat anyway.
        var images = chat.find("img.user-image").filter(function () { return !!$(this).attr("src") });
        var check = function () {
            if (checks_left == 0 || images.filter(function () { return !this.complete; }).length == 0) {
                chat.show();
                containers.needyMonologues.withAllButTakeYourTime(function () { updateMonologue($(this), true); });
                containers.needyMonologues.spill();
                CHAT.RoomUsers.initializeLate(canTalk);
                ScrollTo("#bottom", 0);
                alwaysOnBottom = true;
                scrollSpeed = 500;

                if (mobile) {
                    $("#loading").remove();
                    $("#topbuttons").show();
                    CHAT.Hub.roomReady.fire();
                }
                else {
                    $("#input").focus();
                    $("#loading").fadeOut(function () { $(this).remove(); CHAT.Hub.roomReady.fire(); });
                }
            } else {
                checks_left--;
                window.setTimeout(check, 200);
            }
        };
        check();
        first_run = false;
        sidebar.relayout();
    }
    // takes the server data, which should only consist of message posting and time break events in chronological order
    // and returns a jQuery object contaings the correspondingly created monologues, ready to be inserted into the dom
    function rawMessages(data) {
        var result = $([]);
        var last_user, last_message_time, current_monologue, current_messages, last_timestamp_time;
        var monologue_count_since_timestamp = 0;
        var timebreak = false;
        if (!data.events) return;
        $.each(data.events, function (i, evt) {
            if (evt.event_type == EventType.TimeBreak) {
                timebreak = true;
            } else {
                var uid = evt.user_id;
                var new_user = !uid || uid != last_user;
                var elapsed_time = evt.time_stamp - last_message_time;
                var needs_timestamp = timebreak || elapsed_time > MAX_PAUSE ||
                    (new_user && monologue_count_since_timestamp % (TIMESTAMP_MIN_FREQ + 1) == 0);
                if (timebreak)
                    result = result.add(systemMessage("", "timebreak"));
                if (new_user || needs_timestamp || (evt.content && evt.content.match(/^[-=]{3,}$/))) {
                    needs_timestamp = needs_timestamp || evt.time_stamp - last_timestamp_time > TIMESTAMP_DIST; // only applies if a new monoglue would have been created anyway

                    if (elapsed_time > MAX_ELAPSED) {
                        result = result.add(systemMessage(timeSpanString(elapsed_time, true) + " later&hellip;"));
                    }
                    current_monologue = newMonologue(uid, evt.user_name);
                    result = result.add(current_monologue);
                    if (uid && !shouldShowUser(uid)) { showHideMonologue(current_monologue, true); current_monologue.hide(); }
                    if (needs_timestamp) {
                        putOrUpdateTimeStamp(current_monologue, evt.time_stamp);
                        last_timestamp_time = evt.time_stamp;
                        monologue_count_since_timestamp = 0;
                    }
                    current_messages = current_monologue.find("div.messages");
                    last_user = evt.user_id;
                    monologue_count_since_timestamp++;
                    current_monologue.putInto(containers.needyMonologues);
                }

                var showReplyInline = false;
                if (evt.parent_id && evt.show_parent) {
                    showReplyInline = isParentWithinDistance(evt.parent_id, null, i, data.events) || hasSameParentReplyInMonologue(current_monologue, evt.parent_id);
                }
                newMessage(evt, showReplyInline).appendTo(current_messages);
                last_message_time = evt.time_stamp;
                timebreak = false;
            }
        });
        return result;
    }

    // check if there are at least 10 messages between parent and reply
    function isParentWithinDistance(parentId, msgId = null, msgIndex = null, events = null) {
        var parentIndex = -1;
        // if no array of events or index is passed, look for parent message on the page
        if (events === null && msgIndex === null) {
            var $parentMsg = $(`#message-${parentId}`);
            parentIndex = $(".message").index($parentMsg);
            msgIndex = msgId ? $(".message").index($(`#message-${msgId}`)) : $(".message").length - 1;
        } else {
            parentIndex = events.findIndex((e) => e.message_id === parentId);
        }

        if (parentIndex === -1) {
            return false;
        }

        return msgIndex - parentIndex <= 11;
    }

    // if there is already a reply that is not inline to the same parent in one monologue
    // we don't need to display the parent message text again in that monologue
    function hasSameParentReplyInMonologue($monologue, parentId) {
        var $sameParentReplies = $monologue.find(`.pid-${parentId}:not(.editing):not(.posted) .reply-info-container:not(.inline)`);
        return $sameParentReplies.length > 0;
    }

    function loadInitialMessages() {
        var url = "/chats/" + ROOM_INFO.id + "/events";
        body = fkey({ since: lasttime, mode: "Messages", msgCount: MAX_MESSAGES });
        if (highlights)
            body["highlights"] = true;
        var requestStart = new Date().getTime();
        return $.post(url, body, function (data) { pollDone(data, requestStart); }).promise();
    }
    function postCallbackFactory(message) {
        return function (data) {
            if (!data.id || $("#message-" + data.id).length > 0) { // either the server didn't return an id at all, or that message exists already
                // because we've received the post event *before* recieving the new ID
                var monologue = message.closest(".monologue");
                message.remove();
                updateMonologue(monologue);
                return;
            }
            message.addClass("posted").removeClass("pending").attr("id", "message-" + data.id);
            message.info("time", data.time);
            cancelReplying();
        }
    }

    function postFailureFactory(message, oncancel) {
        var src = message.data("source");
        return function (xhr, failType) {
            var errorMessage = failType;
            if (failType == "error") {
                errorMessage = xhr.getResponseHeader("chat") == "error" ? xhr.responseText : "An unknown error has occurred";
            }
            var failmessage = $("<i></i>").text(" - " + errorMessage + " - ");
            var retrylink = $("<a href='#'>retry</a>").click(function (evt) {
                failmessage.remove();
                postMessage(message);
                evt.preventDefault();
            });
            var editlink = $("<a href='#'>edit</a>").click(function (evt) {
                failmessage.remove();
                setInput(src);
                oncancel();
                evt.preventDefault();
            });
            var cancellink = $("<a href='#'>cancel</a>").click(function (evt) {
                failmessage.remove();
                oncancel();
                evt.preventDefault();
            });
            message.find("img.progressbar").remove();
            failmessage.append(retrylink, "<span> / </span>", editlink, "<span> / </span>", cancellink).appendTo(message);
        }
    }

    function handleEditResponse(data) {
        if (data == "ok") {
            return true;
        }
        notify(data);
        return false;
    }

    function postMessage(message) {
        var url, callback, revert;
        if (message.attr("id").search(/^pending-message-/) != -1) {
            url = "/chats/" + ROOM_INFO.id + "/messages/new";
            callback = postCallbackFactory(message);
            revert = function () {
                // remove the not-posted message
                var monologue = message.closest(".monologue");
                message.remove();
                updateMonologue(monologue);
            }
        } else {
            url = "/messages/" + message.messageId();

            revert = function () {
                // revert to pre-edit state
                message.data("source", null).removeClass("pending");
                message.find(".content").html(message.data("previous_content"));
                message.data("previous_content", null);
            };

            callback = function (data) {
                message.removeClass("pending");
                if (!handleEditResponse(data)) {
                    revert();
                } else {
                    message.addClass("posted");
                }
                cancelReplying();
            }
        }
        if (!mobile)
            $("#input").focus();
        $.ajax({
            type: "POST",
            url: url,
            data: fkey({ text: message.data("source"), parentId: message.info("parent_id") }),
            success: callback,
            dataType: "json",
            error: postFailureFactory(message, revert)
        });
    }

    var inputError = (function () {
        var tooLong = false,
            tooOld = false,
            almostTooOld = null; // an integer, showing a number of seconds

        var showHide = function () {
            var text = "";
            if (tooOld)
                text = "This message is now too old to be edited. "
            else if (typeof almostTooOld == "number")
                text = "You have less than " + almostTooOld + " seconds left for editing. "
            if (tooLong)
                text += "This message is too long."
            if (text.length)
                $("#inputerror").text(text).fadeIn();
            else
                $("#inputerror").fadeOut();
        };

        return {
            clear: function () {
                tooLong = false;
                tooOld = false;
                almostTooOld = null;
                showHide();
            },
            tooLong: function (yesno) {
                tooLong = yesno;
                showHide();
            },
            secondsLeft: function (sec) {
                if (sec === null) {
                    tooOld = false;
                    almostTooOld = null;
                    showHide();
                    return;
                }

                sec = sec | 0;
                if (sec > 0) {
                    almostTooOld = sec;
                    tooOld = false;
                } else {
                    tooOld = true;
                }
                showHide();
            }
        };
    })();

    var everTyped = false;

    function checkInput() {
        var text = $("#input").val() || '';
        var valid = false, tooLong = false;
        var multiLine = text.match(/[\n\r]/);
        if (!text || text.length == 0 || $.trim(text).length == 0) { text = ''; }
        else if (text.length > 500 && !multiLine) { tooLong = true }
        else if (text.length > 10000) { tooLong = true; }
        else { valid = true; }
        if (valid || text.length == 0) {
            $("#sayit-button").prop("disabled", false).removeClass("disabled");
        } else {
            $("#sayit-button").prop("disabled", true).addClass("disabled");
        }
        if (text.length == 0) {
            $("#upload-file").prop("disabled", false).removeClass("disabled");
        } else {
            $("#upload-file").prop("disabled", true).addClass("disabled");
            if (!everTyped) {
                CHAT.Hub.firstTyping.fire();
                everTyped = true;
            }
        }
        inputError.tooLong(tooLong);
        if (multiLine) {
            $("#codify-button").fadeIn();
        } else {
            $("#codify-button").fadeOut();
        }
        return valid ? text : null;
    }

    function RunEasterEgg(text) {
        if (!mobile)
            Eggs.Current(text);
    }

    // called either as event handler for .click() on the "say it" button
    // or from the keydown handler on the input text box if the user pressed return
    // TODO: This function is a weird combination of sometimes repeated and sometimes
    // slightly different code and might thus need some cleanup.
    function sayIt() {
        var text = checkInput();
        var parentId;
        var isReplying = $('#reply-box').length > 0;
        if (isReplying) {
            parentId = parseInt($('#reply-box').attr('data-parent-id'));
        }
        if (!text) return;
        setInput("");
        checkInput(); // resets buttons etc after posting
        if (text == "//test") {
            runTests();
            return;
        }
        if (text == "//cash") {
            findCacheLeaks();
            return;
        }

        var message = getEditing();
        if (message.length > 0) {
            if (message.hasClass("pending")) {
                setInput(text);
                checkInput(); // resets buttons etc after posting
                notify("This message cannot be edited before it has been received by the server. Please try again.");
                return;
            }
            message.addClass("pending").data("source", text);
            message.data("previous_content", message.find(".content").html());
            message.info("parent_id", parentId);
            message.find(".content").html(clientSideRender(text));
        }
        else {
            var last_message = $("#chat > div.monologue:last div.message:last");
            var monologue;
            if (last_message.hasClass("pending")) {
                monologue = last_message.closest(".monologue");
            } else {
                var last_message_id;
                if (last_message.length == 0)
                    last_message_id = 0;
                else
                    last_message_id = last_message.messageId();
                monologue = getMonologue(current_user_id,
                    last_message_id + next_pending_id + 1000, // give a little headroom, in case the last two messages happen to have reverse ids
                    now(), CHAT.user.getName());
            }
            message = $("<div/>").addClass("message pending").attr("id", "pending-message-" + next_pending_id);
            var messageInfoContainer = $("<div/>").addClass("message-info-container break-word");
            messageInfoContainer.append($("<div/>").addClass("content").html(clientSideRender(text)));
            message.append(messageInfoContainer);
            message.appendTo(monologue.find(".messages"));

            if (parentId) {
                var showReplyInline = isParentWithinDistance(parentId) || hasSameParentReplyInMonologue(monologue, parentId);
                createRepliedTo(message, parentId, null, null, showReplyInline);
            }

            message.data("source", text);
            message.info("parent_id", parentId);
            ScrollTo(message, 200);
            next_pending_id++;
            showHideSilenceNote(true);
            RunEasterEgg(text);
        }
        cancelEditing();
        postMessage(message);
        clearEditWarning();
        serverThing.dataExpected();
        replyQueue.clear();
    }

    function toggleVote(jMessage, verb, target) {
        if (target) $(target).toggleClass("user-" + verb);
        if (verb == "flag" && !gc) // if they're not a mod, they should at least see their own flags
            jMessage.find(".flash").toggleClass("flag-indicator");
        var pastPerfect = { star: "starred", flag: "flagged"}[verb]; // it just had to be irregular verbs, didn't it?
        jMessage.info(pastPerfect, !jMessage.info(pastPerfect));

    }
    // note that the callback parameter is currently ignored for "star" and "flag"
    function messageAction(target, verb, info, callback) {
        var msg = $(target);
        if (!msg.hasClass("message"))
            msg = msg.closest(".message");
        var cb = callback;
        if (verb == "star" || verb == "flag") {
            var el = msg.find("." + verb + "s");
            toggleVote(msg, verb, el);
            cb = function (result) {
                if (result != "ok") {
                    toggleVote(msg, verb, el); // toggle back
                    notify(result || GENERIC_ERROR);
                };
            };
        }
        var msgid = msg.messageId();
        messageActionById(msgid, verb, info, cb, notify);
    }
    function star() {
        $(this).closest(".message").find(".popup").hide();
        messageAction(this, "star");
    }
    function ownerStar() {
        $(this).closest(".message").find(".popup").hide();
        messageAction(this, "owner-star");
    }
    function flag() {
        $(this).closest(".message").find(".popup").hide();
        if (confirmFlag(CHAT.user.canModerate()))
            messageAction(this, "flag");
    }
    function del() {
        var $message = $(this).closest(".message");
        var id = $message.messageId();
        var $repliedMsgs = $(`.pid-${id}`);
        if ($repliedMsgs.length > 0) {
            $repliedMsgs.each(function () {
                $(this).find(".reply-info-container .s-link").text("(removed)");
            });
        }

        $(this).hide();
        messageAction(this, "delete", null, handleEditResponse);
    }
    function undel() {
        $(this).hide();
        messageAction(this, "undelete", null, handleEditResponse);
    }
    function mustBeDeduped(evt) {
        var type = evt.event_type;
        return type === EventType.MessageFlagged || type === EventType.ModeratorFlag;
    }
    function onServerData(data, isIcc) {

        if (!data) return;
        var localKey = roomKey();

        if (data.reset) {
            is_heading_out = true;
            window.location.reload(); // the server is asking us *this client* (regardless of the room) to reload
            return;
        }

        var room = data[localKey];

        // Some events -- notably flags -- are sent to *every* room, so we have to dedupe
        var handledIds = {};

        sound.setIcc(isIcc);
        if(room!=null) {
            pollDone({ time: room.t, events: room.e, timeout: room.timeout, reason: room.reason, sync: data.sync,
                icc: isIcc, ms: room.ms, reset: room.reset, exit: room.exit, since: (room.t && room.d ? room.t - room.d : undefined)
            });
            if (room.e)
                Generator(room.e).filter(mustBeDeduped).map("event_id").forEach(function (id) { handledIds[id] = 1 });
        }
        Generator(data).filter(function (v) { return v[0] !== localKey; }).map("1").filter("e").map("e").forEach(function (events) {
            Generator(events).filter(function (evt) { return !handledIds[evt.event_id]; }).forEach(function (evt) {
                handleChatEvent(evt, true, isIcc);
                if (mustBeDeduped(evt))
                    handledIds[evt.event_id] = 1;
            });
        })
        sound.play();
        showQueuedDesktopReplies();
    }
    function roomKey() {
        return (highlights ? 'h' : 'r') + ROOM_INFO.id;
    }
    function handleBroadcast(message) {
        if (!message.content.command)
            return;

        switch (message.content.command) {
            case "dismiss notification":
                notifier.dismissSingleNotification(message.content.notification, false);
                break;
            case "clear mention":
                sidebar.dismissOtherRoomMention(message.content.roomid, message.content.messageid);

                // for Tim Stone, who has the same room open several times
                if (message.content.roomid === ROOM_INFO.id && !message.content.messageid)
                    replyQueue.clear(/*noBroadcast =*/ true)
                break;
            case "leave all":
                window.location.href = "/";
        }
    }

    function toggleInputAsCode() {
        var txt = $("#input").val();
        var lines = txt.split(/[\n\r]/g);
        var indented = false, outdented = false;
        $.each(lines, function (i, s) { if (s.match(/^ {4,}/)) indented = true; else outdented = true; });

        if (indented && !outdented) {
            $.each(lines, function (i, s) { lines[i] = s.substring(4); });
        } else {
            $.each(lines, function (i, s) { lines[i] = "    " + s; });
        }
        txt = lines.join("\n");
        setInput(txt);
    }

    function editSibling(previous) {
        if (!previous && !upDownOnly) { // pressing UP works everytime the input box is empty; DOWN is less forgiving
            return false;
        }
        var currentlyEditing = getEditing();
        if (currentlyEditing.length == 0) {
            return previous ? editLast() : false;
        }

        if (!upDownOnly) {
            return false;
        }

        var monologue = currentlyEditing.closest(".monologue");
        if (!monologue.hasClass("mine")) {// currently editing someone elses post
            return false;
        }

        var inp = $("#input"),
            currentContent = inp.val();
        if (currentContent != currentlyEditing.data("source")) {// post has been changed (presumably via mouse, since the key check passed)
            return false;
        }

        if (inp.caret().start < currentContent.length) {// cursor has been moved
            return false;
        }

        var sibling = previous ? currentlyEditing.prev(".message") : currentlyEditing.next(".message");
        if (!sibling.length) {
            if (previous)
                sibling = monologue.prevAll(".monologue.mine").first().find(".message:last");
            else
                sibling = monologue.nextAll(".monologue.mine").first().find(".message:first");
        }
        if (!sibling.length) {
            return false;
        }

        if (!CHAT.user.canEditAndDeleteOldPosts() && secondsSince(sibling.info("time")) > 118) {
            return;
        }

        cancelEditing();
        editMessage(sibling);
        return true;
    }

    function editPrevious() {
        return editSibling(true);
    }

    function editNext() {
        return editSibling(false);
    }

    function editLast() {
        var text = $("#input").val();
        var last_sent = $(".monologue.mine").last().find(".message").last();
        if (last_sent.length == 0 || last_sent.find("> .content > .deleted").length)
            return false;
        // Moderators can edit their own messages in any room.
        var may_be_edited = CHAT.user.canEditAndDeleteOldPosts() || (secondsSince(last_sent.info("time")) < 118);
        if ((!text || $.trim(text).length == 0) && last_sent && may_be_edited) {
            editMessage(last_sent);
            upDownOnly = true;
            return true;
        }
        return false;
    }

    function deleteLast() {
        var last_sent = $(".monologue.mine").last().find(".message").last();
        // Moderators can delete their own messages in any room.
        var may_be_deleted = CHAT.user.canEditAndDeleteOldPosts() || (secondsSince(last_sent.info("time")) < 120);
        if (last_sent.length > 0 && may_be_deleted) {
            if (confirm("Delete your last message?"))
                messageAction(last_sent, "delete", null, function (data) { handleEditResponse(data); });
        } else {
            notify("Last message can't be deleted.");
        }
    }

    var upDownOnly = true; // records whether any key except up/down was pressed since an arrow-key-editing started -- once any other key was pressed, up/down don't switch the message anymore

    function bindInput() {
        var input = $("#input");
        tabCompleter();
        input.bind("keydown", function (evt) {
            var handled = false;
            if (evt.which == 229) return; // https://meta.stackexchange.com/a/232251/23354
            upDownOnly = upDownOnly && (evt.which == 38 || evt.which == 40);
            switch (evt.which) {
                case 13: // ret
                    if (!evt.shiftKey && (!mobile || CHAT.NEW_MOBILE)) {
                        sayIt();
                        handled = true;
                    }
                    break;
                case 27: // esc
                    if (!input.val())
                        replyQueue.clear();
                    cancelEditing();
                    cancelReplying();
                    handled = true;
                    break;
                case 38: // up
                    handled = editPrevious();
                    break;
                case 40: // down
                    handled = editNext();
                    break;
                case 75: // k
                    if (evt.ctrlKey) {
                        toggleInputAsCode();
                        handled = true;
                    }
                    break;
            }

            if (handled)
                evt.preventDefault();

        }).bind("paste keyup", checkInput);
        checkInput();
        if (!mobile) $(document).bind("keypress", function (evt) {
            if (evt.ctrlKey || evt.altKey || evt.metaKey)
                return;
            var character;
            if (evt.which && evt.which != 13 && evt.which != 32
                && evt.target.nodeName.toLowerCase() != 'input'
                && evt.target.nodeName.toLowerCase() != 'textarea'
                && $(evt.target).closest(".popup").length == 0
                && (character = String.fromCharCode(evt.keyCode || evt.which))) {
                // above checkes that it is a character, rather than arrows etc

                // In most browsers, focusing the textbox during keypress will cause
                // the typed letter to end up in the input. Not so in Firefox.
                // To fix this, we bind a handler to the `input` event, and we
                // also add a 0-ms timeout. If the browser accepted the pressed key
                // for the input box, the `input` handler will run before the timeout
                // (because the `input` event fires synchronously). If that happens,
                // we know the browser has handled the keypress correctly, and we
                // can unbind the timeout. So *if* the timeout fires, we know that the
                // input event hasn't fired and thus the character hasn't been added to
                // the input box, and we do it programmatically. In both the event handler
                // and the timeout handler, we unbind the event handler.

                var doItOurselves = function() {
                    input.off("input", handler);
                    setInput(input.val() + character);
                }
                var timeout = setTimeout(doItOurselves, 0)
                var handler = function () {
                    input.off("input", handler);
                    clearTimeout(timeout);
                }
                input.on("input", handler)
                input.focus();
            }
        });
    }

    function setupMobile() {
        $("#getmore").click(gimmeMore);
        sidebar = Mobile(ROOM_INFO, {
            scrollTo: ScrollTo,
            editMessage: editMessage,
            messageAction: messageAction,
            handleEditResponse: handleEditResponse,
            checkIfOnBottom: checkIfOnBottom,
            replyToMessage: replyToMessage,
            messageDetails: messageDetails
        });
        initHiddenUsers(ignoreList);
        sound = {};
        icc = {};
        replyQueue = CHAT.NEW_MOBILE ? ReplyQueue() : MobileReplyQueue(function (cb) { onNewMessage = cb; });
        feedTicker = {};
        icc.broadcast = icc.receive = sound.play = sound.queue = sound.setIcc =
            feedTicker.add = function () { }; // TODO: mobile version of notification -- how?
        var actualNotifier = Notifier(null, true);// note that we're not actually using the returned notifier object
        notify = actualNotifier.notify;
        notifier = {
            notify: notify,
            dismissSingleNotification: CHAT.NEW_MOBILE ? actualNotifier.dismissSingleNotification : function () {},
            desktop: null };
        icc.id = 'n/a';
        $("#sayit-button").click(function () {
            sayIt();
            $("#cancel-editing-button").hide();
            $("#gotomenu-main").show();
        });

        bindInput();
        $("#edit-last").click(function (evt) {
            if (!editLast()) {
                alert("Last message cannot be edited.");
            }
            else {
                $("#gotomenu-main").hide();
                $(".mobile-menu").slideUp(200);
                $("#input").prop("disabled", false).focus();
            }
            evt.preventDefault();
        });
        $("#delete-last").click(function () {
            $(".mobile-menu").slideUp(200);
            $("#input").prop("disabled", false);
            deleteLast();
        });
        $("#cancel-editing-button").click(function () {
            cancelEditing();
            $("#gotomenu-main").show();
        });

        $.ajaxSetup({
            error: function (req, err) {
                var resp;
                try {
                    resp = req.statusText;
                } catch (exc) {
                    resp = "unavailable";
                }
                debugMessage("AJAX request failed. Server response: " + resp + ". Error: " + err);
            },
            cache: false,
            timeout: 20000
        });
    }
    function sendText(text) {
        if (!text) return;
        text = text.toString();
        if (!text || !text.length) return;
        setInput(text);
        sayIt();
    }

    function setup() {
        popupDismisser(); // must be before any .live()s, because it binds an event to the document
        icc = InterClientCommunicator();
        icc.receive(handleBroadcast);
        notifier = Notifier(icc);
        notify = notifier.notify;
        CROSS_TAB = CrossTab(ROOM_INFO.id);
        CROSS_TAB.initSound(OPTIONS.sound);
        CROSS_TAB.initDesktopNotificatons(notifier.desktop);
        sound = SoundManager(CROSS_TAB);


        replyQueue = ReplyQueue();
        feedTicker = FeedTicker();
        $("#sayit-button").click(sayIt);
        $("#cancel-editing-button").click(cancelEditing);
        $("#codify-button").click(toggleInputAsCode);
        $("#getmore").click(gimmeMore);
        var uploadFile = $('#upload-file');
        if (uploadFile.length > 0) {
            var fileHandler = initFileUpload();
            uploadFile.click(function () { fileHandler.showDialog(sendText); });
        }
        $("#getmore-mine").click(gimmeMoreMine);
        $("#adm-delete").click(deleteMultiple);
        $("#adm-move").click(moveMultiple);
        $("#sel-cancel").click(toggleMessageAdmin);
        sidebar = Sidebar(ROOM_INFO, messageActionById, { notify: notify, icc: icc }, toggleMessageAdmin, toggleSelectMode, OPTIONS.may_bookmark);

        $("#chat").on("click", ".action-link", messageMenu);
        $("#chat").on("click", ".message .content", selectMessage);

        initHiddenUsers(ignoreList);
        $("#active-user").data("user", current_user_id);
        tryRestoreDraftAndReplyState(); // has to be before bindInput(); the latter calls checkInput(), which will (e.g.) show the "fixed font" button if the draft is multiline

        bindInput();

        $.ajaxSetup({
            error: function (req, err) {
                var resp;
                try {
                    resp = req.statusText;
                } catch (exc) {
                    resp = "unavailable";
                }
                debugMessage("AJAX request failed. Server response: " + resp + ". Error: " + err);
            },
            cache: false,
            timeout: 10000
        });

        $(document).on("click", ".user-container .signature, .user-container > .username, .user-container .avatar", UserUi(current_user_id, ROOM_INFO.id, notify, ROOM_INFO.host).showUserPopupMenu);

        // this works in my Chrome 4 (somewhat), FF 3.6 and IE8 on Win7
        // however, reading https://stackoverflow.com/questions/1255686,
        // we'll have to resort to some trickery to get this to
        // work everywhere
        $(window).focus(function (evt) {
            windowHasFocus = true;
            waitingMessages = 0;
            updateTitle();

            last_message_on_focus = $("#chat > div.monologue:last div.message:last");

        });
        $(window).blur(function (evt) {
            windowHasFocus = false;
            var last = $("#chat > div.monologue:last div.message:last");

            // a) don't do anything if nothing has changed since we came back to this page, but
            // more importantly b) for some reason, Chrome fires a focus -> blur -> focus chain
            // when you come back by clicking on the page.
            if (!last_message_on_focus || last.get(0) != last_message_on_focus.get(0)) {
                last_message_on_blur = last;
                $("#chat div.catchup-marker").each(function () {
                    for (var i = 3; i > 0; i--) {
                        if ($(this).hasClass("catchup-marker-" + i)) {
                            $(this).removeClass("catchup-marker-" + i);
                            if (i < 3)
                                $(this).addClass("catchup-marker-" + (i + 1));
                            else
                                $(this).removeClass("catchup-marker");
                        }
                    }
                });
                last_message_on_blur.closest(".monologue").addClass("catchup-marker catchup-marker-1");
            }
        });

        if (current_user_id > 0 && notifier.desktop) {
            var widgetDiv = $('<div class="sidebar-widget"/>').appendTo('#sidebar #widgets');
            var toggleLink = $('<a id="toggle-notify"/>').attr('href', '#');
            if (!notifier.desktop()) OPTIONS.desktopNotify = false; // check at client
            var refreshState = function() {
                toggleLink.text((OPTIONS.desktopNotify ? 'dis' : 'en') + 'able desktop notification');
            };
            refreshState();
            toggleLink.click(function () {
                if (OPTIONS.desktopNotify) { // trivial to disable
                    $.post('/users/desktopnotify', fkey({ value: false }), function () {
                        OPTIONS.desktopNotify = false; refreshState();
                    });
                } else { // need to check more when enabling
                    notifier.desktop({ callback: function () {
                            if (notifier.desktop()) {
                                $.post('/users/desktopnotify', fkey({ value: true }), function () {
                                    OPTIONS.desktopNotify = true; refreshState();
                                });
                            } else {
                                notify('Desktop notification is blocked by your browser; <a href="/help/desktop-notifications">help me with this</a>');
                            }
                        }
                    });
                }
                return false;
            }).appendTo(widgetDiv);
        }
    }

    var gc = $("#flag-count").length > 0;

    // Opera doesn't support onbeforeunload, so "are you sure" doesn't work. But we can at least
    // store the draft etc. Note that just because Opera at least supports onunload, this doesn't
    // mean that the support doesn't suck.
    $(window).bind('beforeunload', function () {
        if (!trySaveDraftAndReplyState() && !sidebar.isLeaving)
            return "This will lose your unsent message; continue?";
    }).bind("unload", function () {
        serverThing.windowClosing();
        if (notifier.desktop)
            notifier.desktop.removeAll();
    });

    $("#chat").hide();
    $("#mini-help").click(function (evt) {
        evt.preventDefault();
        evt.stopPropagation();
        var content = $("<div/>");
        var link = $("<a/>").html("More&hellip;").attr("href", $(this).attr("href"));
        popUp(evt.pageX, evt.pageY).addClass("mini-help").append(content).append(link).css("bottom", null).css("right", null);
        content.load("/faq #mini-help");
    });


    if (OPTIONS.egg)
        Eggs.load(OPTIONS.egg);
    else
        delete Eggs.load;

    mobile ? setupMobile() : setup();

    // doing this in nextTick because in the mobile version, the other rooms are added via javascript, and that happens
    // after the call to startChat().
    setTimeout(function () {
        if (pendingReplies) {
            for (var messageid in pendingReplies) if (pendingReplies.hasOwnProperty(messageid)) {
                var roomid = pendingReplies[messageid];
                messageid = parseInt(messageid, 10);
                if (roomid == ROOM_INFO.id)
                    replyQueue.add(messageid);
                else
                    sidebar.otherRoomMention(roomid, messageid);
            }
        }
        delete pendingReplies;
    }, 0);

    var serverThingOptions = {
        onData: onServerData,
        onProblem: function () {
            notify("There seems to be a problem connecting to the server. Please check your internet connection and reload this page.", "server-connect");
        },
        onProblemResolved: function () {
            var priorProblem = $("body > div.notification .notification-message.server-connect");
            if (priorProblem.length > 0) {
                notifier.dismissSingleNotification(priorProblem.text(), false); // no need to broadcast -- each client can figure out themselves that the connection is back
            }
        },
        getPollingDelay: function () { return throttler.get_rate() * 1000; },
        useWebsocket: OPTIONS.ws,
        roomId: ROOM_INFO.id,
        roomKey: roomKey()
    };

    // highlights mode is just too weird and rare (does anyone actually use it?) to waste time thinking about ICC handling
    if (!highlights)
        serverThingOptions.icc = icc;

    loadInitialMessages().done(function () {
        serverThingOptions.lastKnownId = lasttime;
        serverThing = TheThingThatGetsDataFromTheServer(serverThingOptions);
    });

    updateStars(null, true);
    updateFlags(null, true);
    sidebar.updateFiles();
    var modTools = moderatorTools(notify);
    return { sidebar: sidebar, initFlagSupport: modTools.initFlagSupport };
};


// End PartialJS/chat.js

// Begin PartialJS/easterEggsBase.js

/* easterEggsBase.js */
;
var Eggs = {};

Eggs.load = function (name) {
    var script = $("script").filter(function () { return /master-chat[\w-]*?\.js/i.test(this.src) }).eq(0);
    if (script.length!=1)
        return; // shouldn't happen

    var eggsPath = script.attr("src").replace(/master-chat[\w-]*?\.js/i, "eggs.js").replace(/v=\w+/, "v=3");

    loaded = function () {
        for (var prop in Eggs) {
            if (!Eggs.hasOwnProperty(prop) || prop == "Current")
                continue;
            if (prop == name)
                Eggs.Current = Eggs[prop];
            else
                delete Eggs[prop]; // note that this includes Eggs.load; that's intentional
        }
        if (name != "Asteroids")
            delete window.initAsteroids;
        if (Eggs.Current && Eggs.Current.init)
            Eggs.Current.init()
    }

    $.ajax({
        url: eggsPath,
        success: loaded,
        dataType: "script",
        cache: true
    });

}

Eggs.Current = function () { };

// End PartialJS/easterEggsBase.js

// Begin PartialJS/time.js

/* time.js */
;
// takes a UTC unix time stamp

var month_name = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
var weekday_name = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]
function now() {
    return (new Date()).getTime() / 1000 + SERVER_TIME_OFFSET; // unlike the rest of the world, JavaScript thinks in milliseconds.
                                                               // SERVER_TIME_OFFSET gets set in the page
}

function secondsSince(t) {
    return now() - t;
}

function localTimeSimple(t) {
    var d = new Date(t * 1000);
    var min = d.getMinutes();
    var today = (new Date()).setHours(0, 0, 0, 0) / 1000;
    var result = d.getHours() + ":" + (min < 10 ? "0" : "") + min;
    if ((today - t) > 0) {
        if ((today - t) < 86400)
            result = "yst " + result
        else if ((today - t) < 518400) // 6 days
            result = weekday_name[d.getDay()] + " " + result;
        else
            result = month_name[d.getMonth()] + " " + d.getDate() + ", " + result;
    }
    return result;
}

function plural(num, singular, longer) {
    if (!longer)
        return num + singular.substr(0, 1)
    return num + " " + singular + (num != 1 ? "s" : "");
}

function timeSpanString(delta, longer, realDate, relativeSuffix) {
    relativeSuffix = relativeSuffix || "";
    if (delta < 60) {
        return plural(Math.round(delta), "second", longer) + relativeSuffix;
    }
    if (delta < 3600) { // 60 mins * 60 sec
        return plural(Math.round(delta / 60), "minute", longer) + relativeSuffix;
    }
    if (delta < 86400) { // 24 hrs * 60 mins * 60 sec
        return plural(Math.round(delta / 3600), "hour", longer) + relativeSuffix;
    }
    var days = Math.round(delta / 86400);
    if (days <= 2 || !realDate) {
        return plural(days, "day", longer) + relativeSuffix;
    }
    var min = realDate.getMinutes();
    var result = month_name[realDate.getMonth()].toLowerCase() + " " + realDate.getDate() + " at " + realDate.getHours() + ":" + (min < 10 ? "0" : "") + min;
    if (days > 330)
        result = result.replace(" at ", " '" + ("" + realDate.getFullYear()).substr(2, 2) + " at ");
    return result;
}
function ToRelativeTimeMini(t, absoluteIfOverTwoDays) {
    var delta = secondsSince(t);
    var realDate = null;
    if (absoluteIfOverTwoDays)
        realDate = new Date(t * 1000);
    if (delta <= 0) return "just now";
    return timeSpanString(delta, false, realDate, " ago");
}

// End PartialJS/time.js

// Begin PartialJS/throttler.js

/// <reference path="~/Views/Room/SingleRoom.aspx" />
/* throttler.js */
;

function Throttler() {
    var MIN_INTERVAL = 2;
    var MAX_INTERVAL = 15;

    // How many seconds of nothing happening at all for the polling rate to reach MAX_INTERVAL?
    var DEAD_THRESHOLD = 20 * 60;

    // How many seconds of silence (i.e. no speaking/editing) for the polling rate to reach MAX_INTERVAL?
    // This is obviously higher than DEAD_THRESHOLD
    var SILENT_THRESHOLD = 30 * 60;

    var last_happening = now();
    var last_speaking = now();

    function report_happening(time, onlyIfLater) {
        time = time || now();
        if (!onlyIfLater || time > last_happening)
            last_happening = time;
    }
    function report_speaking(time, onlyIfLater) {
        time = time || now();
        if (!onlyIfLater || time > last_speaking)
            last_speaking = time;
        report_happening(time, onlyIfLater);
    }

    // returns the number of seconds that should be waited until the next poll
    function get_rate() {
        var since_happening = Math.max(0, secondsSince(last_happening));
        var since_speaking = Math.max(0, secondsSince(last_speaking));

        if (since_happening >= DEAD_THRESHOLD || since_speaking >= SILENT_THRESHOLD)
            return MAX_INTERVAL;

        // both values are between 0 and 1
        var happening_ratio = since_happening / DEAD_THRESHOLD;
        var speaking_ratio = since_speaking / SILENT_THRESHOLD;

        // If either of the above ratios is close to 1, then so is this number.
        // If something recently happened, this value is close to 0.
        var ratio = 1 - (1 - happening_ratio) * (1 - speaking_ratio);

        return MIN_INTERVAL + (MAX_INTERVAL - MIN_INTERVAL) * ratio;
    }

    return {
        get_rate: get_rate,
        report_happening: report_happening,
        report_speaking: report_speaking
    }

}

// End PartialJS/throttler.js

// Begin PartialJS/ui.js

/// <reference path="~/Views/Room/SingleRoom.aspx" />
/* ui.js */
;

function debugMessage(text) {
    $("<div class='debug-message'/>").text(text).prependTo($("#debug-messages"));
    $("#debug-message-container > h2").text("Debug (" + $(".debug-message").length + ")");
    $("#debug-messages:hidden").next().css({ color: "red" });
}

function UserUi(current_user_id, room_id, notify, host) {

    function invite(uid, rid) {
        $.ajax({
            type: "POST",
            url: "/users/invite",
            data: fkey({ UserId: uid, RoomId: rid }),
            success: function (body) { if (notify) notify(body); },
            dataType: "text",
            error: function (xhr, failType) {
                if (notify) notify(failType == 'error' ? (xhr.status == 409 ? xhr.responseText : "An error occurred performing this action") : failType);
            }
        });
    }

    function inviteClick(evt) {
        var match = /^invite-(\d+)-(\d+)/.exec($(this).closest("li").attr("id"));
        var uid = parseInt(match[1]), rid = parseInt(match[2]);
        evt.preventDefault();
        invite(uid, rid);
        $(this).closest(".popup").remove();
    }

    function showUserPopupMenu(evt) {
        if (evt.button != 0 || evt.ctrlKey)
            return;
        evt.stopPropagation(); // note comment above function popUp()
        evt.preventDefault();
        var uid = $(this).closest(".user-container").data("user");
        var userObj = CHAT.RoomUsers.getIfAvailable(uid);
        var uname = (userObj || { name: "" }).name;
        var popup = popUp(evt.pageX, evt.pageY).addClass("user-popup");
        if (!uid) {
            popup.append("<p>no user data available</p>");
            return;
        }
        var imgLink = $("<a/>").attr('rel', 'noopener noreferrer');
        var img = $("<img/>").attr("src", IMAGE("ajax-loader.gif"));
        var name = $("<h4/>").addClass("username");
        name.text(uname);
        imgLink.appendTo(popup);
        img.appendTo(imgLink);
        name.appendTo(popup);

        var url = "/users/thumbs/" + uid;
        $.getJSON(url, { showUsage: true, roomId: CHAT.CURRENT_ROOM_ID }, function (data) {

            if (data == null) return;

            // Might as well update our local data.
            // Note: NOT updating last_seen and last_post, since /users/thumbs
            // returns *sitewide* values, but we only care about *this* room
            // NOTE: No longer updating name and email_hash as those
            // are now room dependent
            CHAT.RoomUsers.update(uid, {
                // name: data.name,
                is_moderator: data.is_moderator,
                // email_hash: data.email_hash,
                reputation: data.reputation
            }, /*skipUiUpdate=*/false, /*incomplete=*/true);

            name.text(data.name).attr("title", repNumber(data.reputation));
            if (data.is_moderator)
                name.append("<span> &#9830;</span>");
            name.addClass("username");
            if (data.is_moderator) { name.addClass("moderator"); }
            if (data.email_hash) {
                img.attr("src", gravatarUrl(uid, data.email_hash, 48 * (window.devicePixelRatio||1))).addClass("user-gravatar48");
            } else {
                img.remove();
            }
            if (data.site) {
                $("<img/>").attr("width", 16).attr("height", 16).attr("src", data.site.icon).addClass("small-site-logo")
                    .attr("alt", data.site.caption).attr("title", data.site.caption).appendTo(popup);
            }

            popup.append("<br style='clear:both;'/>");
            if (data.user_message && data.user_message.length > 0) {
                $("<p/>").text(data.user_message).appendTo(popup);
            }
            if (data.usage && data.usage.length > 0) {
                popup.append(data.usage);
            }

            var last_dates = [];
            if (data.last_seen && uid > 0) {
                last_dates.push("seen <b>" + ToRelativeTimeMini(data.last_seen) + "</b>");
            }
            if (data.last_post) {
                last_dates.push("talked <b>" + ToRelativeTimeMini(data.last_post) + "</b>");
            }
            if (last_dates.length)
                popup.append("<div class='last-dates'>" + last_dates.join(", ") + "</div>");

            if (data.issues) {
                var s = data.issues > 1 ? "s": "";
                popup.append($("<div/>").append($("<a/>").attr('rel', 'noopener noreferrer').text(data.issues + " annotation" + s + "/suspension" + s).attr("href", "/admin/annotations/" + uid).css("font-weight", "bold")));
            }
            var userProfile = "/users/" + data.id + "/" + urlFriendly(data.name);
            popup.append($("<div/>").append($("<a/>").text("user profile").attr("href", userProfile).attr('rel', 'noopener noreferrer')));
            imgLink.attr("href", userProfile);
            if (data.profileUrl && data.profileUrl.length > 0) {
                popup.append($("<div/>").append($("<a/>").text("user profile on " + data.host).attr("href", data.profileUrl).attr('rel', 'noopener noreferrer')));
            }
            if (data.rooms && data.rooms.length) {
                popup.append("<h5>Rooms</h5>");
                var ul = $("<ul/>").addClass("no-bullets");
                $.each(data.rooms, function (i, room) {
                    ul.append($("<li/>").append($("<a/>").text(room.name).attr("href", "/rooms/" + room.id + "/" + urlFriendly(room.name)).attr("target", "_self")
                        .attr('rel', 'noopener noreferrer').attr("title", room.activity + " posts total" + (room.last_post ? ("; last post " + ToRelativeTimeMini(room.last_post)) : ""))));
                });
                popup.append(ul);
            }

            if (data.id != current_user_id) {
                popup.append("<h5>Actions</h5>");

                if (data.invite_targets && data.invite_targets.length) {
                    var inv = $("<div/>").appendTo(popup);
                    var showhide = $("<a/>").html("invite this user&hellip;").appendTo(inv).attr("href", "#").click(function (evt) {
                        $(this).next().slideToggle();
                        evt.preventDefault();
                    });
                    var inv_list = $("<ul/>").addClass("no-bullets").appendTo(inv).hide();
                    $.each(data.invite_targets, function (i, room) {
                        var name = room.name;
                        if (room.id == room_id) // invite to *this* room
                            name = "<b>" + name + "</b>";
                        inv_list.append($("<li/>").attr("id", "invite-" + uid + "-" + room.id).append(
                            $("<a/>").html("&hellip;to " + name).attr("href", "#").click(inviteClick)
                        ));
                    });
                }
                if (uid > 0 && userObj && !userObj.can_moderate && CHAT.user.isOwner() && data.is_registered) {
                    popup.append($("<div/>").append($("<a/>").text((userObj.is_owner ? "remove" : "add") + " as room-owner").attr("href", "#").click(function () {
                        $.post('/rooms/setuseraccess/' + room_id, fkey({ aclUserId: uid, userAccess: (userObj.is_owner ? "read-write" : "owner") }), function (data) {
                            if (data && data.length > 0) {
                                notify(data);
                            } else {
                                userObj.is_owner = userObj.is_owner ? false : true; /* must remember the js object-safe shortcut for this ;p */
                                $(".user-container.user-" + uid + " .username").toggleClass("owner");
                            }
                        });
                        popup.remove();
                        return false;
                    })));
                }
                if (data.may_pairoff) {
                    popup.append($("<div/>").append($("<a/>").text("start a new room with this user").attr("href", "#").click(function (evt) {
                        evt.preventDefault();
                        promptUser(evt, "<p>To create a new room and automatically invite this user, please enter a name for the new room.</p>" +
                            "<p>Please note that the room will be public, and anybody can join your conversation.</p>" +
                            "<p>You will automatically enter the new room upon creation.</p>",
                            "Room for " + CHAT.user.getName() + " and " + data.name, function (name) {
                                $.post('/rooms/pairoff', fkey({ withUserId: data.id, name: name }), function (data) {
                                    if (data && /^\d+$/.test(data))
                                        window.location.href = "/rooms/" + data;

                                });
                            })
                    })));
                }
                popup.append($("<div/>").append($("<a/>").text(shouldShowUser(data.id) ? "hide posts" : "show posts").attr("href", "#").click(function () {
                    showHideForUser(data.id, shouldShowUser(data.id));
                    popup.remove();
                    return false;
                })));
                if (current_user_id > 0) {
                    popup.append($("<div/>").append($("<a/>").text(shouldShowUser(data.id) ? "ignore this user (everywhere)" : "don't ignore this user").attr("href", "#").click(function () {
                        $.post("/users/ignorelist/" + (shouldShowUser(data.id) ? "add" : "remove"), fkey({ id: data.id }));
                        showHideForUser(data.id, shouldShowUser(data.id), false, true);
                        popup.remove();
                        return false;
                    })));
                }
                if (data.id > 0 && !data.can_moderate && CHAT.user.canKick()) {
                    popup.append($("<div/>").append($("<a/>").text("kick-mute this user").attr("href", "#").click(function (evt) {
                        evt.preventDefault();

                        var requestData = fkey({ userId: data.id });

                        var proceed = false;


                        var message = "Do you want to kick " + data.name + " out of this room? Other room owners and moderators may be notified.";
                        if ($('.moderator-room').length > 0) {

                            var answer = window.prompt(message + '\n\nEnter a custom duration in minutes (optional):');

                            if (answer === '') {
                                proceed = true;
                            } else if (answer) {
                                var duration = +answer;
                                if (!isNaN(duration) && duration > 0) {
                                    requestData.duration = duration;
                                    proceed = true;
                                } else {
                                    alert('Duration must be blank or a positive number.');
                                }
                            }

                        } else {
                            proceed = window.confirm(message);
                        }

                        if (proceed) {
                            $.post('/rooms/kickmute/' + room_id, requestData).done(function (response) {
                                if (response && response.message)
                                    notify(response.message);
                            }).fail(function (xhr, failType) {
                                var message = failType == 'error' ? (xhr.status == 409 ? xhr.responseText : "An error occurred while trying to kick this user.") : failType;
                                notify(message);
                            });
                            popup.close();
                        }
                    })));
                }
            }
        });
    }
    return {
        showUserPopupMenu: showUserPopupMenu
    }
}
function showHideForUser(id, hide, forced, animated) {
    if (!id) return;
    id = id.toString();
    var old = hiddenUsers[id];
    if (!forced && ((old && hide) || (!old && !hide))) return; // no change; deliberately avoiding == here due to undefined
    hiddenUsers[id] = hide;
    $(".monologue.user-" + id).each(function () {
        showHideMonologue(this, hide, animated);
    });
    var usr = $("#present-user-" + id);
    if (hide) { usr.addClass("ignored"); } else { usr.removeClass("ignored"); }
}
function showHideMonologue(div, hide, animated) {
    var items = $(div).andSelf();
    if (hide) items.hide(animated ? 500 : 0); else items.show(animated ? 500 : 0);
}
function shouldShowUser(id) {
    var hidden = id && hiddenUsers[id.toString()];
    return hidden ? false : true; // want typed as bool
}

function promptUser(evt, msg, defaultText, callback, large, inputConfiguration, validator) {

    var popup = popUp(evt.pageX, evt.pageY);
    $("<div/>").html(msg).appendTo(popup);
    var inp, val;
    var isValid;
    isValid = validator ? function (txt) {
        var msg = validator(txt) || "";
        popup.find(".error").remove();
        if (msg.length) {
            $("<div/>").text(msg).addClass("error").appendTo(popup);
            popup.find(".button").addClass("disabled");
            return false;
        }
        else { popup.find(".button").removeClass("disabled"); }
        return true;
    } : function() {return true;};
    if (large) {
        inp = $("<textarea/>").keypress(function (evt) { isValid(inp.val()); });
    }
    else {
        inp = $("<input/>").attr("type", "text").keypress(function (evt) {
            var txt = inp.val();
            var isOk = isValid(txt);
            if (isOk && evt.which == 13) {
                callback(txt);
                popup.close();
            }
        });
    }
    inp.val(defaultText || "").appendTo(popup);
    if (inputConfiguration) inputConfiguration(inp);
    $("<p><span class='button'>OK</span></p>").appendTo(popup).click(function () {
        var txt = inp.val();
        if (isValid(txt)) {
            callback(txt);
            popup.close();
        }
    });
    if (defaultText)
        inp.caret(0, defaultText.length);
    isValid(defaultText || "");
    popup.show();
}

function timeoutPrompt(evt, msg, callback) {

    var popup = popUp(evt.pageX, evt.pageY);
    $("<div/>").html(msg).appendTo(popup);

    var dur = $("<input/>").attr("type", "text");
    dur.val(60).appendTo(popup);
    $("<div/>").html("Timeout reason:").appendTo(popup);
    var msgInp = $("<textarea/>").attr("type", "text").attr("maxlength", "100");
    msgInp.appendTo(popup);
    $("<p><span class='button'>OK</span></p>").appendTo(popup).click(function () {
        callback(dur.val(), msgInp.val());
        popup.close();
    });
    popup.show();
}

$(function () {

    $("#debug-message-container > h2").click(function () {
        $("#debug-messages").slideToggle();
        $(this).css({ color: "black" });
    });
    $("#debug-messages").dblclick(function () {
        $(".debug-message").slideUp(function () { $(this).remove(); });
        $("#debug-message-container > h2").text("Debug (0)");
    });

});

// End PartialJS/ui.js

// Begin PartialJS/tabcompleter.js

(function () {
    window.tabCompleter = function tabCompleter() {
        GenericTabCompleter(function (word) { return modeInfo(word); });
    }

    var textUpdaterCallback;
    tabCompleter.onShowUser = function (callback) { textUpdaterCallback = callback; }

    function makeRecipients(recipients, typedWord, isSuperPing) {
        var count = recipients.length;
        var result = $([]);
        if (!count)
            return result;

        var max = isSuperPing ? 20 : 10;

        if (count > max)
            recipients = recipients.slice(0, max - 1);

        var capGroup = CHAT.IS_MOBILE ? 5 : count > 1 ? 5 : 20;

        $.each(recipients, function (i, group) {
            var u = group.first();
            var name = u.name;
            var li = $("<li/>").data("mention-text", u.mention);
            var groupSize = 0;
            var extras = [];
            group.forEach(function (u2) {
                if (u2.status)
                    extras.push(u2.status);
                groupSize++;
                if (groupSize > capGroup)
                    return;
                if (u2.hash) {
                    li.append(
                        $("<img />").attr("width", 18).attr("height", 18).attr("src", gravatarUrl(u2.id, u2.hash, 18))
                    );
                } else {
                    li.append(CHAT.RoomUsers.createAvatarImage(u2.id, 18));
                }
            });
            if (groupSize > capGroup) {
                li.find("img:last").remove();
                li.append($("<span class='extra-info'/>").text("(+" + (groupSize - capGroup + 1) + ") "));
            }
            li.append($("<span class='mention-name'/>").text(name));
            if (extras.length) {
                li.append($("<span class='extra-info'/>").text(extras.join(", ")));
            }

            if (textUpdaterCallback && ("id" in u)) {
                var res = textUpdaterCallback(u.id, u.name);
                if (typeof res === "string" && res) {
                    li.append($("<span class='extra-info' />").text(res));
                } else if (res && typeof res.done === "function") {
                    res.done(function (s) { if (s) li.append($("<span class='extra-info' />").text(s)); })
                }
            }
            result = result.add(li);
            if (("" + (u.group || u.normalized)).toLowerCase() == typedWord.toLowerCase())
                li.addClass(isSuperPing || count === 1 ? "chosen" : "exact-match");
            else if (u.autoselect) {
                setTimeout(function () {
                    li.click();
                }, 0)
            }
        });
        if (count > max) {
            var s = isSuperPing ? "" : " " + (count - max + 1);
            result = result.add($("<li class='too-many' />").html("<span>(and" + s + " more matches)</span>"));
        }
        return result;
    }

    var normalMentionMode = {
        completionFor: function (jElem) {
            return jElem.data("mention-text");
        },
        getCompletionItems: function (word) {
            var re;
            var allNonAscii = !word.length;
            var normalizedWord = normalizeUserName(word.toLowerCase());
            if (allNonAscii)
                re = /^\W/; // if there's only the "@" character without anything else, show all candidates with crazy moon characters at the start
            else
                re = new RegExp("^" + normalizedWord.replace(/([[\].{}\\+*?^$()|])/g, "\\$1"));

            var all = CHAT.RoomUsers.pingableUsersIncludeIncomplete();
            var recipients = Generator(function (Yield) {
                CHAT.RoomUsers.pingableUsersIncludeIncomplete().forEach(function (user) {
                    var realName = user.name;
                    var normalized = allNonAscii ? realName : normalizeUserName(realName);
                    var semiNormalized = realName.toLowerCase().replace(/\s/g, "");
                    if (normalized.match(re) && shouldShowUser(user.id)) {
                        Yield({name: realName, mention: realName.replace(/\s/g, ""), normalized: normalized, id: user.id, sort: 0, status: user.status, group: semiNormalized });
                    } else if (user.advancedCompletionMatch && word.length) {
                        var match = user.advancedCompletionMatch(word, normalizedWord);
                        if (match && shouldShowUser(user.id)) {
                            Yield({ name: realName, mention: realName.replace(/\s/g, ""), normalized: normalized, id: user.id, status: user.status, sort: 1, group: semiNormalized});
                        }
                    }
                });
            }).sortBy("sort").groupBy("group"); // lyfe.js guarantees sort stability, so this will only separate normal and advanced matches, but keep "last talked" order otherwise
            return makeRecipients(recipients.toArray(), word);

        }
    };

    var superpingGetter;
    var superPingMode = {
        completionFor: function (jElem) {
            return jElem.data("mention-text");
        },
        getCompletionItems: function (word) {
            $("#superping-error").text("");
            if (word.length < 2) // only "@"
                return $([]);
            superpingGetter = superpingGetter || GoodGetter(60*1000, 400);
            $("#tabcomplete-container").addClass("superping-searching");
            return result = superpingGetter("/users/superping-search?q=" + encodeURIComponent(word.substr(1)))
                .pipe(function (result) {
                    $("#tabcomplete-container").removeClass("superping-searching");
                    if (result.error) {
                        $("#superping-error").text(result.error);
                        return $([]);
                    }
                    else {
                        var recipients = Generator(result.users).groupBy("normalized").toArray(); // normalized is = id for superpings, so these groups will always only have one element
                        if (!recipients.length)
                            $("#superping-error").text("No user found.");
                        return makeRecipients(recipients, word, true);
                    }
                });
        },
        start: function () { $("#tabcomplete-container").addClass("super-ping"); },
        stop: function () {
            $("#tabcomplete-container").removeClass("super-ping");
            if (superpingGetter)
                superpingGetter.cancel();
        }
    }

    function modeInfo(word) {
        if (CHAT.user.canSuperPing() && word.charAt(0) === "@") {
            return {
                word: word,
                mode: superPingMode
            };
        }
        var match = word.match(/^(?:[^@\s!?();:+]+|$)/);
        if (!match)
            return null;
        return {
            word: match[0],
            mode: normalMentionMode
        };
    }

    // getModeInfo is a function that receives the @-word as an argument that the cursor
    // is currently on, not including the first @. It returns either null, or an object with the following properties:
    // - mode: the tabcompletion mode that handles this word; see below
    // - word: the actual part of the word that this mode is handling; this must be
    //   a substring of the argument starting at the beginning, but it can be shortened
    //   (to remove trailing punctuation etc.)
    //
    // A tabcompletion mode is an object with the following properties:
    // - start (optional): a function
    // - stop (optional): a function
    // - getCompletionItems: A function that takes a word and returns a list of <li/> items that should be
    //   presented to the user as autocompletion choices. Can also return a promise that will be resolved
    //   with such a list instead.

    function GenericTabCompleter(getModeInfo) {

        var jTabcomplete = $("#tabcomplete");
        var jContainer = $("#tabcomplete-container");
        var jInput = $("#input");

        var STATE = {
            mode: null,
            atPos: 0,            // the position of the currently active @-word inside the input
            cursorPos: 0,        // the position of the cursor inside the input
            word: "",            // the currently active @-word, excluding the initial @
            originalWord: null,  // null if STATE.word is as typed by the user; if non-null, then STATE.word was auto-completed, and STATE.originalWord is what the user typed
            completionWord: null // the word, as typed, for which the current autocompletion list was built
        }

        function onModeChange() {
            jTabcomplete.empty();
            STATE.completionWord = null;
        }

        function setCurrentMode(mode) {
            var current = STATE.mode;
            if (mode === current)
                return;
            if (current && current.stop)
                current.stop();
            STATE.mode = mode;
            if (mode && mode.start)
                mode.start();
            STATE.originalWord = null;
            onModeChange();
        }

        var currentCompletionPromise;
        function ensureCompletionList() {
            var currentWord = STATE.originalWord || STATE.word;
            if (STATE.completionWord === currentWord)
                return;
            STATE.completionWord = currentWord;
            currentCompletionPromise = null;
            var result = STATE.mode.getCompletionItems(currentWord);
            jTabcomplete.empty();
            if (typeof result.done === "function") {
                currentCompletionPromise = result;
                result.done(function (items) {
                    if (currentCompletionPromise === result) {
                        jTabcomplete.append(items);
                        currentCompletionPromise = null;
                    }
                }).fail(function () {
                    if (currentCompletionPromise === result) {
                        currentCompletionPromise = null;
                    }
                });
            } else {
                jTabcomplete.append(result);
            }
        }

        function updateCurrentState(afterAutocomplete) {

            var pos = jInput.caret().start;
            if (pos !== jInput.caret().end) { // if there's a selection, no tab completion
                setCurrentMode(null);
                return;
            }
            var val = jInput.val();
            if (val.indexOf("@") === -1) {
                setCurrentMode(null);
                return;
            }
            var toWordEnd = val.substring(pos).search(/[\s]/);
            if (toWordEnd == -1)
                toWordEnd = val.length - pos;

            // the position in val of the first character that no longer
            // belongs to the word, or it's val.length if the word goes to the end of the string
            var firstNonWordCharPos = pos + toWordEnd;

            var atPos = val.lastIndexOf(" @", firstNonWordCharPos - 1);
            if (atPos === -1)
                atPos = val.indexOf("@");
            else
                atPos++;
            if (atPos === -1) {
                setCurrentMode(null);
                return;
            }
            var modeInfo = null;
            var word;
            while (true) {
                word = val.substring(atPos + 1, pos + toWordEnd);
                if (word.indexOf(" ") === -1) {
                    modeInfo = getModeInfo(word);
                    if (modeInfo && atPos + modeInfo.word.length + 1 >= pos)
                        break;
                }

                atPos = val.indexOf("@", atPos + 1);
                if (atPos === -1 || atPos > pos)
                    break;
            }

            if (!modeInfo) {
                setCurrentMode(null);
                return;
            }
            firstNonWordCharPos += modeInfo.word.length - word.length;

            // the cursor is actually outside the word part that the mode
            // considers relevant
            if (pos > firstNonWordCharPos) {
                setCurrentMode(null);
                return;
            }

            setCurrentMode(modeInfo.mode);

            if (!afterAutocomplete) {
                if (modeInfo.word !== STATE.word) {
                    STATE.originalWord = null;
                }
            }

            STATE.atPos = atPos;
            STATE.cursorPos = pos;
            STATE.word = modeInfo.word;
        }

        function choose(jElem) {
            jTabcomplete.find("> li:not(.too-many)").filter(".exact-match").removeClass("exact-match");
            if (!jElem) {
                if (typeof STATE.originalWord === "string") {
                    replaceInInput(STATE.originalWord);
                }
                return;
            }
            var replaced = STATE.mode.completionFor(jElem);
            STATE.originalWord = STATE.originalWord || STATE.word;
            jElem.addClass("chosen");
            replaceInInput(replaced);
            updateCurrentState(true);
        }

        function replaceInInput(s) {
            updateCurrentState();
            if (STATE.word === s) {// we're not changing anything, so in particular, there's no need to move the cursor
                jInput.focus(); // but still make sure the cursor is in the input box (in case a click has pulled the focus from it)
                return;
            }
            var oldval = jInput.val();
            jInput.val(oldval.substring(0, STATE.atPos + 1) + s + oldval.substring(STATE.atPos + STATE.word.length + 1));
            var newpos = STATE.atPos + s.length + 1;
            jInput.caret(newpos, newpos);
        }

        function setEventHandlers() {
            jInput.on("keydown", function (evt) {
                var choices;
                switch (evt.which) {
                    case 9: // tab
                        choices = jTabcomplete.find("> li:not(.too-many)");
                        if (!choices.length)
                            return;
                        var oldchoice = choices.filter(".chosen").removeClass("chosen");
                        var newchoice = evt.shiftKey ? oldchoice.prev() : oldchoice.next().not(".too-many");
                        if (!newchoice.length) // this covers both cases where a) nothing was chosen yet or b) the *last* one was chosen
                            newchoice = evt.shiftKey ? choices.last() : choices.first();
                        choose(newchoice);
                        break;
                    case 27: // ESC
                        if (STATE.mode)
                            evt.stopImmediatePropagation(); // to prevent ESC from clearing the input box
                        choices = jTabcomplete.find("> li:not(.too-many)");
                        if (!choices.length)
                            return;
                        choices.filter(".chosen").removeClass("chosen");
                        choose(null);
                        break;
                    default:
                        return;
                }
                evt.preventDefault();
            });
            function handleChange() {
                updateCurrentState();
                if (!STATE.mode)
                    return;
                ensureCompletionList();
            }
            jInput.on("keyup click paste change", function (evt) {
                if (evt.which) {
                    switch (evt.which) {
                        case 9: // tabs get handled above
                        case 16: // shift
                        case 17: // ctrl
                        case 18: // alt
                        case 220: // caps
                            return;
                    }
                }
                if (evt.type === "paste")
                    setTimeout(handleChange, 0);
                else
                    handleChange();
            });
            jTabcomplete.on("click", "li:not(.too-many)", function () {
                $(this).parent().find("li.chosen").removeClass("chosen");
                choose($(this));
            })

        }

        setEventHandlers();
    }

})();

// End PartialJS/tabcompleter.js

// Begin PartialJS/test.js

/* test.js */
;
function runTests() {
    test_functions = {

        chatContainsOnlyMonologuesAndSystemMessages: function () {
            var res = true;
            $("#chat").children().each(function () {
                if (!($(this).hasClass("monologue") || $(this).hasClass("system-message-container"))) {
                    $(this).addClass("failed-test");
                    res = false;
                }
            });
            return res;
        },

        userContainerDataAndClassMatch: function () {
            var res = true;
            $(".user-container").each(function () {
                if (!$(this).hasClass("user-" + $(this).data("user"))) {
                    $(this).addClass("failed-test");
                    res = false;
                }
            });
            return res;
        },

        eachCollapsibleHasOneMoreLink: function () {
            var res = true;
            $(".collapsible").each(function () {
                if ($(this).closest(".sidebar-widget").find(".more").length != 1) {
                    $(this).addClass("failed-test");
                    res = false;
                }
            });
            return res;
        },

        eachMessageHasIdAndTime: function () {
            var res = true;
            $(".message").each(function () {
                if (!(/^message-\d+$/.test($(this).attr("id")) && $(this).info("time") != undefined)) {
                    $(this).addClass("failed-test");
                    res = false;
                }
            });
            return res;
        },

        monotoneMessageTimeAndId: function () {
            var res = true;
            var lasttime = 0, lastid = 0;
            $(".message").not(".pending").each(function () {
                var thistime = $(this).info("time");
                var thisid = $(this).attr("id").replace("message-", "");
                if (1*thistime < 1*lasttime || 1*thisid <= 1*lastid) {
                    $(this).addClass("failed-test");
                    res = false;
                }
                lasttime = thistime;
                lastid = thisid;
            });
            return res;
        },

        enoughTimeStamps: function () {
            var res = true;
            var lasttime = 0;
            $(".message").not(".pending").each(function () {
                var thistime = $(this).info("time");
                if (thistime - lasttime > 600 && $(this).closest(".monologue").find(".timestamp").length != 1) {
                    $(this).addClass("failed-test");
                    res = false;
                }
                lasttime = thistime;
            });
            return res;
        }

    }

    $.each(test_functions, function (name, func) {
        debugMessage(name + ": " + (func() ? "success" : "failure"));
    });

}

function findCacheLeaks() {
    var leaks = total = handlers = 0;
    for (var key in $.cache) {
        total++;
        if ("events" in $.cache[key])
            handlers++;
        var jObj = $("[" + $.expando + "=" + key + "]");
        if (jObj.length == 0 && key != document[$.expando] && $.cache[key] != $(window).data()) { // document and window aren't selected with $.expando=...s
            leaks++;
            console.log(key);
            console.log($.cache[key]);
        }
    }
    debugMessage(leaks + " out of " + total + " cache entries are for elements that aren't in the dom, " + handlers + " have event handlers");
}

// End PartialJS/test.js

// Begin PartialJS/transcript.js

/* transcript.js */
;
function initTranscript(currentIsOwner, currentUserId, currentIsMod, currentIsRegistered, roomId, currentCanTalk) {
    var notify = Notifier().notify;

    var localStorageAvailable = false;
    try {
        localStorage.getItem("test");
        localStorageAvailable = true;
    } catch (e) {}

    if (window.location.hash && window.location.hash.length > 1) {
        $(window.location.hash).addClass("highlight");
    } else {
        var tgt = $(".highlight");
        if (tgt.length > 0) $.scrollTo(tgt, { offset: -100 });
    }
    $.preload('.message img.user-image', { placeholder: IMAGE('ajax-loader.gif'), notFound: IMAGE('ImageNotFound.png') });
    $(".action-link").click(transcriptMenu).find(".img").addClass("menu");

    $(document).on("mouseenter", ".message:has(a.reply-info)", function (evt) {
        var parentId = $(this).find("a.reply-info").attr("href").replace(/^.*#/, "");
        $("#message-" + parentId).addClass("reply-parent");
    }).on("mouseleave", ".message:has(a.reply-info)", function (evt) {
        var parentId = $(this).find("a.reply-info").attr("href").replace(/^.*#/, "");
        $("#message-" + parentId).removeClass("reply-parent");
    });

    $(".message > .content").each(function() {
        var html = $(this).html();
        var newhtml = handleQuoteMessage(html);
        if (html !== newhtml)
            $(this).html(newhtml);
    })

    // TODO: This is almost identical to parts of newMessage() in chat.js; should be easy enough to factor out
    $(".message > .content > .partial").each(function () {
        var that = $(this);
        var showall = $("<a/>").addClass("more-data").text("(see full text)").attr("href", "/messages/" + roomId + "/" + that.closest(".message").attr("id").replace("message-", ""));
        showall.click(function (evt) {
            if (evt.button != 0 || evt.ctrlKey)
                return; // only AJAX the text in on left-click
            var loading = $("<span/>").html("loading&hellip;");

            // we hide $(this) here and only remove it in the AJAX callback, because when the click event bubbles up
            // to the document, a selector test in a .live() handler throws an exception if the element has no parent anymore
            loading.insertAfter($(this).hide());

            $.ajax({
                type: "GET",
                url: $(this).attr("href"),
                success: function (data) {
                    that.removeClass("partial").addClass("full");
                    if (!that.is("pre")) {
                        var html = "<div class='full'>" + data.toString().replace(/^:\d+ /, "").replace(/\r\n?|\n/g, " <br> ") + "</div>";
                        that.replaceWith(handleQuoteMessage(html));
                    } else {
                        that.html(data.replace(/^    /mg, ""));
                    }
                    loading.add(showall).remove();
                }
            });
            evt.preventDefault();
        });
        that.closest(".content").append(" ", showall);
    });


    $(".signature .username a").each(function () { $(this).attr("title", $(this).text()); }); // make link text the title of the link (in case name is heavily truncated)
    initSearchBox();
    var currentUsers = $("#sidebar .room-mini .room-current-user-count");
    if (currentUsers.length > 0) {
        var s = currentUsers.text();
        if (s && s.length > 0) {
            $("#sidebar #join-room").text("join " + (s == '1' ? "1 user" : (s + " users")) + " in this room now");
        }
    }

    function edit() {
        var message = $(this).closest(".message");
        var parent_class = message.attr("class").split(/\s+/).find(c => c.startsWith("pid-"));
        var parent_id = parent_class ? parent_class.replace("pid-", "") : null;
        var message_id = message.attr("id").replace("message-", "");
        var editing_div = $("<div/>").insertAfter(message.find(".content"));
        var loader = $("<img/>").attr("src", IMAGE("ajax-loader.gif")).appendTo(editing_div);
        $.get("/message/" + message_id + "?plain=true", function (source) {
            loader.hide();
            var input = $("<textarea/>").css({ width: "80%", height: 50, "float" /* needs to be in quotes, otherwise the minifier starts vomiting */: "left" }).val(source).appendTo(editing_div);
            $("<button/>").addClass("button").css({ "float": "left", marginLeft: 5 }).text("save").appendTo(editing_div).click(function () {
                var newtext = input.val();
                loader.show();
                $.ajax({
                    type: "POST",
                    url: "/messages/" + message_id,
                    data: fkey({ text: newtext, parentId: parent_id }),
                    success: function () {
                        var url = PERMALINK(message_id);
                        if (location.href.search(url) >= 0)
                            window.location.reload(true);
                        else
                            window.location.href = url;
                    },
                    error: function () { notify("Editing the message has failed."); loader.hide(); }
                });
            });
            $("<button/>").addClass("button").css({ "float": "left", marginLeft: 5 }).text("cancel").appendTo(editing_div).click(function () {
                editing_div.remove();
            });
            $("<div/>").addClass("clear-both").appendTo(editing_div);
        });
    }

    function replyToMessage() {
        var message = $(this).closest(".message");
        var replyParentId = message.attr("id").replace("message-", "");
        var replyParentText = message.find(".content").text();
        var replyParentUsername = $(this).closest(".monologue").find(".username a:first").attr('title');

        var savedObj = { replyParentId, replyParentText, replyParentUsername };

        try {
            localStorage.setItem(`chat:saved:${roomId}`, JSON.stringify(savedObj));
        } catch (ex) {
            alert("Your browser needs to support localStorage in order to reply from the transcript.");
            return;
        }
        var joinUrl = $("#join-room").attr("href");
        location.href = joinUrl;
    }

    function transcriptMenu(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        var message = $(this).closest(".message");
        var isAdmin = message.closest("#admin-flags");
        isAdmin = isAdmin != null && isAdmin.length > 0;
        var menu = popUp(evt.pageX, evt.pageY, message, isAdmin);
        var verb;
        var verbs = [];
        var info = $("<div/>").appendTo(menu);
        var id = message.attr("id").replace("message-", "");
        info.html("<a href=\"" + PERMALINK(id) + "\">permalink</a> | <a href=\"/messages/" + id + "/history\">history</a>");
        if (message.find(".deleted").length) {
            if (currentIsMod) {
                undelete_button = $("<span/>").addClass("undelete").html("undelete").click(undel).click(menu.close).attr("title", "click to undelete");
                info.append(" | ").append(undelete_button);
                info.append("<br/>");
            }
            return;
        }

        info.append("<br/>");
        var isMine = $(this).closest(".monologue").hasClass("mine");

        if (currentCanTalk && localStorageAvailable && !isMine) {
            $("<span/>").addClass("reply").html("<span class=\"newreply\"> </span> reply to this message")
                .attr("title", "enter the room and reply to this message").appendTo(menu)
                .click(replyToMessage);
            menu.append("<br/>");
        }
        if (message.hasClass("user-star")) {
            verb = "unstar";
            verbs.push("starred");
        } else
            verb = "star";
        if (currentIsRegistered && !isMine) {
            $("<span/>").addClass("star").html("<span class=\"sprite sprite-icon-star\"> </span>" + verb + " as interesting").click(toggleStar).click(menu.close).attr("title", "Add a star to indicate an interesting message, for example to display in the room's highlights").appendTo(menu);
        }

        if (message.hasClass("user-star")) {
            verb = "unpin";
            verbs.push("pinned");
        } else
            verb = "pin";
        if (currentIsOwner) {
            menu.append("<br/>");
            $("<span/>").addClass("star").html("<span class=\"sprite sprite-ownerstar-on\"> </span>" + verb + " this message").click(togglePin).click(menu.close).attr("title", "Pinning is like adding a star, but pinned items takes priority; this option is only available to the room owner.").appendTo(menu);
        }

        var hasFlagged = message.hasClass("user-flag");
        if (hasFlagged)
            verbs.push("flagged");
        if (verbs.length > 0)
            info.html(info.html() + "You have " + verbs.join(" and ") + " this message.<br/>");

        if (!hasFlagged && (currentIsRegistered && !isMine)) {
            menu.append("<br/>");
            $("<span/>").addClass("flag").html("<span class=\"sprite sprite-icon-flag\"> </span> flag as spam/offensive").click(doFlag).click(menu.close).attr("title", "Flagging a message helps bring inappropriate content to the attention of moderators and other users, for example spam or abusive messages.").appendTo(menu);
        }
        if (currentIsMod) {
            menu.append("<br/><br/>");
            $("<span/>").addClass("edit").text("edit").click(edit).click(menu.close).attr("title", "click to edit").appendTo(menu);
            if (message.find(".flag-indicator").length > 0) {
                menu.append(" | ");
                $("<span/>").addClass("edit").text("cancel flags").click(cancelFlags).click(menu.close).attr("title", "Cancel the flags against this message").appendTo(menu);
            }
            menu.append(" | ");
            $("<span/>").addClass("edit").text("delete").click(deletePost).click(menu.close).attr("title", "Delete this message").appendTo(menu);
        }
        menu.append("<br/><br><small>(changes will not show until you reload)</small>");
    }
    function cancelFlags() {
        var msg = $(this).closest(".message");
        var id = msg.attr("id").replace("message-", "");
        if (confirm("Cancel the flags for this message?")) {
            $.post("/messages/" + id + "/unflag", fkey(), function () {
                msg.find(".flag-indicator").remove();
            });
        }
    }
    function deletePost() {
        var msg = $(this).closest(".message");
        var id = msg.attr("id").replace("message-", "");
        if (confirm("Delete this message?")) {
            $.post("/messages/" + id + "/delete", fkey(), function () {
                var parent = msg.closest(".monologue-row");
                if(!parent || !parent.length) parent = msg.closest(".monologue");
                msg.remove();
                if (parent.find(".message").length == 0) parent.remove();
            });
        }
    }
    function togglePin() {
        var msg = $(this).closest(".message");
        messageActionById(msg.attr("id").replace("message-", ""), msg.hasClass("owner-star") ? "unowner-star" : "owner-star", null, function () {
            if (msg.hasClass("owner-star")) msg.removeClass("owner-star"); else msg.addClass("owner-star");
        }, notify);
    }
    function toggleStar() {
        var msg = $(this).closest(".message");
        messageActionById(msg.attr("id").replace("message-", ""), "star", null, function () {
            if (msg.hasClass("user-star")) msg.removeClass("user-star"); else msg.addClass("user-star");
        }, notify);
    }
    function doFlag() {
        if (!confirmFlag(currentIsMod))
            return;
        var msg = $(this).closest(".message");
        messageActionById(msg.attr("id").replace("message-", ""), "flag", null, function () {
            msg.addClass("user-flag");
        }, notify);
    }
    function undel() {
        var msg = $(this).closest(".message");
        if (confirm("Undelete this message? (The message will show after a reload)")) {
            messageActionById(msg.attr("id").replace("message-", ""), "undelete", null, function () {
                if (data == "ok") {
                    return true;
                }
                notify(data);
                return false;
            }, notify);
        }
    }

    // TODO: The below is mostly copy&past from the same function(s) in chat.js; there should be
    // a good way to factor out
    var singleSelectCallback;
    function toggleSelectMode(setOn, callback) {
        var main = $("#main");
        var isOn = main.hasClass("select-mode");
        if (singleSelectCallback)
            singleSelectCallback(null);
        if (setOn && callback)
            singleSelectCallback = callback;
        else
            singleSelectCallback = null;
        if (setOn == undefined)
            setOn = !isOn;
        $("#transcript div.message.selected").removeClass("selected");
        if (setOn || !isOn) {
            main.addClass("select-mode");
        }
        else if (!setOn || isOn) {
            main.removeClass("select-mode");
        }
    }
    $(document).on("click", ".message .content", function (evt) {
        if (!$("#main").hasClass("select-mode")) return;
        evt.preventDefault();
        evt.stopImmediatePropagation();
        var el = $(this).closest(".message");
        if (singleSelectCallback) {
            singleSelectCallback(el);
        }
    });
    // note that this button is only on the page for non-anon users, so nothing will happen
    // for not-logged-in
    if ($("#bookmark-button").length) {
        var conversationSelector = ConversationSelector(roomId, toggleSelectMode, notify, $("#transcript"), /*autoShowIfDataPresent=*/ true);
        $("#bookmark-button").click(function (evt) {
            evt.preventDefault();
            conversationSelector.Dialog();
        });
    }
    if (currentIsMod)
    {
        $('.messages .message .flash.flag-indicator').css('cursor', 'pointer').click(function (evt) {
            evt.preventDefault();
            evt.stopImmediatePropagation();
            var msg = $(this).closest('.message');
            var id = msg.attr('id').replace('message-', '');
            if (id && id.length) {
                var menu = popUp(evt.pageX, evt.pageY, msg, false).addClass("flags-popup")
                    .append("<h3>Loading flags <img class='ajax-loader' src='" + IMAGE("progress-dots.gif") + "' /></h3>");
                var url = '/admin/flagged/' + id;
                menu.css('width', '400px').load(url);

            }
            return false;
        });
    }

    return { notify: notify };
}

// End PartialJS/transcript.js

// Begin PartialJS/mobile.js

/// <reference path="~/Views/Room/SingleRoom.mobile.cshtml" />
/* mobile.js */
;
function Mobile(ROOM_INFO) {
    var all_menus = $("#menu-container > div.mobile-menu");

    function page(jList, pagenum) {
        var pager = jList.parent().find(".menupager");
        if (pagenum == undefined)
            pagenum = (parseInt(pager.find(".menupager-page").text()) - 1) || 0;
        var all = jList.find("li");
        var pagecount = Math.ceil(all.length / 5);
        pagenum = Math.max(0, Math.min(pagecount - 1, pagenum));
        all.hide();
        all.slice(pagenum * 5, (pagenum + 1) * 5).show();
        pager.setVisible(pagecount > 1);
        pager.find(".menupager-prev").setVisible(pagenum > 0);
        pager.find(".menupager-next").setVisible(pagenum < pagecount - 1);
        pager.find(".menupager-pagecount").text(pagecount);
        pager.find(".menupager-page").text(pagenum + 1);
    }

    $(document).on("click", ".gotomenu", function (evt) {
        var targetname = $(this).attr("id").replace(/(?:\d+-)?goto/, "single");
        if ($(this).attr("id") == "gotomenu-main" && all_menus.filter(":visible").length > 0)
            targetname = "none";
        var target = $("#" + targetname);

        all_menus.not(target).slideUp(200);
        switch (targetname) {
            case "singlemenu-people":
                page($("#present-users"), 0);
                break;
            case "singlemenu-otherrooms":
                page($("#my-rooms"), 0);
                $("#my-rooms li").each(function () {
                    var li = $(this);
                    li.find(".time-since-activity").text(ToRelativeTimeMini(li.find(".room-info > .last-message > .time").text()));
                });
                break;
        }


        target.slideDown(200);
        if (target.length > 0) // i.e. there's actually a menu to be displayed
            $("#input").attr("disabled", "disabled"); // for some reason, in the android browser the input box reacts to a click even though the menu lies above it
        else
            $("#input").prop("disabled", false);

        $(this).removeClass("mention");
    });

    $(document).on("click", ".menupager-prev, .menupager-next", function () {
        var pager = $(this).closest(".menupager");
        var list = pager.parent().find("ul");
        var current_page = parseInt(pager.find(".menupager-page").text()) - 1;
        page(list, $(this).hasClass("menupager-prev") ? current_page - 1 : current_page + 1);
    });

    function leaveThisClicked() {
        if (sidebarObject.isLeaving || confirm("Do you want to leave this room?"))
            $.post($(this).attr("href"), fkey({ quiet: true }), function () { window.location = "/"; });
        return false;
    }

    function leaveOtherRoomClicked(evt) {
        if ($(this).hasClass("quickleave")) {
            var li = $(this).closest("li");
            var roomname = li.find("h3").text();
            // if the room name was truncated for display, the title holds the full name
            if (!confirm("Do you want to leave " + roomname + "?"))
                return;
        }
        var room_id = li.attr("id").replace("room-", "");
        $.post("/chats/leave/" + room_id, fkey({ quiet: true }));
        leaveOtherRoom(room_id);
    }
    function otherRoomActivity(roomid, roomname, username, message_text, time, message_id) {
        var room = $("#room-" + roomid);
        if (room.length == 0) {
            room = $("<li/>").attr("id", "room-" + roomid);
            roomname = roomname || "(unknown)";
            var row = $("<tr/>").appendTo($("<table/>").appendTo(room));
            var buttons = $("<td/>").appendTo(row);
            $("<a class='button quickswitch'/>").appendTo(buttons).attr("href", "/rooms/" + roomid + "/" + urlFriendly(roomname)).text("go");
            $("<span class='quickleave button'/>").appendTo(buttons).text("leave");
            var infodiv = $("<div/>").append($("<h3/>").text(roomname)).appendTo($("<td/>").appendTo(row));
            infodiv.append($("<span/>").text(username ? username + ", " : "")).append($("<span/>").addClass("time-since-activity").text(ToRelativeTimeMini(time)));
            room.append(div("room-info").append(div("last-message").append(span("user-name"), ": ", span("text"), div("time data"))));
        }
        var last_message_id = room.data("message_id") || 0;
        if (message_id && message_id >= last_message_id) {// it's not an edit etc. to an older message
            if (arguments.length > 2) {
                room.find(".room-info > .last-message > .user-name").text(username);
                room.find(".room-info > .last-message > .text").text($("<span>" + (message_text || "") + "</span>").text());
                room.find(".room-info > .last-message > .time").text(time);
                room.find("div:first > span:first").text(username ? username + ", " : "");
                room.find(".time-since-activity").text(ToRelativeTimeMini(time));
            }
            room.data("message_id", message_id);
        }
        room.prependTo("#my-rooms");
        page($("#my-rooms"));
    }

    function lastOtherRoomMessageTime() {
        var timediv = $("#my-rooms > li:first .time.data");
        if (!timediv.length)
            return 1;
        return parseInt(timediv.text());
    }

    function otherRoomMention(roomid, message_id) {
        if (!$("#room-" + roomid).length)
            return false;
        $("#room-" + roomid + ", #gotomenu-otherrooms, #gotomenu-main").addClass("mention");
        return true;
    }

    function dismissOtherRoomMention(roomid, message_id) {
        $("#room-" + roomid).removeClass("mention");
        if ($("#my-rooms > li.mention").length == 0) {
            $("#gotomenu-otherrooms, #gotomenu-main").removeClass("mention");
        }
    }

    function leaveOtherRoom(roomid) {
        $("#room-" + roomid).remove();
        page($("#my-rooms"));
    }

    function updateStars() {
        $("#starred-posts ul").load("/chats/stars/" + ROOM_INFO.id + "?count=3");
    }

    $(document).on("click", "#my-rooms span.quickleave", leaveOtherRoomClicked);
    $("#leave").click(leaveThisClicked);
    $("#chat").on("click", ".timestamp", false);

    var uploadJqXHR;
    var resetUpload = function () {
        uploadJqXHR = null;
        $("#upload-file-input").val("");
        $("#gotomenu-none").click();
    };
    $("#upload-file-input").change(function (e) {
        var self = $(this), file, formData;
        if (self.val() !== "") {
            file = self[0].files[0];
            formData = new FormData();
            formData.append("fkey", $("#fkey").val());
            formData.append("newFile", file);
            uploadJqXHR = $.ajax({
                url: "/files/upload/" + ROOM_INFO.id,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                timeout: 0,
                // See https://stackoverflow.com/a/8758614/13 on using the
                // xhr option to provide file upload progress.
                xhr: function () {
                    var xhr = $.ajaxSettings.xhr();
                    if (xhr.upload) {
                        $(xhr.upload).on("progress", function (e) {
                            var loaded, total;
                            if (e.originalEvent.lengthComputable) {
                                loaded = e.originalEvent.loaded;
                                total = e.originalEvent.total;
                                $("#upload-progress").prop("max", total).val(loaded);
                                $("#upload-progress-percent").text((loaded / total * 100).toFixed());
                            }
                        }).on("loadstart", function (e) {
                            $("#upload-progress").removeAttr("value");
                            $("#gotomenu-progress").click();
                        });
                    }
                    return xhr;
                }
            }).done(resetUpload).fail(function (jqXHR, status, error) {
                if (status !== "abort")
                    alert("File " + file.name + " failed to upload: " + error);
                resetUpload();
            });
        }
    });
    $("#upload-file").click(function (e) {
        $("#upload-file-input").click();
    });
    $("#cancel-upload").click(function (e) {
        if (uploadJqXHR) {
            uploadJqXHR.abort();
            uploadJqXHR = null;
        }
    });

    var noop = function () { };

    Mobile.page = page;

    var sidebarObject = {
        relayout: noop,
        leaveOtherRoom: leaveOtherRoom,
        otherRoomActivity: otherRoomActivity,
        otherRoomMention: otherRoomMention,
        updateStars: updateStars,
        updateFiles: noop,
        updateRoomMeta: noop,
        updateAdminCounters: noop,
        dismissOtherRoomMention: dismissOtherRoomMention,
        lastOtherRoomMessageTime: lastOtherRoomMessageTime
    };
    return sidebarObject;
}

function MobileReplyQueue(setOnNewMessage) {
    var queue = [];
    uniquePush = function (x) {
        if ($.inArray(x, queue) < 0)
            queue.push(x);
    };

    function add(message_id) {
        uniquePush(message_id);
        $("#message-" + message_id).addClass("new-reply");
    }

    function len() {
        return queue.length;
    }

    // no "clear all" on mobile
    function clear(noBroadcast) { }

    setOnNewMessage(function (jMsg, id) {
        if ($.inArray(id, queue) >= 0)
            jMsg.addClass("new-reply");
    });

    $(document).on("click", ".message.new-reply", function () {
        $(this).removeClass("new-reply");
        $.post("/messages/ack", fkey({ id: $(this).messageId() }));
    });

    return {
        add: add,
        len: len,
        clear: clear
    };
}


// End PartialJS/mobile.js

// Begin PartialJS/conversation.js

/* conversation.js */
;
// container is $("#chat") or $("#transcript")
function ConversationSelector(roomId, toggleSelectMode, notify, container, autoShowIfDataPresent) {
    var texts = [],
        ids = [];

    var updateDialog;
    var dialog;
    var valueKey = "chat:conversationSelection";

    function trySaveValues() {
        try {
            window.localStorage.setItem(valueKey, stringify({
                roomId: roomId,
                texts: texts,
                ids: ids,
                time: (new Date()).getTime()
            }));
        } catch (ex) {
            return false;
        };
        return true;
    }

    function tryLoadValues() {
        var data;
        try {
            data = $.parseJSON(window.localStorage.getItem(valueKey));
        } catch (ex) {
            return false;
        }
        if (data && data.roomId == roomId) {
            // unless the previous selection was made on this very page, only remember
            // it for ten minutes
            if ((new Date()).getTime() - data.time > 600000)
                return false;
            texts = data.texts;
            ids = data.ids;
            return true;
        }
        return false;
    }

    function clearValues() {
        texts = [];
        ids = [];
        try {
            window.localStorage.setItem(valueKey, null)
        } catch (ex) { };
    }

    function closeDialog() {
        dialog.fadeOut(200, function () { $(this).remove(); });
    }

    // highlight the selected messages
    function updateSelection() {
        if (ids.length < 2) {
            container.find("div.message.selected").removeClass("selected");
            if (ids.length == 1)
                $("#message-" + ids[0]).addClass("selected");
        }
        else {
            container.find("div.message").each(function () {
                var msg = $(this);
                var id = msg.attr("id").replace("message-", "");
                if (id < ids[0] || id > ids[1])
                    msg.removeClass("selected");
                else
                    msg.addClass("selected");
            });
        }
    }

    function select(jEl) {
        if (jEl == null) { // select mode was cancelled
            closeDialog();
            return;
        }

        if (jEl.find(".deleted").length)
            return;
        var id = parseInt(jEl.attr("id").replace("message-", ""));
        var text = jEl.find(".content").text();

        if (ids.length < 2) {
            ids.push(id);
            texts.push(text);
        } else {
            var replace = (Math.abs(ids[0] - id) < Math.abs (ids[1] - id)) ? 0 : 1; // replace the one that is closer to the newly chosen message;
            ids[replace] = id;
            texts[replace] = text;
        }

        if (ids[0] == ids[1]) {
            texts.pop();
            ids.pop();
        } else if (ids[0] > ids[1]) {
            // swap them
            ids.push(ids.shift())
            texts.push(texts.shift())
        }
        updateSelection();
        updateDialog();
        trySaveValues();
    }

    function Dialog() {
        toggleSelectMode(true, select);
        $(".popup:not(.mini-help)").fadeOut(200, function () { $(this).remove(); });
        dialog = $("<div id=\"conversation-sel\"/>").css({width: 300, right: 200, top: 200 }).appendTo("body");

        dialog.append("<h2>Bookmark a conversation</h2><p>A conversation is a chronological thread of chat messages that you can select, give a title to, and share.</p>" +
            "<p>Please <b>click the two messages</b> that define the <b>start</b> and the <b>end</b> of the conversation.</p>");
        first = $("<div/>").css({ fontWeight: "bold", marginLeft: 10 });
        last = $("<div/>").css({ fontWeight: "bold", marginLeft: 10 });
        dialog.append($("<p>First message:</p>").append(first).hide(),
            $("<p>Last message:</p>").append(last).hide());
        var titleChooser = $("<div><p>Give the conversation a title:</p><input type=\"text\" /></div>").hide().appendTo(dialog);
        var sendButton = $("<button class=\"button\"/>").text("bookmark").hide().appendTo(titleChooser);
        var titlebox = titleChooser.find("input").css("width", 250);
        var message = $("<div/>").appendTo(dialog).hide();
        var ajaxLoader = $("<img/>").attr("src", IMAGE("ajax-loader.gif")).hide().appendTo(dialog);
        var cancelButtom = $("<button class=\"button\"/>").text("cancel").appendTo(dialog).click(function () {
            clearValues();
            toggleSelectMode(false); // this will also cause the dialog to close, because it calls select() with a null argument
        });
        $("<span>&nbsp;</span>").appendTo(dialog);
        var clearButtom = $("<button class=\"button\"/>").text("clear").css("display", "none").appendTo(dialog).click(function () {
            clearValues();
            updateDialog();
            updateSelection();
        });
        var closeButton = $("<div class='btn-close'>X</div>").prependTo(dialog).click(function () {
            toggleSelectMode(false);
        });

        function send() {
            ajaxLoader.show();
            message.hide();
            $.ajax({
                type: 'POST',
                url: "/conversation/new",
                data: fkey({ roomId: roomId, firstMessageId: ids[0], lastMessageId: ids[1], title: titlebox.val() }),
                success: function (response) {
                    if (response.ok) {
                        notify(response.message);
                        clearValues();
                        toggleSelectMode(false);
                    } else {
                        message.text(response).css("color", "red");
                    }
                    ajaxLoader.hide();
                    message.show();
                },
                error: function(request, status, error) {
                    var text = GENERIC_ERROR;
                    if (request.responseText.length < 50) // make sure we don't dump "real" server errors here
                        text = request.responseText;
                    message.text(text).css("color", "red");
                    ajaxLoader.hide();
                    message.show();
                },
                dataType: "json"
            });
        }

        titlebox.bind("change keyup click", function (evt) {
            var thereIsText = $(this).val().length > 0;
            sendButton.setVisible(thereIsText);
            if (evt.type == "keyup" && evt.which == 13 && thereIsText)
                send();
        });

        sendButton.click(send);

        updateDialog = function () {
            if (texts[0]) {
                first.text(texts[0].substr(0, 50));
                first.closest("p").show();
            } else {
                first.text("");
                first.closest("p").hide();
            }
            if (texts[1]) {
                last.text(texts[1].substr(0, 50));
                last.closest("p").show();
            } else {
                last.text("");
                last.closest("p").hide();
            }

            titleChooser.setVisible(ids.length == 2);
            // using show/hide gives display: block in FF
            clearButtom.css("display", ids.length > 0 ? "inline" : "none");
        }

        tryLoadValues();
        updateSelection();
        updateDialog();

    }

    if (autoShowIfDataPresent && tryLoadValues())
        Dialog();

    return {
        Dialog: Dialog
    }
}

function ConversationViewer() {
    $("#participants li").click(function () {
        $(this).toggleClass("selected");
        var sel_count = $("#participants li.selected").length
        if (sel_count > 0 && sel_count < $("#participants li").length) {
            var hide = ["div.system-message-container"];
            var show = [];
            $("#participants li").each(function () {
                var sel = "div.monologue." + $(this).attr("id").replace("participating-", "");
                if ($(this).hasClass("selected"))
                    show.push(sel);
                else
                    hide.push(sel);
            });
            $(show.join(","), "#conversation").slideDown();
            $(hide.join(","), "#conversation").slideUp();
            $("#conversation .timestamp").fadeOut();
        } else { // nothing or everything is selected -- treat identically
            $("div.monologue, div.system-message-container", "#conversation").slideDown();
            $("#conversation .timestamp").fadeIn();
        }
    });
}

// End PartialJS/conversation.js

// Begin PartialJS/fileUpload.js


function initFileUpload() {
    var util = {};
    // Removes a listener callback from a DOM element which is fired on a specified
    // event.
    util.removeEvent = function (elem, event, listener) {
        if (elem.detachEvent) {
            // IE only.  The "on" is mandatory.
            elem.detachEvent("on" + event, listener);
        }
        else {
            // Other browsers.
            elem.removeEventListener(event, listener, false);
        }
    };
    // Adds a listener callback to a DOM element which is fired on a specified
    // event.
    util.addEvent = function (elem, event, listener) {
        if (elem.attachEvent) {
            // IE only.  The "on" is mandatory.
            elem.attachEvent("on" + event, listener);
        }
        else {
            // Other browsers.
            elem.addEventListener(event, listener, false);
        }
    };
    var doc = top.document;
    util.getPageSize = function () {

        var scrollWidth, scrollHeight;
        var innerWidth, innerHeight;

        // It's not very clear which blocks work with which browsers.
        if (self.innerHeight && self.scrollMaxY) {
            scrollWidth = doc.body.scrollWidth;
            scrollHeight = self.innerHeight + self.scrollMaxY;
        }
        else if (doc.body.scrollHeight > doc.body.offsetHeight) {
            scrollWidth = doc.body.scrollWidth;
            scrollHeight = doc.body.scrollHeight;
        }
        else {
            scrollWidth = doc.body.offsetWidth;
            scrollHeight = doc.body.offsetHeight;
        }

        if (self.innerHeight) {
            // Non-IE browser
            innerWidth = self.innerWidth;
            innerHeight = self.innerHeight;
        }
        else if (doc.documentElement && doc.documentElement.clientHeight) {
            // Some versions of IE (IE 6 w/ a DOCTYPE declaration)
            innerWidth = doc.documentElement.clientWidth;
            innerHeight = doc.documentElement.clientHeight;
        }
        else if (doc.body) {
            // Other versions of IE
            innerWidth = doc.body.clientWidth;
            innerHeight = doc.body.clientHeight;
        }

        var maxWidth = Math.max(scrollWidth, innerWidth);
        var maxHeight = Math.max(scrollHeight, innerHeight);
        return [maxWidth, maxHeight, innerWidth, innerHeight];
    };
    util.isIE_5or6 = /msie 6/.test(top.navigator.userAgent.toLowerCase()) || /msie 5/.test(top.navigator.userAgent.toLowerCase());
    util.isIE = /msie/.test(top.navigator.userAgent.toLowerCase());

    // Creates the background behind the hyperlink text entry box.
    // And download dialog
    // Most of this has been moved to CSS but the div creation and
    // browser-specific hacks remain here.
    util.createBackground = function () {

        var background = doc.createElement("div");
        background.className = "wmd-prompt-background";
        style = background.style;
        style.position = "fixed";
        style.top = "0";

        style.zIndex = "1000";

        if (util.isIE) {
            style.filter = "alpha(opacity=50)";
        }
        else {
            style.opacity = "0.5";
        }

        //var pageSize = util.getPageSize();
        // pageSize[1] + "px";

        if (util.isIE) {
            style.left = doc.documentElement.scrollLeft;
            style.width = doc.documentElement.clientWidth;
            style.height = doc.documentElement.clientHeight;
        }
        else {
            style.left = "0";
            style.width = "100%";
            style.height = '100%';
        }

        doc.body.appendChild(background);
        return background;
    };
    util.getHeight = function (elem) {
        return elem.offsetHeight || elem.scrollHeight;
    };
    util.getWidth = function (elem) {
        return elem.offsetWidth || elem.scrollWidth;
    };
    util.getPageSize = function () {

        var scrollWidth, scrollHeight;
        var innerWidth, innerHeight;

        // It's not very clear which blocks work with which browsers.
        if (self.innerHeight && self.scrollMaxY) {
            scrollWidth = doc.body.scrollWidth;
            scrollHeight = self.innerHeight + self.scrollMaxY;
        }
        else if (doc.body.scrollHeight > doc.body.offsetHeight) {
            scrollWidth = doc.body.scrollWidth;
            scrollHeight = doc.body.scrollHeight;
        }
        else {
            scrollWidth = doc.body.offsetWidth;
            scrollHeight = doc.body.offsetHeight;
        }

        if (self.innerHeight) {
            // Non-IE browser
            innerWidth = self.innerWidth;
            innerHeight = self.innerHeight;
        }
        else if (doc.documentElement && doc.documentElement.clientHeight) {
            // Some versions of IE (IE 6 w/ a DOCTYPE declaration)
            innerWidth = doc.documentElement.clientWidth;
            innerHeight = doc.documentElement.clientHeight;
        }
        else if (doc.body) {
            // Other versions of IE
            innerWidth = doc.body.clientWidth;
            innerHeight = doc.body.clientHeight;
        }

        var maxWidth = Math.max(scrollWidth, innerWidth);
        var maxHeight = Math.max(scrollHeight, innerHeight);
        return [maxWidth, maxHeight, innerWidth, innerHeight];
    };
    util.uploadDialog = function (callback) {
        var dialog;
        var background;
        var input;

        // workaround for an ugly Chrome bug that causes the iframe to re-submit the POST request
        // from the previous image upload as soon as the IFRAME is created (in other words, as soon
        // as you click the "upload..." button in the input area, the last image is uploaded again.
        // Giving the IFRAME a unique name & id stops this.
        //
        // Probably related to https://code.google.com/p/chromium/issues/detail?id=59576

        var id = "upload-iframe-" + new Date().getTime() + "-" + ((Math.random() * 100000) | 0);

        var checkEscape = function (key) {
            var code = (key.charCode || key.keyCode);
            if (code === 27) {
                close(null);
            }
        };


        dialog = $("<div style='top: 50%; left: 50%; display: block; padding: 10px; position: fixed; width:400px; z-index:1001' class='wmd-prompt-dialog'>\
<p>\
<b>Insert an image</b>\
</p>\
<p style='padding-top: 10px;'>\
<a href='#' class='wmd-mini-button selected' id='upload-image-button'>from my computer</a>\
<a href='#' class='wmd-mini-button' id='upload-url-button'>from the web</a>\
</p>\
<iframe id='" + id + "' style='display:none;' src='about:blank' name='" + id + "'/>\
<form action='/upload/image' method='post' enctype='multipart/form-data'>\
<div style='position: relative' id='upload-file-input'>\
  <input type='file' name='filename' id='filename-input' value='browse' style='border:0; font-size:18px; position:relative; text-align:right; -moz-opacity:0; filter:alpha(opacity: 0); opacity: 0; z-index: 2;'>\
  <div style='position: absolute; top:0px; left:0px; z-index: 1;'>\
    <input type='input' name='shadow-filename' value='' id='shadow-filename' style='width: 180px; margin-left:64px;'>\
    <input class='button' type='button' name='choose-file' id='choose-file' value='browse&hellip;' style='width: 7em; margin-left: 5px;'>\
  </div>\
</div>\
<div id='upload-url-input' style='display:none;'>\
    <input type='input' name='upload-url' value='' style='width: 250px;'>\
</div>\
<p id='upload-message' style='padding-top: 4px; margin:0; line-height: 16px;'>\
</p>\
<div class='ac_loading' id='image-upload-progress' style='background-color: transparent; display:none;'>Uploading&hellip;</div>\
<input class='button' type='submit' value='upload' style='width: 7em; margin: 10px;'>\
<input class='button' type='button' value='cancel' id='close-dialog-button' style='width: 7em; margin: 10px 10px 20px;'>\
</form>\
</div>");


        if (util.isIE_5or6) {
            dialog[0].style.position = "absolute";
            dialog[0].style.top = doc.documentElement.scrollTop + 200 + "px";
            dialog[0].style.left = "50%";
        }

        background = util.createBackground();

        var close = function (url) {
            util.removeEvent(doc.body, "keydown", checkEscape);
            dialog.remove();
            $(background).remove();
            if (callback) callback(url == undefined ? null : url);
            return false;
        };

        top.setTimeout(function () {
            $(doc.body).append(dialog);
            $("#close-dialog-button").click(function () { close(); });

            var uploadImage = $("#upload-image-button");
            var uploadUrl = $("#upload-url-button");

            var uploadUrlInput = $('#upload-url-input');
            var form = uploadUrlInput.parent();
            var uploadFileInput = $('#upload-file-input');

            uploadUrlInput.remove().show();

            var setUploadFileMessage = function () { $('#upload-message').text("click browse to choose an image from your computer"); };
            setUploadFileMessage();

            uploadImage.click(function () {
                uploadUrl.removeClass("selected");
                uploadImage.addClass("selected");
                setUploadFileMessage();
                uploadUrlInput.remove();
                form.prepend(uploadFileInput);
                bindFileName();
                return false;
            });

            uploadUrl.click(function () {
                uploadImage.removeClass("selected");
                uploadUrl.addClass("selected");
                $('#upload-message').text("paste the URL of your image above");
                form.prepend(uploadUrlInput);
                uploadFileInput.remove();
                return false;
            });

            var bindFileName = function () {
                // ie hack - though we should just change :active style instead
                $('#filename-input').click(function () { this.blur(); });

                $('#filename-input').change(function () {
                    $('#shadow-filename').val(this.value);
                });
            }
            bindFileName();

            var iframe = $('#' + id)[0];
            dialog.find("form").submit(function () {
                $('#upload-message').hide();
                $('#image-upload-progress').show();
                this.target = id;
                window.closeDialog = close;
                window.displayUploadError = function (error) {
                    $('#image-upload-progress').hide();
                    $('#upload-message').show().text(error);
                };
                return true;
            });

            dialog[0].style.marginTop = -(util.getHeight(dialog[0]) / 2) + "px";
            dialog[0].style.marginLeft = -(util.getWidth(dialog[0]) / 2) + "px";
            util.addEvent(doc.body, "keydown", checkEscape);
        }, 0);



    }
    return { showDialog: util.uploadDialog };
}

// End PartialJS/fileUpload.js

// End of file


