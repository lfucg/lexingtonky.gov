/*global define,dojo,appGlobals */
/*jslint browser:true,sloppy:true,nomen:true,unparam:true,plusplus:true,indent:4 */
/*
 | Copyright 2015 Esri
 |
 | Licensed under the Apache License, Version 2.0 (the "License");
 | you may not use this file except in compliance with the License.
 | You may obtain a copy of the License at
 |
 |    http://www.apache.org/licenses/LICENSE-2.0
 |
 | Unless required by applicable law or agreed to in writing, software
 | distributed under the License is distributed on an "AS IS" BASIS,
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 | See the License for the specific language governing permissions and
 | limitations under the License.
 */
//============================================================================================================================//
define([
    "dojo/_base/declare",
    "dijit/_WidgetBase",
    "dojo/dom-construct",
    "dojo/window",
    "dojo/_base/lang",
    "dojo/dom-attr",
    "dojo/dom-style",
    "dojo/dom-class",
    "dojo/topic",
    "dojo/query",
    "dojo/on",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dijit/a11yclick"

], function (declare, WidgetBase, domConstruct, win, lang, domAttr, domStyle, domClass, topic, query, on, sharedNls, a11yclick) {

    //========================================================================================================================//

    return declare([WidgetBase], {
        sharedNls: sharedNls,                         // Variable for shared NLS
        isPodCreated: 0,                              // Variable for if pod is created
        divToggle: null,                              // Variable for toggle button
        divImageBackground: null,                     // Variable for back ground image div
        imgToggleResults: null,                       // Variable for toggle result image
        divCarouselContent: null,                     // Variable for carousel content div
        resultboxPanelContent: null,                  // Variable for result box panel content

        /**
        * create carouselContainer widget
        *
        * @class
        * @name widgets/carouselContainer/carouselContainer
        */
        postCreate: function () {
            topic.subscribe("collapseCarousel", lang.hitch(this, function () {
                this.collapseCarousel();
                appGlobals.shareOptions.isShowPod = "false";
            }));
        },
        /**
        * Create carousel container pod
        * @param {Object} Node in which we want to create carousel pod
        * @param {String} Span text which we want to show.
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        createPod: function (parentNote, spanValue) {
            var divCarouselContentInfo, resultboxPanel;
            this.divToggle = domConstruct.create("div", { "class": "esriCTdivToggle" }, parentNote);
            this.divImageBackground = domConstruct.create("div", { "class": "esriCTDivImageBackground" }, this.divToggle);
            this.imgToggleResults = domConstruct.create("div", { "class": "esriCTUpAndDownArrow" }, this.divImageBackground);
            this.imgToggleResults.title = sharedNls.tooltips.hidePanelTooltip;
            domClass.add(this.imgToggleResults, "esriCTDownDownarrowImage");
            domConstruct.create("div", { "class": "esriCTSpanResult", "innerHTML": spanValue, "title": spanValue }, this.divImageBackground);
            this.divCarouselContent = domConstruct.create("div", { "class": "esriCTDivCarouselContent" }, parentNote);
            domClass.add(this.divCarouselContent, "esriCTHideBottomContainerHeight");
            domClass.add(this.divCarouselContent, "esriCTzeroHeight");
            divCarouselContentInfo = domConstruct.create("div", { "class": "esriCTtransparentBackground" }, this.divCarouselContent);
            domClass.add(divCarouselContentInfo, "esriCTDivCarouselContentInfo");
            resultboxPanel = domConstruct.create("div", { "class": "esriCTResultBoxPanel" }, divCarouselContentInfo);
            this.resultboxPanelContent = domConstruct.create("div", { "class": "esriCTResultBoxPanelContent" }, resultboxPanel);
            // On click on image for wipe in and wipeout.
            this.own(on(this.divImageBackground, a11yclick, lang.hitch(this, function () {
                this._toggleCarouselPod();
                // On mobile close header panels
                if (win.getBox().w <= 766) {
                    topic.publish("toggleWidget");
                }
            })));
            domStyle.set(this.divImageBackground, "display", "none");
        },

        /**
        * Show carousel pod
        * @param {array} Array of div which we insert in carousel Container
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        addPod: function (content) {
            var i = 0;
            // Checking condition if pod is added in container
            if (this.resultboxPanelContent && content && content.length > 0) {
                for (i; i < content.length; i++) {
                    this.isPodCreated++;
                    this.resultboxPanelContent.appendChild(content[i]);
                }
            }
        },

        /**
        * Clear Content in side the carousel container
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        removePod: function (carouselPodData) {
            var j, i, carouselPodAttributeValue;
            // Checking condition if pod is added in container
            if (this.resultboxPanelContent && this.resultboxPanelContent.childNodes) {
                // Looping for container pod data
                for (j = 0; j < carouselPodData.length; j++) {
                    // Looping for child in container
                    for (i = 0; i < this.resultboxPanelContent.childNodes.length; i++) {
                        carouselPodAttributeValue = domAttr.get(this.resultboxPanelContent.childNodes[i], "CarouselPodName");
                        // Checking container pod's
                        if (carouselPodAttributeValue.toLowerCase() === carouselPodData[j].toLowerCase()) {
                            this.resultboxPanelContent.removeChild(this.resultboxPanelContent.childNodes[i]);
                        }
                    }
                }
            }
        },

        /**
        * Clear entire pod from carousel container
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        removeAllPod: function () {
            var i, j, childNodesArray = [];
            // Checking pod's container if available then remove children
            if (this.resultboxPanelContent && this.resultboxPanelContent.childNodes) {
                // Looping for result content data
                for (i = 0; i < this.resultboxPanelContent.childNodes.length; i++) {
                    childNodesArray.push(this.resultboxPanelContent.childNodes[i]);
                }
                // Looping for container data
                for (j = 0; j < childNodesArray.length; j++) {
                    this.resultboxPanelContent.removeChild(childNodesArray[j]);
                }
            }
        },

        /**
        * Show  carousel container
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        showCarouselContainer: function () {
            // Checking if pod is created and container is present.
            if (this.divCarouselContent && this.isPodCreated > 0) {
                domStyle.set(this.divImageBackground, "display", "block");
                domStyle.set(this.divCarouselContent, "display", "block");
                domStyle.set(this.divToggle, "display", "block");
                // Checking for legend position.
                if (query('.esriCTDivLegendBox')[0]) {
                    domClass.add(query('.esriCTDivLegendBox')[0], "esriCTDivLegendBoxTop");
                }
            }
        },

        /**
        * Hide  carousel container
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        hideCarouselContainer: function () {
            var customLogoPositionChange;
            if (this.divCarouselContent) {
                domStyle.set(this.divImageBackground, "display", "none");
                domStyle.set(this.divCarouselContent, "display", "none");
                domStyle.set(this.divToggle, "display", "none");
                // If legend is available then set legend position
                if (query('.esriCTDivLegendBox')[0]) {
                    domClass.remove(query('.esriCTDivLegendBox')[0], "esriCTDivLegendBoxTop");
                }
                customLogoPositionChange = query('.esriCTCustomMapLogo');
                if (customLogoPositionChange[0]) {
                    // if ShowLegend is True than replace classes having different positions of customLogo from bottom else default position
                    if (appGlobals.configData.ShowLegend) {
                        domClass.replace(customLogoPositionChange[0], "esriCTCustomMapLogoBottom", "esriCTCustomMapLogoPostionChange");
                    } else {
                        domClass.remove(customLogoPositionChange[0], "esriCTCustomMapLogoPostion");
                    }
                }
            }
        },

        /**
        * collapse container up
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        expandCarousel: function () {
            var customLogoPositionChange;
            appGlobals.shareOptions.isShowPod = "true";
            // Checking the container data
            if (this.isPodCreated > 0) {
                domStyle.set(this.divCarouselContent, "display", "block");
                this._setLegendPositionUp();
                domClass.add(this.divImageBackground, "esriCTResultImageBlock");
                domClass.replace(this.divCarouselContent, "esriCTBottomPanelHeight", "esriCTzeroHeight");
                domClass.replace(this.imgToggleResults, "esriCTDownDownarrowImage", "esriCTUparrowImage");
                domClass.replace(this.divToggle, "esriCTBottomPanelPosition", "esriCTZeroBottom");
                this.imgToggleResults.title = sharedNls.tooltips.hidePanelTooltip;
                customLogoPositionChange = query('.esriCTCustomMapLogo');
                if (customLogoPositionChange[0]) {
                    // if ShowLegend is True than replace classes having different positions of customLogo from bottom else add the class
                    if (appGlobals.configData.ShowLegend) {
                        domClass.replace(customLogoPositionChange[0], "esriCTCustomMapLogoPostionChange", "esriCTCustomMapLogoBottom");
                    } else {
                        domClass.add(customLogoPositionChange[0], "esriCTCustomMapLogoPostion");
                    }
                }
            }
        },

        /**
        * collapse container bellow
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        collapseCarousel: function () {
            var customLogoPositionChange;
            appGlobals.shareOptions.isShowPod = "false";
            if (this.isPodCreated > 0) {
                domStyle.set(this.divCarouselContent, "display", "none");
                domClass.replace(this.divCarouselContent, "esriCTzeroHeight", "esriCTBottomPanelHeight");
                domClass.replace(this.imgToggleResults, "esriCTUparrowImage", "esriCTDownDownarrowImage");
                domClass.replace(this.divToggle, "esriCTZeroBottom", "esriCTBottomPanelPosition");
                this.imgToggleResults.title = sharedNls.tooltips.showPanelTooltip;
                this._setLegendPositionDown();
                customLogoPositionChange = query('.esriCTCustomMapLogo');
                if (customLogoPositionChange[0]) {
                    // if ShowLegend is True than replace classes having different positions of customLogo from bottom else default poition
                    if (appGlobals.configData.ShowLegend) {
                        domClass.replace(customLogoPositionChange[0], "esriCTCustomMapLogoBottom", "esriCTCustomMapLogoPostionChange");
                    } else {
                        domClass.remove(customLogoPositionChange[0], "esriCTCustomMapLogoPostion");
                    }
                }
            }
        },

        /**
        * set position of legend box and esri logo when container is hide
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        _setLegendPositionDown: function () {
            var legendChangePositionDownContainer, mapLogoPostionDown;
            legendChangePositionDownContainer = query('.esriCTDivLegendBox')[0];
            if (legendChangePositionDownContainer) {
                domClass.remove(legendChangePositionDownContainer, "esriCTDivLegendBoxUp");
            }
            mapLogoPostionDown = query('.esriControlsBR')[0];
            // if class 'esriCTDivMapPoitionUp' exists then only add or remove classes
            if (query('.esriCTDivMapPositionUp')[0]) {
                domClass.remove(mapLogoPostionDown, "esriCTDivMapPositionUp");
                domClass.add(mapLogoPostionDown, "esriCTDivMapPositionTop");
            }
        },

        /**
        * set position of legend box and esri logo when container is show
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        _setLegendPositionUp: function () {
            var legendChangePositionDownContainer, mapLogoPostionDown;
            legendChangePositionDownContainer = query('.esriCTDivLegendBox')[0];
            // Checking for legend position
            if (legendChangePositionDownContainer) {
                domClass.add(legendChangePositionDownContainer, "esriCTDivLegendBoxUp");
            }
            mapLogoPostionDown = query('.esriControlsBR')[0];
            domClass.remove(mapLogoPostionDown, "esriCTDivMapPositionTop");
            domClass.add(mapLogoPostionDown, "esriCTDivMapPositionUp");
        },

        /**
        * set carousel collapse up and down
        * @memberOf widgets/carouselContainer/carouselContainer
        */
        _toggleCarouselPod: function () {
            // Checking condition if pod is added in container
            if (this.isPodCreated > 0) {
                // Checking the class for expanding and collapsing the container.
                if (domClass.contains(this.divCarouselContent, "esriCTzeroHeight")) {
                    this.expandCarousel();
                    appGlobals.shareOptions.isShowPod = "true";
                } else {
                    this.collapseCarousel();
                    appGlobals.shareOptions.isShowPod = "false";
                }
            }
        }
    });
});
