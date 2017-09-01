/*global define,dojo,dojoConfig:true,alert,console,esri,Modernizr,dijit,appGlobals */
/*jslint browser:true,sloppy:true,nomen:true,unparam:true,plusplus:true,indent:4 */
/** @license
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
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/_base/lang",
    "dojo/on",
    "dojo/_base/array",
    "dojo/dom-class",
    "dojo/window",
    "esri/graphic",
    "dijit/_WidgetBase",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "esri/geometry/Point",
    "esri/geometry/Polyline",
    "esri/geometry/Polygon",
    "dijit/a11yclick",
    "esri/symbols/SimpleLineSymbol",
    "esri/symbols/SimpleMarkerSymbol",
    "esri/symbols/SimpleFillSymbol",
    "dojo/_base/Color",
    "dojo/query"

], function (declare, domConstruct, domStyle, domAttr, lang, on, array, domClass, win, Graphic, _WidgetBase, sharedNls, topic, Point, Polyline, Polygon, a11yclick, SimpleLineSymbol, SimpleMarkerSymbol, SimpleFillSymbol, Color, query) {
    // ========================================================================================================================//

    return declare([_WidgetBase], {
        sharedNls: sharedNls, // Variable for shared NLS

        /** This file has some common function which is used in intire project **/

        /**
        * Remove highlighted symbol graphics from map
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeHighlightedCircleGraphics: function () {
            // Check the highlighted symbol layer id and graphics for removing graphics from map
            if (this.map.getLayer("highlightLayerId") && this.map.getLayer("highlightLayerId").graphics.length > 0) {
                this.map.getLayer("highlightLayerId").clear();
            }
        },

        /**
        * Remove buffer graphics from map
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeBuffer: function () {
            // Check the buffer graphics layer and graphics for removing graphics from map
            if (this.map.getLayer("tempBufferLayer") && this.map.getLayer("tempBufferLayer").graphics.length > 0) {
                this.map.getLayer("tempBufferLayer").clear();
                this.zoomToFullRoute = true;
            }
        },

        /**
        * remove geolocation graphics from map
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeGeolocationPushPin: function () {
            // Check the geolocation layer and graphics  for removing graphics from map
            if (this.map.getLayer(this.geoLocationGraphicsLayerID) && this.map.getLayer(this.geoLocationGraphicsLayerID).graphics.length > 0) {
                this.map.getLayer(this.geoLocationGraphicsLayerID).clear();
            }
        },

        /**
        * Remove Locator's layer graphics from map
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeLocatorPushPin: function () {
            // Check the locator's layer and graphics  for removing graphics from map
            if (this.map.getLayer(this.locatorGraphicsLayerID) && this.map.getLayer(this.locatorGraphicsLayerID).graphics.length > 0) {
                this.map.getLayer(this.locatorGraphicsLayerID).clear();
            }
        },

        /**
        * Remove Graphics of Direction widget
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeRouteGraphichOfDirectionWidget: function () {
            this._esriDirectionsWidget._clearRouteGraphics();
            this._esriDirectionsWidget.removeStops();
            this._esriDirectionsWidget._clearStopGraphics();
        },

        /**
        * Disable InfoPopup Of DirectionWidget
        * @memberOf widgets/commonHelper/commonHelper
        */
        disableInfoPopupOfDirectionWidget: function (dirctionWidgetObject) {
            var i;
            // Checking the direction widget's stop graphics
            if (dirctionWidgetObject.stopGraphics) {
                // Looping for direction widget stop graphic for disabling info window
                for (i = 0; i < dirctionWidgetObject.stopGraphics.length; i++) {
                    dirctionWidgetObject.stopGraphics[i].infoTemplate = null;
                }
            }
        },

        /**
        * Function for clearing graphics and Carousel pod data.
        * @memberOf widgets/commonHelper/commonHelper
        */
        clearGraphicsAndCarousel: function () {
            this.locatorAddress = "";
            this.removeHighlightedCircleGraphics();
            this.removeBuffer();
            this.removeGeolocationPushPin();
            this.removeLocatorPushPin();
            this.carouselContainer.hideCarouselContainer();
            this.carouselContainer._setLegendPositionDown();
        },

        /**
        * Remove null value from the attribute.
        * @param {object} featureObject is object for feature
        * @return {object} featureObject of object for feature without null value
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeNullValue: function (featureObject) {
            var i, j;
            // Checking the feature object
            if (featureObject) {
                // Looping through the feature attributes and setting text configured in NLS file (showNullValue) for attributes with null value
                for (i = 0; i < featureObject.length; i++) {
                    for (j in featureObject[i].attributes) {
                        if (featureObject[i].attributes.hasOwnProperty(j)) {
                            // Assigning text configured in NLS file (showNullValue) to attributes with no value
                            if ((!featureObject[i].attributes[j]) && (featureObject[i].attributes[j] !== 0 || lang.trim(String(featureObject[i].attributes[j])) === "")) {
                                featureObject[i].attributes[j] = appGlobals.configData.ShowNullValueAs;
                            }
                        }
                    }
                }
            }
            return featureObject;
        },

        /**
        * Hide carousel container
        * @memberOf widgets/commonHelper/commonHelper
        */
        hideCarouselContainer: function () {
            // If carousel container is created then collapse it down
            if (this.carouselContainer) {
                this.carouselContainer.collapseCarousel();
            }
        },

        /**
        * Highlight the nearest feature on the map
        * @param{object} featureGeometry contains feature
        * @memberOf widgets/commonHelper/commonHelper
        */
        highlightFeature: function (featureGeometry) {
            var symbol, graphics;
            // Clear the previous highlighted layer graphics
            this.removeHighlightedCircleGraphics();
            // Checks the geometry and setting highlighted symbol on map
            if (featureGeometry) {
                if (featureGeometry.type === "polygon") {
                    symbol = new SimpleFillSymbol(SimpleFillSymbol.STYLE_SOLID, new SimpleLineSymbol(SimpleLineSymbol.STYLE_SOLID, new Color([parseInt(appGlobals.configData.RippleColor.split(",")[0], 10), parseInt(appGlobals.configData.RippleColor.split(",")[1], 10), parseInt(appGlobals.configData.RippleColor.split(",")[2], 10)]), 4), new Color([0, 0, 0, 0]));
                    graphics = new Graphic(new Polygon(featureGeometry), symbol);
                } else if (featureGeometry.type === "polyline") {
                    symbol = new SimpleLineSymbol(SimpleLineSymbol.STYLE_SOLID, new Color([parseInt(appGlobals.configData.RippleColor.split(",")[0], 10), parseInt(appGlobals.configData.RippleColor.split(",")[1], 10), parseInt(appGlobals.configData.RippleColor.split(",")[2], 10)]), 4);
                    graphics = new Graphic(new Polyline(featureGeometry), symbol);
                } else {
                    symbol = new SimpleMarkerSymbol(SimpleMarkerSymbol.STYLE_CIRCLE, appGlobals.configData.LocatorRippleSize, new esri.symbol.SimpleLineSymbol(esri.symbol.SimpleLineSymbol.STYLE_SOLID, new Color([parseInt(appGlobals.configData.RippleColor.split(",")[0], 10), parseInt(appGlobals.configData.RippleColor.split(",")[1], 10), parseInt(appGlobals.configData.RippleColor.split(",")[2], 10)]), 4), new Color([0, 0, 0, 0]));
                    graphics = new Graphic(new Point(featureGeometry), symbol);
                }
                this.map.getLayer("highlightLayerId").add(graphics);
            }
        },

        /**
        * Center at the specified geometry
        * @memberOf widgets/commonHelper/commonHelper
        */
        setCenterAt: function (geometry) {
            // Checking if application in share url, If it is a share url then do not set extent, else set extent
            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                // Checking if application in share url, If it is a share url then do not set extent, else set extent
                if (this.isExtentSet) {
                    this.map.centerAt(geometry);
                }
            } else {
                this.map.centerAt(geometry);
            }
        },

        /**
        * Center and zoom to the specified geometry
        * @memberOf widgets/commonHelper/commonHelper
        */
        setZoomAndCenterAt: function (geometry) {
            // Checking if application in share url, If it is a share url then do not set extent, else set extent
            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                // Checking if application in share url, If it is a share url then do not set extent, else set extent
                if (this.isExtentSet) {
                    this.map.centerAndZoom(geometry, appGlobals.configData.ZoomLevel);
                }
            } else {
                this.map.centerAndZoom(geometry, appGlobals.configData.ZoomLevel);
            }
        },

        /**
        * Calculate the distance between the pushpin(start point) and nearest feature(end point)
        * @param {object} startPoint is pushpin on map
        * @param {object} endPoint is search result
        * @memberOf widgets/commonHelper/commonHelper
        */
        getDistance: function (startPoint, endPoint) {
            var startPointGeometry, unitName, endPointGeometry, startPointlong, startPointlat, endPointlong, endPointlat, theta, dist;
            startPointGeometry = esri.geometry.webMercatorToGeographic(startPoint);
            endPointGeometry = esri.geometry.webMercatorToGeographic(endPoint);
            startPointlong = startPointGeometry.x;
            startPointlat = startPointGeometry.y;
            endPointlong = endPointGeometry.x;
            endPointlat = endPointGeometry.y;
            theta = startPointlong - endPointlong;
            dist = Math.sin(this.deg2Rad(startPointlat)) * Math.sin(this.deg2Rad(endPointlat)) + Math.cos(this.deg2Rad(startPointlat)) * Math.cos(this.deg2Rad(endPointlat)) * Math.cos(this.deg2Rad(theta));
            dist = Math.acos(dist);
            dist = this.rad2Deg(dist);
            dist = dist * 60 * 1.1515;
            // Getting unit name from config file and setting according to direction widget
            unitName = this._getSubStringUnitData();
            // Switching unit name to calculate distance from start point to end point
            switch (unitName) {
            case " Miles":
                dist = (dist * 10) / 10;
                break;
            case " Meters":
                dist = dist / 0.00062137;
                break;
            case " Kilometers":
                dist = dist / 0.62137;
                break;
            case " Nautical Miles":
                dist = dist * 0.86898;
                break;
            }
            return (dist * 10) / 10;
        },

        /**
        * Convert the  degrees  to radians
        * @param {object} deg is degree which converts to radians
        * @return radians value
        * @memberOf widgets/commonHelper/commonHelper
        */
        deg2Rad: function (deg) {
            return (deg * Math.PI) / 180.0;
        },

        /**
        * Convert the radians to degrees
        * @param {object} rad is radians which converts to degree
        * @return degree value
        * @memberOf widgets/commonHelper/commonHelper
        */
        rad2Deg: function (rad) {
            return (rad / Math.PI) * 180.0;
        },
        /**
        * Convert the UTC time stamp from Millisecond
        * @param {object} utcMilliseconds contains UTC millisecond
        * @returns Date
        * @memberOf widgets/commonHelper/commonHelper
        */
        utcTimestampFromMs: function (utcMilliseconds) {
            return this.localToUtc(new Date(utcMilliseconds));
        },

        /**
        * Convert the local time to UTC
        * @param {object} localTimestamp contains Local time
        * @returns Date
        * @memberOf widgets/commonHelper/commonHelper
        */
        localToUtc: function (localTimestamp) {
            return new Date(localTimestamp.getTime() + (localTimestamp.getTimezoneOffset() * 60000));
        },

        /**
        * Returns the pod enabled status from config file.
        * @param {string} Key name mentioned in config file
        * @memberOf widgets/commonHelper/commonHelper
        */
        getPodStatus: function (keyValue) {
            var isEnabled, i, key;
            isEnabled = false;
            // Looping the podSetting in config file
            for (i = 0; i < appGlobals.configData.PodSettings.length; i++) {
                for (key in appGlobals.configData.PodSettings[i]) {
                    if (appGlobals.configData.PodSettings[i].hasOwnProperty(key)) {
                        // Checking the pod setting variable, if it is set true then show pod
                        if (key === keyValue && appGlobals.configData.PodSettings[i][key].Enabled) {
                            isEnabled = true;
                            break;
                        }
                    }
                }
            }
            return isEnabled;
        },

        /**
        * Get query url from unified search data
        * @param {object} activityData contains unified search data with layer information
        * @param {object} result contains unified search data only
        * @memberOf widgets/commonHelper/commonHelper
        */
        getQueryURLWithUnifiedSearch: function (activityData, result) {
            var g, l, queryURL = "";
            if (activityData) {
                for (g = 0; g < activityData.length; g++) {
                    // Looping for features
                    for (l = 0; l < activityData[g].records.features.length; l++) {
                        if (activityData[g].records.features[l].distance === result) {
                            queryURL = activityData[g].queryURL;
                        }
                    }
                }
            }
            return queryURL;
        },

        /**
        * Converts min to hour
        * @param {string} string contains the minute
        * @memberOf widgets/commonHelper/commonHelper
        */
        convertMinToHr: function (minutes) {
            var hours, convertMinutes, displayTime;
            hours = Math.floor(Math.abs(minutes) / 60);
            convertMinutes = Math.round((Math.abs(minutes) % 60));
            if (hours === 0) {
                displayTime = convertMinutes + sharedNls.titles.minuteText;
            } else if (convertMinutes === 0) {
                displayTime = hours + sharedNls.titles.hourText;
            } else {
                displayTime = hours + sharedNls.titles.hourText + " " + convertMinutes + sharedNls.titles.minuteText;
            }
            return displayTime;
        },

        /**
        * Click on previous button of pagination
        * @param {featureCount} number of feature selected
        * @memberOf widgets/commonHelper/commonHelper
        */
        previousButtonClick: function (featureCount) {
            var rowNumber, point, infoWindowParameter;
            if (Number(this.divPaginationCount.innerHTML.split("/")[0]) > 1) {
                this.divPaginationCount.innerHTML = Number(this.divPaginationCount.innerHTML.split("/")[0]) - 1 + "/" + featureCount;
                rowNumber = Number(this.divPaginationCount.innerHTML.split("/")[0]) - 1;
                if (this.infoWindowFeatureData[rowNumber].attr.geometry.type === "polygon") {
                    point = this.infoWindowFeatureData[rowNumber].attr.geometry.getCentroid();
                } else if (this.infoWindowFeatureData[rowNumber].attr.geometry.type === "polyline") {
                    point = this.infoWindowFeatureData[rowNumber].attr.geometry.getPoint(0, 0);
                } else {
                    point = this.infoWindowFeatureData[rowNumber].attr.geometry;
                }
                // Setting info window parameter for showing info window on map
                infoWindowParameter = {
                    "mapPoint": point,
                    "attribute": this.infoWindowFeatureData[rowNumber].attr.attributes,
                    "layerId": this.infoWindowFeatureData[rowNumber].layerId,
                    "layerTitle": this.infoWindowFeatureData[rowNumber].layerTitle,
                    "featureArray": this.infoWindowFeatureData,
                    "featureSet": this.infoWindowFeatureData[rowNumber].attr,
                    "IndexNumber": Number(Number(this.divPaginationCount.innerHTML.split("/")[0]))
                };
                this._createInfoWindowContent(infoWindowParameter);
            } else {
                domClass.replace(this.previousButton, "esriCTTPaginationDisPrev", "esriCTTPaginationPrev");
            }
        },

        /**
        * Click on next button of pagination
        * @param {featureCount} number of feature selected
        * @memberOf widgets/commonHelper/commonHelper
        */
        nextButtonClick: function (featureCount) {
            var rowNumber, point, infoWindowParameter;
            if (Number(this.divPaginationCount.innerHTML.split("/")[0]) < featureCount) {
                this.divPaginationCount.innerHTML = Number(this.divPaginationCount.innerHTML.split("/")[0]) + 1 + "/" + featureCount;
                rowNumber = Number(this.divPaginationCount.innerHTML.split("/")[0]) - 1;
                if (this.infoWindowFeatureData[rowNumber].attr.geometry.type === "polygon") {
                    point = this.infoWindowFeatureData[rowNumber].attr.geometry.getCentroid();
                } else if (this.infoWindowFeatureData[rowNumber].attr.geometry.type === "polyline") {
                    point = this.infoWindowFeatureData[rowNumber].attr.geometry.getPoint(0, 0);
                } else {
                    point = this.infoWindowFeatureData[rowNumber].attr.geometry;
                }
                // Setting info window parameter for showing info window on map
                infoWindowParameter = {
                    "mapPoint": point,
                    "attribute": this.infoWindowFeatureData[rowNumber].attr.attributes,
                    "layerId": this.infoWindowFeatureData[rowNumber].layerId,
                    "layerTitle": this.infoWindowFeatureData[rowNumber].layerTitle,
                    "featureArray": this.infoWindowFeatureData,
                    "featureSet": this.infoWindowFeatureData[rowNumber].attr,
                    "IndexNumber": Number(Number(this.divPaginationCount.innerHTML.split("/")[0]))
                };
                this._createInfoWindowContent(infoWindowParameter);
            } else {
                domClass.replace(this.nextButton, "esriCTTPaginationDisNext", "esriCTTPaginationNext");
            }
        },

        /**
        * Get the setting name by passing query layer
        * @ return search setting Data
        * @memberOf widgets/commonHelper/commonHelper
        */
        getSearchSetting: function (queryURL) {
            var settingData;
            // Looping for getting object id from event search.
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                if (settings.QueryURL === queryURL) {
                    settingData = settings;
                }
            }));
            // Looping for getting object id from activity search.
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings, activitySettingIndex) {
                if (settings.QueryURL === queryURL) {
                    settingData = settings;
                }
            }));
            return settingData;
        },

        /**
        * Remove comment pod from container for event layer
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeCommentPod: function () {
            var selectedPodName, eventPodData = [], i;
            this.carouselContainer.removeAllPod();
            // Looping for carousel pod data for removing comment pod from container
            for (i = 0; i < this.carouselPodData.length; i++) {
                selectedPodName = domAttr.get(this.carouselPodData[i], "CarouselPodName");
                // Checking for comment pod for removing it from container
                if (selectedPodName !== "CommentsPod") {
                    // Checking for gallery pod from settings for removal of gallery pod from settings
                    if (!this.isGalleryPodEnabled) {
                        if (selectedPodName !== "GalleryPod") {
                            eventPodData.push(this.carouselPodData[i]);
                        }
                    } else {
                        eventPodData.push(this.carouselPodData[i]);
                    }
                }
            }
            this.carouselContainer.addPod(eventPodData);
        },

        /**
        * Remove Gallery Pod pod from container for event layer
        * @memberOf widgets/commonHelper/commonHelper
        */
        removeGalleryPod: function () {
            var selectedPodName, eventPodData = [], i;
            this.carouselContainer.removeAllPod();
            // Looping for carousel pod data for removing comment pod from container
            for (i = 0; i < this.carouselPodData.length; i++) {
                selectedPodName = domAttr.get(this.carouselPodData[i], "CarouselPodName");
                if (selectedPodName !== "GalleryPod") {
                    eventPodData.push(this.carouselPodData[i]);
                }
            }
            this.carouselContainer.addPod(eventPodData);
        },

        /**
        * Add comment pod from container for event layer
        * @memberOf widgets/commonHelper/commonHelper
        */
        addCommentPod: function () {
            var selectedPodName, eventPodData = [], i;
            // Looping for carousel pod data for adding comment pod from container
            for (i = 0; i < this.carouselPodData.length; i++) {
                selectedPodName = domAttr.get(this.carouselPodData[i], "CarouselPodName");
                if (selectedPodName === "CommentsPod") {
                    eventPodData.push(this.carouselPodData[i]);
                }
            }
            this.carouselContainer.addPod(eventPodData);
        },

        /**
        * Add gallery pod from container for event layer
        * @memberOf widgets/commonHelper/commonHelper
        */
        addGalleryPod: function () {
            var selectedPodName, eventPodData = [], i, divHeaderContent;
            // Looping for carousel pod data for adding comment pod from container
            divHeaderContent = query('.esriCTDivGalleryContent');
            if (divHeaderContent.length === 0) {
                for (i = 0; i < this.carouselPodData.length; i++) {
                    selectedPodName = domAttr.get(this.carouselPodData[i], "CarouselPodName");
                    // Looping if gallery div has data
                    if (selectedPodName === "GalleryPod") {
                        eventPodData.push(this.carouselPodData[i]);
                    }
                }
                this.carouselContainer.addPod(eventPodData);
            }
        },


        /**
        * Setting value to change for extent
        * @memberOf widgets/commonHelper/commonHelper
        */
        setZoomForGeolocation: function () {
            var isZoomToLocation = false;
            // Checking if application in share url, If it is a share url then do not set extent, else set extent
            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                // Checking if application in share url, If it is a share url then do not set extent, else set extent
                if (this.isExtentSet) {
                    isZoomToLocation = true;
                } else {
                    isZoomToLocation = false;
                }
            } else {
                isZoomToLocation = true;
            }
            return isZoomToLocation;
        },

        /**
        * Calculate offset point to show infowindow
        * @param {object} mapPoint
        * @memberOf widgets/commonHelper/commonHelper
        */
        calculateCustomMapExtent: function (mapPoint) {
            var width, infoWidth, height, diff, ratioHeight, ratioWidth, totalYPoint, xmin,
                ymin, xmax, ymax;
            width = this.map.extent.getWidth();
            infoWidth = (this.map.width / 2) + appGlobals.configData.InfoPopupWidth / 2 + 400;
            height = this.map.extent.getHeight();
            // Check if infoWindow width is greater than map width
            if (infoWidth > this.map.width) {
                diff = infoWidth - this.map.width;
            } else {
                diff = 0;
            }
            ratioHeight = height / this.map.height;
            ratioWidth = width / this.map.width;
            totalYPoint = appGlobals.configData.InfoPopupHeight + 30 + 61;
            xmin = mapPoint.x - (width / 2);
            // Validate the width of window
            if (win.getBox().w >= 680) {
                ymin = mapPoint.y - height + (ratioHeight * totalYPoint);
                xmax = xmin + width + diff * ratioWidth;
            } else {
                ymin = mapPoint.y - (height / 2);
                xmax = xmin + width;
            }
            ymax = ymin + height;
            return new esri.geometry.Extent(xmin, ymin, xmax, ymax, this.map.spatialReference);
        },

        /**
        * It gets the info window index from info window settings of selected feature
        * @param {layerTitle} layerTitle of feature
        * @param {layerId} layerID of feature
        * return index of the infoWindow feature
        * @memberOf widgets/commonHelper/commonHelper
        */
        getInfowWindowIndex: function (layerTitle, layerId) {
            var index, i;
            // Looping for getting the layer id
            for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                if (appGlobals.operationLayerSettings[i].infoWindowData) {
                    if (layerTitle === appGlobals.operationLayerSettings[i].layerTitle && layerId === appGlobals.operationLayerSettings[i].layerID) {
                        index = i;
                        break;
                    }
                }
            }
            return index;
        },

        /**
        * Get object id from the layer
        * @param {object} object of layer
        * @return {objectId} returns the objectId
        * @memberOf widgets/commonHelper/commonHelper
        */
        getObjectId: function (response) {
            var objectId, j;
            // Loop through the layer fields to fetch field of the type 'esriFieldTypeOID'
            for (j = 0; j < response.length; j++) {
                if (response[j].type === "esriFieldTypeOID") {
                    objectId = response[j].name;
                    break;
                }
            }
            return objectId;
        },

        /**
        * Get the key field value from the config file
        * @param {data} keyField value with $ sign
        * @memberOf widgets/commonHelper/commonHelper
        */
        getKeyValue: function (data) {
            var firstPlace, secondPlace, keyValue;
            firstPlace = data.indexOf("{");
            secondPlace = data.indexOf("}");
            keyValue = data.substring(Number(firstPlace) + 1, secondPlace);
            return keyValue;
        },

        /**
        * Function get object id on the basic of setting name
        * @param {LayerId} layer id value
        * @param {LayerTitle} layer title value
        * return a URL on which query is performed
        * @memberOf widgets/commonHelper/commonHelper
        */
        getQueryUrl: function (LayerId, LayerTitle) {
            var queryURL;
            // Looping for getting object id from event search.
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                if (parseInt(settings.QueryLayerId, 10) === LayerId && settings.Title === LayerTitle) {
                    queryURL = settings.QueryURL;
                }
            }));
            // Looping for getting object id from activity search.
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings, activitySettingIndex) {
                if (parseInt(settings.QueryLayerId, 10) === LayerId && settings.Title === LayerTitle) {
                    queryURL = settings.QueryURL;
                }
            }));
            if (!queryURL) {
                queryURL = "otherURL";
            }
            return queryURL;
        },

        /**
        * It gets the feature type from  selected feature
        * @param {layerTitle} layerTitle of feature
        * @param {layerId} layerID of feature
        * return the name of the widget
        * @memberOf widgets/commonHelper/commonHelper
        */
        getInfowWindowWidgetName: function (layerTitle, layerId) {
            var widgetName = "", key, j;
            for (key in appGlobals.configData) {
                if (appGlobals.configData.hasOwnProperty(key)) {
                    if (key === "ActivitySearchSettings" || key === "EventSearchSettings") {
                        if (appGlobals.configData.ActivitySearchSettings[0].Title === layerTitle && parseInt(appGlobals.configData.ActivitySearchSettings[0].QueryLayerId, 10) === layerId) {
                            widgetName = "InfoActivity";
                            break;
                        }
                        for (j = 0; j < appGlobals.configData.EventSearchSettings.length; j++) {
                            if (appGlobals.configData.EventSearchSettings[j].Title === layerTitle && parseInt(appGlobals.configData.EventSearchSettings[j].QueryLayerId, 10) === layerId) {
                                widgetName = "InfoEvent";
                                break;
                            }
                        }
                    }
                }
            }
            return widgetName;
        },

        /**
        * Get Attachments For InfoWindow
        * @param {object} response is the information about the attachment
        * @memberOf widgets/commonHelper/commonHelper
        */
        getAttachments: function (response) {
            var divAttchmentInfo, divPreviousImgInfo, divNextImgInfo, filteredResponse = [], i;
            this.imageCountInfo = 0;
            for (i = 0; i < response.length; i++) {
                if (response[i].contentType.indexOf("image") > -1) {
                    filteredResponse.push(response[i]);
                }
            }
            // Check if number of attachments fetched is more than 1
            if (filteredResponse && filteredResponse.length > 1) {
                divPreviousImgInfo = domConstruct.create("div", { "class": "esriCTImgPrev" }, this.galleryContainer);
                divNextImgInfo = domConstruct.create("div", { "class": "esriCTImgNext" }, this.galleryContainer);
                divAttchmentInfo = domConstruct.create("img", { "class": "esriCTDivAttchmentInfo" }, this.galleryContainer);
                domAttr.set(divAttchmentInfo, "src", response[this.imageCountInfo].url);
                this.own(on(divPreviousImgInfo, a11yclick, lang.hitch(this, function () {
                    this.imageCountInfo--;
                    if (this.imageCountInfo === 0) {
                        domStyle.set(divPreviousImgInfo, "display", "none");
                    } else {
                        domStyle.set(divPreviousImgInfo, "display", "block");
                    }
                    domStyle.set(divNextImgInfo, "display", "block");
                    domAttr.set(divAttchmentInfo, "src", filteredResponse[this.imageCountInfo].url);
                })));
                this.own(on(divNextImgInfo, a11yclick, lang.hitch(this, function () {
                    this.imageCountInfo++;
                    if (this.imageCountInfo === filteredResponse.length - 1) {
                        domStyle.set(divNextImgInfo, "display", "none");
                    } else {
                        domStyle.set(divNextImgInfo, "display", "block");
                    }
                    domStyle.set(divPreviousImgInfo, "display", "block");
                    domAttr.set(divAttchmentInfo, "src", filteredResponse[this.imageCountInfo].url);
                })));
                // If number of attachments fetched is equal to 1
            } else if (filteredResponse.length === 1) {
                divAttchmentInfo = domConstruct.create("img", { "class": "esriCTDivAttchmentInfo" }, this.galleryContainer);
                domAttr.set(divAttchmentInfo, "src", filteredResponse[0].url);
            } else {
                // If no attachments fetched
                domConstruct.create("div", { "class": "esriCTGalleryBox", "innerHTML": sharedNls.errorMessages.imageDoesNotFound }, this.galleryContainer);
            }
        },

        /**
        * Fetch the geometry type of the mapPoint
        * @param {object} geometry Contains the geometry service
        * return selected MapPoint
        * @memberOf widgets/commonHelper/commonHelper
        */
        getMapPoint: function (geometry) {
            var selectedMapPoint, mapPoint, rings, points;
            // If geometry type is point
            if (geometry.type === "point") {
                selectedMapPoint = geometry;
                // If geometry type is polyline
            } else if (geometry.type === "polyline") {
                selectedMapPoint = geometry.getPoint(0, 0);
            } else if (geometry.type === "polygon") {
                mapPoint = geometry.getExtent().getCenter();
                if (!geometry.contains(mapPoint)) {
                    // If the center of the polygon does not lie within the polygon
                    rings = Math.floor(geometry.rings.length / 2);
                    points = Math.floor(geometry.rings[rings].length / 2);
                    selectedMapPoint = geometry.getPoint(rings, points);
                } else {
                    // If the center of the polygon lies within the polygon
                    selectedMapPoint = geometry.getExtent().getCenter();
                }
            }
            return selectedMapPoint;
        },

        /**
        * Get date field from layer
        * @param {object} object of layer
        * @memberOf widgets/commonHelper/commonHelper
        */
        getDateField: function (response) {
            var j, dateFieldArray = [], dateField;
            for (j = 0; j < response.fields.length; j++) {
                if (response.fields[j].type === "esriFieldTypeDate") {
                    dateField = response.fields[j].name;
                    dateFieldArray.push(dateField);
                }
            }
            return dateFieldArray;
        },

        /**
        * Function for adding item in my list panel
        * @param {object} eventDataObject - contains the event object information
        * @param {string} widgetName - contains the widget name
        * @memberOf widgets/commonHelper/commonHelper
        */
        addtoMyList: function (eventDataObject, widgetName) {
            topic.publish("showProgressIndicator");
            var sortedMyList, eventObject, l, eventSearchSettingsIndex, listObject, IsSearchsettingFound, LayerInfo, layerSearchSetting, orderEvent, queryLayerID, sortedMyListData;
            listObject = {};
            // Looping event search setting for getting event index, and event setting name for further query
            IsSearchsettingFound = false;
            LayerInfo = [];
            array.forEach(appGlobals.operationLayerSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                if (settings.layerTitle === eventDataObject.layerTitle && settings.layerID === eventDataObject.layerId) {
                    LayerInfo.push(settings);
                }
            }));
            // Looping through layer info for getting settings
            array.forEach(LayerInfo, lang.hitch(this, function (settingsName, eventSettingIndexValue) {
                if (!IsSearchsettingFound) {
                    var key;
                    for (key in settingsName) {
                        if (settingsName.hasOwnProperty(key)) {
                            if (key.toLowerCase() === "activitysearchsettings") {
                                layerSearchSetting = [settingsName.activitySearchSettings];
                                this.searchSettingsName = "activitysettings";
                                IsSearchsettingFound = true;
                                break;
                            }
                            if (key.toLowerCase() === "eventsearchsettings") {
                                layerSearchSetting = [settingsName.eventSearchSettings];
                                this.searchSettingsName = "eventsettings";
                                IsSearchsettingFound = true;
                                break;
                            }
                        }
                    }
                }
            }));
            // Looping through layer search settings for gettings search settings data
            array.forEach(layerSearchSetting, lang.hitch(this, function (settings, eventSettingIndex) {
                queryLayerID = parseInt(settings.QueryLayerId, 10);
                if (queryLayerID === eventDataObject.layerId && settings.Title === eventDataObject.layerTitle) {
                    if (this.searchSettingsName === "eventsettings") {
                        eventSearchSettingsIndex = eventSettingIndex;
                    } else if (this.searchSettingsName === "activitysettings") {
                        eventSearchSettingsIndex = eventSettingIndex;
                    }
                }
            }));
            // Add the selected event object to the memory store
            listObject = { "key": eventDataObject.ObjectIDField, "value": eventDataObject.eventDetails, "featureSet": eventDataObject.featureSet, "startDateField": eventDataObject.StartDateField, "eventSettingsIndex": eventSearchSettingsIndex, "settingsName": this.searchSettingsName };
            this.myListStore.push(listObject);
            topic.publish("getMyListData", this.myListStore);
            if (this.myListStore.length > 0) {
                topic.publish("replaceClassForMyList");
            }
            // Sort with ascending order of date
            topic.publish("sortMyList", true, this.featureSet);
            if (widgetName.toLowerCase() === "infoevent" || "event") {
                sortedMyList = this.sortedList;
                if (!eventDataObject.infoWindowClick) {
                    eventObject = { "EventDetails": eventDataObject.eventDetails, "SortedData": sortedMyList, "InfowindowClick": eventDataObject.infoWindowClick, "layerId": eventDataObject.layerId, "layerTitle": eventDataObject.layerTitle, "settingsName": this.searchSettingsName, "key": eventDataObject.ObjectIDField, "startDateField": eventDataObject.StartDateField };
                } else {
                    eventObject = { "EventDetails": eventDataObject.eventDetails, "SortedData": sortedMyList, "InfowindowClick": eventDataObject.infoWindowClick, "layerId": eventDataObject.layerId, "layerTitle": eventDataObject.layerTitle, "settingsName": this.searchSettingsName, "key": eventDataObject.ObjectIDField, "startDateField": eventDataObject.StartDateField };
                }
            } else {
                eventObject = { "EventDetails": eventDataObject.eventDetails, "SortedData": this.sortedList, "InfowindowClick": eventDataObject.infoWindowClick, "layerId": eventDataObject.layerId, "layerTitle": eventDataObject.layerTitle, "settingsName": this.searchSettingsName, "key": eventDataObject.ObjectIDField, "startDateField": eventDataObject.StartDateField };
            }
            topic.publish("refreshMyList", eventObject, widgetName);
            if (!eventDataObject.infoWindowClick) {
                appGlobals.shareOptions.eventIndex = null;
            }
            // Looping my list store for adding object id in an array for share application
            for (l = 0; l < this.myListStore.length; l++) {
                // Check if event added from event search
                if (!eventDataObject.infoWindowClick) {
                    if (appGlobals.shareOptions.eventIndex) {
                        appGlobals.shareOptions.eventIndex += "," + this.myListStore[l].value[this.myListStore[l].key].toString();
                    } else {
                        appGlobals.shareOptions.eventIndex = this.myListStore[l].value[this.myListStore[l].key].toString();
                    }
                }
            }
            // Function for share for odering even search data
            if (window.location.toString().split("$eventOrderInMyList=").length > 1) {
                if (this.myListStore.length === Number(window.location.toString().split("$eventOrderInMyList=")[1].split(",")[1].split("$")[0])) {
                    orderEvent = window.location.toString().split("$eventOrderInMyList=")[1].split(",")[0] === "true" ? true : false;
                    topic.publish("sortMyList", orderEvent);
                    sortedMyListData = this.sortDate(orderEvent);
                    eventObject = { "EventDetails": null, "SortedData": sortedMyListData, "InfowindowClick": false, "eventForOrder": true };
                    // show data in mylist panel after order by list.
                    topic.publish("refreshMyList", eventObject, widgetName);
                }
            }
            topic.publish("hideProgressIndicator");
        },

        /**
        * Function to get value type
        * @param {object} data contains the data
        * @memberOf widgets/commonHelper/commonHelper
        */
        getValueType: function (data) {
            var isNumber;
            isNumber = isNaN(data);
            return isNumber;
        },

        /**
        * Function to set UTC date format
        * @param {object} eventData contains the event data
        * @memberOf widgets/commonHelper/commonHelper
        */
        setDateWithUTC: function (eventData) {
            var utcDateObject = {}, startDateAttr, endDateAttr;
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings) {
                startDateAttr = this.getKeyValue(settings.AddToCalendarSettings[0].StartDate);
                endDateAttr = this.getKeyValue(settings.AddToCalendarSettings[0].EndDate);
            }));
            utcDateObject[startDateAttr] = eventData.attributes[startDateAttr];
            utcDateObject[endDateAttr] = eventData.attributes[endDateAttr];
            eventData.utcDate = utcDateObject;
            return eventData;
        },

        /**
        * Sort date by order
        * @param {string} startDate contains date attribute
        * @param {string} ascendingFlag contains boolean flag for ascending value
        * @memberOf widgets/commonHelper/commonHelper
        */
        sortDate: function (ascendingFlag) {
            var sortResult = [], sortedEventData = [], sortedActivityData = [], t, startDateFound, p, q, sortedDataKey, sortedDateArray, nameValue, nameData;
            // Checking for order of data and sorting in assending order.
            if (ascendingFlag) {
                sortResult = this.myListStore.sort(lang.hitch(this, function (a, b) {
                    if (b.value[b.startDateField] && a.value[a.startDateField]) {
                        sortedDateArray = new Date(b.value[b.startDateField]).getTime() - new Date(a.value[a.startDateField]).getTime();
                    } else {
                        sortedDateArray = 1;
                    }
                    return sortedDateArray;
                }));
                // Checking for order of data and sorting in decending  order.
            } else {
                sortResult = this.myListStore.sort(lang.hitch(this, function (a, b) {
                    if (a.value[a.startDateField] && b.value[b.startDateField]) {
                        sortedDateArray = new Date(a.value[a.startDateField]).getTime() - new Date(b.value[b.startDateField]).getTime();
                    } else {
                        sortedDateArray = 1;
                    }
                    return sortedDateArray;
                }));
            }
            // Looping sorted data for finding start date field to store event and activity data seperatly
            for (t = 0; t < sortResult.length; t++) {
                startDateFound = false;
                for (sortedDataKey in sortResult[t].value) {
                    if (sortResult[t].value.hasOwnProperty(sortedDataKey)) {
                        if (sortedDataKey === sortResult[t].startDateField) {
                            startDateFound = true;
                            break;
                        }
                    }
                }
                if (startDateFound) {
                    sortedEventData.push(sortResult[t]);
                } else {
                    sortedActivityData.push(sortResult[t]);
                }
            }
            // Sorting for activity data on the basic of name attribute
            sortedActivityData = sortedActivityData.sort(function (a, b) {
                nameValue = a.value.NAME.toLowerCase();
                nameData = b.value.NAME.toLowerCase();
                if (nameValue < nameData) { //sort string ascending
                    return -1;
                }
                if (nameValue > nameData) {
                    return 1;
                }
                return 0; //default return value (no sorting)
            });
            // Fetching of event and activity data in two diffrent array and finally returning sorted data
            sortResult.length = 0;
            for (p = 0; p < sortedEventData.length; p++) {
                sortResult.push(sortedEventData[p]);
            }
            for (q = 0; q < sortedActivityData.length; q++) {
                sortResult.push(sortedActivityData[q]);
            }
            return sortResult;
        }
    });
});
