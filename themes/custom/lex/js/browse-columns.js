// https://github.com/alphagov/collections/blob/master/app/assets/javascripts/browse-columns.js
(function() {
  "use strict";
  window.GOVUK = window.GOVUK || {};

  // https://github.com/alphagov/frontend/blob/master/app/assets/javascripts/support.js
  if(typeof window.GOVUK.support === 'undefined'){ window.GOVUK.support = {}; }
  window.GOVUK.support.history = function() {
    return window.history && window.history.pushState && window.history.replaceState;
  }

  var $ = window.jQuery;

  function BrowseColumns(options){
    if(options.$el.length === 0) return;
    if(!GOVUK.support.history()) return;
    if($(window).width() < 640) return; // don't ajax navigation on mobile

    this.$el = options.$el;
    this.$root = this.$el.find('#root');
    this.$section = this.$el.find('#section');
    this.$subsection = this.$el.find('#subsection');
    this.$breadcrumbs = $('#global-breadcrumb ol');
    this.animateSpeed = 330;

    if(this.$section.length === 0){
      this.$section = $('<div id="section" class="pane with-sort" />');
      this.$el.prepend(this.$section);
      this.$el.addClass('section');
    }

    if(this.$subsection.length === 0){
      this.$subsection = $('<div id="subsection" class="pane" />').hide();
      this.$el.prepend(this.$subsection);
    } else {
      this.$subsection.show();
    }

    this.displayState = this.$el.data('state');
    if(typeof this.displayState === 'undefined'){
      this.displayState = 'root';
    }
    this._cache = {};

    this.lastState = this.parsePathname(window.location.pathname);

    this.$el.on('click', 'a', this.navigate.bind(this));

    $(window).on('popstate', this.popState.bind(this));
  }
  BrowseColumns.prototype = {
    popState: function(e){
      var state = e.originalEvent.state,
          loadPromise;
      if(!state){ // state will be null if there was no state set
        state = this.parsePathname(window.location.pathname);
      }

      if(this.lastState.slug === state.slug){
        return; // nothing has changed
      }

      if(state.slug == ''){
        loadPromise = this.showRoot();
      } else if(state.subsection){
        loadPromise = this.restoreSubsection(state);
      } else {
        loadPromise = this.loadSectionFromState(state, true);
      }
      loadPromise.done(function(){
        this.trackPageview(state);
      }.bind(this));
    },
    restoreSubsection: function(state){
      // are we displaying the correct section for the subsection?
      if(this.lastState.section != state.section){
        // load the section then load the subsection after
        var sectionPathname = window.location.pathname.split('/').slice(0,-1).join('/');
        var sectionState = this.parsePathname(sectionPathname);
        var sectionPromise = this.loadSectionFromState(sectionState, true);
        sectionPromise.pipe(function(){
          return this.loadSectionFromState(state, true);
        }.bind(this));
        return sectionPromise;
      } else {
        return this.loadSectionFromState(state, true);
      }
    },
    sectionCache: function(prefix, slug, data){
      if(typeof data === 'undefined'){
        return this._cache[prefix + slug];
      } else {
        this._cache[prefix + slug] = data;
      }
    },
    isDesktop: function(){
      return $(window).width() > 768;
    },
    showRoot: function(){
      this.$section.html('');
      this.displayState = 'root';
      this.$root.find('h1').focus();

      var out = new $.Deferred()
      return out.resolve();
    },
    showSection: function(state){
      state.title = this.getTitle(state.slug);
      this.setTitle(state.title);
      this.$section.html(state.sectionData.html)

      this.highlightSection('root', state.path);
      this.removeLoading();
      this.updateBreadcrumbs(state);
      this.lexUpdateTranslation();

      var animationDone;
      if(this.displayState === 'subsection'){
        // animate to the right position and update the data
        animationDone = this.animateSubsectionToSectionDesktop();
      } else if(this.displayState === 'root'){
        animationDone = this.animateRootToSectionDesktop();
      } else {
        animationDone = new $.Deferred();
        animationDone.resolve();
      }
      animationDone.done(function(){
        this.$section.find('h1').focus();
      }.bind(this));
    },
    animateSubsectionToSectionDesktop: function(){
      var out = new $.Deferred()
      function afterAnimate(){
        this.displayState = 'section';

        this.$el.removeClass('subsection').addClass('section');
        this.$section.attr('style', '');
        this.$section.find('.pane-inner').attr('style', '');
        this.$section.addClass('with-sort');
        this.$root.attr('style', '');
        out.resolve();
      }


      // animate to the right position and update the data
      this.$root.css({ position: 'absolute', width: this.$root.width() });
      this.$subsection.hide();
      this.$section.css('margin-right', '63%');
      if(this.isDesktop()){
        this.$section.find('.pane-inner.curated').animate({
          paddingLeft: '30px'
        }, this.animateSpeed);

        this.$section.find('.pane-inner.alphabetical').animate({
          paddingLeft: '96px'
        }, this.animateSpeed);

        var sectionProperties = {
          width: '35%',
          marginLeft: '0%',
          marginRight: '40%'
        };
      } else {
        var sectionProperties = {
          width: '30%',
          marginLeft: '0%',
          marginRight: '45%'
        };
      }
      this.$section.animate(sectionProperties, this.animateSpeed, afterAnimate.bind(this));
      return out;
    },
    animateRootToSectionDesktop: function(){
      var out = new $.Deferred()
      this.displayState = 'section';
      this.$el.removeClass('subsection').addClass('section');

      return out.resolve();
    },
    showSubsection: function(state){
      state.title = this.getTitle(state.slug);

      this.setTitle(state.title);
      this.$subsection.html(state.sectionData.html);
      this.highlightSection('section', state.path);
      this.highlightSection('root', '/browse/' + state.section);
      this.removeLoading();
      this.updateBreadcrumbs(state);

      var animationDone;
      if(this.displayState !== 'subsection'){
        animationDone = this.animateSectionToSubsectionDesktop();
      } else {
        animationDone = new $.Deferred();
        animationDone.resolve();
      }
      animationDone.done(function(){
        this.$subsection.find('h1').focus();
      }.bind(this));
    },
    animateSectionToSubsectionDesktop: function(){
      var out = new $.Deferred()
      // animate to the right position and update the data
      this.$root.css({ position: 'absolute', width: this.$root.width() });
      this.$section.find('.sort-order').hide();
      this.$section.find('.pane-inner').animate({
        paddingLeft: '0'
      }, this.animateSpeed);
      if(this.isDesktop()){
        var sectionProperties = {
          width: '25%',
          marginLeft: '-13%',
          marginRight: '63%'
        };
      } else {
        var sectionProperties = {
          width: '30%',
          marginLeft: '-18%',
          marginRight: '63%'
        };
      }
      this.$section.animate(sectionProperties, this.animateSpeed, function(){
        this.$el.removeClass('section').addClass('subsection');
        this.$subsection.show();
        this.$section.removeClass('with-sort');
        this.displayState = 'subsection';

        this.$section.find('.sort-order').attr('style', '');
        this.$section.attr('style', '');
        this.$section.find('.pane-inner').attr('style', '');
        this.$root.attr('style', '');
        out.resolve();
      }.bind(this));
      return out;
    },
    getTitle: function(slug){
      var $link = this.$el.find('a[href$="/browse/'+slug+'"]:first'),
          $heading = $link.find('h3');
      if($heading.length > 0){
        return $heading.text();
      } else {
        return $link.text();
      }
    },
    setTitle: function(title){
      $('title').text(title);
    },
    addLoading: function($el){
      this.$el.attr('aria-busy', 'true');
      $el.addClass('loading');
    },
    removeLoading: function(){
      this.$el.attr('aria-busy', 'false');
      this.$el.find('a.loading').removeClass('loading');
    },

    // getSectionData: returns a promise which will contain the data
    // Returns data from the cache if it is there or puts the data in the cache
    // if it is not.
    getSectionData: function(state){
      var cacheForSlug = this.sectionCache('section', state.slug),
          out = new $.Deferred(),
          url = '/browse/' + state.slug

      if(typeof state.sectionData !== 'undefined'){
        out.resolve(state.sectionData);
      } else if(typeof cacheForSlug !== 'undefined'){
        out.resolve(cacheForSlug);
      } else {
        $.ajax({
          url: url
        }).done(function(data){
          data = this.lexParseFullPage(data, state.slug);
          this.sectionCache('section', state.slug, data);

          out.resolve(data);
        }.bind(this));
      }
      return out;
    },
    highlightSection: function(section, slug){
      this.$el.find('#'+section+' .active').removeClass('active')
      this.$el.find('a[href$="'+slug+'"]').parent().addClass('active');
    },
    parsePathname: function(pathname){
      var out = {
        path: pathname,
        slug: pathname.replace(/\/browse\/?/, '')
      };

      if(out.slug.indexOf('/') > -1){
        out.section = out.slug.split('/')[0];
        out.subsection = out.slug.split('/')[1];
      } else {
        out.section = out.slug;
      }
      return out;
    },
    scrollToBrowse: function(){
      var $body = $('body'),
          elTop = this.$el.offset().top;

      if( $body.scrollTop() > elTop ){
        $('body').animate({scrollTop: elTop}, this.animateSpeed);
      }
    },
    loadSectionFromState: function(state, poppingState){
      var donePromise = this.getSectionData(state);
      if(state.subsection){
        var sectionPromise = donePromise;
        donePromise = $.when(sectionPromise);
      }

      donePromise.done(function(sectionData){
        state.sectionData = sectionData;
        this.scrollToBrowse();

        if(state.subsection){
          this.showSubsection(state);
        } else {
          this.showSection(state);
        }

        if(typeof poppingState === 'undefined'){
          history.pushState(state, '', state.path);
          this.trackPageview(state);
        }
        this.lastState = state;
      }.bind(this));

      return donePromise;
    },
    navigate: function(e){
      if(e.currentTarget.pathname.match(/^\/browse\/[^\/]+(\/[^\/]+)?$/)){
        e.preventDefault();

        var $target = $(e.currentTarget);
        var state = this.parsePathname(e.currentTarget.pathname);
        state.title = $target.text();

        if(state.path === window.location.pathname){
          return;
        }

        this.addLoading($target);
        this.loadSectionFromState(state);
      }
    },
    updateBreadcrumbs: function(state){
      return this.lexUpdateBreadcrumbs(state);
    },
    lexParseFullPage: function(fullPage, slug) {
      var fullPageHTML = $.parseHTML(fullPage);
      var section = (slug.indexOf('/') > -1 ? '#subsection' : '#section');
      return {'html': $(fullPageHTML).find(section).html()};
    },
    lexUpdateBreadcrumbs: function(state){
      var $breadcrumbItems = this.$breadcrumbs.find('li');
      if(state.subsection){
        var sectionSlug = state.section;
        var sectionTitle = this.$section.find('h1').text();

        if($breadcrumbItems.length === 1){
          var $sectionBreadcrumb = $('<li class="lex-breadcrumb-item"/>');
          this.$breadcrumbs.append($sectionBreadcrumb);
        } else {
          var $sectionBreadcrumb = $breadcrumbItems.slice(1);
        }

        $sectionBreadcrumb.html('<a class="lex-font-muted" href="/browse/'+sectionSlug+'">'+sectionTitle+'</a>');
      } else {
        this.$breadcrumbs.find('li').slice(1).remove();
      }
    },
    lexUpdateTranslation: function(){
      $.getScript("//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit");
    },
    trackPageview: function(state){
      // handled via GTM
    }
  };

  GOVUK.BrowseColumns = BrowseColumns;

  $(function(){ GOVUK.browseColumns = new BrowseColumns({$el: $('.browse-panes')}); })
}());
