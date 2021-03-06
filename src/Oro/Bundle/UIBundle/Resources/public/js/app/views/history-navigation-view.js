define(function(require) {
    'use strict';

    var HistoryNavigationView;
    var BaseView = require('./base/view');

    HistoryNavigationView = BaseView.extend({
        autoRender: true,
        template: require('tpl-loader!oroui/templates/history.html'),
        events: {
            'click .undo-btn': 'onUndo',
            'click .redo-btn': 'onRedo'
        },

        listen: {
            'change:index model': 'render'
        },

        /**
         * @inheritDoc
         */
        constructor: function HistoryNavigationView() {
            HistoryNavigationView.__super__.constructor.apply(this, arguments);
        },

        onUndo: function() {
            var index = this.model.get('index');
            this.trigger('navigate', index - 1);
        },

        onRedo: function() {
            var index = this.model.get('index');
            this.trigger('navigate', index + 1);
        }
    });

    return HistoryNavigationView;
});
