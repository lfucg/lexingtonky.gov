/*global define,dojo,dojoConfig:true,alert,esri,Modernizr,dijit,appGlobals */
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
    "dojo/_base/lang",
    "dojo/on",
    "dojo/_base/array",
    "dojo/query",
    "dojo/string",
    "dojo/Deferred",
    "dojo/promise/all",
    "esri/geometry/Point",
    "dijit/_WidgetBase",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "dojo/_base/Color",
    "esri/dijit/Directions",
    "esri/urlUtils",
    "esri/units",
    "widgets/locator/locator",
    "dojo/has",
    "dojo/sniff"

], function (declare, domConstruct, lang, on, array, query, string, Deferred, all, Point, _WidgetBase, sharedNls, topic, Color, Directions, urlUtils, units, LocatorTool, has) {
    // ========================================================================================================================//

    return declare([_WidgetBase], {
        sharedNls: sharedNls,                                       // Variable for shared NLS
        _esriDirectionsWidget: null,                                // Variable for Direction widget
        featureSetWithoutNullValue: null,                           // Variable for store the featureSet Without NullValue

        /**
        * Create Direction widget
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _createDirectionWidget: function () {
            var address, queryObject, resultcontent, directionObject, queryURLLink;
            try {
                // Proxy setting for routeService
                urlUtils.addProxyRule({
                    urlPrefix: appGlobals.configData.DrivingDirectionSettings.RouteServiceURL,
                    proxyUrl: appGlobals.configData.ProxyUrl
                });
                // Proxy setting for GeometryService
                urlUtils.addProxyRule({
                    urlPrefix: appGlobals.configData.GeometryService,
                    proxyUrl: appGlobals.configData.ProxyUrl
                });
                // Creating esriDirection widget.
                this._esriDirectionsWidget = new Directions({
                    map: this.map,
                    directionsLengthUnits: units[appGlobals.configData.DrivingDirectionSettings.RouteUnit.toUpperCase()],
                    showTrafficOption: false,
                    dragging: false,
                    routeTaskUrl: appGlobals.configData.DrivingDirectionSettings.RouteServiceURL
                });
                // Set geocoderOptions is autoComplete for ersiDirection widget
                //Commented out after migration to 3.15...geocoderOptions is now searchOptions...
                // however, searchOptions does not appear to have an autoComplete property
                //uncomment the following line for 3.13
                this._esriDirectionsWidget.options.geocoderOptions.autoComplete = true;
                this._esriDirectionsWidget.autoSolve = false;
                this._esriDirectionsWidget.deactivate();
                // Calling esriDirection widget
                this._esriDirectionsWidget.startup();
                // Set the color of route in direction widget
                this._esriDirectionsWidget.options.routeSymbol.color = new Color([parseInt(appGlobals.configData.DrivingDirectionSettings.RouteColor.split(",")[0], 10), parseInt(appGlobals.configData.DrivingDirectionSettings.RouteColor.split(",")[1], 10), parseInt(appGlobals.configData.DrivingDirectionSettings.RouteColor.split(",")[2], 10), parseFloat(appGlobals.configData.DrivingDirectionSettings.Transparency.split(",")[0], 10)]);
                // Set the width of route in direction widget
                this._esriDirectionsWidget.options.routeSymbol.width = parseInt(appGlobals.configData.DrivingDirectionSettings.RouteWidth, 10);
                // Callback function for direction widget when route is started
                this.own(on(this._esriDirectionsWidget, "directions-start", lang.hitch(this, function (a) {
                    // Setting length of geocoders to 0 in the case of IE8 browser  because direction widget throwing error internally when it sort to geocoders.
                    if (has("ie") === 8) {
                        a.target.geocoders.length = 0;
                    }
                })));
                // Callback function for direction widget when route is finish
                this.own(on(this._esriDirectionsWidget, "directions-finish", lang.hitch(this, function (a) {
                    this.disableInfoPopupOfDirectionWidget(this._esriDirectionsWidget);
                    if (this.locatorAddress !== "") {
                        address = this.locatorAddress;
                        // Check is start point is there then set title
                    } else if (this.routeObject.StartPoint) {
                        address = sharedNls.titles.directionCurrentLocationText;
                    }
                    // Check if direction not null or null then set zoom level of route in share URL
                    if (this._esriDirectionsWidget.directions !== null) {
                        if (this.zoomToFullRoute) {
                            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                                if (this.isExtentSet) {
                                    this._esriDirectionsWidget.zoomToFullRoute();
                                }
                            } else {
                                this._esriDirectionsWidget.zoomToFullRoute();
                            }
                        }
                        appGlobals.shareOptions.isActivitySearch = null;
                        // Switch case for widget name
                        switch (this.routeObject.WidgetName.toLowerCase()) {
                        case "activitysearch":
                            appGlobals.shareOptions.sharedGeolocation = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            appGlobals.shareOptions.infowindowDirection = null;
                            appGlobals.shareOptions.eventInfoWindowData = null;
                            appGlobals.shareOptions.eventRoutePoint = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            if (appGlobals.shareOptions.activitySearch) {
                                appGlobals.shareOptions.infoRoutePoint = null;
                            }
                            queryObject = { "FeatureData": this.routeObject.EndPoint, "SolveRoute": a.result.routeResults, "Index": this.routeObject.Index, "QueryURL": this.routeObject.QueryURL, "WidgetName": this.routeObject.WidgetName, "Address": address, "IsRouteCreated": true };
                            topic.publish("showProgressIndicator");
                            this.removeBuffer();
                            this.queryCommentLayer(queryObject);
                            break;
                        case "searchedfacility":
                            appGlobals.shareOptions.infowindowDirection = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            queryObject = { "FeatureData": this.routeObject.EndPoint, "SolveRoute": a.result.routeResults, "Index": this.routeObject.Index, "QueryURL": this.routeObject.QueryURL, "WidgetName": this.routeObject.WidgetName, "Address": address, "IsRouteCreated": true, "activityData": this.routeObject.activityData };
                            topic.publish("showProgressIndicator");
                            this.queryCommentLayer(queryObject);
                            break;
                        case "event":
                            this.showEventData(a, address);
                            topic.publish("hideProgressIndicator");
                            break;
                        case "unifiedsearch":
                            appGlobals.shareOptions.sharedGeolocation = null;
                            appGlobals.shareOptions.infowindowDirection = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            queryURLLink = this.getQueryURLWithUnifiedSearch(this.routeObject.activityData, this.routeObject.EndPoint[this.routeObject.Index].distance);
                            queryURLLink = queryURLLink === "" ? this.routeObject.QueryURL : queryURLLink;
                            if (queryURLLink === appGlobals.configData.ActivitySearchSettings[0].QueryURL) {
                                queryObject = { "FeatureData": this.routeObject.EndPoint, "SolveRoute": a.result.routeResults, "Index": this.routeObject.Index, "QueryURL": queryURLLink, "WidgetName": this.routeObject.WidgetName, "Address": address, "IsRouteCreated": true, "activityData": this.routeObject.activityData };
                                topic.publish("showProgressIndicator");
                                this.queryCommentLayer(queryObject);
                                appGlobals.shareOptions.sharedGeolocation = null;
                            } else {
                                this.showEventData(a, address);
                            }
                            break;
                        case "geolocation":
                            // AppGlobals.shareOptions.addressLocationDirectionActivity = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            appGlobals.shareOptions.eventInfoWindowData = null;
                            appGlobals.shareOptions.infoRoutePoint = null;

                            queryURLLink = this.getQueryURLWithUnifiedSearch(this.routeObject.activityData, this.routeObject.EndPoint[this.routeObject.Index].distance);
                            queryURLLink = queryURLLink === "" ? this.routeObject.QueryURL : queryURLLink;
                            if (queryURLLink === appGlobals.configData.ActivitySearchSettings[0].QueryURL) {
                                queryURLLink = this.getQueryURLWithUnifiedSearch(this.routeObject.activityData, this.routeObject.EndPoint[this.routeObject.Index].distance);
                                queryObject = { "FeatureData": this.routeObject.EndPoint, "SolveRoute": a.result.routeResults, "Index": this.routeObject.Index, "QueryURL": queryURLLink, "WidgetName": this.routeObject.WidgetName, "Address": address, "IsRouteCreated": true, "activityData": this.routeObject.activityData };
                                topic.publish("showProgressIndicator");
                                this.queryCommentLayer(queryObject);
                            } else {
                                this.showEventData(a, address);
                            }
                            break;
                        case "infoevent":
                            resultcontent = { "value": 0 };
                            this.removeBuffer();
                            appGlobals.shareOptions.addressLocation = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            appGlobals.shareOptions.doQuery = "false";
                            appGlobals.shareOptions.eventPlannerQuery = null;
                            directionObject = { "Feature": this.routeObject.EndPoint, "SelectedItem": resultcontent, "SolveRoute": a.result.routeResults, "Address": address, "WidgetName": this.routeObject.WidgetName, "QueryURL": this.routeObject.QueryURL };
                            this.setDirection(directionObject, true);
                            break;
                        case "infoactivity":
                            resultcontent = { "value": 0 };
                            this.removeBuffer();
                            appGlobals.shareOptions.addressLocation = null;
                            appGlobals.shareOptions.eventForListClicked = null;
                            appGlobals.shareOptions.doQuery = "false";
                            directionObject = { "Feature": this.routeObject.EndPoint, "SelectedItem": resultcontent, "SolveRoute": a.result.routeResults, "Address": address, "WidgetName": this.routeObject.WidgetName, "QueryURL": this.routeObject.QueryURL };
                            this.setDirection(directionObject, true);
                            break;
                        case "routeforlist":
                            setTimeout(lang.hitch(this, function () {
                                this._esriDirectionsWidget._printDirections();
                            }), 2000);
                            break;
                        case "default":
                            break;
                        }
                        topic.publish("hideProgressIndicator");
                    } else {
                        if (this.routeObject.WidgetName.toLowerCase() === "routeforlist") {
                            alert(sharedNls.errorMessages.routeComment);
                            topic.publish("hideProgressIndicator");
                            this.isRouteCreated = false;
                        } else if (this.routeObject.WidgetName.toLowerCase() === "infoactivity" || this.routeObject.WidgetName.toLowerCase() === "infoevent") {
                            alert(sharedNls.errorMessages.routeComment);
                            topic.publish("hideProgressIndicator");
                            this.isRouteCreated = false;
                        } else {
                            appGlobals.shareOptions.eventForListClicked = null;
                            this._executeWhenRouteNotCalculated(this.routeObject);
                            topic.publish("hideProgressIndicator");
                            this.isRouteCreated = false;
                        }
                        topic.publish("hideProgressIndicator");
                    }
                    topic.publish("hideProgressIndicator");
                    this._setCrouselContainerInSharedCase();
                })));
                topic.publish("hideProgressIndicator");
            } catch (error) {
                alert(error);
                topic.publish("hideProgressIndicator");
            }
        },

        /**
        * Function will show the event search
        * @param {object} a contains route data
        * @param {string} address contains address of the route
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        showEventData: function (a, address) {
            var queryObject, resultcontent, facilityObject, directionObject;
            appGlobals.shareOptions.infowindowDirection = null;
            appGlobals.shareOptions.doQuery = "false";
            this.removeCommentPod();
            this.routeObject.EndPoint = this.removeNullValue(this.routeObject.EndPoint);
            queryObject = { "FeatureData": this.routeObject.EndPoint, "SolveRoute": a.result.routeResults, "Index": this.routeObject.Index, "QueryURL": this.routeObject.QueryURL, "WidgetName": this.routeObject.WidgetName, "Address": address, "activityData": this.routeObject.activityData };
            topic.publish("showProgressIndicator");
            this.carouselContainer.showCarouselContainer();
            this.setSearchContent(this.routeObject.EndPoint, false, this.routeObject.QueryURL, this.routeObject.WidgetName, this.routeObject.activityData);
            this.highlightFeature(this.routeObject.EndPoint[this.routeObject.Index].geometry);
            resultcontent = { "value": this.routeObject.Index };
            facilityObject = { "Feature": this.routeObject.EndPoint, "SelectedItem": resultcontent, "QueryURL": this.routeObject.QueryURL, "WidgetName": this.routeObject.WidgetName, "activityData": this.routeObject.activityData };
            this.setFacility(facilityObject);
            // Checking for solve route if direction is not calculated then show address search box
            if (queryObject.SolveRoute && queryObject.SolveRoute[0].directions && queryObject.SolveRoute[0].directions.totalLength <= 0) {
                directionObject = { "Feature": queryObject.FeatureData, "SelectedItem": resultcontent, "SolveRoute": queryObject.SolveRoute, "Address": queryObject.Address, "WidgetName": queryObject.WidgetName, "activityData": queryObject.activityData, "QueryURL": queryObject.QueryURL };
                this.setDirection(directionObject);
            } else if (this.routeObject.isLayerCandidateClicked) {
                this._createAddressSearchTextBox(queryObject, resultcontent);
            } else {
                directionObject = { "Feature": this.routeObject.EndPoint, "SelectedItem": resultcontent, "SolveRoute": a.result.routeResults, "Address": address, "WidgetName": this.routeObject.WidgetName, "QueryURL": this.routeObject.QueryURL, "activityData": this.routeObject.activityData };
                this.setDirection(directionObject);
            }
            queryObject = { "FeatureData": this.routeObject.EndPoint, "QueryURL": this.routeObject.QueryURL, "WidgetName": this.routeObject.WidgetName, "activityData": this.routeObject.activityData };
            this.setGallery(queryObject, resultcontent);
            this.removeCommentPod();
            this.carouselContainer.expandCarousel();
        },

        /**
        * Create route for two points
        * @param {object} routeObject contains Start point, End point, widget name and query URL
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        showRoute: function (routeObject) {
            var endPointGeometery, startPointGeometery, geoArray, queryObject;
            // If variable is set to false directions cannot be enabled
            topic.publish("showProgressIndicator");
            routeObject.EndPoint = this.removeNullValue(routeObject.EndPoint);
            // Remove ripple only when routeObject's widgetName is neither InfoActivity nor InfoEvent
            if (routeObject.WidgetName !== "InfoActivity" && routeObject.WidgetName !== "InfoEvent") {
                this.removeHighlightedCircleGraphics();
            }
            this.removeRouteGraphichOfDirectionWidget();
            geoArray = [];
            // Creating new point with start point geometry
            startPointGeometery = new Point(parseFloat(routeObject.StartPoint.geometry.x), parseFloat(routeObject.StartPoint.geometry.y), this.map.spatialReference);
            geoArray.push(startPointGeometery);
            // Creating new point with end point geometry
            endPointGeometery = new Point(parseFloat(routeObject.EndPoint[routeObject.Index].geometry.x), parseFloat(routeObject.EndPoint[routeObject.Index].geometry.y), this.map.spatialReference);
            geoArray.push(endPointGeometery);
            this.routeObject = routeObject;
            // Updating two stops for direction widget.
            if (appGlobals.configData.DrivingDirectionSettings.GetDirections && !routeObject.isLayerCandidateClicked) {
                // For clearing the direction from map and div.
                this._esriDirectionsWidget.clearDirections();
                // Function for updating stops and getting direction from direction widget
                this._esriDirectionsWidget.updateStops(geoArray).then(lang.hitch(this, function () {
                    this._esriDirectionsWidget.getDirections().then(lang.hitch(this, function (e) {
                        topic.publish("hideProgressIndicator");
                    }), lang.hitch(this, function (err) {
                        // When route is not calculated by direction widget then show alert message and show results in pod
                        alert(sharedNls.errorMessages.routeComment);
                        topic.publish("executeWhenRouteNotCalculated", this.routeObject);
                        this.isRouteCreated = false;
                        topic.publish("hideProgressIndicator");
                    }));
                }), lang.hitch(this, function (err) {
                    // When route is not calculated by direction widget then show alert message and show results in pod
                    alert(sharedNls.errorMessages.routeComment);
                    topic.publish("executeWhenRouteNotCalculated", this.routeObject);
                    this.isRouteCreated = false;
                    topic.publish("hideProgressIndicator");
                }));
            } else {
                queryObject = { "FeatureData": this.routeObject.EndPoint, "Index": this.routeObject.Index, "QueryURL": this.routeObject.QueryURL, "WidgetName": this.routeObject.WidgetName, "activityData": this.routeObject.activityData };
                this.queryCommentLayer(queryObject);
                topic.publish("hideProgressIndicator");
            }
        },

        /**
        * Query on Comment Layer
        * @param {object} queryObject Contains FeatureData, Index, Layer URL, WidgetName, Address, IsRouteCreated{boolean}
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        queryCommentLayer: function (queryObject) {
            var queryTask, errorMessage, foreignKeyField, primaryKeyField, esriQuery, queryString, deferredArray = [], commentArray = [], k, j, featureId, i, searchSettingsData;
            try {
                searchSettingsData = [];
                for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                    if (appGlobals.operationLayerSettings[i].activitySearchSettings) {
                        searchSettingsData.push(appGlobals.operationLayerSettings[i].activitySearchSettings);
                        break;
                    }
                }
                if (searchSettingsData.length === 0) {
                    this._switchCaseForRoute(queryObject, commentArray, featureId, null);
                }
                if (appGlobals.configData.ActivitySearchSettings && appGlobals.configData.ActivitySearchSettings[0].PrimaryKeyForActivity && this.primaryFieldType) {
                    queryObject.primaryFieldType = this.primaryFieldType;
                }
                // Looping activity search setting for getting comments
                array.forEach(searchSettingsData, (lang.hitch(this, function (SearchSettings) {
                    if (appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.QueryURL !== "") {
                        queryTask = new esri.tasks.QueryTask(SearchSettings.CommentsSettings.QueryURL);
                        primaryKeyField = this.getKeyValue(SearchSettings.PrimaryKeyForActivity) || "";
                        foreignKeyField = this.getKeyValue(SearchSettings.CommentsSettings.ForeignKeyFieldForComment) || "";
                        if (primaryKeyField !== "" && foreignKeyField !== "" && queryObject.FeatureData) {
                            esriQuery = new esri.tasks.Query();
                            esriQuery.outFields = ["*"];
                            esriQuery.returnGeometry = true;
                            if (queryObject.WidgetName.toLowerCase() === "infoactivity") {
                                queryString = this.getQueryStringForComment(this.commentLayerResponse, foreignKeyField, queryObject.primaryFieldType);
                                esriQuery.where = queryString === "" ? "" : string.substitute(queryString, [foreignKeyField, queryObject.FeatureData[primaryKeyField]]);
                                featureId = queryObject.FeatureData[primaryKeyField];
                                this.queryString = queryString;
                            } else {
                                queryString = this.getQueryStringForComment(this.commentLayerResponse, foreignKeyField, queryObject.primaryFieldType);
                                esriQuery.where = queryString === "" ? "" : string.substitute(queryString, [foreignKeyField, queryObject.FeatureData[queryObject.Index].attributes[primaryKeyField]]);
                                this.queryString = queryString;
                            }
                            if (esriQuery.where !== "") {
                                // Setting deferred array
                                deferredArray.push(queryTask.execute(esriQuery, lang.hitch(this, this._executeQueryTask)));
                                // Calling deferred array then to do further query.
                                all(deferredArray).then(lang.hitch(this, function (result) {
                                    commentArray = [];
                                    // If result is got from service call then push data in array for further query.
                                    if (result.length > 0) {
                                        for (j = 0; j < result.length; j++) {
                                            if (result[j] && result[j].features) {
                                                for (k = 0; k < result[j].features.length; k++) {
                                                    commentArray.push(result[j].features[k]);
                                                }
                                            }
                                        }
                                        // If comment array is created then sort data
                                        if (commentArray.length > 0) {
                                            commentArray.sort(lang.hitch(this, function (a, b) {
                                                return b.attributes[this.objectIdForCommentLayer] - a.attributes[this.objectIdForCommentLayer];
                                            }));
                                        }
                                        this._switchCaseForRoute(queryObject, commentArray, featureId);
                                    }
                                }), lang.hitch(this, function (err) {
                                    commentArray = [];
                                    errorMessage = sharedNls.errorMessages.errorInQueringLayer;
                                    // Creating other pod if comment is unable to get, due to some issue.
                                    this._switchCaseForRoute(queryObject, commentArray, featureId, errorMessage);
                                    topic.publish("hideProgressIndicator");
                                }));
                            } else {
                                errorMessage = "";
                                this._switchCaseForRoute(queryObject, commentArray, featureId, errorMessage);
                            }
                        } else {
                            errorMessage = sharedNls.errorMessages.fieldNotConfigured;
                            this._switchCaseForRoute(queryObject, commentArray, featureId, errorMessage);
                        }
                    } else {
                        this._switchCaseForRoute(queryObject, commentArray, featureId, errorMessage);
                    }
                })));
            } catch (error) {
                // Creating other pod if comment is unable to get, due to some issue.
                commentArray = [];
                errorMessage = sharedNls.errorMessages.errorInQueringLayer;
                this._switchCaseForRoute(queryObject, commentArray, featureId, errorMessage);
                topic.publish("hideProgressIndicator");
            }
        },

        /**
        * This function is used to get query string for comment layer
        * @param {object} commentLayerResponse contains comment Layer's Response
        * @param {string} keyField contains field name
        * @param {string} primaryFieldType contains the primary field type
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        getQueryStringForComment: function (commentLayerResponse, keyField, primaryFieldType) {
            var queryString, foreignKeyField;
            // Function to get field type
            foreignKeyField = this.getTypeOfField(commentLayerResponse, keyField);
            queryString = "";
            switch (foreignKeyField) {
            case "string":
                switch (primaryFieldType) {
                case "string":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                case "number":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                case "objectID":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                case "GUID":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                case "GlobalID":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                default:
                    queryString = "";
                }
                break;
            case "number":
                switch (primaryFieldType) {
                case "number":
                    queryString = "${0} =" + "${1}";
                    break;
                case "objectID":
                    queryString = "${0} =" + "${1}";
                    break;
                default:
                    queryString = "";
                }
                break;
            case "objectID":
                switch (primaryFieldType) {
                case "number":
                    queryString = "${0} =" + "${1}";
                    break;
                default:
                    queryString = "";
                }
                break;
            case "GUID":
                switch (primaryFieldType) {
                case "GUID":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                case "GlobalID":
                    queryString = "${0} =" + "'" + "${1}" + "'";
                    break;
                default:
                    queryString = "";
                }
                break;
            default:
                queryString = "";
            }
            return queryString;
        },

        /**
        * This function is used to get type of field
        * @param {object} commentLayerResponse contains comment Layer's Response
        * @param {string} keyField contains field name
        * @return {string} foreignKeyField contains field value type
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        getTypeOfField: function (commentLayerResponse, keyField) {
            var keyFieldType, fieldType, j;
            for (j = 0; j < commentLayerResponse.fields.length; j++) {
                if (commentLayerResponse.fields[j].name === keyField) {
                    fieldType = commentLayerResponse.fields[j].type;
                    break;
                }
            }
            switch (fieldType) {
            case "esriFieldTypeSmallInteger":
                keyFieldType = "number";
                break;
            case "esriFieldTypeInteger":
                keyFieldType = "number";
                break;
            case "esriFieldTypeSingle":
                keyFieldType = "number";
                break;
            case "esriFieldTypeString":
                keyFieldType = "string";
                break;
            case "esriFieldTypeOID":
                keyFieldType = "objectID";
                break;
            case "esriFieldTypeGUID":
                keyFieldType = "GUID";
                break;
            case "esriFieldTypeGlobalID":
                keyFieldType = "GlobalID";
                break;
            default:
                keyFieldType = "none";
            }
            return keyFieldType;
        },

        /**
        * This function calculates route bassed on wedget click
        * @param {object} queryObject Contains FeatureData
        * @param {array} commentArray Contains comments data
        * @param {string} featureId Contains feature Id
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _switchCaseForRoute: function (queryObject, commentArray, featureId, errorMessage) {
            var resultcontent, queryURLLink;
            switch (queryObject.WidgetName.toLowerCase()) {
            // If it is comming from activity search then set bottom pod's data     
            case "activitysearch":
                topic.publish("showProgressIndicator");
                resultcontent = { "value": queryObject.Index };
                this.carouselContainer.removeAllPod();
                this.carouselContainer.addPod(this.carouselPodData);
                this.carouselContainer.showCarouselContainer();
                this.carouselContainer.expandCarousel();
                this.highlightFeature(queryObject.FeatureData[0].geometry);
                if (!queryObject.IsRouteCreated) {
                    this.setCenterAt(queryObject.FeatureData[0].geometry);
                }
                this.setSearchContent(queryObject.FeatureData, false, queryObject.QueryURL, queryObject.WidgetName);
                this._createCommonPods(queryObject, commentArray, resultcontent);
                if (errorMessage) {
                    this.setCommentForError(errorMessage);
                }
                this._setCrouselContainerInSharedCase();
                topic.publish("hideProgressIndicator");
                break;
            case "searchedfacility":
                // If it is comming from click on search pod item then set bottom pod's data
                topic.publish("showProgressIndicator");
                this.highlightFeature(queryObject.FeatureData[queryObject.Index].geometry);
                if (!queryObject.IsRouteCreated) {
                    this.setCenterAt(queryObject.FeatureData[queryObject.Index].geometry);
                }
                resultcontent = { "value": queryObject.Index };
                this._createCommonPods(queryObject, commentArray, resultcontent, queryObject.activityData);
                if (errorMessage) {
                    this.setCommentForError(errorMessage);
                }
                this._setCrouselContainerInSharedCase();
                topic.publish("hideProgressIndicator");
                break;
            case "unifiedsearch":
                // If it is comming from unified search then set bottom pod's data
                topic.publish("showProgressIndicator");
                resultcontent = { "value": 0 };
                this.carouselContainer.removeAllPod();
                this.carouselContainer.addPod(this.carouselPodData);
                this.carouselContainer.showCarouselContainer();
                this.carouselContainer.expandCarousel();
                this.highlightFeature(queryObject.FeatureData[0].geometry);
                if (!queryObject.IsRouteCreated) {
                    this.setCenterAt(queryObject.FeatureData[queryObject.Index].geometry);
                }
                queryURLLink = this.getQueryURLWithUnifiedSearch(this.routeObject.activityData, this.routeObject.EndPoint[this.routeObject.Index].distance);
                queryURLLink = queryURLLink === "" ? this.routeObject.QueryURL : queryURLLink;
                queryObject.QueryURL = queryURLLink;
                this.setSearchContent(queryObject.FeatureData, false, queryObject.QueryURL, queryObject.WidgetName, queryObject.activityData);
                this._createCommonPods(queryObject, commentArray, resultcontent, queryObject.activityData);
                if (errorMessage) {
                    this.setCommentForError(errorMessage);
                }
                this._setCrouselContainerInSharedCase();
                topic.publish("hideProgressIndicator");
                this.widgetName = true;
                break;
            case "geolocation":
                // If it is comming from geolocation search widget then set bottom pod's data
                topic.publish("showProgressIndicator");
                resultcontent = { "value": 0 };
                this.carouselContainer.showCarouselContainer();
                this.carouselContainer.expandCarousel();
                if (dijit.registry.byId("Geolocation")) {
                    this.highlightFeature(queryObject.FeatureData[0].geometry);
                }
                if (!queryObject.IsRouteCreated) {
                    this.setCenterAt(queryObject.FeatureData[queryObject.Index].geometry);
                }
                queryURLLink = this.getQueryURLWithUnifiedSearch(this.routeObject.activityData, this.routeObject.EndPoint[this.routeObject.Index].distance);
                queryURLLink = queryURLLink === "" ? this.routeObject.QueryURL : queryURLLink;
                queryObject.QueryURL = queryURLLink;
                this.setSearchContent(queryObject.FeatureData, false, queryObject.QueryURL, queryObject.WidgetName, queryObject.activityData);
                this._createCommonPods(queryObject, commentArray, resultcontent, queryObject.activityData);
                if (errorMessage) {
                    this.setCommentForError(errorMessage);
                }
                this._setCrouselContainerInSharedCase();
                topic.publish("hideProgressIndicator");
                break;
            case "infoactivity":
                // If it is comming from info window's direction widget  search then set bottom pod's data
                resultcontent = { "value": 0 };
                if (commentArray !== null) {
                    this._setInfoWindowComment(commentArray, featureId, queryObject, errorMessage);
                }
                topic.publish("hideProgressIndicator");
                break;
            case "default":
                break;
            }
        },

        /**
        * Set the carousel container state (hide/show) in share case.
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _setCrouselContainerInSharedCase: function () {
            // Checking for bottom pod's status in the case of share
            if (window.location.href.toString().split("$isShowPod=").length > 1) {
                if (window.location.href.toString().split("$isShowPod=")[1].split("$")[0].toString() === "false") {
                    // Collapsing down the carousel container
                    if (this.carouselContainer) {
                        this.carouselContainer.collapseCarousel();
                    }
                }
                if (this.isExtentSet && this.carouselContainer) {
                    // Checking  for the widget name if it is not coming from info window direction
                    if (this.routeObject.WidgetName.toLowerCase() !== "infoactivity" && this.routeObject.WidgetName.toLowerCase() !== "infoevent") {
                        this.carouselContainer.expandCarousel();
                    }
                }
            }
        },

        /**
        * Execute query task for Comment Layer
        * @param {object} relatedRecords Contains Comment layer URL
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _executeQueryTask: function (relatedRecords) {
            var featureSet, i, deferred, features = [];
            deferred = new Deferred();
            featureSet = new esri.tasks.FeatureSet();
            // Check if relatedRecords contains features or not
            if (relatedRecords.features.length > 0) {
                // Loop for the features of comment layer and push the data into features
                for (i = 0; i < relatedRecords.features.length; i++) {
                    if (relatedRecords.features.hasOwnProperty(i)) {
                        features.push(relatedRecords.features[i]);
                    }
                }
                featureSet.features = features;
            }
            deferred.resolve(relatedRecords);
        },

        /**
        * Set the error message in comments carousel pod in case of error
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        setCommentForError: function (errorMessage) {
            var isPodEnabled = this.getPodStatus("CommentsPod"), divHeaderContent, divCommentRow;
            // If pod setting is true then remove div's item and set error message in pod.
            if (isPodEnabled) {
                divHeaderContent = query('.esriCTDivCommentContent');
                if (divHeaderContent[0]) {
                    domConstruct.empty(divHeaderContent[0]);
                }
                divCommentRow = domConstruct.create("div", { "class": "esriCTDivCommentRow" }, divHeaderContent[0]);
                domConstruct.create("div", { "class": "esriCTInfotextRownoComment", "innerHTML": errorMessage }, divCommentRow);
            }
        },

        /**
        * Execute this function when route is not calculated due to any error
        * @param{object} routeObject contains route information
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _executeWhenRouteNotCalculated: function (routeObject) {
            // Check if widgetName is activitysearch
            if (routeObject.WidgetName.toLowerCase() === "activitysearch") {
                this.removeBuffer();
                this.executeWithoutGeolocation(this.featureSetWithoutNullValue, routeObject.QueryURL, routeObject.WidgetName, 0);
            }
            // Check if widgetName is searchedfacility
            if (routeObject.WidgetName.toLowerCase() === "searchedfacility") {
                //this.removeBuffer(); //commented out per github issue #214
                this.executeWithoutGeolocation(this.featureSetWithoutNullValue, routeObject.QueryURL, routeObject.WidgetName, routeObject.Index);
            }
            // Check if widgetName is event
            if (routeObject.WidgetName.toLowerCase() === "event") {
                this.removeBuffer();
                this.executeWithoutGeolocation(this.featureSetWithoutNullValue, routeObject.QueryURL, routeObject.WidgetName, routeObject.Index);
            }
            if (routeObject.WidgetName.toLowerCase() === "unifiedsearch") {
                this.removeBuffer();
                this.executeWithoutGeolocation(this.featureSetWithoutNullValue, routeObject.QueryURL, routeObject.WidgetName, routeObject.Index);
            }
            if (routeObject.WidgetName.toLowerCase() === "geolocation") {
                //this.removeBuffer(); //commented out per github issue #214
                this.executeWithoutGeolocation(this.featureSetWithoutNullValue, routeObject.QueryURL, routeObject.WidgetName, routeObject.Index);
            }
        },

        /**
        * Common bottom pod creation
        * @param {object} queryObject contains feature information
        * @param {array} commentArray contains comment data
        * @param {object} resultcontent contains information about the selected row
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _createCommonPods: function (queryObject, commentArray, resultcontent) {
            var directionObject, facilityObject;
            facilityObject = { "Feature": queryObject.FeatureData, "SelectedItem": resultcontent, "QueryURL": queryObject.QueryURL, "WidgetName": queryObject.WidgetName, "activityData": queryObject.activityData };
            this.setFacility(facilityObject);
            // Checking for Driving direction setting is enabled
            if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                // Check whether the solve route is null
                if (queryObject.SolveRoute === null) {
                    this._createAddressSearchTextBox(queryObject, resultcontent);
                } else {
                    if (queryObject.SolveRoute && queryObject.SolveRoute[0].directions && queryObject.SolveRoute[0].directions.totalLength <= 0) {
                        directionObject = { "Feature": queryObject.FeatureData, "SelectedItem": resultcontent, "SolveRoute": queryObject.SolveRoute, "Address": queryObject.Address, "WidgetName": queryObject.WidgetName, "activityData": queryObject.activityData, "QueryURL": queryObject.QueryURL };
                        this.setDirection(directionObject);
                    } else if (this.routeObject.isLayerCandidateClicked) {
                        this._createAddressSearchTextBox(queryObject, resultcontent);
                    } else {
                        directionObject = { "Feature": queryObject.FeatureData, "SelectedItem": resultcontent, "SolveRoute": queryObject.SolveRoute, "Address": queryObject.Address, "WidgetName": queryObject.WidgetName, "activityData": queryObject.activityData, "QueryURL": queryObject.QueryURL };
                        this.setDirection(directionObject);
                    }
                }
            }
            this.setGallery(queryObject, resultcontent);
            // If comment is not null, set comment in comment pod
            if (commentArray !== null) {
                this.setComment(queryObject.FeatureData, commentArray, resultcontent, queryObject.QueryURL);
            } else {
                this.removeCommentPod();
            }
        },

        /**
        * Create address search text box when route is not calculated
        * @param {object} queryObject contains feature information
        * @param {object} resultcontent contains result item no
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _createAddressSearchTextBox: function (queryObject, resultcontent) {
            var searchContenData, searchSetting, activityMapPoint, locatorParamsForCarouselContainer, locatorObjectForCarouselContainer, routeObject, divDirectioncontent, divHeader, mapPoint;
            divDirectioncontent = query(".esriCTDivDirectioncontent")[0];
            if (divDirectioncontent) {
                domConstruct.empty(divDirectioncontent);
                locatorParamsForCarouselContainer = {
                    defaultAddress: appGlobals.configData.LocatorSettings.LocatorDefaultAddress,
                    preLoaded: false,
                    parentDomNode: divDirectioncontent,
                    map: this.map,
                    graphicsLayerId: this.locatorGraphicsLayerID,
                    locatorSettings: appGlobals.configData.LocatorSettings,
                    configSearchSettings: appGlobals.configData.SearchSettings
                };
                // Getting the key value from search display field from config file
                searchSetting = this.getSearchSetting(queryObject.QueryURL);
                searchContenData = this.getKeyValue(searchSetting.SearchDisplayFields);
                divHeader = domConstruct.create("div", {}, divDirectioncontent);
                domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": sharedNls.titles.directionText + " " + queryObject.FeatureData[resultcontent.value].attributes[searchContenData] }, divHeader);
                activityMapPoint = this.map.getLayer(locatorParamsForCarouselContainer.graphicsLayerId);
                locatorObjectForCarouselContainer = new LocatorTool(locatorParamsForCarouselContainer);
                locatorObjectForCarouselContainer.candidateClicked = lang.hitch(this, function (graphic) {
                    // Checking for selectedGraphic from locator
                    if (locatorObjectForCarouselContainer && locatorObjectForCarouselContainer.selectedGraphic) {
                        appGlobals.shareOptions.addressLocationDirectionActivity = locatorObjectForCarouselContainer.selectedGraphic.geometry.x.toString() + "," + locatorObjectForCarouselContainer.selectedGraphic.geometry.y.toString();
                    }
                    // Check graphic address
                    if (graphic && graphic.attributes && graphic.attributes.address) {
                        this.locatorAddress = graphic.attributes.address;
                    }
                    if (graphic && graphic.layer) {
                        this.selectedLayerTitle = graphic.layer.SearchDisplayTitle;
                    }
                    this._clearBuffer();
                    this.removeGeolocationPushPin();
                    // Checking for graphic layer for showing results in pod
                    if (graphic && graphic.layer) {
                        this.selectedGraphic = graphic;
                        if (queryObject.WidgetName.toLowerCase() === "unifiedsearch" || queryObject.WidgetName.toLowerCase() === "geolocation") {
                            routeObject = { "StartPoint": graphic, "EndPoint": queryObject.FeatureData, "Index": queryObject.Index, "WidgetName": queryObject.WidgetName, "QueryURL": queryObject.QueryURL, "activityData": queryObject.activityData };
                        } else {
                            routeObject = { "StartPoint": graphic, "EndPoint": queryObject.FeatureData, "Index": queryObject.Index, "WidgetName": queryObject.WidgetName, "QueryURL": queryObject.QueryURL };
                        }
                        this.showRoute(routeObject);
                        if (locatorObjectForCarouselContainer && locatorObjectForCarouselContainer.selectedGraphic === null) {
                            appGlobals.shareOptions.addressLocationDirectionActivity = graphic.geometry.x.toString() + "," + graphic.geometry.y.toString();
                        }
                    } else {
                        if (queryObject.WidgetName.toLowerCase() === "unifiedsearch" || queryObject.WidgetName.toLowerCase() === "geolocation") {
                            routeObject = { "StartPoint": activityMapPoint.graphics[0], "EndPoint": queryObject.FeatureData, "Index": queryObject.Index, "WidgetName": queryObject.WidgetName, "QueryURL": queryObject.QueryURL, "activityData": queryObject.activityData };
                        } else {
                            routeObject = { "StartPoint": activityMapPoint.graphics[0], "EndPoint": queryObject.FeatureData, "Index": queryObject.Index, "WidgetName": queryObject.WidgetName, "QueryURL": queryObject.QueryURL };
                        }
                        appGlobals.shareOptions.addressLocationDirectionActivity = activityMapPoint.graphics[0].geometry.x.toString() + "," + activityMapPoint.graphics[0].geometry.y.toString();
                        this.showRoute(routeObject);

                    }
                });
                // Check if "addressLocationDirectionActivity" is there in share URL or not.
                if (window.location.href.toString().split("$addressLocationDirectionActivity=").length > 1) {
                    mapPoint = new Point(window.location.href.toString().split("$addressLocationDirectionActivity=")[1].split("$")[0].split(",")[0], window.location.href.toString().split("$addressLocationDirectionActivity=")[1].split("$")[0].split(",")[1], this.map.spatialReference);
                    locatorObjectForCarouselContainer._locateAddressOnMap(mapPoint);
                    this._clearBuffer();
                    routeObject = { "StartPoint": activityMapPoint.graphics[0], "EndPoint": queryObject.FeatureData, "Index": queryObject.Index, "WidgetName": queryObject.WidgetName, "QueryURL": queryObject.QueryURL };
                    this.showRoute(routeObject);
                }
            }
        },

        /**
        * Create pod when comment layer fires some error
        * @param {object} queryObject contains feature information
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _createPodWithoutCommentLayer: function (queryObject) {
            var resultcontent;
            // Check if widgetName is activitysearch.
            if (queryObject.WidgetName.toLowerCase() === "activitysearch") {
                topic.publish("showProgressIndicator");
                resultcontent = { "value": 0 };
                this.carouselContainer.removeAllPod();
                this.carouselContainer.addPod(this.carouselPodData);
                this.carouselContainer.showCarouselContainer();
                this.carouselContainer.expandCarousel();
                this.highlightFeature(queryObject.FeatureData[0].geometry);
                this.setCenterAt(queryObject.FeatureData[0].geometry);
                this.setSearchContent(queryObject.FeatureData, false, queryObject.QueryURL, queryObject.WidgetName);
                this._createCommonPods(queryObject, null, resultcontent);
                this.setCommentForError();
                topic.publish("hideProgressIndicator");
            }
            // Check if widgetName is searchedfacility.
            if (queryObject.WidgetName.toLowerCase() === "searchedfacility") {
                topic.publish("showProgressIndicator");
                this.highlightFeature(queryObject.FeatureData[queryObject.Index].geometry);
                this.setCenterAt(queryObject.FeatureData[queryObject.Index].geometry);
                resultcontent = { "value": queryObject.Index };
                this._createCommonPods(queryObject, null, resultcontent);
                this.setCommentForError();
                topic.publish("hideProgressIndicator");
            }
        },

        /**
        * Create route for multiple points
        * @param {object} contains Start point, End point, widget name and query URL
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _showRouteForList: function (routeObject) {
            var geoArray, i;
            topic.publish("showProgressIndicator");
            this.clearGraphicsAndCarousel();
            this.removeRouteGraphichOfDirectionWidget();
            this.routeObject = routeObject;
            topic.publish("routeObject", this.routeObject);
            geoArray = [];
            geoArray.push(routeObject.StartPoint.geometry);
            // Looping for route object for pushing points.
            for (i = 0; i < routeObject.EndPoint.length; i++) {
                geoArray.push(routeObject.EndPoint[i].geometry);
            }
            // Calling update stops function for showing points on map and calculating direction
            this._esriDirectionsWidget.updateStops(geoArray).then(lang.hitch(this, function () {
                this._esriDirectionsWidget.getDirections();
            }), function (err) {
                alert(sharedNls.errorMessages.routeComment);
                topic.publish("hideProgressIndicator");
            });
        },

        /**
        * Get the feature within buffer and sort it in ascending order.
        * @param {object} featureSetObject Contains Feature
        * @param {string} QueryURL Contains layer URL
        * @param {string} widgetName Contains name of widget
        * @param {bool} featureClick
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        _executeQueryForFeatures: function (featureSetObject, QueryURL, widgetName, featureClick) {
            var featureSet, i, dist, isDistanceFound, isZoomToGeolocation;
            // Calling function for removing null value from feature
            this.featureSetWithoutNullValue = this.removeNullValue(featureSetObject);
            // Calling function to change date attribute format
            featureSet = [];
            isDistanceFound = false;
            isZoomToGeolocation = this.setZoomForGeolocation();
            this.featureClick = featureClick;
            // If browser is supporting geolocation then proceed else show message.
            if (Modernizr.geolocation) {
                // If geolocation widget is configured
                if (dijit.registry.byId("geoLocation")) {
                    // Call show current location.
                    dijit.registry.byId("geoLocation").showCurrentLocation(false, isZoomToGeolocation);
                    // Call back of geolocation complete
                    dijit.registry.byId("geoLocation").onGeolocationComplete = lang.hitch(this, function (mapPoint, isPreLoaded) {
                        // If mappoint is found then clean graphics
                        if (mapPoint) {
                            this._clearBuffer();
                            this.removeLocatorPushPin();
                            // If it is not coming from geolocation widget
                            if (!isPreLoaded) {
                                // Looping for features
                                for (i = 0; i < this.featureSetWithoutNullValue.length; i++) {
                                    // If mappoint has geometry calculate distance
                                    if (mapPoint.geometry) {
                                        dist = this.getDistance(mapPoint.geometry, this.featureSetWithoutNullValue[i].geometry);
                                        isDistanceFound = true;
                                    }
                                    try {
                                        featureSet[i] = this.featureSetWithoutNullValue[i];
                                        this.featureSetWithoutNullValue[i].distance = dist.toString();
                                    } catch (err) {
                                        alert(sharedNls.errorMessages.falseConfigParams);
                                    }
                                }
                                // If distance is found then sort feature basic of distance
                                if (isDistanceFound) {
                                    featureSet.sort(function (a, b) {
                                        return parseFloat(a.distance) - parseFloat(b.distance);
                                    });
                                    this.featureSetWithoutNullValue = featureSet;
                                    this.highlightFeature(featureSet[0].geometry);
                                    var routeObject = { "StartPoint": mapPoint, "EndPoint": this.featureSetWithoutNullValue, "Index": 0, "WidgetName": widgetName, "QueryURL": QueryURL };
                                    this.showRoute(routeObject);
                                } else {
                                    // If point is not found then show message
                                    alert(sharedNls.errorMessages.invalidProjection);
                                    // Function for creating bottom pod without geolocation point, setting first feature in bottom pod as selected and others in search pod
                                    this.executeWithoutGeolocation(this.featureSetWithoutNullValue, QueryURL, widgetName, 0);
                                }
                            } else {
                                // if it is coming from geolocation widget
                                this.selectedLayerTitle = null;
                                appGlobals.shareOptions.doQuery = "false";
                                appGlobals.shareOptions.addressLocationDirectionActivity = null;
                                appGlobals.shareOptions.searchFacilityIndex = -1;
                                appGlobals.shareOptions.addressLocation = null;
                                appGlobals.shareOptions.sharedGeolocation = mapPoint;
                                topic.publish("extentSetValue", true);
                                topic.publish("hideInfoWindow");
                                appGlobals.shareOptions.eventRoutePoint = null;
                                this.removeRouteGraphichOfDirectionWidget();
                                this.createBuffer(mapPoint, "geolocation");
                            }
                        } else {
                            // Function for creating bottom pod without geolocation point, setting first feature in bottom pod as selected and others in search pod
                            this.removeLocatorPushPin();
                            this.selectedLayerTitle = null;
                            this.executeWithoutGeolocation(this.featureSetWithoutNullValue, QueryURL, widgetName, 0);
                        }
                    });
                    dijit.registry.byId("geoLocation").onGeolocationError = lang.hitch(this, function (error, isPreLoaded) {
                        if (isPreLoaded) {
                            appGlobals.shareOptions.eventRoutePoint = null;
                            topic.publish("extentSetValue", true);
                            topic.publish("hideInfoWindow");
                            this.removeHighlightedCircleGraphics();
                            // Checking for carousel container for hiding carousel container and setting legend position
                            if (this.carouselContainer) {
                                this.carouselContainer.hideCarouselContainer();
                                this.carouselContainer._setLegendPositionDown();
                            }
                        }
                        if (!isPreLoaded) {
                            // AppGlobals.shareOptions.eventRoutePoint = null;
                            this.removeLocatorPushPin();
                            topic.publish("hideProgressIndicator");
                            this.removeRouteGraphichOfDirectionWidget();
                            topic.publish("hideInfoWindow");
                            this.executeWithoutGeolocation(this.featureSetWithoutNullValue, QueryURL, widgetName, 0);
                        }
                    });
                } else {
                    // Calling error message when geoloation widget is not configured.
                    topic.publish("hideProgressIndicator");
                    alert(sharedNls.errorMessages.geolocationWidgetNotFoundMessage);
                }
            } else {
                // Calling error message when geolocation is not supported
                topic.publish("hideProgressIndicator");
                alert(sharedNls.errorMessages.activitySearchGeolocationText);
                appGlobals.shareOptions.eventRoutePoint = null;
                this.removeLocatorPushPin();
                topic.publish("clearGraphicsAndCarousel");
                topic.publish("removeRouteGraphichOfDirectionWidget");
                topic.publish("hideProgressIndicator");
                topic.publish("hideInfoWindow");
                this.executeWithoutGeolocation(this.featureSetWithoutNullValue, QueryURL, widgetName, 0);
            }
        },

        /**
        * Execute when geolocation is not found.
        * @param {object} featureset Contains information of feature
        * @param {string} Query URL of the layer
        * @param {string} widget name
        * @param {number} index
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        executeWithoutGeolocation: function (featureSetWithoutNullValue, QueryURL, widgetName, index) {
            var queryURLLink, queryObject;
            appGlobals.shareOptions.addressLocationDirectionActivity = null;
            // If query is coming from event layer
            if (widgetName.toLowerCase() === "event") {
                this.showEventSearchedDataInPod(featureSetWithoutNullValue, QueryURL, widgetName, index);
                topic.publish("hideProgressIndicator");
            } else if (widgetName.toLowerCase() === "unifiedsearch" || widgetName.toLowerCase() === "geolocation") {
                appGlobals.shareOptions.eventRoutePoint = null;
                topic.publish("showProgressIndicator");
                queryURLLink = this.getQueryURLWithUnifiedSearch(this.routeObject.activityData, this.routeObject.EndPoint[this.routeObject.Index].distance);
                queryURLLink = queryURLLink === "" ? this.routeObject.QueryURL : queryURLLink;
                if (queryURLLink === appGlobals.configData.ActivitySearchSettings[0].QueryURL) {
                    queryObject = { "FeatureData": this.routeObject.EndPoint, "SolveRoute": null, "Index": index, "QueryURL": queryURLLink, "WidgetName": widgetName, "Address": null, "IsRouteCreated": false, "activityData": this.routeObject.activityData };
                    topic.publish("showProgressIndicator");
                    this.queryCommentLayer(queryObject);
                } else {
                    topic.publish("showProgressIndicator");
                    this.showEventSearchedDataInPod(this.routeObject.EndPoint, QueryURL, widgetName, index, this.routeObject.activityData);
                }
            } else {
                // If it coming from other then event layer then query comment layer, because in event layer we do not have comment layer settings
                appGlobals.shareOptions.eventRoutePoint = null;
                appGlobals.shareOptions.sharedGeolocation = null;
                queryObject = { "FeatureData": featureSetWithoutNullValue, "SolveRoute": null, "Index": index, "QueryURL": QueryURL, "WidgetName": widgetName, "Address": null, "IsRouteCreated": false };
                topic.publish("showProgressIndicator");
                this.queryCommentLayer(queryObject);
                topic.publish("hideProgressIndicator");
            }
        },

        /**
        * Execute when search for even in performed
        * @param {object} featureSetWithoutNullValue Contains information of feature
        * @param {object} Query URL of the layer
        * @param {string} widget name
        * @param {string} index contains the searched item count
        * @param {object} activityData contains the unifed search data
        * @memberOf widgets/commonHelper/directionWidgetHelper
        */
        showEventSearchedDataInPod: function (featureSetWithoutNullValue, QueryURL, widgetName, index, activityData) {
            var facilityObject, resultcontent, queryObject, searchSetting, locatorParamsForEventContainer, divDirectioncontent, divHeader, eventMapPoint, routeObject, searchContenData;
            appGlobals.shareOptions.doQuery = "false";
            this.removeCommentPod();
            // Removing null value from features
            featureSetWithoutNullValue = this.removeNullValue(featureSetWithoutNullValue);
            this.removeHighlightedCircleGraphics();
            topic.publish("showProgressIndicator");
            if (this.carouselContainer) {
                this.carouselContainer.showCarouselContainer();
            }
            // If it is coming from share url then maintain the pod state.
            if (window.location.toString().split("isShowPod=").length > 1 && window.location.toString().split("isShowPod=")[1].toString().split("$")[0] === "false") {
                this.carouselContainer.collapseCarousel();
            } else {
                this.carouselContainer.expandCarousel();
            }
            // Highlight feature
            this.highlightFeature(featureSetWithoutNullValue[index].geometry);
            // Set it in center
            this.setCenterAt(featureSetWithoutNullValue[index].geometry);
            // Creating search content box
            this.setSearchContent(featureSetWithoutNullValue, false, QueryURL, widgetName, activityData);
            appGlobals.shareOptions.sharedGeolocation = "false";
            resultcontent = { "value": index };
            facilityObject = { "Feature": featureSetWithoutNullValue, "SelectedItem": resultcontent, "QueryURL": QueryURL, "WidgetName": widgetName, "activityData": activityData };
            // Setting facility pod in bottom pod
            this.setFacility(facilityObject);
            divDirectioncontent = query(".esriCTDivDirectioncontent")[0];
            // If direction pod is not created then show direction tab
            if (divDirectioncontent) {
                domConstruct.empty(divDirectioncontent);
                locatorParamsForEventContainer = {
                    defaultAddress: appGlobals.configData.LocatorSettings.LocatorDefaultAddress,
                    preLoaded: false,
                    parentDomNode: divDirectioncontent,
                    map: this.map,
                    graphicsLayerId: this.locatorGraphicsLayerID,
                    locatorSettings: appGlobals.configData.LocatorSettings,
                    configSearchSettings: appGlobals.configData.SearchSettings
                };
                // Getting search display field from layer
                searchSetting = this.getSearchSetting(QueryURL);
                searchContenData = this.getKeyValue(searchSetting.SearchDisplayFields);
                divHeader = domConstruct.create("div", {}, divDirectioncontent);
                domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": sharedNls.titles.directionText + " " + featureSetWithoutNullValue[0].attributes[searchContenData] }, divHeader);
                locatorParamsForEventContainer = new LocatorTool(locatorParamsForEventContainer);
                eventMapPoint = this.map.getLayer(locatorParamsForEventContainer.graphicsLayerId);
                // Calling candidate click function for showing route and data in bottom pod
                locatorParamsForEventContainer.candidateClicked = lang.hitch(this, function (graphic) {
                    if (locatorParamsForEventContainer && locatorParamsForEventContainer.selectedGraphic) {
                        appGlobals.shareOptions.addressLocationDirectionActivity = locatorParamsForEventContainer.selectedGraphic.geometry.x.toString() + "," + locatorParamsForEventContainer.selectedGraphic.geometry.y.toString();
                    }
                    if (graphic && graphic.attributes && graphic.attributes.address) {
                        this.locatorAddress = graphic.attributes.address;
                    }
                    if (graphic && graphic.layer) {
                        this.selectedLayerTitle = graphic.layer.SearchDisplayTitle;
                    }
                    appGlobals.shareOptions.doQuery = "false";
                    topic.publish("hideInfoWindow");
                    this.removeGeolocationPushPin();
                    // Checking for graphics for showing route
                    if (graphic && graphic.layer) {
                        if (widgetName.toLowerCase() === "unifiedsearch" || widgetName.toLowerCase() === "geolocation") {
                            routeObject = { "StartPoint": graphic, "EndPoint": featureSetWithoutNullValue, "Index": 0, "WidgetName": widgetName, "QueryURL": QueryURL, "activityData": activityData };
                        } else {
                            routeObject = { "StartPoint": graphic, "EndPoint": featureSetWithoutNullValue, "Index": 0, "WidgetName": widgetName, "QueryURL": QueryURL };
                        }
                        this.showRoute(routeObject);
                        this.selectedEventGraphic = graphic;
                        if (locatorParamsForEventContainer && locatorParamsForEventContainer.selectedGraphic === null) {
                            appGlobals.shareOptions.addressLocationDirectionActivity = graphic.geometry.x.toString() + "," + graphic.geometry.y.toString();
                        }
                    } else {
                        if (widgetName.toLowerCase() === "unifiedsearch" || widgetName.toLowerCase() === "geolocation") {
                            routeObject = { "StartPoint": eventMapPoint.graphics[0], "EndPoint": featureSetWithoutNullValue, "Index": 0, "WidgetName": widgetName, "QueryURL": QueryURL, "activityData": activityData };
                        } else {
                            routeObject = { "StartPoint": eventMapPoint.graphics[0], "EndPoint": featureSetWithoutNullValue, "Index": 0, "WidgetName": widgetName, "QueryURL": QueryURL };
                        }
                        this.showRoute(routeObject);
                    }
                });
            }
            // Invoke function for setting content in gallery
            queryObject = { "FeatureData": featureSetWithoutNullValue, "WidgetName": widgetName, "QueryURL": QueryURL, "activityData": null };
            this.setGallery(queryObject, resultcontent);
            this.removeCommentPod();
            // Function for share in the case of address searh from activity in bottom pod.
            setTimeout(lang.hitch(this, function () {
                // If it is a share url and direction is calculated from bottom pod then show route for the same
                if (window.location.href.toString().split("$addressLocationDirectionActivity=").length > 1 && window.location.href.toString().split("$addressLocationDirectionActivity=")[1].substring(0, 18) !== "$sharedGeolocation") {
                    var mapPoint = new Point(window.location.href.toString().split("$addressLocationDirectionActivity=")[1].split("$")[0].split(",")[0], window.location.href.toString().split("$addressLocationDirectionActivity=")[1].split("$")[0].split(",")[1], this.map.spatialReference);
                    locatorParamsForEventContainer._locateAddressOnMap(mapPoint, true);
                    routeObject = { "StartPoint": eventMapPoint.graphics[0], "EndPoint": featureSetWithoutNullValue, "Index": 0, "WidgetName": widgetName, "QueryURL": QueryURL };
                    this.showRoute(routeObject);
                }
            }, 20000));
            topic.publish("hideProgressIndicator");
        }
    });
});
