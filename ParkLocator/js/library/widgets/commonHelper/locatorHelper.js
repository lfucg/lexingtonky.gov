/*global define,dojo,dojoConfig:true,alert,esri,Modernizr,appGlobals */
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
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/dom",
    "dojo/on",
    "dojo/_base/array",
    "esri/tasks/query",
    "dojo/promise/all",
    "esri/tasks/QueryTask",
    "esri/graphic",
    "dijit/_WidgetBase",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "dijit/form/HorizontalSlider",
    "dijit/form/HorizontalRule",
    "esri/tasks/BufferParameters",
    "dojo/_base/Color",
    "esri/tasks/GeometryService",
    "esri/symbols/SimpleLineSymbol",
    "esri/symbols/SimpleFillSymbol"

], function (declare, lang, domStyle, domAttr, dom, on, array, Query, all, QueryTask, Graphic, _WidgetBase, sharedNls, topic, HorizontalSlider, HorizontalRule, BufferParameters, Color, GeometryService, SimpleLineSymbol, SimpleFillSymbol) {
    // ========================================================================================================================//

    return declare([_WidgetBase], {
        sharedNls: sharedNls, // Variable for shared NLS
        unitValues: [null, null, null, null],

        /**
        * Create buffer around pushpin
        * @param {object} mapPoint Contains the map point on map
        * @param {string} widgetName Contains the name of the functionality from where buffer is created.
        * @memberOf widgets/commonHelper/locatorHelper
        */
        createBuffer: function (mapPoint, widgetName) {
            var params, geometryService, isValid;
            this.carouselContainer.removeAllPod();
            this.carouselContainer.addPod(this.carouselPodData);
            this.removeBuffer();
            geometryService = new GeometryService(appGlobals.configData.GeometryService);
          // Checking the map point or map point is having geometry and if config data has buffer distance.
          //TODO...JH Set bufferDistance from slider value
            //isValid = this._validateRangeFilterValues();
          //if ((mapPoint || mapPoint.geometry) && appGlobals.configData.BufferDistance) {
          //if ((mapPoint || mapPoint.geometry) && isValid) {
            if ((mapPoint || mapPoint.geometry)) {
              var geometry = mapPoint.geometry;
              if (typeof (geometry) === 'undefined') {
                geometry = mapPoint;
              }
              //TODO...JH check out the share options
              //appGlobals.shareOptions.arrBufferDistance[this.workflowCount] = bufferDistance;
              //commented from site-selector...this.featureGeometry[this.workflowCount] = geometry;
              //commented from site-selector...selectedPanel = query('.esriCTsearchContainerSitesSelected')[0];
              // set slider values for various workflows

              slider = dijit.byId("sliderhorizontalSliderContainer");
                sliderDistance = slider.value;

              //////////////////////////
                if (Math.round(sliderDistance) !== 0) {
                  if (geometry && geometry.type === "point") {
                    //setup the buffer parameters
                    params = new BufferParameters();
                    params.distances = [Math.round(sliderDistance)];
                    params.bufferSpatialReference = this.map.spatialReference;
                    params.outSpatialReference = this.map.spatialReference;
                    params.geometries = [geometry];
                    params.unit = GeometryService[this._getDistanceUnit(appGlobals.configData.DistanceUnitSettings.DistanceUnitName)];
                    geometryService.buffer(params, lang.hitch(this, function (geometries) {
                      this.showBuffer(geometries, mapPoint, widgetName);
                    }));
                  } else {
                    topic.publish("hideProgressIndicator");
                  }
                }
                //else {
                //  topic.publish("hideProgressIndicator");
                //  if (document.activeElement) {
                //    document.activeElement.blur();
                //  }
                //  // clear buildings, sites and business tab data
                //  if (this.workflowCount === 0) {
                //    domStyle.set(this.outerDivForPegination, "display", "none");
                //    domConstruct.empty(this.outerResultContainerBuilding);
                //    domConstruct.empty(this.attachmentOuterDiv);
                //    delete this.buildingTabData;
                //  } else if (this.workflowCount === 1) {
                //    domStyle.set(this.outerDivForPeginationSites, "display", "none");
                //    domConstruct.empty(this.outerResultContainerSites);
                //    domConstruct.empty(this.attachmentOuterDivSites);
                //    delete this.sitesTabData;
                //  } else if (this.workflowCount === 2) {
                //    this._clearBusinessData();
                //  }
                //  this.lastGeometry[this.workflowCount] = null;
                //  this.map.graphics.clear();
                //  this.map.getLayer("esriBufferGraphicsLayer").clear();
                //  alert(sharedNls.errorMessages.bufferSliderValue);
                //  this.isSharedExtent = false;
                //}
              //////////////////////////

                //params = new BufferParameters();
                //params.distances = [appGlobals.configData.BufferDistance];
              //TODO...JH Get buffer distance from config...will need to map config values to geom service constants 
                //params.unit = GeometryService.UNIT_STATUTE_MILE;
                //params.bufferSpatialReference = this.map.spatialReference;
                //params.outSpatialReference = this.map.spatialReference;
                // Checking the geometry
                //if (mapPoint.geometry) {
                //    params.geometries = [mapPoint.geometry];
                //} else {
                //    params.geometries = [mapPoint];
                //}
                // Ccreating buffer and calling show buffer function.
                //geometryService.buffer(params, lang.hitch(this, function (geometries) {
                //    this.showBuffer(geometries, mapPoint, widgetName);
                //}));
            }
        },

      /**
* get distance unit based on unit selection
* @param {string} input distance unit
* @memberOf widgets/siteLocator/siteLocatorHelper
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
        * Show buffer on map
        * @param {object} geometries of mapPoint
        * @param {object} mapPoint Contains the map point
        * @memberOf widgets/commonHelper/locatorHelper
        */
        showBuffer: function (geometries, mapPoint, widgetName) {
            var bufferSymbol;
            // Checking the geolocation variable in the case of share app.
            if (!appGlobals.shareOptions.sharedGeolocation) {
                this._clearBuffer();
            }
            bufferSymbol = new SimpleFillSymbol(SimpleFillSymbol.STYLE_SOLID, new SimpleLineSymbol(SimpleLineSymbol.STYLE_SOLID, new Color([parseInt(appGlobals.configData.BufferSymbology.LineSymbolColor.split(",")[0], 10), parseInt(appGlobals.configData.BufferSymbology.LineSymbolColor.split(",")[1], 10), parseInt(appGlobals.configData.BufferSymbology.FillSymbolColor.split(",")[2], 10), parseFloat(appGlobals.configData.BufferSymbology.LineSymbolTransparency.split(",")[0], 10)]), 2),
                        new Color([parseInt(appGlobals.configData.BufferSymbology.FillSymbolColor.split(",")[0], 10), parseInt(appGlobals.configData.BufferSymbology.FillSymbolColor.split(",")[1], 10), parseInt(appGlobals.configData.BufferSymbology.LineSymbolColor.split(",")[2], 10), parseFloat(appGlobals.configData.BufferSymbology.FillSymbolTransparency.split(",")[0], 10)]));
            // Adding graphic on map
            try {
              this._addGraphic(this.map.getLayer("tempBufferLayer"), bufferSymbol, geometries[0]);
            } catch (error) {
                alert(sharedNls.errorMessages.unableToDrawBuffer);
                topic.publish("hideProgressIndicator");
                this.zoomToFullRoute = true; /* Changed Code for GitHub issue #182 */
            }
            topic.publish("showProgressIndicator");
            // Querying for layer to find features.
            this._queryLayer(geometries[0], mapPoint, widgetName);
        },

        /**
        * Clear buffer from map
        * @memberOf widgets/commonHelper/locatorHelper
        */
        _clearBuffer: function () {
            if (this.map.getLayer("tempBufferLayer") && this.map.getLayer("tempBufferLayer").graphics.length > 0) {
                this.map.getLayer("tempBufferLayer").clear();
            }
            topic.publish("hideInfoWindow");
            this.isInfowindowHide = true;
            this.zoomToFullRoute = true;     
        },

        /**
        * Add graphic layer on map of buffer and set expand
        * @param {object} layer Contains feature layer
        * @param {object} symbol Contains graphic
        * @param {object}point Contains the map point
        * @memberOf widgets/commonHelper/locatorHelper
        */
        _addGraphic: function (layer, symbol, point) {
            var graphic;
            graphic = new Graphic(point, symbol);
            layer.add(graphic);
            this.point = point;
            this.zoomToFullRoute = false;
            // Checking the extent changed variable in the case of shared app to maintain extent on map
            if (window.location.href.toString().split("$extentChanged=").length > 1) {
                // If extent change variable set to be true then set the extent other wise don't do any thing.
                if (this.isExtentSet) {
                    this.map.setExtent(point.getExtent().expand(1.6));
                }
            } else {
                // In normal scenario set extent when graphics is added.
                this.map.setExtent(point.getExtent().expand(1.6));
            }
        },

        /**
        * Query layer URL
        * Create an object of graphic
        * @param {object} geometry of graphic
        * @param {object} mapPoint Contains the map point
        * @param {object} widget name of the functionality from query layer is called.
        * @memberOf widgets/commonHelper/locatorHelper
        */
        _queryLayer: function (geometry, mapPoint, widget) {
            var layerobject, i, deferredArray = [], result = [], widgetName, featuresWithinBuffer = [],
                dist, featureSet = [], isDistanceFound, j, k, routeObject;
            // Validate selectedLayerTitle for querying on each layer configured, for finding facility within the buffer.
            if (widget) {
                widgetName = widget;
            } else {
                widgetName = "unifiedSearch";
            }
            if (this.selectedLayerTitle) {
                // Looping each layer for query
                array.forEach(appGlobals.configData.SearchSettings, lang.hitch(this, function (SearchSettings) {
                    // Checking search display title for getting layer.
                    if (SearchSettings.SearchDisplayTitle === this.selectedLayerTitle) {
                        layerobject = SearchSettings;
                        // Query on layer for facility.
                        this._queryLayerForFacility(layerobject, geometry, deferredArray, result);
                    }
                }));
            } else {
                // Looping on each layer for finding facility within the buffer
                for (i = 0; i < appGlobals.configData.SearchSettings.length; i++) {
                    layerobject = appGlobals.configData.SearchSettings[i];
                    this._queryLayerForFacility(layerobject, geometry, deferredArray, result);
                }
                // Calling deferred list when all query is completed.
                all(deferredArray).then(lang.hitch(this, function (relatedRecords) {
                    // looping the result for getting records and pushing it in a variable for further query
                    for (j = 0; j < result.length; j++) {
                        if (result.length > 0) {
                            this.dateFieldArray = this.getDateField(result[j].records);
                            for (k = 0; k < result[j].records.features.length; k++) {
                                featuresWithinBuffer.push(result[j].records.features[k]);
                            }
                        }
                    }
                    // Looping final array for finding distance from start point and calculating route.
                    for (i = 0; i < featuresWithinBuffer.length; i++) {
                        // Checking the geometry
                        if (mapPoint.geometry) {
                            dist = this.getDistance(mapPoint.geometry, featuresWithinBuffer[i].geometry);
                            isDistanceFound = true;
                        }
                        try {
                            featureSet[i] = featuresWithinBuffer[i];
                            featuresWithinBuffer[i].distance = dist.toString();
                        } catch (err) {
                            alert(sharedNls.errorMessages.falseConfigParams);
                        }
                    }
                    // If distance is calculated from the start point
                    if (isDistanceFound) {
                        featureSet.sort(function (a, b) {
                            return parseFloat(a.distance) - parseFloat(b.distance);
                        });
                        // looping the result data for sorting data by distance
                        array.forEach(result, lang.hitch(this, function (resultSet) {
                            resultSet.records.features.sort(function (a, b) {
                                return parseFloat(a.distance) - parseFloat(b.distance);
                            });
                        }));
                        this.highlightFeature(featureSet[0].geometry);
                        // Changing date format for feature if date field is available.
                        routeObject = { "StartPoint": mapPoint, "EndPoint": featureSet, "Index": 0, "WidgetName": widgetName, "QueryURL": layerobject.QueryURL, "activityData": result };
                        //Calling route function to create route
                        this.showRoute(routeObject);
                    }
                    // Checking result array length, if it is 0 then show message and hide carousel container and remove graphics
                    if (result.length === 0) {
                        alert(sharedNls.errorMessages.facilityNotfound);
                        appGlobals.shareOptions.eventInfoWindowData = null;
                        appGlobals.shareOptions.infoRoutePoint = null;
                        this.removeRouteGraphichOfDirectionWidget();
                        this.removeHighlightedCircleGraphics();
                        if (widgetName !== "unifiedSearch") {
                            this.removeLocatorPushPin();
                        }
                        if (this.carouselContainer) {
                            this.carouselContainer.hideCarouselContainer();
                            this.carouselContainer._setLegendPositionDown();
                        }
                        topic.publish("hideProgressIndicator");
                    }
                }));
            }
        },

        /**
        * Query layer for getting facilty
        * finding route from start point to the nearest feature
        * @param {object} layerobject contains the layer information
        * @param {object} geometry contains the geometry
        * @param {object} deferredArray contains deferred array for further operation
        * @param {object} result array to contain feature data
        * @memberOf widgets/commonHelper/locatorHelper
        */
        _queryLayerForFacility: function (layerobject, geometry, deferredArray, result) {
            var queryTask, queryLayer, layerObject;
            // Checking the query url availability
            if (layerobject.QueryURL) {
                queryTask = new QueryTask(layerobject.QueryURL);
                queryLayer = new Query();
                queryLayer.outFields = ["*"];
                queryLayer.returnGeometry = true;
                // Checking the geometry
                if (geometry) {
                    queryLayer.geometry = geometry;
                }
                layerObject = {};
                // Pushing the query task in deferred array for further query
                deferredArray.push(queryTask.execute(queryLayer, lang.hitch(this, function (records) {
                    layerObject = { "queryURL": layerobject.QueryURL, "records": records };
                    // If feature is available the push data in result.
                    if (records.features.length > 0) {
                        result.push(layerObject);
                    }
                })));
            } else {
                topic.publish("hideProgressIndicator");
            }
        }
    });
});
