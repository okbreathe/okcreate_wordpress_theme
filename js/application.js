(function($){
  // Scoping to no-js will happen sooner than scoping to js-enabled
  $('html').removeClass('no-js').addClass('js-enabled');
  
  window.App = {
    init: function(){
      $.each(App.ui,function(k,fn){ fn(); });
    },
    state: {
      device    : function(){
        if (window.getComputedStyle) 
          return window.getComputedStyle(document.body,':after').getPropertyValue('content').replace(/['"]/g,'');
      },
      isPhone        : function(){ return this.device().indexOf("phone") > -1 },
      isTablet       : function(){ return this.device() == 'tablet';  },
      isDesktop      : function(){ return $.inArray(this.device(),['none', 'desktop-large', '']) > -1; },
      isDesktopLarge : function(){ return this.device() == 'desktop-large'; }
    },
    // When DOM is ready each function here will be called
    ui: {
    }
  };

  $(function(){ App.init(); });

})(jQuery);
