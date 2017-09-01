/*global define,dojoConfig,dojo,alert,console,Modernizr,dijit,appGlobals*/
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
    "dojo/dom",
    "dojo/dom-attr",
    "dojo/dom-class",
    "dojo/_base/html",
    "dojo/dom-style",
    "dojo/date/locale",
    "dojo/text!./templates/myListTemplate.html",
    "dojo/window",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "dojo/date",
    "esri/geometry/Point",
    "dojo/_base/array",
    "dojo/string",
    "dojo/query",
    "dijit/a11yclick",
    "../myList/myListHelper"
], function (declare, domConstruct, lang, on, dom, domAttr, domClass, html, domStyle, locale, template, win, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, sharedNls, topic, date, Point, array, string, query, a11yclick, MyListHelper) {
    //========================================================================================================================//

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, MyListHelper], {
        templateString: template,            // Variable for template string
        sharedNls: sharedNls,                // Variable for shared NLS
        todayDate: new Date(),               // Variable for getting today's date
        myListStore: [],                     // Array to store event and activity added from list
        featureSet: null,                    // Variable to store feature searched from event search
        addToListFeatures: [],               // Array to store feature added to mylist from info window or info pod
        dateFieldArray: [],                  // Array to store date field name from layer to change date format
        isExtentSet: false,                  // Checks the extent is set in the case of shared app
        /**
        * Create myList widget to store feature added from activity and event, User can search show route, add to calendar, find route for list, print for list.
        *
        * @class
        * @name widgets/myList/myList
        */
        postCreate: function () {
            /**
            * Minimize other open header panel widgets and show myList
            */
            topic.subscribe("toggleWidget", lang.hitch(this, function (widget) {
                var myListContainer = query(".esriCTMyListContainer")[0];
                // Checking if widget name is 'myList' then show panel.
                if (widget !== "myList") {
                    // Checking if panel is open for replacing class.
                    if (html.coords(this.applicationHeaderActivityContainer).h > 0) {
                        domClass.replace(this.domNode, "esriCTEventsImg", "esriCTEventsImgSelected");
                        domClass.replace(this.applicationHeaderActivityContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
                        if (query(".esriCTShowMyListContainer")[0]) {
                            domClass.replace(myListContainer, "esriCTHideMyListContainer", "esriCTShowMyListContainer");
                        }
                    }
                }
            }));
            this.domNode = domConstruct.create("div", { "title": sharedNls.tooltips.eventsTooltip, "class": "esriCTEventsImg" }, null);
            dom.byId("esriCTParentDivContainer").appendChild(this.applicationHeaderActivityContainer);

            /** Subscribe functions for calling them from other widget
            subscribing for showing myList container.*/
            topic.subscribe("showActivityPlannerContainer", lang.hitch(this, function () {
                this._showMyListContainer();
            }));

            // Subscribing to store feature set searched from event search in eventPlannerHelper.js file.
            topic.subscribe("setEventFeatrueSet", lang.hitch(this, function (value) {
                this.featureSet = value;
            }));

            // Subscribing to refresh myList panel data from other file.
            topic.subscribe("refreshMyList", lang.hitch(this, function (eventObject) {
                this._refreshMyList(eventObject);
            }));

            // Subscribing to show route from list
            topic.subscribe("eventForListForShare", lang.hitch(this, function () {
                this._drawRouteForListItem();
            }));

            // Subscribing for storing myList data from other file
            topic.subscribe("getMyListData", lang.hitch(this, function (value) {
                this.myListStore = value;
            }));

            // Subscribing for storing myList data from other file
            topic.subscribe("infowWindowClick", lang.hitch(this, function () {
                this.infowWindowClick = true;
            }));

            // Subscribing for replacing class
            topic.subscribe("replaceClassForMyList", lang.hitch(this, function () {
                this._replaceClassForMyList();
            }));

            // Subscribing for call sort my list function from other file
            topic.subscribe("sortMyList", lang.hitch(this, function (ascendingFlag, featureSet) {
                this.sortedList = this._sortMyList(ascendingFlag, featureSet);
                topic.publish("sortMyListData", this.sortedList);
            }));

            // Subscribing for replacing class for application header container.
            topic.subscribe("replaceApplicationHeaderContainer", lang.hitch(this, function () {
                domClass.replace(this.applicationHeaderActivityContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
            }));

            /** Subscribe functions for calling them from other widget
            *  subscribing to execute query for event for list.
            */
            topic.subscribe("eventForListClick", lang.hitch(this, function (featureSetObject) {
                this._executeQueryForEventForList(featureSetObject);
            }));

            // Subscribing for getting widget name
            topic.subscribe("showWidgetName", lang.hitch(this, function (widgetName) {
                this.widgetName = widgetName;
            }));

            // Subscribing for add To List Features Data
            topic.subscribe("addToListFeaturesData", lang.hitch(this, function (value) {
                this.addToListFeatures = value;
            }));

            /** End for subscribe function for calling them from other widget */

            /** On click functions */
            // Function to show myList container on click.
            this.own(on(this.domNode, a11yclick, lang.hitch(this, function () {
                /**
                * minimize other open header panel widgets and show events panel
                */
                topic.publish("toggleWidget", "myList");
                this._showMyListContainer();
            })));

            // Function to sort data by clicking on order by button.
            this.own(on(this.orderByDate, a11yclick, lang.hitch(this, function () {
                var sortedMyList, eventObject, divHeaderContent;
                this.isExtentSet = true;
                divHeaderContent = query('.esriCTMyListHeaderTextDisable');
                if (divHeaderContent.length > 0) {
                    return;
                }
                topic.publish("extentSetValue", this.isExtentSet);
                if (domClass.contains(this.orderByDateImage, "esriCTImgOrderByDateDown")) {
                    // Sort with descending order of date
                    sortedMyList = this._sortMyList(false, this.featureSet);
                } else if (domClass.contains(this.orderByDateImage, "esriCTImgOrderByDateUp")) {
                    // Sort with ascending order of date
                    sortedMyList = this._sortMyList(true, this.featureSet);
                }
                eventObject = { "EventDetails": null, "SortedData": sortedMyList, "InfowindowClick": false };
                // Show data in mylist panel after order by list.
                this._refreshMyList(eventObject);
            })));

            // On click on route for list icon in my list panel.
            this.own(on(this.directionForEvents, "click", lang.hitch(this, function (event) {
                if (event.currentTarget.className === "esriCTHeaderDirectionAcitivityList") {
                    topic.publish("extentSetValue", true);
                    this._drawRouteForListItem();
                }
            })));

            // Click on print for list icon
            this.own(on(this.printEventList, "click", lang.hitch(this, function () {
                topic.publish("extentSetValue", true);
                this._printForEventList();
            })));

            // On click on calendar icon for list
            on(this.calenderForEvent, "click", lang.hitch(this, function () {
                if (query(".esriCTHeaderAddAcitivityList")[0]) {
                    topic.publish("extentSetValue", true);
                    this._showDataForCalendar();
                }
            }));
            /** End of On click functions */

            // Check if getDirection is enable then add the class"esriCTHeaderDirectionAcitivityListDisable"
            if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                domClass.add(this.directionForEvents, "esriCTHeaderDirectionAcitivityListDisable");
            }
            on(window, "resize", lang.hitch(this, function () {
                this.checkWidthForNexusDevice();
            }));

            setTimeout(lang.hitch(this, function () {
                var eventObjectId, activityObjectId;
                // Check if eventInfoWindowAttribute is there in share URL or not. It stores the event layer objectID.
                if (window.location.toString().split("$eventInfoWindowAttribute=").length > 1) {
                    eventObjectId = window.location.toString().split("eventInfoWindowAttribute=")[1].split("$")[0];
                    this._queryForEventShare(eventObjectId);
                    appGlobals.shareOptions.eventInfoWindowAttribute = eventObjectId;
                }
                // Check if eventInfoWindowIdActivity is there in share URL or not. It stores the activity layer objectID.
                if (window.location.toString().split("$eventInfoWindowIdActivity=").length > 1) {
                    activityObjectId = window.location.toString().split("eventInfoWindowIdActivity=")[1].split("$")[0];
                    this._queryForActivityShare(activityObjectId);
                    appGlobals.shareOptions.eventInfoWindowIdActivity = activityObjectId;
                }
            }), 3000);
        },

        /**
        * Show and close My list panel
        * @memberOf widgets/myList/myList
        */
        _showMyListContainer: function () {
            var myListContainer = query(".esriCTMyListContainer")[0];
            if (html.coords(this.applicationHeaderActivityContainer).h > 1) {
                /**
                * When user clicks on eventPlanner icon in header panel, close the eventPlanner panel if it is open
                */
                domClass.replace(this.domNode, "esriCTEventsImg", "esriCTEventsImgSelected");
                domClass.replace(this.applicationHeaderActivityContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
                if (query(".esriCTShowMyListContainer")[0]) {
                    domClass.replace(myListContainer, "esriCTHideMyListContainer", "esriCTShowMyListContainer");
                }
            } else {
                /**
                * When user clicks on eventPlanner icon in header panel, open the eventPlanner panel if it is closed
                */
                domClass.replace(this.domNode, "esriCTEventsImgSelected", "esriCTEventsImg");
                this._showActivityTab();
                domClass.replace(this.applicationHeaderActivityContainer, "esriCTShowContainerHeight", "esriCTHideContainerHeight");
                if (query(".esriCTHideMyListContainer")[0]) {
                    domClass.replace(myListContainer, "esriCTShowMyListContainer", "esriCTHideMyListContainer");
                }
                if (win.getBox().w <= 766) {
                    topic.publish("collapseCarousel");
                }
            }
        },

        /**
        * Displays the updated and sorted list
        * @param {object} object of event details having Event attributes, sorted event details, infowindow click
        * @param widgetName string for refresh page according to info window click
        * @memberOf widgets/myList/myList
        */
        _refreshMyList: function (eventObject) {
            var fieldName, fieldValue, searchSetting, evenObjectID, featureArray = [], startDateAttribute, isDateFound = false, myListRow, myListLeft = [], myListRight, myListIcons, name, objectIdField, myListDeleteIcon = [], splitedField, finalText,
                directionAcitivityList = [], startDate, endDate, difference, addToCalender = [], isStartDateFound = false, layerId, layerTitle, n, endDateKeyField, isAddToCalandarEnabled, startDateKeyField, calEventEndDate, calEventStartDate, startDatesCount = 0;
            topic.publish("showProgressIndicator");
            appGlobals.shareOptions.addToListEvents = eventObject;
            // Checking feature set data length for removing null value from features.
            if (this.featureSet && this.featureSet.length > 0) {
                for (n = 0; n < this.featureSet.length; n++) {
                    this.featureSet[n].value.features = this._removeNullValue(this.featureSet[n].value.features);
                }
            }
            // Checking added feature details for getting object id value.
            if (eventObject.EventDetails) {
                evenObjectID = string.substitute("${" + eventObject.key + "}", eventObject.EventDetails);
            }
            // Checking activity list container's length
            if (this.activityList.childNodes.length > 1) {
                domConstruct.destroy(this.activityList.children[1]);
            }
            this.myListContainer = domConstruct.create("div", { "class": "esriCTMyListContainer esriCTShowMyListContainer" }, this.activityList);
            this.myListTable = domConstruct.create("div", { "class": "esriCTMyListTable" }, this.myListContainer);
            // Looping for creating row in my list panel from sorted data.
            array.forEach(eventObject.SortedData, function (myListEvent, j) {
                isStartDateFound = false;
                // Checking if my list item's search setting name for getting value from config.
                if (myListEvent.settingsName === "eventsettings") {
                    searchSetting = appGlobals.configData.EventSearchSettings;
                } else {
                    searchSetting = appGlobals.configData.ActivitySearchSettings;
                }
                // Checking if my list item's search setting name for getting value from config.
                finalText = "";
                if (myListEvent.settingsName === "eventsettings") {
                    splitedField = searchSetting[myListEvent.eventSettingsIndex].SearchDisplaySubFields ? searchSetting[myListEvent.eventSettingsIndex].SearchDisplaySubFields.split(',') : "";
                    array.forEach(splitedField, function (splitedFieldValue, splitedFieldIndex) {
                        fieldName = this.getKeyValue(splitedFieldValue);
                        fieldValue = myListEvent.value[fieldName] !== appGlobals.configData.ShowNullValueAs ? myListEvent.value[fieldName] : "";
                        finalText = finalText === "" ? fieldValue : finalText + (fieldValue === "" ? fieldValue : ", " + fieldValue);
                    }, this);
                    startDateAttribute = searchSetting[myListEvent.eventSettingsIndex].SortingKeyField ? this.getKeyValue(searchSetting[myListEvent.eventSettingsIndex].SortingKeyField) : "";
                    name = string.substitute(searchSetting[myListEvent.eventSettingsIndex].SearchDisplayFields, myListEvent.value);
                    layerId = searchSetting[myListEvent.eventSettingsIndex].QueryLayerId;
                    layerTitle = searchSetting[myListEvent.eventSettingsIndex].Title;
                } else {
                    name = string.substitute(searchSetting[0].SearchDisplayFields, myListEvent.value);
                    layerId = searchSetting[0].QueryLayerId;
                    layerTitle = searchSetting[0].Title;
                }
                objectIdField = string.substitute("${" + myListEvent.key + "}", myListEvent.value);
                myListRow = domConstruct.create("div", { "class": "esriCTMyListRow" }, this.myListTable);
                myListLeft[j] = domConstruct.create("div", { "class": "esriCTMyListLeft", "value": myListEvent.value }, myListRow);
                // Checking if name field has no value then set to N/A
                if (!name) {
                    name = appGlobals.configData.ShowNullValueAs;
                }
                domConstruct.create("div", { "class": "esriCTMyListText", "innerHTML": name }, myListLeft[j]);
                // Checking if eventDate field has no value then do not set event date and address
                domConstruct.create("div", { "class": "esriCTMyListDates", "innerHTML": finalText }, myListLeft[j]);
                myListRight = domConstruct.create("div", { "class": "esriCTMyListRight" }, myListRow);
                myListIcons = domConstruct.create("div", { "class": "esriCTMyListIcons" }, myListRight);
                domAttr.set(myListLeft[j], "LayerId", layerId);
                domAttr.set(myListLeft[j], "LayerTitle", layerTitle);
                // On click on my list item to show info window.
                on(myListLeft[j], a11yclick, lang.hitch(this, function (event) {
                    this._clickOnMyListRow(event);
                }));
                directionAcitivityList[j] = domConstruct.create("div", { "class": "esriCTDirectionEventListWithoutImage", "value": myListEvent.value }, myListIcons);
                if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                    directionAcitivityList[j].title = sharedNls.tooltips.routeTooltip;
                    domClass.replace(directionAcitivityList[j], "esriCTDirectionEventList", "esriCTDirectionEventListWithoutImage");
                }
                domAttr.set(directionAcitivityList[j], "ObjectID", objectIdField);
                domAttr.set(directionAcitivityList[j], "LayerId", layerId);
                domAttr.set(directionAcitivityList[j], "LayerTitle", layerTitle);
                // On click on my list item to calculate route and show data in bottom pod.
                on(directionAcitivityList[j], a11yclick, lang.hitch(this, function (event) {
                    if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                        this._clickOnRouteButton(event, directionAcitivityList[j], featureArray, eventObject);
                    }
                }));
                isAddToCalandarEnabled = false;
                // Checking if list have no start date attribute.
                if (myListEvent.settingsName === "eventsettings") {
                    endDateKeyField = this.getKeyValue(searchSetting[0].AddToCalendarSettings[0].EndDate);
                    startDateKeyField = this.getKeyValue(searchSetting[0].AddToCalendarSettings[0].StartDate);
                    calEventEndDate = myListEvent.featureSet.utcDate[endDateKeyField];
                    calEventStartDate = myListEvent.featureSet.utcDate[startDateKeyField];
                    if (calEventStartDate === null || calEventEndDate === null || calEventStartDate === appGlobals.configData.ShowNullValueAs || calEventEndDate === appGlobals.configData.ShowNullValueAs) {
                        isAddToCalandarEnabled = false;
                        if (calEventStartDate === null || calEventStartDate === appGlobals.configData.ShowNullValueAs) {
                            isStartDateFound = false;
                        } else {
                            isStartDateFound = true;
                        }
                    } else {
                        isStartDateFound = true;
                        startDate = new Date(calEventStartDate);
                        endDate = new Date(calEventEndDate);
                        difference = date.difference(startDate, endDate, "day");
                        if (difference < 0) {
                            isAddToCalandarEnabled = false;
                        } else {
                            isAddToCalandarEnabled = true;
                            isDateFound = true;
                        }
                    }
                }
                // If start date found then change the add to calendar icon color.
                if (isStartDateFound) {
                    startDatesCount++;
                    if (isAddToCalandarEnabled) {
                        addToCalender[j] = domConstruct.create("div", { "title": sharedNls.tooltips.addToCalanderTooltip, "class": "esriCTAddEventList" }, myListIcons);
                        domAttr.set(addToCalender[j], "WidgetName", "eventlistitem");
                    } else {
                        addToCalender[j] = domConstruct.create("div", { "title": sharedNls.tooltips.addToCalanderTooltip, "class": "esriCTActivityCalender" }, myListIcons);
                        domAttr.set(addToCalender[j], "WidgetName", "eventlistitem");
                    }
                } else {
                    addToCalender[j] = domConstruct.create("div", { "title": sharedNls.tooltips.addToCalanderTooltip, "class": "esriCTActivityCalender" }, myListIcons);
                    domAttr.set(addToCalender[j], "WidgetName", "activitylistitem");
                }
                domAttr.set(addToCalender[j], "ObjectID", objectIdField);
                // On click on my list add to calendar icon
                this.own(on(addToCalender[j], "click", lang.hitch(this, function (event) {
                    if (event.currentTarget.className !== "esriCTActivityCalender") {
                        this._clickOnAddToCalander(event);
                    }
                })));
                myListDeleteIcon[j] = domConstruct.create("div", { "title": sharedNls.tooltips.deleteFromListTooltip, "class": "esriCTDeleteEventList" }, myListIcons);
                domAttr.set(myListDeleteIcon[j], "ID", myListEvent.id);
                // If new event is being added, highlight the added event
                if (eventObject.EventDetails) {
                    if (objectIdField === evenObjectID) {
                        domClass.add(myListRow, "esriCTMyListRowChecked");
                    }
                } else {
                    domClass.remove(myListRow, "esriCTMyListRowChecked");
                }
                domAttr.set(myListDeleteIcon[j], "ObjectID", objectIdField);
                domAttr.set(myListDeleteIcon[j], "SettingsName", myListEvent.settingsName);
                domAttr.set(myListDeleteIcon[j], "StartDate", startDateAttribute);
                // On click to delete item from my list
                this.own(on(myListDeleteIcon[j], a11yclick, lang.hitch(this, function (event) {
                    this._clickOnMyListDeleteIcon(event, myListDeleteIcon, myListLeft, eventObject);
                })));
            }, this);
            domStyle.set(this.myListTable, "display", "block");
            this.checkWidthForNexusDevice();
            if (isDateFound) {
                domClass.replace(this.calenderForEvent, "esriCTHeaderAddAcitivityList", "esriCTHeaderAddAcitivityListDisable");
            } else {
                domClass.replace(this.calenderForEvent, "esriCTHeaderAddAcitivityListDisable", "esriCTHeaderAddAcitivityList");
            }
            if (startDatesCount < 2) {
                domClass.replace(this.orderByDateList, "esriCTMyListHeaderTextDisable", "esriCTMyListHeaderText");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDownDisable", "esriCTImgOrderByDateDown");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDisable", "esriCTImgOrderByDate");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDisable", "esriCTImgOrderByDateUp");
            }
            topic.publish("hideProgressIndicator");
            if (window.location.toString().split("$eventRouteforList=").length > 1) {
                if (this.myListStore.length === Number(window.location.toString().split("$eventRouteforList=")[1].split("$")[0])) {
                    //Checking for event for list to be called only one time
                    if (!eventObject.eventForOrder && !this.isExtentSet) {
                        topic.publish("eventForListForShare");
                    }
                }
            }
        },

        /**
        * Function to show info window on the click of my list item
        * @param {event} event contains the event data
        * @memberOf widgets/myList/myList
        */
        _clickOnMyListRow: function (event) {
            var featureData, mapPoint, objectIDforRow, LayerId, LayerTitle, g, activityListObjectId, infoWindowParameter, tolerance, screenPoint, mapPoint1, mapPoint2,
                pnt1, pnt2, shareOptionScreenPoint;
            topic.publish("hideCarouselContainer");
            LayerId = Number(domAttr.get(event.currentTarget, "LayerId"));
            LayerTitle = domAttr.get(event.currentTarget, "LayerTitle");
            this.isExtentSet = true;
            topic.publish("extentSetValue", true);
            objectIDforRow = this.getObjectIdFromSettings(LayerId, LayerTitle);
            activityListObjectId = event.currentTarget.value[objectIDforRow];
            // Looping for getting feature data for showing info window.
            for (g = 0; g < this.myListStore.length; g++) {
                if (this.myListStore[g].value[this.myListStore[g].key] === Number(activityListObjectId)) {
                    featureData = this.myListStore[g].featureSet;
                    break;
                }
            }
            // Object of infowindow parameter
            infoWindowParameter = {
                "mapPoint": featureData.geometry,
                "attribute": featureData.attributes,
                "layerId": LayerId,
                "layerTitle": LayerTitle,
                "featureArray": featureData,
                "featureSet": featureData,
                "IndexNumber": 1,
                "widgetName": "listclick"
            };
            mapPoint = featureData.geometry;
            topic.publish("extentFromPoint", mapPoint);
            appGlobals.shareOptions.mapClickedPoint = mapPoint;
            tolerance = 20;
            screenPoint = this.map.toScreen(mapPoint);
            pnt1 = new Point(screenPoint.x - tolerance, screenPoint.y + tolerance);
            pnt2 = new Point(screenPoint.x + tolerance, screenPoint.y - tolerance);
            mapPoint1 = this.map.toMap(pnt1);
            mapPoint2 = this.map.toMap(pnt2);
            // Set the screen point xmin, ymin, xmax, ymax
            shareOptionScreenPoint = mapPoint1.x + "," + mapPoint1.y + "," + mapPoint2.x + "," + mapPoint2.y;
            appGlobals.shareOptions.screenPoint = shareOptionScreenPoint;
            topic.publish("createInfoWindowContent", infoWindowParameter);
            topic.publish("setZoomAndCenterAt", featureData.geometry);
        },

        /**
        * Function to show route on the click of my list item
        * @param {directionAcitivityList} event contains the event data
        * @param {featureArray} event feature
        * @param {eventObject} contains event data information
        * @memberOf widgets/myList/myList
        */
        _clickOnRouteButton: function (event, directionAcitivityList, featureArray, eventObject) {
            var infoLayerId, infoLayerTitle, widgetName, queryURL, featureClick;
            topic.publish("extentSetValue", true);
            this.isExtentSet = true;
            infoLayerId = parseInt(domAttr.get(event.currentTarget, "LayerId"), 10);
            infoLayerTitle = domAttr.get(event.currentTarget, "LayerTitle");
            queryURL = this.getQueryUrl(infoLayerId, infoLayerTitle);
            topic.publish("getInfowWindowWidgetName", infoLayerTitle, infoLayerId);
            // Checking for widget name to show widget name
            if (this.widgetName === "InfoActivity") {
                widgetName = "activitysearch";
            } else if (this.widgetName === "InfoEvent") {
                widgetName = "event";
            }
            featureClick = "eventclick";
            domClass.replace(this.domNode, "esriCTEventsImg", "esriCTEventsImgSelected");
            this._drawRouteForSingleListItem(directionAcitivityList, featureArray, eventObject, widgetName, queryURL, featureClick);
        },

        /**
        * Function for add an event to calendar on the click of Add To Calendar icon
        * @param {event} event contains the event data
        * @memberOf widgets/myList/myList
        */
        _clickOnAddToCalander: function (event) {
            var featureData = [], l, layerName, layerInfowData;
            this.isExtentSet = true;
            layerInfowData = domAttr.get(event.currentTarget, "ObjectID");
            layerName = domAttr.get(event.currentTarget, "WidgetName");
            // Checking for layer name to store feature data to create ICS file to adding them in calendar.
            if (layerName === "eventlistitem") {
                for (l = 0; l < this.myListStore.length; l++) {
                    if (this.myListStore[l].value[this.myListStore[l].key] === Number(layerInfowData)) {
                        featureData.push(this.myListStore[l]);
                        break;
                    }
                }
                this._createICSFile(featureData); //passing the parameter as array because this will create single ICS file.
            }
        },

        /**
        * Function to delete an event/facility from my list on the click of My List Delete Icon
        * @param {object} event
        * @param {object} myListDeleteIcon - myListDeleteIcon object
        * @param {object} myListLeft - data of my list panel
        * @param {object} eventObject contains event data information
        * @memberOf widgets/myList/myList
        */
        _clickOnMyListDeleteIcon: function (event, myListDeleteIcon, myListLeft, eventObject) {
            var eventIndex, searchSetting, infoWindowArray = null, objId, eventIndexArray = null, infoWindowArrayActivity, objectID, eventObjectToRefresh, m, settingsName, startDate, i, objectIdFeild, indexForData;
            topic.publish("extentSetValue", true);
            this.isExtentSet = true;
            eventIndex = array.indexOf(myListDeleteIcon, event.currentTarget);
            settingsName = domAttr.get(event.currentTarget, "SettingsName");
            objectID = domAttr.get(event.currentTarget, "ObjectID");
            startDate = domAttr.get(event.currentTarget, "StartDate");
            // Verify the layer is event or activity
            if (settingsName === "eventsettings") {
                searchSetting = appGlobals.configData.EventSearchSettings;
            } else {
                searchSetting = appGlobals.configData.ActivitySearchSettings;
            }
            // Loop for feature which is added on myList
            for (i = 0; i < this.addToListFeatures.length; i++) {
                // "getObjectIdFromAddToList" returns the objectId fields
                objectIdFeild = this.getObjectIdFromAddToList(this.addToListFeatures[i]);
                if (this.addToListFeatures[i].value.attributes[objectIdFeild] === Number(objectID)) {
                    indexForData = i;
                    // "splice" is use to delete feature from Mylist
                    this.addToListFeatures.splice(indexForData, 1);
                }
            }
            topic.publish("addToListFeaturesUpdate", this.addToListFeatures);
            // Store the event objectID which is added from Infowindow and bottom pod
            if (appGlobals.shareOptions.eventInfoWindowAttribute) {
                infoWindowArray = appGlobals.shareOptions.eventInfoWindowAttribute.split(",");
            }
            // Store the activity objectID which is added from Infowindow and bottom pod
            if (appGlobals.shareOptions.eventInfoWindowIdActivity) {
                infoWindowArrayActivity = appGlobals.shareOptions.eventInfoWindowIdActivity.split(",");
            }
            // AppGlobals.shareOptions.eventIndex(array) is store the event objectID which is search from datePicker
            if (appGlobals.shareOptions.eventIndex) {
                eventIndexArray = appGlobals.shareOptions.eventIndex.split(",");
            }
            appGlobals.shareOptions.eventInfoWindowAttribute = null;
            appGlobals.shareOptions.eventIndex = null;
            // Looping for deleting events and activity from myList
            for (m = 0; m < this.myListStore.length; m++) {
                objId = this.myListStore[m].value[this.myListStore[m].key].toString();
                if (infoWindowArray) {
                    if (array.indexOf(infoWindowArray, objId) > -1 && myListLeft[eventIndex].value[this.myListStore[m].key].toString() === objId) {
                        infoWindowArray.splice(array.indexOf(infoWindowArray, objId), 1);
                    }
                }
                if (infoWindowArrayActivity) {
                    if (array.indexOf(infoWindowArrayActivity, objId) > -1 && myListLeft[eventIndex].value[this.myListStore[m].key].toString() === objId) {
                        infoWindowArrayActivity.splice(array.indexOf(infoWindowArrayActivity, objId), 1);
                    }
                }
                if (eventIndexArray) {
                    if (array.indexOf(eventIndexArray, objId) > -1 && myListLeft[eventIndex].value[this.myListStore[m].key].toString() === objId) {
                        eventIndexArray.splice(array.indexOf(eventIndexArray, objId), 1);
                    }
                }
            }
            if (infoWindowArray) {
                appGlobals.shareOptions.eventInfoWindowAttribute = infoWindowArray.join(",");
            }
            if (infoWindowArrayActivity) {
                appGlobals.shareOptions.eventInfoWindowIdActivity = infoWindowArrayActivity.join(",");
            }
            if (eventIndexArray) {
                appGlobals.shareOptions.eventIndex = eventIndexArray.join(",");
            }
            eventIndex = array.indexOf(myListDeleteIcon, event.currentTarget);
            this.myListStore.splice(eventIndex, 1);
            if ((this.myListStore.length === 0)) {
                if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                    domClass.replace(this.directionForEvents, "esriCTHeaderDirectionAcitivityListDisable", "esriCTHeaderDirectionAcitivityList");
                }
                domClass.replace(this.calenderForEvent, "esriCTHeaderAddAcitivityListDisable", "esriCTHeaderAddAcitivityList");
                domClass.replace(this.printEventList, "esriCTHeaderPrintAcitivityListDisable", "esriCTHeaderPrintAcitivityList");
                domClass.replace(this.orderByDateList, "esriCTMyListHeaderTextDisable", "esriCTMyListHeaderText");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDownDisable", "esriCTImgOrderByDateDown");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDisable", "esriCTImgOrderByDate");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDisable", "esriCTImgOrderByDateUp");
            }
            // Sort with ascending order of date
            eventObject.SortedData = this._sortMyList(true, this.featureSet);
            eventObjectToRefresh = { "EventDetails": null, "SortedData": eventObject.SortedData, "InfowindowClick": eventObject.InfowindowClick, "layerId": searchSetting.QueryLayerId, "layerTitle": searchSetting.Title, "settingsName": settingsName, "key": objectID, "startDateField": startDate };
            this._refreshMyList(eventObjectToRefresh);
        },

        /**
        * Function to draw route for single list item
        *@param {eventListObject} feature set for single event list item
        *@param {featureArray} feature array object
        *@param {eventObject} containing the data related to event item
        *@param {string} widgetName contains the data from it is coming
        *@param {string} queryURL contains query url for layer
        * @memberOf widgets/myList/myList
        */
        _drawRouteForSingleListItem: function (eventListObject, featureArray, eventObject, widgetName, queryURL, featureClick) {
            var isIndexFound = false, objectidOfEvents, g;
            this.isExtentSet = true;
            topic.publish("extentSetValue", true);
            appGlobals.shareOptions.infowindowDirection = null;
            topic.publish("getAcitivityListDiv", eventListObject);
            topic.publish("hideInfoWindow");
            topic.publish("showProgressIndicator");
            objectidOfEvents = domAttr.get(eventListObject, "ObjectID");
            featureArray.length = 0;
            // Looping in my list store array to get feature data
            for (g = 0; g < this.myListStore.length; g++) {
                if (this.myListStore[g].value[this.myListStore[g].key] === Number(objectidOfEvents)) {
                    featureArray.push(this.myListStore[g].featureSet);
                    appGlobals.shareOptions.eventRoutePoint = objectidOfEvents;
                    break;
                }
            }
            // Looping in feature set for getting feature type.
            if (this.featureSet && this.featureSet.length > 0) {
                array.forEach(this.featureSet, lang.hitch(this, function (featureSetData) {
                    array.forEach(featureSetData.value.features, lang.hitch(this, function (featureSet) {
                        if (Number(objectidOfEvents) === featureSet.attributes[featureSetData.key]) {
                            eventObject.InfowindowClick = false;
                            isIndexFound = true;
                        }
                    }));
                }));
                if (!isIndexFound) {
                    appGlobals.shareOptions.eventRoutePoint = null;
                    appGlobals.shareOptions.activitySearch = null;
                    appGlobals.shareOptions.searchFacilityIndex = null;
                    appGlobals.shareOptions.addressLocation = null;
                    appGlobals.shareOptions.addressLocationDirectionActivity = null;
                    appGlobals.shareOptions.infoRoutePoint = Number(objectidOfEvents);
                }
            } else {
                appGlobals.shareOptions.eventRoutePoint = null;
                appGlobals.shareOptions.activitySearch = null;
                appGlobals.shareOptions.searchFacilityIndex = null;
                appGlobals.shareOptions.addressLocation = null;
                appGlobals.shareOptions.addressLocationDirectionActivity = null;
                appGlobals.shareOptions.infoRoutePoint = Number(objectidOfEvents);
            }
            domClass.replace(this.applicationHeaderActivityContainer, "esriCTHideContainerHeight", "esriCTShowContainerHeight");
            topic.publish("executeQueryForFeatures", featureArray, queryURL, widgetName, featureClick);
        },

        /**
        * Convert the UTC time stamp from Millisecond
        * @returns Date
        * @param {object} utcMilliseconds contains UTC millisecond
        * @memberOf widgets/myList/myList
        */
        utcTimestampFromMs: function (utcMilliseconds) {
            return this.localToUtc(new Date(utcMilliseconds));
        },

        /**
        * Convert the local time to UTC
        * @param {object} localTimestamp contains Local time
        * @returns Date
        * @memberOf widgets/myList/myList
        */
        localToUtc: function (localTimestamp) {
            return new Date(localTimestamp.getTime());
        },

        /**
        * Draw route for event list items
        * @memberOf widgets/myList/myList
        */
        _drawRouteForListItem: function () {
            var eventListArrayList, q, sortResult;
            topic.publish("hideInfoWindow");
            this.isExtentSet = true;
            topic.publish("extentSetValue", true);
            topic.publish("showActivityPlannerContainer");
            appGlobals.shareOptions.eventRoutePoint = null;
            appGlobals.shareOptions.addressLocation = null;
            appGlobals.shareOptions.infowindowDirection = null;
            appGlobals.shareOptions.doQuery = "false";
            appGlobals.shareOptions.searchFacilityIndex = -1;
            appGlobals.shareOptions.addressLocationDirectionActivity = null;
            appGlobals.shareOptions.sharedGeolocation = null;
            appGlobals.shareOptions.infoRoutePoint = null;
            sortResult = this.sortDate(this.ascendingFlag);
            eventListArrayList = [];
            // Looping for getting feature set.
            for (q = 0; q < sortResult.length; q++) {
                eventListArrayList.push(sortResult[q].featureSet);
            }
            if (eventListArrayList.length > 0) {
                appGlobals.shareOptions.eventForListClicked = eventListArrayList.length;
                topic.publish("eventForListClick", eventListArrayList);
            } else {
                topic.publish("hideInfoWindow");
            }
        },

        /**
        * Check if field type is date
        * @param{object} layerObj - layer data
        * @param{string} fieldName - current field
        * @memberOf widgets/myList/myList
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
        * @memberOf widgets/myList/myList
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
        * @memberOf widgets/myList/myList
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
        * @memberOf widgets/myList/myList
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
        * @memberOf widgets/myList/myList
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
        * @memberOf widgets/myList/myList
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
        * this function is used to convert number to thousand separator
        * @memberOf widgets/mapSettings/mapSettings
        */
        convertNumberToThousandSeperator: function (number) {
            number = number.split(".");
            number[0] = number[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
            return number.join('.');
        },

        /**
        * Check width of device for scroll
        * @memberOf widgets/myList/myList
        */
        checkWidthForNexusDevice: function () {
            var headerMyListRight, myListContainerHeight, myListTableHeight;
            if (typeof (this.myListContainer) !== 'undefined' && typeof (this.myListTable) !== 'undefined') {
                headerMyListRight = query(".esriCTHeaderMyListRight")[0];
                myListContainerHeight = domStyle.get(this.myListContainer, "height");
                myListTableHeight = domStyle.get(this.myListTable, "height");
                if (win.getBox().w === 640 || win.getBox().w === 360) {
                    if (myListContainerHeight < myListTableHeight) {
                        if (!domClass.contains(headerMyListRight, "esriCTExtraPadding")) {
                            domClass.add(headerMyListRight, "esriCTExtraPadding");
                        }
                    } else {
                        if (domClass.contains(headerMyListRight, "esriCTExtraPadding")) {
                            domClass.remove(headerMyListRight, "esriCTExtraPadding");
                        }
                    }
                }
            }
        }
    });
});
