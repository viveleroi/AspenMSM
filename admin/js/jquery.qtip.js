/*!
 * jquery.qtip. The jQuery tooltip plugin
 *
 * Copyright (c) 2009 Craig Thompson
 * http://craigsworks.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Launch   : February 2009
 * Version  : TRUNK - NOT FOR USE IN PRODUCTION ENVIRONMENTS!!!!
 * Debugging: jquery.qtip.debug.js
 *
 * FOR STABLE VERSIONS VISIT: http://craigsworks.com/projects/qtip/download/
 */
(function($)
{
   // Implementation
   $.fn.qtip = function(options)
   {
      var i, j, id, interfaces, opts, obj, command, config, len, data, self;

      // Return API / Interfaces if requested
      if(typeof options == 'string')
      {
         options = options.toLowerCase();
         if(options == 'id' || options == 'api' || options == 'interfaces')
         {
            data = $(this).data('qtip');
            if(typeof data != 'object')
               $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.NO_TOOLTIP_PRESENT, false);
            else if(options == 'id')
               return $.fn.qtip.interfaces[ data.current ].id;
            else if(options == 'api')
               return $.fn.qtip.interfaces[ data.interfaces[data.current] ];
            else if(options == 'interfaces')
            {
               interfaces = {}; i = data.interfaces.length;
               while(i--) interfaces[ data.interfaces[i] ] = $.fn.qtip.interfaces[ data.interfaces[i] ];
               return interfaces;
            }
         }
      }

      // Validate provided options
      else
      {
         // Set null options object if no options are provided
         if(!options) options = {};

         // Sanitize option data
         if(typeof options.content != 'object' || (options.content.jquery && options.content.length > 0)) options.content = { text: options.content };
         if(typeof options.content.title != 'object') options.content.title = { text: options.content.title };
         if(typeof options.content.url != 'object') options.content.url = { path: options.content.url };
         if(options.content.data !== null) { options.content.url.data = options.content.data; delete options.content.data }
         if(options.content.method !== null) { options.content.url.method = options.content.method; delete options.content.method }
         if(typeof options.position != 'object') options.position = { corner: options.position };
         if(typeof options.position.corner != 'object') options.position.corner = { target: options.position.corner, tooltip: options.position.corner };
         if(typeof options.show != 'object') options.show = { when: options.show };
         if(typeof options.show.when != 'object') options.show.when = { event: options.show.when };
         if(typeof options.show.effect != 'object') options.show.effect = { type: options.show.effect };
         if(typeof options.show.effect.length == 'number') options.show.effect = { type: options.show.effect.type, duration: options.show.effect.length };
         if(typeof options.hide != 'object') options.hide = { when: options.hide };
         if(typeof options.hide.when != 'object') options.hide.when = { event: options.hide.when };
         if(typeof options.hide.effect != 'object') options.hide.effect = { type: options.hide.effect };
         if(typeof options.hide.effect.length == 'number') options.hide.effect = { type: options.hide.effect.type, duration: options.hide.effect.length };
         if(typeof options.style != 'object') options.style = { name: options.style };
         options.style = sanitizeStyle(options.style);

         // Build main options object if CSS isn't in use
         opts = $.extend(true, {}, $.fn.qtip.defaults, options);

         // Inherit all style properties into one syle object and include original options
         opts.style = buildStyle.call({ options: opts }, opts.style);
         opts.user = $.extend(true, {}, options);

         // Pre fetch and cache URL contents if enabled
         if(opts.content.url.prefetch === true && opts.content.url.path !== false)
         {
            // Setup options and sanitize
            options = opts.content.url;
            ajax = $.extend({}, opts.content.url);
            for(i in $.fn.qtip.defaults.content.url) delete ajax[i];

            // Perform the AJAX call
            $.ajax(
               $.extend(ajax, {
                  url: opts.content.url.path,
                  type: opts.content.url.method || 'GET',
                  data: opts.content.url.data,
                  success: function(content){ $.fn.qtip.cache[url+type].content = content; }
               })
            );
         };
      };

      // Iterate each matched element
      return $(this).each(function() // Return original elements as per jQuery guidelines
      {
         // Check for API commands
         if(typeof options == 'string')
         {
            // Setup command and data variables
            command = options.toLowerCase();
            data = $(this).data('qtip');

            if(typeof data == 'object' && typeof data.interfaces == 'object')
            {
               // Execute command on chosen qTips
               interfaces = $(this).qtip('interfaces');
               i = interfaces.length; while(i--)
               {
                  // Render and destroy commands don't require tooltip to be rendered
                  if(command == 'render') interfaces[i].render();
                  else if(command == 'destroy') interfaces[i].destroy();

                  // Only call API if tooltip is rendered and it wasn't a render or destroy call
                  else if(interfaces[i].status.rendered === true)
                  {
                     if(command == 'show') interfaces[i].show();
                     else if(command == 'hide') interfaces[i].hide();
                     else if(command == 'focus') interfaces[i].focus();
                     else if(command == 'disable') interfaces[i].disable(true);
                     else if(command == 'enable') interfaces[i].disable(false);
                  };
               };
            };
         }

         // No API commands, continue with qTip creation
         else
         {
            // Determine tooltip ID (Reuse array slots if possible)
            id = i = $.fn.qtip.interfaces.length;
            while(i--){ if(typeof $.fn.qtip.interfaces[id-(i+1)] == 'undefined'){ id = id-(i+1); break; }; };

            // Setup initial interfaces object with render and destroy methods
            $.fn.qtip.interfaces[id] = {
               id: id, status: { rendered: false },
               render: function(){ target.show.trigger(opts.show.when.event) },
               destroy: function(){ target.show.unbind(opts.show.when.event+namespace) }
            };

            // Determine hide and show targets
            target = { show: opts.show.when.target || $(this), hide: opts.hide.when.target || $(this) };

            // If prerendering is disabled, create tooltip on show event
            if(opts.content.prerender === false && opts.show.when.event !== false && opts.show.ready !== true)
            {
               // Determine events and setup temporary events namespace
               events = { show: opts.show.when.event || 'mouseover', hide: opts.hide.when.event || 'mouseout' };
               namespace = '.qtip-'+id+'-create';

               // Bind defined show event to show target to construct and show the tooltip
               target.show.bind(events.show+namespace, { id: id, target: this }, function(event)
               {
                  // Cache the event data and start the event sequence
                  var data = {
                     id: event.data.id,
                     target: event.data.target,
                     mouse: { left: event.pageX, top: event.pageY }
                  };
                  $.fn.qtip.cache.timers[data.id] = setTimeout(function()
                  {
                     // Instantiate the qTip
                     self = Instantiate.call(data.target, data.id, opts);

                     // Cache mouse coords,render and show the tooltip
                     self.cache.mouse = data.mouse;
                     self.render(); self.show();

                     // Unbind show and hide event
                     self.options.show.when.target.unbind(self.options.show.when.event+'.qtip-'+data.id+'-create');
                     self.options.hide.when.target.unbind(self.options.hide.when.event+'.qtip-'+data.id+'-create');
                  }
                  , opts.show.delay);
               });

               // If hide and show targets and events aren't identical, bind hide event to reset show timer
               if(target.show !== target.hide && opts.show.when.event !== opts.hide.when.event)
               {
                  target.hide.bind(events.hide+namespace, { id: id }, function(event)
                  {
                     clearTimeout($.fn.qtip.cache.timers[event.data.id]);
                  });
               }
            }

            // Prerendering is enabled, create tooltip now
            else
            {
               // Instantiate the qTip and set mouse position cache and render
               obj = Instantiate.call(this, id, opts);
               obj.cache.mouse = target.show.offset();
               obj.render();
            }
         };
      });
   };

   // Instantiator
   function Instantiate(id, opts)
   {
      // Create unique configuration object
      config = $.extend(true, {}, opts);

      // Sanitize target options
      if(config.position.container === false) config.position.container = $(document.body);
      if(config.position.target === false) config.position.target = $(this);
      if(config.show.when.target === false) config.show.when.target = $(this);
      if(config.hide.when.target === false) config.hide.when.target = $(this);

      // Instantiate the tooltip and add API reference
      obj = new qTip($(this), config, id);
      $.fn.qtip.interfaces[id] = obj;

      // Check if element already has qTip data assigned
      if(typeof $(this).data('qtip') == 'object')
      {
         // Set new current interface id
         if(typeof $(this).attr('qtip') === 'undefined')
            $(this).data('qtip').current = $(this).data('qtip').interfaces.length;

         // Push new API interface onto interfaces array
         $(this).data('qtip').interfaces.push(id);
      }

      // No qTip data is present, create now
      else $(this).data('qtip', { current: 0, interfaces: [id] });

      return obj;
   }

   // qTip constructor
   function qTip(target, options, id)
   {
      // Declare this reference
      var self = this;

      // Setup class attributes
      self.id = id;
      self.options = options;
      self.status = {
         animated: false,
         rendered: false,
         disabled: false,
         focused: false,
         hidden: true
      };
      self.elements = {
         target: target.addClass(self.options.style.classes.target),
         tooltip: null,
         wrapper: null,
         content: null,
         contentWrapper: null,
         title: null,
         button: null,
         tip: null,
         bgiframe: null
      };
      self.cache = {
         mouse: {},
         position: {},
         tip: false,
         imagemap: false
      };
      self.timers = {};

      // Define exposed API methods
      $.extend(self, self.options.api);
   };

   qTip.prototype.render = function()
   {
      var self = this, content, url, data, method, i, coords, x, y, temp;

      // If tooltip has already been rendered, exit
      if(self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_ALREADY_RENDERED, 'render');

      // Call API method
      self.beforeRender.call(self);

      // Create initial tooltip elements
      self.elements.tooltip =  '<div qtip="'+self.id+'" ' +
         ' class="qtip '+(self.options.style.classes.tooltip || self.options.style)+'"' +
         ' style="display:none; -moz-border-radius:0; -webkit-border-radius:0; border-radius:0;' +
         ' position:'+self.options.position.type+';">' +
         '  <div class="qtip-wrapper" style="position:relative; overflow:hidden; text-align:left;">' +
         '    <div class="qtip-contentWrapper" style="overflow:hidden;">' +
         '       <div class="qtip-content '+self.options.style.classes.content+'"></div>' +
         '</div></div></div>';

      // Append to container element
      self.elements.tooltip = $(self.elements.tooltip);
      self.elements.tooltip.appendTo(self.options.position.container)

      // Setup tooltip qTip data
      self.elements.tooltip.data('qtip', { current: 0, interfaces: [self.id] });

      // Setup element references
      self.elements.wrapper = self.elements.tooltip.children('div:first');
      self.elements.contentWrapper = self.elements.wrapper.children('div:first').css({ background: self.options.style.background });
      self.elements.content = self.elements.contentWrapper.children('div:first').css( jQueryStyle(self.options.style) );

      // Apply IE hasLayout fix to wrapper and content elements
      if($.browser.msie) self.elements.wrapper.add(self.elements.content).css({ zoom: 1 });

      // Setup tooltip attributes
      if(self.options.hide.when.event == 'unfocus') self.elements.tooltip.attr('unfocus', true);

      // Set rendered status to true
      self.status.rendered = true;

      // Convert position corner values into x and y strings
      self.options.position.corner.target = new Corner(self.options.position.corner.target);
      self.options.position.corner.tooltip = new Corner(self.options.position.corner.tooltip);
      if(self.options.style.tip.corner !== false) self.options.style.tip.corner = new Corner(self.options.style.tip.corner);
      if(self.options.style.tip.type !== false) self.options.style.tip.type = new Corner(self.options.style.tip.type);

      // If the positioning target element is an AREA element, cache the imagemap properties
      if(self.options.position.target.jquery && self.options.position.target.is('area') === true) cacheImagemap.call(self);

      // If an explicit width is set, updateWidth prior to setting content to prevent 'dirty' rendering
      if(typeof self.options.style.width.value == 'number') self.updateWidth();

      // Create borders and tips if supported by the browser
      if($('<canvas/>').get(0).getContext || $.browser.msie)
      {
         // Create border
         if(self.options.style.border.radius > 0)
            createBorder.call(self);
         else
            self.elements.contentWrapper.css({ border: self.options.style.border.width+'px solid '+self.options.style.border.color  });

         // Create tip if enabled
         if(self.options.style.tip.corner !== false) createTip.call(self);
      }

      // Neither canvas or VML is supported, tips and borders cannot be drawn!
      else
      {
         // Set defined border width
         self.elements.contentWrapper.css({ border: self.options.style.border.width+'px solid '+self.options.style.border.color  });

         // Reset border radius and tip
         self.options.style.border.radius = 0;
         self.options.style.tip.corner = false;

         // Inform via log
         $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.CANVAS_VML_NOT_SUPPORTED, 'render');
      };

      // Use the provided content string or DOM array
      if((typeof self.options.content.text == 'string' && self.options.content.text.length > 0)
      || (self.options.content.text.jquery && self.options.content.text.length > 0))
         content = self.options.content.text;

      // Use title string for content if present
      else if(typeof self.elements.target.attr('title') == 'string' && self.elements.target.attr('title').length > 0)
      {
         content = self.elements.target.attr('title').replace("\\n/g", '<br />');
         self.elements.target.removeAttr('title'); // Remove title attribute to prevent default tooltip showing
      }

      // No title is present, use alt attribute instead
      else if(typeof self.elements.target.attr('alt') == 'string' && self.elements.target.attr('alt').length > 0)
      {
         content = self.elements.target.attr('alt').replace("\\n/g", '<br />');
         self.elements.target.removeAttr('alt'); // Remove alt attribute to prevent default tooltip showing
      }

      // No valid content was provided, inform via log
      else
      {
         $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.NO_VALID_CONTENT, 'render');
         if(self.options.content.url.path === false) self.destroy();
         else content = 'Loading...';
      };

      // Set the tooltips content and create title if enabled
      self.updateContent(content, false);
      if(self.options.content.title.text !== false) createTitle.call(self);

      // Assign events and show tooltip if needed
      assignEvents.call(self);
      if(self.options.show.ready === true) self.show(null, self.options.show.ready);

      // Retrieve ajax content if provided
      if(self.options.content.url.path !== false)
      {
         // Check if prefetch was enabled and grab from cache initially if so
         if(self.options.content.url.prefetch === true && $.fn.qtip.cache[self.options.content.url+self.options.content.method])
            self.updateContent($.fn.qtip.cache[self.options.content.url+self.options.content.method]);

         // Prefetch was not enabled, grab remote content now
         else
         {
            // Setup options and sanitize
            options = self.options.content.url;
            ajax = $.extend({}, self.options.content.url);
            for(i in $.fn.qtip.defaults.content.url) delete ajax[i];

            // Load the content with specified options
            self.loadContent(options.path, options.data, options.method || 'get', ajax, true);
         }
      };

      // Focus the new tooltip
      self.focus();

      // Call API method and log event
      self.onRender.call(self);
      $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_RENDERED, 'render');
   };

   qTip.prototype.show = function(event, duration)
   {
      var self = this, returned, solo;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'show');

      // Make sure original target is still present and if not, destroy tooltip
      if(self.elements.target.length < 1)
         return $.fn.qtip.log.error.call(self, 4, $.fn.qtip.constants.TARGET_ELEMENT_REMOVED, 'show');

      // Only continue if element is visible
      if(self.elements.tooltip.css('display') != 'none'){ self.status.hidden = false; return self; };

      // Use initial option duration if not passed manually
      if(typeof duration != 'number') self.options.show.effect.duration;

      // Clear animation queue
      self.elements.tooltip.stop(true, false);

      // Call API method and if return value is false, halt
      returned = self.beforeShow.call(self, event);
      if(returned === false) return self;

      // Define afterShow callback method
      function afterShow()
      {
         // Reset opacity to avoid bugs and focus if it isn't static
         self.elements.tooltip.css({ opacity: "" });
         if(self.options.position.type !== 'static') self.focus();

         // Call API method
         self.onShow.call(self, event);

         // Prevent antialias from disappearing in IE7 by removing filter attribute
         if($.browser.msie) self.elements.tooltip.get(0).style.removeAttribute('filter');
      };

      // Maintain toggle functionality if enabled
      self.status.hidden = false;

      // Update tooltip position if it isn't static
      if(self.options.position.type !== 'static')
         self.updatePosition(event, (duration > 0 && self.options.show.effect === false));

      // Hide other tooltips if tooltip is solo
      if(self.options.show.solo === true)
      {
         $('.qtip[qtip]').not(self.elements.tooltip)
            .each(function()
            {
               api = $(this).qtip('api');
               if(api.status.rendered === true && api.status.hidden === false) api.hide();
            });
      }

      // Show tooltip
      if(typeof self.options.show.effect.type == 'function')
      {
         self.options.show.effect.type.call(self, self.elements.tooltip, duration);
         self.elements.tooltip.queue(function(){ afterShow(); $(this).dequeue(); });
      }
      else
      {
         if(self.options.show.effect.type === false)
         {
            self.elements.tooltip.show();
            afterShow();
         }
         else
         {
            switch(self.options.show.effect.type.toLowerCase())
            {
               case 'fade':
                  self.elements.tooltip.fadeIn(duration, afterShow);
                  break;
               case 'slide':
                  self.elements.tooltip.slideDown(duration, function()
                  {
                     afterShow();
                     if(self.options.position.type !== 'static') self.updatePosition(event, true);
                  });
                  break;
               case 'grow':
                  self.elements.tooltip.show(duration, afterShow);
                  break;
               default:
                  self.elements.tooltip.show();
                  afterShow();
                  break;
            };
         };

         // Add active class to tooltip
         self.elements.tooltip.addClass(self.options.style.classes.active);
      };

      // If inactive hide method is set, active it
      self.options.show.when.target.trigger('qtip_inactive');

      // Log event and return
      return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_SHOWN, 'show');
   };

   qTip.prototype.hide = function(event, duration)
   {
      var self = this, returned;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered) return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'hide');

      // Only continue if element is visible
      else if(self.elements.tooltip.css('display') === 'none'){ self.status.hidden = true; return self; };

      // Use initial option duration if not passed manually
      if(typeof duration != 'number') self.options.hide.effect.duration;

      // Stop show timer and animation queue
      clearTimeout(self.timers.show);
      self.elements.tooltip.stop(true, false);
      self.elements.tooltip.css({ opacity: "" });

      // Call API method and if return value is false, halt
      returned = self.beforeHide.call(self, event);
      if(returned === false) return self;

      // Define afterHide callback method
      function afterHide()
      {
         // Reset opacity to avoid bugs and call onHide event
         self.elements.tooltip.css({ opacity: "" });
         self.onHide.call(self, event);
      };

      // Maintain toggle functionality if enabled
      self.status.hidden = true;

      // Hide tooltip
      if(typeof self.options.hide.effect.type == 'function')
      {
         self.options.hide.effect.type.call(self, self.elements.tooltip, duration);
         self.elements.tooltip.queue(function(){ afterHide(); $(this).dequeue(); });
      }
      else
      {
         if(self.options.hide.effect.type === false)
         {
            self.elements.tooltip.hide();
            afterHide();
         }
         else
         {
            switch(self.options.hide.effect.type.toLowerCase())
            {
               case 'fade':
                  self.elements.tooltip.fadeOut(duration, afterHide);
                  break;
               case 'slide':
                  self.elements.tooltip.slideUp(duration, afterHide);
                  break;
               case 'grow':
                  self.elements.tooltip.hide(duration, afterHide);
                  break;
               default:
                  self.elements.tooltip.hide();
                  afterHide();
                  break;
            };
         };

         // Remove active class to tooltip
         self.elements.tooltip.removeClass(self.options.style.classes.active);
      };

      // Log event and return
      return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_HIDDEN, 'hide');
   };

   qTip.prototype.updatePosition = function(event, animate)
   {
      var self = this, target, tooltip, imagePos, newPosition, ieAdjust, ie6Adjust, mouseAdjust, offset, curPosition, returned;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'updatePosition');

      // If tooltip is static, return
      else if(self.options.position.type == 'static')
         return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.CANNOT_POSITION_STATIC, 'updatePosition');

      // Define property objects
      target = {
         position: { left: 0, top: 0 },
         dimensions: { height: 0, width: 0 },
         corner: self.options.position.corner.target
      };
      tooltip = {
         position: self.getPosition(),
         size: self.getDimensions(),
         corner: self.options.position.corner.tooltip
      };

      // Setup IE adjustment variables (Pixel gap bugs)
      ieAdjust = ($.browser.msie) ? 1 : 0; // And this is why I hate IE...
      ie6Adjust = ($.fn.qtip.cache.ie6) ? 1 : 0; // ...and even more so IE6!

      // Mouse is the target, set position to current mouse coordinates
      if(self.options.position.target === 'mouse')
      {
         // Setup target position and dimensions objects
         target.position = newPosition = { left: self.cache.mouse.left, top: self.cache.mouse.top, corner: tooltip.corner };
         target.size = { height: 1, width: 1 };
      }

      // Target is a regular HTML element
      else
      {
         // If the HTML element is an AREA element, calculate position manually
         if(self.cache.imagemap !== false)
         {
            // Cache image position
            imagePos = self.cache.imagemap.map.image.offset();

            // Use cached coordinate values if AREA element is of POLY shape
            if(self.cache.imagemap.area.shape === 'poly')
            {
               target.position = self.cache.imagemap.area.corners[self.options.position.corner.target.toString()];
               target.position = {
                  left: Math.floor(imagePos.left + target.position[0]),
                  top: Math.floor(imagePos.top + target.position[1])
               }
               target.size = { width: 0, height: 0 };
            }
            else
            {
               target.position = {
                  left: Math.floor(imagePos.left + self.cache.imagemap.area.left),
                  top: Math.floor(imagePos.top + self.cache.imagemap.area.top)
               };
               target.size = {
                  width: self.cache.imagemap.area.width,
                  height: self.cache.imagemap.area.height
               };
            }
         }

         // Target is the document
         else if(self.options.position.target.add(document.body).length === 1)
         {
            target.size = $.fn.qtip.cache.screen;
            target.position = target.size.scroll;
         }

         // Target is a regular HTML element, find position normally
         else
         {
            // Check if the target is another tooltip and if its animated, retrieve position from cached position
            if(self.options.position.target.attr('qtip') != undefined && typeof self.options.position.target.data('qtip') == 'object')
               target.position = self.options.position.target.qtip('api').cache.position;
            else
               target.position = self.options.position.target.offset();

            // Setup dimensions objects
            target.size = {
               height: self.options.position.target.outerHeight(),
               width: self.options.position.target.outerWidth()
            };
         };

         // Calculate correct target corner position
         newPosition = $.extend({}, target.position);
         if(target.corner.x == 'right') newPosition.left += target.size.width;
         if(target.corner.y == 'bottom') newPosition.top += target.size.height;
         if(target.corner.x == 'middle' || target.corner.x == 'center') newPosition.left += (target.size.width / 2);
         if(target.corner.y == 'middle' || target.corner.y == 'center') newPosition.top += (target.size.height / 2);
      };

      // Calculate correct target corner position
      if(tooltip.corner.x == 'right') newPosition.left -= tooltip.size.width;
      if(tooltip.corner.y == 'bottom') newPosition.top -= tooltip.size.height;
      if(tooltip.corner.x == 'middle' || tooltip.corner.x == 'center') newPosition.left -= (tooltip.size.width / 2);
      if(tooltip.corner.y == 'middle' || tooltip.corner.y == 'center') newPosition.top -= (tooltip.size.height / 2);

      // Adjust for border radius
      if(self.options.style.border.radius > 0)
      {
         if(tooltip.corner.precedance == 'y')
         {
            if(tooltip.corner.x == 'left') newPosition.left -= self.options.style.border.radius;
            else if(tooltip.corner.x == 'right') newPosition.left += self.options.style.border.radius;
         }
         else
         {
            if(tooltip.corner.y == 'top') newPosition.top -= self.options.style.border.radius;
            else if(tooltip.corner.y == 'bottom') newPosition.top += self.options.style.border.radius;
         }
      };

      // IE only adjustments (Pixel perfect!)
      if(ieAdjust)
      {
         if(tooltip.corner.y == 'top') newPosition.top -= ieAdjust
         else if(tooltip.corner.y == 'bottom') newPosition.top += ieAdjust;
         if(tooltip.corner.x == 'left') newPosition.left -= ieAdjust
         else if(tooltip.corner.x == 'right') newPosition.left += ieAdjust;
         if(tooltip.corner.y == 'middle') newPosition.top -= ieAdjust;
      };

      // If screen adjustment is enabled, apply adjustments
      if(self.options.position.adjust.screen === true) newPosition = screenAdjust.call(self, newPosition, target, tooltip);

      // If mouse is the target, prevent tooltip appearing directly under the mouse
      if(self.options.position.target === 'mouse' && self.options.position.adjust.mouse === true)
      {
         newPosition.left -= (newPosition.corner.x == 'right') ? 6 : -6;
         newPosition.top -= (newPosition.corner.y == 'bottom') ? 6 : -6;
      }

      // Initiate bgiframe plugin in IE6 if tooltip overlaps a select box or object element
      if($.fn.qtip.cache.ie6 && self.elements.bgiframe == null)
      {
         $('select, object').each(function()
         {
            offset = $(this).offset();
            offset.bottom = offset.top + $(this).height();
            offset.right = offset.left + $(this).width();

            if(newPosition.top + tooltip.size.height >= offset.top
            && newPosition.left + tooltip.size.width >= offset.left)
            {
               bgiframe.call(self);
               return false;
            }
         });
      };

      // Add user xy adjustments
      newPosition.left += self.options.position.adjust.x;
      newPosition.top += self.options.position.adjust.y;

      // Call API method and if return value is false, halt
      returned = self.beforePositionUpdate.call(self, event);
      if(returned === false) return self;

      // Cache new position
      self.cache.position = newPosition;

      // Check if animation is enabled
      if(animate === true)
      {
         // Set animated status, animate and reset status at end
         self.status.animated = true;
         self.elements.tooltip.animate(newPosition, 200, 'swing', function(){ self.status.animated = false });
      }

      // Set new position via CSS
      else self.elements.tooltip.css(newPosition);

      // Call API method and log event if its not a mouse move
      self.onPositionUpdate.call(self, event);
      if(!event || (typeof event != 'undefined' && event.type && event.type != 'mousemove'))
         $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_POSITION_UPDATED, 'updatePosition');

      return self;
   };

   qTip.prototype.updateWidth = function(newWidth)
   {
      var self = this, hidden;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'updateWidth');

      // Make sure supplied width is a number and if not, return
      else if(newWidth && typeof newWidth != 'number')
         return $.fn.qtip.log.error.call(self, 2, 'Width must be a number!', 'updateWidth');

      // Setup elements which must be hidden during width update
      hide = self.elements.contentWrapper.siblings().add(self.elements.tip);
      zoom = self.elements.content.add(self.elements.wrapper).add(self.elements.title);

      // Calculate the new width if one is not supplied
      if(!newWidth)
      {
         // Explicit numerical width is set
         if(typeof self.options.style.width.value == 'number') newWidth = self.options.style.width.value;

         // No width is set, proceed with auto detection
         else
         {
            // Set zoom to default to prevent IE hasLayout bug
            if($.browser.msie)
            {
               zoom.css({ zoom: 'normal' });
               hide.hide();
            }

            // Determine width
            self.elements.tooltip.css({ width: 'auto' });
            newWidth = self.getDimensions().width;

            // Make sure its within the maximum and minimum width boundries
            if(self.options.style.width.max && newWidth > self.options.style.width.max) newWidth = self.options.style.width.max
            if(self.options.style.width.min && newWidth < self.options.style.width.min) newWidth = self.options.style.width.min
         };
      };

      // Adjust newWidth by 1px if width is odd (IE6 rounding bug fix)
      if(typeof newWidth == 'number' && newWidth % 2 !== 0) newWidth -= 1;

      // Set the new calculated width and if width has not numerical, grab new pixel width
      self.elements.tooltip.width(newWidth);
      if(typeof newWidth != 'number') newWidth = self.getDimensions().width;

      // Set the border width, if enabled
      if(self.options.style.border.radius) self.elements.tooltip.find('.qtip-betweenCorners').css({ width: newWidth - (self.options.style.border.radius * 2) });

      // IE only adjustments
      if($.browser.msie)
      {
         // Set wrapper width and reset zoom to give the wrapper layout (IE hasLayout bug)
         zoom.css({ zoom: 1 });
         hide.show();

         // Adjust BGIframe height and width if enabled
         if(self.elements.bgiframe) self.elements.bgiframe.width(newWidth).height(self.getDimensions().height);
      };

      // Log event and return
      return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_WIDTH_UPDATED, 'updateWidth');
   };

   qTip.prototype.updateStyle = function(name)
   {
      var self = this, tip, borders, context, corner, coordinates;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'updateStyle');

      // Return if style is not defined or name is not a string
      else if(typeof name != 'string' || !$.fn.qtip.styles[name])
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.STYLE_NOT_DEFINED, 'updateStyle');

      // Set the new style object
      self.options.style = buildStyle.call(self, self.options.user.style, $.fn.qtip.styles[name]);

      // Update initial styles of content and title elements
      self.elements.content.css( jQueryStyle(self.options.style) );
      if(self.options.content.title.text !== false)
         self.elements.title.css( jQueryStyle(self.options.style.title, true) );

      // Update CSS border colour
      self.elements.contentWrapper.css({ borderColor: self.options.style.border.color });

      // Update tip color if enabled
      if(self.options.style.tip.corner !== false)
      {
         if($('<canvas/>').get(0).getContext)
         {
            // Retrieve canvas context and clear
            tip = self.elements.tooltip.find('.qtip-tip canvas:first');
            context = tip.get(0).getContext('2d');
            context.clearRect(0,0,300,300);

            // Draw new tip
            coordinates = calculateTip(self.cache.tip, self.options.style.tip.size.width, self.options.style.tip.size.height);
            drawTip.call(self, tip, coordinates, self.options.style.tip.color || self.options.style.border.color);
         }
         else if($.browser.msie)
         {
            // Set new fillcolor attribute
            tip = self.elements.tooltip.find('.qtip-tip [nodeName="shape"]');
            tip.attr('fillcolor', self.options.style.tip.color || self.options.style.border.color);
         };
      };

      // Update border colors if enabled
      if(self.options.style.border.radius > 0)
      {
         self.elements.tooltip.find('.qtip-betweenCorners').css({ backgroundColor: self.options.style.border.color });

         if($('<canvas/>').get(0).getContext)
         {
            borders = calculateBorders(self.options.style.border.radius)
            self.elements.tooltip.find('.qtip-wrapper canvas').each(function()
            {
               // Retrieve canvas context and clear
               context = $(this).get(0).getContext('2d');
               context.clearRect(0,0,300,300);

               // Draw new border
               corner = $(this).parent('div[rel]:first').attr('rel')
               drawBorder.call(self, $(this), borders[corner],
                  self.options.style.border.radius, self.options.style.border.color);
            });
         }
         else if($.browser.msie)
         {
            // Set new fillcolor attribute on each border corner
            self.elements.tooltip.find('.qtip-wrapper [nodeName="arc"]').each(function()
            {
               $(this).attr('fillcolor', self.options.style.border.color)
            });
         };
      };

      // Update width and position to coincide with new style
      self.updateWidth(); self.updatePosition(null, true);

      // Log event and return
      return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_STYLE_UPDATED, 'updateStyle');
   };

   qTip.prototype.updateContent = function(content, reposition)
   {
      var self = this, returned, images, loadedImages;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered) return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'updateContent');

      // Make sure content is defined before update
      else if(!content) return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.NO_CONTENT_PROVIDED, 'updateContent');

      // Call API method and if return value is false, halt
      if(self.beforeContentUpdate.call(self, content) === false) return;

      // Append new content if its a DOM array and show it if hidden
      if(content.jquery && content.length > 0) self.elements.content.html( content.clone(true).removeAttr('id').css({ display: 'block' }) );

      // Content is a regular string, insert the new content
      else self.elements.content.html(content);

      // Check if images need to be loaded before position is updated to prevent mis-positioning
      loadedImages = 0;
      images = self.elements.content.find('img')
      if(images.length)
      {
         images.load(function()
         {
            // Set image dimensions to prevent incorrect positioning
            $(this).attr('width', $(this).innerWidth());
            $(this).attr('height', $(this).innerHeight());

            // Make sure all iamges are loaded before proceeding with position update
            if(++loadedImages == images.length) afterLoad();
         })
      }
      else afterLoad();

      function afterLoad()
      {
         // Update the tooltip width
         self.updateWidth();

         // If repositioning is enabled, update positions
         if(reposition !== false)
         {
            // Update positions
            if(self.options.position.type != 'static') self.updatePosition(null, false);
            positionTip.call(self);
         };

         // Call API method and log event
         self.onContentUpdate.call(self);
         return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_CONTENT_UPDATED, 'loadContent');
      }
   };

   qTip.prototype.loadContent = function(url, data, method, ajax, reposition)
   {
      var self = this, returned, request;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'loadContent');

      // Call API method and if return value is false, halt
      if(self.beforeContentLoad.call(self) === false) return self;

      // Setup $.ajax option object and process the requeqst
      request = $.extend({}, ajax, { url: url, type: method, data: data, extended: ajax, success: setupContent, error: errorHandler });
      $.ajax(request);

      function errorHandler(xhr, status, error)
      {
         // Call user-defined error handler if present
         if($.isFunction(request.extended.error))
         {
            returned = request.extended.error();
            if(returned === false) return;
         }

         // Log error to console
         message = $.fn.qtip.constants.AJAX_ERROR + '[' + status + ']: ' + error;
         $.fn.qtip.log.error.call(self, 1, message, 'loadContent');

         // Update tooltip content to indicate error
         self.updateContent($.fn.qtip.constants.AJAX_ERROR, reposition);
      };

      function setupContent(content)
      {
         // Call user-defined success handler if present
         if($.isFunction(request.extended.success))
         {
            returned = request.extended.success();
            if(returned === false) return;
         }

         // Call API method and if return value is false, halt
         returned = self.onContentLoad.call(self, content);
         if(typeof returned == 'string') content = returned;

         // Log event and update content
         $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_CONTENT_LOADED, 'loadContent');
         self.updateContent(content, reposition);
      };

      return self;
   };

   qTip.prototype.updateTitle = function(content)
   {
      var self = this;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'updateTitle');

      // Make sure content is defined before update
      else if(!content)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.NO_CONTENT_PROVIDED, 'updateTitle');

      // Call API method and if return value is false, halt
      returned = self.beforeTitleUpdate.call(self);
      if(returned === false) return self;

      // Set the new content and reappend the button if enabled
      if(self.elements.button) self.elements.button = self.elements.button.clone(true);
      self.elements.title.html(content)
      if(self.elements.button) self.elements.title.prepend(self.elements.button);

      // Call API method and log event
      self.onTitleUpdate.call(self);
      return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_TITLE_UPDATED, 'updateTitle');
   };

   qTip.prototype.focus = function(event)
   {
      var self = this, curIndex, newIndex, elemIndex, returned;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'focus');

      else if(self.options.position.type == 'static')
         return $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.CANNOT_FOCUS_STATIC, 'focus');

      // Locate all the qTips on the page
      var qTips = $('.qtip[qtip]').not(self.elements.tooltip);

      // Set z-index variables
      curIndex = parseInt( self.elements.tooltip.css('z-index') );
      newIndex = $.fn.qtip.constants.baseIndex + qTips.length;

      // Only update the z-index if it has changed and tooltip is not already focused
      if(!self.status.focused && curIndex !== newIndex)
      {
         // Call API method and if return value is false, halt
         returned = self.beforeFocus.call(self, event);
         if(returned === false) return self;

         // Loop through all other tooltips
         qTips.each(function()
         {
            var api = $.fn.qtip.interfaces[$(this).attr('qtip')];

            if(api.status.rendered === true)
            {
               // Reduce all other tooltip z-index by 1
               elemIndex = parseInt($(this).css('z-index'));
               if(typeof elemIndex == 'number' && elemIndex > -1) $(this).css({ zIndex: elemIndex - 1 });

               // Set focused status to false
               api.status.focused = false;
            }
         })

         // Set the new z-index and set focus status to true
         self.elements.tooltip.css({ zIndex: newIndex });
         self.status.focused = true;

         // Call API method and log event
         self.onFocus.call(self, event);
         $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_FOCUSED, 'focus');
      };

      return self;
   };

   qTip.prototype.disable = function(state)
   {
      var self = this;

      if(state)
      {
         // Tooltip is not already disabled, proceed
         if(!self.status.disabled)
         {
            // Set the disabled flag and log event
            self.status.disabled = true;
            $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_DISABLED, 'disable');
         }

         // Tooltip is already disabled, inform user via log
         else  $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.TOOLTIP_ALREADY_DISABLED, 'disable');
      }
      else
      {
         // Tooltip is not already enabled, proceed
         if(self.status.disabled)
         {
            // Reassign events, set disable status and log
            self.status.disabled = false;
            $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_ENABLED, 'disable');
         }

         // Tooltip is already enabled, inform the user via log
         else $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.TOOLTIP_ALREADY_ENABLED, 'disable');
      };

      return self;
   };

   qTip.prototype.destroy = function()
   {
      var self = this, i, returned, interfaces;

      // Call API method and if return value is false, halt
      returned = self.beforeDestroy.call(self);
      if(returned === false) return self;

      // Check if tooltip is rendered
      if(self.status.rendered)
      {
         // Remove event handlers and remove element
         self.options.show.when.target.unbind('mousemove.qtip', self.updatePosition);
         self.options.show.when.target.unbind('mouseout.qtip', self.hide);
         self.options.show.when.target.unbind(self.options.show.when.event + '.qtip');
         self.options.hide.when.target.unbind(self.options.hide.when.event + '.qtip');
         self.elements.tooltip.unbind(self.options.hide.when.event + '.qtip');
         self.elements.tooltip.unbind('mouseover.qtip', self.focus);
         self.elements.tooltip.remove();
      }

      // Tooltip isn't yet rendered, remove render event
      else self.options.show.when.target.unbind(self.options.show.when.event+'.qtip-'+self.id+'.create');

      // Check to make sure qTip data is present on target element
      var data = self.elements.target.data('qtip');
      if(typeof data == 'object')
      {
         // Remove API references from interfaces object
         if(typeof data.interfaces == 'object' && data.interfaces.length > 0)
         {
            // Remove API from interfaces array
            i = data.interfaces.length;
            while(i--){ if(data.interfaces[i] == self.id){ data.interfaces.splice(i, 1); data.current--; } }
         }
      }

      // Remove interfaces object and element data if no other tooltips are present
      delete $.fn.qtip.interfaces[self.id];
      if(data.interfaces.length < 0) self.elements.target.removeData('qtip');

      // Call API method and log destroy
      self.onDestroy.call(self);
      $.fn.qtip.log.error.call(self, 1, $.fn.qtip.constants.EVENT_DESTROYED, 'destroy');

      return self.elements.target
   };

   qTip.prototype.getPosition = function()
   {
      var self = this, show, offset;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'getPosition');

      show = (self.elements.tooltip.css('display') != 'none') ? false : true;

      // Show and hide tooltip to make sure coordinates are returned
      if(show) self.elements.tooltip.css({ visiblity: 'hidden' }).show();
      offset = self.elements.tooltip.offset();
      if(show) self.elements.tooltip.css({ visiblity: 'visible' }).hide();

      return offset;
   };

   qTip.prototype.getDimensions = function()
   {
      var self = this, show, dimensions;

      // Make sure tooltip is rendered and if not, return
      if(!self.status.rendered)
         return $.fn.qtip.log.error.call(self, 2, $.fn.qtip.constants.TOOLTIP_NOT_RENDERED, 'getDimensions');

      show = (!self.elements.tooltip.is(':visible')) ? true : false;

      // Show and hide tooltip to make sure dimensions are returned
      if(show) self.elements.tooltip.css({ visiblity: 'hidden' }).show();
      dimensions = {
         height: self.elements.tooltip.outerHeight(),
         width: self.elements.tooltip.outerWidth()
      };
      if(show) self.elements.tooltip.css({ visiblity: 'visible' }).hide();

      return dimensions;
   };

   // Create borders using canvas and VML
   function createBorder()
   {
      var self, i, width, radius, color, coordinates, containers, size, betweenWidth, betweenCorners, borderTop, borderBottom, borderCoord, sideWidth, vertWidth;
      self = this;

      // Destroy previous border elements, if present
      self.elements.wrapper.find('.qtip-borderBottom, .qtip-borderTop').remove();

      // Setup local variables
      width = self.options.style.border.width;
      radius = self.options.style.border.radius;
      color = self.options.style.border.color || self.options.style.tip.color;

      // Calculate border coordinates
      coordinates = calculateBorders(radius);

      // Create containers for the border shapes
      containers = {};
      for(i in coordinates)
      {
         // Create shape container
         containers[i] = '<div rel="'+i+'" style="'+((i.search(/left/) !== -1) ? 'left' : 'right') + ':0; ' +
            'position:absolute; height:'+radius+'px; width:'+radius+'px; overflow:hidden; line-height:0.1px; font-size:1px">';

         // Canvas is supported
         if($('<canvas/>').get(0).getContext)
            containers[i] += '<canvas height="'+radius+'" width="'+radius+'" style="vertical-align: top"></canvas>';

         // No canvas, but if it's IE use VML
         else if($.browser.msie)
         {
            size = radius * 2 + 3;
            containers[i] += '<v:arc stroked="false" fillcolor="'+color+'" startangle="'+coordinates[i][0]+'" endangle="'+coordinates[i][1]+'" ' +
               'style="width:'+size+'px; height:'+size+'px; margin-top:'+((i.search(/bottom/) !== -1) ? -2 : -1)+'px; ' +
               'margin-left:'+((i.search(/right/) !== -1) ? coordinates[i][2] - 3.5 : -1)+'px; ' +
               'vertical-align:top; display:inline-block; behavior:url(#default#VML)"></v:arc>';

         };

         containers[i] += '</div>';
      };

      // Create between corners elements
      betweenWidth = self.getDimensions().width - (Math.max(width, radius) * 2);
      betweenCorners = '<div class="qtip-betweenCorners" style="height:'+radius+'px; width:'+betweenWidth+'px; ' +
         'overflow:hidden; background-color:'+color+'; line-height:0.1px; font-size:1px;">';

      // Create top border container
      borderTop = '<div class="qtip-borderTop" dir="ltr" style="height:'+radius+'px; ' +
         'margin-left:'+radius+'px; line-height:0.1px; font-size:1px; padding:0;">' +
         containers.topleft + containers.topright + betweenCorners + '</div>';
      self.elements.wrapper.prepend(borderTop);

      // Create bottom border container
      borderBottom = '<div class="qtip-borderBottom" dir="ltr" style="height:'+radius+'px; ' +
         'margin-left:'+radius+'px; line-height:0.1px; font-size:1px; padding:0;">' +
         containers.bottomleft + containers.bottomright + betweenCorners + '</div>';
      self.elements.wrapper.append(borderBottom);

      // Draw the borders if canvas were used (Delayed til after DOM creation)
      if($('<canvas/>').get(0).getContext)
      {
         self.elements.wrapper.find('canvas').each(function()
         {
            borderCoord = coordinates[ $(this).parent('[rel]:first').attr('rel') ];
            drawBorder.call(self, $(this), borderCoord, radius, color);
         })
      }

      // Create a phantom VML element (IE won't show the last created VML element otherwise)
      else if($.browser.msie) self.elements.tooltip.append('<v:image style="behavior:url(#default#VML);"></v:image>');

      // Setup contentWrapper border
      sideWidth = Math.max(radius, (radius + (width - radius)) )
      vertWidth = Math.max(width - radius, 0);
      self.elements.contentWrapper.css({
         border: '0px solid ' + color,
         borderWidth: vertWidth + 'px ' + sideWidth + 'px'
      })
   };

   // Border canvas draw method
   function drawBorder(canvas, coordinates, radius, color)
   {
      // Create corner
      var context = canvas.get(0).getContext('2d');
      context.fillStyle = color;
      context.beginPath();
      context.arc(coordinates[0], coordinates[1], radius, 0, Math.PI * 2, false);
      context.fill();
   };

   // Create tip using canvas and VML
   function createTip(corner)
   {
      var self, color, coordinates, coordsize, path;
      self = this;

      // Destroy previous tip, if there is one
      if(self.elements.tip !== null) self.elements.tip.remove();

      // Setup color, type and corner values
      if(self.options.style.tip.corner === false) return;
      else if(!corner) corner = self.options.style.tip.corner;
      color = self.options.style.tip.color || self.options.style.border.color;
      type = self.options.style.tip.type || corner;

      // Calculate tip coordinates
      coordinates = calculateTip(type.toString(), self.options.style.tip.size.width, self.options.style.tip.size.height);

      // Create tip element
      self.elements.tip =  '<div class="'+self.options.style.classes.tip+'" dir="ltr" rel="'+corner.x+corner.y+'" style="position:absolute; ' +
         'height:'+self.options.style.tip.size.height+'px; width:'+self.options.style.tip.size.width+'px; ' +
         'margin:0 auto; line-height:0.1px; font-size:1px;">';

      // Use canvas element if supported
      if($('<canvas/>').get(0).getContext)
          self.elements.tip += '<canvas height="'+self.options.style.tip.size.height+'" width="'+self.options.style.tip.size.width+'"></canvas>';

      // Canvas not supported - Use VML (IE)
      else if($.browser.msie)
      {
         // Create coordize and tip path using tip coordinates
         coordsize = self.options.style.tip.size.width + ',' + self.options.style.tip.size.height;
         path = 'm' + coordinates[0][0] + ',' + coordinates[0][1];
         path += ' l' + coordinates[1][0] + ',' + coordinates[1][1];
         path += ' ' + coordinates[2][0] + ',' + coordinates[2][1];
         path += ' xe';

         // Create VML element
         self.elements.tip += '<v:shape fillcolor="'+color+'" stroked="false" filled="true" path="'+path+'" coordsize="'+coordsize+'" ' +
            'style="width:'+self.options.style.tip.size.width+'px; height:'+self.options.style.tip.size.height+'px; ' +
            'line-height:0.1px; display:inline-block; behavior:url(#default#VML); ' +
            'vertical-align:'+corner.y+'"></v:shape>';

         // Create a phantom VML element (IE won't show the last created VML element otherwise)
         self.elements.tip += '<v:image style="behavior:url(#default#VML);"></v:image>';

         // Prevent tooltip appearing above the content (IE z-index bug)
         self.elements.contentWrapper.css('position', 'relative');
      };

      // Attach new tip to tooltip element
      self.elements.tooltip.prepend(self.elements.tip + '</div>');

      // Create element reference and draw the canvas tip (Delayed til after DOM creation)
      self.elements.tip = self.elements.tooltip.find('.'+self.options.style.classes.tip).eq(0);
      if($('<canvas/>').get(0).getContext)
         drawTip.call(self, self.elements.tip.find('canvas:first'), coordinates, color);

      // Fix IE small tip bug
      if(corner.y == 'top' && corner.precedance == 'y' && $.browser.msie)
         self.elements.tip.css({ marginTop: 1 });

      // Cache and set the tip position
      self.cache.tip = corner;
      positionTip.call(self, corner);
   };

   // Canvas tip drawing method
   function drawTip(canvas, coordinates, color)
   {
      // Setup properties
      var context = canvas.get(0).getContext('2d');
      context.fillStyle = color;

      // Create tip
      context.beginPath();
      context.moveTo(coordinates[0][0], coordinates[0][1]);
      context.lineTo(coordinates[1][0], coordinates[1][1]);
      context.lineTo(coordinates[2][0], coordinates[2][1]);
      context.fill();
   };

   function positionTip(corner)
   {
      var self, ieAdjust, paddingCorner, paddingSize, newMargin;
      self = this;

      // Return if tips are disabled or tip is not yet rendered
      if(self.options.style.tip.corner === false || !self.elements.tip) return;
      if(!corner) corner = self.cache.tip;

      // Setup adjustment variables
      ieAdjust = positionAdjust = ($.browser.msie) ? 1 : 0;

      // Set initial position
      self.elements.tip.css(corner.x, 0).css(corner.y, 0);

      // Set position of tip to correct side
      if(corner.precedance == 'y')
      {
         // Adjustments for IE6 - 0.5px border gap bug
         if($.browser.msie)
         {
            if($.fn.qtip.cache.ie6)
               positionAdjust = (corner.x == 'top') ? -3 : 1;
            else
               positionAdjust = (corner.x == 'top') ? 1 : 2;
         };

         if(corner.x == 'middle')
            self.elements.tip.css({ left: '50%', marginLeft: -(self.options.style.tip.size.width / 2) });

         else if(corner.x == 'left')
            self.elements.tip.css({ left: self.options.style.border.radius - ieAdjust });

         else if(corner.x == 'right')
            self.elements.tip.css({ right: self.options.style.border.radius + ieAdjust });

         if(corner.y == 'top')
            self.elements.tip.css({ top: -positionAdjust });
         else
            self.elements.tip.css({ bottom: positionAdjust });

      }
      else
      {
         // Adjustments for IE6 - 0.5px border gap bug
         if($.browser.msie)
            positionAdjust = ($.fn.qtip.cache.ie6) ? 1 : (corner.x == 'left' ? 1 : 2);

         if(corner.y == 'middle')
            self.elements.tip.css({ top: '50%', marginTop: -(self.options.style.tip.size.height / 2) });

         else if(corner.y == 'top')
            self.elements.tip.css({ top: self.options.style.border.radius - ieAdjust });

         else if(corner.y == 'bottom')
            self.elements.tip.css({ bottom: self.options.style.border.radius + ieAdjust });

         if(corner.x == 'left')
            self.elements.tip.css({ left: -positionAdjust });
         else
            self.elements.tip.css({ right: positionAdjust });
      };

      // Adjust tooltip padding to compensate for tip
      paddingCorner = 'padding-' + corner[corner.precedance];
      paddingSize = self.options.style.tip.size[ (paddingCorner.search(/left|right/) !== -1) ? 'width' : 'height' ];
      self.elements.tooltip.css('padding', 0);
      self.elements.tooltip.css(paddingCorner, paddingSize);

      // Match content margin to prevent gap bug in IE6 ONLY
      if($.fn.qtip.cache.ie6)
      {
         newMargin = parseInt(self.elements.tip.css('margin-top')) || 0;
         newMargin += parseInt(self.elements.content.css('margin-top')) || 0;

         self.elements.tip.css({ marginTop: newMargin });
      };
   };

   // Create title bar for content
   function createTitle()
   {
      var self = this;

      // Destroy previous title element, if present
      if(self.elements.title !== null) self.elements.title.remove();

      // Create title element
      self.elements.title = $('<div class="'+self.options.style.classes.title+'">')
         .css( jQueryStyle(self.options.style.title, true) )
         .css({ zoom: ($.browser.msie) ? 1 : 0 })
         .prependTo(self.elements.contentWrapper);

      // Update title with contents if enabled
      if(self.options.content.title.text) self.updateTitle.call(self, self.options.content.title.text);

      // Create title close buttons if enabled
      if(self.options.content.title.button !== false
      && typeof self.options.content.title.button == 'string')
      {
         self.elements.button = $('<a class="'+self.options.style.classes.button+'" style="float:right; position: relative"></a>')
            .css( jQueryStyle(self.options.style.button, true) )
            .html(self.options.content.title.button)
            .prependTo(self.elements.title)
            .click(function(event){ if(!self.status.disabled) return self.onButtonClick.call(self); });
      };
   };

   // Assign hide and show events
   function assignEvents()
   {
      var self, showTarget, hideTarget, inactiveEvents;
      self = this;

      // Setup event target variables
      showTarget = self.options.show.when.target;
      hideTarget = self.options.hide.when.target;

      // Add tooltip as a hideTarget is its fixed
      if(self.options.hide.fixed) hideTarget = hideTarget.add(self.elements.tooltip);

      // Check if the hide event is special 'inactive' type
      if(self.options.hide.when.event == 'inactive')
      {
         // Define 'inactive' event timer method and bind it as custom event
         function inactiveMethod(event)
         {
            if(self.status.disabled === true) return;

            //Clear and reset the timer
            clearTimeout(self.timers.inactive);
            self.timers.inactive = setTimeout(function(){ self.hide(event); }, self.options.hide.delay);
         };
         showTarget.bind('qtip_inactive', inactiveMethod);

         // Define events which reset the 'inactive' event handler
         $(['click', 'dblclick', 'mousedown', 'mouseup', 'mousemove', 'mouseout', 'mouseover' ]).each(function()
         {
            hideTarget.add(self.elements.content).bind(this+'.qtip-inactive', inactiveMethod);
         });
      }

      // Check if the tooltip is 'fixed'
      else if(self.options.hide.fixed === true)
      {
         self.elements.tooltip.bind('mouseover.qtip', function()
         {
            if(self.status.disabled === true) return;

            // Reset the hide timer
            clearTimeout(self.timers.hide);
         });
      };

      // Define show event method
      function showMethod(event)
      {
         if(self.status.disabled === true) return;

         // If set, hide tooltip when inactive for delay period
         if(self.options.hide.when.event == 'inactive') showTarget.trigger('qtip_inactive');

         // Clear hide timers
         clearTimeout(self.timers.show);
         clearTimeout(self.timers.hide);

         // Start show timer
         self.timers.show = setTimeout(function(){ self.show(event); }, self.options.show.delay);
      };

      // Define hide event method
      function hideMethod(event)
      {
         if(self.status.disabled === true) return;

         // Prevent hiding if tooltip is fixed and event target is the tooltip
         if(self.options.hide.fixed === true
         && self.options.hide.when.event.search(/mouse(out|leave)/i) !== -1
         && $(event.relatedTarget).parents('div.qtip[qtip]').length > 0)
         {

            // Prevent default and popagation
            event.stopPropagation();
            event.preventDefault();

            // Reset the hide timer
            clearTimeout(self.timers.hide);
            return false;
         };

         // Clear timers and stop animation queue
         clearTimeout(self.timers.show);
         clearTimeout(self.timers.hide);
         self.elements.tooltip.stop(true, true);

         // If tooltip has displayed, start hide timer
         self.timers.hide = setTimeout(function(){ self.hide(event); }, self.options.hide.delay);
      };

      // Both events and targets are identical, apply events using a toggle
      if((self.options.show.when.target.add(self.options.hide.when.target).length === 1
      && self.options.show.when.event == self.options.hide.when.event
      && self.options.hide.when.event != 'inactive')
      || self.options.hide.when.event == 'unfocus')
      {
         self.status.hidden = true;
         // Use a toggle to prevent hide/show conflicts
         showTarget.bind(self.options.show.when.event + '.qtip', function(event)
         {
            if(self.status.hidden == true) showMethod(event);
            else hideMethod(event);
         });
      }

      // Events are not identical, bind normally
      else
      {
         showTarget.bind(self.options.show.when.event + '.qtip', showMethod);

         // If the hide event is not 'inactive', bind the hide method
         if(self.options.hide.when.event != 'inactive')
            hideTarget.bind(self.options.hide.when.event + '.qtip', hideMethod);
      };

      // Focus the tooltip on mouseover
      if(self.options.position.type.search(/(fixed|absolute)/) !== -1)
         self.elements.tooltip.bind('mouseover.qtip', function(){ self.focus() });

      // If mouse is the target, update tooltip position on mousemove
      if(self.options.position.target === 'mouse' && self.options.position.type != 'static')
      {
         showTarget.bind('mousemove.qtip', function(event)
         {
            // Set the new mouse positions if adjustment is enabled
            self.cache.mouse = { left: event.pageX, top: event.pageY };

            // Update the tooltip position only if the tooltip is visible and adjustment is enabled
            if(self.status.disabled === false
            && self.options.position.adjust.mouse === true
            && self.options.position.type != 'static'
            && self.elements.tooltip.css('display') != 'none')
               self.updatePosition(event);
         });
      };
   };

   // Screen position adjustment
   function screenAdjust(position, target, tooltip)
   {
      var self, newPosition, overflow;
      self = this;

      // TODO: 'center' corner adjustment
      if(tooltip.corner.x == 'center') return position;

      // Setup corner and adjustment variable
      cache = $.fn.qtip.cache.screen.scroll;
      newPosition = {
         left: position.left, top: position.top,
         corner: tooltip.corner.clone()
      };

      // Determine which corners currently overflow off screen
      overflow = {
         left: (newPosition.left < cache.left),
         right: (newPosition.left + tooltip.size.width >= $.fn.qtip.cache.screen.width + cache.left),
         top: (newPosition.top < cache.top),
         bottom: (newPosition.top + tooltip.size.height >= $.fn.qtip.cache.screen.height + cache.top)
      };

      // Tooltip overflows off the left side of the screen
      if(overflow.left && (newPosition.corner.x == 'right' || (newPosition.corner.x != 'right' && !overflow.right)))
      {
         newPosition.left += tooltip.size.width - (self.options.style.border.radius * 2 || 0);
         newPosition.corner.x = 'left';
      }

      // Tooltip overflows off the right side of the screen
      else if(overflow.right && (newPosition.corner.x == 'left' || (newPosition.corner.x != 'left' && !overflow.left)))
      {
         newPosition.left -= tooltip.size.width - (self.options.style.border.radius * 2 || 0);
         newPosition.corner.x = 'right';
      };

      // Tooltip overflows off the top of the screen
      if(overflow.top && newPosition.corner.y != 'top')
      {
         if(self.options.position.target !== 'mouse')
            newPosition.top = target.position.top + target.size.height;
         else
            newPosition.top = self.cache.mouse.top

         newPosition.corner.y = 'top';
      }

      // Tooltip overflows off the bottom of the screen
      else if(overflow.bottom && newPosition.corner.y != 'bottom')
      {
         newPosition.top -= tooltip.size.height;
         newPosition.corner.y = 'bottom';
      };

      // Don't adjust if resulting position is negative
      if(newPosition.left < 0) newPosition.left += Math.abs(newPosition.left);
      if(newPosition.top < 0) newPosition.top += Math.abs(newPosition.top);

      // Change tip corner if positioning has changed and tips are enabled
      if(self.options.style.tip.corner !== false && newPosition.corner.toString() !== self.cache.tip.toString())
         createTip.call(self, newPosition.corner);

      return newPosition;
   };

   // Build a jQuery style object from supplied style object
   function jQueryStyle(style, sub)
   {
      var styleObj, i;

      styleObj = $.extend(true, {}, style);
      for(i in styleObj)
      {
         if(sub === true && i.search(/(tip|classes)/i) !== -1)
            delete styleObj[i];
         else if(!sub && i.search(/(width|border|tip|title|classes|user)/i) !== -1)
            delete styleObj[i];
      };

      return styleObj;
   };

   // Sanitize styles
   function sanitizeStyle(style)
   {
      if(typeof style.tip != 'object') style.tip = { corner: style.tip };
      if(typeof style.tip.size != 'object') style.tip.size = { width: style.tip.size, height: style.tip.size };
      if(typeof style.border != 'object') style.border = { width: style.border };
      if(typeof style.width != 'object') style.width = { value: style.width };
      if(typeof style.width.max == 'string') style.width.max = parseInt(style.width.max.replace(/([0-9]+)/i, "$1"));
      if(typeof style.width.min == 'string') style.width.min = parseInt(style.width.min.replace(/([0-9]+)/i, "$1"));

      // Convert deprecated x and y tip values to width/height
      if(typeof style.tip.size.x == 'number')
      {
         style.tip.size.width = style.tip.size.x;
         delete style.tip.size.x;
      };
      if(typeof style.tip.size.y == 'number')
      {
         style.tip.size.height = style.tip.size.y;
         delete style.tip.size.y;
      };

      return style;
   };

   // Build styles recursively with inheritance
   function buildStyle()
   {
      var self, i, styleArray, styleExtend, finalStyle;
      self = this;

      // Build style options from supplied arguments
      styleArray = [true, {}];
      i = arguments.length; while(i--) styleArray.push(arguments[i]);
      styleExtend = [ $.extend.apply($, styleArray) ];

      // Loop through each named style inheritance
      while(styleExtend[0] && typeof styleExtend[0].name == 'string')
      {
         // Check style exists
         if(typeof $.fn.qtip.styles[ styleExtend[0].name ] != 'object')
         {
            $.fn.qtip.log.error.call(self, 1, styleExtend[0].name+': '+$.fn.qtip.constants.STYLE_NOT_DEFINED, 'buildStyle');
            styleExtend.shift();
            continue;
         }

         // Sanitize style data and append to extend array
         else styleExtend.unshift( sanitizeStyle($.fn.qtip.styles[ styleExtend[0].name ]) );
      };

      // Make sure resulting tooltip className represents final style
      styleExtend.unshift(true,
         { classes: { tooltip: 'qtip-' + (arguments[0].name || 'defaults') } },
         (!arguments[0].name || (arguments[0].name && arguments[0].name.search(/^css$/i) < 0)) ? $.fn.qtip.styles.defaults : null);

      // Extend into a single style object
      finalStyle = $.extend.apply($, styleExtend);

      // Adjust tip size if needed (IE 1px adjustment bug fix)
      finalStyle.tip.size.width += ($.browser.msie) ? 1 : 0;
      finalStyle.tip.size.height += ($.browser.msie) ? 1 : 0;

      // Force even numbers for pixel precision
      if(finalStyle.tip.size.width % 2 > 0) finalStyle.tip.size.width += 1;
      if(finalStyle.tip.size.height % 2 > 0) finalStyle.tip.size.height += 1;

      // Sanitize final styles tip corner value
      if(finalStyle.tip.corner === true)
         finalStyle.tip.corner = (self.options.position.corner.tooltip === 'center') ? false : self.options.position.corner.tooltip;

      return finalStyle;
   };

   // Tip coordinates calculator
   function calculateTip(corner, width, height)
   {
      // Define tip coordinates in terms of height and width values
      var tips = {
         bottomright:   [[0,0],              [width,height],      [width,0]],
         bottomleft:    [[0,0],              [width,0],           [0,height]],
         topright:      [[0,height],         [width,0],           [width,height]],
         topleft:       [[0,0],              [0,height],          [width,height]],
         topmiddle:     [[0,height],         [width / 2,0],       [width,height]],
         bottommiddle:  [[0,0],              [width,0],           [width / 2,height]],
         rightmiddle:   [[0,0],              [width,height / 2],  [0,height]],
         leftmiddle:    [[width,0],          [width,height],      [0,height / 2]]
      };
      tips.lefttop = tips.bottomright;
      tips.righttop = tips.bottomleft;
      tips.leftbottom = tips.topright;
      tips.rightbottom = tips.topleft;

      return tips[corner];
   };

   // Special corner object
   function Corner(corner)
   {
      this.x = corner.match(/left|right|middle|center/i)[0].toLowerCase();
      this.y = corner.match(/top|bottom|middle|center/i)[0].toLowerCase();
      this.precedance = (corner.charAt(0).search(/t|b/) > -1) ? 'y' : 'x';
   };
   Corner.prototype.toString = function(){ return (this.precedance == 'y') ? this.y+this.x : this.x+this.y; };
   Corner.prototype.clone = function(){ return { x: this.x, y: this.y, precedance: this.precedance, toString: this.toString } };

   // Border coordinates calculator
   function calculateBorders(radius)
   {
      var borders;

      // Use canvas element if supported
      if($('<canvas/>').get(0).getContext)
      {
         borders = {
            topleft: [radius,radius], topright: [0,radius],
            bottomleft: [radius,0], bottomright: [0,0]
         };
      }

      // Canvas not supported - Use VML (IE)
      else if($.browser.msie)
      {
         borders = {
            topleft: [-90,90,0], topright: [-90,90,-radius],
            bottomleft: [90,270,0], bottomright: [90, 270,-radius]
         };
      };

      return borders;
   };

   function cacheImagemap()
   {
      var self = this, area, i, corners;

      // Setup imagemap cache object
      self.cache.imagemap = {
         area: {
            coords: self.options.position.target.attr('coords').split(','),
            shape: self.options.position.target.attr('shape').toLowerCase()
         },
         map: {
            name: self.options.position.target.parent('map').attr('name')
         }
      };
      self.cache.imagemap.map.image = $('img[usemap="#'+self.cache.imagemap.map.name+'"]:first');

      // Check imagemap for any invalid properties and prevent rendering if found
      if(typeof self.cache.imagemap.area.coords != 'object')
      {
         self.destroy(); self.status.rendered = false;
         return $.fn.qtip.log.error.call(self, 4, $.fn.qtip.constants.INVALID_AREA_COORDS, 'render');
      }
      else if(self.cache.imagemap.area.shape.search(/rect|circle|poly/i) < 0)
      {
         self.destroy(); self.status.rendered = false;
         return $.fn.qtip.log.error.call(self, 4, $.fn.qtip.constants.INVALID_AREA_SHAPE, 'render');
      }
      else if(typeof self.cache.imagemap.map.name == 'undefined')
      {
         self.destroy(); self.status.rendered = false;
         return $.fn.qtip.log.error.call(self, 4, $.fn.qtip.constants.NO_MAP_NAME_SPECIFIED, 'render');
      }
      else if(self.cache.imagemap.map.image.length < 1)
      {
         self.destroy(); self.status.rendered = false;
         return $.fn.qtip.log.error.call(self, 4, $.fn.qtip.constants.NO_IMAGES_USING_AREA_MAP, 'render');
      }

      // No invalid properties were found, continue with render
      else
      {
         area = self.cache.imagemap.area;

         // Determine width and height of the AREA element
         switch(area.shape)
         {
            case 'rect':
               // Parse coordinate strings into integers
               i = area.coords.length; while(i--) area.coords[i] = parseInt(area.coords[i]);

               // Setup height and width
               area.width = Math.ceil(Math.abs(area.coords[2] - area.coords[0]));
               area.height = Math.ceil(Math.abs(area.coords[3] - area.coords[1]));

               // Setup top and left coordinates
               area.left = area.coords[0];
               area.top = area.coords[1];
               break;

            case 'circle':
               // Parse coordinate strings into integers
               i = area.coords.length; while(i--) area.coords[i] = parseInt(area.coords[i]);

               // Setup height and width
               area.width = area.coords[2] + 2;
               area.height = area.coords[2] + 2;

               // Setup top and left coordinates
               area.left = area.coords[0] - Math.floor(area.width / 2);
               area.top = area.coords[1] - Math.floor(area.height / 2);
               break;

            case 'poly':
               // Split coordinates into xy arrays
               position = { top: 10000, right: 0, bottom: 0, left: 10000 };
               coords = []; i = area.coords.length; while(i--)
               {
                  var next = [ parseInt(area.coords[--i]), parseInt(area.coords[i+1]) ];

                  if(next[0] > position.right) position.right = next[0];
                  if(next[0] < position.left) position.left = next[0];
                  if(next[1] > position.bottom) position.bottom = next[1];
                  if(next[1] < position.top) position.top = next[1];

                  coords.push(next);
               }

               // Setup height, width and coordinates
               area.width = position.right - position.left;
               area.height = position.bottom - position.top;
               area.left = position.left; area.top = position.top;

               // Calculate all possible coordinates
               corners = {
                  bottomright:0, bottomleft:0, topright:0, topleft:0,
                  topmiddle:0, bottommiddle:0, rightmiddle:0, leftmiddle:0
               };
               for(i in corners)
               {
                  x = i.match(/left|right|middle/i)[0].toLowerCase();
                  y = i.match(/top|bottom|middle/i)[0].toLowerCase();
                  corners[i] = polyCoordinates.call(self, coords.slice(), x, y);
               };
               corners.lefttop = corners.topleft; corners.righttop = corners.topright;
               corners.leftbottom = corners.bottomleft; corners.rightbottom = corners.bottomright;
               corners.center = [ area.width / 2, area.height / 2 ];

               // Store calculated coordinates
               area.corners = corners;
               break;

            default:
               self.destroy();
               return $.fn.qtip.log.error.call(self, 4, $.fn.qtip.constants.INVALID_AREA_SHAPE, 'render');
               break;
         };

         // Set new area object properties
         self.cache.imagemap.area = area;
      };
   }

   // POLY area coordinate calculator
   //   Special thanks to Ed Cradock for helping out with this
   //   binary search algorithm that finds suitable coordinates.
   function polyCoordinates(coords, x, y)
   {
      var self, area, height, width, newWidth, newHeight, compareX, compareY, realX, realY;
      self = this;

      // Cache height and width
      area = self.cache.imagemap.area;
      width = area.width; height = area.height;

      // Use a binary search algorithm to locate most suitable coordinate (hopefully)
      newWidth = width; newHeight = height; compareX = 0; compareY = 0;
      while(newWidth > 0 && newHeight > 0)
      {
         newWidth = Math.floor(newWidth / 2);
         newHeight = Math.floor(newHeight / 2);

         if(x === 'left') compareX = newWidth;
         else if(x === 'right') compareX = width - newWidth;
         else compareX += Math.floor(newWidth / 2);

         if(y === 'top') compareY = newHeight;
         else if(y === 'bottom') compareY = height - newHeight;
         else compareY += Math.floor(newHeight / 2);

         var j = coords.length; while(j--)
         {
            if(coords.length < 2) break;

            realX = coords[j][0] - area.left;
            realY = coords[j][1] - area.top;

            if((x === 'left' && realX >= compareX)
            || (x === 'right' && realX <= compareX)
            || (x === 'middle' && (realX < compareX || realX > (width - compareX)))
            || (y === 'top' && realY >= compareY)
            || (y === 'bottom' && realY <= compareY)
            || (y === 'middle' && (realY < compareY || realY > (height - compareY)))) coords.splice(j, 1);

         }
      }

      return coords[0];
   };

   // BGIFRAME JQUERY PLUGIN ADAPTION
   //   Special thanks to Brandon Aaron for this plugin
   //   http://plugins.jquery.com/project/bgiframe
   function bgiframe()
   {
      var self, html, dimensions;
      self = this;

      // Determine current tooltip dimensions
      dimensions = self.getDimensions();

      // Setup iframe HTML string
      html = '<iframe class="qtip-bgiframe" frameborder="0" tabindex="-1" src="'+$.fn.qtip.constants.bgisource+'" '+
         ' style="display:block; position:absolute; z-index:-1; filter:alpha(opacity=\'0\'); ' +
         ' height:'+dimensions.height+'px; width:'+dimensions.width+'px"></iframe>';

      // Append the new HTML and setup element reference
      self.elements.bgiframe = self.elements.tooltip.prepend(html).children('.qtip-bgiframe:first');
   };

   // Setup cache and event initialisation on document load
   $.fn.qtip.cache = {
      timers: [],
      screen: {
         scroll: { left: 0, top: 0 },
         width: 800,
         height: 600
      },
      ie6: ($.browser.msie && parseInt($.browser.version) == 6)
   };
   $(document).ready(function()
   {
      // Setup library cache with window scroll and dimensions of document
      $.fn.qtip.cache.screen = {
         scroll: {
            left: $(window).scrollLeft(),
            top: $(window).scrollTop()
         },
         width: $(window).width(),
         height: $(window).height()
      };

      // Adjust positions of the tooltips on window resize or scroll if enabled
      var adjustTimer, i, api;
      $(window).bind('resize scroll', function(event)
      {
         clearTimeout(adjustTimer);
         adjustTimer = setTimeout(function()
         {
            // Readjust cached screen values
            if(event.type === 'scroll')
               $.fn.qtip.cache.screen.scroll = { left: $(window).scrollLeft(), top: $(window).scrollTop() };
            else
            {
               $.fn.qtip.cache.screen.width = $(window).width();
               $.fn.qtip.cache.screen.height = $(window).height();
            };

            i = $.fn.qtip.interfaces.length; while(i--)
            {
               // Access current elements API
               api = $.fn.qtip.interfaces[i];
               if(!api || !api.status) continue;

               // Update position if resize or scroll adjustments are enabled
               if(api.status.rendered === true && api.status.hidden === false && api.options.position.type !== 'static'
               && (
                  (api.options.position.adjust.scroll && event.type === 'scroll') ||
                  (api.options.position.adjust.resize && event.type === 'resize')
               ))
               {
                  // Queue the animation so positions are updated correctly
                  api.updatePosition(event, true);
               }
            };
         }
         , 100);
      });

      // Hide unfocus toolips on document mousedown
      $(document).bind('mousedown.qtip', function(event)
      {
         if($(event.target).parents('div.qtip').length === 0)
         {
            $('.qtip[unfocus]').each(function()
            {
               var api = $(this).qtip('api');

               // Only hide if its visible and not the tooltips target
               if($(this).is(':visible') && !api.status.disabled
               && $(event.target).add(api.elements.target).length > 1)
                  api.hide(event);
            })
         };
      })
   });

   // Define qTip API interfaces and log objects
   $.fn.qtip.interfaces = []
   $.fn.qtip.log = { error: function(){ return this; } };

   // Define configuration defaults
   $.fn.qtip.constants = { baseIndex: 6000, bgisource: "javascript:'';" };
   $.fn.qtip.defaults = {
      // Content
      content: {
         prerender: false,
         text: false,
         url: {
            prefetch: false,
            path: false,
            data: null,
            method: 'GET'
         },
         title: {
            text: false,
            button: false
         }
      },
      // Position
      position: {
         target: false,
         corner: {
            target: 'bottomRight',
            tooltip: 'topLeft'
         },
         adjust: {
            x: 0, y: 0,
            mouse: true,
            screen: false,
            scroll: true,
            resize: true
         },
         type: 'absolute',
         container: false
      },
      // Effects
      show: {
         when: {
            target: false,
            event: 'mouseover'
         },
         effect: {
            type: 'fade',
            duration: 100
         },
         delay: 140,
         solo: false,
         ready: false
      },
      hide: {
         when: {
            target: false,
            event: 'mouseout'
         },
         effect: {
            type: 'fade',
            duration: 100
         },
         delay: 0,
         fixed: false
      },
      // Callbacks
      api: {
         beforeRender: function(){},
         onRender: function(){},
         beforePositionUpdate: function(){},
         onPositionUpdate: function(){},
         beforeShow: function(){},
         onShow: function(){},
         beforeHide: function(){},
         onHide: function(){},
         beforeContentUpdate: function(){},
         onContentUpdate: function(){},
         beforeContentLoad: function(){},
         onContentLoad: function(){},
         beforeTitleUpdate: function(){},
         onTitleUpdate: function(){},
         beforeDestroy: function(){},
         onDestroy: function(){},
         beforeFocus: function(){},
         onFocus: function(){},
         onButtonClick: function(){ this.hide(); }
      }
   };

   $.fn.qtip.styles = {
      defaults: {
         background: 'white',
         color: '#111',
         overflow: 'hidden',
         textAlign: 'left',
         width: {
            min: 0,
            max: 250
         },
         padding: '5px 9px',
         border: {
            width: 1,
            radius: 0,
            color: '#d3d3d3'
         },
         tip: {
            corner: false,
            type: false,
            color: false,
            size: { width: 13, height: 13 }
         },
         title: {
            background: '#e1e1e1',
            fontWeight: 'bold',
            padding: '7px 12px'
         },
         button: {
            cursor: 'pointer'
         },
         classes: {
            target: '',
            tip: 'qtip-tip',
            title: 'qtip-title',
            button: 'qtip-button',
            content: 'qtip-content',
            active: 'qtip-active'
         }
      },
      css: {
         width: 'auto',
         border: {
            width: 0,
            radius: 0
         },
         tip: {
            corner: false,
            type: false,
            color: false,
            size: { width: 13, height: 13 }
         },
         title: {},
         button: {}
      },
      cream: {
         border: {
            width: 3,
            radius: 0,
            color: '#F9E98E'
         },
         title: {
            background: '#F0DE7D',
            color: '#A27D35'
         },
         background: '#FBF7AA',
         color: '#A27D35',

         classes: { tooltip: 'qtip-cream' }
      },
      light: {
         border: {
            width: 3,
            radius: 0,
            color: '#E2E2E2'
         },
         title: {
            background: '#f1f1f1',
            color: '#454545'
         },
         background: 'white',
         color: '#454545',

         classes: { tooltip: 'qtip-light' }
      },
      dark: {
         border: {
            width: 3,
            radius: 0,
            color: '#303030'
         },
         title: {
            background: '#404040',
            color: '#f3f3f3'
         },
         background: '#505050',
         color: '#f3f3f3',

         classes: { tooltip: 'qtip-dark' }
      },
      red: {
         border: {
            width: 3,
            radius: 0,
            color: '#CE6F6F'
         },
         title: {
            background: '#f28279',
            color: '#9C2F2F'
         },
         background: '#F79992',
         color: '#9C2F2F',

         classes: { tooltip: 'qtip-red' }
      },
      green: {
         border: {
            width: 3,
            radius: 0,
            color: '#A9DB66'
         },
         title: {
            background: '#b9db8c',
            color: '#58792E'
         },
         background: '#CDE6AC',
         color: '#58792E',

         classes: { tooltip: 'qtip-green' }
      },
      blue: {
         border: {
            width: 3,
            radius: 0,
            color: '#ADD9ED'
         },
         title: {
            background: '#D0E9F5',
            color: '#5E99BD'
         },
         background: '#E5F6FE',
         color: '#4D9FBF',

         classes: { tooltip: 'qtip-blue' }
      }
   };
   $.fn.qtip.styles.css.classes = $.fn.qtip.styles.defaults.classes;
   $.fn.qtip.styles.css.classes.tooltip = 'qtip-css';

})(jQuery);