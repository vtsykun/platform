define([
    'jquery',
    'underscore',
    'oroui/js/mediator',
    'routing',
    './../base/page-region-view'
], function($, _, mediator, routing, PageRegionView) {
    'use strict';

    /* globals Sfjs */
    // Sfjs is global object that provides access to Symfony Debug Toolbar
    // Sfjs is patched to update layout on Debug Toolbar changes
    if (Sfjs !== void 0) {
        var originalRenderAjaxRequests = Sfjs.renderAjaxRequests;
        Sfjs.renderAjaxRequests = function() {
            originalRenderAjaxRequests.call(Sfjs, arguments);
        };
    }

    var DebugToolbarView;

    DebugToolbarView = PageRegionView.extend({
        listen: {
            'page:error mediator': 'onPageUpdate'
        },

        /**
         * @inheritDoc
         */
        constructor: function DebugToolbarView(options) {
            DebugToolbarView.__super__.constructor.apply(this, arguments);
        },

        /**
         * Handles page load event
         *  - loads debug data
         *  - updates a debugger bar
         *
         * @param {Object} data
         * @param {Object} actionArgs arguments of controller's action point
         * @param {Object} xhr
         * @override
         */
        onPageUpdate: function(data, actionArgs, xhr) {
            if (!actionArgs.route.previous) {
                // nothing to do, the page just loaded
                return;
            } else if (!xhr) {
                this.$el.empty();
                return;
            }
            this.updateToolbar(xhr);
        },

        /**
         * Makes request for toolbar's content and call render method
         *
         * @param {Object} xhr
         */
        updateToolbar: function(xhr) {
            var token = xhr.getResponseHeader('x-debug-token');
            if (token) {
                var url = routing.generate('_wdt', {token: token});
                $.get(url, _.bind(this.render, this, token, url));
            }
        },

        /**
         * Updates a debugger bar
         *
         * @param {string} token
         * @param {string} url
         * @param {string} data html content
         */
        render: function(token, url, data) {
            var id;

            if (!data) {
                return;
            }

            id = 'sfwdt' + token;
            this.$el
                .appendTo('body')
                .attr('id', id)
                .attr('data-sfurl', url);
            this.$el.html(data);
            if (Sfjs) {
                Sfjs.renderAjaxRequests();
            }
        }
    });

    return DebugToolbarView;
});
