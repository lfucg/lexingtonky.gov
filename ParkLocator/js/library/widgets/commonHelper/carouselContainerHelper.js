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
    "dojo/window",
    "dojo/dom",
    "dojo/dom-class",
    "dojo/query",
    "dojo/string",
    "dijit/_WidgetBase",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "dijit/a11yclick",
    "dojo/_base/array",
    "widgets/carouselContainer/carouselContainer"

], function (declare, domConstruct, domStyle, domAttr, lang, on, win, dom, domClass, query, string, _WidgetBase, sharedNls, topic, a11yclick, array, CarouselContainer) {
    // ========================================================================================================================//

    return declare([_WidgetBase], {
        sharedNls: sharedNls, // Variable for shared NLS

        /**
        * This file creates carousel container with pods for showing results
        * Search panel : Showing all searched features
        * Informaitional panel: showing information about the feature
        * Galary panel : Showing image if layer has attachment
        * Comment panel : Showing comments after querying on comment layer
        *
        */


        /**
        * create carousel Container
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        _createCarouselContainer: function () {
            this.carouselContainer = new CarouselContainer();
            this.carouselContainer.createPod(dom.byId("esriCTParentDivContainer"), appGlobals.configData.BottomPanelToggleButtonText);
        },

        /**
        * create carousel pod and set it content
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        createCarouselPod: function () {
            var divCarouselPod, divGallerycontent, divPodInfoContainer, divcommentcontent, divHeader, divsearchcontent, i, key, carouselPodKey;
            // Looping for pod settings array from config file for getting value
            for (i = 0; i < appGlobals.configData.PodSettings.length; i++) {
                // Getting key from pod settings
                for (key in appGlobals.configData.PodSettings[i]) {
                    if (appGlobals.configData.PodSettings[i].hasOwnProperty(key)) {
                        // If in config pod settings is enabled then set carousel pod key name to key matched
                        if (appGlobals.configData.PodSettings && appGlobals.configData.PodSettings[i][key].Enabled) {
                            divCarouselPod = domConstruct.create("div", { "class": "esriCTBoxContainer" });
                            divPodInfoContainer = domConstruct.create("div", { "class": "esriCTInfoContainer" }, divCarouselPod);
                            carouselPodKey = key;
                            // If comment settings is enabled then only set comment pod visible to user.
                            if (!appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.Enabled) {
                                if (carouselPodKey.toLowerCase() === "commentspod") {
                                    carouselPodKey = "default";
                                }
                            }
                            if (!appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                                if (appGlobals.configData.PodSettings[i].DirectionsPod) {
                                    carouselPodKey = "default";
                                }
                            }
                        } else {
                            // In else scenario set key to default.
                            carouselPodKey = "default";
                        }
                        // Switch for carouse pod key for creating div
                        switch (carouselPodKey.toLowerCase()) {
                        // If it is a search pod 
                        case "searchresultpod":
                            domAttr.set(divCarouselPod, "id", "esriCTSearchResultPod");
                            divHeader = domConstruct.create("div", { "class": "esriCTDivHeadercontainer" }, divPodInfoContainer);
                            domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": sharedNls.titles.searchResultText }, divHeader);
                            divsearchcontent = domConstruct.create("div", { "class": "esriCTResultContent" }, divHeader);
                            domConstruct.create("div", { "class": "esriCTDivSearchResulContent" }, divsearchcontent);
                            break;
                        case "facilityinformationpod":
                            domAttr.set(divCarouselPod, "id", "esriCTFacilityInformationPod");
                            // If it is a facility pod
                            this.facilityContainer = domConstruct.create("div", { "class": "esriCTDivHeadercontainer" }, divPodInfoContainer);
                            this.divfacilitycontent = domConstruct.create("div", {}, this.facilityContainer);
                            domConstruct.create("div", { "class": "esriCTdivFacilityContent" }, this.divfacilitycontent);
                            break;
                        case "directionspod":
                            domAttr.set(divCarouselPod, "id", "esriCTDirectionsPod");
                            // If it is a direction pod
                            if (appGlobals.configData.DrivingDirectionSettings.GetDirections) {
                                this.directionContainer = domConstruct.create("div", { "class": "esriCTDivHeadercontainer" }, divPodInfoContainer);
                                domConstruct.create("div", { "class": "esriCTDivDirectioncontent" }, this.directionContainer);
                            }
                            break;
                        case "gallerypod":
                            domAttr.set(divCarouselPod, "id", "esriCTGalleryPod");
                            // If it is a gallery pod
                            divHeader = domConstruct.create("div", { "class": "esriCTDivHeadercontainer" }, divPodInfoContainer);
                            domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": sharedNls.titles.galleryText }, divHeader);
                            divGallerycontent = domConstruct.create("div", { "class": "esriCTResultContent" }, divHeader);
                            domConstruct.create("div", { "class": "esriCTDivGalleryContent" }, divGallerycontent);
                            break;
                        case "commentspod":
                            domAttr.set(divCarouselPod, "id", "esriCTCommentsPod");
                            // If it is a comment pod
                            divHeader = domConstruct.create("div", { "class": "esriCTDivHeadercontainer" }, divPodInfoContainer);
                            domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": sharedNls.titles.commentText }, divHeader);
                            divcommentcontent = domConstruct.create("div", { "class": "esriCTResultContent" }, divHeader);
                            domConstruct.create("div", { "class": "esriCTDivCommentContent" }, divcommentcontent);
                            break;
                        case "default":
                            // If default then break
                            break;
                        }
                        // If it is not default then create carousel pod data
                        if (carouselPodKey.toLowerCase() !== "default") {
                            domAttr.set(divCarouselPod, "CarouselPodName", carouselPodKey);
                            this.carouselPodData.push(divCarouselPod);
                        }
                    }
                }
            }
        },

        /**
        * Set the content in (Search result) carousel pod
        * @param {object} result contains features
        * @param {boolean} isBufferNeeded contains boolean value for buffer creation
        * @param {object} queryURL contains Layer URL
        * @param {string} widgetName contains name of widgets
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        setSearchContent: function (result, isBufferNeeded, queryURL, widgetName, activityData) {
            var isPodEnabled = this.getPodStatus("SearchResultPod"), subStringRouteUnit, searchContenTitle, searchedFacilityObject, divHeaderContent, resultcontent = [], milesCalulatedData, searchContenData, g, l, serchSetting;
            // Checking for pod enable status in config file
            if (isPodEnabled) {
                // If it is coming from unified search and geolocation then set search content title and search contend Data
                if (widgetName.toLowerCase() === "unifiedsearch" || widgetName.toLowerCase() === "geolocation") {
                    searchContenTitle = sharedNls.titles.numberOfFeaturesFoundNearAddress;
                } else if (widgetName.toLowerCase() === "activitysearch") {
                    // If it is coming from activity search and geolocation then set search content title and search content Data
                    searchContenTitle = sharedNls.titles.numberOfFeaturesFound;
                } else if (widgetName.toLowerCase() === "event") {
                    // If it is coming from event search and geolocation then set search content title and search contend Data
                    searchContenTitle = sharedNls.titles.numberOfEventsFound;
                }
                // Function for getting search settings from queryURL.
                serchSetting = this.getSearchSetting(queryURL);
                searchContenData = this.getKeyValue(serchSetting.SearchDisplayFields);
                divHeaderContent = query('.esriCTDivSearchResulContent');
                // If DIV has data then remove all childs element
                if (divHeaderContent.length > 0) {
                    domConstruct.empty(divHeaderContent[0]);
                }
                this.spanFeatureListContainer = domConstruct.create("div", { "class": "esriCTSpanFeatureListContainer", "innerHTML": string.substitute(searchContenTitle, [result.length]) }, divHeaderContent[0]);
                // Looping through the results for showing data in pod
                array.forEach(result, lang.hitch(this, function (resultData, i) {
                    if (!isBufferNeeded && result[i].distance) {
                        subStringRouteUnit = this._getSubStringUnitData();
                        var dist = isNaN(result[i].distance) ? 0 : result[i].distance;
                        milesCalulatedData = " (" + parseFloat(dist).toFixed(2) + subStringRouteUnit + sharedNls.showApproxString + ")";
                    } else {
                        milesCalulatedData = "";
                    }
                    // If it is not coming from event layer
                    if (widgetName.toLowerCase() !== "event") {
                        // If result length is greater than 1
                        resultcontent[i] = domConstruct.create("div", { "class": "esriCTSearchResultInfotext" }, divHeaderContent[0]);
                        if (result.length > 1) {
                            // If it is coming from unified search and geolocation
                            if (widgetName.toLowerCase() === "unifiedsearch" || widgetName.toLowerCase() === "geolocation") {
                                if (activityData) {
                                    // Looping for activity data fetched from unified search
                                    for (g = 0; g < activityData.length; g++) {
                                        // Looping for features
                                        for (l = 0; l < activityData[g].records.features.length; l++) {
                                            // checking for distnace attr for getting curren value for unified search data
                                            if (activityData[g].records.features[l].distance === result[i].distance) {
                                                domAttr.set(resultcontent[i], "QueryURL", activityData[g].queryURL);
                                                serchSetting = this.getSearchSetting(activityData[g].queryURL);
                                                searchContenData = this.getKeyValue(serchSetting.SearchDisplayFields);
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Setting attribute of query url for further query
                                domAttr.set(resultcontent[i], "QueryURL", queryURL);
                            }
                            resultcontent[i].innerHTML = result[i].attributes[searchContenData] + milesCalulatedData;
                            domAttr.set(resultcontent[i], "value", i);
                            searchedFacilityObject = { "FeatureData": result, "SelectedRow": resultcontent[i], "IsBufferNeeded": isBufferNeeded, "QueryLayer": queryURL, "WidgetName": widgetName, "searchedFacilityIndex": i, "activityData": activityData };
                            this.own(on(resultcontent[i], a11yclick, lang.hitch(this, function (event) {
                                topic.publish("extentSetValue", true);
                                this._clickOnSearchedFacility(searchedFacilityObject, event);
                            })));
                            // If it is coming from share url then show data in search pod
                            if (window.location.href.toString().split("$selectedSearchResult=").length > 1 && Number(window.location.href.toString().split("$selectedSearchResult=")[1].split("$")[0]) === i) {
                                // Checking when it is not a shared link
                                if (this.isExtentSet === true) {
                                    return;
                                }
                                queryURL = domAttr.get(resultcontent[i], "QueryURL");
                                searchedFacilityObject = { "FeatureData": result, "SelectedRow": resultcontent[i], "IsBufferNeeded": isBufferNeeded, "QueryLayer": queryURL, "WidgetName": widgetName, "searchedFacilityIndex": i, "activityData": activityData };
                                domClass.add(resultcontent[i], "esriCTDivHighlightFacility");
                                this._clickOnSearchedFacility(searchedFacilityObject, null);
                                this.isFirstSearchResult = true;
                            } else if (query('.esriCTDivHighlightFacility').length < 1 && !this.isFirstSearchResult) {
                                domClass.add(resultcontent[0], "esriCTDivHighlightFacility");
                            }
                        } else {
                            // If it is comming from unified search and geolocation then loop through the activity data
                            if (widgetName.toLowerCase() === "unifiedsearch" || widgetName.toLowerCase() === "geolocation") {
                                // Looping for activity data for getting query URL on the basis of direction
                                if (activityData) {
                                    for (g = 0; g < activityData.length; g++) {
                                        // Looping for features
                                        for (l = 0; l < activityData[g].records.features.length; l++) {
                                            if (activityData[g].records.features[l].distance === result[i].distance) {
                                                domAttr.set(resultcontent[i], "QueryURL", activityData[g].queryURL);
                                                serchSetting = this.getSearchSetting(activityData[g].queryURL);
                                                searchContenData = this.getKeyValue(serchSetting.SearchDisplayFields);
                                            }
                                        }
                                    }
                                }
                            }
                            resultcontent[i] = domConstruct.create("div", { "class": "esriCTSearchResultInfotextForEvent", "innerHTML": result[i].attributes[searchContenData] + milesCalulatedData }, divHeaderContent[0]);
                        }
                    } else {
                        resultcontent[i] = domConstruct.create("div", { "class": "esriCTSearchResultInfotextForEvent", "innerHTML": result[i].attributes[searchContenData] + milesCalulatedData }, divHeaderContent[0]);
                    }
                }));
                if (win.getBox().w <= 766) {
                    topic.publish("resizeLegendContainer");
                }
            }
        },

        /**
        * Call all the function when click on search result data
        * @param {object} searchedFacilityObject contains route result, features in buffer area, search address,mapPoint, comment layer info
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        _clickOnSearchedFacility: function (searchedFacilityObject, event) {
            var pushpinGeometry, widgetName, routeObject, queryObject, highlightedDiv, queryURL, rowIndex;
            this.featureSetWithoutNullValue = searchedFacilityObject.FeatureData;
            topic.publish("hideInfoWindow");
            /* Collapse Carousel in smartphones */
            if (win.getBox().w <= 766) {
                // if any feature is clicked in smartphone's search results then collapse down the carousel result pod
                topic.publish("collapseCarousel");
                topic.publish("resizeLegendContainer");
            }
            this.zoomToFullRoute = true; /* Setting zoomToFullRoute to true for Github issue #182 */
            // If feature data has some items
            if (searchedFacilityObject.FeatureData.length > 1) {
                // If event is available then get query URL and row index from event.
                if (event !== null) {
                    queryURL = domAttr.get(event.currentTarget, "QueryURL");
                    rowIndex = domAttr.get(event.currentTarget, "value");
                }
                // If event is not available then get tuery URL and row index from event.
                if (event === null) {
                    queryURL = searchedFacilityObject.QueryLayer;
                    rowIndex = searchedFacilityObject.searchedFacilityIndex;
                }
                // Query for getting highlighted div
                highlightedDiv = query('.esriCTDivHighlightFacility')[0];
                if (highlightedDiv) {
                    domClass.replace(highlightedDiv, "esriCTSearchResultInfotext", "esriCTDivHighlightFacility");
                }
                // If event is not available then change the selected row.
                if (event !== null) {
                    domClass.replace(event.currentTarget, "esriCTDivHighlightFacility", "esriCTSearchResultInfotext");
                }
                appGlobals.shareOptions.searchFacilityIndex = Number(rowIndex);
                // If it is coming from activity search pod then remove buffer layer.
                if (searchedFacilityObject.WidgetName.toLowerCase() === "activitysearch") {
                    this.removeBuffer();
                }
                widgetName = "SearchedFacility";
                topic.publish("showProgressIndicator");
                this.removeHighlightedCircleGraphics();
                // Check if any graphic is present on map, related to geolocation settings or locator settings
                if (this.map.getLayer(this.geoLocationGraphicsLayerID) && this.map.getLayer(this.geoLocationGraphicsLayerID).graphics.length > 0) {
                    pushpinGeometry = this.map.getLayer(this.geoLocationGraphicsLayerID).graphics;
                } else if (this.map.getLayer(this.locatorGraphicsLayerID).graphics.length > 0) {
                    pushpinGeometry = this.map.getLayer(this.locatorGraphicsLayerID).graphics;
                } else {
                    pushpinGeometry = [this.selectedGraphic];
                }
                // If graphic is present on map, execute show route function.
                if (pushpinGeometry[0]) {
                    routeObject = { "StartPoint": pushpinGeometry[0], "EndPoint": searchedFacilityObject.FeatureData, "Index": Number(rowIndex), "WidgetName": widgetName, "QueryURL": queryURL, "activityData": searchedFacilityObject.activityData };
                    this.showRoute(routeObject);
                } else if (this.selectedGraphic) {
                    routeObject = { "StartPoint": this.selectedGraphic, "EndPoint": searchedFacilityObject.FeatureData, "Index": Number(rowIndex), "WidgetName": widgetName, "QueryURL": queryURL, "activityData": searchedFacilityObject.activityData };
                    this.showRoute(routeObject);
                } else {
                    // Else call query comment layer.
                    queryObject = { "FeatureData": searchedFacilityObject.FeatureData, "SolveRoute": null, "Index": Number(rowIndex), "QueryURL": queryURL, "WidgetName": widgetName, "Address": null, "IsRouteCreated": false };
                    topic.publish("showProgressIndicator");
                    this.queryCommentLayer(queryObject);
                }
            }
        },

        /**
        * set the content in (Facility) carousel pod if user click on search result data
        * @param {object} facilityObject contains 6, widget name, selected facility, Layer URL
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        setFacility: function (facilityObject) {
            var divHeaderContent, searchSettingName, layerId, layerTitle, infoPodAddtoList, isEventSearched = false, infowWindowData, divHeader, facilityDiv, divFacilityContainer, divFacilityContent, k, j, m, p, activityImageDiv, SearchSettingsLayers, isPodEnabled, divFacilityImages,
                _self = this, listData, isAlreadyAdded, objectIDField, serchSetting, queryLayerId, descriptionValue, infoTitle, operationLayer, layerdetails, contentDiv, descriptionResult, fieldValue, fieldName, fieldInfo, domainValue, formatedDataField;
            isPodEnabled = this.getPodStatus("FacilityInformationPod");

            // If pod is enabled
            if (isPodEnabled) {
                divHeaderContent = query('.esriCTdivFacilityContent');
                // If div is created
                if (divHeaderContent.length > 0) {
                    domConstruct.empty(divHeaderContent[0]);
                }
                divHeader = domConstruct.create("div", {}, divHeaderContent[0]);
                serchSetting = this.getSearchSetting(facilityObject.QueryURL);
                layerId = serchSetting.QueryLayerId;
                layerTitle = serchSetting.Title;
                facilityObject.Feature = this.removeNullValue(facilityObject.Feature);
                // Looping for operational data for getting info window settings from data
                for (p = 0; p < appGlobals.operationLayerSettings.length; p++) {
                    if (facilityObject.QueryURL === appGlobals.operationLayerSettings[p].layerURL) {
                        operationLayer = appGlobals.operationLayerSettings[p];
                        if (appGlobals.operationLayerSettings[p].layerDetails) {
                            layerdetails = appGlobals.operationLayerSettings[p].layerDetails;
                            if (appGlobals.operationLayerSettings[p].infoWindowData) {
                                infowWindowData = appGlobals.operationLayerSettings[p].infoWindowData.infoWindowfields;
                                if (appGlobals.operationLayerSettings[p].layerDetails && appGlobals.operationLayerSettings[p].layerDetails.popupInfo && appGlobals.operationLayerSettings[p].layerDetails.popupInfo.description) {
                                    descriptionValue = appGlobals.operationLayerSettings[p].layerDetails;
                                }
                            }
                            if (appGlobals.operationLayerSettings[p].activitySearchSettings) {
                                if (facilityObject.QueryURL !== appGlobals.operationLayerSettings[p].activitySearchSettings.QueryURL) {
                                    isEventSearched = true;
                                }
                            }
                        }
                    }
                }
                // If facility object has feature
                if (facilityObject.SelectedItem && facilityObject.Feature) {
                    try {
                        infoTitle = this.popUpTitleDetails(facilityObject.Feature[facilityObject.SelectedItem.value].attributes, layerdetails);
                    } catch (ex) {
                        infoTitle = appGlobals.configData.ShowNullValueAs;
                    }
                    domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": infoTitle }, divHeader);
                    if (dijit.registry.byId("myList")) {
                        infoPodAddtoList = domConstruct.create("div", { "class": "esriCTCarouselAddToListDiv", "title": sharedNls.tooltips.addToListTooltip }, divHeader);
                        domAttr.set(infoPodAddtoList, "LayerId", layerId);
                        domAttr.set(infoPodAddtoList, "LayerTitle", layerTitle);
                        // On click for add to list item
                        this.own(on(infoPodAddtoList, a11yclick, function (event) {
                            layerId = parseInt(domAttr.get(event.currentTarget, "LayerId"), 10);
                            layerTitle = domAttr.get(event.currentTarget, "LayerTitle");
                            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                                queryLayerId = Number(settings.QueryLayerId);
                                if (queryLayerId === layerId && settings.Title === layerTitle) {
                                    objectIDField = settings.ObjectID;
                                    searchSettingName = "eventsetting";
                                }
                            }));
                            // Looping for activity search setting for getting object id
                            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings, activitySettingIndex) {
                                queryLayerId = Number(settings.QueryLayerId);
                                if (queryLayerId === layerId && settings.Title === layerTitle) {
                                    objectIDField = settings.ObjectID;
                                    searchSettingName = "activitysetting";
                                }
                            }));
                            isAlreadyAdded = false;
                            // If my store has data
                            if (_self.myListStore && _self.myListStore.length > 0) {
                                // Looping for my list data
                                for (listData = 0; listData < _self.myListStore.length; listData++) {
                                    // Comparing object id for my list and facility object id value
                                    if (_self.myListStore[listData].value[_self.myListStore[listData].key] === facilityObject.Feature[facilityObject.SelectedItem.value].attributes[objectIDField]) {
                                        alert(sharedNls.errorMessages.activityAlreadyAdded);
                                        isAlreadyAdded = true;
                                        break;
                                    }
                                }
                            }
                            // If activity is not added then add to my list
                            if (!isAlreadyAdded) {
                                if (query(".esriCTEventsImg")[0]) {
                                    topic.publish("toggleWidget", "myList");
                                    topic.publish("showActivityPlannerContainer");
                                }
                                if (searchSettingName === "eventsetting") {
                                    facilityObject.Feature[facilityObject.SelectedItem.value] = _self.setDateWithUTC(facilityObject.Feature[facilityObject.SelectedItem.value]);
                                }
                                formatedDataField = _self._formatedData(facilityObject.Feature[facilityObject.SelectedItem.value]);
                                // Publishing function for add to list item
                                topic.publish("addToMyList", formatedDataField, facilityObject.WidgetName, layerId, layerTitle);
                            }
                        }));
                    }
                    divFacilityContainer = domConstruct.create("div", { "class": "esriCTResultContent" }, divHeaderContent[0]);
                    divFacilityContent = domConstruct.create("div", {}, divFacilityContainer);
                    contentDiv = domConstruct.create("div", { "class": "esriCTDivClear" }, divFacilityContent);
                    if (infowWindowData && infowWindowData.length === 0 && !descriptionValue) {
                        domConstruct.create("div", { "class": "esriCTInfoText", "innerHTML": sharedNls.errorMessages.fieldNotConfigured }, divFacilityContent);
                    }
                    if (infowWindowData) {
                        if (descriptionValue) {
                            descriptionResult = this._getDescription(facilityObject.Feature[facilityObject.SelectedItem.value].attributes, descriptionValue, this.featureClick);
                            domConstruct.create("div", {
                                "innerHTML": descriptionResult,
                                "class": "esriCTCustomPopupDiv"
                            }, contentDiv);
                        } else {
                            // Looping through info window data to set value
                            for (j = 0; j < infowWindowData.length; j++) {
                                facilityDiv = domConstruct.create("div", { "class": "esriCTInfoText" }, divFacilityContent);
                                if (string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes).match("http:") || string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes).match("https:")) {
                                    facilityDiv.innerHTML = infowWindowData[j].DisplayText + " ";
                                    domConstruct.create("a", { "class": "esriCTinfoWindowHyperlink", "href": string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes), "title": string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes), "innerHTML": sharedNls.titles.infoWindowTextURL, "target": "_blank" }, facilityDiv);
                                } else if (string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes).substring(0, 3) === "www") {
                                    domConstruct.create("a", { "class": "esriCTinfoWindowHyperlink", "href": "http://" + string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes), "title": "http://" + string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes), "innerHTML": sharedNls.titles.infoWindowTextURL, "target": "_blank" }, facilityDiv);
                                } else {
                                    try {
                                        //Get field value from feature attributes
                                        fieldValue = string.substitute(infowWindowData[j].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes);
                                    } catch (e) {
                                        fieldValue = appGlobals.configData.ShowNullValueAs;
                                    }
                                    fieldName = infowWindowData[j].FieldName.split("${")[1].split("}")[0];
                                    fieldInfo = this.isDateField(fieldName, operationLayer.layerDetails.layerObject);
                                    if (fieldInfo) {
                                        if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                            fieldValue = this.setDateFormat(infowWindowData[j], fieldValue);
                                        }
                                    } else {
                                        //Check if field has coded values
                                        fieldInfo = this.hasDomainCodedValue(fieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes, operationLayer.layerDetails.layerObject);
                                        if (fieldInfo) {
                                            if (fieldInfo.isTypeIdField) {
                                                fieldValue = fieldInfo.name;
                                            } else {
                                                domainValue = this.domainCodedValues(fieldInfo, fieldValue, this.featureClick);
                                                fieldValue = domainValue.domainCodedValue;
                                            }
                                        }
                                        if (infowWindowData[j].format) {
                                            fieldValue = this.numberFormatCorverter(infowWindowData[j], fieldValue);
                                        }
                                    }
                                    facilityDiv.innerHTML = infowWindowData[j].DisplayText + " " + fieldValue;
                                }
                            }
                        }
                        // If it is not coming from event layer then set facility icons
                        if (facilityObject.WidgetName.toLowerCase() !== "event" && appGlobals.configData.ActivitySearchSettings[0].Enable && !isEventSearched) {
                            divFacilityImages = domConstruct.create("div", { "class": "esriCTDivFacilityImages" }, divFacilityContent);
                            if (facilityObject.Feature) {
                                for (m = 0; m < appGlobals.configData.ActivitySearchSettings.length; m++) {
                                    SearchSettingsLayers = appGlobals.configData.ActivitySearchSettings[m];
                                }
                                for (k = 0; k < SearchSettingsLayers.ActivityList.length; k++) {
                                    if (string.substitute(SearchSettingsLayers.ActivityList[k].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes)) {
                                        if (facilityObject.Feature[facilityObject.SelectedItem.value].attributes[string.substitute(SearchSettingsLayers.ActivityList[k].FieldName, facilityObject.Feature[facilityObject.SelectedItem.value].attributes)] === SearchSettingsLayers.QualifyingActivityValue) {
                                            activityImageDiv = domConstruct.create("div", { "class": "esriCTActivityImage" }, divFacilityImages);
                                            domConstruct.create("img", { "src": SearchSettingsLayers.ActivityList[k].Image, "title": SearchSettingsLayers.ActivityList[k].Alias }, activityImageDiv);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        facilityDiv = domConstruct.create("div", { "class": "esriCTInfoText" }, divFacilityContent);
                        facilityDiv.innerHTML = sharedNls.errorMessages.fieldNotConfigured;
                    }
                }
            }
        },

        /**
        * Set the content in (Direction) carousel pod if user click on search result data
        * @param {object} directionObject contains widget name, solve route results, selected facility
        * @param {boolean} isInfoWindowClick
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        setDirection: function (directionObject, isInfoWindowClick) {
            var isPodEnabled = this.getPodStatus("DirectionsPod"), divHeaderContent, directionTitle, serchSetting, divHeader, divDirectionContainer, divDrectionContent, distanceAndDuration, printButton, j, divDrectionList, ConvertedTime, minutes;
            // If info window is clicked
            if (isInfoWindowClick) {
                isPodEnabled = true;
                divHeaderContent = query('.esriCTDirectionMainContainer');
            } else {
                divHeaderContent = query('.esriCTDivDirectioncontent');
            }
            // If Pod is enabled
            if (isPodEnabled) {
                if (divHeaderContent.length > 0) {
                    domConstruct.empty(divHeaderContent[0]);
                }
                // If it is coming from unified search
                serchSetting = this.getSearchSetting(directionObject.QueryURL);
                directionTitle = this.getKeyValue(serchSetting.SearchDisplayFields);
                divHeader = domConstruct.create("div", {}, divHeaderContent[0]);
                // If direction is found than set direction data in div
                if (directionObject.SelectedItem) {
                    domConstruct.create("div", { "class": "esriCTSpanHeader", "innerHTML": sharedNls.titles.directionText + " " + directionObject.Feature[directionObject.SelectedItem.value].attributes[directionTitle] }, divHeader);
                    // Set start location text
                    directionObject.SolveRoute[0].directions.features[0].attributes.text = directionObject.SolveRoute[0].directions.features[0].attributes.text.replace('Location 1', directionObject.Address);
                    if (directionObject.WidgetName.toLowerCase() !== "infoactivity" && directionObject.WidgetName.toLowerCase() !== "infoevent") {
                        printButton = domConstruct.create("div", { "class": "esriCTDivPrint", "title": sharedNls.tooltips.printButtonTooltip }, divHeader);
                        minutes = directionObject.SolveRoute[0].directions.totalDriveTime;
                        ConvertedTime = this.convertMinToHr(minutes);
                        this.own(on(printButton, "click", lang.hitch(this, this.print)));
                    }
                    minutes = directionObject.SolveRoute[0].directions.totalDriveTime;
                    ConvertedTime = this.convertMinToHr(minutes);
                    divDirectionContainer = domConstruct.create("div", { "class": "esriCTDirectionResultContent" }, divHeaderContent[0]);
                    distanceAndDuration = domConstruct.create("div", { "class": "esriCTDistanceAndDuration" }, divHeader);
                    domConstruct.create("div", { "class": "esriCTDivDistance", "innerHTML": sharedNls.titles.directionTextDistance + " " + parseFloat(directionObject.SolveRoute[0].directions.totalLength).toFixed(2) + this._getSubStringUnitData() }, distanceAndDuration);
                    domConstruct.create("div", { "class": "esriCTDivTime", "innerHTML": sharedNls.titles.directionTextTime + " " + ConvertedTime }, distanceAndDuration);
                    divDrectionContent = domConstruct.create("div", { "class": "esriCTDirectionRow" }, divDirectionContainer);
                    divDrectionList = domConstruct.create("ol", {}, divDrectionContent);
                    domConstruct.create("li", { "class": "esriCTInfotextDirection", "innerHTML": directionObject.SolveRoute[0].directions.features[0].attributes.text }, divDrectionList);
                    for (j = 1; j < directionObject.SolveRoute[0].directions.features.length; j++) {
                        domConstruct.create("li", { "class": "esriCTInfotextDirection", "innerHTML": directionObject.SolveRoute[0].directions.features[j].attributes.text + " (" + parseFloat(directionObject.SolveRoute[0].directions.features[j].attributes.length).toFixed(2) + this._getSubStringUnitData() + ")" }, divDrectionList);
                    }
                }
                topic.publish("hideProgressIndicator");
            }
        },

        /**
        * Set the images in (Gallery) carousel pod
        * @param {object} selectedFeature contains the information of search result
        * @param {object} resultcontent store the value of the click of search result
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        setGallery: function (selectedFeature, resultcontent) {
            var isPodEnabled = this.getPodStatus("GalleryPod"), divHeaderContent, layerID, isAttachmentFound = false, serchSetting, g, l, showAttachment = false, i, queryLayerId;
            serchSetting = this.getSearchSetting(selectedFeature.QueryURL);
            this.addGalleryPod();
            if (selectedFeature.WidgetName.toLowerCase() === "searchedfacility") {
                if (selectedFeature.activityData) {
                    for (g = 0; g < selectedFeature.activityData.length; g++) {
                        // Looping for features for getting search settings for further query
                        for (l = 0; l < selectedFeature.activityData[g].records.features.length; l++) {
                            if (selectedFeature.activityData[g].records.features[l].distance === selectedFeature.FeatureData[resultcontent.value].distance) {
                                serchSetting = this.getSearchSetting(selectedFeature.activityData[g].queryURL);
                            }
                        }
                    }
                } else {
                    serchSetting = this.getSearchSetting(selectedFeature.QueryURL);
                }
            }
            // If pod is enabled
            if (isPodEnabled) {
                divHeaderContent = query('.esriCTDivGalleryContent');
                if (divHeaderContent.length > 0) {
                    domConstruct.empty(divHeaderContent[0]);
                }
                for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                    queryLayerId = Number(serchSetting.QueryLayerId);
                    if (queryLayerId === appGlobals.operationLayerSettings[i].layerID && serchSetting.Title === appGlobals.operationLayerSettings[i].layerTitle) {
                        if (appGlobals.operationLayerSettings[i].layerDetails && appGlobals.operationLayerSettings[i].layerDetails.popupInfo) {
                            showAttachment = appGlobals.operationLayerSettings[i].layerDetails.popupInfo.showAttachments;
                        }
                    }
                }
                // If map has layer
                if (this.map._layers) {
                    // Looping for layer id in map layer
                    for (layerID in this.map._layers) {
                        if (this.map._layers.hasOwnProperty(layerID)) {
                            // If map has layer id than query on the basis of object id for getting attachments.
                            if (this.map._layers[layerID].hasAttachments && (this.map._layers[layerID].url && this.map._layers[layerID].url === serchSetting.QueryURL && showAttachment)) {
                                this.isGalleryPodEnabled = true;
                                this.map._layers[layerID].queryAttachmentInfos(selectedFeature.FeatureData[resultcontent.value].attributes[this.map._layers[layerID].objectIdField], lang.hitch(this, this.setAttachments), this.logError);
                                isAttachmentFound = true;
                                break;
                            }
                        }
                    }
                    // If attachment is not enabled then remove gallery pod from bottom container
                    if (!isAttachmentFound) {
                        this.isGalleryPodEnabled = false;
                        this.removeGalleryPod();
                    }
                }
            }
        },

        /**
        * Query on attachment and show the images on carousel pod
        * @param {object} response contain the images which are in the feature layer
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        setAttachments: function (response) {
            topic.publish("showProgressIndicator");
            var divAttachment, divHeaderContent, divPreviousImg, divNextImg, filteredResponse = [], i;
            this.imageCount = 0;
            divHeaderContent = query('.esriCTDivGalleryContent');
            // Looping if gallery div has data
            if (divHeaderContent.length > 0) {
                domConstruct.empty(divHeaderContent[0]);
                for (i = 0; i < response.length; i++) {
                    if (response[i].contentType.indexOf("image") > -1) {
                        filteredResponse.push(response[i]);
                    }
                }
                // If response is found show attachment in div
                if (filteredResponse && filteredResponse.length > 1) {
                    divPreviousImg = domConstruct.create("div", { "class": "esriCTImgPrev" }, divHeaderContent[0]);
                    divNextImg = domConstruct.create("div", { "class": "esriCTImgNext" }, divHeaderContent[0]);
                    divAttachment = domConstruct.create("img", { "class": "esriCTDivAttchment" }, divHeaderContent[0]);
                    domAttr.set(divAttachment, "src", filteredResponse[0].url);
                    // On click on image previous button
                    this.own(on(divPreviousImg, a11yclick, lang.hitch(this, function () {
                        this.imageCount--;
                        if (this.imageCount === 0) {
                            domStyle.set(divPreviousImg, "display", "none");
                        } else {
                            domStyle.set(divPreviousImg, "display", "block");
                        }
                        domStyle.set(divNextImg, "display", "block");
                        domAttr.set(divAttachment, "src", filteredResponse[this.imageCount].url);
                    })));
                    // On click on image next button
                    this.own(on(divNextImg, a11yclick, lang.hitch(this, function () {
                        this.imageCount++;
                        if (this.imageCount === filteredResponse.length - 1) {
                            domStyle.set(divNextImg, "display", "none");
                        } else {
                            domStyle.set(divNextImg, "display", "block");
                        }
                        domStyle.set(divPreviousImg, "display", "block");
                        domAttr.set(divAttachment, "src", filteredResponse[this.imageCount].url);
                    })));
                } else if (filteredResponse.length === 1) {
                    divAttachment = domConstruct.create("img", { "class": "esriCTDivAttchment" }, divHeaderContent[0]);
                    domAttr.set(divAttachment, "src", filteredResponse[0].url);
                } else {
                    domConstruct.create("div", { "class": "esriCTGalleryBox", "innerHTML": sharedNls.errorMessages.imageDoesNotFound }, divHeaderContent[0]);
                }
            }
            topic.publish("hideProgressIndicator");
        },

        /**
        * Show error in console
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        logError: function (error) {
            var divHeaderContent;
            console.log(error);
            divHeaderContent = query('.esriCTDivGalleryContent');
            if (divHeaderContent.length > 0) {
                domConstruct.empty(divHeaderContent[0]);
            }
            domConstruct.create("div", { "class": "esriCTGalleryBox", "innerHTML": error }, divHeaderContent[0]);
        },

        /**
        * Set the content in (Comments) carousel pod
        * @param {object} feature contains feature
        * @param {object} result contains features array
        * @param {object} resultcontent store the value of the click of search result
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        setComment: function (feature, result, resultcontent, queryURL) {
            var isPodEnabled = this.getPodStatus("CommentsPod"), divHeaderContent, isActivityLayerFound = false, j, index, divHeaderStar, divStar, commentAttribute, utcMilliseconds, l, isCommentFound, rankFieldAttribute, esriCTCommentDateStar, divCommentRow, updatedCommentAttribute, formatedDate;
            // Checking for activity settings and comment settings enabled tag for showing comment layer.
            if (!appGlobals.configData.ActivitySearchSettings[0].Enable || !appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.Enabled || appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.QueryURL === "") {
                this.removeCommentPod();
            } else {
                if (isPodEnabled) {
                    // looping in activity search for setting comment data
                    for (index = 0; index < appGlobals.configData.ActivitySearchSettings.length; index++) {
                        // Checking for search setting to enable and disable comment pod
                        if (appGlobals.configData.ActivitySearchSettings[0].QueryURL === queryURL) {
                            isActivityLayerFound = true;
                        }
                        // If comment setting is set enable and if it is an Activity layer setting than do further things
                        if (appGlobals.configData.ActivitySearchSettings[index].CommentsSettings.Enabled && isActivityLayerFound) {
                            // Add comment pod for activity layer
                            this.addCommentPod();
                            divHeaderContent = query('.esriCTDivCommentContent');
                            // If length is equal to 0
                            if (result.length === 0) {
                                if (divHeaderContent[0]) {
                                    domConstruct.empty(divHeaderContent[0]);
                                }
                                divCommentRow = domConstruct.create("div", { "class": "esriCTRowNoComment" }, divHeaderContent[0]);
                                domConstruct.create("div", { "class": "esriCTInfotextRownoComment", "innerHTML": sharedNls.errorMessages.noCommentsAvailable }, divCommentRow);
                                return;
                            }
                            result = this.removeNullValue(result);
                            isCommentFound = false;
                            // If result is found and has data
                            if (result.length !== 0) {
                                divHeaderContent = query('.esriCTDivCommentContent');
                                if (divHeaderContent[0]) {
                                    domConstruct.empty(divHeaderContent[0]);
                                }
                                // Looping for result data
                                for (l = 0; l < result.length; l++) {
                                    rankFieldAttribute = string.substitute(appGlobals.configData.ActivitySearchSettings[index].CommentsSettings.RankField, result[l].attributes);
                                    commentAttribute = string.substitute(appGlobals.configData.ActivitySearchSettings[index].CommentsSettings.CommentField, result[l].attributes);
                                    updatedCommentAttribute = this._getFormattedCommentText(commentAttribute);
                                    if (updatedCommentAttribute) {
                                        divCommentRow = domConstruct.create("div", { "class": "esriCTDivCommentRow" }, divHeaderContent[0]);
                                        isCommentFound = true;
                                        esriCTCommentDateStar = domConstruct.create("div", { "class": "esriCTCommentDateStar" }, divCommentRow);
                                        divHeaderStar = domConstruct.create("div", { "class": "esriCTHeaderRatingStar" }, esriCTCommentDateStar);
                                        // Looping for showing 5 star in comment div
                                        for (j = 0; j < 5; j++) {
                                            divStar = domConstruct.create("span", { "class": "esriCTRatingStar" }, divHeaderStar);
                                            if (j < rankFieldAttribute) {
                                                domClass.add(divStar, "esriCTRatingStarChecked");
                                            }
                                        }
                                        if (string.substitute(appGlobals.configData.ActivitySearchSettings[index].CommentsSettings.SubmissionDateField, result[l].attributes) === appGlobals.configData.ShowNullValueAs) {
                                            utcMilliseconds = appGlobals.configData.ShowNullValueAs;
                                        } else {
                                            utcMilliseconds = Number(string.substitute(appGlobals.configData.ActivitySearchSettings[index].CommentsSettings.SubmissionDateField, result[l].attributes));
                                        }
                                        domConstruct.create("div", { "class": "esriCTCommentText", "innerHTML": updatedCommentAttribute }, divCommentRow);
                                        if (utcMilliseconds === appGlobals.configData.ShowNullValueAs) {
                                            domConstruct.create("div", { "class": "esriCTCommentDate", "innerHTML": appGlobals.configData.ShowNullValueAs }, esriCTCommentDateStar);
                                        } else {
                                            formatedDate = this._changeDateFormatForComment(result[l].attributes, appGlobals.configData.ActivitySearchSettings[index].CommentsSettings.SubmissionDateField);
                                            domConstruct.create("div", { "class": "esriCTCommentDate", "innerHTML": formatedDate }, esriCTCommentDateStar);
                                        }
                                    }
                                }
                            }
                            // If comment is not found than show comment not found in div
                            if (!isCommentFound) {
                                divCommentRow = domConstruct.create("div", { "class": "esriCTDivCommentRow" }, divHeaderContent[0]);
                                domConstruct.create("div", { "class": "esriCTInfotextRownoComment", "innerHTML": sharedNls.errorMessages.noCommentsAvailable }, divCommentRow);
                            }
                        } else {
                            this.removeCommentPod();
                            return;
                        }
                    }
                }
            }
        },

        /**
        * Initialize the object of printMap Widget
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        print: function () {
            topic.publish("showProgressIndicator");
            this._esriDirectionsWidget._printDirections();
            topic.publish("hideProgressIndicator");
        },

        /**
        * Get the setting name by passing query layer
        * @ return search setting Data
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        getSearchSetting: function (queryURL) {
            var settingData;
            // Looping for fetching search settings of the specified layer from EventSearchSettings
            array.forEach(appGlobals.configData.EventSearchSettings, lang.hitch(this, function (settings, eventSettingIndex) {
                if (settings.QueryURL === queryURL) {
                    settingData = settings;
                }
            }));
            // Looping for fetching search settings of the specified layer from ActivitySearchSettings
            array.forEach(appGlobals.configData.ActivitySearchSettings, lang.hitch(this, function (settings, activitySettingIndex) {
                if (settings.QueryURL === queryURL) {
                    settingData = settings;
                }
            }));
            return settingData;
        },

        /**
        * get the Unit text from config file after removing esri text
        * @ return route unit name
        * @memberOf widgets/commonHelper/carouselContainerHelper
        */
        _getSubStringUnitData: function () {
            var routeUnitString, unitsValue, unitName, defaultUnit = "Miles";
            // If in direction unit esri text is found then set unit value
            if (this._esriDirectionsWidget.directionsLengthUnits.substring(0, 4) === "esri") {
                unitsValue = this._esriDirectionsWidget.directionsLengthUnits.substring(4, this._esriDirectionsWidget.directionsLengthUnits.length).toUpperCase();
            } else {
                // Else set it to kilometers.
                unitsValue = "KILOMETERS";
            }
            // Switch for units value and setting unit name according to it.
            switch (unitsValue) {
            case "MILES":
                unitName = "Miles";
                break;
            case "METERS":
                unitName = "Meters";
                break;
            case "KILOMETERS":
                unitName = "Kilometers";
                break;
            case "NAUTICALMILES":
                unitName = "Nautical Miles";
                break;
            default:
                unitName = defaultUnit;
                break;
            }
            routeUnitString = " " + unitName;
            return routeUnitString;
        }
    });
});
