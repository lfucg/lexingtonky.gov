﻿/*global define,dojo,dojoConfig,appGlobals */
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
    "dojo/_base/lang",
    "dojo/dom-attr",
    "dojo/dom",
    "dojo/text!./templates/appHeaderTemplate.html",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/dom-class",
    "dojo/topic"
], function (declare, domConstruct, lang, domAttr, dom, template, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, sharedNls, domClass, topic) {

    //========================================================================================================================//

    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        sharedNls: sharedNls,

        /**
        * create header panel
        *
        * @param {string} appGlobals.configData.ApplicationName Application name specified in configuration file
        *
        * @class
        * @name widgets/appHeader/appHeader
        */
        postCreate: function () {

            /**
            * add applicationHeaderParentContainer to div for header panel and append to esriCTParentDivContainer container
            *
            * applicationHeaderParentContainer container for application header
            * @member {div} applicationHeaderParentContainer
            * @private
            * @memberOf widgets/appHeader/appHeader
            */
            var applicationHeaderDiv;
            topic.subscribe("showProgressIndicator", lang.hitch(this, this.showProgressIndicator));
            topic.subscribe("hideProgressIndicator", lang.hitch(this, this.hideProgressIndicator));

            applicationHeaderDiv = domConstruct.create("div", {}, dom.byId("esriCTParentDivContainer"));
            domConstruct.place(this.applicationHeaderParentContainer, applicationHeaderDiv);
            this._loadApplicationHeaderIcon();
            /**
            * set browser header and application header to application name
            *
            * applicationHeaderName container for application name
            * @member {div} applicationHeaderName
            * @private
            * @memberOf widgets/appHeader/appHeader
            */
            document.title = appGlobals.configData.ApplicationName;
            domAttr.set(this.applicationHeaderName, "innerHTML", appGlobals.configData.ApplicationName);
        },

        /**
        * append widgets to header panel
        * @param {object} widgets Contain widgets to be displayed in header panel
        * @memberOf widgets/appHeader/appHeader
        */

        loadHeaderWidgets: function (widgets) {
            var i;
            /**
            * applicationHeaderWidgetsContainer container for header panel widgets
            * @member {div} applicationHeaderWidgetsContainer
            * @private
            * @memberOf widgets/appHeader/appHeader
            */
            for (i in widgets) {
                if (widgets.hasOwnProperty(i)) {
                    if (widgets[i].domNode) {
                        domConstruct.place(widgets[i].domNode, this.applicationHeaderWidgetsContainer);
                    }
                }
            }
        },

        /**
        * load Application Header Icon
        * @memberOf widgets/appHeader/appHeader
        */
        _loadApplicationHeaderIcon: function () {
            if (appGlobals.configData.ApplicationFavicon && lang.trim(appGlobals.configData.ApplicationFavicon).length !== 0) {
                this._loadIcons("shortcut icon", appGlobals.configData.ApplicationFavicon);
            }
            if (appGlobals.configData.ApplicationIcon && lang.trim(appGlobals.configData.ApplicationIcon).length !== 0) {
                this._loadIcons("apple-touch-icon-precomposed", appGlobals.configData.ApplicationIcon);
                this._loadIcons("apple-touch-icon", appGlobals.configData.ApplicationIcon);
                domConstruct.create("img", { "class": "", "src": dojoConfig.baseURL + appGlobals.configData.ApplicationIcon }, this.divImgApplicationHeaderIcon);
            }
        },

        /**
        * load Application icon to be shown as application shortcut icon
        * @memberOf widgets/appHeader/appHeader
        */
        _loadIcons: function (rel, iconPath) {
            var icon;
            icon = domConstruct.create("link");
            icon.rel = rel;
            icon.type = "image/x-icon";
            icon.href = dojoConfig.baseURL + iconPath;
            document.getElementsByTagName('head')[0].appendChild(icon);
        },

        /**
        * showing progess indicator
        * @memberOf widgets/appHeader/appHeader
        */
        showProgressIndicator: function () {
            domClass.replace(this.divLoadingIndicator, "displayBlock", "displayNone");
        },

        /**
        * hiding progess indicator
        * @memberOf widgets/appHeader/appHeader
        */
        hideProgressIndicator: function () {
            domClass.replace(this.divLoadingIndicator, "displayNone", "displayBlock");
        }
    });
});
