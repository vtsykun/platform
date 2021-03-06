define(function(require) {
    'use strict';

    var FlowchartViewerTransitionOverlayView;
    var FlowchartJsPlumbOverlayView = require('../jsplumb/overlay-view');

    FlowchartViewerTransitionOverlayView = FlowchartJsPlumbOverlayView.extend({
        template: require('tpl-loader!oroworkflow/templates/flowchart/viewer/transition.html'),

        /**
         * @inheritDoc
         */
        constructor: function FlowchartViewerTransitionOverlayView() {
            FlowchartViewerTransitionOverlayView.__super__.constructor.apply(this, arguments);
        },

        className: function() {
            var classNames = [FlowchartViewerTransitionOverlayView.__super__.className.call(this)];
            classNames.push('workflow-transition-overlay');
            return classNames.join(' ');
        }
    });

    return FlowchartViewerTransitionOverlayView;
});
