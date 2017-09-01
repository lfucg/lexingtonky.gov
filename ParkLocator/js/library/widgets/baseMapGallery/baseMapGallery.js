﻿/*global define,dojo,dojoConfig,esri,alert,selectedBasemap,appGlobals */
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
    "dojo/dom-construct",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/on",
    "dojo/dom",
    "dojo/query",
    "dojo/text!./templates/baseMapGalleryTemplate.html",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "esri/layers/ArcGISTiledMapServiceLayer",
    "esri/layers/OpenStreetMapLayer",
    "dijit/a11yclick",
    "esri/layers/ArcGISDynamicMapServiceLayer",
    "esri/layers/ArcGISImageServiceLayer",
    "esri/layers/ImageParameters",
    "esri/layers/ImageServiceParameters"
], function (declare, domConstruct, array, lang, on, dom, query, template, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, ArcGISTiledMapServiceLayer, OpenStreetMapLayer, a11yclick, ArcGISDynamicMapServiceLayer, ArcGISImageServiceLayer, ImageParameters, ImageServiceParameters) {
    //========================================================================================================================//

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        enableToggling: false,
        isBasemapLayerRemoved: false,
        /**
        * create baseMapGallery widget
        *
        * @class
        * @name widgets/baseMapGallery/baseMapGallery
        */
        postCreate: function () {
            //add basemap layer if old basemap is removed
            this.map.on("layer-remove", lang.hitch(this, function (layer) {
                if (this.enableToggling && this.isBasemapLayerRemoved) {
                    this.isBasemapLayerRemoved = false;
                    this._addBasemapLayerOnMap();
                }
            }));
            this.map.on("layer-add", lang.hitch(this, function (layer) {
                this.enableToggling = true;
            }));
            //do not display basemap toggle if only one basemap is found
            if (appGlobals.configData.BaseMapLayers.length > 1) {
                dom.byId("esriCTParentDivContainer").appendChild(this.esriCTDivLayerContainer);
                //add basemap toggle div in application parent div
                this.layerList.appendChild(this._createBaseMapElement());
            }
            this._loadSharedBasemap();
        },

        /**
        * create UI for basemap toggle widget
        * @memberOf widgets/baseMapGallery/baseMapGallery
        */
        _createBaseMapElement: function () {
            var divContainer, imgThumbnail, thumbnailPath, basemap;
            this.enableToggling = true;
            if (appGlobals.shareOptions.selectedBasemapIndex === appGlobals.configData.BaseMapLayers.length - 1) {
                basemap = appGlobals.configData.BaseMapLayers[0];
            } else {
                basemap = appGlobals.configData.BaseMapLayers[appGlobals.shareOptions.selectedBasemapIndex + 1];
            }
            //set basemap thumbnail URL
            if (basemap.length) {
                thumbnailPath = basemap[0].ThumbnailSource;
            } else {
                thumbnailPath = basemap.ThumbnailSource;
            }
            divContainer = domConstruct.create("div", { "class": "esriCTbaseMapContainerNode" });
            imgThumbnail = domConstruct.create("img", { "class": "esriCTBasemapThumbnail", "src": thumbnailPath }, null);
            //attach click event on basemap toggle div
            on(imgThumbnail, a11yclick, lang.hitch(this, function () {
                if (this.enableToggling) {
                    //change basemap index
                    appGlobals.shareOptions.selectedBasemapIndex++;
                    this._changeBasemapThumbnail();
                }
            }));
            divContainer.appendChild(imgThumbnail);
            return divContainer;
        },

        /**
        * change basemap layer
        * @memberOf widgets/baseMapGallery/baseMapGallery
        */
        _changeBaseMap: function (preLayerIndex) {
            var basemap, basemapLayers;
            basemapLayers = appGlobals.configData.BaseMapLayers[preLayerIndex];
            this.enableToggling = false;
            //check if old basemap has multilayer
            if (basemapLayers.length) {
                array.forEach(basemapLayers, lang.hitch(this, function (layer, index) {
                    basemap = this.map.getLayer(layer.BasemapId);
                    if (basemapLayers.length - 1 === index) {
                        this.enableToggling = true;
                    }
                    if (basemap) {
                        this.isBasemapLayerRemoved = true;
                        this.map.removeLayer(basemap);
                    }
                }));
            } else {
                //remove previous basemap layer from map
                basemap = this.map.getLayer(basemapLayers.BasemapId);
                if (basemap) {
                    this.enableToggling = true;
                    this.isBasemapLayerRemoved = true;
                    this.map.removeLayer(basemap);
                }
            }
        },

        /**
        * get shared basemap
        * @memberOf widgets/baseMapGallery/baseMapGallery
        */
        _addBasemapLayerOnMap: function () {
            var layer, params, imageParameters, basemapLayers = appGlobals.configData.BaseMapLayers[appGlobals.shareOptions.selectedBasemapIndex];
            //check if basmap has multilayer
            if (basemapLayers.length) {
                array.forEach(basemapLayers, lang.hitch(this, function (basemap, index) {
                    this.enableToggling = false;
                    layer = new ArcGISTiledMapServiceLayer(basemap.MapURL, { id: basemap.BasemapId, visible: true });
                    this.map.addLayer(layer, index);
                }));
            } else {
                this.enableToggling = false;
                //add basemap layer on map
                if (basemapLayers.layerType === "OpenStreetMap") {
                    //add basemap as open street layer
                    layer = new OpenStreetMapLayer({ id: basemapLayers.BasemapId, visible: true });
                } else if (basemapLayers.layerType === "ArcGISMapServiceLayer") {
                    imageParameters = new ImageParameters();
                    layer = new ArcGISDynamicMapServiceLayer(basemapLayers.MapURL, {
                        "imageParameters": imageParameters,
                        id: basemapLayers.BasemapId
                    });
                } else if (basemapLayers.layerType === "ArcGISImageServiceLayer") {
                    //add basemap as image service layer
                    params = new ImageServiceParameters();
                    layer = new ArcGISImageServiceLayer(basemapLayers.MapURL, {
                        imageServiceParameters: params,
                        id: basemapLayers.BasemapId,
                        opacity: 0.75
                    });
                } else {
                    //add basemap as tiled service layer
                    layer = new ArcGISTiledMapServiceLayer(basemapLayers.MapURL, { id: basemapLayers.BasemapId, visible: true });
                }
                this.map.addLayer(layer, 0);
            }
        },

        /**
        * get shared basemap
        * @memberOf widgets/baseMapGallery/baseMapGallery
        */
        _loadSharedBasemap: function () {
            //check if basemap is shared in app URL
            if (window.location.toString().split("$selectedBasemapIndex=").length > 1 && appGlobals.configData.BaseMapLayers.length > 1) {
                var preLayerIndex;
                //display shared basemap layer on map
                if (this.isWebmap) {
                    preLayerIndex = appGlobals.shareOptions.selectedBasemapIndex;
                }
                appGlobals.shareOptions.selectedBasemapIndex = parseInt(window.location.toString().split("$selectedBasemapIndex=")[1].split("$")[0], 10);
                // check if shared basemap is already added on map
                if (preLayerIndex !== appGlobals.shareOptions.selectedBasemapIndex) {
                    this._changeBasemapThumbnail(preLayerIndex);
                }
            } else if (!this.isWebmap) {
                //add default basemap on map if it is not a webmap
                this._addBasemapLayerOnMap();
            }
        },

        /**
        * change basemap thumbnail
        * @memberOf widgets/baseMapGallery/baseMapGallery
        */
        _changeBasemapThumbnail: function (preIndex) {
            var baseMapURLCount, presentThumbNail, preLayerIndex, thumbnailPath;
            baseMapURLCount = appGlobals.configData.BaseMapLayers.length;
            preLayerIndex = appGlobals.shareOptions.selectedBasemapIndex - 1;
            //show first basemap map if old one is the last basemap in basemap array
            if (appGlobals.shareOptions.selectedBasemapIndex === baseMapURLCount) {
                appGlobals.shareOptions.selectedBasemapIndex = 0;
            }
            //set old basemap index
            if (appGlobals.shareOptions.selectedBasemapIndex === 0) {
                preLayerIndex = baseMapURLCount - 1;
            }
            //show basemap thumbnail of next basemap
            presentThumbNail = appGlobals.shareOptions.selectedBasemapIndex + 1;
            //show first basemap thumbnail if old basemap is the last basemap in array
            if (appGlobals.shareOptions.selectedBasemapIndex === baseMapURLCount - 1) {
                presentThumbNail = 0;
            }
            //display shared basemap
            if (preIndex || preIndex === 0) {
                preLayerIndex = preIndex;
            }
            this._changeBaseMap(preLayerIndex);
            //check if current base is a multilayer basemap or not
            if (appGlobals.configData.BaseMapLayers[presentThumbNail].length) {
                thumbnailPath = appGlobals.configData.BaseMapLayers[presentThumbNail][0].ThumbnailSource;
            } else {
                thumbnailPath = appGlobals.configData.BaseMapLayers[presentThumbNail].ThumbnailSource;
            }
            //set basemap thumbnail URL
            query('.esriCTBasemapThumbnail')[0].src = thumbnailPath;
        }
    });
});
