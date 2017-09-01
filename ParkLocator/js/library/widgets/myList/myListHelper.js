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
    "dojo/_base/lang",
    "dojo/dom-class",
    "dojo/_base/html",
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "dojo/date",
    "dojo/date/locale",
    "dojo/_base/array",
    "esri/tasks/query",
    "esri/tasks/QueryTask",
    "dojo/string",
    "dojo/query",
    "widgets/printForEvent/printForEventWindow",
    "dijit/a11yclick"
], function (declare, lang, domClass, html, domStyle, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, sharedNls, topic, date, locale, array, Query, QueryTask, string, query, PrintForEventWindow, a11yclick) {
    //========================================================================================================================//

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        sharedNls: sharedNls,                // Variable for shared NLS

        /**
        * Create myList helper file to store feature added from activity and event, User can search show route, add to calendar, find route for list, print for list.
        */

        /**
        * Query for event feature in share window with object id
        * @param {object} object of layer
        * @memberOf widgets/myList/myListHelper
        */
        _queryForEventShare: function (eventObjectId) {
            var queryTask, queryLayer, eventLayer;
            // Looping for event search for query on layer.
            array.forEach(appGlobals.configData.EventSearchSettings, (lang.hitch(this, function (eventSearchSettings) {
                eventLayer = eventSearchSettings.QueryURL;
                queryTask = new QueryTask(eventLayer);
                queryLayer = new Query();
                queryLayer.objectIds = [eventObjectId];
                queryLayer.outSpatialReference = this.map.spatialReference;
                queryLayer.returnGeometry = true;
                queryLayer.outFields = ["*"];
                topic.publish("showProgressIndicator");
                queryTask.execute(queryLayer, lang.hitch(this, this._eventResult, eventSearchSettings));
            })));
        },

        /**
        * Result of query for event in share window
        * @param {object} object of eventSearchSettings
        * @param {object} object of feature set
        * @memberOf widgets/myList/myListHelper
        */
        _eventResult: function (eventSearchSettings, featureSet) {
            var eventFeatureList = [], i, featureSetArray = [], eventDataObject, g, startDateAttribute, queryLayerId;
            // If feature set got from service call then remove null value and change if date field is found
            this.dateFieldArray = this.getDateField(featureSet);
            // Function for setting date attribute
            featureSet.features = this.setDateWithUTC(featureSet.features);
            if (featureSet) {
                featureSet.features = this._removeNullValue(featureSet.features);
                featureSet.features = this._formatedDataForShare(featureSet.features);
            }
            // If feature set got from service call
            if (featureSet) {
                this.featureSetOfInfoWindow = featureSet.features;
                appGlobals.shareOptions.eventInfoWindowData = this.featureSetOfInfoWindow[0].geometry;
                // Looping feature set for getting start date field and adding them in my list
                for (i = 0; i < featureSet.features.length; i++) {
                    startDateAttribute = eventSearchSettings.SortingKeyField ? this.getKeyValue(eventSearchSettings.SortingKeyField) : "";
                    queryLayerId = parseInt(eventSearchSettings.QueryLayerId, 10);
                    eventDataObject = { "eventDetails": featureSet.features[i].attributes, "featureSet": featureSet.features[i], "infoWindowClick": true, "layerId": queryLayerId, "layerTitle": eventSearchSettings.Title, "ObjectIDField": eventSearchSettings.ObjectID, "StartDateField": startDateAttribute };
                    eventFeatureList.push(eventDataObject[i]);
                    topic.publish("addtoMyListFunction", eventDataObject, "Event");
                }
                // Checking for route in shared link for calculating route
                if (window.location.toString().split("$infoRoutePoint=").length > 1) {
                    if (window.location.toString().split("$infoRoutePoint=")[1].split("$")[0]) {
                        for (g = 0; g < featureSet.features.length; g++) {
                            if (Number(window.location.toString().split("$infoRoutePoint=")[1].split("$")[0]) === featureSet.features[g].attributes[eventSearchSettings.ObjectID]) {
                                featureSetArray.push(featureSet.features[g]);
                                topic.publish("replaceApplicationHeaderContainer");
                                topic.publish("executeQueryForFeatures", featureSetArray, eventSearchSettings.QueryURL, "Event");
                            }
                            appGlobals.shareOptions.infoRoutePoint = Number(window.location.toString().split("$infoRoutePoint=")[1].split("$")[0]);
                        }
                    }
                }
            }
        },

        /**
        * Function to set UTC date format
        * @param {object} eventData contains the event data
        * @memberOf widgets/myList/myList
        */
        setDateWithUTC: function (eventData) {
            var utcDateObject = {}, startDateAttr, endDateAttr;
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings) {
                startDateAttr = this.getKeyValue(settings.AddToCalendarSettings[0].StartDate);
                endDateAttr = this.getKeyValue(settings.AddToCalendarSettings[0].EndDate);
            }));
            array.forEach(eventData, lang.hitch(this, function (event) {
                utcDateObject = {};
                utcDateObject[startDateAttr] = event.attributes[startDateAttr];
                utcDateObject[endDateAttr] = event.attributes[endDateAttr];
                event.utcDate = utcDateObject;
            }));
            return eventData;
        },

        /**
        * Change the format of date, number into coded domain value
        * @param {object} featureSet contains the feature data
        * @memberOf widgets/myList/myListHelper
        */
        _formatedDataForShare: function (featureSet) {
            var attributes, i, l, j, k, layerDetails, fieldValue, fieldName, fieldInfo, domainValue;
            for (k = 0; k < featureSet.length; k++) {
                attributes = featureSet[k].attributes;

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
                            } else {
                                // Check if field has coded values
                                fieldInfo = this.hasDomainCodedValue(fieldName, attributes, layerDetails.layerObject);
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
            }
            return featureSet;
        },

        /**
        * Query for activity in share window
        * @param {object} object of layer
        * @memberOf widgets/myList/myListHelper
        */
        _queryForActivityShare: function (activityObjectId) {
            var queryTask, queryLayer, activityLayer;
            // Looping for activity search for query on layer.
            array.forEach(appGlobals.configData.ActivitySearchSettings, (lang.hitch(this, function (ActivitySearchSettings) {
                activityLayer = ActivitySearchSettings.QueryURL;
                queryTask = new QueryTask(activityLayer);
                queryLayer = new Query();
                queryLayer.objectIds = [activityObjectId];
                queryLayer.outSpatialReference = this.map.spatialReference;
                queryLayer.returnGeometry = true;
                queryLayer.outFields = ["*"];
                queryTask.execute(queryLayer, lang.hitch(this, this._activityResult, ActivitySearchSettings));
            })));
        },

        /**
        * Result got after query for event in share window
        * @param {object} object of layer
        * @memberOf widgets/myList/myListHelper
        */
        _activityResult: function (activitySearchSettings, featureSet) {
            var activityFeatureList = [], i, featureSetArray = [], activityDataObject, g, queryLayerId;
            // If feature set is found then remove null value from feature and change date format
            this.dateFieldArray = this.getDateField(featureSet);
            if (featureSet) {
                featureSet.features = this._removeNullValue(featureSet.features);
                featureSet.features = this._formatedDataForShare(featureSet.features);
            }
            // If feature set is found
            if (featureSet) {
                this.featureSetOfInfoWindow = featureSet.features;
                appGlobals.shareOptions.eventInfoWindowData = this.featureSetOfInfoWindow[0].geometry;
                // Looping feature set for adding item in my List
                for (i = 0; i < featureSet.features.length; i++) {
                    queryLayerId = parseInt(appGlobals.configData.ActivitySearchSettings[0].QueryLayerId, 10);
                    activityDataObject = { "eventDetails": featureSet.features[i].attributes, "featureSet": featureSet.features[i], "infoWindowClick": true, "layerId": queryLayerId, "layerTitle": appGlobals.configData.ActivitySearchSettings[0].Title, "ObjectIDField": appGlobals.configData.ActivitySearchSettings[0].ObjectID, "StartDateField": "" };
                    activityFeatureList.push(activityDataObject[i]);
                    topic.publish("addtoMyListFunction", activityDataObject, "activitySearch");
                }
                // Checking in shared link to calculate route
                if (window.location.toString().split("$infoRoutePoint=").length > 1) {
                    if (window.location.toString().split("$infoRoutePoint=")[1].split("$")[0]) {
                        for (g = 0; g < featureSet.features.length; g++) {
                            if (Number(window.location.toString().split("$infoRoutePoint=")[1].split("$")[0]) === featureSet.features[g].attributes[appGlobals.configData.ActivitySearchSettings[0].ObjectID]) {
                                featureSetArray.push(featureSet.features[g]);
                                topic.publish("replaceApplicationHeaderContainer");
                                topic.publish("executeQueryForFeatures", featureSetArray, appGlobals.configData.ActivitySearchSettings[0].QueryURL, "activitySearch");
                            }
                        }
                    }
                }
            }
        },

        /**
        * Get date field key name from layer
        * @param {object} object of layer
        * @return {array} array of date field key name
        * @memberOf widgets/myList/myListHelper
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
        * Function get object id on the basic of layer id and layer title
        * @param {LayerId} layer id value
        * @param {LayerTitle} layer title value
        * @memberOf widgets/myList/myListHelper
        */
        getObjectIdFromSettings: function (LayerId, LayerTitle) {
            var objectID, queryLayerId;
            // Looping for getting object id from event search.
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings) {
                queryLayerId = Number(settings.QueryLayerId);
                if (queryLayerId === LayerId && settings.Title === LayerTitle) {
                    objectID = settings.ObjectID;
                }
            }));
            // Looping for getting object id from activity search.
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings) {
                queryLayerId = Number(settings.QueryLayerId);
                if (queryLayerId === LayerId && settings.Title === LayerTitle) {
                    objectID = settings.ObjectID;
                }
            }));
            return objectID;
        },

        /**
        * Function get object id on the basic of setting name
        * @param {LayerId} layer id value
        * @param {LayerTitle} layer title value
        * @memberOf widgets/myList/myListHelper
        */
        getObjectIdFromAddToList: function (featureData) {
            var objectID, searchSetting;
            // Checking for setting name for setting search setting and returning  object id field.
            if (featureData.settingsName === "eventsettings") {
                searchSetting = appGlobals.configData.EventSearchSettings[featureData.settingsIndex];
            } else if (featureData.settingsName === "activitysettings") {
                searchSetting = appGlobals.configData.ActivitySearchSettings[featureData.settingsIndex];
            }
            if (searchSetting) {
                objectID = searchSetting.ObjectID;
            }
            return objectID;
        },

        /**
        * Function get object id on the basic of setting name
        * @param {LayerId} layer id value
        * @param {LayerTitle} layer title value
        * @memberOf widgets/myList/myListHelper
        */
        getQueryUrl: function (LayerId, LayerTitle) {
            var queryURL, queryLayerId;
            // Looping for getting object id from event search.
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings) {
                queryLayerId = parseInt(settings.QueryLayerId, 10);
                if (queryLayerId === LayerId && settings.Title === LayerTitle) {
                    queryURL = settings.QueryURL;
                }
            }));
            // Looping for getting object id from activity search.
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings) {
                queryLayerId = parseInt(settings.QueryLayerId, 10);
                if (queryLayerId === LayerId && settings.Title === LayerTitle) {
                    queryURL = settings.QueryURL;
                }
            }));
            return queryURL;
        },

        /**
        * Function to generate ICS files in the case of multiple events
        * @memberOf widgets/myList/myListHelper
        */
        _showDataForCalendar: function () {
            var featureArray = [], sortResult, t, startDateFound, searchSetting, startDate, startDateAttr, endDateAttr,
                formatedStartDate, endDate, formatedEndDate, difference;
            sortResult = this.sortDate(this.ascendingFlag);
            searchSetting = appGlobals.configData.EventSearchSettings[0];
            startDateAttr = this.getKeyValue(searchSetting.AddToCalendarSettings[0].StartDate);
            endDateAttr = this.getKeyValue(searchSetting.AddToCalendarSettings[0].EndDate);
            for (t = 0; t < sortResult.length; t++) {
                startDateFound = false;
                // Looping for getting date field
                if (sortResult[t].settingsName === "eventsettings") {
                    startDate = sortResult[t].featureSet.utcDate[startDateAttr];
                    endDate = sortResult[t].featureSet.utcDate[endDateAttr];
                    if (startDate === null || endDate === null || startDate === appGlobals.configData.ShowNullValueAs || endDate === appGlobals.configData.ShowNullValueAs) {
                        startDateFound = false;
                    } else {
                        formatedStartDate = new Date(startDate);
                        formatedEndDate = new Date(endDate);
                        difference = date.difference(formatedStartDate, formatedEndDate, "day");
                        if (difference < 0) {
                            startDateFound = false;
                        } else {
                            startDateFound = true;
                        }
                    }
                }
                // If date field found then store sorted data for creating ICS file
                if (startDateFound) {
                    featureArray.push(sortResult[t]);
                }
            }
            if (sortResult && sortResult.length > 0) {
                this._createICSFile(featureArray);
            }
        },

        /*
        * Function to get a month from a date.
        * @memberOf widgets/myList/myListHelper
        */
        _getMonth: function (date) {
            var month = date.getMonth();
            month = month * 100 / 100 + 1;
            return month < 10 ? '0' + month : month; // ('' + month) for string result
        },

        /*
        * Function to get a month from a date.
        * @memberOf widgets/myList/myListHelper
        */
        _getDate: function (date) {
            date = date.getDate();
            return date < 10 ? '0' + date : date; // ('' + date) for string result
        },

        /**
        * Function to create ICS file for add to calendar button
        * @param {array} featureArray - featureDate is a require parameter which will contain attribute information from feature.
        * @memberOf widgets/myList/myListHelper
        */
        _createICSFile: function (featureArray) {
            var calStartDate, calEndDate, keyField, searchSetting, calLocation, addToCalendarSettingsItemArray = [], calSummary, startingDate, endingDate, calDescription, calOrganizer, startDateAttr, endDateAttr, summary, description, organizerAttr, addToCalendarSettings, index = 0, URI;
            // Loop through the feature data for getting feature data items
            this.URLString = "events=" + featureArray.length;
            array.forEach(featureArray, lang.hitch(this, function (featureDataItem) {
                if (featureDataItem.settingsName === "eventsettings") {
                    this.validEvent = true;
                    searchSetting = appGlobals.configData.EventSearchSettings[featureDataItem.eventSettingsIndex];
                    organizerAttr = searchSetting.AddToCalendarSettings[0].Organizer;
                    startDateAttr = this.getKeyValue(searchSetting.AddToCalendarSettings[0].StartDate);
                    endDateAttr = this.getKeyValue(searchSetting.AddToCalendarSettings[0].EndDate);
                    addToCalendarSettings = searchSetting.AddToCalendarSettings[0].Location.split(',');
                    addToCalendarSettingsItemArray.length = 0;
                    array.forEach(addToCalendarSettings, lang.hitch(this, function (addToCalendarSettingsItem) {
                        keyField = this.getKeyValue(addToCalendarSettingsItem);
                        if (featureDataItem.featureSet.attributes[keyField] && featureDataItem.featureSet.attributes[keyField] !== appGlobals.configData.ShowNullValueAs) {
                            featureDataItem.featureSet.attributes[keyField] = featureDataItem.featureSet.attributes[keyField].replace(/[#$&\/\.%]/g, " ");
                            addToCalendarSettingsItemArray.push(featureDataItem.featureSet.attributes[keyField]);
                        }
                    }));
                    summary = this.getKeyValue(searchSetting.AddToCalendarSettings[0].Summary);
                    description = this.getKeyValue(searchSetting.AddToCalendarSettings[0].Description);
                }
                calStartDate = locale.format(new Date(featureDataItem.featureSet.utcDate[startDateAttr]), { datePattern: "MMMM dd, yyyy", selector: "date", locale: "en-us" });
                calEndDate = locale.format(new Date(featureDataItem.featureSet.utcDate[endDateAttr]), { datePattern: "MMMM dd, yyyy", selector: "date", locale: "en-us" });
                calStartDate = new Date(calStartDate);
                calEndDate = new Date(calEndDate);
                startingDate = calStartDate.getFullYear().toString() + this._getMonth(calStartDate).toString() + this._getDate(calStartDate).toString() + "T020000Z";
                endingDate = calEndDate.getFullYear().toString() + this._getMonth(calEndDate).toString() + this._getDate(calEndDate).toString() + "T100000Z";
                calSummary = featureDataItem.featureSet.attributes[summary];
                if (featureDataItem.featureSet.attributes[description]) {
                    calDescription = encodeURI(featureDataItem.featureSet.attributes[description].replace(/[#$&\/\.%]/g, " "));
                }
                calLocation = encodeURI(addToCalendarSettingsItemArray.join(","));
                calOrganizer = organizerAttr;
                // Open Ics file for add to calendar if it is a valid event
                // Shortening url by using abbreviations for attributes like
                // sd = Start Date
                // ed = End Date
                // sum = Summary
                // des = Description
                // org = Organizer
                // fn = Filename
                // loc = Location
                this.URLString += "&sd" + index + "=" + startingDate.toString() + "&ed" + index + "=" + endingDate.toString() + "&sum" + index + "=" + calSummary + "&des" + index + "=" + calDescription + "&org" + index + "=" + calOrganizer + "&fn" + index + "=" + calSummary + "&loc" + index + "=" + calLocation;
                index++;
                this.featureLength = index;
            }));
            // Check whether the url string is not empty.
            if (this.URLString !== "") {
                this.URLString = dojoConfig.baseURL + "/js/library/widgets/myList/ICalendar.ashx?" + this.URLString;
                URI = encodeURI(this.URLString);
                // If feature count is only 1 and uri length is exceeding 2048, then setting description to null
                if (this.featureLength === 1 && URI && URI.length > 2048) {
                    // Setting index = 0 for a single event.
                    index = 0;
                    // Create url string without description.
                    this.URLString = dojoConfig.baseURL + "/js/library/widgets/myList/ICalendar.ashx?" + "&sd" + index + "=" + startingDate.toString() + "&ed" + index + "=" + endingDate.toString() + "&sum" + index + "=" + calSummary + "&org" + index + "=" + calOrganizer + "&fn" + index + "=" + calSummary + "&loc" + index + "=" + calLocation + "&des" + index + "= " + "&events=" + this.featureLength;
                    URI = encodeURI(this.URLString);
                    // Check uri (with description = null) not exceeding than 2048, else show alert.
                    if (URI.length < 2048) {
                        window.open(this.URLString, "_blank");
                    } else {
                        alert(sharedNls.errorMessages.unableAddEventToCalendar);
                    }
                } else {
                    // If feature count is greater than 1, check encode uri length exeeds than 2048, else show alert.
                    if (URI.length < 2048) {
                        window.open(this.URLString, "_blank");
                    } else {
                        alert(sharedNls.errorMessages.unableAddEventToCalendarList);
                    }
                }
            }
        },

        /**
        * replace class for my list container
        * @memberOf widgets/myList/myListHelper
        */
        _replaceClassForMyList: function () {
            if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                domClass.replace(this.directionForEvents, "esriCTHeaderDirectionAcitivityList", "esriCTHeaderDirectionAcitivityListDisable");
            }
            domClass.replace(this.calenderForEvent, "esriCTHeaderAddAcitivityList", "esriCTHeaderAddAcitivityListDisable");
            domClass.replace(this.printEventList, "esriCTHeaderPrintAcitivityList", "esriCTHeaderPrintAcitivityListDisable");
            if (this.myListStore.length > 1) {
                domClass.replace(this.orderByDateList, "esriCTMyListHeaderText", "esriCTMyListHeaderTextDisable");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDown", "esriCTImgOrderByDateDownDisable");
                domClass.replace(this.orderByDateImage, "esriCTImgOrderByDate", "esriCTImgOrderByDateDisable");
            }
        },

        /**
        * Sort my list events in ascending or descending order
        * @param string ascendingFlag contains boolean flag for ascending value
        * @param {featureSet} contains the feature set data
        * @memberOf widgets/myList/myListHelper
        */
        _sortMyList: function (ascendingFlag, featureSet) {
            var sortResult;
            this.ascendingFlag = ascendingFlag;
            appGlobals.shareOptions.eventOrderInMyList = ascendingFlag.toString() + "," + this.myListStore.length;
            topic.publish("getMyListStoreData", this.myListStore);
            // Checking for order for data
            if (ascendingFlag) {
                if (this.myListStore.length > 1) {
                    domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateDown", "esriCTImgOrderByDateUp");
                }
                sortResult = this.sortDate(ascendingFlag);
            } else {
                if (this.myListStore.length > 1) {
                    domClass.replace(this.orderByDateImage, "esriCTImgOrderByDateUp", "esriCTImgOrderByDateDown");
                }
                sortResult = this.sortDate(ascendingFlag);
            }
            return sortResult;
        },

        /**
        * Sort date by order
        * @param string startDate contains date attribute
        * @param string ascendingFlag contains boolean flag for ascending value
        * @memberOf widgets/myList/myListHelper
        */
        sortDate: function (ascendingFlag) {
            var sortResult = [], searchSettingA, searchSettingB, sortedEventData = [], sortedActivityData = [], t, startDateFound, p, q, sortedDateArray, nameFieldA, nameFieldB;
            // Checking for order of data and sorting.
            if (ascendingFlag) {
                sortResult = this.myListStore.sort(lang.hitch(this, function (a, b) {
                    if (b.value[b.startDateField] && a.value[a.startDateField]) {
                        sortedDateArray = new Date(b.value[b.startDateField]).getTime() - new Date(a.value[a.startDateField]).getTime();
                    } else {
                        sortedDateArray = 1;
                    }
                    return sortedDateArray;
                }));
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
                if (sortResult[t].settingsName === "eventsettings" && sortResult[t].value[sortResult[t].startDateField] !== appGlobals.configData.ShowNullValueAs) {
                    startDateFound = true;
                }
                if (startDateFound) {
                    sortedEventData.push(sortResult[t]);
                } else {
                    sortedActivityData.push(sortResult[t]);
                }
            }
            // Sorting the activity from name
            sortedActivityData = sortedActivityData.sort(function (a, b) {
                if (a.settingsName === "eventsettings") {
                    searchSettingA = appGlobals.configData.EventSearchSettings[a.eventSettingsIndex];
                } else {
                    searchSettingA = appGlobals.configData.ActivitySearchSettings[a.eventSettingsIndex];
                }
                if (b.settingsName === "eventsettings") {
                    searchSettingB = appGlobals.configData.EventSearchSettings[b.eventSettingsIndex];
                } else {
                    searchSettingB = appGlobals.configData.ActivitySearchSettings[b.eventSettingsIndex];
                }
                nameFieldA = string.substitute(searchSettingA.SearchDisplayFields, a.value).toLowerCase();
                nameFieldB = string.substitute(searchSettingB.SearchDisplayFields, b.value).toLowerCase();
                if (nameFieldA < nameFieldB) { //sort string ascending
                    return -1;
                }
                if (nameFieldA > nameFieldB) {
                    return 1;
                }
                return 0; //default return value (no sorting)
            });
            sortResult.length = 0;
            for (p = 0; p < sortedEventData.length; p++) {
                sortResult.push(sortedEventData[p]);
            }
            for (q = 0; q < sortedActivityData.length; q++) {
                sortResult.push(sortedActivityData[q]);
            }
            return sortResult;
        },

        /**
        * Get the key field value from the config file
        * @param {data} keyField value with $ sign
        * @memberOf widgets/myList/myListHelper
        */
        getKeyValue: function (data) {
            if (data) {
                var firstPlace, secondPlace, keyValue;
                firstPlace = data.indexOf("{");
                secondPlace = data.indexOf("}");
                keyValue = data.substring(Number(firstPlace) + 1, secondPlace);
                return keyValue;
            }
        },

        /**
        * Show eventPlanner tab and block the Mylist tab
        * @memberOf widgets/myList/myListHelper
        */
        _showActivityTab: function () {
            domStyle.set(this.activityList, "display", "block");
            domClass.replace(this.activityListTab, "esriCTEventListTabSelected", "esriCTEventListTab");
        },

        /**
        * Setting extent for geometry on center
        * @memberOf widgets/myList/myListHelper
        */
        setZoomAndCenterAt: function (geometry) {
            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                if (this.isExtentSet) {
                    this.map.centerAndZoom(geometry, appGlobals.configData.ZoomLevel);
                }
            } else {
                this.map.centerAndZoom(geometry, appGlobals.configData.ZoomLevel);
            }
        },

        /**
        * Print event List data
        * @memberOf widgets/myList/myListHelper
        */
        _printForEventList: function () {
            var directionData, finalText, activityNameField, nameField, isDataFound, splitedField, sortResult, eventDataArray = [], l, searchSetting;
            sortResult = this.sortDate(this.ascendingFlag);
            // Looping for sort data for getting date field.
            for (l = 0; l < sortResult.length; l++) {
                isDataFound = false;
                if (sortResult[l].settingsName === "eventsettings") {
                    isDataFound = true;
                }
                // Checking if data is coming from event feature.
                if (sortResult[l].settingsName === "eventsettings") {
                    searchSetting = appGlobals.configData.EventSearchSettings;
                    nameField = this.getKeyValue(searchSetting[sortResult[l].eventSettingsIndex].SearchDisplayFields);

                    // Checking if my list item's search setting name for getting value from config.
                    splitedField = searchSetting[sortResult[l].eventSettingsIndex].SearchDisplaySubFields ? searchSetting[sortResult[l].eventSettingsIndex].SearchDisplaySubFields.split(',') : "";
                    finalText = this._getPrintWindowText(splitedField, sortResult[l]);
                } else {
                    searchSetting = appGlobals.configData.ActivitySearchSettings;
                    activityNameField = this.getKeyValue(searchSetting[sortResult[l].eventSettingsIndex].SearchDisplayFields);
                }
                // If date found then set address and start date field.
                if (isDataFound) {
                    directionData = {
                        "Name": sortResult[l].value[nameField] === "" ? appGlobals.configData.ShowNullValueAs : sortResult[l].value[nameField],
                        "BottomText": finalText,
                        "Title": sharedNls.titles.printWindowListTitleText
                    };
                } else {
                    directionData = {
                        "Name": sortResult[l].value[activityNameField] === "" ? appGlobals.configData.ShowNullValueAs : sortResult[l].value[activityNameField],
                        "BottomText": "",
                        "Title": sharedNls.titles.printWindowListTitleText
                    };
                }
                eventDataArray.push(directionData);
            }
            // If event data has value then show print for list.
            if (eventDataArray.length > 0) {
                this.printForEventList = new PrintForEventWindow({ "eventListData": eventDataArray });
            }
        },

        _getPrintWindowText: function (splitedField, sortResult) {
            var finalText = "", fieldName, fieldValue;
            array.forEach(splitedField, lang.hitch(this, function (splitedFieldValue) {
                fieldName = this.getKeyValue(splitedFieldValue);
                fieldValue = sortResult.value[fieldName] !== appGlobals.configData.ShowNullValueAs ? sortResult.value[fieldName] : "";
                finalText = finalText === "" ? fieldValue : finalText + ", " + fieldValue;
            }));
            return finalText;
        },

        /**
        * Remove null value from the attribute.
        * @param {object} featureObject is object for feature
        * @return {object} feature set after removing null value
        * @memberOf widgets/myList/myListHelper
        */
        _removeNullValue: function (featureObject) {
            var i, j;
            if (featureObject) {
                for (i = 0; i < featureObject.length; i++) {
                    for (j in featureObject[i].attributes) {
                        if (featureObject[i].attributes.hasOwnProperty(j)) {
                            if (!featureObject[i].attributes[j]) {
                                featureObject[i].attributes[j] = appGlobals.configData.ShowNullValueAs;
                            }
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
        * Get the feature within buffer and sort it in ascending order.
        * @param {object} featureset Contains information of feature within buffer
        * @param {object} geometry Contains geometry service of route
        * @param {mapPoint} map point
        * @memberOf widgets/myList/myListHelper
        */
        _executeQueryForEventForList: function (featureSetObject) {
            var isZoomToGeolocation;
            this.featureSetWithoutNullValue = this._removeNullValue(featureSetObject);
            isZoomToGeolocation = this.setZoomForGeolocation();
            // If browser is supporting geolocation then proceed else show error message.
            if (Modernizr.geolocation) {
                // If geolocation widget is configured
                if (dijit.registry.byId("geoLocation")) {
                    // Call show current location.
                    dijit.registry.byId("geoLocation").showCurrentLocation(false, isZoomToGeolocation);
                    // Call back of geolocation complete
                    dijit.registry.byId("geoLocation").onGeolocationComplete = lang.hitch(this, function (mapPoint, isPreLoaded) {
                        // If mappoint is found then clean graphics
                        if (mapPoint) {
                            // If it is not coming from geolocation widget
                            if (!isPreLoaded) {
                                var routeObject = { "StartPoint": mapPoint, "EndPoint": this.featureSetWithoutNullValue, WidgetName: "routeForList" };
                                topic.publish("routeForListFunction", routeObject);
                            } else {
                                // if it is coming from geolocation widget
                                topic.publish("hideProgressIndicator");
                                topic.publish("extentSetValue", true);
                                topic.publish("hideInfoWindow");
                                appGlobals.shareOptions.eventForListClicked = null;
                                topic.publish("removeRouteGraphichOfDirectionWidget");
                                appGlobals.shareOptions.searchFacilityIndex = -1;
                                topic.publish("createBuffer", mapPoint, "geolocation");
                                appGlobals.shareOptions.addressLocation = null;
                                appGlobals.shareOptions.sharedGeolocation = mapPoint;
                            }
                        }
                    });
                } else {
                    // Shown when the browser returns an error instead of the current geographical position
                    topic.publish("hideProgressIndicator");
                    alert(sharedNls.errorMessages.geolocationWidgetNotFoundMessage);
                }
                if (dijit.registry.byId("geoLocation")) {
                    // Call back when error is found after geolocation
                    dijit.registry.byId("geoLocation").onGeolocationError = lang.hitch(this, function (error, isPreLoaded) {
                        if (isPreLoaded) {
                            topic.publish("extentSetValue", true);
                            topic.publish("hideInfoWindow");
                            topic.publish("removeHighlightedCircleGraphics");
                            topic.publish("removeLocatorPushPin");
                            topic.publish("removeBuffer");
                            if (this.carouselContainer) {
                                this.carouselContainer.hideCarouselContainer();
                                this.carouselContainer._setLegendPositionDown();
                            }
                        }
                        // If it is not coming from geolocation
                        if (!isPreLoaded) {
                            topic.publish("removeLocatorPushPin");
                            topic.publish("hideInfoWindow");
                            topic.publish("hideProgressIndicator");
                        }
                    });
                }
            } else {
                // Calling error message when geoloation widget is not configured.
                topic.publish("hideProgressIndicator");
                alert(sharedNls.errorMessages.activitySearchGeolocationText);
            }
        },

        /**
        * Setting value to change for extent
        * @memberOf widgets/myList/myListHelper
        */
        setZoomForGeolocation: function () {
            var isZoomToLocation = false;
            // checking if application in share url, If it is a share url then do not set extent, else set extent
            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                // checking if application in share url, If it is a share url then do not set extent, else set extent
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
