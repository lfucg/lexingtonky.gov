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
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/_base/lang",
    "dojo/on",
    "dojo/window",
    "dojo/dom-geometry",
    "dojo/dom",
    "dojo/_base/array",
    "dojo/dom-class",
    "esri/tasks/query",
    "dojo/Deferred",
    "esri/tasks/QueryTask",
    "esri/geometry/Point",
    "dojo/text!./templates/searchSettingTemplate.html",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "esri/urlUtils",
    "../searchSetting/activitySearch",
    "esri/request",
    "../searchSetting/eventPlannerHelper",
    "widgets/locator/locator",
    "dijit/a11yclick",
    "dojo/date/locale"

], function (declare, domConstruct, domStyle, domAttr, lang, on, win, domGeom, dom, array, domClass, Query, Deferred, QueryTask, Point, template, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, sharedNls, topic, urlUtils, ActivitySearch, esriRequest, EventPlannerHelper, LocatorTool, a11yclick, locale) {
    // ========================================================================================================================//

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, ActivitySearch, EventPlannerHelper], {
        templateString: template,                                         // Variable for template string
        sharedNls: sharedNls,                                             // Variable for shared NLS
        acitivityListDiv: null,                                           // Variable store the activity list div
        locatorAddress: "",                                               // Variable for locator address
        isExtentSet: false,                                               // Variable for set the extent in share case
        todayDate: new Date(),                                            // Variable for getting today's date
        widgetName: null,                                                 // Variable to store the widget name
        selectedLayerTitle: null,                                         // Variable for selected layer title
        myListStore: [],                                                  // Array to store myList data
        geoLocationGraphicsLayerID: "geoLocationGraphicsLayer",           // Geolocation graphics layer id
        locatorGraphicsLayerID: "esriGraphicsLayerMapSettings",           // Locator graphics layer id
        /**
        * Display locator, activity and event search in one panel
        *
        * @class
        * @name widgets/searchSetting/searchSetting
        */
        postCreate: function () {
            var contHeight, locatorParams, locatorObject, routeObject, objectIDField, getSearchSettingsDetails, mapPoint, isTrue = false, settingsName, objectIDValue, index, URL, settings;
            this.myFromDate.constraints.min = this.todayDate;
            this.myToDate.constraints.min = this.todayDate;
            // Setting panel's title from config file
            this.searchPanelTitle.innerHTML = appGlobals.configData.SearchPanelTitle;
            this.activityPanelTitle.innerHTML = appGlobals.configData.ActivityPanelTitle;
            this.eventsPanelTitle.innerHTML = appGlobals.configData.EventPanelTitle;
            domAttr.set(this.searchPanelTitle, "title", appGlobals.configData.SearchPanelTitle);
            domAttr.set(this.activityPanelTitle, "title", appGlobals.configData.ActivityPanelTitle);
            domAttr.set(this.eventsPanelTitle, "title", appGlobals.configData.EventPanelTitle);
            /**
            * Close locator widget if any other widget is opened
            * @param {string} widget Key of the newly opened widget
            */
            topic.subscribe("toggleWidget", lang.hitch(this, function (widget) {
                if (widget !== "searchSetting") {
                    if (domGeom.getMarginBox(this.divSearchContainer).h > 0) {
                        domClass.replace(this.domNode, "esriCTHeaderSearch", "esriCTHeaderSearchSelected");
                        domClass.replace(this.divSearchContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
                    }
                } else {
                    if (domClass.contains(this.divSearchContainer, "esriCTHideContainerHeight")) {
                        contHeight = domStyle.get(this.divSearchResultContent, "height");
                        domStyle.set(this.divSearchContainer, "height", contHeight + 2 + "px");
                    }
                }
            }));
            this.domNode = domConstruct.create("div", { "title": sharedNls.tooltips.search, "class": "esriCTHeaderSearch" }, null);
            // Looping for showing and hiding event search div
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (EventSearchSetting) {
                // Checking condition for event search setting enable tag
                if (!EventSearchSetting.Enable) {
                    domStyle.set(this.divEventsPanel, "display", "none");
                } else {
                    isTrue = true;
                }
            }));
            // If activity search is not enable
            if (!appGlobals.configData.ActivitySearchSettings[0].Enable) {
                domStyle.set(this.divActivityPanel, "display", "none");
                if (!appGlobals.configData.ActivitySearchSettings[0].Enable && appGlobals.configData.SearchPanelTitle !== "" && !isTrue) {
                    domClass.replace(this.divSearchPanel, "esriCTDivSearch", "esriCTDivSearchPanel");
                }
            }
            domConstruct.place(this.divSearchContainer, dom.byId("esriCTParentDivContainer"));
            this.own(on(this.domNode, a11yclick, lang.hitch(this, function () {
                /**
                * Minimize other open header panel widgets and show locator widget
                */
                this.isExtentSet = true;
                this.isInfowindowHide = true;
                topic.publish("extentSetValue", true);
                topic.publish("toggleWidget", "searchSetting");
                if (win.getBox().w <= 766) {
                    topic.publish("collapseCarousel");
                }
                this._showLocateContainer();
                // Checking for feature search data of event search if it is present then
                if (this.featureSet && this.featureSet.length > 0) {
                    this._showActivitiesList();
                }
            })));
            // Subscribing to store value for extent from other widget.
            topic.subscribe("extentSetValue", lang.hitch(this, function (value) {
                this.isExtentSet = value;
            }));
            domAttr.set(this.divSearchContainer, "title", "");
            // Click for activity tab in search header panel
            this.own(on(this.divActivityPanel, a11yclick, lang.hitch(this, function () {
                this._showActivityTab();
            })));
            // calling function for create carousel pod is ActivitySearchSettings is enable
            if (appGlobals.configData.ActivitySearchSettings[0].Enable) {
                this._showActivitySearchContainer();
            }
            // Click for unified search tab in search header panel
            this.own(on(this.divSearchPanel, a11yclick, lang.hitch(this, function () {
                this._showSearchTab();
            })));
            // Click for event tab in search header panel
            this.own(on(this.divEventsPanel, a11yclick, lang.hitch(this, function () {
                this._showEventTab();
            })));
            // click on "GO" button in activity search
            this.own(on(this.buttonGo, a11yclick, lang.hitch(this, function () {
                topic.publish("removeBuffer");
                topic.publish("clearGraphicsAndCarousel");
                topic.publish("removeRouteGraphichOfDirectionWidget");
                topic.publish("hideInfoWindow");
                topic.publish("extentSetValue", true);

                //clear share results
                this.mapPoint = null;
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


                appGlobals.shareOptions.isActivitySearch = true;
                this.featureSet.length = 0;
                this._activityPlannerDateValidation();
            })));
            // Change event for date in event planner
            this.own(on(this.myFromDate, "change", lang.hitch(this, function () {
                this.myToDate.reset();
                this.myToDate.constraints.min = this.myFromDate.value;
            })));
            // Proxy setting for route services
            urlUtils.addProxyRule({
                urlPrefix: appGlobals.configData.DrivingDirectionSettings.RouteServiceURL,
                proxyUrl: appGlobals.configData.ProxyUrl
            });
            // Calling function to showing the search tab
            this._showSearchTab();
            // Locator object for unified search
            locatorParams = {
                defaultAddress: appGlobals.configData.LocatorSettings.LocatorDefaultAddress,
                preLoaded: false,
                parentDomNode: this.divSearchContent,
                map: this.map,
                graphicsLayerId: this.locatorGraphicsLayerID,
                locatorSettings: appGlobals.configData.LocatorSettings,
                configSearchSettings: appGlobals.configData.SearchSettings,
                extendedClear: true
            };
            locatorObject = new LocatorTool(locatorParams);
            // Callback after adding graphics
            locatorObject.onGraphicAdd = lang.hitch(this, function (a, b) {
                appGlobals.shareOptions.addressLocation = locatorObject.selectedGraphic.geometry.x.toString() + "," + locatorObject.selectedGraphic.geometry.y.toString();
                appGlobals.shareOptions.doQuery = "false";
                if (window.location.toString().split("$address=").length > 1) {
                    if (window.location.toString().split("$settingsDetails=").length > 1) {
                        settingsName = window.location.toString().split("$settingsDetails=")[1].split(",")[0];
                        index = Number(window.location.toString().split("$settingsDetails=")[1].split(",")[1]);
                        URL = this.getURLFromSettings(settingsName, index);
                        settings = this.getSearchSettingByURL(URL);
                        objectIDValue = Number(window.location.toString().split("$settingsDetails=")[1].split(",")[2].split("$")[0]);
                        this._queryForActivityForAddressSearch(objectIDValue, settings);
                    } else {
                        if (window.location.toString().split("$isActivitySearch=").length > 1) {
                            if (!Boolean(window.location.toString().split("$isActivitySearch=")[1])) {
                                topic.publish("createBuffer", locatorObject.selectedGraphic);
                            }
                        } else {
                            topic.publish("createBuffer", locatorObject.selectedGraphic);
                        }
                    }
                }
                if (a && (b === "slider" || b === "click")) {
                    appGlobals.shareOptions.doQuery = "false";
                    topic.publish("createBuffer", a, "geolocation");
                }
            });
            // Locator candidate click for unified search
            locatorObject.candidateClicked = lang.hitch(this, function (candidate) {
                this.selectedLayerTitle = null;
                topic.publish("hideInfoWindow");
                if (candidate && candidate.attributes && candidate.attributes.address) {
                    this.locatorAddress = candidate.attributes.address;
                    appGlobals.shareOptions.searchSettingsDetails = null;
                    topic.publish("createBuffer", locatorObject.selectedGraphic);
                }
                // Calling function for locate container
                this._showLocateContainer();
                // Calling function to remove the geolocation pushpin
                topic.publish("removeGeolocationPushPin");
                if (candidate.geometry) {
                    locatorObject._toggleTexBoxControls(false);
                    locatorObject._locateAddressOnMap(candidate.geometry);
                }
                if (candidate && candidate.layer) {
                    topic.publish("addCarouselPod");
                    this.selectedLayerTitle = candidate.layer.SearchDisplayTitle;
                    routeObject = { "StartPoint": candidate, "EndPoint": [candidate], "Index": 0, "WidgetName": "unifiedsearch", "QueryURL": candidate.layer.QueryURL, "isLayerCandidateClicked": true };
                    topic.publish("showRoute", routeObject);
                    getSearchSettingsDetails = this.getSearchSetting(candidate.layer.QueryURL);
                    objectIDField = candidate.attributes[candidate.layer.ObjectID];
                    appGlobals.shareOptions.searchSettingsDetails = getSearchSettingsDetails.settingName + "," + getSearchSettingsDetails.index + "," + objectIDField;
                    if (locatorObject && locatorObject.selectedGraphic === null) {
                        appGlobals.shareOptions.addressLocation = candidate.geometry.x.toString() + "," + candidate.geometry.y.toString();
                    }
                }
            });
            // Subscribing to store value of myList data.
            topic.subscribe("getAcitivityListDiv", lang.hitch(this, function (value) {
                this.acitivityListDiv = value;
            }));
            // Subscribing function getting carousel Container object
            topic.subscribe("getCarouselContainer", lang.hitch(this, function (value, carouselPodData) {
                this.carouselContainer = value;
                this.carouselPodData = carouselPodData;
            }));
            // Publish for getting carousel container data
            topic.publish("getCarouselContainerData");
            // subscribing to store value of sortedList
            topic.subscribe("sortMyListData", lang.hitch(this, function (value) {
                this.sortedList = value;
            }));
            // Subscribing function for setting myListStoreData
            topic.subscribe("getMyListStoreData", lang.hitch(this, function (value) {
                this.myListStore = value;
            }));
            // Subscribing "addressSearch" in share URL
            topic.subscribe("addressSearch", lang.hitch(this, function () {
                // Check "address" is there in share URL
                if (window.location.toString().split("$address=").length > 1) {
                    mapPoint = new Point(window.location.toString().split("$address=")[1].split("$")[0].split(",")[0], window.location.toString().split("$address=")[1].split("$")[0].split(",")[1], this.map.spatialReference);
                    appGlobals.shareOptions.addressLocation = window.location.toString().split("$address=")[1].split("$")[0];
                    setTimeout(lang.hitch(this, function () {
                        locatorObject._locateAddressOnMap(mapPoint);
                    }, 5000));
                }
            }));
            // Function for share app
            setTimeout(lang.hitch(this, function () {
                if (window.location.toString().split("$sharedGeolocation=").length > 1 && window.location.toString().split("$sharedGeolocation=")[1].substring(0, 5) !== "false") {
                    var isZoomToGeolocation = this.setZoomForGeolocation();
                    // Check  if  required fields in browsers that support for geolocation or not
                    if (Modernizr.geolocation) {
                        // dijit.registry stores a collection of all the geoLocation widgets within a page
                        if (dijit.registry.byId("geoLocation")) {
                            dijit.registry.byId("geoLocation").showCurrentLocation(true, isZoomToGeolocation);
                            dijit.registry.byId("geoLocation").onGeolocationComplete = lang.hitch(this, function (mapPoint, isPreLoaded) {
                                // Variable to stored the gelocation point for share URL
                                appGlobals.shareOptions.sharedGeolocation = mapPoint;
                                if (mapPoint) {
                                    if (isPreLoaded) {
                                        appGlobals.shareOptions.doQuery = "false";
                                        topic.publish("createBuffer", mapPoint, "geolocation");
                                    }
                                }
                            });
                            // dijit.registry stores a collection of all the geoLocation widgets within a page
                            dijit.registry.byId("geoLocation").onGeolocationError = lang.hitch(this, function (error, isPreLoaded) {
                                if (isPreLoaded) {
                                    topic.publish("hideInfoWindow");
                                    alert(error);
                                }
                            });
                        }
                    } else {
                        topic.publish("hideProgressIndicator");
                        alert(sharedNls.errorMessages.activitySearchGeolocationText);
                    }
                }
            }, 5000));
            // Function for share app
            setTimeout(lang.hitch(this, function () {
                var startDate, endDate, formatedStartDate, formatedEndDate;
                // Check if eventplanner is there in share URL or not.
                if (window.location.toString().split("$eventplanner=").length > 1) {
                    startDate = window.location.toString().split("$startDate=")[1].split("$endDate=")[0].replace(new RegExp(",", 'g'), " ");
                    endDate = window.location.toString().split("$startDate=")[1].split("$endDate=")[1].split("$")[0].replace(new RegExp(",", 'g'), " ");
                    formatedStartDate = locale.format(new Date(startDate), { datePattern: "MM/dd/yyyy", selector: "date" });
                    formatedEndDate = locale.format(new Date(endDate), { datePattern: "MM/dd/yyyy", selector: "date" });
                    this._queryForActivity(formatedStartDate, formatedEndDate);
                    this.myFromDate.value = this.utcTimestampFromMs(startDate);
                    this.myToDate.value = this.utcTimestampFromMs(endDate);
                    this.myToDate.textbox.value = formatedEndDate;
                    this.myFromDate.textbox.value = formatedStartDate;
                    appGlobals.shareOptions.eventPlannerQuery = this.myFromDate.value.toString() + "," + this.myToDate.value.toString();
                    topic.publish("toggleWidget", "myList");
                    this.isEventShared = true;
                    appGlobals.shareOptions.isActivitySearch = true;
                }
            }), 3000);
        },

        /**
        * Execute query for the layer
        * @param {number} index of feature layer
        * @param {object} mapPoint
        * @param {array} onMapFeaturArray Contains array of feature layer URL
        * @memberOf widgets/mapSettings/mapSettings
        */
        _executeQueryTask: function (index, mapPoint, onMapFeaturArray) {
            var queryTask, queryLayer, currentDate = new Date().getTime().toString() + index, deferred;
            queryTask = new QueryTask(appGlobals.operationLayerSettings[index].layerURL);
            queryLayer = new Query();
            queryLayer.where = currentDate + "=" + currentDate;
            queryLayer.outSpatialReference = this.map.spatialReference;
            queryLayer.returnGeometry = true;
            queryLayer.maxAllowableOffset = 100;
            queryLayer.geometry = mapPoint;
            queryLayer.outFields = ["*"];
            deferred = new Deferred();
            queryTask.execute(queryLayer, lang.hitch(this, function (results) {
                deferred.resolve(results);
            }), function (err) {
                alert(err.message);
                deferred.resolve();
            });
            onMapFeaturArray.push(deferred);
        },

        /**
        * Get the setting name by passing query layer
        * @param{string} queryURL contains the url
        * @return search setting Data
        * @memberOf widgets/searchSetting/searchSetting
        */
        getSearchSetting: function (queryURL) {
            var settingData = {};
            // Looping for getting object id from event search.
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                if (settings.QueryURL === queryURL) {
                    settingData = { "settingName": "eventsetting", "index": eventSettingIndex };
                }
            }));
            // Looping for getting object id from activity search.
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings, activitySettingIndex) {
                if (settings.QueryURL === queryURL) {
                    settingData = { "settingName": "activitysetting", "index": activitySettingIndex };
                }
            }));
            return settingData;
        },

        /**
        * Get the url from settings
        * @param{string} settingsName contains the settings name
        * @param{string} index contains index number of settings
        * @return URL of the search settings
        * @memberOf widgets/searchSetting/searchSetting
        */
        getURLFromSettings: function (settingsName, index) {
            var URL = "";
            // Looping for event search settings to get url
            if (settingsName === "eventsetting") {
                array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                    if (index === eventSettingIndex) {
                        URL = settings.QueryURL;
                    }
                }));
            } else {
                // Looping for event search settings to get url
                array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings, activitySettingIndex) {
                    if (index === activitySettingIndex) {
                        URL = settings.QueryURL;
                    }
                }));
            }
            return URL;
        },

        /**
        * Get the setting name by passing query layer
        * @ return search setting Data
        * @memberOf widgets/commonHelper/commonHelper
        */
        getSearchSettingByURL: function (queryURL) {
            var settingData;
            // Looping for getting object id from event search.
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings) {
                if (settings.QueryURL === queryURL) {
                    settingData = settings;
                }
            }));
            // Looping for getting object id from activity search.
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings) {
                if (settings.QueryURL === queryURL) {
                    settingData = settings;
                }
            }));
            return settingData;
        },

        /**
        * Get the layer information after doing json call for comment layer's object Id
        * @param {data} layer url
        * @return {object} layer request
        * @memberOf widgets/searchSetting/searchSetting
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
        * Get object id key name from the layer
        * @param {object} object of layer
        * @return {object} object Id
        * @memberOf widgets/searchSetting/searchSetting
        */
        getObjectId: function (response) {
            var objectId, j;
            for (j = 0; j < response.fields.length; j++) {
                if (response.fields[j].type === "esriFieldTypeOID") {
                    objectId = response.fields[j].name;
                    break;
                }
            }
            return objectId;
        },

        /**
        * Get date field key name from layer
        * @param {object} object of layer
        * @return {array} array of date field key name
        * @memberOf widgets/searchSetting/searchSetting
        */
        getDateField: function (response) {
            var j, dateFieldArray = [], dateField;
            // Looping for getting date field name
            for (j = 0; j < response.fields.length; j++) {
                // Checking for date field type
                if (response.fields[j].type === "esriFieldTypeDate") {
                    dateField = response.fields[j].name;
                    dateFieldArray.push(dateField);
                }
            }
            return dateFieldArray;
        },

        /**
        * Change the date format with configured date format
        * @param {object} object of featureSet
        * @return {object} object of event Search Settings
        * @memberOf widgets/searchSetting/searchSetting
        */
        _changeDateFormat: function (featureSet) {
            var attributes, i, l, j, k, layerDetails, fieldValue, fieldName, fieldInfo;
            for (k = 0; k < featureSet.features.length; k++) {
                attributes = featureSet.features[k].attributes;
                for (l in attributes) {
                    if (attributes.hasOwnProperty(l)) {
                        if ((!attributes[l]) && (attributes[l] !== 0 || lang.trim(String(attributes[l])) === "")) {
                            attributes[l] = appGlobals.configData.ShowNullValueAs;
                        }
                    }
                }
                for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                    if (appGlobals.operationLayerSettings[i].layerDetails && appGlobals.operationLayerSettings[i].layerDetails.popupInfo) {
                        layerDetails = appGlobals.operationLayerSettings[i].layerDetails;
                        for (j = 0; j < layerDetails.popupInfo.fieldInfos.length; j++) {
                            try {
                                // Get field value from feature attributes
                                fieldValue = attributes[layerDetails.popupInfo.fieldInfos[j].fieldName];
                            } catch (ex) {
                                fieldValue = appGlobals.configData.ShowNullValueAs;
                            }
                            fieldName = layerDetails.popupInfo.fieldInfos[j].fieldName;
                            fieldInfo = this.isDateField(fieldName, layerDetails.layerObject);
                            if (fieldInfo) {
                                if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                    fieldValue = this.setDateFormat(layerDetails.popupInfo.fieldInfos[j], fieldValue);
                                    attributes[layerDetails.popupInfo.fieldInfos[j].fieldName] = fieldValue;
                                }
                            }
                        }
                    }
                }
            }
            return featureSet;
        },

        /**
        * Get the key field value from the config file
        * @param {data} keyField value with $ sign
        * @member Of widgets/searchSetting/searchSetting
        */
        getKeyValue: function (data) {
            var firstPlace, secondPlace, keyValue;
            firstPlace = data.indexOf("{");
            secondPlace = data.indexOf("}");
            keyValue = data.substring(Number(firstPlace) + 1, secondPlace);
            return keyValue;
        },

        /**
        * Hide eventPlanner list when date is not selected or selected date is not valid
        * @member Of widgets/searchSetting/searchSetting
        */
        _hideActivitiesList: function () {
            if (this.divEventContainer.childNodes.length > 1) {
                domConstruct.destroy(this.divEventContainer.children[1]);
            }
        },

        /**
        * Remove null value from the attribute.
        * @param {object} featureObject is object for feature
        * @return {object} feature set after removing null value
        * @member Of widgets/searchSetting/searchSetting
        */
        _removeNullValue: function (featureObject) {
            var i, j;
            if (featureObject) {
                // Looping feature set object for removing null value and setting null value from nls file
                for (i = 0; i < featureObject.length; i++) {
                    for (j in featureObject[i].attributes) {
                        if (featureObject[i].attributes.hasOwnProperty(j)) {
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
        * Setting value to change for extent
        * @returns the boolean value for setting extent for geolocation
        * @member Of widgets/searchSetting/searchSetting
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
        }
    });
});
