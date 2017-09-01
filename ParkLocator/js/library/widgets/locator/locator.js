/*global define,dojo,dojoConfig,alert,esri,locatorParams,appGlobals */
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
    "dojo/_base/array",
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/dom-class",
    "dojo/dom-construct",
    "dojo/dom-geometry",
    "dojo/dom-style",
    "dojo/keys",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/on",
    "dojo/query",
    "dojo/string",
    "dojo/text!./templates/locatorTemplate.html",
    "dojo/topic",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetBase",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/Deferred",
    "dojo/promise/all",
    "esri/geometry/Point",
    "esri/graphic",
    "esri/layers/GraphicsLayer",
    "esri/symbols/PictureMarkerSymbol",
    "esri/tasks/GeometryService",
    "esri/tasks/locator",
    "esri/tasks/query",
    "esri/tasks/QueryTask",
    "dijit/a11yclick",
    "dijit/form/HorizontalSlider",
    "dijit/form/HorizontalRule"
], function (Array, declare, lang, dom, domAttr, domClass, domConstruct, domGeom, domStyle, keys, sharedNls, on, query, string, template, topic, _TemplatedMixin, _WidgetBase, _WidgetsInTemplateMixin, Deferred, all, Point, Graphic, GraphicsLayer, PictureMarkerSymbol, GeometryService, Locator, Query, QueryTask, a11yclick, HorizontalSlider, HorizontalRule) {
    //========================================================================================================================//

  return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,                                // Variable for template string
        sharedNls: sharedNls,                                    // Variable for shared NLS
        lastSearchString: null,                                  // Variable for last search string
        stagedSearch: null,                                      // Variable for staged search
        preLoaded: true,                                         // Variable for loading the locator widget
        isShowDefaultPushPin: true,                              // Variable to show the default pushpin on map
        selectedGraphic: null,                                   // Variable for selected graphic
        graphicsLayerId: null,                                   // Variable for storing search settings
        configSearchSettings: null,
        _sliderCollection: [], //TODO...JH should be able to remove this as this app only needs one slider
        unitValues: [null, null, null, null],
        _mapClickHandler: null,                                  // Map click handler
        _mapMoveHandler: null,                                   // Map move handler
        _mapTooltip: null,                                       // MapTooltip Container

        /**
        * display locator widget
        * @class
        * @name widgets/locator/locator
        * @method postCreate
        * @return
        */

        postCreate: function () {
            var graphicsLayer;
            /**
            * close locator widget if any other widget is opened
            * @param {string} widget Key of the newly opened widget
            */
            // variable is check if locator widget is loading in another file
            if (this.preLoaded) {
                topic.subscribe("toggleWidget", lang.hitch(this, function (widget) {
                    if (widget !== "locator") {
                        if (domGeom.getMarginBox(this.divAddressContainer).h > 0) {
                            domClass.replace(this.domNode, "esriCTHeaderSearch", "esriCTHeaderSearchSelected");
                            domClass.replace(this.divAddressContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
                            this.txtAddress.blur();
                        }
                    }
                }));
                this.parentDomNode = dom.byId("esriCTParentDivContainer");
                this.domNode = domConstruct.create("div", { "title": sharedNls.tooltips.search, "class": "esriCTHeaderIcons esriCTHeaderSearch" }, null);
                this.own(on(this.domNode, a11yclick, lang.hitch(this, function () {
                    this._toggleTexBoxControls(false);
                    this.onLocateButtonClick();
                    /**
                    * minimize other open header panel widgets and show locator widget
                    */
                    topic.publish("toggleWidget", "locator");
                    this._showHideLocateContainer();
                })));
                this.locatorSettings = appGlobals.configData.LocatorSettings;
                this.defaultAddress = this.locatorSettings.LocatorDefaultAddress;
                domConstruct.place(this.divAddressContainer, this.parentDomNode);
            } else {
                domConstruct.place(this.divAddressContainer.children[0], this.parentDomNode);
            }
            // verify the graphic layer
            if (!this.graphicsLayerId) {
                this.graphicsLayerId = "locatorGraphicsLayer";
                if (Array.indexOf(this.map.graphicsLayerIds, this.graphicsLayerId) !== -1) {
                    this.graphicsLayerId += this.map.graphicsLayerIds.length;
                }
                graphicsLayer = new GraphicsLayer();
                graphicsLayer.id = this.graphicsLayerId;
                this.map.addLayer(graphicsLayer);
            }
            this._setDefaultTextboxValue(this.txtAddress, "defaultAddress", this.defaultAddress);
            this.txtAddress.value = domAttr.get(this.txtAddress, "defaultAddress");
            this.lastSearchString = lang.trim(this.txtAddress.value);
            if (typeof (this.resetBufferDistance) === 'undefined') {
                this.resetBufferDistance = true;
              this._setBufferDistance();
            }

            //create tool-tip to be shown on map move
            this._mapTooltip = domConstruct.create("div", {
              "class": "tooltip",
              "innerHTML": sharedNls.tooltips.addPoint
            }, this.map.container);
            domStyle.set(this._mapTooltip, "position", "fixed");
            domStyle.set(this._mapTooltip, "display", "none");

            this._attachLocatorEvents();

            // Subscribe function to clear graphics from map
            topic.subscribe("clearLocatorGraphicsLayer", this._clearGraphics);
        },

        /**
        * set buffer distance in all workflows and create horizontal slider for different workflows
        * @memberOf widgets/siteLocator/siteLocator
        */
        _setBufferDistance: function () {
            var bufferDistance = null;
            // check the shared URL for "bufferDistance" to create buffer on map
            if (window.location.toString().split("$bufferDistance=").length > 1) {
                bufferDistance = Number(window.location.toString().split("$bufferDistance=")[1].toString().split("$")[0]);
                appGlobals.shareOptions.bufferDistance = bufferDistance;
            }

            var sliderId = "slider" + domAttr.get(this.horizontalSliderContainer, "data-dojo-attach-point");
            var sn = dom.byId(sliderId);
            if (!sn) {
                this._createHorizontalSlider(this.horizontalSliderContainer, this.horizontalRuleContainer, this.bufferSliderText, bufferDistance);
            }
        },

      /**
      * get distance unit based on unit selection
      * @param {string} input distance unit
      * @memberOf widgets/locator/locator
      */
        _getDistanceUnit: function (strUnit) {
            var sliderUnitValue;
            if (strUnit.toLowerCase() === "miles") {
                sliderUnitValue = "UNIT_STATUTE_MILE";
            } else if (strUnit.toLowerCase() === "feet") {
                sliderUnitValue = "UNIT_FOOT";
            } else if (strUnit.toLowerCase() === "meters") {
                sliderUnitValue = "UNIT_METER";
            } else if (strUnit.toLowerCase() === "kilometers") {
                sliderUnitValue = "UNIT_KILOMETER";
            } else {
                sliderUnitValue = "UNIT_STATUTE_MILE";
            }
            return sliderUnitValue;
        },

      /**
      * create horizontal slider for all tab and set minimum maximum value of slider
      * @param container node, horizontal rule node and slider value
      * @memberOf widgets/locator/locator
      */
        _createHorizontalSlider: function (sliderContainer, horizontalRuleContainer, divSliderValue, bufferDistance) {
            var _self, horizontalSlider, sliderId, horizontalRule, sliderInstance = {};
            sliderId = "slider" + domAttr.get(sliderContainer, "data-dojo-attach-point");
            horizontalRule = new HorizontalRule({
                "class": "horizontalRule"
            }, horizontalRuleContainer);
            horizontalRule.domNode.firstChild.style.border = "none";
            horizontalRule.domNode.lastChild.style.border = "none";
            horizontalRule.domNode.lastChild.style.right = "0" + "px";
            if (!bufferDistance) {
                if (appGlobals.configData.DistanceUnitSettings.MinimumValue >= 0) {
                    bufferDistance = appGlobals.configData.DistanceUnitSettings.MinimumValue;
                } else {
                    bufferDistance = 0;
                    appGlobals.configData.DistanceUnitSettings.MinimumValue = bufferDistance;
                }
            }
            horizontalSlider = new HorizontalSlider({
                intermediateChanges: false,
                "class": "horizontalSlider",
                minimum: appGlobals.configData.DistanceUnitSettings.MinimumValue,
                maximum: appGlobals.configData.DistanceUnitSettings.MaximumValue,
                value: bufferDistance,
                id: sliderId,
                showButtons: false
            }, sliderContainer);
            sliderInstance.id = 0;
            sliderInstance.slider = horizontalSlider;
            sliderInstance.divSliderValue = divSliderValue;
            this._sliderCollection.push(sliderInstance);
            horizontalSlider.tabCount = 0;
            appGlobals.shareOptions.bufferDistance = bufferDistance;
            this.unitValues[0] = this._getDistanceUnit(appGlobals.configData.DistanceUnitSettings.DistanceUnitName);
            if (appGlobals.configData.DistanceUnitSettings.MaximumValue > 0) {
                horizontalRule.domNode.lastChild.innerHTML = appGlobals.configData.DistanceUnitSettings.MaximumValue;
                horizontalSlider.maximum = appGlobals.configData.DistanceUnitSettings.MaximumValue;
            } else {
                horizontalRule.domNode.lastChild.innerHTML = 1000;
                horizontalSlider.maximum = 1000;
            }
            if (appGlobals.configData.DistanceUnitSettings.MinimumValue >= 0) {
                horizontalRule.domNode.firstChild.innerHTML = appGlobals.configData.DistanceUnitSettings.MinimumValue;
            } else {
                horizontalRule.domNode.firstChild.innerHTML = 0;
            }

            domStyle.set(horizontalRule.domNode.lastChild, "text-align", "right");
            domStyle.set(horizontalRule.domNode.lastChild, "width", "98%");
            domStyle.set(horizontalRule.domNode.lastChild, "left", "0");
            domAttr.set(divSliderValue, "distanceUnit", appGlobals.configData.DistanceUnitSettings.DistanceUnitName.toString());
            domAttr.set(divSliderValue, "innerHTML", horizontalSlider.value.toString() + " " + appGlobals.configData.DistanceUnitSettings.DistanceUnitName);
            _self = this;

            /**
            * call back for slider change event
            * @param {object} slider value
            * @memberOf widgets/locator/locator
            */
            on(horizontalSlider, "change", function (value) {
                if (Number(value) > Number(horizontalSlider.maximum)) {
                    horizontalSlider.setValue(horizontalSlider.maximum);
                }
                domAttr.set(divSliderValue, "innerHTML", Math.round(value) + " " + domAttr.get(divSliderValue, "distanceUnit"));
                setTimeout(function () {
                    if (_self.resetBufferDistance) {
                        if (_self.mapPoint) {
                            appGlobals.shareOptions.bufferDistance = value;
                            _self._locateAddressOnMap(_self.mapPoint, "slider");
                        }
                    }
                    appGlobals.shareOptions.bufferDistance = Math.round(value);
                }, 500);
            });

            //var sc = dom.byId("sliderContainer");
            //domStyle.set(sc, "display", "block");

            var addP = dom.byId("divSelectLocation");
            domStyle.set(addP, "display", "table-cell");
        },

        _disconnectMapEventHandler: function () {
            domClass.replace(this.selectLocation, "esriCTImgButtons", "esriCTImgButtonsActive");
            if (this._mapClickHandler) {
                this._mapClickHandler.remove();
            }
            if (this._mapMoveHandler) {
                this._mapMoveHandler.remove();
                this._mapTooltip.style.display = "none";
            }
            this._enableWebMapPopup();
        },

        _onMapClick: function (evt) {
            topic.publish("setInfoShow", true);
            this.mapPoint = evt.mapPoint;
            this.selectedLayerTitle = null;
            this._locateAddressOnMap(evt.mapPoint, "click");
            appGlobals.shareOptions.address = { geometry: this.mapPoint };
            this._disconnectMapEventHandler();
            topic.publish("setInfoShow", false);
        },

        _enableWebMapPopup: function () {
            if (this.map) {
                this.map.setInfoWindowOnClick(true);
            }
        },

        _disableWebMapPopup: function () {
            if (this.map) {
                this.map.setInfoWindowOnClick(false);
            }
        },

        /**
        * Store search settings in an array if the layer url for that particular setting is available
        * @memberOf widgets/locator/locator
        */
        _setSearchSettings: function () {
            var i;
            this.configSearchSettings = [];
            for (i = 0; i < appGlobals.configData.SearchSettings.length; i++) {
                if (appGlobals.configData.SearchSettings[i].QueryURL) {
                    this.configSearchSettings.push(appGlobals.configData.SearchSettings[i]);
                }
            }
        },

      /**
      * This function will connects the map event
      * @memberOf widgets/locator/locator
      **/
        _connectMapEventHandler: function () {
            topic.publish("setInfoShow", true);
            this._disableWebMapPopup();
            domClass.replace(this.selectLocation, "esriCTImgButtonsActive", "esriCTImgButtons");
            this._mapClickHandler = this.map.on("click", lang.hitch(this, this._onMapClick));
            this._mapMoveHandler = this.map.on("mouse-move", lang.hitch(this, this._onMapMouseMove));
        },

        /**
        * On map mouse move update the "add point" toolTip position
        * @memberOf widgets/locator/locator
        **/
        _onMapMouseMove: function (evt) {
            // update the tooltip as the mouse moves over the map
            var px, py;
            if (evt.clientX || evt.pageY) {
                px = evt.clientX;
                py = evt.clientY;
            } else {
                px = evt.clientX + document.body.scrollLeft -
                  document.body.clientLeft;
                py = evt.clientY + document.body.scrollTop - document
                  .body.clientTop;
            }
            domStyle.set(this._mapTooltip, "display", "none");
            domStyle.set(this._mapTooltip, {
                left: (px + 15) + "px",
                top: (py) + "px"
            });
            domStyle.set(this._mapTooltip, "display", "");
        },

        /**
        * Set default value in search textbox as specified in configuration file
        * @param {node} node
        * @param {object} attribute
        * @param {string} value
        * @memberOf widgets/locator/locator
        */
        _setDefaultTextboxValue: function (node, attribute, value) {
            domAttr.set(node, attribute, value);
        },

        /**
        * Attach locator events in this function
        * @memberOf widgets/locator/locator
        */
        _attachLocatorEvents: function () {
            domAttr.set(this.imgSearchLoader, "src", dojoConfig.baseURL + "/js/library/themes/images/loader.gif");
            this.own(on(this.divSearch, a11yclick, lang.hitch(this, function () {
                this._toggleTexBoxControls(true);
                this._locateAddress(true);
            })));
            this.own(on(this.txtAddress, "keyup", lang.hitch(this, function (evt) {
                this._submitAddress(evt);
            })));
            this.own(on(this.txtAddress, "paste", lang.hitch(this, function (evt) {
                this._submitAddress(evt, true);
            })));
            this.own(on(this.txtAddress, "cut", lang.hitch(this, function (evt) {
                this._submitAddress(evt, true);
            })));
            this.own(on(this.txtAddress, "dblclick", lang.hitch(this, function (evt) {
                // Double-click functions like the "X" button: it clears the text box contents
                // and any proposed search results
                this._clearSearchTextbox();
                this._cancelPendingSearches();
                this._clearProposedSearchResults();
            })));
            this.own(on(this.txtAddress, "focus", lang.hitch(this, function () {
                domClass.add(this.txtAddress, "esriCTColorChange");
            })));
            this.own(on(this.close, a11yclick, lang.hitch(this, function () {
                // Clear the search text box and any proposed search results
                this._clearSearchTextbox();
                this._cancelPendingSearches();
                this._clearProposedSearchResults();

                // When used in the dropdown search's text box, clear everything
                if (this.extendedClear) {
                    this._clearResults();
                }
            })));
            this.own(on(this.selectLocation, a11yclick, lang.hitch(this, function () {
              if (domClass.contains(this.selectLocation, "esriCTImgButtonsActive")) {
                this._disconnectMapEventHandler();
                domStyle.set(this.sliderContainer, "display", "none");
              } else {
                this._connectMapEventHandler();
                domStyle.set(this.sliderContainer, "display", "block");
              }
            })));
        },

        /**
        * Hide value from search textbox
        * @memberOf widgets/locator/locator
        */
        _clearResults: function () {
          topic.publish("removeBuffer");
          topic.publish("clearGraphicsAndCarousel");
          topic.publish("removeRouteGraphichOfDirectionWidget");
          this.mapPoint = null;

            //clear share results
          appGlobals.shareOptions.bufferDistance = null;
          appGlobals.shareOptions.address = null;
          appGlobals.shareOptions.sharedGeolocation = null;
          appGlobals.shareOptions.addressLocation = null;
          appGlobals.shareOptions.searchSettingsDetails = null;
          appGlobals.shareOptions.doQuery = false;
          appGlobals.shareOptions.selectedMapPoint = null;
          appGlobals.shareOptions.screenPoint = null;
          appGlobals.shareOptions.mapClickedPoint = null;
          appGlobals.shareOptions.isShowPod = false;
          appGlobals.shareOptions.isActivitySearch = null;
        },

        /**
        * Handle locate button click
        * @memberOf widgets/locator/locator
        */
        onLocateButtonClick: function () {
            // executed when user clicks on the locate button
            return true;
        },

        /**
        * Clears the search textbox
        * @memberOf widgets/locator/locator
        */
        _clearSearchTextbox: function () {
            this.txtAddress.value = "";
            this.lastSearchString = lang.trim(this.txtAddress.value);
        },

        /**
        * Clears the proposed results in the search textbox
        * @memberOf widgets/locator/locator
        */
        _cancelPendingSearches: function () {
            // Indicate that any pending searches are now obsolete
            this.lastSearchTime = (new Date()).getTime();
            this._toggleTexBoxControls(false);
        },

        /**
        * Clears the proposed results in the search textbox
        * @memberOf widgets/locator/locator
        */
        _clearProposedSearchResults: function () {
            // Hide any extant proposed search results
            domStyle.set(this.sliderContainer, "display", "none");
            domStyle.set(this.divActivityList, "display", "none");
            domStyle.set(this.addressHR, "display", "none");

            domConstruct.empty(this.divAddressResults);
            domConstruct.empty(this.divActivityResults);
            domClass.remove(this.divAddressContainer, "esriCTAddressContentHeight");
        },

        /**
        * Show/hide locator widget and set default search text
        * @memberOf widgets/locator/locator
        */
        _showHideLocateContainer: function () {
            this.txtAddress.blur();
            if (domGeom.getMarginBox(this.divAddressContainer).h > 1) {
                /**
                * when user clicks on locator icon in header panel, close the search panel if it is open
                */
                this._hideAddressContainer();
            } else {
                /**
                * when user clicks on locator icon in header panel, open the search panel if it is closed
                */
                domClass.replace(this.domNode, "esriCTHeaderSearchSelected", "esriCTHeaderSearch");
                domClass.replace(this.txtAddress, "esriCTBlurColorChange", "esriCTColorChange");
                domClass.replace(this.divAddressContainer, "esriCTShowContainerHeight", "esriCTHideContainerHeight");
                domStyle.set(this.txtAddress, "verticalAlign", "middle");
                this.txtAddress.value = domAttr.get(this.txtAddress, "defaultAddress");
                this.lastSearchString = lang.trim(this.txtAddress.value);
            }
        },

        /**
        * Search address on every key press
        * @param {object} evt Keyup event
        * @param {string} locatorText
        * @memberOf widgets/locator/locator
        */
        _submitAddress: function (evt, locatorText) {
            if (locatorText) {
                setTimeout(lang.hitch(this, function () {
                    this._locateAddress(true);
                }), 100);
                return;
            }
            // check the keypress event
            if (evt) {
                /**
                * Enter key immediately starts search
                */
                if (evt.keyCode === keys.ENTER) {
                    this._toggleTexBoxControls(true);
                    this._locateAddress(true);
                    return;
                }
                /**
                * do not perform auto complete search if control &| alt key pressed, except for ctrl-v
                */
                if (evt.ctrlKey || evt.altKey || evt.keyCode === keys.UP_ARROW || evt.keyCode === keys.DOWN_ARROW ||
                        evt.keyCode === keys.LEFT_ARROW || evt.keyCode === keys.RIGHT_ARROW ||
                        evt.keyCode === keys.HOME || evt.keyCode === keys.END ||
                        evt.keyCode === keys.CTRL || evt.keyCode === keys.SHIFT) {
                    evt.cancelBubble = true;
                    if (evt.stopPropagation) {
                        evt.stopPropagation();
                    }
                    this._toggleTexBoxControls(false);
                    return;
                }

                /**
                * call locator service if search text is not empty
                */

                this._locateAddress(false);
            }
        },

        /**
        * Perform search by address if search type is address search
        * @memberOf widgets/locator/locator
        */
        _locateAddress: function (launchImmediately) {
            var searchText = lang.trim(this.txtAddress.value).replace(/'/g, "''");
            if (launchImmediately || this.lastSearchString !== searchText) {
                this._toggleTexBoxControls(true);
                this.lastSearchString = searchText;

                // Clear any staged search
                clearTimeout(this.stagedSearch);

                // Hide existing results
                domConstruct.empty(this.divAddressResults);
                domConstruct.empty(this.divActivityResults);
                /**
                * stage a new search, which will launch if no new searches show up
                * before the timeout
                */
                this.stagedSearch = setTimeout(lang.hitch(this, function () {
                    var thisSearchTime;

                    // Replace the close button in search textbox with search loader icon
                    this._toggleTexBoxControls(false);
                    // Launch a search after recording when the search began
                    this.lastSearchTime = thisSearchTime = (new Date()).getTime();
                    this._searchLocation(searchText, thisSearchTime);
                }), (launchImmediately ? 0 : 500));
            }
        },

        /**
        * Query geocoder service and store search results in an array
        * @memberOf widgets/locator/locator
        */
        _searchLocation: function (searchText, thisSearchTime) {
            var nameArray = {}, locatorSettings, locator, searchFieldName, addressField, baseMapExtent, options, searchFields, addressFieldValues, s, deferredArray,
                deferred, resultLength, index, resultAttributes, key, order, basemapId, selectedBasemap = appGlobals.configData.BaseMapLayers[appGlobals.shareOptions.selectedBasemapIndex];

            // Discard searches made obsolete by new typing from user
            if (thisSearchTime < this.lastSearchTime) {
                return;
            }

            // Short-circuit and clear results if the search string is empty
            if (searchText === "") {

                this._toggleTexBoxControls(true);
                this.mapPoint = null;
                this._locatorErrBack(true);

            } else {
                nameArray[this.locatorSettings.DisplayText] = [];
                domAttr.set(this.txtAddress, "defaultAddress", searchText);

                // Set up locator service specified in configuration file
                locatorSettings = this.locatorSettings;
                locator = new Locator(locatorSettings.LocatorURL);
                searchFieldName = locatorSettings.LocatorParameters.SearchField;
                addressField = {};
                addressField[searchFieldName] = searchText;
                //get full extent of selected basemap
                if (selectedBasemap.length) {
                    basemapId = selectedBasemap[0].BasemapId;
                } else {
                    basemapId = selectedBasemap.BasemapId;
                }
                if (this.map.getLayer(basemapId)) {
                    baseMapExtent = this.map.getLayer(basemapId).fullExtent;
                }
                options = {};
                options.address = addressField;
                options.outFields = locatorSettings.LocatorOutFields;
                options[locatorSettings.LocatorParameters.SearchBoundaryField] = baseMapExtent;
                locator.outSpatialReference = this.map.spatialReference;
                searchFields = [];
                addressFieldValues = locatorSettings.FilterFieldValues;
                for (s in addressFieldValues) {
                    if (addressFieldValues.hasOwnProperty(s)) {
                        searchFields.push(addressFieldValues[s]);
                    }
                }

                // Discard searches made obsolete by new typing from user
                if (thisSearchTime < this.lastSearchTime) {
                    return;
                }

                // Launch the searches
                deferredArray = [];
                if (!this.configSearchSettings || !this.preLoaded) {
                    this._setSearchSettings();
                }

                //     -- all configured feature layers
                for (index = 0; index < this.configSearchSettings.length; index++) {
                    this._layerSearchResults(searchText, deferredArray, this.configSearchSettings[index]);
                }

                //     -- address locator
                deferredArray.push(locator.addressToLocations(options));

                // When all searches are done, interpret and display the results
                all(deferredArray).then(lang.hitch(this, function (result) {
                    var num, results;
                    // Discard searches made obsolete by new typing from user
                    if (thisSearchTime < this.lastSearchTime) {
                        return;
                    }
                    if (result) {
                        if (result.length > 0) {
                            for (num = 0; num < result.length; num++) {
                                if (result[num]) {

                                    // Handle feature layer result--an object containing property 'layerSearchSettings'
                                    if (result[num].layerSearchSettings) {
                                        key = result[num].layerSearchSettings.SearchDisplayTitle;
                                        nameArray[key] = [];

                                        if (result[num].featureSet && result[num].featureSet.features) {
                                            resultLength = result[num].featureSet.features.length;
                                            for (order = 0; order < resultLength; order++) {
                                                resultAttributes = result[num].featureSet.features[order].attributes;
                                                for (results in resultAttributes) {
                                                    if (resultAttributes.hasOwnProperty(results)) {
                                                        if (!resultAttributes[results]) {
                                                            resultAttributes[results] = appGlobals.configData.ShowNullValueAs;
                                                        }
                                                    }
                                                }
                                                if (nameArray[key].length < this.locatorSettings.MaxResults) {
                                                    nameArray[key].push({
                                                        name: string.substitute(result[num].layerSearchSettings.SearchDisplayFields, resultAttributes),
                                                        attributes: resultAttributes,
                                                        fields: result[num].featureSet.fields,
                                                        layer: result[num].layerSearchSettings,
                                                        geometry: result[num].featureSet.features[order].geometry
                                                    });
                                                }
                                            }
                                        }

                                    // Handle address locator result--an array
                                    } else if (result[num].length) {
                                        this._addressResult(result[num], nameArray, searchFields);
                                    }
                                }
                            }

                            // Show combined results
                            this._showSearchResults(nameArray);
                        } else {
                        }
                    } else {
                        this.mapPoint = null;
                        this._locatorErrBack(true);
                    }
                }));
            }
        },

        /**
        * Query the layers having search settings configured in the config file
        * @param {array} deferredArray
        * @param {object} layerobject
        * @memberOf widgets/locator/locator
        */
        _layerSearchResults: function (searchText, deferredArray, layerobject) {
            var queryTask, queryLayer, deferred, currentTime, featureObject;
            this._toggleTexBoxControls(true);
            if (layerobject.QueryURL) {
                deferred = new Deferred();
                if (layerobject.UnifiedSearch.toLowerCase() === "true") {
                    currentTime = new Date();
                    queryTask = new QueryTask(layerobject.QueryURL);
                    queryLayer = new Query();
                    queryLayer.where = string.substitute(layerobject.SearchExpression, [searchText.toUpperCase()]) + " AND " + currentTime.getTime().toString() + "=" + currentTime.getTime().toString();
                    queryLayer.outSpatialReference = this.map.spatialReference;
                    queryLayer.returnGeometry = layerobject.ObjectID ? false : true;
                    queryLayer.outFields = ["*"];
                    queryTask.execute(queryLayer, lang.hitch(this, function (featureSet) {
                        featureObject = {
                            "featureSet": featureSet,
                            "layerSearchSettings": layerobject
                        };
                        deferred.resolve(featureObject);
                    }), function (err) {
                        alert(err.message);
                        deferred.resolve();
                    });
                } else {
                    deferred.resolve();
                }
                deferredArray.push(deferred);
            }
        },

        /**
        * Grouping search results
        * @param {object} candidates contains the search data
        * @param {array} nameArray
        * @param {field} searchFields
        * @memberOf widgets/locator/locator
        */
        _addressResult: function (candidates, nameArray, searchFields) {
            var order, j;
            for (order = 0; order < candidates.length; order++) {
                if (candidates[order].attributes[this.locatorSettings.AddressMatchScore.Field] > this.locatorSettings.AddressMatchScore.Value) {
                    for (j in searchFields) {
                        if (searchFields.hasOwnProperty(j)) {
                            if (candidates[order].attributes[this.locatorSettings.FilterFieldName] === searchFields[j]) {
                                if (nameArray[this.locatorSettings.DisplayText].length < this.locatorSettings.MaxResults) {
                                    nameArray[this.locatorSettings.DisplayText].push({
                                        name: string.substitute(this.locatorSettings.DisplayField, candidates[order].attributes),
                                        attributes: candidates[order]
                                    });
                                }
                            }
                        }
                    }
                }
            }
        },

        /**
        * Filter and display valid results from results returned by feature layers and locator service.
        * @param {object} candidates Object with a tag for results from each feature layer and locator service searched
        * @memberOf widgets/locator/locator
        */
        _showSearchResults: function (candidates) {
            var addrListCount = 0, candidatesCount = 0, addrList = [], candidateTitle, candidateArray, title,
                divAddressCounty, candidate, listContainer, i, divAddressSearchCell, searchSettings, activityTitles;

            domConstruct.empty(this.divAddressResults);
            domConstruct.empty(this.divActivityResults);

            // Get the unique titles of all enabled activities and events for grouping results
            activityTitles = [];

            searchSettings = appGlobals.configData.ActivitySearchSettings;
            for (i = 0; i < searchSettings.length; i++) {
                if (searchSettings[i].Enable) {
                    title = searchSettings[i].SearchDisplayTitle;
                    if (activityTitles.indexOf(title) === -1) {
                        activityTitles.push(title);
                        break;
                    }
                }
            }

            searchSettings = appGlobals.configData.EventSearchSettings;
            for (i = 0; i < searchSettings.length; i++) {
                if (searchSettings[i].Enable) {
                    title = searchSettings[i].SearchDisplayTitle;
                    if (activityTitles.indexOf(title) === -1) {
                        activityTitles.push(title);
                        break;
                    }
                }
            }

            /**
            * display all the located address in the address container
            * 'this.divAddressResults' div dom element contains located addresses, created in widget template
            */
            domClass.add(this.divAddressContainer, "esriCTAddressContentHeight");
            this._toggleTexBoxControls(false);
            //domStyle.set(this.divAddressResults, "height", "200px");//JHJH

            for (candidateTitle in candidates) {
                if (candidates.hasOwnProperty(candidateTitle)) {
                    candidateArray = candidates[candidateTitle];

                    if (candidateArray.length > 0) {
                        candidatesCount++;

                      //TODO get the parent div here and check it...

                        var isDefined;
                        var parentNode = this.divActivityResults.offsetParent ?
                            this.divActivityResults.offsetParent : this.divActivityResults.parentNode.parentNode.offsetParent;
                        if (parentNode) {
                            isDefined = parentNode.id === "searchSetting";
                        } else {
                            isDefined = true;
                        }

                        var parentDiv = null;
                        if (activityTitles.indexOf(candidateTitle) > -1) {
                            parentDiv = this.divActivityResults;
                            if (isDefined) {
                              domStyle.set(this.addressHR, "display", "block");
                              domStyle.set(this.esriAddressListContainer, "height", "215px");
                            } else {
                              domStyle.set(this.esriAddressListContainer, "height", "80%");
                            }
                            domStyle.set(this.divAddressResults, "display", "block");

                        } else {
                            parentDiv = this.divAddressResults;
                            if (isDefined) {
                                domStyle.set(this.sliderContainer, "display", "block");
                                domStyle.set(this.esriAddressListContainer, "height", "215px");
                            } else {
                                domStyle.set(this.esriAddressListContainer, "height", "80%");
                            }
                            domStyle.set(this.divActivityList, "display", "block");
                            domStyle.set(this.divActivityResults, "display", "block");
                        }
                        domClass.add(this.esriAddressListContainer, "esriAddressListContainer");
                        divAddressCounty = domConstruct.create("div", {
                            "class": "esriCTSearchGroupRow esriCTBottomBorder esriCTResultColor esriCTCursorPointer esriCTAddressCounty"
                        }, parentDiv);
                        divAddressSearchCell = domConstruct.create("div", { "class": "esriCTSearchGroupCell" }, divAddressCounty);
                        candidate = candidateTitle + " (" + candidateArray.length + ")";
                        domConstruct.create("span", { "innerHTML": "+", "class": "esriCTPlusMinus" }, divAddressSearchCell);
                        domConstruct.create("span", { "innerHTML": candidate, "class": "esriCTGroupList" }, divAddressSearchCell);
                        addrList.push(divAddressSearchCell);
                        this._toggleAddressList(addrList, addrListCount);
                        addrListCount++;
                        listContainer = domConstruct.create("div", { "class": "esriCTListContainer esriCTHideAddressList" }, parentDiv);

                        for (i = 0; i < candidateArray.length; i++) {
                            this._displayValidLocations(candidateArray[i], i, candidateArray, listContainer);
                        }
                    }
                }
            }

            // If no candidates had
            if (candidatesCount === 0) {
                this.mapPoint = null;
                this._locatorErrBack(true);
            }
        },

        /**
        * Show and hide address list
        * @param {array} addressList
        * @param {index} idx
        * @memberOf widgets/locator/locator
        */
        _toggleAddressList: function (addressList, idx) {
            on(addressList[idx], a11yclick, lang.hitch(this, function (evt) {
                var listContainer, listStatusSymbol, resultContainer, t_id;
                resultContainer = (idx === 1) ? this.divActivityResults : this.divAddressResults;
                t_id = (idx === 1) ? 0 : idx;
                listContainer = query(".esriCTListContainer", resultContainer)[t_id];
                if (domClass.contains(listContainer, "esriCTShowAddressList")) {
                    domClass.toggle(listContainer, "esriCTShowAddressList");
                    listStatusSymbol = (domAttr.get(query(".esriCTPlusMinus", evt.currentTarget)[0], "innerHTML") === "+") ? "-" : "+";
                    domAttr.set(query(".esriCTPlusMinus", evt.currentTarget)[0], "innerHTML", listStatusSymbol);
                    return;
                }
                domClass.add(listContainer, "esriCTShowAddressList");
                domAttr.set(query(".esriCTPlusMinus", evt.currentTarget)[0], "innerHTML", "-");
            }));
        },

        /**
        * Display valid results in search panel
        * @param {object} candidate Contains valid result to be displayed in search panel
        * @param {number} index
        * @param {array} candidateArray
        * @param {node} listContainer
        * @memberOf widgets/locator/locator
        */
        _displayValidLocations: function (candidate, index, candidateArray, listContainer) {
            var candidateAddress, divAddressRow, layer, infoIndex;
            divAddressRow = domConstruct.create("div", { "class": "esriCTCandidateList" }, listContainer);
            candidateAddress = domConstruct.create("div", { "class": "esriCTContentBottomBorder esriCTCursorPointer" }, divAddressRow);
            domAttr.set(candidateAddress, "index", index);
            try {
                if (candidate.name) {
                    domAttr.set(candidateAddress, "innerHTML", candidate.name);
                } else {
                    domAttr.set(candidateAddress, "innerHTML", candidate);
                }
                if (candidate.attributes.location) {
                    domAttr.set(candidateAddress, "x", candidate.attributes.location.x);
                    domAttr.set(candidateAddress, "y", candidate.attributes.location.y);
                    domAttr.set(candidateAddress, "address", string.substitute(this.locatorSettings.DisplayField, candidate.attributes.attributes));
                }
            } catch (err) {
                alert(sharedNls.errorMessages.falseConfigParams);
            }

            /**
            * candidate on click of result
            * @param {node} listContainer
            */
            on(candidateAddress, a11yclick, lang.hitch(this, function (evt) {
                var target;
                topic.publish("showProgressIndicator");
                this.txtAddress.value = candidateAddress.innerHTML;
                domAttr.set(this.txtAddress, "defaultAddress", this.txtAddress.value);
                this._hideAddressContainer();
                if (this.isShowDefaultPushPin) {
                    if (candidate.attributes.location) {
                        target = evt.currentTarget || evt.srcElement;
                        this.mapPoint = new Point(Number(domAttr.get(target, "x")), Number(domAttr.get(target, "y")), this.map.spatialReference);
                        this._locateAddressOnMap(this.mapPoint);
                        this.candidateClicked(candidate);
                    } else {
                        if (candidateArray[domAttr.get(candidateAddress, "index", index)]) {
                            layer = candidateArray[domAttr.get(candidateAddress, "index", index)].layer;
                            for (infoIndex = 0; infoIndex < this.configSearchSettings.length; infoIndex++) {
                                if (this.configSearchSettings[infoIndex] && this.configSearchSettings[infoIndex].QueryURL === layer.QueryURL) {
                                    if (!candidate.geometry) {
                                        this._getSelectedCandidateGeometry(layer, candidate);
                                    } else {
                                        this._showFeatureResultsOnMap(candidate);
                                        topic.publish("hideProgressIndicator");
                                        this.candidateClicked(candidate);
                                    }
                                }
                            }
                        }
                    }
                }
            }));
        },

        /**
        * Get geometry of the selected candidate by querying the layer
        * @param {object} layerobject
        * @param {object} candidate
        * @memberOf widgets/locator/locator
        */
        _getSelectedCandidateGeometry: function (layerobject, candidate) {
            var queryTask, queryLayer, currentTime;
            if (layerobject.QueryURL) {
                currentTime = new Date();
                queryTask = new QueryTask(layerobject.QueryURL);
                queryLayer = new Query();
                queryLayer.where = layerobject.ObjectID + " =" + candidate.attributes[layerobject.ObjectID] + " AND " + currentTime.getTime().toString() + "=" + currentTime.getTime().toString();
                queryLayer.outSpatialReference = this.map.spatialReference;
                queryLayer.returnGeometry = true;
                queryTask.execute(queryLayer, lang.hitch(this, function (featureSet) {
                    this._showFeatureResultsOnMap(candidate);
                    candidate.geometry = featureSet.features[0].geometry;
                    this.candidateClicked(candidate);
                    topic.publish("hideProgressIndicator");
                }), function (err) {
                    alert(err.message);
                    topic.publish("hideProgressIndicator");
                });
            }
        },

        /**
        * handler for candidate address click
        * @memberOf widgets/locator/locator
        */
        candidateClicked: function (candidate) {
            // selected address will be returned
            return candidate;
        },

        /**
        * show the feature result on map
        * @param {object} candidate
        * @memberOf widgets/locator/locator
        */
        _showFeatureResultsOnMap: function (candidate) {
            this.txtAddress.value = candidate.name;
        },

        /**
        * Show/hide the close icon and search loader icon present in search textbox
        * @param {boolean} isShow
        * @memberOf widgets/locator/locator
        */
        _toggleTexBoxControls: function (isShow) {
            if (isShow) {
                domStyle.set(this.imgSearchLoader, "display", "block");
                domStyle.set(this.close, "display", "none");
            } else {
                domStyle.set(this.imgSearchLoader, "display", "none");
                domStyle.set(this.close, "display", "block");
            }
        },

        /**
        * Add the pushpin to graphics layer
        * @param {object} mapPoint
        * @memberOf widgets/locator/locator
        */
        _locateAddressOnMap: function (mapPoint, fromEvent) {
            var geoLocationPushpin, locatorMarkupSymbol;
            this._clearGraphics();
            geoLocationPushpin = dojoConfig.baseURL + this.locatorSettings.DefaultLocatorSymbol;
            locatorMarkupSymbol = new PictureMarkerSymbol(geoLocationPushpin, this.locatorSettings.MarkupSymbolSize.width, this.locatorSettings.MarkupSymbolSize.height);
            this.selectedGraphic = new Graphic(mapPoint, locatorMarkupSymbol, {}, null);
            this.map.getLayer(this.graphicsLayerId).add(this.selectedGraphic);
            this.onGraphicAdd(this.selectedGraphic, fromEvent);
            topic.publish("hideProgressIndicator");
        },

        /**
        * Clear graphics from map
        * @memberOf widgets/locator/locator
        */
        _clearGraphics: function () {
            if (this.map.getLayer(this.graphicsLayerId)) {
                this.map.getLayer(this.graphicsLayerId).clear();
            }
            this.selectedGraphic = null;
        },

        /**
        * Handler for adding graphic on map
        * @memberOf widgets/locator/locator
        */
        onGraphicAdd: function () {
            return true;
        },

        /**
        * Hide search panel
        * @memberOf widgets/locator/locator
        */
        _hideAddressContainer: function () {
            domClass.replace(this.domNode, "esriCTHeaderSearch", "esriCTHeaderSearchSelected");
            this.txtAddress.blur();
            domClass.replace(this.divAddressContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
        },

        /**
        * Display error message if locator service fails or does not return any results
        * @memberOf widgets/locator/locator
        */
        _locatorErrBack: function (showMessage) {
            domConstruct.empty(this.divAddressResults);
            //domConstruct.empty(this.divActivityResults);
            domClass.remove(this.divAddressContainer, "esriCTAddressContentHeight");
            domStyle.set(this.divAddressResults, "display", "block");
            domStyle.set(this.divActivityResults, "display", "none");
            domStyle.set(this.sliderContainer, "display", "none");
            domStyle.set(this.addressHR, "display", "none");
            domClass.add(this.divAddressContent, "esriCTAddressResultHeight");
            this._toggleTexBoxControls(false);
            if (showMessage) {
                domConstruct.create("div", { "class": "esriCTDivNoResultFound", "innerHTML": sharedNls.errorMessages.invalidSearch }, this.divAddressResults);
            }
        },

        /**
        * Set default value to search textbox
        * @param {event} evt Blur event
        * @memberOf widgets/locator/locator
        */
        _replaceDefaultText: function (evt) {
            var target = window.event ? window.event.srcElement : evt ? evt.target : null;
            if (!target) {
                return;
            }
            this._resetTargetValue(target, "defaultAddress");
        },

        /**
        * Set default value to search textbox
        * @param {object} target Textbox dom element
        * @param {string} title Default value
        * @memberOf widgets/locator/locator
        */
        _resetTargetValue: function (target, title) {
            if (target.value === '' && domAttr.get(target, title)) {
                target.value = target.title;
                if (target.title === "") {
                    target.value = domAttr.get(target, title);
                }
            }
            if (domClass.contains(target, "esriCTColorChange")) {
                domClass.remove(target, "esriCTColorChange");
            }
            domClass.add(target, "esriCTBlurColorChange");
            this.lastSearchString = lang.trim(this.txtAddress.value);
        }
    });
});
