/*global define,dojo,dojoConfig:true,alert,esri,console,Modernizr,dijit,appGlobals */
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
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-attr",
    "dojo/_base/lang",
    "dojo/on",
    "dojo/window",
    "dojo/_base/array",
    "dojo/dom-class",
    "dojo/query",
    "dojo/string",
    "dojo/date/locale",
    "esri/geometry/Point",
    "dijit/_WidgetBase",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "widgets/locator/locator",
    "dijit/a11yclick",
    "widgets/commonHelper/commonHelper",
    "widgets/commonHelper/locatorHelper",
    "widgets/commonHelper/carouselContainerHelper",
    "widgets/commonHelper/directionWidgetHelper",
    "widgets/commonHelper/infoWindowCommentPod",
    "esri/request",
    "dojo/NodeList-manipulate",
    "widgets/geoLocation/geoLocation"

], function (declare, dom, domConstruct, domAttr, lang, on, win, array, domClass, query, string, locale, Point, _WidgetBase, sharedNls, topic, LocatorTool, a11yclick, CommonHelper, LocatorHelper, CarouselContainerHelper, DirectionWidgetHelper, InfoWindowCommentPod, esriRequest, GeoLocation) {
    //========================================================================================================================//

    return declare([_WidgetBase, CommonHelper, LocatorHelper, CarouselContainerHelper, DirectionWidgetHelper, InfoWindowCommentPod], {
        sharedNls: sharedNls,                                   // Variable for shared NLS
        featureArrayInfoWindow: null,                           // Variable store the feature in infowindow
        rankValue: null,                                        // Variable for the rank value in post comment tab
        isDirectionCalculated: false,                           // Variable for Direction calculate
        infoWindowFeatureData: [],                              // array to store for feature data of infoWindow
        objectIdForCommentLayer: "",                            // Variable to store the objectId for comment layer
        objectIdForActivityLayer: "",                           // Variable to store the objectId for activity layer
        carouselPodData: [],                                    // Array to store carouselPod data
        myListStore: [],                                        // Array to store myList data
        addToListFeatures: [],                                  // Array to store feature added to mylist from info window or infow pod
        isGalleryPodEnabled: true,                              // variable for gallery pod enabled status for showing pod in bottom pod
        zoomToFullRoute: true,                                   // variable to check for zooming to full route
        /**
        * display info window widget
        *
        * @class
        * @name widgets/locator/locator
        */
        postCreate: function () {
            // Appending ActivitySearchSettings and EventSearchSettings into SearchSettings
            appGlobals.configData.SearchSettings = [];
            this._addSearchSettings(appGlobals.configData.ActivitySearchSettings);
            this._addSearchSettings(appGlobals.configData.EventSearchSettings);
            // Calling function for create direction widget.
            this._createDirectionWidget();
            // Calling function for create carousel container
            this._createCarouselContainer();
            // Calling function for create carousel pod
            this.createCarouselPod();
            // Calling function for get the layer information.
            this.getLayerInformaition();
            // Calling function if geolocation is clicked from widget.
            this._geolocationClicked();
            /** Subscribe functions for calling them from other widget
            *  subscribing to create infoWindow content
            */
            topic.publish("getCarouselContainer", this.carouselContainer, this.carouselPodData);
            this.callSubscribeFunctions();
            topic.subscribe("createInfoWindowContent", lang.hitch(this, function (infoWindowParameter) {
                this._createInfoWindowContent(infoWindowParameter);
            }));
        },

        /**
        * Subscribed function call
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        callSubscribeFunctions: function () {
            topic.subscribe("setMapTipPosition", this._onSetMapTipPosition);
            // Get widgetName from other widget
            topic.subscribe("getInfowWindowWidgetName", lang.hitch(this, function (LayerTitle, LayerId) {
                var widgetName;
                widgetName = this.getInfowWindowWidgetName(LayerTitle, LayerId);
                topic.publish("showWidgetName", widgetName);
            }));
            // Subscribing function for infoWindow direction tab
            topic.subscribe("showDirection", lang.hitch(this, function (directionObject) {
                this._infoWindowDirectionTab(directionObject);
            }));
            // Subscribing function for hiding carousel pod
            topic.subscribe("hideCarouselContainer", lang.hitch(this, function () {
                this.hideCarouselContainer();
            }));
            // Subscribing function for setting myListStoreData
            topic.subscribe("getMyListStoreData", lang.hitch(this, function (value) {
                this.myListStore = value;
            }));
            // Subscribing function setting id for layers
            topic.subscribe("setLayerId", lang.hitch(this, function (geoLocationGraphicsLayerID, locatorGraphicsLayerID) {
                this.geoLocationGraphicsLayerID = geoLocationGraphicsLayerID;
                this.locatorGraphicsLayerID = locatorGraphicsLayerID;
            }));
            // Subscribing function for route Object
            topic.subscribe("routeObject", lang.hitch(this, function (value) {
                this.routeObject = value;
            }));
            // Subscribing to store value for extent from other widget.
            topic.subscribe("extentSetValue", lang.hitch(this, function (value) {
                this.isExtentSet = value;
            }));
            topic.subscribe("getCarouselContainerData", lang.hitch(this, function () {
                topic.publish("getCarouselContainer", this.carouselContainer, this.carouselPodData);
            }));
            // Subscribing to store value of sortedList
            topic.subscribe("sortMyListData", lang.hitch(this, function (value) {
                this.sortedList = value;
            }));
            // subscribing to remove Highlighted CircleGraphics
            topic.subscribe("removeHighlightedCircleGraphics", lang.hitch(this, function () {
                this.removeHighlightedCircleGraphics();
            }));
            // Subscribing to remove Buffer
            topic.subscribe("removeBuffer", lang.hitch(this, function () {
                this.removeBuffer();
            }));
            // Subscribing to remove Geolocation PushPin
            topic.subscribe("removeGeolocationPushPin", lang.hitch(this, function () {
                this.removeGeolocationPushPin();
            }));
            // Subscribing to remove Geolocation PushPin
            topic.subscribe("routeForListFunction", lang.hitch(this, function (routeObject) {
                this._showRouteForList(routeObject);
            }));
            // Subscribing to remove remove Locator PushPin
            topic.subscribe("removeLocatorPushPin", lang.hitch(this, function () {
                this.removeLocatorPushPin();
            }));
            // Subscribing to remove Route Graphic Of Direction Widget
            topic.subscribe("removeRouteGraphichOfDirectionWidget", lang.hitch(this, function () {
                this.removeRouteGraphichOfDirectionWidget();
            }));
            // Subscribing to _clear Graphics And Carousel
            topic.subscribe("clearGraphicsAndCarousel", lang.hitch(this, function () {
                this.clearGraphicsAndCarousel();
            }));
            // Subscribing to set Zoom And CenterAt
            topic.subscribe("setZoomAndCenterAt", lang.hitch(this, function (value) {
                this.setZoomAndCenterAt(value);
            }));
            // Subscribing to creating Buffer
            topic.subscribe("createBuffer", lang.hitch(this, function (value, widgetName) {
                this.createBuffer(value, widgetName);
            }));
            // Subscribing to creating Buffer
            topic.subscribe("executeQueryForFeatures", lang.hitch(this, function (records, queryURL, widgetName, featureClick) {
                this._executeQueryForFeatures(records, queryURL, widgetName, featureClick);
            }));
            // Subscribing to creating Buffer
            topic.subscribe("addtoMyListFunction", lang.hitch(this, function (eventDataObject, widgetName) {
                this.addtoMyList(eventDataObject, widgetName);
            }));
            // Subscribing to store feature set searched from event search in eventPlannerHelper.js file.
            topic.subscribe("setEventFeatrueSet", lang.hitch(this, function (value) {
                this.featureSet = value;
            }));
            // Subscribing to store addToList Features Update
            topic.subscribe("addToListFeaturesUpdate", lang.hitch(this, function (value) {
                this.addToListFeatures = value;
            }));
            // Subscribing calling add to list from info window click
            topic.subscribe("addToListFromInfoWindow", lang.hitch(this, function (addToListObject) {
                this.clickOnAddToList(addToListObject);
            }));
            // Subscribing for add To My List Features Data
            topic.subscribe("addToMyList", lang.hitch(this, function (featureArray, widgetName, layerId, layerTitle) {
                this._setWidgetNameFeature(featureArray, widgetName, layerId, layerTitle);
            }));
            // Subscribing for show route function
            topic.subscribe("showRoute", lang.hitch(this, function (routeObject) {
                this.showRoute(routeObject);
            }));
            // Subscribing for adding and removing carousel pod data
            topic.subscribe("addCarouselPod", lang.hitch(this, function () {
                this.carouselContainer.removeAllPod();
                this.carouselContainer.addPod(this.carouselPodData);
                this.removeBuffer();
            }));
            topic.subscribe("executeWhenRouteNotCalculated", lang.hitch(this, function (routeObject) {
                this._executeWhenRouteNotCalculated(routeObject);
            }));
        },

        /**
        * Set the wigdet name of feature which is added in the mylist panel
        * @param {array} featureArray contains the array of feature data
        * @param {string} widgetName contains name od widget
        * @param {number} layerId contains layerId
        * @param {string} layerTitle contains layer title
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _setWidgetNameFeature: function (featureArray, widgetName, layerId, layerTitle) {
            var isExtentSet = true, IsSearchsettingFound = false, infoWindowClick = true, addToListEventFeature = [], addToListActivityFeature = [], i, eventDataObject, objectIDField, startDateField, settingsIndex, eventSettingsObject, j, LayerInfo,
                layerSearchSetting, queryLayerId;
            topic.publish("extentSetValue", isExtentSet);
            LayerInfo = [];
            array.forEach(appGlobals.operationLayerSettings, lang.hitch(this, function (settings) {
                if (settings.layerTitle === layerTitle && settings.layerID === layerId) {
                    LayerInfo.push(settings);
                }
            }));
            array.forEach(LayerInfo, lang.hitch(this, function (settingsName) {
                if (!IsSearchsettingFound) {
                    var key;
                    // Checking for settins for showing data and getting search settins on the basic of settings
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
            array.forEach(layerSearchSetting, lang.hitch(this, function (settings, eventSettingIndex) {
                queryLayerId = parseInt(settings.QueryLayerId, 10);
                if (queryLayerId === layerId && settings.Title === layerTitle) {
                    if (this.searchSettingsName === "eventsettings") {
                        objectIDField = settings.ObjectID;
                        startDateField = settings.SortingKeyField ? this.getKeyValue(settings.SortingKeyField) : "";
                        settingsIndex = eventSettingIndex;
                    } else if (this.searchSettingsName === "activitysettings") {
                        objectIDField = settings.ObjectID;
                        startDateField = "";
                        settingsIndex = eventSettingIndex;
                    }
                }
            }));
            this.featureSetOfInfoWindow = featureArray;
            // Variable is store the infowindow geometry.
            appGlobals.shareOptions.eventInfoWindowData = featureArray.geometry;
            eventSettingsObject = { "settingsName": this.searchSettingsName, "settingsIndex": settingsIndex, "value": this.featureSetOfInfoWindow };
            this.addToListFeatures.push(eventSettingsObject);
            topic.publish("addToListFeaturesData", this.addToListFeatures);
            // Check if widgetName if "infoevent" or not.
            if (this.addToListFeatures.length > 0) {
                for (i = 0; i < this.addToListFeatures.length; i++) {
                    if (this.addToListFeatures[i].settingsName === "eventsettings") {
                        addToListEventFeature.push(this.addToListFeatures[i].value.attributes[objectIDField]);
                        appGlobals.shareOptions.eventInfoWindowAttribute = addToListEventFeature.join(",");
                    }
                }
            }
            // Check if widgetName if "infoactivity" or not.
            if (this.addToListFeatures.length > 0) {
                for (j = 0; j < this.addToListFeatures.length; j++) {
                    if (this.addToListFeatures[j].settingsName === "activitysettings") {
                        addToListActivityFeature.push(this.addToListFeatures[j].value.attributes[objectIDField]);
                        appGlobals.shareOptions.eventInfoWindowIdActivity = addToListActivityFeature.join(",");
                    }
                }
            }
            eventDataObject = { "eventDetails": featureArray.attributes, "featureSet": this.featureSetOfInfoWindow, "infoWindowClick": infoWindowClick, "layerId": layerId, "layerTitle": layerTitle, "ObjectIDField": objectIDField, "StartDateField": startDateField };
            this.addtoMyList(eventDataObject, widgetName);
        },

        /**
        * Address search setting
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _addSearchSettings: function (layerSetting) {
            var i;
            // Loop for the layer and push the layer in appGlobals.configData.SearchSettings
            for (i = 0; i < layerSetting.length; i++) {
                appGlobals.configData.SearchSettings.push(layerSetting[i]);
            }
        },

        /**
        * Function for getting comment layer information like object id.
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        getLayerInformaition: function () {
            var commentLayerRequestData, activityLayerRequestData, eventLayerRequestData, keyField;
            // Calling query layer for activity search comment layer for getting object id field.
            if (appGlobals.configData.ActivitySearchSettings && appGlobals.configData.ActivitySearchSettings.length > 0 && appGlobals.configData.ActivitySearchSettings[0].QueryURL) {
                this.objectIdForActivityLayer = appGlobals.configData.ActivitySearchSettings[0].ObjectID;
                // Checking for object id field from search settings if it is blank then call function for gettins object id field.
                if (this.objectIdForActivityLayer === "") {
                    activityLayerRequestData = this._queryLayerForLayerInformation(appGlobals.configData.ActivitySearchSettings[0].QueryURL);
                }
                // Checking for query url
                if (appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.QueryURL !== "") {
                    commentLayerRequestData = this._queryLayerForLayerInformation(appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.QueryURL);
                } else {
                    this.objectIdForCommentLayer = "";
                }
            }
            // If object id field is blank then call function for getting object id field
            if (this.objectIdForActivityLayer === "") {
                if (activityLayerRequestData) {
                    // If comment layer request data is found.
                    activityLayerRequestData.then(lang.hitch(this, function (response) {
                        topic.publish("showProgressIndicator");
                        this.objectIdForActivityLayer = this.getObjectId(response.fields);
                        appGlobals.configData.ActivitySearchSettings[0].ObjectID = response.objectIdField || this.getObjectId(response.fields);
                        appGlobals.configData.ActivitySearchSettings[0].DateField = this.getDateField(response);
                        topic.publish("hideProgressIndicator");
                    }), function (error) {
                        console.log("Error: ", error.message);
                        topic.publish("hideProgressIndicator");
                    });
                }
            }
            if (appGlobals.configData.ActivitySearchSettings && appGlobals.configData.ActivitySearchSettings[0].PrimaryKeyForActivity && appGlobals.configData.ActivitySearchSettings[0].QueryURL !== "") {
                keyField = this.getKeyValue(appGlobals.configData.ActivitySearchSettings[0].PrimaryKeyForActivity) || "";
                if (keyField !== "") {
                    if (appGlobals.configData.ActivitySearchSettings[0].QueryURL) {
                        activityLayerRequestData = this._queryLayerForLayerInformation(appGlobals.configData.ActivitySearchSettings[0].QueryURL);
                        if (activityLayerRequestData) {
                            // If comment layer request data is found.
                            activityLayerRequestData.then(lang.hitch(this, function (response) {
                                topic.publish("showProgressIndicator");
                                this.primaryFieldType = this.getTypeOfField(response, keyField);
                                topic.publish("hideProgressIndicator");
                            }), function (error) {
                                console.log("Error: ", error.message);
                                topic.publish("hideProgressIndicator");
                            });
                        }
                    }
                }
            }
            // If comment layer request data is found.
            if (commentLayerRequestData) {
                // If comment layer request data is found.
                commentLayerRequestData.then(lang.hitch(this, function (response) {
                    topic.publish("showProgressIndicator");
                    this.commentLayerResponse = response;
                    this.objectIdForCommentLayer = response.objectIdField || this.getObjectId(response.fields);
                    topic.publish("hideProgressIndicator");
                }), function (error) {
                    console.log("Error: ", error.message);
                    topic.publish("hideProgressIndicator");
                });
            }
            // Checking for event settings for getting event data
            if (appGlobals.configData.EventSearchSettings) {
                // Looping for event settings for getting search setting and getting object id
                array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                    if (settings.ObjectID === "" && settings.QueryURL) {
                        eventLayerRequestData = this._queryLayerForLayerInformation(settings.QueryURL);
                        if (eventLayerRequestData) {
                            // If comment layer request data is found.
                            eventLayerRequestData.then(lang.hitch(this, function (response) {
                                topic.publish("showProgressIndicator");
                                appGlobals.configData.EventSearchSettings[eventSettingIndex].ObjectID = response.objectIdField || this.getObjectId(response.fields);
                                appGlobals.configData.EventSearchSettings[eventSettingIndex].DateField = this.getDateField(response);
                                topic.publish("hideProgressIndicator");
                            }), function (error) {
                                console.log("Error: ", error.message);
                                topic.publish("hideProgressIndicator");
                            });
                        }
                    }
                }));
            }
        },

        /**
        * Get the layer information after doing json call
        * @param {data} layer url
        * @return {object} layer request
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _queryLayerForLayerInformation: function (QueryURL) {
            var layersRequest = esriRequest({
                url: QueryURL,
                content: { f: "json" },
                handleAs: "json"
            });
            return layersRequest;
        },

        /**
        * Fire when geolocation widget is clicked, show buffer and route and direction
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _geolocationClicked: function () {
            // If geolocation is click then this function will be called.
            if (dijit.registry.byId("geoLocation")) {
                // On complete of geolocation widget.
                dijit.registry.byId("geoLocation").onGeolocationComplete = lang.hitch(this, function (mapPoint, isPreLoaded) {
                    // If it is coming from geolocation widget then remove graphics and create buffer.
                    if (mapPoint && isPreLoaded) {
                        this.selectedLayerTitle = null;
                        this.removeLocatorPushPin();
                        topic.publish("extentSetValue", true);
                        topic.publish("hideInfoWindow");
                        appGlobals.shareOptions.addressLocation = null;
                        appGlobals.shareOptions.doQuery = "false";
                        appGlobals.shareOptions.infowindowDirection = null;
                        appGlobals.shareOptions.searchFacilityIndex = -1;
                        appGlobals.shareOptions.addressLocationDirectionActivity = null;
                        appGlobals.shareOptions.addressLocation = null;
                        appGlobals.shareOptions.sharedGeolocation = mapPoint;
                        this.removeRouteGraphichOfDirectionWidget();
                        this.removeBuffer();
                        this.createBuffer(mapPoint, "geolocation");
                    }
                });
            }
            // If geolocation widget
            if (dijit.registry.byId("geoLocation")) {
                // If any error is got after clicking on geolocation widget, hide carousel container and remove all graphics.
                dijit.registry.byId("geoLocation").onGeolocationError = lang.hitch(this, function (error, isPreLoaded) {
                    if (!this.widgetName && isPreLoaded) {
                        this.selectedLayerTitle = null;
                        topic.publish("extentSetValue", true);
                        topic.publish("hideInfoWindow");
                        this.removeRouteGraphichOfDirectionWidget();
                        if (this.carouselContainer) {
                            this.carouselContainer.hideCarouselContainer();
                            this.carouselContainer._setLegendPositionDown();
                        }
                    }
                });
            }
        },

        /**
        * Positioning the infoWindow on extent change
        * @param {object} selectedPoint contains feature
        * @param {object} map
        * @param {object} infoWindow
        * @memberOf widgets/commonHelper/infoWindowHelper
        */
        _onSetMapTipPosition: function (selectedPoint, map, infoWindow) {
            // Check if feature is contain
            if (selectedPoint) {
                var screenPoint = map.toScreen(selectedPoint);
                screenPoint.y = map.height - screenPoint.y;
                infoWindow.setLocation(screenPoint);
            }
        },

        /**
        * Set the zoom level an screen point and infowindow
        * @param {object} infoWindowZoomLevelObject contain featurePoint,attribute, layerId, layerTitle,featureSet
        * @memberOf widgets/commonHelper/infoWindowHelper
        */
        _setInfoWindowZoomLevel: function (infoWindowZoomLevelObject) {
            var extentChanged, screenPoint, zoomDeferred;
            appGlobals.shareOptions.selectedMapPoint = infoWindowZoomLevelObject.Mappoint;
            // Check the zoom level of map and set infowindow zoom level according to it
            if (this.map.getLevel() !== appGlobals.configData.ZoomLevel && infoWindowZoomLevelObject.InfoWindowParameter) {
                zoomDeferred = this.map.setLevel(appGlobals.configData.ZoomLevel);
                zoomDeferred.then(lang.hitch(this, function () {
                    // Check if "$extentChanged=" is present in url
                    if (window.location.href.toString().split("$extentChanged=").length > 1) {
                        if (this.isExtentSet) {
                            extentChanged = this.map.setExtent(this.calculateCustomMapExtent(infoWindowZoomLevelObject.mapPoint));
                        } else {
                            topic.publish("hideProgressIndicator");
                            screenPoint = this.map.toScreen(appGlobals.shareOptions.selectedMapPoint);
                            screenPoint.y = this.map.height - screenPoint.y;
                            topic.publish("setInfoWindowOnMap", infoWindowZoomLevelObject.MobileTitle, screenPoint, infoWindowZoomLevelObject.InfoPopupWidth, infoWindowZoomLevelObject.InfoPopupHeight);
                        }
                    } else {
                        extentChanged = this.map.setExtent(this.calculateCustomMapExtent(appGlobals.shareOptions.selectedMapPoint));
                    }
                    if (extentChanged) {
                        extentChanged.then(lang.hitch(this, function () {
                            topic.publish("hideProgressIndicator");
                            screenPoint = this.map.toScreen(appGlobals.shareOptions.selectedMapPoint);
                            screenPoint.y = this.map.height - screenPoint.y;
                            topic.publish("setInfoWindowOnMap", infoWindowZoomLevelObject.MobileTitle, screenPoint, infoWindowZoomLevelObject.InfoPopupWidth, infoWindowZoomLevelObject.InfoPopupHeight);
                        }));
                    }
                }));
            } else {
                // Check if "$extentChanged=" is present in url
                if (window.location.href.toString().split("$extentChanged=").length > 1) {
                    if (this.isExtentSet) {
                        extentChanged = this.map.setExtent(this.calculateCustomMapExtent(appGlobals.shareOptions.selectedMapPoint));
                    } else {
                        topic.publish("hideProgressIndicator");
                        screenPoint = this.map.toScreen(appGlobals.shareOptions.selectedMapPoint);
                        screenPoint.y = this.map.height - screenPoint.y;
                        topic.publish("setInfoWindowOnMap", infoWindowZoomLevelObject.MobileTitle, screenPoint, infoWindowZoomLevelObject.InfoPopupWidth, infoWindowZoomLevelObject.InfoPopupHeight);
                    }
                } else {
                    extentChanged = this.map.setExtent(this.calculateCustomMapExtent(appGlobals.shareOptions.selectedMapPoint));
                }
                if (extentChanged) {
                    extentChanged.then(lang.hitch(this, function () {
                        topic.publish("hideProgressIndicator");
                        screenPoint = this.map.toScreen(appGlobals.shareOptions.selectedMapPoint);
                        screenPoint.y = this.map.height - screenPoint.y;
                        topic.publish("setInfoWindowOnMap", infoWindowZoomLevelObject.MobileTitle, screenPoint, infoWindowZoomLevelObject.InfoPopupWidth, infoWindowZoomLevelObject.InfoPopupHeight);
                    }));
                }
            }
        },

        /**
        * Create info window on map and populate the result
        * @param {object} infoWindowParameter  contains the mapPoint,attribute,layerId, featureArray, featureCount
        * @memberOf widgets/commonHelper/infoWindowHelper
        */
        _createInfoWindowContent: function (infoWindowParameter) {
            var infoPopupHeight, queryLayerId, primaryFieldType, keyField, isAttachmentFound, queryURL, infoWindowZoomLevelObject, galaryObject, featureSet = [], index, infoPopupWidth, infoTitle, widgetName, commentObject, directionObject, i;
            featureSet.push(infoWindowParameter.featureSet);
            if (appGlobals.configData.ActivitySearchSettings && appGlobals.configData.ActivitySearchSettings[0].PrimaryKeyForActivity) {
                keyField = this.getKeyValue(appGlobals.configData.ActivitySearchSettings[0].PrimaryKeyForActivity) || "";
                if (keyField !== "" && infoWindowParameter.widgetName !== "listclick") {
                    primaryFieldType = this.getTypeOfField(infoWindowParameter.featureArray[0], keyField);
                }
            }
            if (win.getBox().w <= 766) {
                topic.publish("collapseCarousel");
                topic.publish("toggleWidget");
            }
            queryLayerId = Number(infoWindowParameter.layerId);
            this.infoWindowFeatureData = infoWindowParameter.featureArray;
            index = this.getInfowWindowIndex(infoWindowParameter.layerTitle, queryLayerId);
            queryURL = this.getQueryUrl(queryLayerId, infoWindowParameter.layerTitle);
            topic.publish("returnQueryURL", queryURL);
            widgetName = this.getInfowWindowWidgetName(infoWindowParameter.layerTitle, queryLayerId);
            isAttachmentFound = this._getAttachmentInLayer(infoWindowParameter.layerTitle, queryLayerId);
            topic.publish("isattachmentFound", isAttachmentFound);
            topic.publish("getInfoWidgetName", widgetName);
            // To know the click of feature in Event planner.
            this.getMapPoint(infoWindowParameter.mapPoint);
            this.highlightFeature(infoWindowParameter.featureSet.geometry);
            infoPopupHeight = appGlobals.configData.InfoPopupHeight;
            infoPopupWidth = appGlobals.configData.InfoPopupWidth;
            this._infoWindowInformationTab(infoWindowParameter.attribute, index, widgetName, featureSet, Number(infoWindowParameter.IndexNumber), infoWindowParameter.featureArray.length, infoWindowParameter.widgetName, infoWindowParameter.layerId, infoWindowParameter.layerTitle);
            if (isAttachmentFound) {
                galaryObject = { "attribute": infoWindowParameter.attribute, "widgetName": widgetName, "LayerID": this.layerIDForGallery };
                topic.publish("galaryObject", galaryObject);
                this._infoWindowGalleryTab(galaryObject);
            }
            if (queryURL !== "otherURL") {
                commentObject = { "attribute": infoWindowParameter.attribute, "widgetName": widgetName, "index": index, "primaryFieldType": primaryFieldType };
                topic.publish("commentObject", commentObject);
                this._infoWindowCommentTab(commentObject);
                /*Direction tab*/
                directionObject = { "attribute": infoWindowParameter.attribute, "widgetName": widgetName, "featureSet": infoWindowParameter.featureSet, "QueryURL": queryURL };
                topic.publish("showDirection", directionObject);
            }
            // Check if InfowindowContent available if not then showing info title as mobile title.
            // In WebMap case infowindow contents will be not available
            for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                if (appGlobals.operationLayerSettings[i].layerID === parseInt(infoWindowParameter.layerId, 10) && appGlobals.operationLayerSettings[i].layerTitle === infoWindowParameter.layerTitle) {
                    if (appGlobals.operationLayerSettings[i].infoWindowData && appGlobals.operationLayerSettings[i].infoWindowData.infoWindowHeader) {
                        try {
                            infoTitle = this.popUpTitleDetails(infoWindowParameter.attribute, appGlobals.operationLayerSettings[i].layerDetails);
                        } catch (e) {
                            infoTitle = appGlobals.configData.ShowNullValueAs;
                        }
                    } else {
                        infoTitle = appGlobals.configData.ShowNullValueAs;
                    }
                }
            }
            infoWindowZoomLevelObject = { "Mappoint": infoWindowParameter.mapPoint, "MobileTitle": infoTitle, "InfoPopupWidth": infoPopupWidth, "InfoPopupHeight": infoPopupHeight, "InfoWindowParameter": infoWindowParameter.featureCount };
            appGlobals.shareOptions.selectedMapPoint = infoWindowParameter.mapPoint;
            this._setInfoWindowZoomLevel(infoWindowZoomLevelObject);
            topic.publish("hideProgressIndicator");
        },

        /**
        * Function for getting attachment in layer
        * @param {string} layerTitle contains layerTitle
        * @param {string} queryLayerId contains queryLayerId
        * @return{boolean} isAttachmentFound contains true or false value
        * @memberOf widgets/commonHelper/infoWindowHelper
        */
        _getAttachmentInLayer: function (layerTitle, queryLayerId) {
            var isAttachmentFound = false, layerIndex, layerIDForGallery;
            this.layerIDForGallery = "";
            if (this.map._layers) {
                for (layerIndex = 0; layerIndex < appGlobals.operationLayerSettings.length; layerIndex++) {
                    if (appGlobals.operationLayerSettings[layerIndex].layerTitle === layerTitle && appGlobals.operationLayerSettings[layerIndex].layerID === queryLayerId) {
                        if (appGlobals.operationLayerSettings[layerIndex].layerDetails.popupInfo && appGlobals.operationLayerSettings[layerIndex].layerDetails.popupInfo.showAttachments) {
                            layerIDForGallery = appGlobals.operationLayerSettings[layerIndex].layerDetails.id;
                            this.layerIDForGallery = layerIDForGallery;
                            break;
                        }
                    }
                }
                if (layerIDForGallery && this.map._layers && this.map._layers[layerIDForGallery].hasAttachments) {
                    isAttachmentFound = true;
                }
            }
            return isAttachmentFound;
        },

        /**
        * infoWindow Direction tab
        * @param {object} directionObject contains attributes, featureSet, QueryURL and widgetName
        * @memberOf widgets/commonHelper/infoWindowHelper
        */
        _infoWindowDirectionTab: function (directionObject) {
            var directionMainContainer, infoWindowPoint, point, serchSetting, locatorInfoWindowObject, locatorInfoWindowParams, searchContentData, divHeader, infoWindowMapPoint, routeObject, mapLogoPostionDown, imgCustomLogo, getDirContainer;
            getDirContainer = dom.byId("getDirContainer");
            // Check direction container is present
            if (getDirContainer) {
                domConstruct.empty(getDirContainer);
            }
            directionMainContainer = domConstruct.create("div", { "class": "esriCTDirectionMainContainer" }, getDirContainer);
            serchSetting = this.getSearchSetting(directionObject.QueryURL);
            searchContentData = string.substitute(serchSetting.SearchDisplayFields, directionObject.attribute);
            divHeader = domConstruct.create("div", {}, directionMainContainer);
            domConstruct.create("div", { "class": "esriCTSpanHeaderInfoWindow", "innerHTML": sharedNls.titles.directionText + " " + searchContentData }, divHeader);
            // Check if it is geolocation graphic or address search graphic
            locatorInfoWindowParams = {
                defaultAddress: appGlobals.configData.LocatorSettings.LocatorDefaultAddress,
                preLoaded: false,
                parentDomNode: directionMainContainer,
                map: this.map,
                graphicsLayerId: this.locatorGraphicsLayerID,
                locatorSettings: appGlobals.configData.LocatorSettings,
                configSearchSettings: appGlobals.configData.SearchSettings
            };
            infoWindowMapPoint = this.map.getLayer(locatorInfoWindowParams.graphicsLayerId);
            locatorInfoWindowObject = new LocatorTool(locatorInfoWindowParams);
            locatorInfoWindowObject.candidateClicked = lang.hitch(this, function (graphic) {
                this.removeBuffer();
                // Set variable for infowindow direction in case of share
                appGlobals.shareOptions.infowindowDirection = directionObject.featureSet.geometry.x.toString() + "," + directionObject.featureSet.geometry.y.toString();
                if (locatorInfoWindowObject && locatorInfoWindowObject.selectedGraphic && !graphic.layer) {
                    appGlobals.shareOptions.infowindowDirection = appGlobals.shareOptions.infowindowDirection + "," + locatorInfoWindowObject.selectedGraphic.geometry.x.toString() + "," + locatorInfoWindowObject.selectedGraphic.geometry.y.toString();
                }
                appGlobals.shareOptions.directionScreenPoint = appGlobals.shareOptions.screenPoint;
                if (graphic && graphic.attributes && graphic.attributes.address) {
                    this.locatorAddress = graphic.attributes.address;
                }
                // Check if the layer information is in the graphic
                if (graphic && graphic.layer) {
                    this.selectedLayerTitle = graphic.layer.SearchDisplayTitle;
                }
                appGlobals.shareOptions.eventInfoWindowData = null;
                appGlobals.shareOptions.sharedGeolocation = null;
                appGlobals.shareOptions.infoRoutePoint = null;
                topic.publish("showProgressIndicator");
                this.removeGeolocationPushPin();
                if (this.carouselContainer) {
                    this.carouselContainer.hideCarouselContainer();
                }
                if (appGlobals.configData.CustomLogoUrl) {
                    // If ShowLegend is 'True' then set legend widget position down and place the customLogo image at the bottom of screen
                    if (appGlobals.configData.ShowLegend) {
                        imgCustomLogo = query('.esriCTCustomMapLogo')[0];
                        if (this.carouselContainer) {
                            this.carouselContainer._setLegendPositionDown();
                        }
                        domClass.replace(imgCustomLogo, "esriCTCustomMapLogoBottom", "esriCTCustomMapLogoPostionChange");
                    } else {
                        mapLogoPostionDown = query('.esriControlsBR')[0];
                        imgCustomLogo = query('.esriCTCustomMapLogo')[0];
                        // If ShowLegend is 'False' then remove all classes which holds esriLogo and customLogo position above the bottom of screen
                        if (query('.esriCTDivMapPositionTop')[0]) {
                            domClass.remove(mapLogoPostionDown, "esriCTDivMapPositionTop");
                        }
                        if (query('.esriCTDivMapPositionUp')[0]) {
                            domClass.remove(mapLogoPostionDown, "esriCTDivMapPositionUp");
                        }
                        if (query('.esriCTCustomMapLogoPostion')[0]) {
                            domClass.remove(imgCustomLogo, "esriCTCustomMapLogoPostion");
                        }
                    }
                }
                // If layer information contains in graphic
                if (graphic && graphic.layer) {
                    if (this.carouselContainer) {
                        this.carouselContainer.hideCarouselContainer();
                    }
                    routeObject = { "StartPoint": graphic, "EndPoint": [directionObject.featureSet], "Index": 0, "WidgetName": directionObject.widgetName, "QueryURL": directionObject.QueryURL };
                    this.showRoute(routeObject);
                    this.selectedGraphicInfowindow = graphic;
                    topic.publish("extentSetValue", false);
                    appGlobals.shareOptions.selectedMapPoint = directionObject.featureSet.geometry;
                    appGlobals.shareOptions.infowindowDirection = appGlobals.shareOptions.infowindowDirection + "," + graphic.geometry.x.toString() + "," + graphic.geometry.y.toString();
                    // If address is locate then pass startPoint and endPoint to showroute function
                } else {
                    if (this.carouselContainer) {
                        this.carouselContainer.hideCarouselContainer();
                    }
                    routeObject = { "StartPoint": infoWindowMapPoint.graphics[0], "EndPoint": [directionObject.featureSet], "Index": 0, "WidgetName": directionObject.widgetName, "QueryURL": directionObject.QueryURL };
                    this.showRoute(routeObject);
                }
            });
            // Function for share in the case of dirction from info window
            setTimeout(lang.hitch(this, function () {
                if (window.location.href.toString().split("$infowindowDirection=").length > 1 && !this.isDirectionCalculated) {
                    this.isDirectionCalculated = true;
                    var mapPoint = new Point(window.location.href.toString().split("$infowindowDirection=")[1].split("$")[0].split(",")[6], window.location.href.toString().split("$infowindowDirection=")[1].split("$")[0].split(",")[7], this.map.spatialReference); /* Changes for GitHub issue #157 */
                    appGlobals.shareOptions.infowindowDirection = window.location.href.toString().split("$infowindowDirection=")[1].split("$")[0]; //mapPoint;
                    locatorInfoWindowObject._locateAddressOnMap(mapPoint, true);
                    routeObject = { "StartPoint": infoWindowMapPoint.graphics[0], "EndPoint": [directionObject.featureSet], "Index": 0, "WidgetName": directionObject.widgetName, "QueryURL": directionObject.QueryURL };
                    this.showRoute(routeObject);
                    if (window.location.href.toString().split("$mapClickPoint=").length > 1) {
                        infoWindowPoint = window.location.href.toString().split("$mapClickPoint=")[1].split("$")[0].split(",");
                        point = new Point(parseFloat(infoWindowPoint[0]), parseFloat(infoWindowPoint[1]), this.map.spatialReference);
                        topic.publish("showInfoWindowOnMap", point, "mapclickpoint");
                    } else {
                        topic.publish("hideInfoWindow");
                        this.removeHighlightedCircleGraphics();
                    }
                }
            }), 1000);
        },

        /**
        * Information Tab for InfoWindow
        * @param {field} attributes is the field of feature
        * @param {number} InfoIndex is the feature layer id
        * @param {string} widgetName is name of widget
        * @param {object} featureSet Contain set of features
        * @param {number} index
        * @param {number} featureCount Contain no.of feature
        * @param {string} featureClickName Contain name of feature
        * @param {number} layerId Contain layer id
        * @param {string} layerTitle Contain layer Title
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _infoWindowInformationTab: function (attributes, infoIndex, widgetName, featureSet, index, featureCount, featureClickName, layerId, layerTitle) {
            var divInfoRow, divInformationContent, queryLayerId, divHeader, contentDiv, divFacilityContent, divPaginationPrevNext, facilityDiv, addToListObject, infoTitle,
                divInfoWindowTtile, descriptionValue, divAccessfee, SearchSettingsLayers, activityImageDiv, i, j, k, p, fieldInfo, fieldName, fieldValue, domainValue, informationTabContainer;
            // If information tap container is present
            informationTabContainer = dom.byId("informationTabContainer");
            if (informationTabContainer) {
                domConstruct.empty(informationTabContainer);
            }
            if (featureClickName === "listclick") {
                featureCount = 1;
            }
            divInfoRow = domConstruct.create("div", { "class": "esriCTInfoWindoContainerOuter" }, informationTabContainer);
            divInformationContent = domConstruct.create("div", { "class": "esriCTInfoWindoContainer" }, divInfoRow);
            divHeader = domConstruct.create("div", { "class": "esriCTDivHeadercontainerInfo" }, divInformationContent);
            divPaginationPrevNext = domConstruct.create("div", { "class": "esriCTTPaginationPrevNext" }, divHeader);
            this.nextButton = domConstruct.create("div", { "class": "esriCTPaginationNext", "title": sharedNls.tooltips.nextFeatureTooltip }, divPaginationPrevNext);
            this.divPaginationCount = domConstruct.create("div", { "class": "esriCTTPaginationList", "innerHTML": index + "/" + featureCount }, divPaginationPrevNext);
            this.previousButton = domConstruct.create("div", { "class": "esriCTTPaginationPrev", "title": sharedNls.tooltips.previousFeatureTooltip }, divPaginationPrevNext);
            contentDiv = domConstruct.create("div", { "class": "esriCTDivClear" }, divInformationContent);
            divFacilityContent = domConstruct.create("div", { "class": "esriCTUtilityImgContainer" }, divInformationContent);
            divInfoWindowTtile = domConstruct.create("div", { "class": "esriCTCustomPopupTitle" }, divHeader);
            if (featureCount === 1) {
                domClass.replace(this.divPaginationCount, "esriCTPaginationDisContainer", "esriCTTPaginationList");
                domClass.replace(this.previousButton, "esriCTTPaginationDisPrev", "esriCTTPaginationPrev");
                domClass.replace(this.nextButton, "esriCTTPaginationDisNext", "esriCTPaginationNext");
                domAttr.set(this.previousButton, "title", null);
                domAttr.set(this.nextButton, "title", null);
            } else if (index === 1) {
                domClass.replace(this.previousButton, "esriCTTPaginationDisPrev", "esriCTTPaginationPrev");
                domAttr.set(this.previousButton, "title", null);
            } else if (featureCount === index) {
                domClass.replace(this.nextButton, "esriCTTPaginationDisNext", "esriCTPaginationNext");
                domAttr.set(this.nextButton, "title", null);
            }
            this.own(on(this.previousButton, a11yclick, lang.hitch(this, this.previousButtonClick, featureCount)));
            this.own(on(this.nextButton, a11yclick, lang.hitch(this, this.nextButtonClick, featureCount)));
            queryLayerId = Number(layerId);
            // Looping through the feature attributes and setting text configured in NLS file (showNullValue) for attributes with null value
            for (i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    if ((!attributes[i]) && (attributes[i] !== 0 || lang.trim(String(attributes[i])) === "")) {
                        attributes[i] = appGlobals.configData.ShowNullValueAs;
                    }
                }
            }
            // Looping for operation layer settings for getting data for information tab

            for (k = 0; k < appGlobals.operationLayerSettings.length; k++) {
                if (appGlobals.operationLayerSettings[k].infoWindowData && appGlobals.operationLayerSettings[k].layerTitle === layerTitle && appGlobals.operationLayerSettings[k].layerID === queryLayerId) {
                    if (appGlobals.operationLayerSettings[k].layerDetails.popupInfo && appGlobals.operationLayerSettings[k].layerDetails.popupInfo.description) {
                        descriptionValue = this._getDescription(attributes, appGlobals.operationLayerSettings[k].layerDetails, featureClickName);
                        // Create a div with popup info description and add it to details div
                        domConstruct.create("div", {
                            "innerHTML": descriptionValue,
                            "class": "esriCTCustomPopupDiv"
                        }, contentDiv);
                    } else {
                        for (p = 0; p < appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields.length; p++) {
                            divAccessfee = domConstruct.create("div", { "class": "esriCTInfofacility" }, contentDiv);
                            domConstruct.create("div", { "class": "esriCTFirstChild", "innerHTML": appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].DisplayText }, divAccessfee);
                            facilityDiv = domConstruct.create("div", { "class": "esriCTSecondChild" }, divAccessfee);
                            if (string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes).match("http:") || string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes).match("https:")) {
                                domConstruct.create("a", { "class": "esriCTinfoWindowHyperlink", "href": string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes), "title": string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes), "innerHTML": sharedNls.titles.infoWindowTextURL, "target": "_blank" }, facilityDiv);
                                domClass.add(facilityDiv, "esriCTWordBreak");
                            } else if (string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes).substring(0, 3) === "www") {
                                domConstruct.create("a", { "class": "esriCTinfoWindowHyperlink", "href": "http://" + string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes), "title": "http://" + string.substitute(appGlobals.configData.InfoWindowSettings[infoIndex].InfoWindowData[k].FieldName, attributes), "innerHTML": sharedNls.titles.infoWindowTextURL, "target": "_blank" }, facilityDiv);
                            } else {
                                try {
                                    // Get field value from feature attributes
                                    fieldValue = string.substitute(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName, attributes);
                                } catch (ex) {
                                    fieldValue = appGlobals.configData.ShowNullValueAs;
                                }
                                fieldName = appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].FieldName.split("${")[1].split("}")[0];
                                fieldInfo = this.isDateField(fieldName, appGlobals.operationLayerSettings[k].layerDetails.layerObject);
                                if (fieldInfo) {
                                    if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                        if (featureClickName !== "listclick") {
                                            fieldValue = this.setDateFormat(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p], fieldValue);
                                        }
                                    }
                                } else {
                                    // Check if field has coded values
                                    fieldInfo = this.hasDomainCodedValue(fieldName, attributes, appGlobals.operationLayerSettings[k].layerDetails.layerObject);
                                    if (fieldInfo) {
                                        if (fieldInfo.isTypeIdField) {
                                            fieldValue = fieldInfo.name;
                                        } else {
                                            domainValue = this.domainCodedValues(fieldInfo, fieldValue);
                                            fieldValue = domainValue.domainCodedValue;
                                        }
                                    }
                                    if (appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p].format) {
                                        if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                            fieldValue = this.numberFormatCorverter(appGlobals.operationLayerSettings[k].infoWindowData.infoWindowfields[p], fieldValue);

                                        }
                                    }
                                }
                                facilityDiv.innerHTML = fieldValue;
                            }
                        }
                    }
                    // If widgetName is "infoactivity" and ActivitySearchSettings is enable from config
                    if (widgetName.toLowerCase() === "infoactivity" && appGlobals.configData.ActivitySearchSettings[0].Enable) {
                        for (j = 0; j < appGlobals.configData.ActivitySearchSettings.length; j++) {
                            SearchSettingsLayers = appGlobals.configData.ActivitySearchSettings[j];
                            // Looping for ActivityList to show the activity images in infowindow
                            for (i = 0; i < SearchSettingsLayers.ActivityList.length; i++) {
                                if (string.substitute(SearchSettingsLayers.ActivityList[i].FieldName, attributes)) {
                                    if (attributes[string.substitute(SearchSettingsLayers.ActivityList[i].FieldName, attributes)] === SearchSettingsLayers.QualifyingActivityValue) {
                                        activityImageDiv = domConstruct.create("div", { "class": "esriCTActivityImage" }, divFacilityContent);
                                        domConstruct.create("img", { "src": SearchSettingsLayers.ActivityList[i].Image, "title": SearchSettingsLayers.ActivityList[i].Alias }, activityImageDiv);
                                    }
                                }
                            }
                        }
                    }
                    try {
                        infoTitle = this.popUpTitleDetails(attributes, appGlobals.operationLayerSettings[k].layerDetails);
                    } catch (e) {
                        infoTitle = appGlobals.configData.ShowNullValueAs;
                    }
                }
            }
            divInfoWindowTtile.innerHTML = infoTitle;
            addToListObject = { "featureSet": featureSet[0], "widgetName": widgetName, "layerId": Number(layerId), "layerTitle": layerTitle };
            topic.publish("addToListObject", addToListObject);
        },

        /**
        * Function for getting description
        * @param {object} featureSet is the object of feature set
        * @param {number} operationalLayerDetails
        * @return {object} descriptionValue is returning object for description value
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _getDescription: function (featureSet, operationalLayerDetails, featureClickName) {
            var descriptionValue, i, field, splittedArrayForClosingBraces, fieldInfo, popupInfoValue, domainValue, fieldValue;
            // Assuming Fields will be configure within the curly braces'{}'
            // Check if Custom Configuration has any fields Configured in it.
            if (operationalLayerDetails.popupInfo.description.split("{").length > 0) {
                // Add the data before 1st instance on curly '{' braces
                descriptionValue = operationalLayerDetails.popupInfo.description.split("{")[0];
                // Loop through the possible number of configured fields
                for (i = 1; i < operationalLayerDetails.popupInfo.description.split("{").length; i++) {
                    // Check if string is having closing curly braces '}'. i.e. it has some field
                    if (operationalLayerDetails.popupInfo.description.split("{")[i].indexOf("}") !== -1) {
                        splittedArrayForClosingBraces = operationalLayerDetails.popupInfo.description.split("{")[i].split("}");
                        field = string.substitute(splittedArrayForClosingBraces[0]);
                        fieldInfo = this.isDateField(field, operationalLayerDetails.layerObject);
                        popupInfoValue = this.getPopupInfo(field, operationalLayerDetails.popupInfo);
                        if (fieldInfo && featureSet[lang.trim(field)] !== null && lang.trim(String(featureSet[lang.trim(field)])) !== "") {
                            // Set date format
                            fieldValue = featureSet[lang.trim(field)];
                            if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                fieldValue = this.setDateFormat(popupInfoValue, featureSet[lang.trim(field)]);
                                if (popupInfoValue.format) {
                                    // Check whether format for digit separator is available
                                    fieldValue = this.numberFormatCorverter(popupInfoValue, fieldValue, featureClickName);
                                }
                            }
                            descriptionValue += fieldValue;
                        } else {
                            fieldInfo = this.hasDomainCodedValue(field, featureSet, operationalLayerDetails.layerObject);
                            if (fieldInfo) {
                                if (fieldInfo.isTypeIdField) {
                                    descriptionValue += fieldInfo.name;
                                } else {
                                    domainValue = this.domainCodedValues(fieldInfo, featureSet[lang.trim(field)], featureClickName);
                                    descriptionValue += domainValue.domainCodedValue;
                                }
                            } else if (featureSet[field] || featureSet[field] === 0) {
                                // Check if the field is valid field or not, if it is valid then substitute its value.
                                fieldValue = featureSet[field];
                                if (popupInfoValue.format) {
                                    // Check whether format for digit separator is available
                                    fieldValue = this.numberFormatCorverter(popupInfoValue, fieldValue, featureClickName);
                                }
                                descriptionValue += fieldValue;
                            } else if (field === "") {
                                // If field is empty means only curly braces are configured in pop-up
                                descriptionValue += "{}";
                            }
                        }
                        splittedArrayForClosingBraces.shift();
                        // If splittedArrayForClosingBraces length is more than 1, then there are more closing braces in the string, so join the array with }
                        if (splittedArrayForClosingBraces.length > 1) {
                            descriptionValue += splittedArrayForClosingBraces.join("}");
                        } else {
                            descriptionValue += splittedArrayForClosingBraces.join("");
                        }
                    } else {
                        // If there is no closing bracket then add the rest of the string prefixed with '{' as we have split it with '{'
                        descriptionValue += "{" + operationalLayerDetails.popupInfo.description.split("{")[i];
                    }
                }
            } else {
                // No '{' braces means no field has been configured only Custom description is present in pop-up
                descriptionValue = operationalLayerDetails.popupInfo.description;
            }
            return descriptionValue;
        },

        /**
        * Fetch field from popup info
        * @param{string} fieldName - current field
        * @param{object} popupInfo - operational layer popupInfo object
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        getPopupInfo: function (fieldName, popupInfo) {
            var i, fieldInfo;
            for (i = 0; i < popupInfo.fieldInfos.length; i++) {
                if (popupInfo.fieldInfos[i].fieldName === fieldName) {
                    fieldInfo = popupInfo.fieldInfos[i];
                    break;
                }
            }
            return fieldInfo;
        },

        /**
        * Check if field type is date
        * @param{object} layerObj - layer data
        * @param{string} fieldName - current field
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        isDateField: function (fieldName, layerObj) {
            var i, isDateField = null;
            for (i = 0; i < layerObj.fields.length; i++) {
                if (layerObj.fields[i].name === fieldName && layerObj.fields[i].type === "esriFieldTypeDate") {
                    isDateField = layerObj.fields[i];
                    break;
                }
            }
            return isDateField;
        },

        /**
        * Format date value based on the format received from info popup
        * @param{object} dateFieldInfo
        * @param{string} dataFieldValue
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        setDateFormat: function (dateFieldInfo, dateFieldValue) {
            var isFormatedDate = false, formatedDate, dateObj, popupDateFormat;
            formatedDate = Number(dateFieldValue);
            if (formatedDate) {
                dateFieldValue = Number(dateFieldValue);
            } else {
                isFormatedDate = true;
            }
            dateObj = new Date(dateFieldValue);
            if (dateFieldInfo.format && dateFieldInfo.format.dateFormat) {
                if (!isFormatedDate) {
                    popupDateFormat = this._getDateFormat(dateFieldInfo.format.dateFormat);
                    dateFieldValue = locale.format(dateObj, {
                        datePattern: popupDateFormat,
                        selector: "date"
                    });
                }
            } else {
                if (!isFormatedDate) {
                    dateFieldValue = dateObj.toLocaleDateString();
                }
            }
            return dateFieldValue;
        },

        /**
        * This function is used to convert ArcGIS date format constants to readable date formats
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _getDateFormat: function (type) {
            var dateFormat;
            switch (type) {
            case "shortDate":
                dateFormat = "MM/dd/yyyy";
                break;
            case "shortDateLE":
                dateFormat = "dd/MM/yyyy";
                break;
            case "longMonthDayYear":
                dateFormat = "MMMM dd, yyyy";
                break;
            case "dayShortMonthYear":
                dateFormat = "dd MMM yyyy";
                break;
            case "longDate":
                dateFormat = "EEEE, MMMM dd, yyyy";
                break;
            case "shortDateLongTime":
                dateFormat = "MM/dd/yyyy hh:mm:ss a";
                break;
            case "shortDateLELongTime":
                dateFormat = "dd/MM/yyyy hh:mm:ss a";
                break;
            case "shortDateLELongTime24":
                dateFormat = "dd/MM/yyyy hh:mm:ss";
                break;
            case "shortDateShortTime":
                dateFormat = "MM/dd/yyyy hh:mm a";
                break;
            case "shortDateLEShortTime":
                dateFormat = "dd/MM/yyyy hh:mm a";
                break;
            case "shortDateShortTime24":
                dateFormat = "MM/dd/yyyy HH:mm";
                break;
            case "shortDateLongTime24":
                dateFormat = "MM/dd/yyyy hh:mm:ss";
                break;
            case "shortDateLEShortTime24":
                dateFormat = "dd/MM/yyyy HH:mm";
                break;
            case "longMonthYear":
                dateFormat = "MMMM yyyy";
                break;
            case "shortMonthYear":
                dateFormat = "MMM yyyy";
                break;
            case "year":
                dateFormat = "yyyy";
                break;
            default:
                dateFormat = "MMMM dd, yyyy";
            }
            return dateFormat;
        },


        /**
        * Check if field has domain coded values
        * @param{string} fieldName
        * @param{object} feature
        * @param{object} layerObject
        */
        hasDomainCodedValue: function (fieldName, feature, layerObject) {
            var i, j, fieldInfo;
            for (i = 0; i < layerObject.fields.length; i++) {
                if (layerObject.fields[i].name === fieldName) {
                    if (layerObject.fields[i].domain && layerObject.fields[i].domain.codedValues) {
                        fieldInfo = layerObject.fields[i];
                    } else if (layerObject.typeIdField) {
                        // Get types from layer object, if typeIdField is available
                        for (j = 0; j < layerObject.types.length; j++) {
                            if (String(layerObject.types[j].id) === String(feature[layerObject.typeIdField])) {
                                fieldInfo = layerObject.types[j];
                                break;
                            }
                        }
                        // If types info is found for current value of typeIdField then break the outer loop
                        if (fieldInfo) {
                            break;
                        }
                    }
                }
            }
            // Get domain values from layer types object according to the value of typeIdfield
            if (fieldInfo && fieldInfo.domains) {
                if (layerObject.typeIdField && layerObject.typeIdField !== fieldName) {
                    fieldInfo.isTypeIdField = false;
                    if (fieldInfo.domains.hasOwnProperty(fieldName)) {
                        fieldInfo.domain = {};
                        fieldInfo.domain = fieldInfo.domains[fieldName];
                    } else {
                        fieldInfo = null;
                    }
                } else {
                    // Set isTypeIdField to true if current field is typeIdField
                    fieldInfo.isTypeIdField = true;
                }
            }
            return fieldInfo;
        },

        /**
        * Fetch domain coded value
        * @param{object} operationalLayerDetails
        * @param{string} fieldValue
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        domainCodedValues: function (operationalLayerDetails, fieldValue, widgetName) {
            var k, codedValues, domainValueObj;
            domainValueObj = { domainCodedValue: appGlobals.configData.ShowNullValueAs };
            if (widgetName !== "listclick") {
                codedValues = operationalLayerDetails.domain.codedValues;
                if (codedValues) {
                    // Loop for codedValue
                    for (k = 0; k < codedValues.length; k++) {
                        // Check if the value is string or number
                        if (codedValues[k].code === fieldValue) {
                            fieldValue = codedValues[k].name;
                        } else if (codedValues[k].code === parseInt(fieldValue, 10)) {
                            fieldValue = codedValues[k].name;
                        }
                    }
                }
            }
            domainValueObj.domainCodedValue = fieldValue;
            return domainValueObj;
        },


        /**
        * Format number value based on the format received from info popup
        * @param{object} popupInfoValue
        * @param{string} fieldValue
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        numberFormatCorverter: function (popupInfoValue, fieldValue, widgetName) {
            if (popupInfoValue.format && popupInfoValue.format.places !== null && popupInfoValue.format.places !== "" && !isNaN(parseFloat(fieldValue))) {
                // Check if digit separator is available
                if (popupInfoValue.format.digitSeparator) {
                    if (widgetName !== "listclick") {
                        fieldValue = Number(fieldValue);
                        fieldValue = parseFloat(fieldValue).toFixed(popupInfoValue.format.places);
                        fieldValue = this.convertNumberToThousandSeperator(fieldValue);
                    }
                } else {
                    if (widgetName !== "listclick") {
                        fieldValue = parseFloat(fieldValue).toFixed(popupInfoValue.format.places);
                    }
                }
            }
            return fieldValue;
        },

        /**
        * Sets the info popup header
        * @param{array} featureSet
        * @param{object} operationalLayer - operational layer data
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        popUpTitleDetails: function (featureSet, operationalLayer) {
            var i, j, titleField, fieldValue, domainValue, popupTitle, titleArray, headerValue, headerFieldArray, fieldInfo, popupInfoValue;
            headerValue = null;
            // Split info popup header fields
            popupTitle = operationalLayer.popupInfo.title.split("{");
            headerFieldArray = [];
            // If header contains more than 1 fields
            if (popupTitle.length > 1) {
                // Get strings from header
                titleField = popupTitle[0];
                for (i = 0; i < popupTitle.length; i++) {
                    // Insert remaining fields in an array
                    titleArray = popupTitle[i].split("}");
                    if (i === 0) {
                        if (featureSet.hasOwnProperty(titleArray[0])) {
                            fieldValue = featureSet[titleArray[0]];
                            // Concatenate string and first field from the header and insert in an array
                            headerFieldArray.push(fieldValue);
                        } else {
                            headerFieldArray.push(titleField);
                        }
                    } else {
                        for (j = 0; j < titleArray.length; j++) {
                            if (j === 0) {
                                if (featureSet.hasOwnProperty(titleArray[j])) {
                                    popupInfoValue = this.getPopupInfo(titleArray[j], operationalLayer.popupInfo);
                                    fieldValue = featureSet[lang.trim(titleArray[j])];
                                    if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                        fieldInfo = this.isDateField(titleArray[j], operationalLayer.layerObject);
                                        if (fieldInfo) {
                                            // Set date format
                                            fieldValue = this.setDateFormat(popupInfoValue, fieldValue);
                                        } else {
                                            fieldInfo = this.hasDomainCodedValue(titleArray[j], featureSet, operationalLayer.layerObject);
                                            if (fieldInfo) {
                                                if (fieldInfo.isTypeIdField) {
                                                    fieldValue = fieldInfo.name;
                                                } else {
                                                    domainValue = this.domainCodedValues(fieldInfo, fieldValue);
                                                    fieldValue = domainValue.domainCodedValue;
                                                }
                                            }
                                        }
                                        if (popupInfoValue.format) {
                                            // Check whether format for digit separator is available
                                            fieldValue = this.numberFormatCorverter(popupInfoValue, fieldValue);
                                        }
                                    }
                                    headerFieldArray.push(fieldValue);
                                }
                            } else {
                                headerFieldArray.push(titleArray[j]);
                            }
                        }
                    }
                }

                // Form a string from the headerFieldArray array, to display in header
                for (j = 0; j < headerFieldArray.length; j++) {
                    if (headerValue) {
                        headerValue = headerValue + headerFieldArray[j];
                    } else {
                        headerValue = headerFieldArray[j];
                    }
                }
            } else {
                // If popup title is not empty, display popup field headerValue else display a configurable text
                if (lang.trim(operationalLayer.popupInfo.title) !== "") {
                    headerValue = operationalLayer.popupInfo.title;
                }
            }
            if (headerValue === null) {
                headerValue = appGlobals.configData.ShowNullValueAs;
            }
            return headerValue;
        },

        /**
        * Click to add a facility into my list
        * @param {object} featureSet contains featureSet, widgetName, LayerId and LayerTitle
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        clickOnAddToList: function (addToListObject) {
            var isAlreadyAdded, objectIDField, queryLayerId, listData, formatedDataField;
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings) {
                queryLayerId = Number(settings.QueryLayerId);
                // Check the layerId and layerTitle object
                if (queryLayerId === addToListObject.layerId && settings.Title === addToListObject.layerTitle) {
                    objectIDField = settings.ObjectID;
                }
            }));
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings) {
                queryLayerId = Number(settings.QueryLayerId);
                if (queryLayerId === addToListObject.layerId && settings.Title === addToListObject.layerTitle) {
                    objectIDField = settings.ObjectID;
                }
            }));
            isAlreadyAdded = false;
            // Check length of myListStore array
            if (this.myListStore.length > 0) {
                for (listData = 0; listData < this.myListStore.length; listData++) {
                    if (this.myListStore[listData].value[this.myListStore[listData].key] === addToListObject.featureSet.attributes[objectIDField]) {
                        alert(sharedNls.errorMessages.activityAlreadyAdded);
                        isAlreadyAdded = true;
                        break;
                    }
                }
            }
            // Check if feature is not added in myList
            if (!isAlreadyAdded) {
                if (query(".esriCTEventsImg")[0]) {
                    topic.publish("toggleWidget", "myList");
                    topic.publish("showActivityPlannerContainer");
                }
                if (addToListObject.widgetName === "InfoEvent") {
                    addToListObject.featureSet = this.setDateWithUTC(addToListObject.featureSet);
                }
                formatedDataField = this._formatedData(addToListObject.featureSet);
                topic.publish("addToMyList", formatedDataField, addToListObject.widgetName, addToListObject.layerId, addToListObject.layerTitle);
            }
        },

        /**
        * Change the date format with configured date format
        * @param {object} object of featureSet
        * @return {object} object of event Search Settings
        * @memberOf widgets/searchSetting/searchSetting
        */
        _formatedData: function (featureSet) {
            var i, l, j, layerDetails, fieldValue, fieldName, fieldInfo, domainValue;
            for (l in featureSet.attributes) {
                if (featureSet.attributes.hasOwnProperty(l)) {
                    if ((!featureSet.attributes[l]) && (featureSet.attributes[l] !== 0 || lang.trim(String(featureSet.attributes[l])) === "")) {
                        featureSet.attributes[l] = appGlobals.configData.ShowNullValueAs;
                    }
                }
            }
            for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                if (appGlobals.operationLayerSettings[i].layerDetails && appGlobals.operationLayerSettings[i].layerDetails.popupInfo) {
                    layerDetails = appGlobals.operationLayerSettings[i].layerDetails;
                    for (j = 0; j < layerDetails.popupInfo.fieldInfos.length; j++) {
                        try {
                            // Get field value from feature attributes
                            fieldValue = featureSet.attributes[layerDetails.popupInfo.fieldInfos[j].fieldName];
                        } catch (e) {
                            fieldValue = appGlobals.configData.ShowNullValueAs;
                        }
                        fieldName = layerDetails.popupInfo.fieldInfos[j].fieldName;
                        fieldInfo = this.isDateField(fieldName, layerDetails.layerObject);
                        if (fieldInfo) {
                            if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                fieldValue = this.setDateFormat(layerDetails.popupInfo.fieldInfos[j], fieldValue);
                                featureSet.attributes[layerDetails.popupInfo.fieldInfos[j].fieldName] = fieldValue;
                            }
                        } else {
                            // Check if field has coded values
                            fieldInfo = this.hasDomainCodedValue(fieldName, featureSet.attributes, layerDetails.layerObject);
                            if (fieldInfo) {
                                if (fieldInfo.isTypeIdField) {
                                    fieldValue = fieldInfo.name;
                                } else {
                                    domainValue = this.domainCodedValues(fieldInfo, fieldValue);
                                    fieldValue = domainValue.domainCodedValue;
                                }
                            }
                            if (layerDetails.popupInfo.fieldInfos[j].format) {
                                if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                    fieldValue = this.numberFormatCorverter(layerDetails.popupInfo.fieldInfos[j], fieldValue);
                                }
                            }
                        }
                    }
                }
            }
            return featureSet;
        },

        /**
        * Gallery Tab for InfoWindow
        * @param {object} galleryObject contain attribute, index, featureLayer, widget name, attachment
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _infoWindowGalleryTab: function (galleryObject) {
            var hasLayerAttachments, galleryTabContainer;
            // Check the node "this.galleryContainer"
            if (this.galleryContainer) {
                domConstruct.destroy(this.galleryContainer);
            }
            galleryTabContainer = dom.byId("galleryTabContainer");
            this.galleryContainer = domConstruct.create("div", { "class": "esriCTGalleryInfoContainer" }, galleryTabContainer);
            if (galleryObject.attribute && galleryObject.LayerID) {
                hasLayerAttachments = true;
                this.map._layers[galleryObject.LayerID].queryAttachmentInfos(galleryObject.attribute[this.map._layers[galleryObject.LayerID].objectIdField], lang.hitch(this, this.getAttachments), this._errorLog);
            }
            // If attachment is not on the layer
            if (!hasLayerAttachments) {
                domConstruct.create("div", { "class": "esriCTGalleryBox", "innerHTML": sharedNls.errorMessages.imageDoesNotFound }, this.galleryContainer);
            }
        },

        /**
        * Change the image when click on next arrow of image
        * @param {object} error contain the error string
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        _errorLog: function (error) {
            console.log(error);
            if (this.galleryContainer) {
                domConstruct.destroy(this.galleryContainer);
            }
            domConstruct.create("div", { "class": "esriCTGalleryBox", "innerHTML": error }, this.galleryContainer);
        },

        /**
        * This function is used to convert number to thousand separator
        * @memberOf widgets/commonHelper/InfoWindowHelper
        */
        convertNumberToThousandSeperator: function (number) {
            number = number.split(".");
            number[0] = number[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
            return number.join('.');
        }
    });
});
